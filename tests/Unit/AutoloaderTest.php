<?php
/**
 * Tests for the Autoloader.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Tests\Unit;

use SEOEarth\Autoloader;

/**
 * @covers \SEOEarth\Autoloader
 */
final class AutoloaderTest extends TestCase {

	public function test_ignores_foreign_namespaces_and_traversal(): void {
		Autoloader::register( dirname( __DIR__, 2 ) . '/src' );

		Autoloader::load( 'Other\\Thing' );
		Autoloader::load( 'SEOEarth\\..\\..\\etc\\passwd' );

		$this->assertFalse( class_exists( 'Other\\Thing', false ) );
	}
}
