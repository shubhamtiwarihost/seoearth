<?php
/**
 * Text sanitizing helpers.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Sanitizers shared by settings, post meta and term meta.
 */
final class Text {

	/**
	 * Stand-in for "%" while core sanitizes. Plain ASCII letters so core leaves it alone.
	 */
	private const PERCENT = 'SEOEARTHPERCENTSIGN';

	/**
	 * Single-line plain text that may contain %%variables%%.
	 *
	 * Core's sanitize_text_field() removes anything that looks like a URL-encoded
	 * octet ("%" followed by two hex digits), which would turn "%%description%%"
	 * into "%scription%%" and "%%date%%" into "%te%%". Percent signs are
	 * protected while core sanitizes, then restored.
	 *
	 * @param mixed $value Raw value.
	 */
	public static function sanitize_line( $value ): string {
		if ( ! is_scalar( $value ) ) {
			return '';
		}
		$protected = str_replace( '%', self::PERCENT, (string) $value );
		return str_replace( self::PERCENT, '%', sanitize_text_field( $protected ) );
	}
}
