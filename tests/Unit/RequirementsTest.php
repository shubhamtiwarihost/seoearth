<?php
/**
 * Tests for Requirements.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Tests\Unit;

use Brain\Monkey\Functions;
use SEOEarth\Requirements;

/**
 * @covers \SEOEarth\Requirements
 */
final class RequirementsTest extends TestCase {

	/**
	 * Version combinations and whether they meet requirements.
	 *
	 * @return array<string, array{string, string, bool}>
	 */
	public function versions_provider(): array {
		return array(
			'exact minimums' => array( '7.4.0', '6.4', true ),
			'newer'          => array( '8.3.1', '6.8.1', true ),
			'old php'        => array( '7.3.33', '6.8', false ),
			'old wordpress'  => array( '8.2.0', '6.3.5', false ),
			'wp beta suffix' => array( '8.2.0', '6.9-beta1', true ),
		);
	}

	/**
	 * @dataProvider versions_provider
	 *
	 * @param string $php      PHP version.
	 * @param string $wp       WordPress version.
	 * @param bool   $expected Expected result.
	 */
	public function test_met( string $php, string $wp, bool $expected ): void {
		$this->assertSame( $expected, ( new Requirements( $php, $wp ) )->met() );
	}

	public function test_notice_hidden_from_users_who_cannot_activate_plugins(): void {
		Functions\when( 'current_user_can' )->justReturn( false );

		$this->expectOutputString( '' );
		( new Requirements( '7.0', '5.0' ) )->render_notice();
	}

	public function test_notice_is_escaped_and_shown_to_admins(): void {
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( '__' )->returnArg();
		Functions\when( 'esc_html' )->alias(
			static function ( $text ) {
				return htmlspecialchars( $text, ENT_QUOTES );
			}
		);

		$this->expectOutputRegex( '/notice-error.*PHP 7\.4 and WordPress 6\.4/' );
		( new Requirements( '7.0', '5.0' ) )->render_notice();
	}
}
