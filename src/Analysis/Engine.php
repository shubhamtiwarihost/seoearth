<?php
/**
 * Runs the analysis rules.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Analysis;

defined( 'ABSPATH' ) || exit;

/**
 * Runs every applicable rule and summarises the results.
 *
 * There is deliberately no numeric score: the checks are editorial guidance,
 * and a score would suggest a ranking effect nobody can promise. The summary
 * counts results per status instead.
 */
class Engine {

	/**
	 * Status order, worst first.
	 */
	private const ORDER = array( Result::ERROR, Result::WARNING, Result::INFO, Result::PASS );

	/**
	 * Built-in rules keyed by ID.
	 *
	 * @return array<string, Rule>
	 */
	public static function default_rules(): array {
		$rules = array(
			new Rules\KeyphraseSet(),
			new Rules\KeyphraseInTitle(),
			new Rules\KeyphraseInDescription(),
			new Rules\KeyphraseInSlug(),
			new Rules\KeyphraseInIntro(),
			new Rules\KeyphraseDensity(),
			new Rules\KeyphraseInSubheadings(),
			new Rules\KeyphraseUnique(),
			new Rules\TitleLength(),
			new Rules\DescriptionLength(),
			new Rules\ContentLength(),
			new Rules\InternalLinks(),
			new Rules\OutboundLinks(),
			new Rules\ImageAlt(),
			new Rules\SingleH1(),
			new Rules\Indexable(),
		);
		$by_id = array();
		foreach ( $rules as $rule ) {
			$by_id[ $rule->id() ] = $rule;
		}
		return $by_id;
	}

	/**
	 * Analyses an input.
	 *
	 * @param Input $input Analysis input.
	 * @return array{status: string, counts: array<string, int>, results: array<int, array{id: string, status: string, severity: string, message: string, recommendation: string, metadata: array<string, int|float|string|bool>}>}
	 */
	public function run( Input $input ): array {
		$rules = self::default_rules();
		if ( function_exists( 'apply_filters' ) ) {
			/**
			 * Filters the analysis rules. Entries that are not Rule instances are ignored.
			 *
			 * @param array<string, mixed> $rules Rules keyed by ID.
			 * @param Input                $input Analysis input.
			 */
			$rules = apply_filters( 'seoearth_analysis_rules', $rules, $input );
		}

		$results = array();
		foreach ( (array) $rules as $rule ) {
			if ( $rule instanceof Rule && $rule->applies( $input ) ) {
				$results[] = $rule->check( $input );
			}
		}

		// Worst first, then most important first; stable for equal keys.
		$severity = array( Result::HIGH, Result::MEDIUM, Result::LOW );
		$keyed    = array();
		foreach ( $results as $index => $result ) {
			$keyed[] = array( array_search( $result->status, self::ORDER, true ), array_search( $result->severity, $severity, true ), $index, $result );
		}
		sort( $keyed );

		$counts = array_fill_keys( self::ORDER, 0 );
		$sorted = array();
		foreach ( $keyed as $entry ) {
			$result = $entry[3];
			++$counts[ $result->status ];
			$sorted[] = $result->to_array();
		}

		$status = Result::PASS;
		foreach ( self::ORDER as $candidate ) {
			if ( Result::INFO !== $candidate && $counts[ $candidate ] > 0 ) {
				$status = $candidate;
				break;
			}
		}

		return array(
			'status'  => $status,
			'counts'  => $counts,
			'results' => $sorted,
		);
	}
}
