<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The public-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    Site_Notices_WP
 * @subpackage Site_Notices_WP/public
 * @author     Alsvin <alsvin.tech@gmail.com>
 */
if( !class_exists('Site_Notices_WP_Public') ) {
	class Site_Notices_WP_Public {

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $plugin_name The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $version The current version of this plugin.
		 */
		private $version;

		private $plugin_title;
		private $setting_menu_slug;
		private $options = array();

		/**
		 * Initialize the class and set its properties.
		 *
		 * @param string $plugin_name The name of this plugin.
		 * @param string $version The version of this plugin.
		 *
		 * @since    1.0.0
		 */
		public function __construct( $plugin_name, $version ) {
			$this->plugin_name = $plugin_name;
			$this->version     = $version;

			$this->plugin_title      = __( 'Site Notices WP', 'alsvin-sn-wp' );
			$this->setting_menu_slug = "{$plugin_name}-settings";
			$this->options           = get_option( $this->setting_menu_slug, array() );

			add_action('wp_footer', [$this, 'wp_footer'] );
		}

		public function wp_footer() {

			$alsvin_site_notices = get_posts(['post_type' => 'alsvin-site-notice', 'numberposts' => -1, 'posts_per_page' => -1, 'meta_key' => '_sn_wp_is_site_wide', 'meta_value' => 'on', 'fields' => 'ids']);

			if( is_array($alsvin_site_notices) && !empty($alsvin_site_notices) ) {
				foreach ($alsvin_site_notices as $alsvin_site_notice_id) {
					echo do_shortcode("[site_notice_wp id=\"{$alsvin_site_notice_id}\"]");
				}
			}
		}
	}
}