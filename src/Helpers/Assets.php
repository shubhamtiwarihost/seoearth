<?php
/**
 * Built script manifests.
 *
 * @package ShubhamTiwariSeoTools
 */

namespace ShubhamTiwariSeoTools\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Reads the index.asset.php files that `npm run build` writes into build/.
 * A checkout that was never built has none; callers then skip the script.
 */
final class Assets {

	/**
	 * Dependencies and version of a built script, or null when it is not built.
	 *
	 * @param string $dir Directory below build/, e.g. "editor" or "blocks/breadcrumbs".
	 * @return array{dependencies: string[], version: string, url: string}|null
	 */
	public static function manifest( string $dir ): ?array {
		$file = STSEO_DIR . 'build/' . $dir . '/index.asset.php';
		if ( ! is_readable( $file ) ) {
			return null;
		}
		$asset = require $file;
		if ( ! is_array( $asset ) ) {
			return null;
		}
		return array(
			'dependencies' => array_values( array_filter( (array) ( $asset['dependencies'] ?? array() ), 'is_string' ) ),
			'version'      => is_string( $asset['version'] ?? null ) ? $asset['version'] : STSEO_VERSION,
			'url'          => STSEO_URL . 'build/' . $dir . '/',
		);
	}
}
