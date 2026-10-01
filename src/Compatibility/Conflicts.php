<?php
/**
 * Detection of other plugins that print the same tags.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Compatibility;

defined( 'ABSPATH' ) || exit;

/**
 * Detects active plugins known to print Open Graph / X Card tags or schema.org
 * JSON-LD, so ShubhamTiwari SEO Tools can step aside instead of printing a second set.
 *
 * Detection uses each plugin's public version constant. Only
 * the name is shown to the site owner, on the ShubhamTiwari SEO Tools settings screen.
 */
class Conflicts {

	/**
	 * Display names keyed by the version constant that proves the plugin is active.
	 * Every plugin listed prints both social tags and a JSON-LD graph.
	 */
	private const SEO_PLUGINS = array(
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
		/**
		 * Filters the detected conflicting social-tag plugin. Return '' to force ShubhamTiwari SEO Tools' tags on.
		 *
		 * @param mixed $found Plugin name, or ''. Non-strings are treated as ''.
		 */
		$found = apply_filters( 'stseo_social_conflict', $this->active_seo_plugin() );
		return is_string( $found ) ? $found : '';
	}

	/**
	 * Name of an active plugin that prints schema.org JSON-LD, or '' when none.
	 */
	public function schema_plugin(): string {
		/**
		 * Filters the detected conflicting structured-data plugin. Return '' to force ShubhamTiwari SEO Tools' schema on.
		 *
		 * @param mixed $found Plugin name, or ''. Non-strings are treated as ''.
		 */
		$found = apply_filters( 'stseo_schema_conflict', $this->active_seo_plugin() );
		return is_string( $found ) ? $found : '';
	}

	/**
	 * Name of the first known SEO plugin that is active, or ''.
	 */
	private function active_seo_plugin(): string {
		foreach ( self::SEO_PLUGINS as $marker => $name ) {
			if ( defined( $marker ) ) {
				return $name;
			}
		}
		return '';
	}
}
