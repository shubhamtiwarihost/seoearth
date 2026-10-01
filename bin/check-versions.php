<?php
/**
 * Fails when version numbers disagree across the plugin header, the
 * STSEO_VERSION constant, readme.txt "Stable tag", package.json and composer.json.
 *
 * Usage: php bin/check-versions.php
 *
 * @package ShubhamTiwariSeoTools
 */

$stseo_root = dirname( __DIR__ );
$stseo_main = (string) file_get_contents( $stseo_root . '/shubhamtiwari-seo-tools.php' );
$stseo_read = (string) file_get_contents( $stseo_root . '/readme.txt' );
$stseo_pkg  = json_decode( (string) file_get_contents( $stseo_root . '/package.json' ), true );
$stseo_comp = json_decode( (string) file_get_contents( $stseo_root . '/composer.json' ), true );

$stseo_versions = array(
	'plugin header'     => preg_match( '/^\s*\*\s*Version:\s*(\S+)/m', $stseo_main, $m ) ? $m[1] : null,
	'STSEO_VERSION'     => preg_match( "/define\(\s*'STSEO_VERSION',\s*'([^']+)'/", $stseo_main, $m ) ? $m[1] : null,
	'readme Stable tag' => preg_match( '/^Stable tag:\s*(\S+)/mi', $stseo_read, $m ) ? $m[1] : null,
	'package.json'      => $stseo_pkg['version'] ?? null,
	'composer.json'     => $stseo_comp['version'] ?? null,
);

foreach ( $stseo_versions as $stseo_source => $stseo_version ) {
	echo str_pad( $stseo_source, 20 ) . ( $stseo_version ?? '(missing)' ) . PHP_EOL;
}

if ( in_array( null, $stseo_versions, true ) || 1 !== count( array_unique( $stseo_versions ) ) ) {
	fwrite( STDERR, "Version mismatch.\n" );
	exit( 1 );
}

echo "Versions consistent.\n";
