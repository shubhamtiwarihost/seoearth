<?php
/**
 * Uninstall behaviour.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Tests\Integration;

use SEOEarth\Migrations\Migrator;
use WP_UnitTestCase;

/**
 * Runs the real uninstall.php and checks what it removes (and what it keeps).
 */
final class UninstallTest extends WP_UnitTestCase {

	public function test_uninstall_removes_bookkeeping_and_keeps_content(): void {
		$post_id = self::factory()->post->create( array( 'post_title' => 'Keep me' ) );
		update_option( Migrator::VERSION_OPTION, '0.1.0' );
		update_option( Migrator::LOCK_OPTION, time() );

		$this->run_uninstall();

		$this->assertFalse( get_option( Migrator::VERSION_OPTION ) );
		$this->assertFalse( get_option( Migrator::LOCK_OPTION ) );
		$this->assertSame( 'Keep me', get_the_title( $post_id ), 'Uninstall must never delete content.' );
	}

	public function test_uninstall_cleans_every_site_on_multisite(): void {
		if ( ! is_multisite() ) {
			$this->markTestSkipped( 'Multisite only. Run: npm run test:php:multisite' );
		}

		$site_id = self::factory()->blog->create();
		update_option( Migrator::VERSION_OPTION, '0.1.0' );
		switch_to_blog( $site_id );
		update_option( Migrator::VERSION_OPTION, '0.1.0' );
		restore_current_blog();

		$this->run_uninstall();

		$this->assertFalse( get_option( Migrator::VERSION_OPTION ) );
		switch_to_blog( $site_id );
		$this->assertFalse( get_option( Migrator::VERSION_OPTION ) );
		restore_current_blog();
	}

	/**
	 * Includes uninstall.php the way WordPress does.
	 */
	private function run_uninstall(): void {
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', 'seoearth/seoearth.php' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Core constant uninstall.php checks for.
		}
		require dirname( __DIR__, 2 ) . '/uninstall.php';
	}
}
