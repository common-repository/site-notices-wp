<?php
/**
 * Plugin Name:       Site Notices WP
 * Plugin URI:        https://www.alsvin-tech.com/
 * Description:       A simple plugin to display site wide notices, alerts and info messages
 * Version:           1.1.3
 * Requires at least: 5.1
 * Requires PHP:      7.3
 * Author:            Alsvin
 * Author URI:        https://profiles.wordpress.org/alsvin/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       alsvin-sn-wp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ALSVIN_SN_WP_VERSION', '1.1.3' );
define( 'ALSVIN_SN_WP_FILE', __FILE__ );

function activate_site_notices_wp() {

}

function deactivate_site_notices_wp() {

}

register_activation_hook( __FILE__, 'activate_site_notices_wp' );
register_deactivation_hook( __FILE__, 'deactivate_site_notices_wp' );
require plugin_dir_path( __FILE__ ) . 'class-site-notices-wp-custom-fields.php';
require plugin_dir_path( __FILE__ ) . 'class-site-notices-wp-admin.php';
require plugin_dir_path( __FILE__ ) . 'class-site-notices-wp-public.php';
require plugin_dir_path( __FILE__ ) . 'class-site-notices-wp-shortcode.php';


function run_site_notices_wp() {

	new Site_Notices_WP_Admin('alsvin-sn-wp', ALSVIN_SN_WP_VERSION);
	new Site_Notices_WP_Public('alsvin-sn-wp', ALSVIN_SN_WP_VERSION);
	new Site_Notices_WP_Shortcode('alsvin-sn-wp', ALSVIN_SN_WP_VERSION);

}

run_site_notices_wp();