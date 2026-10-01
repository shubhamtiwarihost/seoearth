<?php
/**
 * Integration test bootstrap. Loads the real WordPress test suite (provided by wp-env).
 *
 * @package ShubhamTiwariSeoTools
 */

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

$stseo_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $stseo_tests_dir ) {
	$stseo_tests_dir = '/wordpress-phpunit';
}

if ( ! file_exists( $stseo_tests_dir . '/includes/functions.php' ) ) {
	fwrite( STDERR, "WordPress test suite not found in {$stseo_tests_dir}. Run via: npm run test:php:integration\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite, WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI message.
	exit( 1 );
}

define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills' );

require_once $stseo_tests_dir . '/includes/functions.php';

// `npm run test:php:woo` also loads WooCommerce (installed by wp-env) before ShubhamTiwari SEO Tools, like a real site would.
$stseo_with_woo = (bool) getenv( 'STSEO_TEST_WOO' );

tests_add_filter(
	'muplugins_loaded',
	static function () use ( $stseo_with_woo ) {
		if ( $stseo_with_woo ) {
			// wp-env names the folder after the zip (woocommerce.latest-stable); a normal install uses "woocommerce".
			$stseo_woo = glob( WP_PLUGIN_DIR . '/woocommerce*/woocommerce.php' );
			if ( ! $stseo_woo ) {
				fwrite( STDERR, "WooCommerce not found in wp-content/plugins. Run: npm run env:start\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI message.
				exit( 1 );
			}
			require $stseo_woo[0];
		}
		require dirname( __DIR__, 2 ) . '/shubhamtiwari-seo-tools.php';
	}
);

if ( $stseo_with_woo ) {
	tests_add_filter(
		'setup_theme',
		static function () {
			// Creates WooCommerce tables, roles and its shop/cart/checkout/account pages in the test database.
			\WC_Install::install();
		}
	);
}

require $stseo_tests_dir . '/includes/bootstrap.php';
