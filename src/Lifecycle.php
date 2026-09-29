<?php
/**
 * Activation and deactivation handlers.
 *
 * @package SEOEarth
 */

namespace SEOEarth;

defined( 'ABSPATH' ) || exit;

/**
 * Activation/deactivation. Uninstall lives in uninstall.php.
 */
final class Lifecycle {

	public const VERSION_OPTION = 'seoearth_db_version';

	/**
	 * Runs on activation. Must be idempotent.
	 */
	public static function activate(): void {
		if ( false === get_option( self::VERSION_OPTION ) ) {
			add_option( self::VERSION_OPTION, SEOEARTH_VERSION, '', false );
		}
	}

	/**
	 * Runs on deactivation. Never deletes user data.
	 */
	public static function deactivate(): void {
		// Nothing to clean up yet. Rewrite rules are flushed here once the sitemap module exists.
	}
}
