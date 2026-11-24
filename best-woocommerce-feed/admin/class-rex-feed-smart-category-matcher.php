<?php
/**
 * Rule-based matcher that suggests Google product categories for store categories.
 *
 * @package Rex_Product_Feed
 */

defined( 'ABSPATH' ) || exit;

/**
 * Suggests Google product categories for WooCommerce product categories.
 *
 * Works with rules only (no ML, no remote calls): text normalization, a synonym map,
 * an inverted token index over the taxonomy list file and a weighted score.
 * Store text and taxonomy text go through the same normalizer, so plural forms,
 * spelling variants and synonyms meet in the middle.
 *
 * @since 7.14.4
 */
class Rex_Feed_Smart_Category_Matcher {

	/**
	 * Index format version. Bump when normalization or the index shape changes.
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const INDEX_VERSION = '4';

	/**
	 * Uploads sub-folder that holds the cached index files (shared with the localized taxonomy files).
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const CACHE_DIR = 'wpfm-smart-category';

	/**
	 * Transient key prefix of the index cache used by earlier builds. Still cleared, no longer written.
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const TRANSIENT_PREFIX = 'wpfm_smart_cat_index_';

	/**
	 * Weight of the leaf token overlap.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const WEIGHT_LEAF = 0.55;

	/**
	 * Bonus when the normalized name equals the normalized leaf.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const WEIGHT_EXACT = 0.25;

	/**
	 * Weight of the overlap between parent category names and the taxonomy path.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const WEIGHT_CONTEXT = 0.15;

	/**
	 * Weight of the string similarity between name and leaf.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const WEIGHT_SIMILARITY = 0.05;

	/**
	 * Score bonus per taxonomy level, so specific nodes beat generic parents on ties.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const DEPTH_BONUS = 0.004;

	/**
	 * Token weight for the head noun (last word of each name part).
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const HEAD_TOKEN_WEIGHT = 2.0;

	/**
	 * Token weight for weak words such as "accessory".
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const WEAK_TOKEN_WEIGHT = 0.3;

	/**
	 * Minimum score for High confidence.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const THRESHOLD_HIGH = 0.75;

	/**
	 * Minimum score for Medium confidence.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const THRESHOLD_MEDIUM = 0.5;

	/**
	 * Minimum score for Low confidence. Nothing below this is suggested.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const THRESHOLD_LOW = 0.35;

	/**
	 * Maximum suggestions returned per store category.
	 *
	 * @since 7.14.4
	 * @var int
	 */
	const MAX_SUGGESTIONS = 3;

	/**
	 * Share of a name token's weight credited when the token is found in the taxonomy
	 * path but not in the leaf ("Sports & Outdoors" → Sporting Goods > Outdoor Recreation).
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const PATH_TOKEN_CREDIT = 0.5;

	/**
	 * Highest score a fallback suggestion may carry, so it always shows as Low confidence.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const FALLBACK_MAX_SCORE = 0.45;

	/**
	 * Penalty when a main word of the name is only a describing word in the leaf
	 * ("Tablets" → "Tablet Tripods", "Pans" → "Pan Lids", "Coffee" → "Coffee Cakes").
	 * Such a leaf also stays below Medium, so it is never bulk-filled.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const HEAD_MODIFIER_PENALTY = 0.12;

	/**
	 * Leaf heads that name a kind of category rather than another product ("Gift Giving",
	 * "Dog Supplies", "Sporting Goods", "Hair Care"). They never trigger HEAD_MODIFIER_PENALTY.
	 *
	 * @since 7.14.4
	 * @var string[]
	 */
	const CATEGORY_HEADS = array( 'giving', 'supply', 'good', 'care', 'equipment', 'product', 'item', 'gear', 'recreation', 'activity' );

	/**
	 * Words that make the Google "Mature" branch eligible. Without one of them in the
	 * category name or its parents, Mature entries are never suggested (Merchant Center
	 * policy risk on ordinary stores).
	 *
	 * @since 7.14.4
	 * @var string[]
	 */
	const ADULT_WORDS = array( 'erotic', 'erotica', 'sex', 'fetish', 'bdsm', 'xxx' );

	/**
	 * Score bonus (times product support) when product titles agree with a name match.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const WEIGHT_PRODUCT_AGREE = 0.15;

	/**
	 * Highest score of a suggestion that comes from product titles only (also kept below Medium).
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const PRODUCT_ONLY_MAX = 0.6;

	/**
	 * Least product support for a product-only suggestion.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const PRODUCT_MIN_SUPPORT = 0.25;

	/**
	 * Most product titles read per category.
	 *
	 * @since 7.14.4
	 * @var int
	 */
	const PRODUCT_MAX_TITLES = 30;

	/**
	 * Most frequent title words matched per category.
	 *
	 * @since 7.14.4
	 * @var int
	 */
	const PRODUCT_MAX_TOKENS = 4;

	/**
	 * Candidates kept per match before merging name and product scores.
	 *
	 * @since 7.14.4
	 * @var int
	 */
	const POOL_SIZE = 10;

	/**
	 * Title words that describe variants, not product types. They never vote.
	 *
	 * @since 7.14.4
	 * @var string[]
	 */
	const TITLE_NOISE_WORDS = array(
		'black',
		'white',
		'red',
		'blue',
		'green',
		'yellow',
		'pink',
		'purple',
		'orange',
		'grey',
		'gray',
		'brown',
		'beige',
		'gold',
		'silver',
		'small',
		'medium',
		'large',
		'xl',
		'xxl',
		'xs',
		'size',
		'pack',
		'piece',
		'pc',
		'pcs',
		'inch',
		'cm',
		'mm',
		'ml',
		'kg',
		'gift',
		'premium',
		'classic',
		'original',
		'edition',
		'style',
		'color',
		'colour',
	);

	/**
	 * Marketing and catch-all words in other languages (accents removed). Such a category gets no suggestion.
	 *
	 * @since 7.14.4
	 * @var string[]
	 */
	const NOISE_WORDS_INTL = array(
		'angebot',
		'angebote',
		'aktion',
		'rabatt',
		'neuheiten',
		'unkategorisiert',
		'soldes',
		'promo',
		'promos',
		'promotion',
		'promotions',
		'nouveautes',
		'oferta',
		'ofertas',
		'rebajas',
		'novedades',
		'promocion',
		'promociones',
		'offerte',
		'saldi',
		'sconti',
		'novita',
		'aanbieding',
		'aanbiedingen',
		'uitverkoop',
		'korting',
		'tilbud',
		'udsalg',
		'wyprzedaz',
		'promocja',
		'promocao',
		'promocoes',
		'liquidacao',
		'indirim',
		'kampanya',
		'uncategorised',
	);

	/**
	 * Articles, prepositions and audience words of major European languages (accents removed),
	 * ignored when matching against a localized taxonomy.
	 *
	 * @since 7.14.4
	 * @var string[]
	 */
	const LOCAL_STOPWORDS = array(
		// German.
		'und',
		'fur',
		'der',
		'die',
		'das',
		'den',
		'dem',
		'des',
		'mit',
		'von',
		'zum',
		'zur',
		'im',
		'am',
		'damen',
		'herren',
		'kinder',
		'frauen',
		'manner',
		'madchen',
		'jungen',
		'unisex',
		// French.
		'et',
		'de',
		'des',
		'du',
		'la',
		'le',
		'les',
		'pour',
		'avec',
		'en',
		'au',
		'aux',
		'femme',
		'femmes',
		'homme',
		'hommes',
		'enfant',
		'enfants',
		'fille',
		'filles',
		'garcon',
		'garcons',
		// Spanish and Portuguese.
		'y',
		'del',
		'el',
		'los',
		'las',
		'para',
		'con',
		'do',
		'da',
		'dos',
		'das',
		'com',
		'mujer',
		'mujeres',
		'hombre',
		'hombres',
		'nino',
		'ninos',
		'nina',
		'ninas',
		'mulher',
		'homem',
		'criancas',
		'feminino',
		'masculino',
		// Italian.
		'di',
		'della',
		'il',
		'lo',
		'gli',
		'per',
		'donna',
		'donne',
		'uomo',
		'uomini',
		'bambini',
		'bambino',
		'bambina',
		// Dutch and Scandinavian.
		'van',
		'het',
		'voor',
		'met',
		'dames',
		'heren',
		'kinderen',
		'och',
		'og',
		'for',
		'med',
		'dam',
		'herr',
		'barn',
		'damer',
		'herrer',
	);

	/**
	 * Audience words that still point at a taxonomy branch when they are the whole name
	 * and there is no parent category to inherit from ("Kids" → Baby & Toddler).
	 * Suggestions from this map are always Low confidence.
	 *
	 * @since 7.14.4
	 * @var array<string,string>
	 */
	const AUDIENCE_FALLBACK = array(
		'kid'  => 'baby toddler',
		'baby' => 'baby toddler',
	);

	/**
	 * Words ignored for matching.
	 *
	 * Gender and audience words are here because Google's taxonomy does not split by them.
	 * Values are already in normalized (singular) form.
	 *
	 * @since 7.14.4
	 * @var string[]
	 */
	const STOPWORDS = array(
		'a',
		'an',
		'and',
		'by',
		'for',
		'from',
		'in',
		'of',
		'on',
		'or',
		'the',
		'to',
		'with',
		'man',
		'men',
		'woman',
		'women',
		'kid',
		'idea',
		'him',
		'her',
		'his',
		'unisex',
		'sale',
		'new',
		'other',
		'misc',
		'more',
		'all',
		'shop',
		'item',
		'product',
	);

	/**
	 * Words that mark a marketing or catch-all category. Such a category gets no suggestion.
	 *
	 * @since 7.14.4
	 * @var string[]
	 */
	const NOISE_WORDS = array(
		'sale',
		'clearance',
		'arrival',
		'seller',
		'bestseller',
		'deal',
		'offer',
		'misc',
		'miscellaneous',
		'uncategorized',
		'featured',
		'trending',
		'discount',
	);

	/**
	 * Words that carry little meaning on their own; they get a reduced token weight.
	 *
	 * @since 7.14.4
	 * @var string[]
	 */
	const WEAK_WORDS = array( 'accessory', 'supply', 'part', 'set', 'equipment' );

	/**
	 * Words whose trailing "s" must not be stripped.
	 *
	 * @since 7.14.4
	 * @var string[]
	 */
	const PLURAL_EXCEPTIONS = array(
		'news',
		'series',
		'species',
		'gas',
		'lens',
		'chess',
		'canvas',
		'atlas',
		'bias',
		'plus',
		'yes',
		'christmas',
		'tennis',
		'billiards',
		'scissors',
		'electronics',
		'cosmetics',
		'athletics',
		'aerobics',
		'mathematics',
		'physics',
		'pilates',
		'dvds',
	);

	/**
	 * Irregular plural to singular forms.
	 *
	 * @since 7.14.4
	 * @var array<string,string>
	 */
	const IRREGULAR_PLURALS = array(
		'mice'     => 'mouse',
		'knives'   => 'knife',
		'shelves'  => 'shelf',
		'leaves'   => 'leaf',
		'wolves'   => 'wolf',
		'scarves'  => 'scarf',
		'children' => 'child',
		'feet'     => 'foot',
		'teeth'    => 'tooth',
		'geese'    => 'goose',
		'shoes'    => 'shoe',
		'dvds'     => 'dvd',
		'kids'     => 'kid',
		'mens'     => 'men',
		'womens'   => 'women',
	);

	/**
	 * Path of the taxonomy list file (`<id> - A > B > C` per line).
	 *
	 * @since 7.14.4
	 * @var string
	 */
	private $list_file;

	/**
	 * Optional localized taxonomy file with the same IDs (Google `taxonomy-with-ids.<locale>.txt`).
	 *
	 * @since 7.14.4
	 * @var string
	 */
	private $localized_file = '';

	/**
	 * Confidence thresholds in use (`high`, `medium`, `low`).
	 *
	 * @since 7.14.4
	 * @var array<string,float>
	 */
	private $thresholds;

	/**
	 * Built indexes keyed by language (`en`, `local`), loaded lazily.
	 *
	 * @since 7.14.4
	 * @var array<string,array<string,mixed>>
	 */
	private $indexes = array();

	/**
	 * Normalized synonym map (phrase => replacement), longest phrase first.
	 *
	 * @since 7.14.4
	 * @var array<string,string>|null
	 */
	private $synonyms = null;

	/**
	 * Regex that matches any synonym phrase on word boundaries.
	 *
	 * @since 7.14.4
	 * @var string|null
	 */
	private $synonym_pattern = null;

	/**
	 * Values held below Medium by the last score() calls (reset per name match), so
	 * product votes cannot lift them back into bulk-fill range.
	 *
	 * @since 7.14.4
	 * @var array<string,bool>
	 */
	private $capped = array();

	/**
	 * Set up the matcher.
	 *
	 * @since 7.14.4
	 *
	 * @param string $list_file Taxonomy list file. Defaults to the bundled Google list.
	 * @param array  $args      Optional. `localized_file` (string) a localized taxonomy file with the same IDs;
	 *                          `thresholds` (array) `high` / `medium` overrides, see confidence().
	 */
	public function __construct( string $list_file = '', array $args = array() ) {
		$this->list_file = '' !== $list_file ? $list_file : WPFM_PLUGIN_DIR_PATH . 'admin/partials/google_category_list.txt';

		if ( ! empty( $args['localized_file'] ) && is_string( $args['localized_file'] ) ) {
			$this->localized_file = $args['localized_file'];
		}

		$this->thresholds = array(
			'high'   => self::THRESHOLD_HIGH,
			'medium' => self::THRESHOLD_MEDIUM,
			'low'    => self::THRESHOLD_LOW,
		);
		if ( ! empty( $args['thresholds'] ) && is_array( $args['thresholds'] ) ) {
			foreach ( array( 'high', 'medium' ) as $key ) {
				if ( isset( $args['thresholds'][ $key ] ) && is_numeric( $args['thresholds'][ $key ] ) ) {
					$this->thresholds[ $key ] = (float) $args['thresholds'][ $key ];
				}
			}
			// Keep the labels ordered and sane whatever was passed.
			$this->thresholds['medium'] = min( 0.9, max( self::THRESHOLD_LOW + 0.05, $this->thresholds['medium'] ) );
			$this->thresholds['high']   = min( 0.97, max( $this->thresholds['medium'] + 0.05, $this->thresholds['high'] ) );
		}
	}

	/**
	 * Suggest taxonomy entries for a list of store category terms.
	 *
	 * Ancestor names are resolved from the given terms, so pass the whole tree
	 * (for example every `product_cat` term) to get parent context.
	 *
	 * @since 7.14.4
	 *
	 * @param WP_Term[]|object[]  $terms  Terms with `term_id`, `name` and `parent`.
	 * @param array<int,string[]> $titles Optional. Product titles keyed by term ID, used as votes.
	 * @return array<int,array<int,array{value:string,score:float,confidence:string}>> Suggestions keyed by term ID.
	 *                                                                                Terms without a suggestion are omitted.
	 */
	public function suggest_for_terms( array $terms, array $titles = array() ): array {
		$by_id = array();
		foreach ( $terms as $term ) {
			if ( is_object( $term ) && isset( $term->term_id, $term->name ) ) {
				$by_id[ (int) $term->term_id ] = $term;
			}
		}

		$suggestions = array();
		foreach ( $by_id as $term_id => $term ) {
			$result = $this->suggest(
				(string) $term->name,
				$this->ancestor_names( $term, $by_id ),
				isset( $titles[ $term_id ] ) && is_array( $titles[ $term_id ] ) ? $titles[ $term_id ] : array()
			);
			if ( ! empty( $result ) ) {
				$suggestions[ $term_id ] = $result;
			}
		}

		/**
		 * Filter smart category suggestions before they are returned.
		 *
		 * @since 7.14.4
		 *
		 * @param array<int,array<int,array{value:string,score:float,confidence:string}>> $suggestions Suggestions keyed by term ID.
		 * @param array<int,object>                                                       $terms       Input terms keyed by term ID.
		 */
		$filtered = apply_filters( 'wpfm_smart_category_suggestions', $suggestions, $by_id );

		return is_array( $filtered ) ? $filtered : $suggestions;
	}

	/**
	 * Ancestor names of a term, root first.
	 *
	 * @since 7.14.4
	 *
	 * @param object            $term  Term with `parent`.
	 * @param array<int,object> $by_id All terms keyed by term ID.
	 * @return string[]
	 */
	public function ancestor_names( $term, array $by_id ): array {
		$ancestors = array();
		$parent    = isset( $term->parent ) ? (int) $term->parent : 0;
		$guard     = 0;
		while ( $parent && isset( $by_id[ $parent ] ) && $guard < 20 ) {
			array_unshift( $ancestors, (string) $by_id[ $parent ]->name );
			$parent = isset( $by_id[ $parent ]->parent ) ? (int) $by_id[ $parent ]->parent : 0;
			++$guard;
		}
		return $ancestors;
	}

	/**
	 * Suggest taxonomy entries for one store category.
	 *
	 * Matches against the English taxonomy and, when set, the localized one; both
	 * return the English value of the same ID, so results merge by value.
	 *
	 * @since 7.14.4
	 *
	 * @param string   $name      Store category name (may be HTML-encoded).
	 * @param string[] $ancestors Ancestor names, root first.
	 * @param string[] $titles    Optional. Titles of products in the category, used as votes.
	 * @return array<int,array{value:string,score:float,confidence:string}> Up to MAX_SUGGESTIONS entries, best first.
	 */
	public function suggest( string $name, array $ancestors = array(), array $titles = array() ): array {
		if ( $this->is_noise_name( $name ) ) {
			return array();
		}

		$titles = array_slice( array_values( array_filter( array_map( 'strval', $titles ), 'strlen' ) ), 0, self::PRODUCT_MAX_TITLES );

		$best = array();
		foreach ( $this->languages() as $lang ) {
			foreach ( $this->suggest_in( $lang, $name, $ancestors, $titles ) as $value => $score ) {
				if ( ! isset( $best[ $value ] ) || $score > $best[ $value ] ) {
					$best[ $value ] = $score;
				}
			}
		}

		return $this->finalize( $best );
	}

	/**
	 * Canonical taxonomy value (`<id> - A > B`) for a saved value, or an empty string.
	 *
	 * Only values whose ID exists in the taxonomy are accepted; the path text is
	 * taken from the list, so old or hand-edited paths still resolve.
	 *
	 * @since 7.14.4
	 *
	 * @param string $value Saved value.
	 * @return string
	 */
	public function canonical_value( string $value ): string {
		if ( ! preg_match( '/^\s*(\d+)\s*-/', $value, $match ) ) {
			return '';
		}
		$lines = self::list_lines( $this->list_file );
		$id    = (int) $match[1];
		return isset( $lines[ $id ] ) ? $lines[ $id ] : '';
	}

	/**
	 * Lines of a taxonomy list file keyed by category ID (`<id> - A > B`), read once per request.
	 *
	 * A light lookup (about 1 MB for the Google list) for checking saved values, so saving a
	 * map never has to load or build the search index.
	 *
	 * @since 7.14.4
	 *
	 * @param string $file Taxonomy list file.
	 * @return array<int,string>
	 */
	private static function list_lines( string $file ): array {
		static $cache = array();
		if ( isset( $cache[ $file ] ) ) {
			return $cache[ $file ];
		}

		$lines = array();
		$raw   = is_readable( $file ) ? @file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) : false; // phpcs:ignore WordPress.PHP.NoSilencedErrors
		foreach ( is_array( $raw ) ? $raw : array() as $line ) {
			$line = trim( (string) $line );
			if ( preg_match( '/^(\d+)\s+-\s+\S/', $line, $match ) ) {
				$lines[ (int) $match[1] ] = $line;
			}
		}

		$cache[ $file ] = $lines;
		return $lines;
	}

	/**
	 * Confidence thresholds in use.
	 *
	 * @since 7.14.4
	 *
	 * @return array<string,float> `high`, `medium`, `low`.
	 */
	public function get_thresholds(): array {
		return $this->thresholds;
	}

	/**
	 * Languages to match in: always `en`, plus `local` when a readable localized file is set.
	 *
	 * @since 7.14.4
	 *
	 * @return string[]
	 */
	private function languages(): array {
		return '' !== $this->localized_file && is_readable( $this->localized_file ) ? array( 'en', 'local' ) : array( 'en' );
	}

	/**
	 * Sort scored values and keep the best ones.
	 *
	 * @since 7.14.4
	 *
	 * @param array<string,float> $scored Score per value.
	 * @return array<int,array{value:string,score:float,confidence:string}>
	 */
	private function finalize( array $scored ): array {
		$scored = array_filter(
			$scored,
			function ( $score ) {
				return $score >= $this->thresholds['low'];
			}
		);
		uksort(
			$scored,
			static function ( $a, $b ) use ( $scored ) {
				if ( $scored[ $a ] !== $scored[ $b ] ) {
					return $scored[ $b ] <=> $scored[ $a ];
				}
				// Shorter path first, then by text, so the order is the same on every PHP version.
				return array( strlen( (string) $a ), (string) $a ) <=> array( strlen( (string) $b ), (string) $b );
			}
		);

		$results = array();
		foreach ( array_slice( $scored, 0, self::MAX_SUGGESTIONS, true ) as $value => $score ) {
			$results[] = array(
				'value'      => (string) $value,
				'score'      => round( min( 1.0, $score ), 3 ),
				'confidence' => $this->confidence( $score ),
			);
		}
		return $results;
	}

	/**
	 * Score candidates for one store category in one language.
	 *
	 * @since 7.14.4
	 *
	 * @param string   $lang      `en` or `local`.
	 * @param string   $name      Store category name.
	 * @param string[] $ancestors Ancestor names, root first.
	 * @param string[] $titles    Product titles.
	 * @return array<string,float> Score per value (unsorted, may include values below the minimum).
	 */
	private function suggest_in( string $lang, string $name, array $ancestors, array $titles ): array {
		$raw_tokens = $this->tokenize( $name, false, $lang );

		// Meaningful ancestors, root first. Noise ancestors ("Sale") add no context.
		$levels = array();
		foreach ( $ancestors as $ancestor ) {
			$ancestor = (string) $ancestor;
			$tokens   = $this->tokenize( $ancestor, true, $lang );
			if ( ! empty( $tokens ) && ! $this->is_noise_name( $ancestor ) ) {
				$levels[] = array(
					'name'   => $ancestor,
					'tokens' => $tokens,
				);
			}
		}

		$name_tokens = $this->tokenize( $name, true, $lang );
		$fallback    = false;

		// A name made only of audience words ("Men", "For Her") stands for its nearest meaningful parent.
		if ( empty( $name_tokens ) && ! empty( $levels ) ) {
			$parent      = array_pop( $levels );
			$name        = $parent['name'];
			$name_tokens = $parent['tokens'];
		}

		// No parent either: some audience words still point at a branch ("Kids" → Baby & Toddler).
		if ( empty( $name_tokens ) && 'en' === $lang ) {
			foreach ( $raw_tokens as $token ) {
				if ( isset( self::AUDIENCE_FALLBACK[ $token ] ) ) {
					$name        = self::AUDIENCE_FALLBACK[ $token ];
					$name_tokens = $this->tokenize( $name );
					$fallback    = true;
					break;
				}
			}
		}

		$scores       = array();
		$this->capped = array();
		if ( ! empty( $name_tokens ) ) {
			$scores = $this->match( $lang, $name, $name_tokens, $levels );

			// Nothing for the name itself ("Fiction" under "Books"): inherit the nearest parent's match.
			$parents = $levels;
			while ( empty( $scores ) && ! empty( $parents ) ) {
				$parent   = array_pop( $parents );
				$scores   = $this->match( $lang, $parent['name'], $parent['tokens'], $parents );
				$fallback = true;
			}

			if ( $fallback ) {
				// Always Low, even when calibration moved the Medium threshold down.
				$cap = min( self::FALLBACK_MAX_SCORE, $this->thresholds['medium'] - 0.01 );
				foreach ( $scores as $value => $score ) {
					$scores[ $value ] = min( $score, $cap );
				}
			}
		}

		if ( count( $titles ) < 2 ) {
			return $scores;
		}

		// Product titles vote. The category name (when it has meaning) is context for them.
		$context = $levels;
		if ( ! empty( $name_tokens ) ) {
			$context[] = array(
				'name'   => $name,
				'tokens' => $name_tokens,
			);
		}
		$name_capped = $this->capped;
		$votes       = $this->product_votes( $lang, $titles, $context );
		$below_mid   = $this->thresholds['medium'] - 0.01;

		// A confident name match stays on top: products confirm it or reorder the rest,
		// but never push a broader or different category above it.
		$name_top   = empty( $scores ) ? '' : (string) array_key_first( $scores );
		$name_score = '' === $name_top ? 0.0 : $scores[ $name_top ];
		$ceiling    = $name_score >= $this->thresholds['medium'] ? $name_score - 0.001 : 1.0;

		foreach ( $votes as $value => $support ) {
			if ( isset( $scores[ $value ] ) ) {
				// Name and products agree.
				$boosted          = $scores[ $value ] + self::WEIGHT_PRODUCT_AGREE * $support;
				$scores[ $value ] = (string) $value === $name_top ? $boosted : max( $scores[ $value ], min( $boosted, $ceiling ) );
				if ( isset( $name_capped[ $value ] ) ) {
					$scores[ $value ] = min( $scores[ $value ], $below_mid );
				}
			} elseif ( $support >= self::PRODUCT_MIN_SUPPORT ) {
				// Products only: single title words are too noisy to bulk-fill, so Low at most.
				$scores[ $value ] = min( self::PRODUCT_ONLY_MAX, self::THRESHOLD_LOW + 0.3 * $support, $ceiling, $below_mid );
			}
		}

		return $scores;
	}

	/**
	 * Votes from product titles: frequent title words are matched on their own, weighted
	 * by how many titles contain them.
	 *
	 * @since 7.14.4
	 *
	 * @param string                                        $lang    `en` or `local`.
	 * @param string[]                                      $titles  Product titles (2 or more).
	 * @param array<int,array{name:string,tokens:string[]}> $context Category name and ancestors, root first.
	 * @return array<string,float> Support per value, 0–1.
	 */
	private function product_votes( string $lang, array $titles, array $context ): array {
		$index = $this->get_index( $lang );
		if ( empty( $index['tokens'] ) ) {
			return array();
		}

		$df = array();
		foreach ( $titles as $title ) {
			foreach ( $this->tokenize( $title, true, $lang ) as $token ) {
				if ( isset( $index['tokens'][ $token ] ) && ! in_array( $token, self::TITLE_NOISE_WORDS, true ) && ! is_numeric( $token ) ) {
					$df[ $token ] = ( $df[ $token ] ?? 0 ) + 1;
				}
			}
		}

		$total  = count( $titles );
		$min_df = max( 2, (int) ceil( 0.2 * $total ) );
		$df     = array_filter(
			$df,
			static function ( $count ) use ( $min_df ) {
				return $count >= $min_df;
			}
		);
		if ( empty( $df ) ) {
			return array();
		}
		uksort(
			$df,
			static function ( $a, $b ) use ( $df ) {
				return array( $df[ $b ], (string) $a ) <=> array( $df[ $a ], (string) $b );
			}
		);
		$df = array_slice( $df, 0, self::PRODUCT_MAX_TOKENS, true );

		$votes = array();
		foreach ( $df as $token => $count ) {
			$share = $count / $total;
			// The best match of a word votes fully, the runner-up at half; the rest do not vote.
			$rank = 0;
			foreach ( $this->match( $lang, (string) $token, array( (string) $token ), $context, 2 ) as $value => $score ) {
				$votes[ $value ] = min( 1.0, ( $votes[ $value ] ?? 0.0 ) + ( 0 === $rank ? 1.0 : 0.5 ) * $share * $score );
				++$rank;
			}
		}

		return $votes;
	}

	/**
	 * Score taxonomy entries for a name.
	 *
	 * @since 7.14.4
	 *
	 * @param string                                        $lang        `en` or `local`.
	 * @param string                                        $name        Raw name (used to find head nouns).
	 * @param string[]                                      $name_tokens Normalized name tokens.
	 * @param array<int,array{name:string,tokens:string[]}> $levels      Meaningful ancestors, root first.
	 * @param int                                           $limit       Most values returned.
	 * @return array<string,float> Score per value, best first, all at or above the minimum score.
	 */
	private function match( string $lang, string $name, array $name_tokens, array $levels, int $limit = self::POOL_SIZE ): array {
		$context_tokens = array();
		foreach ( $levels as $level ) {
			$context_tokens = array_merge( $context_tokens, $level['tokens'] );
		}
		$context_tokens = array_values( array_diff( array_unique( $context_tokens ), $name_tokens ) );

		$index = $this->get_index( $lang );
		if ( empty( $index['entries'] ) ) {
			return array();
		}

		$candidates = array();
		foreach ( array_unique( $name_tokens ) as $token ) {
			if ( isset( $index['tokens'][ $token ] ) ) {
				foreach ( $index['tokens'][ $token ] as $entry_index ) {
					$candidates[ $entry_index ] = true;
				}
			}
		}
		if ( empty( $candidates ) ) {
			return array();
		}

		$name_weights = $this->token_weights( $name, $name_tokens, $lang );
		$name_string  = implode( ' ', $name_tokens );
		$name_set     = array_fill_keys( $name_tokens, true );
		$known_set    = $name_set + array_fill_keys( $context_tokens, true );

		$scored = array();
		foreach ( array_keys( $candidates ) as $entry_index ) {
			$entry = $index['entries'][ $entry_index ];
			$score = $this->score( $entry, $name_weights, $name_set, $known_set, $context_tokens, $name_string );
			if ( $score >= $this->thresholds['low'] ) {
				$scored[ $entry_index ] = $score;
			}
		}

		uksort(
			$scored,
			static function ( $a, $b ) use ( $scored, $index ) {
				if ( $scored[ $a ] !== $scored[ $b ] ) {
					return $scored[ $b ] <=> $scored[ $a ];
				}
				// Shorter path first, then list order, so the order is the same on every PHP version.
				return array( strlen( $index['entries'][ $a ]['v'] ), $a ) <=> array( strlen( $index['entries'][ $b ]['v'] ), $b );
			}
		);

		$results = array();
		foreach ( array_slice( $scored, 0, $limit, true ) as $entry_index => $score ) {
			$value = (string) $index['entries'][ $entry_index ]['v'];
			if ( ! isset( $results[ $value ] ) ) {
				$results[ $value ] = $score;
			}
		}

		return $results;
	}

	/**
	 * Map a score to a confidence label.
	 *
	 * @since 7.14.4
	 *
	 * @param float $score Score between 0 and 1.
	 * @return string `high`, `medium`, `low` or an empty string below the minimum score.
	 */
	public function confidence( float $score ): string {
		if ( $score >= $this->thresholds['high'] ) {
			return 'high';
		}
		if ( $score >= $this->thresholds['medium'] ) {
			return 'medium';
		}
		if ( $score >= $this->thresholds['low'] ) {
			return 'low';
		}
		return '';
	}

	/**
	 * Normalize text into match tokens.
	 *
	 * Decodes HTML entities, lowercases, strips punctuation, singularizes, applies
	 * synonyms and (optionally) removes stopwords. Tokens keep their order.
	 *
	 * @since 7.14.4
	 *
	 * For `local` (a non-English taxonomy) the English rules are skipped: no singular
	 * forms, synonyms or stopwords, and accents are removed so both sides meet.
	 *
	 * @param string $text           Raw text.
	 * @param bool   $drop_stopwords Whether to remove stopwords.
	 * @param string $lang           `en` (default) or `local`.
	 * @return string[] Tokens.
	 */
	public function tokenize( string $text, bool $drop_stopwords = true, string $lang = 'en' ): array {
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$text = function_exists( 'mb_strtolower' ) ? mb_strtolower( $text, 'UTF-8' ) : strtolower( $text );

		if ( 'en' !== $lang ) {
			return $this->tokenize_neutral( $text, $drop_stopwords );
		}
		$text = str_replace( array( "'", '’' ), '', $text );
		// Glue single-letter prefixes so "E-books" and "T-shirts" keep their meaning.
		$text = (string) preg_replace( '/(?<![\p{L}\p{N}])(\p{L})-(?=\p{L})/u', '$1', $text );
		$text = (string) preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $text );

		$words = array();
		foreach ( preg_split( '/\s+/', trim( $text ) ) as $word ) {
			if ( '' !== $word ) {
				$words[] = $this->singularize( $word );
			}
		}
		if ( empty( $words ) ) {
			return array();
		}

		$this->load_synonyms();
		$joined = ' ' . implode( ' ', $words ) . ' ';
		if ( $this->synonym_pattern ) {
			$synonyms = $this->synonyms;
			$joined   = (string) preg_replace_callback(
				$this->synonym_pattern,
				static function ( $found ) use ( $synonyms ) {
					return ' ' . $synonyms[ trim( $found[0] ) ] . ' ';
				},
				$joined
			);
		}

		$tokens = array();
		foreach ( preg_split( '/\s+/', trim( $joined ) ) as $token ) {
			if ( '' === $token || ( strlen( $token ) < 2 && ! is_numeric( $token ) ) ) {
				continue;
			}
			if ( $drop_stopwords && in_array( $token, self::STOPWORDS, true ) ) {
				continue;
			}
			$tokens[] = $token;
		}

		return array_values( array_unique( $tokens ) );
	}

	/**
	 * Language-neutral tokens: accents removed, split on non-letters, short words dropped.
	 *
	 * With `$normalize` common articles and audience words of major European languages are
	 * dropped and one plural-like ending is cut ("spielzeuge" → "spielzeug", "bucher" → "buch"),
	 * the same way on both sides. Without it the raw words are kept (for noise checks).
	 *
	 * @since 7.14.4
	 *
	 * @param string $text      Lowercase text.
	 * @param bool   $normalize Whether to drop stopwords and cut endings.
	 * @return string[]
	 */
	private function tokenize_neutral( string $text, bool $normalize = true ): array {
		if ( function_exists( 'remove_accents' ) ) {
			$text = remove_accents( $text );
		}
		$text = str_replace( array( "'", '’' ), '', $text );
		$text = (string) preg_replace( '/(?<![\p{L}\p{N}])(\p{L})-(?=\p{L})/u', '$1', $text );
		$text = (string) preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $text );

		$tokens = array();
		foreach ( preg_split( '/\s+/', trim( $text ) ) as $token ) {
			if ( '' === $token || ( $this->length( $token ) < 2 && ! is_numeric( $token ) ) ) {
				continue;
			}
			if ( $normalize ) {
				if ( in_array( $token, self::LOCAL_STOPWORDS, true ) ) {
					continue;
				}
				$token = $this->stem_neutral( $token );
			}
			$tokens[] = $token;
		}
		return array_values( array_unique( $tokens ) );
	}

	/**
	 * Cut one common plural or inflection ending, keeping at least 4 characters.
	 *
	 * @since 7.14.4
	 *
	 * @param string $token Accent-free lowercase word.
	 * @return string
	 */
	private function stem_neutral( string $token ): string {
		if ( is_numeric( $token ) || ! preg_match( '/^[a-z]+$/', $token ) ) {
			return $token;
		}
		foreach ( array( 'en', 'er', 'es', 'os', 'as', 'e', 's', 'a', 'i', 'o', 'n' ) as $suffix ) {
			$cut = strlen( $token ) - strlen( $suffix );
			if ( $cut >= 4 && substr( $token, $cut ) === $suffix ) {
				return substr( $token, 0, $cut );
			}
		}
		return $token;
	}

	/**
	 * Character length of a string.
	 *
	 * @since 7.14.4
	 *
	 * @param string $text Text.
	 * @return int
	 */
	private function length( string $text ): int {
		return function_exists( 'mb_strlen' ) ? (int) mb_strlen( $text, 'UTF-8' ) : strlen( $text );
	}

	/**
	 * Delete index transients written by earlier builds (2 MB+ each).
	 *
	 * @since 7.14.4
	 *
	 * @return void
	 */
	private static function delete_legacy_transients(): void {
		global $wpdb;
		if ( ! is_object( $wpdb ) ) {
			return;
		}

		$like = $wpdb->esc_like( '_transient_' . self::TRANSIENT_PREFIX ) . '%';
		$keys = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( (array) $keys as $key ) {
			delete_transient( substr( $key, strlen( '_transient_' ) ) );
		}
	}

	/**
	 * Uploads folder for the index cache, or empty when uploads are not available.
	 *
	 * @since 7.14.4
	 *
	 * @return string
	 */
	private static function cache_dir(): string {
		if ( ! function_exists( 'wp_upload_dir' ) ) {
			return '';
		}
		$uploads = wp_upload_dir( null, false );
		if ( ! is_array( $uploads ) || ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) ) {
			return '';
		}
		return rtrim( (string) $uploads['basedir'], '/\\' ) . '/' . self::CACHE_DIR;
	}

	/**
	 * Read a cached index file.
	 *
	 * @since 7.14.4
	 *
	 * @param string $path File path.
	 * @return array|null Index, or null when missing or invalid.
	 */
	private static function read_cache( string $path ): ?array {
		if ( '' === $path || ! is_readable( $path ) ) {
			return null;
		}
		$data = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		return is_array( $data ) && isset( $data['entries'], $data['tokens'], $data['ids'] ) && is_array( $data['entries'] ) ? $data : null;
	}

	/**
	 * Write an index to a cache file and remove older ones of the same language.
	 *
	 * The index is about 2 MB, too big for a transient (WordPress's query checks on a
	 * value that size log a PHP deprecation, and small `max_allowed_packet` hosts reject it).
	 * A failed write only means the index is rebuilt on the next request.
	 *
	 * @since 7.14.4
	 *
	 * @param string $path  File path.
	 * @param string $lang  `en` or `local`.
	 * @param array  $index Index.
	 * @return void
	 */
	private static function write_cache( string $path, string $lang, array $index ): void {
		$dir = dirname( $path );
		if ( ! wp_mkdir_p( $dir ) ) {
			return;
		}
		if ( ! file_exists( $dir . '/index.php' ) ) {
			@file_put_contents( $dir . '/index.php', "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
		}

		$json = wp_json_encode( $index );
		if ( ! is_string( $json ) || '' === $json ) {
			return;
		}
		$tmp = $path . '.' . uniqid( '', true ) . '.tmp';
		if ( false === @file_put_contents( $tmp, $json ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
			return;
		}
		if ( ! @rename( $tmp, $path ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
			wp_delete_file( $tmp );
			return;
		}

		foreach ( (array) glob( $dir . '/index-' . $lang . '-*.json' ) as $old ) {
			if ( is_string( $old ) && $old !== $path && is_file( $old ) ) {
				wp_delete_file( $old );
			}
		}
		self::delete_legacy_transients();
	}

	/**
	 * Score one taxonomy entry against a store category.
	 *
	 * @since 7.14.4
	 *
	 * @param array<string,mixed> $entry          Index entry.
	 * @param array<string,float> $name_weights   Name token weights.
	 * @param array<string,bool>  $name_set       Name tokens as a set.
	 * @param array<string,bool>  $known_set      Name and context tokens as a set.
	 * @param string[]            $context_tokens Ancestor tokens not in the name.
	 * @param string              $name_string    Normalized name.
	 * @return float Score, about 0–1 (may slightly exceed 1; capped when shown).
	 */
	private function score( array $entry, array $name_weights, array $name_set, array $known_set, array $context_tokens, string $name_string ): float {
		$leaf = $entry['l'];

		// Adult categories only for stores that name them.
		if ( 0 === strpos( (string) preg_replace( '/^\d+\s*-\s*/', '', (string) $entry['v'] ), 'Mature' ) && ! array_intersect( array_keys( $known_set ), self::ADULT_WORDS ) ) {
			return 0.0;
		}

		// How much of the name the leaf explains. A name word found higher up the path
		// ("Sports" in Sporting Goods > Outdoor Recreation) counts partly.
		$name_total = 0.0;
		$name_hit   = 0.0;
		$leaf_hit   = false;
		foreach ( $name_weights as $token => $weight ) {
			$name_total += $weight;
			if ( in_array( $token, $leaf, true ) ) {
				$name_hit += $weight;
				$leaf_hit  = true;
			} elseif ( in_array( $token, $entry['p'], true ) ) {
				$name_hit += self::PATH_TOKEN_CREDIT * $weight;
			}
		}
		$name_cover = $name_total > 0 ? $name_hit / $name_total : 0.0;
		if ( ! $leaf_hit ) {
			return 0.0;
		}

		// How much of the leaf is explained by the name or its parents. Google joins sibling
		// concepts with "&" and "," ("Mice & Trackballs"), so a fully explained part counts too.
		$leaf_cover = $this->cover( $leaf, $known_set );
		$part_cover = $leaf_cover;
		foreach ( $entry['lp'] as $part ) {
			$part_cover = max( $part_cover, $this->cover( $part, $known_set ) );
		}

		$score = self::WEIGHT_LEAF * ( 0.6 * $name_cover + 0.2 * $leaf_cover + 0.2 * $part_cover );

		if ( count( $leaf ) === count( $name_set ) && $name_cover >= 1.0 ) {
			$score += self::WEIGHT_EXACT;
		}

		if ( ! empty( $context_tokens ) ) {
			$path_hits = 0;
			foreach ( $context_tokens as $token ) {
				if ( in_array( $token, $entry['p'], true ) ) {
					++$path_hits;
				}
			}
			$score += self::WEIGHT_CONTEXT * ( $path_hits / count( $context_tokens ) );
		}

		// The leaf is about its part heads ("Tablet Tripods" is about tripods). A main word of
		// the name that only describes something else here points to a different product.
		$leaf_heads   = array_fill_keys( ! empty( $entry['h'] ) ? $entry['h'] : array( (string) end( $leaf ) ), true );
		$generic_leaf = (bool) array_intersect( array_keys( $leaf_heads ), self::CATEGORY_HEADS );
		$name_heads   = array();
		foreach ( $name_weights as $token => $weight ) {
			if ( $weight >= self::HEAD_TOKEN_WEIGHT ) {
				$name_heads[] = $token;
			}
		}
		$below_medium = false;
		// When the leaf's last word is one of the name's main words ("Crisps & Snacks" → "Snack Foods"),
		// the leaf is about the name's product.
		$about_name = in_array( (string) end( $leaf ), $name_heads, true );
		foreach ( $name_heads as $token ) {
			if ( ! $generic_leaf && ! $about_name && ! isset( $leaf_heads[ $token ] ) && in_array( $token, $leaf, true ) ) {
				$score       -= self::HEAD_MODIFIER_PENALTY;
				$below_medium = true;
				break;
			}
		}
		// None of the name's main words is in the leaf ("Video Games" → "Video"): not confident.
		if ( ! empty( $name_heads ) && ! array_intersect( $name_heads, $leaf ) ) {
			$below_medium = true;
		}

		$percent = 0.0;
		similar_text( $name_string, implode( ' ', $leaf ), $percent );
		$score += self::WEIGHT_SIMILARITY * ( $percent / 100 );

		// Prefer specific nodes only when the whole leaf is explained; otherwise prefer the general one.
		$score += ( $leaf_cover >= 1.0 ? 1 : -1 ) * self::DEPTH_BONUS * $entry['d'];

		// "Phones & Tablets": a category that covers only one of the name's products stays Low,
		// so it is never bulk-filled for the other one.
		// Also when another product is only higher up the path and the leaf is narrower
		// ("Pots & Pans" → "Stock Pots").
		if ( count( $name_heads ) > 1 && ( array_diff( $name_heads, $leaf, $entry['p'] ) || ( $leaf_cover < 1.0 && array_diff( $name_heads, $leaf ) ) ) ) {
			$below_medium = true;
		}
		if ( $below_medium ) {
			$score                       = min( $score, $this->thresholds['medium'] - 0.01 );
			$this->capped[ $entry['v'] ] = true;
		}

		// Not capped here, so ranking keeps the depth preference above 1.0; finalize() caps the shown score.
		return $score;
	}

	/**
	 * Weighted share of tokens found in a set. Weak words count less.
	 *
	 * @since 7.14.4
	 *
	 * @param string[]           $tokens Tokens to check.
	 * @param array<string,bool> $set    Known tokens.
	 * @return float Share between 0 and 1.
	 */
	private function cover( array $tokens, array $set ): float {
		$total = 0.0;
		$hit   = 0.0;
		foreach ( $tokens as $token ) {
			$weight = in_array( $token, self::WEAK_WORDS, true ) ? self::WEAK_TOKEN_WEIGHT : 1.0;
			$total += $weight;
			if ( isset( $set[ $token ] ) ) {
				$hit += $weight;
			}
		}
		return $total > 0 ? $hit / $total : 0.0;
	}

	/**
	 * Weight name tokens: head nouns count more, weak words less.
	 *
	 * The head noun is the last word of each part of the name, where parts are split on
	 * "&", "and", "," and "/" ("Coffee Mugs" → mugs; "Phones & Tablets" → phones, tablets).
	 *
	 * @since 7.14.4
	 *
	 * @param string   $name   Raw name.
	 * @param string[] $tokens Normalized name tokens.
	 * @param string   $lang   `en` or `local`.
	 * @return array<string,float> Weight per token.
	 */
	private function token_weights( string $name, array $tokens, string $lang = 'en' ): array {
		$heads = array();
		$name  = html_entity_decode( $name, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		foreach ( preg_split( '/\s*(?:&|,|\/|\band\b|\+)\s*/i', $name ) as $part ) {
			$part_tokens = $this->tokenize( $part, true, $lang );
			if ( ! empty( $part_tokens ) ) {
				$heads[ end( $part_tokens ) ] = true;
			}
		}

		$weights = array();
		foreach ( $tokens as $token ) {
			if ( in_array( $token, self::WEAK_WORDS, true ) ) {
				$weights[ $token ] = count( $tokens ) > 1 ? self::WEAK_TOKEN_WEIGHT : 1.0;
			} elseif ( isset( $heads[ $token ] ) ) {
				$weights[ $token ] = self::HEAD_TOKEN_WEIGHT;
			} else {
				$weights[ $token ] = 1.0;
			}
		}

		return $weights;
	}

	/**
	 * Whether the raw tokens describe a marketing or catch-all category.
	 *
	 * @since 7.14.4
	 *
	 * @param string[] $raw_tokens Tokens with stopwords kept.
	 * @return bool
	 */
	private function is_noise( array $raw_tokens ): bool {
		if ( empty( $raw_tokens ) ) {
			return true;
		}
		return (bool) array_intersect( $raw_tokens, self::NOISE_WORDS );
	}

	/**
	 * Whether a category name is a marketing or catch-all category, in English or another language.
	 *
	 * @since 7.14.4
	 *
	 * @param string $name Raw name.
	 * @return bool
	 */
	private function is_noise_name( string $name ): bool {
		if ( $this->is_noise( $this->tokenize( $name, false ) ) ) {
			return true;
		}
		return (bool) array_intersect( $this->tokenize( $name, false, 'local' ), self::NOISE_WORDS_INTL );
	}

	/**
	 * Reduce an English word to a simple singular form.
	 *
	 * Also maps a trailing "ie" to "y" so "hoodie" and "hoodies" meet.
	 *
	 * @since 7.14.4
	 *
	 * @param string $word Lowercase word.
	 * @return string
	 */
	private function singularize( string $word ): string {
		if ( isset( self::IRREGULAR_PLURALS[ $word ] ) ) {
			return self::IRREGULAR_PLURALS[ $word ];
		}
		if ( strlen( $word ) < 3 || is_numeric( $word ) || in_array( $word, self::PLURAL_EXCEPTIONS, true ) ) {
			return $word;
		}
		if ( preg_match( '/ies$/', $word ) && strlen( $word ) > 4 ) {
			return substr( $word, 0, -3 ) . 'y';
		}
		if ( preg_match( '/ie$/', $word ) && strlen( $word ) > 3 ) {
			return substr( $word, 0, -2 ) . 'y';
		}
		if ( preg_match( '/(?:ss|x|ch|sh|zz)es$/', $word ) ) {
			return substr( $word, 0, -2 );
		}
		if ( preg_match( '/(?:ss|us|is)$/', $word ) ) {
			return $word;
		}
		if ( 's' === substr( $word, -1 ) ) {
			return substr( $word, 0, -1 );
		}
		return $word;
	}

	/**
	 * Built-in synonyms. Keys and values are raw text; they are normalized on load.
	 *
	 * @since 7.14.4
	 *
	 * @return array<string,string>
	 */
	private function default_synonyms(): array {
		return array(
			// Apparel.
			'tee'               => 'shirt',
			'tshirt'            => 'shirt',
			't shirt'           => 'shirt',
			'blouse'            => 'shirt',
			'hoodie'            => 'top',
			'sweatshirt'        => 'top',
			'sweat'             => 'top',
			'jumper'            => 'sweater',
			'jean'              => 'pant',
			'trouser'           => 'pant',
			'legging'           => 'pant',
			'chino'             => 'pant',
			'knicker'           => 'underwear',
			'fashion accessory' => 'clothing accessory',
			'footwear'          => 'shoe',
			'sneaker'           => 'shoe',
			'trainer'           => 'shoe',
			'boot'              => 'shoe',
			'sandal'            => 'shoe',
			'flip flop'         => 'shoe',
			'heel'              => 'shoe',
			'slipper'           => 'shoe',
			'loafer'            => 'shoe',
			'cap'               => 'hat',
			'jewellery'         => 'jewelry',
			// Books. The head word stays last so "book" is the head noun.
			'cookbook'          => 'cooking book',
			'recipe book'       => 'cooking book',
			'novel'             => 'fiction book',
			'fiction'           => 'fiction book',
			'non fiction'       => 'nonfiction book',
			'nonfiction'        => 'nonfiction book',
			'biography'         => 'biography book',
			'paperback'         => 'print book',
			'hardcover'         => 'print book',
			'purse'             => 'handbag',
			// Electronics.
			'smartphone'        => 'mobile phone',
			'cell phone'        => 'mobile phone',
			'phone case'        => 'mobile phone case',
			'phone'             => 'mobile phone',
			'tablet'            => 'tablet computer',
			'notebook computer' => 'laptop',
			'tv'                => 'television',
			'earbud'            => 'headphone',
			'earphone'          => 'headphone',
			'gaming'            => 'video game',
			'console'           => 'video game console',
			'video game'        => 'video game software',
			'controller'        => 'game controller',
			// Home.
			'couch'             => 'sofa',
			'settee'            => 'sofa',
			'pan'               => 'cookware',
			'wall art'          => 'artwork',
			'lamp'              => 'lamp lighting',
			// Pets, baby.
			'nappy'             => 'diaper',
			'scratching post'   => 'cat furniture',
			'pushchair'         => 'stroller',
			'pram'              => 'stroller',
			// Beauty.
			'skincare'          => 'skin care',
			'moisturiser'       => 'moisturizer',
			'cleanser'          => 'facial cleanser',
			'perfume'           => 'perfume cologne',
			'fragrance'         => 'perfume cologne',
			// Sports.
			'sport'             => 'sporting',
			'bike'              => 'bicycle',
			'dumbbell'          => 'free weight',
			'kettlebell'        => 'free weight',
			'resistance band'   => 'exercise band',
			'yoga mat'          => 'yoga pilates mat',
			// Toys.
			'rc'                => 'remote control',
			'drone'             => 'remote control',
			'lego'              => 'construction set toy',
			'building block'    => 'construction set toy',
			// Food.
			'drink'             => 'beverage',
			'crisp'             => 'chip snack',
			'snack'             => 'snack food',
			'chocolate'         => 'candy chocolate',
			'protein powder'    => 'vitamin supplement',
			'olive oil'         => 'cooking oil',
			'spice'             => 'herb spice',
			// Office, DIY, car.
			'organiser'         => 'organizer',
			'notebook'          => 'notebook notepad',
			'stationery'        => 'office supply',
			'power drill'       => 'drill',
			'plant pot'         => 'pot planter',
			'car'               => 'vehicle',
			'automotive'        => 'vehicle',
		);
	}

	/**
	 * Load, normalize and compile the synonym map.
	 *
	 * @since 7.14.4
	 *
	 * @return void
	 */
	private function load_synonyms(): void {
		if ( null !== $this->synonyms ) {
			return;
		}

		/**
		 * Filter the synonym map used by the smart category matcher.
		 *
		 * Keys are store words or phrases, values are the taxonomy words they should match.
		 * Both are normalized (lowercase, singular), so "Hoodies" and "hoodie" are the same key.
		 *
		 * @since 7.14.4
		 *
		 * @param array<string,string> $synonyms Phrase => replacement.
		 */
		$raw = apply_filters( 'wpfm_smart_category_synonyms', $this->default_synonyms() );

		$this->synonyms = array();
		foreach ( (array) $raw as $from => $to ) {
			if ( ! is_string( $from ) || ! is_string( $to ) ) {
				continue;
			}
			$from = $this->normalize_phrase( $from );
			$to   = $this->normalize_phrase( $to );
			if ( '' !== $from && '' !== $to && $from !== $to ) {
				$this->synonyms[ $from ] = $to;
			}
		}

		if ( empty( $this->synonyms ) ) {
			$this->synonym_pattern = null;
			return;
		}

		$keys = array_keys( $this->synonyms );
		usort(
			$keys,
			static function ( $a, $b ) {
				return array( strlen( $b ), $a ) <=> array( strlen( $a ), $b );
			}
		);
		$quoted                = array_map(
			static function ( $key ) {
				return preg_quote( $key, '/' );
			},
			$keys
		);
		$this->synonym_pattern = '/(?<= )(?:' . implode( '|', $quoted ) . ')(?= )/u';
	}

	/**
	 * Normalize a synonym phrase (lowercase, singular, single spaces) without applying synonyms.
	 *
	 * @since 7.14.4
	 *
	 * @param string $phrase Raw phrase.
	 * @return string
	 */
	private function normalize_phrase( string $phrase ): string {
		$phrase = strtolower( html_entity_decode( $phrase, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
		$phrase = (string) preg_replace( '/[^\p{L}\p{N}]+/u', ' ', str_replace( "'", '', $phrase ) );
		$words  = array();
		foreach ( preg_split( '/\s+/', trim( $phrase ) ) as $word ) {
			if ( '' !== $word ) {
				$words[] = $this->singularize( $word );
			}
		}
		return implode( ' ', $words );
	}

	/**
	 * Get a taxonomy index, from memory, the transient cache or a fresh build.
	 *
	 * @since 7.14.4
	 *
	 * @param string $lang `en` (bundled English list) or `local` (localized list).
	 * @return array{entries:array<int,array<string,mixed>>,tokens:array<string,int[]>,ids:array<int,int>}
	 */
	private function get_index( string $lang = 'en' ): array {
		$lang = 'en' === $lang ? 'en' : 'local';
		if ( isset( $this->indexes[ $lang ] ) ) {
			return $this->indexes[ $lang ];
		}

		$empty = array(
			'entries' => array(),
			'tokens'  => array(),
			'ids'     => array(),
		);
		$file  = 'en' === $lang ? $this->list_file : $this->localized_file;
		if ( '' === $file || ! is_readable( $file ) ) {
			$this->indexes[ $lang ] = $empty;
			return $empty;
		}

		$this->load_synonyms();
		// The key changes with the index format, the source file and (English) the synonyms,
		// or (localized) the English list the values come from.
		$extra = 'en' === $lang ? (string) wp_json_encode( $this->synonyms ) : $this->list_file . '|' . (int) @filemtime( $this->list_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		$hash  = substr( md5( self::INDEX_VERSION . '|' . $file . '|' . (int) filemtime( $file ) . '|' . $extra ), 0, 12 );
		$dir   = self::cache_dir();
		$path  = '' === $dir ? '' : $dir . '/index-' . $lang . '-' . $hash . '.json';

		$cached = self::read_cache( $path );
		if ( null !== $cached ) {
			$this->indexes[ $lang ] = $cached;
			return $cached;
		}

		$english = 'en' === $lang ? null : $this->get_index( 'en' );
		$index   = $this->build_index( $file, $lang, $english );
		if ( '' !== $path && ! empty( $index['entries'] ) ) {
			self::write_cache( $path, $lang, $index );
		}

		$this->indexes[ $lang ] = $index;
		return $index;
	}

	/**
	 * Build an index from a taxonomy list file.
	 *
	 * Entry keys are short to keep the cached transient small:
	 * `v` value as shown in autocomplete, `l` leaf tokens, `lp` tokens of each "&"/","-joined leaf part,
	 * `p` path tokens, `d` depth. A localized file is indexed with its own words but every
	 * entry keeps the English value of the same ID, so saved values stay in one format.
	 *
	 * @since 7.14.4
	 *
	 * @param string     $file    Taxonomy list file.
	 * @param string     $lang    `en` or `local`.
	 * @param array|null $english English index, required for `local`.
	 * @return array{entries:array<int,array<string,mixed>>,tokens:array<string,int[]>,ids:array<int,int>}
	 */
	private function build_index( string $file, string $lang, $english = null ): array {
		$index = array(
			'entries' => array(),
			'tokens'  => array(),
			'ids'     => array(),
		);

		if ( 'en' !== $lang && ( ! is_array( $english ) || empty( $english['ids'] ) ) ) {
			return $index;
		}

		$lines = @file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		if ( false === $lines ) {
			return $index;
		}

		foreach ( $lines as $line ) {
			$line = trim( (string) $line );
			if ( ! preg_match( '/^(\d+)\s+-\s+(.+)$/', $line, $match ) ) {
				continue;
			}
			$id = (int) $match[1];

			$value = $line;
			if ( 'en' !== $lang ) {
				if ( ! isset( $english['ids'][ $id ] ) ) {
					continue;
				}
				$value = $english['entries'][ $english['ids'][ $id ] ]['v'];
			}

			$parts = array_map( 'trim', explode( '>', $match[2] ) );
			$leaf  = $this->tokenize( (string) end( $parts ), true, $lang );
			if ( empty( $leaf ) ) {
				continue;
			}
			$path       = $this->tokenize( implode( ' ', $parts ), true, $lang );
			$leaf_parts = array();
			$heads      = array();
			foreach ( preg_split( '/\s*(?:&|,)\s*/', (string) end( $parts ) ) as $leaf_part ) {
				$part_tokens = $this->tokenize( $leaf_part, true, $lang );
				if ( empty( $part_tokens ) ) {
					continue;
				}
				$heads[] = (string) end( $part_tokens );
				if ( count( $part_tokens ) < count( $leaf ) ) {
					$leaf_parts[] = $part_tokens;
				}
			}

			$entry_index         = count( $index['entries'] );
			$index['entries'][]  = array(
				'v'  => $value,
				'l'  => $leaf,
				'lp' => $leaf_parts,
				'h'  => array_values( array_unique( $heads ) ),
				'p'  => $path,
				'd'  => count( $parts ),
			);
			$index['ids'][ $id ] = $entry_index;
			foreach ( $leaf as $token ) {
				$index['tokens'][ $token ][] = $entry_index;
			}
		}

		return $index;
	}
}
