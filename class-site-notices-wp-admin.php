<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Site_Notices_WP
 * @subpackage Site_Notices_WP/admin
 * @author     Alsvin <alsvin.tech@gmail.com>
 */
if( !class_exists('Site_Notices_WP_Admin') ) {
	class Site_Notices_WP_Admin {
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
		 * @since    1.0.0
		 * @param      string $plugin_name The name of this plugin.
		 * @param      string $version The version of this plugin.
		 */
		public function __construct($plugin_name, $version)
		{
			$this->plugin_name = $plugin_name;
			$this->version = $version;

			$this->plugin_title = __('Site Notices WP', 'alsvin-sn-wp');
			$this->setting_menu_slug = "{$plugin_name}-settings";
			$this->options = get_option( $this->setting_menu_slug, array() );

			add_action( 'init', [$this, 'register_scripts'], 0 );
			add_action('admin_enqueue_scripts', [$this, 'enqueue_styles'] );
			add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
			add_action( 'init', [$this, 'register_post_type'], 0 );
			add_action( 'admin_notices', [$this, 'plugin_review_notice'] );
		}

		public function register_scripts() {
			wp_register_style($this->plugin_name . '-toastr', plugin_dir_url(__FILE__) . 'css/toastr.css', array(), $this->version, 'all');
			wp_register_script($this->plugin_name . '-toastr', plugin_dir_url(__FILE__) . 'js/toastr.js', array('jquery'), $this->version, true);
			wp_register_script($this->plugin_name . '-jquery-cookie', plugin_dir_url(__FILE__) . 'js/js.cookie.min.js', array('jquery'), $this->version, true);

		}



		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles()
		{
			wp_enqueue_style($this->plugin_name . '-toastr');
			wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/site-notices-wp-admin.css', array(), $this->version, 'all');

		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts()
		{
			if ( 'alsvin-site-notice' == get_post_type() ) {
				wp_dequeue_script( 'autosave' ); //Disable autosave
			}

			wp_enqueue_script($this->plugin_name . '-toastr');
			wp_enqueue_script($this->plugin_name . '-jquery-cookie');
//			if(is_admin()) {
//				wp_enqueue_style( 'wp-color-picker' );
//			}
			wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/site-notices-wp-admin.js', array('jquery','wp-color-picker'), $this->version, true);
		}

		/**
         * Creating a function to create our CPT
         */
		function register_post_type() {

			// Set UI labels for Custom Post Type
			$labels = array(
				'name'                => _x( 'Notices', 'Post Type General Name' ),
				'singular_name'       => _x( 'Notice', 'Post Type Singular Name' ),
				'menu_name'           => __( 'Notices' ),
				'parent_item_colon'   => __( 'Parent Notice', 'alsvin-sn-wp' ),
				'all_items'           => __( 'All Notices', 'alsvin-sn-wp' ),
				'view_item'           => __( 'View Notice', 'alsvin-sn-wp' ),
				'add_new_item'        => __( 'Add New Notice', 'alsvin-sn-wp' ),
				'add_new'             => __( 'Add New' ),
				'edit_item'           => __( 'Edit Notice', 'alsvin-sn-wp' ),
				'update_item'         => __( 'Update Notice', 'alsvin-sn-wp' ),
				'search_items'        => __( 'Search Notice', 'alsvin-sn-wp' ),
				'not_found'           => __( 'Not Found' ),
				'not_found_in_trash'  => __( 'Not found in Trash' ),
			);

            // Set other options for Custom Post Type

			$args = array(
				'label'               => __( 'notices' ),
				'description'         => __( 'An easy way to show site notices', 'alsvin-sn-wp' ),
				'labels'              => $labels,
				// Features this CPT supports in Post Editor
				'supports'            => array( 'title', 'description', 'author' ),
				// You can associate this CPT with a taxonomy or custom taxonomy.
				'taxonomies'          => array( 'site_notice_category' ),
                'hierarchical'        => false,
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'menu_position'       => 5,
				'menu_icon'           => 'dashicons-info',
				'can_export'          => true,
				'has_archive'         => false,
				'exclude_from_search' => false,
				'capability_type'     => 'post',
				'show_in_rest' => true,

			);

			// Registering Custom Post Type
			register_post_type( 'alsvin-site-notice', $args );
		}

		/**
		 * Plugin Review Notice
		 */
		public function plugin_review_notice() {

			if( !current_user_can('manage_options') || ! is_admin() || ! is_plugin_active(  plugin_basename ( ALSVIN_SN_WP_FILE ) ) ) {
				return;
			}

			$user_id = get_current_user_id();
			$review_dismissed_key = $this->plugin_name . '_review_dismissed_' . $user_id;
			$review_dismissed_action_key = $this->plugin_name . '_dismiss_notice';

			if( isset( $_GET[$review_dismissed_action_key] ) ) {
				set_transient($review_dismissed_key, 1, MONTH_IN_SECONDS);
			}

			// Show review notice where needed
			global $pagenow;

			$is_better_place_to_show_notice = ( ( $pagenow == 'post.php' || $pagenow == 'post-new.php' || $pagenow == 'edit.php' ) && get_post_type() === 'alsvin-site-notice' );

			if( $is_better_place_to_show_notice ) {
				$user_data = get_userdata( get_current_user_id() );
				$review_dismissed = get_transient($review_dismissed_key);
				$dismiss_url = add_query_arg( $review_dismissed_action_key, 1 );

				if( ! function_exists('get_plugin_data') ){
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}

				$plugin_data = get_plugin_data( ALSVIN_SN_WP_FILE );

				$message = __('Hey %s, Thank you for using <strong>%s</strong>. Please give us a review <a href="%s" target="_blank">here</a> if you like our plugin, or submit a support ticket <a href="%s" target="_blank">here</a> to report any issue or suggestion.', 'alsvin-sn-wp');
				$message = sprintf( $message, esc_html( $user_data->user_nicename ), $plugin_data['Name'], esc_url('https://wordpress.org/support/plugin/site-notices-wp/reviews/'), esc_url('https://wordpress.org/support/plugin/site-notices-wp/') );

				$message_html =  sprintf(__('<div class="notice notice-info alsvin-review-notice" style="padding-right: 38px; position: relative;">
						<p>%s</p>
					<button type="button" class="notice-dismiss"  onclick="location.href=\'%s\';"><span class="screen-reader-text">%s</span></button></div>', 'alsvin-sn-wp'), $message, $dismiss_url, __('Dismiss this notice.', 'alsvin-sn-wp'));

				if ( ! $review_dismissed ) {
					echo $message_html;
				}
			}
		}
	}
}