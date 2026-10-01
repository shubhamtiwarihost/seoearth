<?php
/**
 * Unit test bootstrap. WordPress is NOT loaded; WP functions are mocked with Brain Monkey.
 *
 * @package ShubhamTiwariSeoTools
 */

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}
if ( ! defined( 'STSEO_VERSION' ) ) {
	define( 'STSEO_VERSION', '0.1.0' );
	define( 'STSEO_FILE', dirname( __DIR__, 2 ) . '/shubhamtiwari-seo-tools.php' );
	define( 'STSEO_DIR', dirname( __DIR__, 2 ) . '/' );
	define( 'STSEO_URL', 'https://example.org/wp-content/plugins/shubhamtiwari-seo-tools/' );
}

if ( ! class_exists( 'WP_Screen' ) ) {
	// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Stands in for a WordPress core class in unit tests, which run without WordPress.
	/**
	 * Minimal stand-in for core's WP_Screen (only the property the plugin reads).
	 */
	final class WP_Screen {

		/**
		 * Screen ID.
		 *
		 * @var string
		 */
		public $id = '';
	}
}
