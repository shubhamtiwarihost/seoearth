<?php
/**
 * Keyphrase in subheadings.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Analysis\Rules;

use SEOEarth\Analysis\Input;
use SEOEarth\Analysis\Result;

defined( 'ABSPATH' ) || exit;

/**
 * At least one subheading (h2–h6) should mention the keyphrase. Only for
 * texts that have subheadings.
 */
final class KeyphraseInSubheadings extends BaseRule {

	/**
	 * Rule ID.
	 */
	public function id(): string {
		return 'keyphrase_in_subheadings';
	}

	/**
	 * Only when the text has subheadings.
	 *
	 * @param Input $input Analysis input.
	 */
	public function applies( Input $input ): bool {
		return $input->has_keyphrase() && array() !== $input->content->subheadings;
	}

	/**
	 * Runs the check.
	 *
	 * @param Input $input Analysis input.
	 */
	public function check( Input $input ): Result {
		$matches = 0;
		foreach ( $input->content->subheadings as $heading ) {
			if ( $input->keyphrase->in( $heading ) ) {
				++$matches;
			}
		}
		$meta = array(
			'matching'    => $matches,
			'subheadings' => count( $input->content->subheadings ),
		);
		if ( $matches > 0 ) {
			return $this->result( Result::PASS, Result::LOW, __( 'A subheading contains the focus keyphrase.', 'seoearth' ), '', $meta );
		}
		return $this->result(
			Result::WARNING,
			Result::LOW,
			__( 'No subheading contains the focus keyphrase.', 'seoearth' ),
			__( 'Use the keyphrase (or close wording) in at least one subheading.', 'seoearth' ),
			$meta
		);
	}
}
