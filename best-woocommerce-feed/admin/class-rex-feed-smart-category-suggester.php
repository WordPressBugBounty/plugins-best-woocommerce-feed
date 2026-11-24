<?php
/**
 * Smart category suggestions: combines the store's own earlier mappings, the rule
 * matcher, product titles, calibrated thresholds and a localized taxonomy.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin
 * @since      7.14.4
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Rex_Feed_Smart_Category_Suggester
 *
 * Every extra signal is optional: when one fails it is skipped, and when the whole
 * pipeline fails the plain matcher result is returned, so the Suggest button keeps working.
 *
 * Signals, strongest first:
 * 1. Memory: the same store category mapped in another saved map, or a category with the
 *    same name (and parent) mapped before, including maps deleted since (learned on save).
 * 2. Rules: the matcher (name, parents, synonyms) plus votes from product titles.
 *
 * @since 7.14.4
 */
class Rex_Feed_Smart_Category_Suggester {

	/**
	 * Option with values learned from saved maps: `{ key: { v: value, t: time } }`.
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const LEARNED_OPTION = 'wpfm_smart_category_learned';

	/**
	 * Most learned keys kept (oldest dropped first).
	 *
	 * @since 7.14.4
	 * @var int
	 */
	const LEARNED_MAX = 1000;

	/**
	 * Rank bonus of memory suggestions over rule suggestions with the same score.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const MEMORY_BONUS = 0.1;

	/**
	 * Option that stores the category maps.
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const MAP_OPTION = 'rex-wpfm-category-mapping';

	/**
	 * Product titles read per category.
	 *
	 * @since 7.14.4
	 * @var int
	 */
	const TITLES_PER_TERM = 20;

	/**
	 * Most categories whose product titles are read in one request.
	 *
	 * @since 7.14.4
	 * @var int
	 */
	const TITLE_TERMS_MAX = 500;

	/**
	 * Seconds spent reading product titles at most; categories after that use their name only.
	 *
	 * @since 7.14.4
	 * @var float
	 */
	const TITLE_TIME_BUDGET = 4.0;

	/**
	 * Categories per batched title query.
	 *
	 * @since 7.14.4
	 * @var int
	 */
	const TITLE_BATCH_SIZE = 100;

	/**
	 * Folder in uploads for downloaded localized taxonomy files.
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const TAXONOMY_DIR = 'wpfm-smart-category';

	/**
	 * WP-Cron hook that downloads a localized taxonomy file in the background.
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const FETCH_HOOK = 'wpfm_smart_category_fetch_taxonomy';

	/**
	 * Transient prefix that pauses downloads after a failure.
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const TAXONOMY_FAIL_PREFIX = 'wpfm_smart_cat_taxfail_';

	/**
	 * Age after which a downloaded taxonomy file is refreshed.
	 *
	 * @since 7.14.4
	 * @var int
	 */
	const TAXONOMY_MAX_AGE = 7776000; // 90 days.

	/**
	 * Google taxonomy locales that have a `taxonomy-with-ids.<locale>.txt` file.
	 *
	 * @since 7.14.4
	 * @var string[]
	 */
	const TAXONOMY_LOCALES = array( 'ar-SA', 'cs-CZ', 'da-DK', 'de-CH', 'de-DE', 'es-ES', 'fi-FI', 'fr-CH', 'fr-FR', 'it-IT', 'ja-JP', 'nl-NL', 'no-NO', 'pl-PL', 'pt-BR', 'ru-RU', 'sv-SE', 'tr-TR', 'zh-CN' );

	/**
	 * Taxonomy locale used for a language when the exact locale has no file.
	 *
	 * @since 7.14.4
	 * @var array<string,string>
	 */
	const TAXONOMY_LANGUAGE_DEFAULTS = array(
		'ar' => 'ar-SA',
		'cs' => 'cs-CZ',
		'da' => 'da-DK',
		'de' => 'de-DE',
		'es' => 'es-ES',
		'fi' => 'fi-FI',
		'fr' => 'fr-FR',
		'it' => 'it-IT',
		'ja' => 'ja-JP',
		'nb' => 'no-NO',
		'nn' => 'no-NO',
		'no' => 'no-NO',
		'nl' => 'nl-NL',
		'pl' => 'pl-PL',
		'pt' => 'pt-BR',
		'ru' => 'ru-RU',
		'sv' => 'sv-SE',
		'tr' => 'tr-TR',
		'zh' => 'zh-CN',
	);

	/**
	 * Merchants whose category field takes Google product taxonomy values.
	 *
	 * @since 7.14.4
	 *
	 * @return string[] Merchant slugs.
	 */
	public static function supported_merchants(): array {
		$merchants = array(
			'google',
			'google_express',
			'google_shopping_actions',
			'google_local_products',
			'google_local_products_inventory',
			'google_local_inventory_ads',
			'google_css_center',
			'facebook',
			'instagram',
			'whatsapp',
			'pinterest',
			'snapchat',
			'tiktok',
			'bing',
			'reddit_ads',
		);

		/**
		 * Filter the merchants that get smart category suggestions (Google product taxonomy values).
		 *
		 * @since 7.14.4
		 *
		 * @param string[] $merchants Merchant slugs.
		 */
		$filtered = apply_filters( 'wpfm_smart_category_merchants', $merchants );

		return is_array( $filtered ) ? array_values( array_filter( array_map( 'strval', $filtered ) ) ) : $merchants;
	}

	/**
	 * Whether a merchant gets smart category suggestions.
	 *
	 * @since 7.14.4
	 *
	 * @param string $merchant Merchant slug.
	 * @return bool
	 */
	public static function is_supported_merchant( string $merchant ): bool {
		return in_array( (string) $merchant, self::supported_merchants(), true );
	}

	/**
	 * Suggestions for store category terms.
	 *
	 * @since 7.14.4
	 *
	 * @param WP_Term[]|object[] $terms           Every `product_cat` term (for parent context).
	 * @param string             $exclude_map_key Optional. Key of the map open in the editor, left out of "Used before".
	 * @return array<int,array<int,array{value:string,score:float,confidence:string,source:string}>> Keyed by term ID.
	 */
	public static function suggest_for_terms( array $terms, string $exclude_map_key = '' ): array {
		try {
			return self::suggest_enhanced( $terms, $exclude_map_key );
		} catch ( \Throwable $e ) {
			try {
				$matcher = new Rex_Feed_Smart_Category_Matcher();
				return self::merge( $matcher->suggest_for_terms( $terms ), array() );
			} catch ( \Throwable $e ) {
				return array();
			}
		}
	}

	/**
	 * Remember the values of a saved map, so later maps can reuse them.
	 *
	 * Never throws: a failure here must not break saving the map.
	 *
	 * @since 7.14.4
	 *
	 * @param array  $rows     Saved map rows (`map-key`, `map-value`, `cat-name`).
	 * @param string $merchant Merchant slug of the map.
	 * @return void
	 */
	public static function learn_from_rows( array $rows, string $merchant ): void {
		try {
			if ( ! self::is_supported_merchant( $merchant ) || empty( $rows ) ) {
				return;
			}

			$by_id   = self::terms_by_id( self::all_terms() );
			$matcher = new Rex_Feed_Smart_Category_Matcher();
			$learned = self::get_learned();
			$changed = false;
			$now     = time();

			foreach ( $rows as $row ) {
				if ( ! is_array( $row ) || ! isset( $row['map-key'], $row['map-value'] ) ) {
					continue;
				}
				$value = $matcher->canonical_value( (string) $row['map-value'] );
				if ( '' === $value ) {
					continue;
				}

				$term_id = absint( $row['map-key'] );
				$auto    = self::is_auto_value( $row, $value, $matcher ) ? 1 : 0;
				$keys    = isset( $by_id[ $term_id ] )
					? self::term_keys( $by_id[ $term_id ], $by_id, $matcher )
					: self::name_keys( isset( $row['cat-name'] ) ? (string) $row['cat-name'] : '', '', $matcher );

				foreach ( array( 'ctx', 'name' ) as $type ) {
					if ( '' === $keys[ $type ] ) {
						continue;
					}
					$key = $type . ':' . $keys[ $type ];
					// Same value again: nothing new, unless a person now confirms it by hand. A
					// hand-picked value never turns into an accepted suggestion, because accepting its
					// own "Used before" chip in a later map is not new evidence against it.
					if ( isset( $learned[ $key ] ) && $learned[ $key ]['v'] === $value && ( 0 === $learned[ $key ]['a'] || $learned[ $key ]['a'] === $auto ) ) {
						continue;
					}
					// Re-adding moves the key to the end, so the oldest keys are dropped first.
					unset( $learned[ $key ] );
					$learned[ $key ] = array(
						'v' => $value,
						't' => $now,
						'a' => $auto,
					);
					$changed         = true;
				}
			}

			if ( ! $changed ) {
				return;
			}
			if ( count( $learned ) > self::LEARNED_MAX ) {
				$learned = array_slice( $learned, -self::LEARNED_MAX, null, true );
			}
			update_option( self::LEARNED_OPTION, $learned, false );
		} catch ( \Throwable $e ) {
			return;
		}
	}

	/**
	 * Full pipeline. Each optional signal is guarded on its own.
	 *
	 * @since 7.14.4
	 *
	 * @param array  $terms           Terms.
	 * @param string $exclude_map_key Map left out of "Used before".
	 * @return array
	 */
	private static function suggest_enhanced( array $terms, string $exclude_map_key = '' ): array {
		$defaults = array(
			'high'   => Rex_Feed_Smart_Category_Matcher::THRESHOLD_HIGH,
			'medium' => Rex_Feed_Smart_Category_Matcher::THRESHOLD_MEDIUM,
			'low'    => Rex_Feed_Smart_Category_Matcher::THRESHOLD_LOW,
		);

		$thresholds = $defaults;
		try {
			if ( class_exists( 'Rex_Feed_Smart_Category_Tracker' ) && method_exists( 'Rex_Feed_Smart_Category_Tracker', 'calibrated_thresholds' ) ) {
				$thresholds = Rex_Feed_Smart_Category_Tracker::calibrated_thresholds( $defaults );
			}
		} catch ( \Throwable $e ) {
			$thresholds = $defaults;
		}

		$localized = '';
		try {
			$localized = self::localized_taxonomy_file();
		} catch ( \Throwable $e ) {
			$localized = '';
		}

		$matcher = new Rex_Feed_Smart_Category_Matcher(
			'',
			array(
				'localized_file' => $localized,
				'thresholds'     => $thresholds,
			)
		);

		$titles = array();
		try {
			$titles = self::product_titles( $terms );
		} catch ( \Throwable $e ) {
			$titles = array();
		}

		$rules = $matcher->suggest_for_terms( $terms, $titles );

		$memory = array();
		try {
			$memory = self::memory_suggestions( $terms, $matcher, $exclude_map_key );
		} catch ( \Throwable $e ) {
			$memory = array();
		}

		return self::merge( $rules, $memory );
	}

	/**
	 * Merge memory and rule suggestions; one entry per value, at most 3, best first.
	 *
	 * Memory ranks a little above rules at the same score (MEMORY_BONUS), so the store's
	 * own choice for the same category wins, while a weak memory hit (Low) never buries
	 * a strong rule match.
	 *
	 * @since 7.14.4
	 *
	 * @param array $rules  Matcher suggestions keyed by term ID.
	 * @param array $memory Memory suggestions keyed by term ID.
	 * @return array
	 */
	private static function merge( array $rules, array $memory ): array {
		$result = array();
		foreach ( array_unique( array_merge( array_keys( $memory ), array_keys( $rules ) ) ) as $term_id ) {
			$list = array();
			$seq  = 0;

			// A past value that was only an accepted suggestion is not a person's choice: it gets
			// the confidence the rules give that same value now, or Low (on top, never bulk-filled)
			// when the rules no longer suggest it. Otherwise one wrong bulk fill would come back
			// as High in every later map.
			if ( ! empty( $memory[ $term_id ] ) ) {
				$rule_by_value = array();
				foreach ( isset( $rules[ $term_id ] ) ? (array) $rules[ $term_id ] : array() as $rule ) {
					if ( is_array( $rule ) && isset( $rule['value'] ) && ! isset( $rule_by_value[ $rule['value'] ] ) ) {
						$rule_by_value[ $rule['value'] ] = $rule;
					}
				}
				foreach ( $memory[ $term_id ] as $i => $item ) {
					if ( ! is_array( $item ) || empty( $item['auto'] ) || ! isset( $item['value'] ) ) {
						continue;
					}
					if ( isset( $rule_by_value[ $item['value'] ] ) ) {
						$memory[ $term_id ][ $i ]['confidence'] = (string) $rule_by_value[ $item['value'] ]['confidence'];
						$memory[ $term_id ][ $i ]['score']      = (float) $rule_by_value[ $item['value'] ]['score'];
					} else {
						$memory[ $term_id ][ $i ]['confidence'] = 'low';
						$memory[ $term_id ][ $i ]['disputed']   = true;
					}
				}
			}

			// An earlier mapping that disagrees with a High rule match is disputed: keep it
			// on top (the store chose it) but Low, so only an explicit Low / "Fill All" fill applies it.
			$rules_top = isset( $rules[ $term_id ][0] ) && is_array( $rules[ $term_id ][0] ) ? $rules[ $term_id ][0] : null;
			if ( $rules_top && 'high' === ( $rules_top['confidence'] ?? '' ) && ! empty( $memory[ $term_id ] ) ) {
				foreach ( $memory[ $term_id ] as $i => $item ) {
					if ( is_array( $item ) && isset( $item['value'] ) && $item['value'] !== $rules_top['value'] && 'low' !== ( $item['confidence'] ?? '' ) ) {
						$memory[ $term_id ][ $i ]['confidence'] = 'low';
						$memory[ $term_id ][ $i ]['disputed']   = true;
					}
				}
			}

			$sets = array(
				'memory' => isset( $memory[ $term_id ] ) ? $memory[ $term_id ] : array(),
				'rules'  => isset( $rules[ $term_id ] ) ? $rules[ $term_id ] : array(),
			);
			foreach ( $sets as $source => $items ) {
				foreach ( (array) $items as $item ) {
					if ( ! is_array( $item ) || empty( $item['value'] ) || ! is_string( $item['value'] ) ) {
						continue;
					}
					$rank = ( isset( $item['score'] ) ? (float) $item['score'] : 0.0 ) + ( 'memory' === $source ? self::MEMORY_BONUS : 0.0 );
					if ( isset( $list[ $item['value'] ] ) && $list[ $item['value'] ]['rank'] >= $rank ) {
						continue;
					}
					$item['source']         = $source;
					$list[ $item['value'] ] = array(
						'rank' => $rank,
						'seq'  => $seq++,
						'item' => $item,
					);
				}
			}
			if ( empty( $list ) ) {
				continue;
			}
			uasort(
				$list,
				static function ( $a, $b ) {
					// Ties keep insertion order (memory first), the same on every PHP version.
					return array( $b['rank'], $a['seq'] ) <=> array( $a['rank'], $b['seq'] );
				}
			);
			$result[ $term_id ] = array_values(
				array_map(
					static function ( $entry ) {
						return $entry['item'];
					},
					array_slice( $list, 0, 3, true )
				)
			);
		}
		return $result;
	}

	/**
	 * Suggestions from the store's own mappings.
	 *
	 * - Same category in another saved map: High.
	 * - Same name under the same parent, mapped before (also learned from deleted maps): High.
	 * - Values that were only accepted suggestions are marked `auto`; merge() re-rates them.
	 * - Same name under another parent: Medium for specific names (2+ words), Low for one word,
	 *   because a word like "Accessories" means different things under different parents.
	 *
	 * @since 7.14.4
	 *
	 * @param array                           $terms           Terms.
	 * @param Rex_Feed_Smart_Category_Matcher $matcher         Matcher (for name keys and value checks).
	 * @param string                          $exclude_map_key Map left out (the one open in the editor).
	 * @return array<int,array<int,array{value:string,score:float,confidence:string}>>
	 */
	private static function memory_suggestions( array $terms, Rex_Feed_Smart_Category_Matcher $matcher, string $exclude_map_key = '' ): array {
		$by_id   = self::terms_by_id( $terms );
		$by_term = array();
		$by_ctx  = array();
		$by_name = array();

		$maps = get_option( self::MAP_OPTION, array() );
		foreach ( is_array( $maps ) ? $maps : array() as $map_key => $map ) {
			// The map open in the editor is not "another" map.
			if ( '' !== $exclude_map_key && (string) $map_key === $exclude_map_key ) {
				continue;
			}
			if ( ! is_array( $map ) || empty( $map['map-config'] ) || ! is_array( $map['map-config'] ) ) {
				continue;
			}
			$merchant = ! empty( $map['merchant'] ) ? (string) $map['merchant'] : 'google';
			if ( ! self::is_supported_merchant( $merchant ) ) {
				continue;
			}
			foreach ( $map['map-config'] as $row ) {
				if ( ! is_array( $row ) || ! isset( $row['map-key'], $row['map-value'] ) || '' === trim( (string) $row['map-value'] ) ) {
					continue;
				}
				$value = $matcher->canonical_value( (string) $row['map-value'] );
				if ( '' === $value ) {
					continue;
				}
				$term_id = absint( $row['map-key'] );
				$hand    = ! self::is_auto_value( $row, $value, $matcher );
				self::add_vote( $by_term, (string) $term_id, $value, $hand );

				$keys = isset( $by_id[ $term_id ] )
					? self::term_keys( $by_id[ $term_id ], $by_id, $matcher )
					: self::name_keys( isset( $row['cat-name'] ) ? (string) $row['cat-name'] : '', '', $matcher );
				self::add_vote( $by_ctx, $keys['ctx'], $value, $hand );
				self::add_vote( $by_name, $keys['name'], $value, $hand );
			}
		}

		foreach ( self::get_learned() as $key => $item ) {
			$value = $matcher->canonical_value( $item['v'] );
			if ( '' === $value ) {
				continue;
			}
			$hand = 0 === $item['a'];
			if ( 0 === strpos( $key, 'ctx:' ) ) {
				self::add_vote( $by_ctx, substr( $key, 4 ), $value, $hand );
			} elseif ( 0 === strpos( $key, 'name:' ) ) {
				self::add_vote( $by_name, substr( $key, 5 ), $value, $hand );
			}
		}

		$result = array();
		foreach ( $by_id as $term_id => $term ) {
			$keys  = self::term_keys( $term, $by_id, $matcher );
			$words = '' === $keys['name'] ? 0 : count( explode( ' ', $keys['name'] ) );
			$tiers = array(
				array( isset( $by_term[ (string) $term_id ] ) ? $by_term[ (string) $term_id ] : array(), 0.97, 'high' ),
				array( '' !== $keys['ctx'] && isset( $by_ctx[ $keys['ctx'] ] ) ? $by_ctx[ $keys['ctx'] ] : array(), 0.92, 'high' ),
				array( '' !== $keys['name'] && isset( $by_name[ $keys['name'] ] ) ? $by_name[ $keys['name'] ] : array(), $words >= 2 ? 0.7 : 0.45, $words >= 2 ? 'medium' : 'low' ),
			);

			// A value a person picked anywhere (another map, or learned from a deleted one) counts
			// as their choice, even where it was later only accepted as a "Used before" chip.
			$hand_values = array();
			foreach ( $tiers as $tier ) {
				foreach ( $tier[0] as $value => $vote ) {
					if ( ! empty( $vote['h'] ) ) {
						$hand_values[ $value ] = true;
					}
				}
			}

			$list = array();
			$seen = array();
			foreach ( $tiers as $tier ) {
				list( $votes, $score, $confidence ) = $tier;
				// Most used first, then by text, so the order is the same on every PHP version.
				uksort(
					$votes,
					static function ( $a, $b ) use ( $votes ) {
						return array( $votes[ $b ]['n'], (string) $a ) <=> array( $votes[ $a ]['n'], (string) $b );
					}
				);
				foreach ( $votes as $value => $vote ) {
					if ( isset( $seen[ $value ] ) ) {
						continue;
					}
					$seen[ $value ] = true;
					$list[]         = array(
						'value'      => (string) $value,
						'score'      => $score,
						'confidence' => $confidence,
						// Only an accepted suggestion so far, never a person's own choice.
						'auto'       => ! $vote['h'] && ! isset( $hand_values[ $value ] ),
					);
					// Lower tiers only add alternatives.
					$confidence = 'low';
					$score      = min( $score, 0.45 );
				}
			}

			if ( ! empty( $list ) ) {
				$result[ $term_id ] = array_slice( $list, 0, 2 );
			}
		}

		return $result;
	}

	/**
	 * Count one value for a key.
	 *
	 * @since 7.14.4
	 *
	 * @param array  $bucket Votes, by reference.
	 * @param string $key    Key; empty keys are skipped.
	 * @param string $value  Value.
	 * @param bool   $hand   Whether a person chose this value (not just accepted a suggestion).
	 * @return void
	 */
	private static function add_vote( array &$bucket, string $key, string $value, bool $hand = true ): void {
		if ( '' === (string) $key ) {
			return;
		}
		if ( ! isset( $bucket[ $key ][ $value ] ) ) {
			$bucket[ $key ][ $value ] = array(
				'n' => 0,
				'h' => false,
			);
		}
		++$bucket[ $key ][ $value ]['n'];
		$bucket[ $key ][ $value ]['h'] = $bucket[ $key ][ $value ]['h'] || (bool) $hand;
	}

	/**
	 * Whether a saved value only came from accepting a suggestion (not a person's own choice).
	 *
	 * Uses the tracker keys on the row: `accepted` or `accepted_alt` with the value still equal
	 * to `accepted-value` (or, on older rows, to the suggestion). Typed, picked or changed values
	 * count as a person's choice.
	 *
	 * @since 7.14.4
	 *
	 * @param array                           $row     Saved map row.
	 * @param string                          $value   Canonical saved value.
	 * @param Rex_Feed_Smart_Category_Matcher $matcher Matcher.
	 * @return bool
	 */
	private static function is_auto_value( array $row, string $value, Rex_Feed_Smart_Category_Matcher $matcher ): bool {
		$outcome = isset( $row['suggestion-outcome'] ) ? (string) $row['suggestion-outcome'] : '';
		if ( 'accepted' !== $outcome && 'accepted_alt' !== $outcome ) {
			return false;
		}

		// The value as it was accepted (rows saved since the tracker stores it). A different
		// value now means a person changed it by hand later.
		if ( isset( $row['accepted-value'] ) && is_scalar( $row['accepted-value'] ) && '' !== (string) $row['accepted-value'] ) {
			return $matcher->canonical_value( (string) $row['accepted-value'] ) === $value;
		}

		// Older rows: an accepted top can still be compared with the suggestion; for an
		// accepted alternative the accepted value is unknown, so it stays an accepted suggestion.
		if ( 'accepted' === $outcome ) {
			$suggested = isset( $row['suggested-value'] ) ? $matcher->canonical_value( (string) $row['suggested-value'] ) : '';
			return '' === $suggested || $suggested === $value;
		}
		return true;
	}

	/**
	 * Memory keys of a term: `name` (normalized name) and `ctx` (name plus nearest meaningful parent).
	 *
	 * @since 7.14.4
	 *
	 * @param object                          $term    Term.
	 * @param array<int,object>               $by_id   Terms keyed by ID.
	 * @param Rex_Feed_Smart_Category_Matcher $matcher Matcher.
	 * @return array{name:string,ctx:string}
	 */
	private static function term_keys( object $term, array $by_id, Rex_Feed_Smart_Category_Matcher $matcher ): array {
		$parent = '';
		foreach ( array_reverse( $matcher->ancestor_names( $term, $by_id ) ) as $ancestor ) {
			$tokens = $matcher->tokenize( $ancestor );
			if ( ! empty( $tokens ) ) {
				$parent = implode( ' ', $tokens );
				break;
			}
		}
		return self::name_keys( (string) $term->name, $parent, $matcher );
	}

	/**
	 * Memory keys from a name and a normalized parent key.
	 *
	 * A name made only of audience words ("Men") has no `name` key; its `ctx` key still
	 * tells "Men" under "Shoes" from "Men" under "Clothing".
	 *
	 * @since 7.14.4
	 *
	 * @param string                          $name    Raw name.
	 * @param string                          $parent_key Normalized parent key, or empty.
	 * @param Rex_Feed_Smart_Category_Matcher $matcher Matcher.
	 * @return array{name:string,ctx:string}
	 */
	private static function name_keys( string $name, string $parent_key, Rex_Feed_Smart_Category_Matcher $matcher ): array {
		$tokens = $matcher->tokenize( (string) $name );
		$key    = implode( ' ', $tokens );
		if ( '' === $key ) {
			$raw = $matcher->tokenize( (string) $name, false );
			$ctx = empty( $raw ) || '' === $parent_key ? '' : implode( ' ', $raw ) . '|' . $parent_key;
			return array(
				'name' => '',
				'ctx'  => $ctx,
			);
		}
		return array(
			'name' => $key,
			'ctx'  => $key . '|' . $parent_key,
		);
	}

	/**
	 * Learned values, validated.
	 *
	 * @since 7.14.4
	 *
	 * @return array<string,array{v:string,t:int,a:int}> `a` = 1 when the value was only an accepted suggestion.
	 */
	private static function get_learned(): array {
		$stored  = get_option( self::LEARNED_OPTION, array() );
		$learned = array();
		foreach ( is_array( $stored ) ? $stored : array() as $key => $item ) {
			if ( is_string( $key ) && is_array( $item ) && isset( $item['v'] ) && is_string( $item['v'] ) ) {
				$learned[ $key ] = array(
					'v' => $item['v'],
					't' => isset( $item['t'] ) ? absint( $item['t'] ) : 0,
					// Unknown origin (saved by an earlier build) counts as an accepted suggestion.
					'a' => isset( $item['a'] ) ? ( $item['a'] ? 1 : 0 ) : 1,
				);
			}
		}
		return $learned;
	}

	/**
	 * Titles of published products per category, within a time budget.
	 *
	 * @since 7.14.4
	 *
	 * @param array $terms Terms.
	 * @return array<int,string[]> Titles keyed by term ID.
	 */
	private static function product_titles( array $terms ): array {
		global $wpdb;

		/**
		 * Filter whether product titles are used for smart category suggestions.
		 *
		 * @since 7.14.4
		 *
		 * @param bool $use Whether to read product titles.
		 */
		if ( ! apply_filters( 'wpfm_smart_category_use_products', true ) || ! is_object( $wpdb ) ) {
			return array();
		}

		// Categories to read, at most TITLE_TERMS_MAX: term_taxonomy_id => term_id.
		// No term->count gate: WooCommerce leaves hidden products out of it, but they still belong to the category.
		$wanted = array();
		foreach ( $terms as $term ) {
			if ( is_object( $term ) && ! empty( $term->term_id ) && ! empty( $term->term_taxonomy_id ) ) {
				$wanted[ (int) $term->term_taxonomy_id ] = (int) $term->term_id;
				if ( count( $wanted ) >= self::TITLE_TERMS_MAX ) {
					break;
				}
			}
		}
		if ( empty( $wanted ) ) {
			return array();
		}

		$start  = microtime( true );
		$titles = null;
		if ( self::supports_window_functions() ) {
			$titles = self::product_titles_batched( $wanted, $start );
		}
		if ( null === $titles ) {
			$titles = self::product_titles_per_term( $wanted, $start );
		}

		return $titles;
	}

	/**
	 * Product titles with one query per batch of categories (MySQL 8+ / MariaDB 10.2+).
	 *
	 * ROW_NUMBER() keeps the newest TITLES_PER_TERM published products per category.
	 *
	 * @since 7.14.4
	 *
	 * @param array<int,int> $wanted term_taxonomy_id => term_id.
	 * @param float          $start  Start time, for TITLE_TIME_BUDGET.
	 * @return array<int,string[]>|null Titles keyed by term ID, or null when the query failed.
	 */
	private static function product_titles_batched( array $wanted, float $start ): ?array {
		global $wpdb;

		$titles = array();
		foreach ( array_chunk( array_keys( $wanted ), self::TITLE_BATCH_SIZE ) as $chunk ) {
			if ( ( microtime( true ) - $start ) > self::TITLE_TIME_BUDGET ) {
				break;
			}

			$placeholders = implode( ',', array_fill( 0, count( $chunk ), '%d' ) );
			$sql          = "SELECT x.tt_id, x.post_title FROM (
					SELECT tr.term_taxonomy_id AS tt_id, p.post_title,
						ROW_NUMBER() OVER ( PARTITION BY tr.term_taxonomy_id ORDER BY p.ID DESC ) AS rn
					FROM {$wpdb->posts} p
					INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
					WHERE tr.term_taxonomy_id IN ( {$placeholders} ) AND p.post_type = 'product' AND p.post_status = 'publish'
				) x
				WHERE x.rn <= %d
				ORDER BY x.tt_id, x.rn";

			$suppress = $wpdb->suppress_errors( true );
			$rows     = $wpdb->get_results( $wpdb->prepare( $sql, array_merge( $chunk, array( self::TITLES_PER_TERM ) ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- placeholders are built above, values are passed to prepare().
			$error    = (string) $wpdb->last_error;
			$wpdb->suppress_errors( $suppress );

			if ( '' !== $error || ! is_array( $rows ) ) {
				return null;
			}

			$by_tt = array();
			foreach ( $rows as $row ) {
				$by_tt[ (int) $row['tt_id'] ][] = (string) $row['post_title'];
			}
			foreach ( $by_tt as $tt_id => $list ) {
				if ( isset( $wanted[ $tt_id ] ) && count( $list ) >= 2 ) {
					$titles[ $wanted[ $tt_id ] ] = $list;
				}
			}
		}

		return $titles;
	}

	/**
	 * Product titles with one small indexed query per category (older databases).
	 *
	 * @since 7.14.4
	 *
	 * @param array<int,int> $wanted term_taxonomy_id => term_id.
	 * @param float          $start  Start time, for TITLE_TIME_BUDGET.
	 * @return array<int,string[]> Titles keyed by term ID.
	 */
	private static function product_titles_per_term( array $wanted, float $start ): array {
		global $wpdb;

		$titles = array();
		foreach ( $wanted as $tt_id => $term_id ) {
			if ( ( microtime( true ) - $start ) > self::TITLE_TIME_BUDGET ) {
				break;
			}
			$rows = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT p.post_title FROM {$wpdb->posts} p
					INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
					WHERE tr.term_taxonomy_id = %d AND p.post_type = 'product' AND p.post_status = 'publish'
					ORDER BY p.ID DESC LIMIT %d",
					$tt_id,
					self::TITLES_PER_TERM
				)
			);
			if ( is_array( $rows ) && count( $rows ) >= 2 ) {
				$titles[ $term_id ] = array_map( 'strval', $rows );
			}
		}

		return $titles;
	}

	/**
	 * Whether the database supports window functions (MySQL 8.0+, MariaDB 10.2+).
	 *
	 * @since 7.14.4
	 *
	 * @return bool
	 */
	private static function supports_window_functions(): bool {
		global $wpdb;

		/**
		 * Filter whether product titles are read with one query per batch of categories.
		 *
		 * @since 7.14.4
		 *
		 * @param bool|null $supported Null to detect from the database version.
		 */
		$forced = apply_filters( 'wpfm_smart_category_batched_titles', null );
		if ( is_bool( $forced ) ) {
			return $forced;
		}

		$version = method_exists( $wpdb, 'db_version' ) ? (string) $wpdb->db_version() : '';
		$info    = method_exists( $wpdb, 'db_server_info' ) ? (string) $wpdb->db_server_info() : '';
		if ( '' === $version ) {
			return false;
		}
		return false !== stripos( $info, 'mariadb' ) ? version_compare( $version, '10.2', '>=' ) : version_compare( $version, '8.0', '>=' );
	}

	/**
	 * Localized Google taxonomy file for the site language.
	 *
	 * A file bundled with the plugin (admin/partials/taxonomies/) is preferred and needs
	 * no download. Otherwise it never downloads during the request: a missing or outdated
	 * file only schedules the background download (see maybe_schedule_taxonomy_fetch()),
	 * and matching uses the English list until the file is there. An outdated downloaded
	 * file is still used meanwhile.
	 *
	 * @since 7.14.4
	 *
	 * @return string File path, or empty.
	 */
	private static function localized_taxonomy_file(): string {
		$locale = self::taxonomy_locale();

		// A file shipped with the plugin wins: no download, always available.
		$bundled = self::bundled_taxonomy_path( $locale );
		if ( '' !== $bundled ) {
			return $bundled;
		}

		$file = self::localized_taxonomy_path( $locale );
		self::maybe_schedule_taxonomy_fetch();
		return '' !== $file && is_readable( $file ) ? $file : '';
	}

	/**
	 * Path of the taxonomy file shipped with the plugin for a locale, when present and readable.
	 *
	 * These files live in admin/partials/taxonomies/ and let common languages work offline,
	 * with no Google download. Unlisted locales simply have no bundled file.
	 *
	 * @since 7.14.4
	 *
	 * @param string $locale Google taxonomy locale such as `de-DE`.
	 * @return string File path, or empty.
	 */
	private static function bundled_taxonomy_path( string $locale ): string {
		if ( '' === $locale || ! in_array( $locale, self::TAXONOMY_LOCALES, true ) || ! defined( 'WPFM_PLUGIN_DIR_PATH' ) ) {
			return '';
		}
		$file = WPFM_PLUGIN_DIR_PATH . 'admin/partials/taxonomies/taxonomy-with-ids.' . $locale . '.txt';
		return is_readable( $file ) ? $file : '';
	}

	/**
	 * Schedule the background download of the site language's taxonomy file when it is
	 * missing or older than TAXONOMY_MAX_AGE, unless the last try failed less than a day ago.
	 *
	 * Called when the Category Mapping page loads and when suggestions are requested.
	 * Cheap: a file check and, at most, one cron event.
	 *
	 * @since 7.14.4
	 *
	 * @return void
	 */
	public static function maybe_schedule_taxonomy_fetch(): void {
		try {
			$locale = self::taxonomy_locale();

			// A bundled file covers this locale: never download.
			if ( '' !== self::bundled_taxonomy_path( $locale ) ) {
				return;
			}

			$file = self::localized_taxonomy_path( $locale );
			if ( '' === $file ) {
				return;
			}
			$fresh = is_readable( $file ) && ( time() - (int) filemtime( $file ) ) < self::TAXONOMY_MAX_AGE;
			if ( $fresh || get_transient( self::TAXONOMY_FAIL_PREFIX . $locale ) || wp_next_scheduled( self::FETCH_HOOK, array( $locale ) ) ) {
				return;
			}
			wp_schedule_single_event( time(), self::FETCH_HOOK, array( $locale ) );
		} catch ( \Throwable $e ) {
			return;
		}
	}

	/**
	 * WP-Cron callback: download one localized taxonomy file into uploads.
	 *
	 * The locale comes from the stored cron event, so it is checked against the known
	 * list again. The file is validated before it is written, and written atomically.
	 * A failure pauses new tries for a day.
	 *
	 * @since 7.14.4
	 *
	 * @param mixed $locale Google taxonomy locale such as `de-DE`.
	 * @return void
	 */
	public static function fetch_taxonomy( $locale = '' ): void {
		$locale = is_string( $locale ) ? $locale : '';
		$file   = self::localized_taxonomy_path( $locale );
		if ( '' === $file ) {
			return;
		}
		$dir = dirname( $file );

		$response = wp_remote_get(
			'https://www.google.com/basepages/producttype/taxonomy-with-ids.' . $locale . '.txt',
			array( 'timeout' => 15 )
		);
		$body     = is_wp_error( $response ) ? '' : (string) wp_remote_retrieve_body( $response );
		$valid    = 200 === (int) wp_remote_retrieve_response_code( $response )
			&& strlen( $body ) > 100000
			&& preg_match_all( '/^\d+ - /m', $body ) > 3000;

		if ( $valid && wp_mkdir_p( $dir ) ) {
			if ( ! file_exists( $dir . '/index.php' ) ) {
				@file_put_contents( $dir . '/index.php', "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
			}
			$tmp = $file . '.' . uniqid( '', true ) . '.tmp';
			if ( false !== @file_put_contents( $tmp, $body ) && @rename( $tmp, $file ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.PHP.NoSilencedErrors
				delete_transient( self::TAXONOMY_FAIL_PREFIX . $locale );
				return;
			}
			if ( file_exists( $tmp ) ) {
				wp_delete_file( $tmp );
			}
		}

		set_transient( self::TAXONOMY_FAIL_PREFIX . $locale, 1, DAY_IN_SECONDS );
	}

	/**
	 * Path of the taxonomy file for a known locale, or empty for an unknown locale or no uploads.
	 *
	 * @since 7.14.4
	 *
	 * @param string $locale Google taxonomy locale.
	 * @return string
	 */
	private static function localized_taxonomy_path( string $locale ): string {
		if ( ! in_array( $locale, self::TAXONOMY_LOCALES, true ) ) {
			return '';
		}
		$dir = self::taxonomy_dir();
		return '' === $dir ? '' : trailingslashit( $dir ) . 'taxonomy-with-ids.' . $locale . '.txt';
	}

	/**
	 * Google taxonomy locale for the site language, or empty for English and unsupported languages.
	 *
	 * @since 7.14.4
	 *
	 * @return string
	 */
	private static function taxonomy_locale(): string {
		$locale = function_exists( 'get_locale' ) ? (string) get_locale() : '';
		$lang   = strtolower( substr( $locale, 0, 2 ) );
		$code   = '';
		if ( '' !== $lang && 'en' !== $lang ) {
			$exact = str_replace( '_', '-', substr( $locale, 0, 5 ) );
			if ( in_array( $exact, self::TAXONOMY_LOCALES, true ) ) {
				$code = $exact;
			} elseif ( isset( self::TAXONOMY_LANGUAGE_DEFAULTS[ $lang ] ) ) {
				$code = self::TAXONOMY_LANGUAGE_DEFAULTS[ $lang ];
			}
		}

		/**
		 * Filter the Google taxonomy locale used for localized smart category matching.
		 *
		 * Return an empty string to turn localized matching (and its one-time download) off.
		 *
		 * @since 7.14.4
		 *
		 * @param string $code   Locale such as `de-DE`, or empty.
		 * @param string $locale Site locale.
		 */
		$code = (string) apply_filters( 'wpfm_smart_category_taxonomy_locale', $code, $locale );

		return in_array( $code, self::TAXONOMY_LOCALES, true ) ? $code : '';
	}

	/**
	 * Uploads folder for taxonomy files, or empty when uploads are not available.
	 *
	 * @since 7.14.4
	 *
	 * @return string
	 */
	private static function taxonomy_dir(): string {
		if ( ! function_exists( 'wp_upload_dir' ) ) {
			return '';
		}
		$uploads = wp_upload_dir( null, false );
		if ( ! is_array( $uploads ) || ! empty( $uploads['error'] ) || empty( $uploads['basedir'] ) ) {
			return '';
		}
		return trailingslashit( $uploads['basedir'] ) . self::TAXONOMY_DIR;
	}

	/**
	 * Every product category term.
	 *
	 * @since 7.14.4
	 *
	 * @return array
	 */
	private static function all_terms(): array {
		$terms = get_terms(
			array(
				'taxonomy'      => 'product_cat',
				'hide_empty'    => false,
				'no_found_rows' => true,
			)
		);
		return is_array( $terms ) ? $terms : array();
	}

	/**
	 * Terms keyed by ID.
	 *
	 * @since 7.14.4
	 *
	 * @param array $terms Terms.
	 * @return array<int,object>
	 */
	private static function terms_by_id( array $terms ): array {
		$by_id = array();
		foreach ( $terms as $term ) {
			if ( is_object( $term ) && isset( $term->term_id, $term->name ) ) {
				$by_id[ (int) $term->term_id ] = $term;
			}
		}
		return $by_id;
	}
}
