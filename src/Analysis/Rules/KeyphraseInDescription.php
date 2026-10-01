<?php
/**
 * Keyphrase in the meta description.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Analysis\Rules;

use ShubhamTiwariSeoTools\Analysis\Input;
use ShubhamTiwariSeoTools\Analysis\Result;

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
			return $this->result( Result::PASS, Result::MEDIUM, __( 'The meta description contains the focus keyphrase.', 'shubhamtiwari-seo-tools' ) );
		}
		return $this->result(
			Result::WARNING,
			Result::MEDIUM,
			__( 'The meta description does not contain the focus keyphrase.', 'shubhamtiwari-seo-tools' ),
			__( 'Mention the keyphrase naturally in the meta description.', 'shubhamtiwari-seo-tools' )
		);
	}
}
