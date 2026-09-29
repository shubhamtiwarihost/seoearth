<?php
/**
 * Uninstall handler.
 *
 * SEOEarth deletes its data on uninstall only when the site owner has opted in
 * through the "Remove all data on uninstall" setting (added with the settings
 * framework). Until then, only internal bookkeeping options are removed.
 *
 * On multisite, each site's options are removed individually.
 *
 * @package SEOEarth
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Closure rather than a named function, so this file can be included more than once (tests).
$seoearth_uninstall_site = static function () {
	delete_option( 'seoearth_db_version' );
	delete_option( 'seoearth_migration_lock' );
};

if ( is_multisite() ) {
	$seoearth_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $seoearth_site_ids as $seoearth_site_id ) {
		switch_to_blog( (int) $seoearth_site_id );
		$seoearth_uninstall_site();
		restore_current_blog();
	}
} else {
	$seoearth_uninstall_site();
}
