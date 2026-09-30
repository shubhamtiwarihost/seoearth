<?php
/**
 * Meta description length.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Analysis\Rules;

use SEOEarth\Analysis\Input;
use SEOEarth\Analysis\Result;

defined( 'ABSPATH' ) || exit;

/**
 * Length of the meta description. Around 120–160 characters are shown in
 * most search results; without one, search engines pick text themselves.
 */
final class DescriptionLength extends BaseRule {

	public const MIN = 120;
	public const MAX = 160;

	/**
	 * Rule ID.
	 */
	public function id(): string {
		return 'description_length';
	}

	/**
	 * Always applies.
	 *
	 * @param Input $input Analysis input.
	 */
	public function applies( Input $input ): bool {
		return true;
	}

	/**
	 * Runs the check.
	 *
	 * @param Input $input Analysis input.
	 */
	public function check( Input $input ): Result {
		$length = mb_strlen( $input->description, 'UTF-8' );
		$meta   = array(
			'length' => $length,
			'min'    => self::MIN,
			'max'    => self::MAX,
		);
		if ( 0 === $length ) {
			return $this->result(
				Result::WARNING,
				Result::MEDIUM,
				__( 'The page has no meta description.', 'seoearth' ),
				__( 'Write a short summary; otherwise search engines choose a snippet from the page themselves.', 'seoearth' ),
				$meta
			);
		}
		/* translators: %d: number of characters. */
		$found = sprintf( _n( 'The meta description is %d character long.', 'The meta description is %d characters long.', $length, 'seoearth' ), $length );
		if ( $length < self::MIN ) {
			return $this->result( Result::WARNING, Result::LOW, $found, __( 'There is room to say a little more about the page.', 'seoearth' ), $meta );
		}
		if ( $length > self::MAX ) {
			return $this->result( Result::WARNING, Result::LOW, $found, __( 'Shorten it so the end is not cut off in search results.', 'seoearth' ), $meta );
		}
		return $this->result( Result::PASS, Result::LOW, $found, '', $meta );
	}
}
