<?php
/**
 * Tests for the Plugin module registry.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Tests\Unit;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use SEOEarth\Module;
use SEOEarth\Plugin;

/**
 * @covers \SEOEarth\Plugin
 */
final class PluginTest extends TestCase {

	protected function tear_down() {
		Plugin::reset();
		parent::tear_down();
	}

	public function test_boot_registers_only_modules_that_should_load(): void {
		$active   = $this->module( true );
		$inactive = $this->module( false );

		$active->expects( $this->once() )->method( 'register' );
		$inactive->expects( $this->never() )->method( 'register' );

		Filters\expectApplied( 'seoearth_modules' )->once()->andReturn(
			array(
				'active'   => $active,
				'inactive' => $inactive,
				'bogus'    => new \stdClass(),
			)
		);
		Actions\expectDone( 'seoearth_loaded' )->once();

		Plugin::instance()->boot();

		$this->assertSame( $active, Plugin::instance()->module( 'active' ) );
		$this->assertNull( Plugin::instance()->module( 'inactive' ) );
		$this->assertNull( Plugin::instance()->module( 'bogus' ) );
	}

	public function test_boot_runs_only_once(): void {
		Filters\expectApplied( 'seoearth_modules' )->once()->andReturn( array() );

		Plugin::instance()->boot();
		Plugin::instance()->boot();

		$this->addToAssertionCount( 1 );
	}

	/**
	 * Creates a mock module.
	 *
	 * @param bool $should_load Return value of should_load().
	 * @return Module&\PHPUnit\Framework\MockObject\MockObject
	 */
	private function module( bool $should_load ) {
		$module = $this->createMock( Module::class );
		$module->method( 'should_load' )->willReturn( $should_load );
		return $module;
	}
}
