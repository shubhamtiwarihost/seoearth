<?php
/**
 * Detection of other plugins that print the same tags.
 *
 * @package SEOEarth
 */

namespace SEOEarth\Compatibility;

defined( 'ABSPATH' ) || exit;

/**
 * Detects active plugins known to print Open Graph / X Card tags, so SEOEarth
 * can step aside instead of printing a second set.
 *
 * Detection uses each plugin's public version constant. Only
 * the name is shown to the site owner, on the SEOEarth settings screen.
 */
class Conflicts {

	/**
	 * Display names keyed by the version constant that proves the plugin is active.
	 */
	private const SOCIAL = array(
		'WPSEO_VERSION'             => 'Yoast SEO',
		'RANK_MATH_VERSION'         => 'Rank Math',
		'AIOSEO_VERSION'            => 'All in One SEO',
		'SEOPRESS_VERSION'          => 'SEOPress',
		'THE_SEO_FRAMEWORK_VERSION' => 'The SEO Framework',
		'SLIM_SEO_VER'              => 'Slim SEO',
	);

	/**
	 * Name of an active plugin that prints social tags, or '' when none.
	 */
	public function social_plugin(): string {
		$found = '';
		foreach ( self::SOCIAL as $marker => $name ) {
			if ( defined( $marker ) ) {
				$found = $name;
				break;
			}
		}

		/**
		 * Filters the detected conflicting social-tag plugin. Return '' to force SEOEarth's tags on.
		 *
		 * @param mixed $found Plugin name, or ''. Non-strings are treated as ''.
		 */
		$found = apply_filters( 'seoearth_social_conflict', $found );
		return is_string( $found ) ? $found : '';
	}
}
