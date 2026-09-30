<?php
/**
 * Keyphrase in the SEO title.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Analysis\Rules;

use SEOEarth\Analysis\Input;
use SEOEarth\Analysis\Result;

defined( 'ABSPATH' ) || exit;

/**
 * The keyphrase should appear in the SEO title, ideally near the start.
 */
final class KeyphraseInTitle extends BaseRule {

	/**
	 * Rule ID.
	 */
	public function id(): string {
		return 'keyphrase_in_title';
	}

	/**
	 * Runs the check.
	 *
	 * @param Input $input Analysis input.
	 */
	public function check( Input $input ): Result {
		$position = $input->keyphrase->position_in( $input->title );
		$meta     = array( 'position' => $position );

		if ( $position < 0 ) {
			return $this->result(
				Result::ERROR,
				Result::HIGH,
				__( 'The SEO title does not contain the focus keyphrase.', 'seoearth' ),
				__( 'Add the keyphrase to the SEO title.', 'seoearth' ),
				$meta
			);
		}
		if ( $position > 0 && mb_strlen( $input->title, 'UTF-8' ) > 0 && $position > mb_strlen( $input->title, 'UTF-8' ) / 2 ) {
			return $this->result(
				Result::WARNING,
				Result::LOW,
				__( 'The focus keyphrase is in the second half of the SEO title.', 'seoearth' ),
				__( 'Move the keyphrase towards the start of the title, where it is less likely to be cut off.', 'seoearth' ),
				$meta
			);
		}
		return $this->result( Result::PASS, Result::HIGH, __( 'The SEO title contains the focus keyphrase.', 'seoearth' ), '', $meta );
	}
}
