<?php
/**
 * Fails when version numbers disagree across the plugin header, the
 * SEOEARTH_VERSION constant, readme.txt "Stable tag" and package.json.
 *
 * Usage: php bin/check-versions.php
 *
 * @package SEOEarth
 */

$seoearth_root = dirname( __DIR__ );
$seoearth_main = (string) file_get_contents( $seoearth_root . '/seoearth.php' );
$seoearth_read = (string) file_get_contents( $seoearth_root . '/readme.txt' );
$seoearth_pkg  = json_decode( (string) file_get_contents( $seoearth_root . '/package.json' ), true );

$seoearth_versions = array(
	'plugin header'     => preg_match( '/^\s*\*\s*Version:\s*(\S+)/m', $seoearth_main, $m ) ? $m[1] : null,
	'SEOEARTH_VERSION'  => preg_match( "/define\(\s*'SEOEARTH_VERSION',\s*'([^']+)'/", $seoearth_main, $m ) ? $m[1] : null,
	'readme Stable tag' => preg_match( '/^Stable tag:\s*(\S+)/mi', $seoearth_read, $m ) ? $m[1] : null,
	'package.json'      => $seoearth_pkg['version'] ?? null,
);

foreach ( $seoearth_versions as $seoearth_source => $seoearth_version ) {
	echo str_pad( $seoearth_source, 20 ) . ( $seoearth_version ?? '(missing)' ) . PHP_EOL;
}

if ( in_array( null, $seoearth_versions, true ) || 1 !== count( array_unique( $seoearth_versions ) ) ) {
	fwrite( STDERR, "Version mismatch.\n" );
	exit( 1 );
}

echo "Versions consistent.\n";
