<?php
/**
 * Whether search engines may index the page.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Analysis\Rules;

use ShubhamTiwariSeoTools\Analysis\Input;
use ShubhamTiwariSeoTools\Analysis\Result;

defined( 'ABSPATH' ) || exit;

/**
 * Points out that the other checks matter little while the page is noindex.
 */
final class Indexable extends BaseRule {

	/**
	 * Rule ID.
	 */
	public function id(): string {
		return 'indexable';
	}

	/**
	 * Only for noindex pages.
	 *
	 * @param Input $input Analysis input.
	 */
	public function applies( Input $input ): bool {
		return $input->noindex;
	}

	/**
	 * Runs the check.
	 *
	 * @param Input $input Analysis input.
	 */
	public function check( Input $input ): Result {
		return $this->result(
			Result::INFO,
			Result::HIGH,
			__( 'This page is hidden from search engines (noindex), so it will not appear in search results.', 'shubhamtiwari-seo-tools' ),
			__( 'If that is not intended, change the page’s search engine visibility.', 'shubhamtiwari-seo-tools' )
		);
	}
}
