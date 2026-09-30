<?php
/**
 * Shared helpers for rules.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Analysis\Rules;

use SEOEarth\Analysis\Input;
use SEOEarth\Analysis\Result;

defined( 'ABSPATH' ) || exit;

/**
 * Base class: stores the ID and builds results.
 */
abstract class BaseRule implements \SEOEarth\Analysis\Rule {

	/**
	 * Applies whenever a keyphrase is set, unless a rule says otherwise.
	 *
	 * @param Input $input Analysis input.
	 */
	public function applies( Input $input ): bool {
		return $input->has_keyphrase();
	}

	/**
	 * Builds a result for this rule.
	 *
	 * @param string                               $status         Status.
	 * @param string                               $severity       Severity.
	 * @param string                               $message        What was found.
	 * @param string                               $recommendation What to do.
	 * @param array<string, int|float|string|bool> $metadata       Measured values.
	 */
	protected function result( string $status, string $severity, string $message, string $recommendation = '', array $metadata = array() ): Result {
		return new Result( $this->id(), $status, $severity, $message, $recommendation, $metadata );
	}
}
