<?php
/**
 * Plugin Name:       ShubhamTiwari SEO Tools
 * Description:       Search appearance, sitemaps, social metadata, structured data and content analysis for WordPress.
 * Version:           1.0.1
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Shubham Tiwari
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       shubhamtiwari-seo-tools
 * Domain Path:       /languages
 *
 * @package ShubhamTiwariSeoTools
 */

defined( 'ABSPATH' ) || exit;

define( 'STSEO_VERSION', '1.0.1' );
define( 'STSEO_FILE', __FILE__ );
define( 'STSEO_DIR', plugin_dir_path( __FILE__ ) );
define( 'STSEO_URL', plugin_dir_url( __FILE__ ) );

require_once STSEO_DIR . 'src/Autoloader.php';
\ShubhamTiwariSeoTools\Autoloader::register( STSEO_DIR . 'src/' );
require_once STSEO_DIR . 'src/functions.php';

register_activation_hook( __FILE__, array( \ShubhamTiwariSeoTools\Lifecycle::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( \ShubhamTiwariSeoTools\Lifecycle::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		$requirements = new \ShubhamTiwariSeoTools\Requirements( PHP_VERSION, get_bloginfo( 'version' ) );
		if ( ! $requirements->met() ) {
			// Prints on the Plugins screen only (see Requirements::render_notice()).
			add_action( 'admin_notices', array( $requirements, 'render_notice' ) );
			add_action( 'network_admin_notices', array( $requirements, 'render_notice' ) );
			return;
		}
		\ShubhamTiwariSeoTools\Plugin::instance()->boot();
	}
);
