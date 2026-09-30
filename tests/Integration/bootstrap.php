<?php
/**
 * Integration test bootstrap. Loads the real WordPress test suite (provided by wp-env).
 *
 * @package SEOEarth
 */

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

$seoearth_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $seoearth_tests_dir ) {
	$seoearth_tests_dir = '/wordpress-phpunit';
}

if ( ! file_exists( $seoearth_tests_dir . '/includes/functions.php' ) ) {
	fwrite( STDERR, "WordPress test suite not found in {$seoearth_tests_dir}. Run via: npm run test:php:integration\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite, WordPress.Security.EscapeOutput.OutputNotEscaped -- CLI message.
	exit( 1 );
}

define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills' );

require_once $seoearth_tests_dir . '/includes/functions.php';

// `npm run test:php:woo` also loads WooCommerce (installed by wp-env) before SEOEarth, like a real site would.
$seoearth_with_woo = (bool) getenv( 'SEOEARTH_TEST_WOO' );

tests_add_filter(
	'muplugins_loaded',
	static function () use ( $seoearth_with_woo ) {
		if ( $seoearth_with_woo ) {
			// wp-env names the folder after the zip (woocommerce.latest-stable); a normal install uses "woocommerce".
			$seoearth_woo = glob( WP_PLUGIN_DIR . '/woocommerce*/woocommerce.php' );
			if ( ! $seoearth_woo ) {
				fwrite( STDERR, "WooCommerce not found in wp-content/plugins. Run: npm run env:start\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI message.
				exit( 1 );
			}
			require $seoearth_woo[0];
		}
		require dirname( __DIR__, 2 ) . '/seoearth.php';
	}
);

if ( $seoearth_with_woo ) {
	tests_add_filter(
		'setup_theme',
		static function () {
			// Creates WooCommerce tables, roles and its shop/cart/checkout/account pages in the test database.
			\WC_Install::install();
		}
	);
}

require $seoearth_tests_dir . '/includes/bootstrap.php';
