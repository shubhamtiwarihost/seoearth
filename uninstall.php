<?php
/**
 * Uninstall handler.
 *
 * What is deleted when the plugin is deleted from the Plugins screen:
 *
 * - Always: the internal migration lock (temporary bookkeeping).
 * - Only if the site owner ticked "Remove all ShubhamTiwari SEO Tools data when the plugin is
 *   deleted": the settings option, the data version marker, and the SEO
 *   fields saved on posts and terms (_stseo_title, _stseo_description,
 *   _stseo_canonical, _stseo_robots, _stseo_social_title,
 *   _stseo_social_description, _stseo_social_image, _stseo_focus_keyphrase),
 *   and all redirects (stseo_redirect posts and the stseo_redirect_index option).
 *
 * When the box is not ticked, settings and the version marker are kept so that
 * reinstalling restores the configuration and upgrades it correctly.
 *
 * Posts, pages, media and other content are never deleted.
 *
 * On multisite, each site is handled according to its own setting.
 *
 * @package ShubhamTiwariSeoTools
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Closure rather than a named function, so this file can be included more than once (tests).
$stseo_uninstall_site = static function () {
	delete_option( 'stseo_migration_lock' );

	$settings = get_option( 'stseo_settings' );
	if ( ! is_array( $settings ) || true !== ( $settings['remove_data_on_uninstall'] ?? false ) ) {
		return;
	}

	delete_option( 'stseo_settings' );
	delete_option( 'stseo_db_version' );

	// Per-post and per-term SEO fields. delete_all removes the key from every object in one query.
	delete_option( 'stseo_redirect_index' );
	$stseo_redirects = get_posts(
		array(
			'post_type'      => 'stseo_redirect',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( $stseo_redirects as $stseo_redirect ) {
		wp_delete_post( (int) $stseo_redirect, true );
	}

	foreach ( array( '_stseo_title', '_stseo_description', '_stseo_canonical', '_stseo_robots', '_stseo_social_title', '_stseo_social_description', '_stseo_social_image', '_stseo_focus_keyphrase' ) as $meta_key ) {
		delete_metadata( 'post', 0, $meta_key, '', true );
		delete_metadata( 'term', 0, $meta_key, '', true );
	}
};

if ( is_multisite() ) {
	$stseo_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $stseo_site_ids as $stseo_site_id ) {
		switch_to_blog( (int) $stseo_site_id );
		$stseo_uninstall_site();
		restore_current_blog();
	}
} else {
	$stseo_uninstall_site();
}
