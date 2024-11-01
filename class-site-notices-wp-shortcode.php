<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

if( !class_exists('Site_Notices_WP_Shortcode') ) {

	/**
	 * Class Site_Notices_WP_Shortcode
	 */
    class Site_Notices_WP_Shortcode {

	    /**
		 * Hook into the appropriate actions when the class is constructed.
		 */
		public function __construct($plugin_name, $version) {

			$this->plugin_name = $plugin_name;
			$this->version = $version;

			add_action( 'plugins_loaded', array( $this, 'init_shortcode' ) );
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		}

	    public function init_shortcode() {
		    add_shortcode( 'site_notice_wp', array( $this, 'site_notice_wp_render' ) );
	    }

	    public function site_notice_wp_render($atts) {
		    $args = wp_parse_args( $atts, array(
			    'id' => 0
		    ) );

		    $args = apply_filters( 'site_notice_wp_atts', $args );

		    if ( array_key_exists( 'id', $args ) ) {

			    $id = absint( $args['id'] );

			    if( $id > 0 ) {
//				    ob_start();
				    $this->render_shortcode($id);
//				    return ob_get_clean();
			    }
		    }
	    }

	    public function render_shortcode($id) {

		    $notice = get_post($id);
		    if ( 'alsvin-site-notice' != $notice->post_type ) {
			    return;
		    }

		    $duration = absint(get_post_meta($notice->ID, '_sn_wp_duration', true));

		    $cookie_name = 'sn-wp-hide-' . $notice->ID;

		    if( isset($_COOKIE[$cookie_name]) && !empty($duration) ) { //Ignore cookie if duration is empty
		        if(empty($duration)) {
			        setcookie($cookie_name, time() - 3600); //Delete cookie if exists
                }
			    return;
		    }

		    $option_colors = [
			    'success' => '#51A351',
			    'error' => '#BD362F',
			    'info' => '#2F96B4',
			    'warning' => '#F89406',
		    ];
		    $notice_type = get_post_meta($notice->ID, '_sn_wp_notice_type', true);
		    $notice_color = get_post_meta($notice->ID, '_sn_wp_notice_color', true);
		    if(empty($notice_color)) {
			    $notice_color = $option_colors[$notice_type];
            }

		    wp_enqueue_style($this->plugin_name . '-toastr');
		    wp_enqueue_script($this->plugin_name . '-toastr');
		    wp_enqueue_script($this->plugin_name . '-jquery-cookie');
		    ob_start();
		    ?>
            <script type="text/javascript">


                (function( $ ) {

                    //Append class to head
                    function sn_wp_createClass(class_name,rules){
                        if( $('#'+class_name).length === 0 ) {

                             $('<style>')
                                .prop('type', 'text/css')
                                .prop('id', class_name)
                                .html( '.' + class_name + ' {' + rules + '}' )
                                .appendTo('head');
                        }
                    }

                    $(function() {
                        var cookie_name = '<?php echo $cookie_name; ?>';
                        let post_ID = '<?php echo absint($id); ?>';
                        let title = '<?php echo esc_html($notice->post_title); ?>';
                        let message = '<?php echo esc_html(get_post_meta($notice->ID, '_sn_wp_message', true)); ?>';
                        let notice_type = '<?php echo $notice_type; ?>';
                        let notice_color = '<?php echo $notice_color; ?>';
                        let position = 'toast-container ' + '<?php echo get_post_meta($notice->ID, '_sn_wp_position', true); ?>';
                        let show_title = '<?php echo get_post_meta($notice->ID, '_sn_wp_show_title', true); ?>';
                        let notice_anime = '<?php echo get_post_meta($notice->ID, '_sn_wp_notice_anime', true); ?>';
                        let auto_hide = '<?php echo get_post_meta($notice->ID, '_sn_wp_auto_hide', true); ?>';
                        let can_hide = '<?php echo get_post_meta($notice->ID, '_sn_wp_can_hide', true); ?>';
                        let duration = <?php echo $duration; ?>;
                        let duration_type = '<?php echo get_post_meta($notice->ID, '_sn_wp_duration_type', true); ?>';

                        var class_name  = 'toast-custom-' + post_ID;

                        var min_in_seconds = 60;

                        if( duration_type === 'min' ) {
                            var seconds = duration * min_in_seconds;
                        } else if( duration_type === 'hour' ) {
                            var seconds = duration * 60 * min_in_seconds;
                        } else if( duration_type === 'day' ) {
                            var seconds = duration * 24 * 60 * min_in_seconds;
                        }

                        if( ! title.trim().length && ! message.trim().length ) {
                            return;
                        }

                        sn_wp_createClass(class_name,'background-color: ' + notice_color + ' !important;');

                        if(show_title!=='on') {
                            title = null;
                        }

                        //setTimeout(function() {
                        toastr.options = {
                            positionClass: position || 'toast-container toast-top-right',
                            closeButton: can_hide,
                            tapToDismiss: can_hide,
                            showDuration:300,
                            hideDuration:1000,
                            progressBar: auto_hide,
                            timeOut: auto_hide ? 5000 : 0,
                            extendedTimeOut: auto_hide ? 1000 : 0,
                            showMethod: 'fadeIn',
                            hideMethod: 'fadeOut',
                            closeMethod: 'fadeOut',
                            containerId: 'toast-container-' + post_ID,
                            closeHtml: '<span>&times;</span>'
                        };

                        toastr.options.onHidden = function() {
                            if ( !Cookies.get(cookie_name) ) {
                                var date = new Date();
                                date.setTime(date.getTime() + (seconds * 1000)); //add seconds to current date-time 1s = 1000ms
                                Cookies.set(cookie_name, true, { expires: date, path: '' });
                            }
                        }

                        if(notice_anime === 'slide') {
                            toastr.options.showMethod = 'slideDown';
                            toastr.options.hideMethod = 'slideUp';
                            toastr.options.closeMethod = 'slideUp';
                        }

                        toastr[notice_type](message, title, {iconClass: 'toast-' + notice_type + ' ' + class_name}); // Wire up an event handler to a button in the toast, if it exists
                        //}, 0);
                    });

                })( jQuery );
            </script>
		    <?php
            $script = ob_get_contents();
            ob_clean();
		    add_action( 'wp_footer', function() use( $script ){
			    echo $script;
		    }, 21);
	    }

		/**
		 * Adds the meta box container.
		 */
		public function add_meta_box( $post_type ) {
			// Limit meta box to certain post types.
			$post_types = array( 'alsvin-site-notice' );

			if ( in_array( $post_type, $post_types ) ) {
				add_meta_box(
					'sn_wp_shortcode',
					__( 'Shortcode' ),
					array( $this, 'render_meta_box_content' ),
					$post_type,
					'side',
					'high'
				);
			}
		}

		/**
		 * Render Meta Box content.
		 *
		 * @param WP_Post $post The post object.
		 */
		public function render_meta_box_content( $post ) {
			$shortcode = "[site_notice_wp id=\"{$post->ID}\"]";
			?>
			<code class="sn-wp-code code"><?php echo esc_html($shortcode); ?></code> <a href="#" class="sn-wp-copy-code" data-before-text="<?php _e('Copy'); ?>" data-after-text="<?php _e('Copied'); ?>"><?php _e('Copy'); ?></a>
            <p><?php esc_html_e( __( 'Click "Copy" and paste this shortcode to any post or page.', 'alsvin-sn-wp' ) )?></p>
			<?php
		}
	}
}
