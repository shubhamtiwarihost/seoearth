<?php
/**
 * Uninstall handler.
 *
 * SEOEarth deletes its data on uninstall only when the site owner has opted in
 * through the "Remove all data on uninstall" setting (added in Phase 4). Until
 * then, only the internal version marker is removed.
 *
 * @package SEOEarth
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'seoearth_db_version' );
