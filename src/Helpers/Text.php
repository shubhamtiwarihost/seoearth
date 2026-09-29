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

	/**
	 * Empty string, or an absolute http(s) URL with a host. Returns null for
	 * anything else (javascript:, data:, relative, protocol-relative, ftp:).
	 *
	 * @param mixed $value Raw URL.
	 */
	public static function http_url( $value ): ?string {
		if ( ! is_scalar( $value ) ) {
			return null;
		}
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return '';
		}

		$scheme = wp_parse_url( $value, PHP_URL_SCHEME );
		$host   = wp_parse_url( $value, PHP_URL_HOST );
		if ( ! is_string( $scheme ) || ! in_array( strtolower( $scheme ), array( 'http', 'https' ), true ) || ! is_string( $host ) || '' === $host ) {
			return null;
		}

		$clean = esc_url_raw( $value, array( 'http', 'https' ) );
		return '' === $clean ? null : $clean;
	}
}
