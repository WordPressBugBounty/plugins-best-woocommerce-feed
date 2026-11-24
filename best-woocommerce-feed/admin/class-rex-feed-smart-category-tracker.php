<?php
/**
 * Smart Category Tracker.
 *
 * Records whether smart category suggestions were accepted or changed when a
 * category map is saved, and keeps running counts so an accuracy score can be
 * computed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin
 * @since      7.14.4
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Rex_Feed_Smart_Category_Tracker
 *
 * Flow on every Save / Update of a category map:
 * 1. `parse_suggestions()` validates the `suggestions` payload sent by the mapping page.
 * 2. `apply()` adds `suggested-value` / `suggestion-outcome` to the map rows and returns
 *    the counts of outcomes that are new for this save (pure, no side effects).
 * 3. `record()` adds those counts to option `wpfm_smart_category_stats` and fires
 *    `wpfm_smart_category_feedback`.
 *
 * A row is counted once. Re-saving the same map counts it again only when its stored
 * outcome was `ignored` and is now a decision; `ignored` is then decreased, so the
 * `ignored` total always equals the rows that are currently ignored.
 *
 * @since 7.14.4
 */
class Rex_Feed_Smart_Category_Tracker {

	/**
	 * Option that stores the aggregate counts.
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const OPTION = 'wpfm_smart_category_stats';

	/**
	 * Top suggestion saved as is.
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const ACCEPTED = 'accepted';

	/**
	 * One of the alternatives saved.
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const ACCEPTED_ALT = 'accepted_alt';

	/**
	 * A different, non-empty value saved.
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const CHANGED = 'changed';

	/**
	 * Row saved empty.
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const IGNORED = 'ignored';

	/**
	 * Most alternatives kept per row (the page shows up to 2).
	 *
	 * @since 7.14.4
	 * @var int
	 */
	const MAX_ALTS = 2;

	/**
	 * Longest accepted value; Google paths are well under this.
	 *
	 * @since 7.14.4
	 * @var int
	 */
	const MAX_VALUE_LENGTH = 500;

	/**
	 * Suggestion from the matcher rules (name, parents, product titles).
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const SOURCE_RULES = 'rules';

	/**
	 * Suggestion from the store's own earlier mappings.
	 *
	 * @since 7.14.4
	 * @var string
	 */
	const SOURCE_MEMORY = 'memory';

	/**
	 * Decided rows a confidence label needs before its threshold is calibrated.
	 *
	 * @since 7.14.4
	 * @var int
	 */
	const CALIBRATION_MIN_DECIDED = 20;

	/**
	 * Top-1 accuracy each label should reach.
	 *
	 * @since 7.14.4
	 * @var array<string,float>
	 */
	const CALIBRATION_TARGETS = array(
		'high'   => 0.85,
		'medium' => 0.6,
	);

	/**
	 * Most a threshold may be raised (stricter) or lowered (looser) by calibration.
	 *
	 * Medium is never lowered: a looser Medium would put weaker guesses under the
	 * "Fill Medium Confidence Suggestions" bulk action. Lowering High only moves rows
	 * between two labels that both read as confident.
	 *
	 * @since 7.14.4
	 * @var array<string,array<string,float>>
	 */
	const CALIBRATION_LIMITS = array(
		'high'   => array(
			'raise' => 0.1,
			'lower' => 0.05,
		),
		'medium' => array(
			'raise' => 0.1,
			'lower' => 0.0,
		),
	);

	/**
	 * All outcomes, in display order.
	 *
	 * @since 7.14.4
	 *
	 * @return string[]
	 */
	public static function outcomes(): array {
		return array( self::ACCEPTED, self::ACCEPTED_ALT, self::CHANGED, self::IGNORED );
	}

	/**
	 * Confidence labels the matcher returns.
	 *
	 * @since 7.14.4
	 *
	 * @return string[]
	 */
	public static function confidences(): array {
		return array( 'high', 'medium', 'low' );
	}

	/**
	 * Suggestion sources.
	 *
	 * @since 7.14.4
	 *
	 * @return string[]
	 */
	public static function sources(): array {
		return array( self::SOURCE_RULES, self::SOURCE_MEMORY );
	}

	/**
	 * Empty counts structure.
	 *
	 * @since 7.14.4
	 *
	 * @return array{shown:int,accepted:int,accepted_alt:int,changed:int,ignored:int,by_confidence:array<string,array<string,int>>}
	 */
	public static function empty_counts(): array {
		$counts = array( 'shown' => 0 );
		foreach ( self::outcomes() as $outcome ) {
			$counts[ $outcome ] = 0;
		}

		$counts['by_confidence'] = array();
		foreach ( self::confidences() as $confidence ) {
			$counts['by_confidence'][ $confidence ] = array( 'shown' => 0 );
			foreach ( self::outcomes() as $outcome ) {
				$counts['by_confidence'][ $confidence ][ $outcome ] = 0;
			}
		}

		$counts['by_source'] = array();
		foreach ( self::sources() as $source ) {
			$counts['by_source'][ $source ] = array( 'shown' => 0 );
			foreach ( self::outcomes() as $outcome ) {
				$counts['by_source'][ $source ][ $outcome ] = 0;
			}
		}

		return $counts;
	}

	/**
	 * Validate and sanitize the `suggestions` payload.
	 *
	 * Expected JSON: `{ term_id: { top: string, alts: string[], confidence: string, source?: string } }`.
	 * Unknown term IDs, empty tops and unknown confidence labels are dropped.
	 *
	 * @since 7.14.4
	 *
	 * @param mixed $raw              JSON string from the request (slashed) or an already decoded array.
	 * @param int[] $allowed_term_ids Term IDs present in the submitted map; others are ignored.
	 *
	 * @return array<int,array{top:string,alts:string[],confidence:string,source:string}>
	 */
	public static function parse_suggestions( $raw, array $allowed_term_ids ): array {
		if ( is_string( $raw ) ) {
			$raw = json_decode( wp_unslash( $raw ), true );
		}

		if ( ! is_array( $raw ) ) {
			return array();
		}

		$allowed = array_flip( array_map( 'absint', $allowed_term_ids ) );
		$result  = array();

		foreach ( $raw as $term_id => $item ) {
			$term_id = absint( $term_id );
			if ( ! $term_id || ! isset( $allowed[ $term_id ] ) || ! is_array( $item ) || ! isset( $item['top'] ) || ! is_string( $item['top'] ) ) {
				continue;
			}

			$top = self::clean_value( $item['top'] );
			if ( '' === $top ) {
				continue;
			}

			$alts = array();
			if ( isset( $item['alts'] ) && is_array( $item['alts'] ) ) {
				foreach ( $item['alts'] as $alt ) {
					$alt = is_string( $alt ) ? self::clean_value( $alt ) : '';
					if ( '' !== $alt && $alt !== $top && ! in_array( $alt, $alts, true ) ) {
						$alts[] = $alt;
					}
					if ( count( $alts ) >= self::MAX_ALTS ) {
						break;
					}
				}
			}

			$confidence = isset( $item['confidence'] ) && is_string( $item['confidence'] ) ? sanitize_key( $item['confidence'] ) : '';
			if ( ! in_array( $confidence, self::confidences(), true ) ) {
				$confidence = 'low';
			}

			$source = isset( $item['source'] ) && is_string( $item['source'] ) ? sanitize_key( $item['source'] ) : '';
			if ( ! in_array( $source, self::sources(), true ) ) {
				$source = self::SOURCE_RULES;
			}

			$result[ $term_id ] = array(
				'top'        => $top,
				'alts'       => $alts,
				'confidence' => $confidence,
				'source'     => $source,
			);
		}

		return $result;
	}

	/**
	 * Outcome of one row.
	 *
	 * @since 7.14.4
	 *
	 * @param string   $saved Saved value.
	 * @param string   $top   Top suggestion.
	 * @param string[] $alts  Alternatives.
	 *
	 * @return string One of the outcome constants.
	 */
	public static function outcome( string $saved, string $top, array $alts = array() ): string {
		$saved = self::clean_value( $saved );

		if ( '' === $saved ) {
			return self::IGNORED;
		}
		if ( self::clean_value( $top ) === $saved ) {
			return self::ACCEPTED;
		}
		foreach ( $alts as $alt ) {
			if ( self::clean_value( $alt ) === $saved ) {
				return self::ACCEPTED_ALT;
			}
		}

		return self::CHANGED;
	}

	/**
	 * Add suggestion keys to map rows and count the new outcomes.
	 *
	 * For each row:
	 * - shown a suggestion now and never recorded before: record and count it (`shown` + outcome);
	 * - shown now and previously `ignored`: record the new outcome; count it only when it is a decision;
	 * - previously decided, sent again with the same value for the same suggestion: keep, count nothing;
	 * - previously decided, shown again and decided differently (the row was cleared): a new decision;
	 * - not shown now: keep any stored keys, unless a decided row was saved empty (decision undone).
	 *
	 * Rows get `suggested-value`, `suggestion-outcome`, `suggestion-source`, `suggestion-confidence`
	 * and, for an accepted top or alternative, `accepted-value` (the value as accepted). The source and
	 * confidence say which bucket counted the row, so a later decision on an ignored row moves its
	 * counts out of the right bucket; `accepted-value` tells a later hand edit from the accepted value.
	 * Feed generation reads only `map-key` / `map-value`, so the extra keys do not affect it.
	 *
	 * @since 7.14.4
	 *
	 * @param array<int,array<string,mixed>>                               $rows          Map rows (`map-key`, `map-value`, `cat-name`).
	 * @param array<int,array{top:string,alts:string[],confidence:string}> $suggestions   From `parse_suggestions()`.
	 * @param array<int,array<string,mixed>>                               $previous_rows Rows of the stored map before this save, or empty for a new map.
	 *
	 * @return array{rows:array<int,array<string,mixed>>,counts:array<string,mixed>}
	 */
	public static function apply( array $rows, array $suggestions, array $previous_rows = array() ): array {
		$counts   = self::empty_counts();
		$previous = array();

		foreach ( $previous_rows as $prev ) {
			if ( is_array( $prev ) && isset( $prev['map-key'], $prev['suggestion-outcome'], $prev['suggested-value'] ) ) {
				$previous[ absint( $prev['map-key'] ) ] = $prev;
			}
		}

		foreach ( $rows as $index => $row ) {
			if ( ! is_array( $row ) || ! isset( $row['map-key'] ) ) {
				continue;
			}

			$term_id = absint( $row['map-key'] );
			$prev    = isset( $previous[ $term_id ] ) ? $previous[ $term_id ] : null;
			$prev_ok = $prev && in_array( $prev['suggestion-outcome'], self::outcomes(), true );

			$saved   = isset( $row['map-value'] ) ? (string) $row['map-value'] : '';
			$decided = $prev_ok && self::IGNORED !== $prev['suggestion-outcome'];

			if ( ! isset( $suggestions[ $term_id ] ) ) {
				// Not shown this time. A decided row saved empty was cleared: its decision is
				// undone and the next suggestion shown for it is a new one. Otherwise keep what
				// was recorded.
				if ( $prev_ok && ! ( $decided && '' === trim( $saved ) ) ) {
					$rows[ $index ] = self::copy_feedback_keys( $rows[ $index ], $prev );
				}
				continue;
			}

			$suggestion = $suggestions[ $term_id ];

			if ( $decided ) {
				// A row keeps its "shown" marker for the rest of the page view, so a later save
				// sends it again: the same value for the same suggestion is the decision already
				// counted. Anything else means the row was cleared and decided again (a chip
				// only appears on an empty row): count it as a new decision below.
				$prev_value = isset( $prev['map-value'] ) ? self::clean_value( (string) $prev['map-value'] ) : '';
				$same_value = self::clean_value( $saved ) === $prev_value;
				if ( $same_value && $suggestion['top'] === (string) $prev['suggested-value'] ) {
					$rows[ $index ] = self::copy_feedback_keys( $rows[ $index ], $prev );
					continue;
				}
				$prev_ok = false;
			}

			$outcome    = self::outcome( $saved, $suggestion['top'], $suggestion['alts'] );
			$confidence = $suggestion['confidence'];
			$source     = isset( $suggestion['source'] ) && in_array( $suggestion['source'], self::sources(), true ) ? $suggestion['source'] : self::SOURCE_RULES;
			// Confidence buckets measure the matcher only; the store's own earlier choices are counted by source.
			$by_conf = self::SOURCE_RULES === $source;

			if ( $prev_ok ) {
				// The bucket that counted this row when it was ignored (older rows did not store
				// it: fall back to the current one, as before).
				$prev_source  = isset( $prev['suggestion-source'] ) && in_array( $prev['suggestion-source'], self::sources(), true ) ? (string) $prev['suggestion-source'] : $source;
				$prev_conf    = isset( $prev['suggestion-confidence'] ) && in_array( $prev['suggestion-confidence'], self::confidences(), true ) ? (string) $prev['suggestion-confidence'] : $confidence;
				$prev_by_conf = self::SOURCE_RULES === $prev_source;

				if ( self::IGNORED === $outcome ) {
					// Still ignored: nothing new; keep the bucket that counted it.
					$rows[ $index ] = self::copy_feedback_keys( $rows[ $index ], $prev );
					continue;
				}

				// Decided now: take the row out of the bucket that counted it as shown and ignored,
				// and count it as shown in the bucket of the suggestion that was decided on. The
				// overall `shown` total does not change.
				--$counts[ self::IGNORED ];
				--$counts['by_source'][ $prev_source ][ self::IGNORED ];
				--$counts['by_source'][ $prev_source ]['shown'];
				++$counts['by_source'][ $source ]['shown'];
				if ( $prev_by_conf ) {
					--$counts['by_confidence'][ $prev_conf ][ self::IGNORED ];
					--$counts['by_confidence'][ $prev_conf ]['shown'];
				}
				if ( $by_conf ) {
					++$counts['by_confidence'][ $confidence ]['shown'];
				}
			} else {
				++$counts['shown'];
				++$counts['by_source'][ $source ]['shown'];
				if ( $by_conf ) {
					++$counts['by_confidence'][ $confidence ]['shown'];
				}
			}

			$rows[ $index ]['suggested-value']       = $suggestion['top'];
			$rows[ $index ]['suggestion-outcome']    = $outcome;
			$rows[ $index ]['suggestion-source']     = $source;
			$rows[ $index ]['suggestion-confidence'] = $confidence;
			unset( $rows[ $index ]['accepted-value'] );
			if ( self::ACCEPTED === $outcome || self::ACCEPTED_ALT === $outcome ) {
				$rows[ $index ]['accepted-value'] = self::clean_value( $saved );
			}

			++$counts[ $outcome ];
			++$counts['by_source'][ $source ][ $outcome ];
			if ( $by_conf ) {
				++$counts['by_confidence'][ $confidence ][ $outcome ];
			}
		}

		return array(
			'rows'   => $rows,
			'counts' => $counts,
		);
	}

	/**
	 * Copy the stored feedback keys of a previous row onto a row.
	 *
	 * @since 7.14.4
	 *
	 * @param array<string,mixed> $row  Row being saved.
	 * @param array<string,mixed> $prev Same row in the previous version of the map.
	 *
	 * @return array<string,mixed> Row with the previous feedback keys.
	 */
	private static function copy_feedback_keys( array $row, array $prev ): array {
		foreach ( array( 'suggested-value', 'suggestion-outcome', 'suggestion-source', 'suggestion-confidence', 'accepted-value' ) as $key ) {
			unset( $row[ $key ] );
			if ( isset( $prev[ $key ] ) && is_scalar( $prev[ $key ] ) ) {
				$row[ $key ] = (string) $prev[ $key ];
			}
		}
		return $row;
	}

	/**
	 * Add this save's counts to the stored totals and fire the feedback action.
	 *
	 * Does nothing when no row was shown a suggestion.
	 *
	 * @since 7.14.4
	 *
	 * @param array<string,mixed> $counts Counts from `apply()`.
	 *
	 * @return array<string,mixed> Stored totals after this save, with accuracy.
	 */
	public static function record( array $counts ): array {
		$stats = self::get_raw_stats();

		if ( ! self::has_changes( $counts ) ) {
			return self::with_accuracy( $stats );
		}

		$stats['shown'] = max( 0, $stats['shown'] + (int) $counts['shown'] );
		foreach ( self::outcomes() as $outcome ) {
			$stats[ $outcome ] = max( 0, $stats[ $outcome ] + (int) $counts[ $outcome ] );
		}
		foreach ( self::confidences() as $confidence ) {
			$stats['by_confidence'][ $confidence ]['shown'] = max( 0, $stats['by_confidence'][ $confidence ]['shown'] + (int) $counts['by_confidence'][ $confidence ]['shown'] );
			foreach ( self::outcomes() as $outcome ) {
				$stats['by_confidence'][ $confidence ][ $outcome ] = max(
					0,
					$stats['by_confidence'][ $confidence ][ $outcome ] + (int) $counts['by_confidence'][ $confidence ][ $outcome ]
				);
			}
		}
		foreach ( self::sources() as $source ) {
			$bucket                                 = isset( $counts['by_source'][ $source ] ) && is_array( $counts['by_source'][ $source ] ) ? $counts['by_source'][ $source ] : array();
			$stats['by_source'][ $source ]['shown'] = max( 0, $stats['by_source'][ $source ]['shown'] + ( isset( $bucket['shown'] ) ? (int) $bucket['shown'] : 0 ) );
			foreach ( self::outcomes() as $outcome ) {
				$stats['by_source'][ $source ][ $outcome ] = max(
					0,
					$stats['by_source'][ $source ][ $outcome ] + ( isset( $bucket[ $outcome ] ) ? (int) $bucket[ $outcome ] : 0 )
				);
			}
		}
		$stats['updated_at'] = time();

		update_option( self::OPTION, $stats, false );

		$stats = self::with_accuracy( $stats );

		/**
		 * Fires after a category map save recorded smart category feedback.
		 *
		 * @since 7.14.4
		 *
		 * @param array $counts Outcome counts of this save (may hold negative `ignored` for ignored → decision).
		 * @param array $stats  Stored totals after this save, with `accuracy` and `top1_accuracy`.
		 */
		do_action( 'wpfm_smart_category_feedback', $counts, $stats );

		return $stats;
	}

	/**
	 * Stored totals with accuracy and top-1 accuracy.
	 *
	 * @since 7.14.4
	 *
	 * @return array<string,mixed>
	 */
	public static function get_stats(): array {
		return self::with_accuracy( self::get_raw_stats() );
	}

	/**
	 * Accuracy = (accepted + accepted_alt) / (accepted + accepted_alt + changed).
	 * Top-1 accuracy = accepted / (accepted + accepted_alt + changed). Ignored rows are excluded.
	 *
	 * @since 7.14.4
	 *
	 * @param array<string,mixed> $counts Counts or totals.
	 *
	 * @return array{accuracy:float|null,top1_accuracy:float|null} Ratios 0–1, null when nothing was decided.
	 */
	public static function accuracy( array $counts ): array {
		$accepted     = isset( $counts[ self::ACCEPTED ] ) ? max( 0, (int) $counts[ self::ACCEPTED ] ) : 0;
		$accepted_alt = isset( $counts[ self::ACCEPTED_ALT ] ) ? max( 0, (int) $counts[ self::ACCEPTED_ALT ] ) : 0;
		$changed      = isset( $counts[ self::CHANGED ] ) ? max( 0, (int) $counts[ self::CHANGED ] ) : 0;
		$decided      = $accepted + $accepted_alt + $changed;

		if ( ! $decided ) {
			return array(
				'accuracy'      => null,
				'top1_accuracy' => null,
			);
		}

		return array(
			'accuracy'      => round( ( $accepted + $accepted_alt ) / $decided, 4 ),
			'top1_accuracy' => round( $accepted / $decided, 4 ),
		);
	}

	/**
	 * Confidence thresholds tuned to how this store decided on earlier suggestions.
	 *
	 * For `high` and `medium`: once a label has CALIBRATION_MIN_DECIDED decided rows from
	 * the matcher, a top-1 accuracy below its target raises the threshold (stricter labels),
	 * and one well above it lowers the threshold a little. Changes are capped by
	 * CALIBRATION_LIMITS, so the labels move gradually and never far from the defaults.
	 *
	 * @since 7.14.4
	 *
	 * @param array<string,float> $defaults Default thresholds (`high`, `medium`, `low`).
	 *
	 * @return array<string,float> Thresholds to use.
	 */
	public static function calibrated_thresholds( array $defaults ): array {
		$thresholds = $defaults;
		$stats      = self::get_raw_stats();

		foreach ( self::CALIBRATION_TARGETS as $confidence => $target ) {
			if ( ! isset( $thresholds[ $confidence ], $stats['by_confidence'][ $confidence ] ) ) {
				continue;
			}
			$bucket   = $stats['by_confidence'][ $confidence ];
			$accepted = (int) $bucket[ self::ACCEPTED ];
			$decided  = $accepted + (int) $bucket[ self::ACCEPTED_ALT ] + (int) $bucket[ self::CHANGED ];
			if ( $decided < self::CALIBRATION_MIN_DECIDED ) {
				continue;
			}

			$top1   = $accepted / $decided;
			$limits = self::CALIBRATION_LIMITS[ $confidence ];
			if ( $top1 < $target ) {
				$thresholds[ $confidence ] += min( $limits['raise'], ( $target - $top1 ) * 0.5 );
			} elseif ( $top1 > $target + 0.1 ) {
				$thresholds[ $confidence ] -= min( $limits['lower'], ( $top1 - $target - 0.1 ) * 0.5 );
			}
			$thresholds[ $confidence ] = round( (float) $thresholds[ $confidence ], 3 );
		}

		/**
		 * Filter the confidence thresholds used by smart category suggestions.
		 *
		 * @since 7.14.4
		 *
		 * @param array<string,float> $thresholds Calibrated thresholds (`high`, `medium`, `low`).
		 * @param array<string,float> $defaults   Default thresholds.
		 * @param array<string,mixed> $stats      Stored feedback totals.
		 */
		$filtered = apply_filters( 'wpfm_smart_category_thresholds', $thresholds, $defaults, $stats );

		return is_array( $filtered ) ? $filtered : $thresholds;
	}

	/**
	 * Stored totals, normalized to the full structure.
	 *
	 * @since 7.14.4
	 *
	 * @return array<string,mixed>
	 */
	private static function get_raw_stats(): array {
		$stored = get_option( self::OPTION, array() );
		$stored = is_array( $stored ) ? $stored : array();
		$stats  = self::empty_counts();

		$stats['shown'] = isset( $stored['shown'] ) ? absint( $stored['shown'] ) : 0;
		foreach ( self::outcomes() as $outcome ) {
			$stats[ $outcome ] = isset( $stored[ $outcome ] ) ? absint( $stored[ $outcome ] ) : 0;
		}
		foreach ( self::confidences() as $confidence ) {
			$bucket = isset( $stored['by_confidence'][ $confidence ] ) && is_array( $stored['by_confidence'][ $confidence ] ) ? $stored['by_confidence'][ $confidence ] : array();
			foreach ( array_keys( $stats['by_confidence'][ $confidence ] ) as $key ) {
				$stats['by_confidence'][ $confidence ][ $key ] = isset( $bucket[ $key ] ) ? absint( $bucket[ $key ] ) : 0;
			}
		}
		foreach ( self::sources() as $source ) {
			$bucket = isset( $stored['by_source'][ $source ] ) && is_array( $stored['by_source'][ $source ] ) ? $stored['by_source'][ $source ] : array();
			foreach ( array_keys( $stats['by_source'][ $source ] ) as $key ) {
				$stats['by_source'][ $source ][ $key ] = isset( $bucket[ $key ] ) ? absint( $bucket[ $key ] ) : 0;
			}
		}
		$stats['updated_at'] = isset( $stored['updated_at'] ) ? absint( $stored['updated_at'] ) : 0;

		return $stats;
	}

	/**
	 * Add `accuracy` and `top1_accuracy` to totals.
	 *
	 * @since 7.14.4
	 *
	 * @param array<string,mixed> $stats Totals.
	 *
	 * @return array<string,mixed>
	 */
	private static function with_accuracy( array $stats ): array {
		return array_merge( $stats, self::accuracy( $stats ) );
	}

	/**
	 * Whether a counts array holds anything new.
	 *
	 * @since 7.14.4
	 *
	 * @param array<string,mixed> $counts Counts from `apply()`.
	 *
	 * @return bool
	 */
	private static function has_changes( array $counts ): bool {
		if ( ! empty( $counts['shown'] ) ) {
			return true;
		}
		foreach ( self::outcomes() as $outcome ) {
			if ( ! empty( $counts[ $outcome ] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Normalize a value for storing and comparing: sanitize, trim, cap length.
	 *
	 * @since 7.14.4
	 *
	 * @param string $value Raw value.
	 *
	 * @return string
	 */
	private static function clean_value( string $value ): string {
		// Values arrive unslashed (the request is unslashed once where it is read), so no
		// wp_unslash() here: a second one would strip real backslashes ("Tools\Parts").
		$value = trim( sanitize_text_field( $value ) );
		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, self::MAX_VALUE_LENGTH ) : substr( $value, 0, self::MAX_VALUE_LENGTH );
	}
}
