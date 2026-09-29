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

tests_add_filter(
	'muplugins_loaded',
	static function () {
		require dirname( __DIR__, 2 ) . '/seoearth.php';
	}
);

require $seoearth_tests_dir . '/includes/bootstrap.php';
