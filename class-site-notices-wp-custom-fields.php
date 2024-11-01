<?php
if ( !class_exists('Site_Notices_WP_Post_Fields') ) {

	class Site_Notices_WP_Post_Fields {
		/**
		 * @var  string  $prefix  The prefix for storing post fields in the postmeta table
		 */
		var $prefix = '_sn_wp_';
		/**
		 * @var  array  $postTypes  An array of public custom post types
		 */
		var $postTypes = array( 'alsvin-site-notice' );
		/**
		 * @var  array  $postFields  Defines the custom fields available
		 */

		var $postFields = [];

		/**
		 * PHP 4 Compatible Constructor
		 */
		function Site_Notices_WP_Post_Fields() { $this->__construct(); }
		/**
		 * PHP 5 Constructor
		 */
		function __construct() {
			add_action( 'add_meta_boxes', array( &$this, 'createPostFields' ) );
            add_action( 'save_post', array( &$this, 'savePostFields' ), 1, 2 );
            // Comment this line out if you want to keep default post fields meta box
            add_action( 'do_meta_boxes', array( &$this, 'removeDefaultPostFields' ), 10, 3 );

			$this->postFields = array(
				array(
					"name"          => "message",
					"title"         => __('Message'),
					"description"   => "",
					"type"          => "textarea",
					"scope"         =>   array( 'alsvin-site-notice' ),
					"capability"    => "edit_pages"
				),
				array(
					"name"          => "notice_anime",
					"title"         => __('Animation'),
					"description"   => "",
					"type"          => "radio",
					"scope"         =>   array( 'alsvin-site-notice' ),
					"capability"    => "edit_pages",
					'options' => array(
						'fade' => __('Fade'),
						'slide' => __('Slide')
					)
				),
				array(
					"name"          => "notice_type",
					"title"         => __('Color Scheme'),
					"description"   => "Click on \"Select Color\" box to change the color",
					"type"          => "color_scheme",
					"scope"         =>   array( 'alsvin-site-notice' ),
					"capability"    => "edit_pages",
					'options' => array(
						'success' => __('Success'),
						'info' => __('Info'),
						'warning' => __('Warning'),
						'error' => __('Error')
					)
				),
				array(
					"name"          => "position",
					"title"         => __('Position'),
					"description"   => "",
					"type"          => "dropdown",
					"scope"         =>   array( 'alsvin-site-notice' ),
					"capability"    => "edit_pages",
					'options' => array(
						'toast-top-full-width'      => __('Top Full', 'alsvin-sn-wp'),
						'toast-top-right'           => __('Top Right', 'alsvin-sn-wp'),
						'toast-top-center'          => __('Top Center', 'alsvin-sn-wp'),
						'toast-top-left'            => __('Top Left', 'alsvin-sn-wp'),
						'toast-bottom-full-width'   => __('Bottom Full', 'alsvin-sn-wp'),
						'toast-bottom-right'        => __('Bottom Right', 'alsvin-sn-wp'),
						'toast-bottom-center'       => __('Bottom Center', 'alsvin-sn-wp'),
						'toast-bottom-left'         => __('Bottom Left', 'alsvin-sn-wp'),
					)
				),
				array(
					"name"          => "show_title",
					"title"         => "Show Title?",
					"description"   => "If checked, title will be displayed for this notice",
					"type"          => "checkbox",
					"scope"         =>   array( 'alsvin-site-notice' ),
					"capability"    => "manage_options"
				),
				array(
					"name"          => "auto_hide",
					"title"         => "Auto Hide?",
					"description"   => "If checked, notification will hide automatically after 5 seconds",
					"type"          => "checkbox",
					"scope"         =>   array( 'alsvin-site-notice' ),
					"capability"    => "manage_options"
				),
				array(
					"name"          => "is_site_wide",
					"title"         => "Is site wide?",
					"description"   => "If checked, notification will be displayed on entire website",
					"type"          => "checkbox",
					"scope"         =>   array( 'alsvin-site-notice' ),
					"capability"    => "manage_options"
				),
				array(
					"name"          => "can_hide",
					"title"         => "Can hide?",
					"description"   => "If checked, there will be \"x\" button available to hide or close the notification",
					"type"          => "checkbox",
					"scope"         =>   array( 'alsvin-site-notice' ),
					"capability"    => "manage_options"
				),
				array(
					"name"          => "duration",
					"title"         => "Duration",
					"description"   => "Keep the notification hidden for the specified duration after user hide/close the notification",
					"placeholder"   => "Set it to \"0\" to hide the notification for infinite time",
					"default"       => "0",
					"type"          => "number",
					"scope"         => array( 'alsvin-site-notice' ),
					"capability"    => "manage_options"
				),
				array(
					"name"          => "duration_type",
					"title"         => "Duration Type",
					"description"   => "Specify the type of duration i.e. Minutes/Hours/Days",
					"type"          => "dropdown",
					"scope"         => array( 'alsvin-site-notice' ),
					"capability"    => "manage_options",
					'options' => array(
						'min' => __('Minute'),
						'hour' => __('Hour'),
						'day' => __('Day'),
					)
				)
            );
        }
		/**
		 * Remove the default Post Fields meta box
		 */
		function removeDefaultPostFields( $type, $context, $post ) {
			foreach ( array( 'normal', 'advanced', 'side' ) as $context ) {
				foreach ( $this->postTypes as $postType ) {
					remove_meta_box( 'postcustom', $postType, $context );
				}
			}
		}
		/**
		 * Create the new Post Fields meta box
		 */
		function createPostFields() {
			if ( function_exists( 'add_meta_box' ) ) {
				foreach ( $this->postTypes as $postType ) {
					add_meta_box( 'site-notices-wp-fields', 'Notice Details', array( &$this, 'displayPostFields' ), $postType, 'normal', 'high' );
                }
			}
		}
		/**
		 * Display the new Post Fields meta box
		 */
		function displayPostFields() {
			global $post;
			?>
			<div class="form-wrap">
				<?php
				wp_nonce_field( 'site-notices-wp-fields', 'site-notices-wp-fields_wpnonce', false, true );
				foreach ( $this->postFields as $postField ) {
					// Check scope
					$scope = $postField[ 'scope' ];
					$output = false;
					foreach ( $scope as $scopeItem ) {
						switch ( $scopeItem ) {
							default: {
								if ( $post->post_type == $scopeItem )
									$output = true;
								break;
							}
						}
						if ( $output ) break;
					}
					// Check capability
					if ( !current_user_can( $postField['capability'], $post->ID ) )
						$output = false;
					// Output if allowed
					if ( $output ) { ?>
						<div class="form-field form-required sn_wp_field_<?php echo $postField['name']; ?>">
							<?php
							$field_key = $this->prefix . $postField['name'];

							switch ( $postField[ 'type' ] ) {
								case "checkbox": {
									// Checkbox
                                    $selected_value = get_post_meta( $post->ID, $field_key, true );
									?>
                                    <label for="<?php echo esc_html($field_key); ?>">
                                        <?php echo sprintf('<input type="checkbox" id="%s" name="%s" %s>', $field_key, $field_key, checked($selected_value == 'on', true, false)); ?>
                                        <strong><?php echo esc_html($postField[ 'title' ]); ?></strong>
                                    </label>
                                    <?php
									break;
								}
								case "editor": {
									echo '<label for="' . esc_html($field_key) .'"><strong>' . esc_html($postField[ 'title' ]) . '</strong></label>';
									$args = array(
										'tinymce'       => array(
											'toolbar1'      => 'bold,italic,underline,separator,alignleft,aligncenter,alignright,separator,link,unlink,undo,redo',
											'toolbar2'      => '',
											'toolbar3'      => '',
										),
									);
									wp_editor( get_post_meta( $post->ID, $field_key, true ) , $field_key, $args);
									break;
                                }
								case "textarea": {
									// Text area
									echo '<label for="' . esc_html($field_key) .'"><strong>' . esc_html($postField[ 'title' ]) . '</strong></label>';
									echo '<textarea name="' . esc_html($field_key) .'" id="' . esc_html($field_key) .'" columns="30" rows="3">' . htmlspecialchars( get_post_meta( $post->ID, $field_key, true ) ) . '</textarea>';
									// WYSIWYG
									if ( $postField[ 'type' ] == "wysiwyg" ) { ?>
										<script type="text/javascript">
                                            jQuery( document ).ready( function() {
                                                jQuery( "<?php echo esc_html($field_key); ?>" ).addClass( "mceEditor" );
                                                if ( typeof( tinyMCE ) == "object" && typeof( tinyMCE.execCommand ) == "function" ) {
                                                    tinyMCE.execCommand( "mceAddControl", false, "<?php echo esc_html($field_key); ?>" );
                                                }
                                            });
										</script>
									<?php }
									break;
								}
								case 'radio': {
									$selected_option = get_post_meta( $post->ID, $field_key, true );
									if(empty($selected_option)) {
										$selected_option = 'fade';
                                    }
									// Text area
									echo '<label for="' . esc_html($field_key) .'"><strong>' . esc_html($postField[ 'title' ]) . '</strong></label>';
									echo '<div id="' . esc_html($field_key) .'">';
									if( is_array($postField[ 'options' ]) ) {
									    foreach ($postField[ 'options' ] as $option_value => $option_name) {
									        ?>
                                            <label><input type="radio" name="<?php echo esc_html($field_key); ?>" value="<?php echo esc_html($option_value); ?>" <?php checked($option_value, $selected_option, true); ?>> <?php echo esc_html($option_name); ?></label>
                                            <?php
                                        }
                                    }
									echo '</div>';
									break;
								}
								case 'dropdown': {
									$selected_option = get_post_meta( $post->ID, $field_key, true );
									// Text area
									echo '<label for="' . esc_html($field_key) .'"><strong>' . esc_html($postField[ 'title' ]) . '</strong></label>';
									echo '<select name="' . esc_html($field_key) .'" id="' . esc_html($field_key) .'">';
									if( is_array($postField[ 'options' ]) ) {
									    foreach ($postField[ 'options' ] as $option_value => $option_name) {
									        ?>
                                            <option value="<?php echo esc_html($option_value); ?>" <?php selected($option_value, $selected_option, true); ?>><?php echo esc_html($option_name); ?></option>
                                            <?php
                                        }
                                    }
									echo '</select>';
									break;
								}
                                case 'color_scheme': {
                                    $selected_option = get_post_meta( $post->ID, $field_key, true );
                                    if(empty($selected_option)) {
                                        $selected_option = 'success';
                                    }
                                    // Text area
                                    echo '<label for="' . esc_html($field_key) .'"><strong>' . esc_html($postField[ 'title' ]) . '</strong></label>';
                                    echo '<div id="' . esc_html($field_key) .'">';
                                    ?>
                                        <input type="hidden" name="<?php echo esc_html($field_key); ?>" value="<?php echo esc_html($selected_option); ?>">
                                    <?php
                                    $option_colors = [
                                        'success' => '#51A351',
                                        'error' => '#BD362F',
                                        'info' => '#2F96B4',
                                        'warning' => '#F89406',
                                    ];

                                    $selected_color = get_post_meta( $post->ID, "{$this->prefix}notice_color", true );
                                    if(empty($selected_color)) {
                                        $selected_color = $option_colors[$selected_option];
                                    }

                                    echo '<ul class="toast-samples">';
                                    if( is_array($postField[ 'options' ]) ) {
                                        foreach ($postField[ 'options' ] as $option_value => $option_name) {
                                        ?>

                                <li>
                                    <div class="toast-container sn-wp-toast-sample <?php echo ($selected_option === $option_value) ? 'active' : ''; ?>" data-notice-type="<?php echo esc_html($option_value); ?>" data-notice-color="<?php echo $option_colors[$option_value]; ?>">
                                        <div class="toast toast-<?php echo esc_html($option_value); ?>"><?php echo esc_html($option_name); ?></div>
                                    </div>
                                </li>

                                        <?php
                                        }
                                    }
                                    ?>
                                <li>
                                    <input type="text" value="<?php echo $selected_color; ?>" id="<?php echo "{$this->prefix}notice_color"; ?>" name="<?php echo "{$this->prefix}notice_color"; ?>" data-default-color="#51A351" />
                                </li>

								<?php
                                    echo '</ul>';
                                    echo '</div>';
                                    break;
                                }
								case "number": {
									// Number text field
									$value = get_post_meta( $post->ID, $field_key, true );
									if( empty($value) && isset($postField[ 'default' ]) ) {
										$value = $postField[ 'default' ];
									}
									echo '<label for="' . esc_html($field_key) .'"><strong>' . esc_html($postField[ 'title' ]) . '</strong></label>';
									echo '<input type="number" name="' . esc_html($field_key) .'" id="' . esc_html($field_key) .'" value="' . htmlspecialchars( $value ) . '" placeholder="' . esc_html($postField[ 'placeholder' ]) . '" min="0" />';
									break;
                                }
								default: {
									// Plain text field
									$value = get_post_meta( $post->ID, $field_key, true );
									if( empty($value) && isset($postField[ 'default' ]) ) {
										$value = $postField[ 'default' ];
									}
									echo '<label for="' . esc_html($field_key) .'"><strong>' . esc_html($postField[ 'title' ]) . '</strong></label>';
									echo '<input type="text" name="' . esc_html($field_key) .'" id="' . esc_html($field_key) .'" value="' . htmlspecialchars( $value ) . '" placeholder="' . esc_html($postField[ 'placeholder' ]) . '" />';
									break;
								}
							}
							?>
							<?php if ( $postField[ 'description' ] ) echo '<p>' . esc_html($postField[ 'description' ]) . '</p>'; ?>
						</div>

						<?php
					}
				} ?>
                <div>
                    <button class="button button-secondary sn-wp-preview-button"><?php _e('Preview Notice');?></button>
                </div>
			</div>
			<?php
		}
		/**
		 * Save the new Post Fields values
		 */
		function savePostFields( $post_id, $post ) {
			if ( !isset( $_POST[ 'site-notices-wp-fields_wpnonce' ] ) || !wp_verify_nonce( sanitize_text_field($_POST[ 'site-notices-wp-fields_wpnonce' ]), 'site-notices-wp-fields' ) )
				return;
			if ( !current_user_can( 'edit_post', $post_id ) )
				return;
			if ( ! in_array( $post->post_type, $this->postTypes ) )
				return;
			foreach ( $this->postFields as $postField ) {
				if ( current_user_can( $postField['capability'], $post_id ) ) {
					$field_key = $this->prefix . $postField['name'];
					if ( isset( $_POST[ $field_key ] ) && $_POST[ $field_key ] ) {
						// Auto-paragraphs for any WYSIWYG
					    if ( $postField['type'] == "editor" ) {
							$value = wpautop( wp_filter_post_kses($_POST[ $field_key ]) );
						} else {
							$value = sanitize_text_field($_POST[ $field_key ]);
						}

						update_post_meta( $post_id, $field_key, $value );
					} else {
						delete_post_meta( $post_id, $field_key );
					}
                }
			}

			//Save notice_color field
			$field_key = "{$this->prefix}notice_color";
			$value = $value = sanitize_text_field($_POST[ $field_key ]);
			update_post_meta( $post_id, $field_key, $value );

		}

	} // End Class

	new Site_Notices_WP_Post_Fields();

} // End if class exists statement