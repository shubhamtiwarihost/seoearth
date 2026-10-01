<?php
/**
 * Keyphrase in the first paragraph.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Analysis\Rules;

use ShubhamTiwariSeoTools\Analysis\Input;
use ShubhamTiwariSeoTools\Analysis\Result;

defined( 'ABSPATH' ) || exit;

/**
 * The keyphrase should appear in the first paragraph, so readers (and
 * search engines) see early what the page is about.
 */
final class KeyphraseInIntro extends BaseRule {

	/**
	 * Rule ID.
	 */
	public function id(): string {
		return 'keyphrase_in_intro';
	}

	/**
	 * Only when there is content.
	 *
	 * @param Input $input Analysis input.
	 */
	public function applies( Input $input ): bool {
		return $input->has_keyphrase() && '' !== $input->content->intro;
	}

	/**
	 * Runs the check.
	 *
	 * @param Input $input Analysis input.
	 */
	public function check( Input $input ): Result {
		if ( $input->keyphrase->in( $input->content->intro ) ) {
			return $this->result( Result::PASS, Result::MEDIUM, __( 'The first paragraph contains the focus keyphrase.', 'shubhamtiwari-seo-tools' ) );
		}
		return $this->result(
			Result::WARNING,
			Result::MEDIUM,
			__( 'The first paragraph does not contain the focus keyphrase.', 'shubhamtiwari-seo-tools' ),
			__( 'Use the keyphrase in the opening paragraph.', 'shubhamtiwari-seo-tools' )
		);
	}
}
