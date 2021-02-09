<?php
/**
 * Plugin Name:     Zoom for WordPress
 * Description:     Sell virtual Zoom webinars with WordPress
 * Version:         1.0.0
 * Author:          Seattle Web Co.
 * Author URI:      https://seattlewebco.com
 * Text Domain:     wp-zoom
 * Domain Path:     /languages/
 * Contributors:    seattlewebco, dkjensen
 * Requires PHP:    7.2.0
 *
 * @package SeattleWebCo\WPZoom
 */

namespace SeattleWebCo\WPZoom;

define( 'WP_ZOOM_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_ZOOM_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_ZOOM_VER', function_exists( 'get_plugin_data' ) ? get_plugin_data( __FILE__ )['Version'] : '1.0.0' );
define( 'WP_ZOOM_DB_VER', '1.0.0' );
define( 'WP_ZOOM_BASE', __FILE__ );

require_once 'vendor/autoload.php';

require_once 'includes/wp-zoom-enqueue-scripts.php';
require_once 'includes/wp-zoom-api-functions.php';
require_once 'includes/wp-zoom-markup-functions.php';
require_once 'includes/wp-zoom-helper-functions.php';

require_once 'includes/integrations/wp-zoom-load-integrations.php';

if ( defined( 'WP_ZOOM_CLIENT_ID' ) && defined( 'WP_ZOOM_CLIENT_SECRET' ) ) {
	$wp_zoom_provider = new \SeattleWebCo\WPZoom\Provider\Zoom(
		array(
			'redirectUri'   => admin_url( 'admin.php?page=wc-settings&tab=integration&section=wp_zoom&wp-zoom-oauth' ),
			'timeout'       => 30,
			'clientId'      => constant( 'WP_ZOOM_CLIENT_ID' ),
			'clientSecret'  => constant( 'WP_ZOOM_CLIENT_SECRET' ),
		)
	);
} else {
	$wp_zoom_provider = new \SeattleWebCo\WPZoom\Provider\ZoomForWp(
		array(
			'redirectUri' => admin_url( 'admin.php?page=wc-settings&tab=integration&section=wp_zoom' ),
			'timeout'     => 30,
		)
	);
}

$GLOBALS['wp_zoom'] = new Api( $wp_zoom_provider );

/**
 * Activation hook
 */
function wp_zoom_activation() {
	if ( version_compare( PHP_VERSION, '7.2.0', '<' ) ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die(
			esc_html__( 'This plugin requires a minimum PHP version of 7.2.0', 'wp-zoom' ),
			esc_html__( 'Plugin activation error', 'wp-zoom' ),
			array(
				'response'  => 200,
				'back_link' => true,
			)
		);
	}

	delete_option( 'rewrite_rules' );

	flush_rewrite_rules();
}
register_activation_hook( __FILE__, '\\SeattleWebCo\\WPZoom\\wp_zoom_activation' );
