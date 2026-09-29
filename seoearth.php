<?php
/**
 * Plugin Name:       SEOEarth
 * Plugin URI:        https://wordpress.org/plugins/seoearth/
 * Description:       Search appearance, sitemaps, social metadata, structured data and content analysis for WordPress.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            SEOEarth
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       seoearth
 * Domain Path:       /languages
 *
 * @package SEOEarth
 */

defined( 'ABSPATH' ) || exit;

define( 'SEOEARTH_VERSION', '0.1.0' );
define( 'SEOEARTH_FILE', __FILE__ );
define( 'SEOEARTH_DIR', plugin_dir_path( __FILE__ ) );
define( 'SEOEARTH_URL', plugin_dir_url( __FILE__ ) );

require_once SEOEARTH_DIR . 'src/Autoloader.php';
\SEOEarth\Autoloader::register( SEOEARTH_DIR . 'src/' );

register_activation_hook( __FILE__, array( \SEOEarth\Lifecycle::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( \SEOEarth\Lifecycle::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		$requirements = new \SEOEarth\Requirements( PHP_VERSION, get_bloginfo( 'version' ) );
		if ( ! $requirements->met() ) {
			add_action( 'admin_notices', array( $requirements, 'render_notice' ) );
			return;
		}
		\SEOEarth\Plugin::instance()->boot();
	}
);
