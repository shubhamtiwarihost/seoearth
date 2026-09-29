<?php
/**
 * Smoke tests against a real WordPress install.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Tests\Integration;

use SEOEarth\Lifecycle;
use SEOEarth\Plugin;
use WP_UnitTestCase;

/**
 * Verifies the plugin boots inside WordPress.
 */
final class PluginLoadsTest extends WP_UnitTestCase {

	public function test_plugin_constants_and_boot(): void {
		$this->assertTrue( defined( 'SEOEARTH_VERSION' ) );
		$this->assertSame( 1, did_action( 'seoearth_loaded' ) );
		$this->assertInstanceOf( Plugin::class, Plugin::instance() );
	}

	public function test_activation_is_idempotent_and_not_autoloaded(): void {
		delete_option( Lifecycle::VERSION_OPTION );

		Lifecycle::activate();
		Lifecycle::activate();

		$this->assertSame( SEOEARTH_VERSION, get_option( Lifecycle::VERSION_OPTION ) );

		global $wpdb;
		$autoload = $wpdb->get_var( $wpdb->prepare( "SELECT autoload FROM {$wpdb->options} WHERE option_name = %s", Lifecycle::VERSION_OPTION ) );
		$this->assertContains( $autoload, array( 'no', 'off' ) );
	}
}
