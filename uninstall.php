<?php
/**
 * Uninstall handler.
 *
 * What is deleted when the plugin is deleted from the Plugins screen:
 *
 * - Always: the internal migration lock (temporary bookkeeping).
 * - Only if the site owner ticked "Remove all SEOEarth data when the plugin is
 *   deleted": the settings option and the data version marker. (Per-post SEO
 *   data is added to this list by later phases.)
 *
 * When the box is not ticked, settings and the version marker are kept so that
 * reinstalling restores the configuration and upgrades it correctly.
 *
 * Posts, pages, media and other content are never deleted.
 *
 * On multisite, each site is handled according to its own setting.
 *
 * @package SEOEarth
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Closure rather than a named function, so this file can be included more than once (tests).
$seoearth_uninstall_site = static function () {
	delete_option( 'seoearth_migration_lock' );

	$settings = get_option( 'seoearth_settings' );
	if ( ! is_array( $settings ) || true !== ( $settings['remove_data_on_uninstall'] ?? false ) ) {
		return;
	}

	delete_option( 'seoearth_settings' );
	delete_option( 'seoearth_db_version' );
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
