<?php
/**
 * One analysis check.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Analysis;

defined( 'ABSPATH' ) || exit;

/**
 * A single SEO check. Rules are registered through `seoearth_analysis_rules`,
 * read only from Input, and must not query the database.
 */
interface Rule {

	/**
	 * Stable ID, e.g. "keyphrase_in_title".
	 */
	public function id(): string;

	/**
	 * Whether the rule has anything to say for this input.
	 *
	 * @param Input $input Analysis input.
	 */
	public function applies( Input $input ): bool;

	/**
	 * Runs the check.
	 *
	 * @param Input $input Analysis input.
	 */
	public function check( Input $input ): Result;
}
