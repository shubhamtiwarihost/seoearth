<?php
/**
 * Unit test bootstrap. WordPress is NOT loaded; WP functions are mocked with Brain Monkey.
 *
 * @package SEOEarth
 */

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}
if ( ! defined( 'SEOEARTH_VERSION' ) ) {
	define( 'SEOEARTH_VERSION', '0.1.0' );
}
