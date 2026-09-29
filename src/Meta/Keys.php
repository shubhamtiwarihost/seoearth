<?php
/**
 * Meta keys and settings key helpers.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Meta;

defined( 'ABSPATH' ) || exit;

/**
 * Names used to store per-object SEO data and to address template settings.
 */
final class Keys {

	/**
	 * Custom SEO title (post meta and term meta). Protected (leading underscore).
	 */
	public const TITLE = '_seoearth_title';

	/**
	 * Custom meta description (post meta and term meta).
	 */
	public const DESCRIPTION = '_seoearth_description';

	/**
	 * Custom canonical URL (absolute http/https).
	 */
	public const CANONICAL = '_seoearth_canonical';

	/**
	 * Robots tokens, comma-separated, from Robots::TOKENS.
	 */
	public const ROBOTS = '_seoearth_robots';

	/**
	 * All per-object meta keys, for registration and uninstall.
	 */
	public const ALL = array( self::TITLE, self::DESCRIPTION, self::CANONICAL, self::ROBOTS );

	/**
	 * Turns a post type or taxonomy name into a settings-key-safe fragment.
	 *
	 * Post type names may contain hyphens; settings keys may not.
	 *
	 * @param string $name Post type or taxonomy name.
	 */
	public static function slug( string $name ): string {
		return (string) preg_replace( '/[^a-z0-9_]/', '_', strtolower( $name ) );
	}
}
