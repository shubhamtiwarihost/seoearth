<?php
/**
 * Template functions for themes.
 *
 * @package ShubhamTiwariSeoTools
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'stseo_get_breadcrumbs' ) ) {
	/**
	 * Breadcrumb HTML for the current page ('' on the homepage, or when ShubhamTiwari SEO Tools is not running).
	 *
	 * Usage in a theme: `if ( function_exists( 'stseo_breadcrumbs' ) ) { stseo_breadcrumbs(); }`
	 */
	function stseo_get_breadcrumbs(): string {
		$module = \ShubhamTiwariSeoTools\Plugin::instance()->module( 'breadcrumbs' );
		return $module instanceof \ShubhamTiwariSeoTools\Breadcrumbs\BreadcrumbsModule ? $module->html() : '';
	}
}

if ( ! function_exists( 'stseo_breadcrumbs' ) ) {
	/**
	 * Prints the breadcrumbs for the current page.
	 */
	function stseo_breadcrumbs(): void {
		echo stseo_get_breadcrumbs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by Breadcrumbs\Renderer, which escapes every value.
	}
}
