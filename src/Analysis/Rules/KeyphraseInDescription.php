<?php
/**
 * Keyphrase in the meta description.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Analysis\Rules;

use SEOEarth\Analysis\Input;
use SEOEarth\Analysis\Result;

defined( 'ABSPATH' ) || exit;

/**
 * The keyphrase should appear in the meta description (search engines often
 * highlight matching words in results).
 */
final class KeyphraseInDescription extends BaseRule {

	/**
	 * Rule ID.
	 */
	public function id(): string {
		return 'keyphrase_in_description';
	}

	/**
	 * Only when there is a description; DescriptionLength reports a missing one.
	 *
	 * @param Input $input Analysis input.
	 */
	public function applies( Input $input ): bool {
		return $input->has_keyphrase() && '' !== $input->description;
	}

	/**
	 * Runs the check.
	 *
	 * @param Input $input Analysis input.
	 */
	public function check( Input $input ): Result {
		if ( $input->keyphrase->in( $input->description ) ) {
			return $this->result( Result::PASS, Result::MEDIUM, __( 'The meta description contains the focus keyphrase.', 'seoearth' ) );
		}
		return $this->result(
			Result::WARNING,
			Result::MEDIUM,
			__( 'The meta description does not contain the focus keyphrase.', 'seoearth' ),
			__( 'Mention the keyphrase naturally in the meta description.', 'seoearth' )
		);
	}
}
