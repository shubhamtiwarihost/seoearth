<?php
/**
 * Template functions for themes.
 *
 * @package SEOEarth
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'seoearth_get_breadcrumbs' ) ) {
	/**
	 * Breadcrumb HTML for the current page ('' on the homepage, or when SEOEarth is not running).
	 *
	 * Usage in a theme: `if ( function_exists( 'seoearth_breadcrumbs' ) ) { seoearth_breadcrumbs(); }`
	 */
	function seoearth_get_breadcrumbs(): string {
		$module = \SEOEarth\Plugin::instance()->module( 'breadcrumbs' );
		return $module instanceof \SEOEarth\Breadcrumbs\BreadcrumbsModule ? $module->html() : '';
	}
}

if ( ! function_exists( 'seoearth_breadcrumbs' ) ) {
	/**
	 * Prints the breadcrumbs for the current page.
	 */
	function seoearth_breadcrumbs(): void {
		echo seoearth_get_breadcrumbs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by Breadcrumbs\Renderer, which escapes every value.
	}
}
