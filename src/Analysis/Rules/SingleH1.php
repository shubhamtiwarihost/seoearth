<?php
/**
 * H1 headings inside the content.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Analysis\Rules;

use ShubhamTiwariSeoTools\Analysis\Input;
use ShubhamTiwariSeoTools\Analysis\Result;

defined( 'ABSPATH' ) || exit;

/**
 * Themes print the post title as the page's h1. An extra h1 in the content
 * blurs which heading is the main one.
 */
final class SingleH1 extends BaseRule {

	/**
	 * Rule ID.
	 */
	public function id(): string {
		return 'content_h1';
	}

	/**
	 * Only when the content contains an h1.
	 *
	 * @param Input $input Analysis input.
	 */
	public function applies( Input $input ): bool {
		return $input->content->h1_count > 0;
	}

	/**
	 * Runs the check.
	 *
	 * @param Input $input Analysis input.
	 */
	public function check( Input $input ): Result {
		return $this->result(
			Result::WARNING,
			Result::LOW,
			__( 'The text contains a level 1 heading, and most themes already show the title as one.', 'shubhamtiwari-seo-tools' ),
			__( 'Change headings inside the text to level 2 or lower.', 'shubhamtiwari-seo-tools' ),
			array( 'count' => $input->content->h1_count )
		);
	}
}
