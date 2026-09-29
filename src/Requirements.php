<?php
/**
 * Environment requirement checks.
 *
 * @package SEOEarth
 */

namespace SEOEarth;

defined( 'ABSPATH' ) || exit;

/**
 * Compares running PHP/WordPress versions with the plugin minimums.
 */
final class Requirements {

	public const MIN_PHP = '7.4';
	public const MIN_WP  = '6.4';

	/**
	 * Running PHP version.
	 *
	 * @var string
	 */
	private $php_version;

	/**
	 * Running WordPress version.
	 *
	 * @var string
	 */
	private $wp_version;

	/**
	 * Constructor.
	 *
	 * @param string $php_version Running PHP version.
	 * @param string $wp_version  Running WordPress version.
	 */
	public function __construct( string $php_version, string $wp_version ) {
		$this->php_version = $php_version;
		$this->wp_version  = $wp_version;
	}

	/**
	 * Whether PHP meets the minimum.
	 */
	public function php_ok(): bool {
		return version_compare( $this->php_version, self::MIN_PHP, '>=' );
	}

	/**
	 * Whether WordPress meets the minimum.
	 */
	public function wp_ok(): bool {
		return version_compare( $this->wp_version, self::MIN_WP, '>=' );
	}

	/**
	 * Whether all requirements are met.
	 */
	public function met(): bool {
		return $this->php_ok() && $this->wp_ok();
	}

	/**
	 * Admin notice shown when requirements are not met.
	 */
	public function render_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: 1: required PHP version, 2: required WordPress version. */
					__( 'SEOEarth is inactive: it requires PHP %1$s and WordPress %2$s or newer.', 'seoearth' ),
					self::MIN_PHP,
					self::MIN_WP
				)
			)
		);
	}
}
