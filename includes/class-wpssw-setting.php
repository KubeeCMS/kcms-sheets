<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

/**
 * Class WPSSW_Setting.
 */
class WPSSW_Setting {
	/**
	 * Plugin documentation URL
	 *
	 * @var $documentation
	 */
	protected static $documentation = 'https://docs.wpsyncsheets.com/wpssw-introduction/';
	/**
	 * Plugin sheet setting URL
	 *
	 * @var $doc_sheet_setting
	 */
	protected static $doc_sheet_setting = 'https://docs.wpsyncsheets.com/wpssw-google-sheets-api-settings/';
	/**
	 * Plugin sheet allowtocopy setting URL
	 *
	 * @var $doc_sheet_setting_allowtocopy
	 */
	protected static $doc_sheet_setting_allowtocopy = 'https://docs.wpsyncsheets.com/wpssw-plugin-settings/#allowtocopy';
	/**
	 * Plugin support URL
	 *
	 * @var $submit_ticket
	 */
	protected static $submit_ticket = 'https://support.wpsyncsheets.com/index.php/signup?plugin=wpssw';
	/**
	 * Default status of post
	 *
	 * @var $wpssw_default_status
	 */
	protected static $wpssw_default_status = array( 'pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed' );
	/**
	 * Plugin License Page URL
	 *
	 * @var $wpssw_license_page
	 */
	protected static $wpssw_license_page = 'wpsyncsheets-for-woocommerce&tab=em-settings';
	/**
	 * Register Link
	 *
	 * @var $wpssw_register_link
	 */
	protected static $wpssw_register_link = 'https://www.wpsyncsheets.com/checkout/?edd_action=add_to_cart&download_id=1378&discount=ENVATO100';

	/**
	 * Plugin Store URL
	 *
	 * @var $wpssw_store_url
	 */
	protected static $wpssw_store_url = WPSSW_STORE_URL;
	/**
	 * Default status slug of post
	 *
	 * @var $wpssw_default_status_slug
	 */
	protected static $wpssw_default_status_slug = array( 'wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed' );
	/**
	 * Global headers
	 *
	 * @var $wpssw_global_headers
	 */
	protected static $wpssw_global_headers = array();
	/**
	 * Instance of WPSSW_Google_API_Functions
	 *
	 * @var $instance_api
	 */
	protected static $instance_api = null;
	/**
	 * Initialization
	 */
	public static function init() {
		$wpssw_include = new WPSSW_Include_Action();
		$wpssw_include->wpssw_include_plugin_hook();
		self::wpssw_google_api();
		self::wpssw_license()->init();
	}
	/**
	 * LICENSE functions
	 */
	public static function wpssw_license() {
		return WPSSW_License::instance();
	}
	/**
	 * UPDATE functions
	 */
	public static function wpssw_updater() {
		return new WPSSW_Plugin_Update();
	}

	/**
	 * Plugin Activation Hook
	 */
	public static function wpssw_activation() {
		self::wpssw_update_option( 'active_wpssw', 1 );
	}

	/**
	 * Plugin Deactivation Hook
	 */
	public static function wpssw_deactivation() {
		self::wpssw_update_option( 'active_wpssw', '' );
	}
	/**
	 * Register a plugin menu page.
	 */
	public static function wpssw_menu_page() {
		global $admin_page_hooks, $_parent_pages;
		if ( ! isset( $admin_page_hooks['wpsyncsheets'] ) ) {
			$wpssg_page = add_menu_page(
				esc_attr__( 'WPSyncSheets', 'wpssw' ),
				'WPSyncSheets',
				'manage_options',
				'wpsyncsheets',
				'',
				WPSSW_URL . 'assets/images/menu-icon.svg',
				90
			);
		}
		add_submenu_page( 'wpsyncsheets', 'Google Sheets API Settings', 'Google Sheets API Settings', 'manage_options', 'wpsyncsheets', __CLASS__ . '::wpssw_plugin_page' );
		add_submenu_page( 'wpsyncsheets', 'WPSyncSheets For WooCommerce', 'For WooCommerce', 'manage_options', 'wpsyncsheets-for-woocommerce', __CLASS__ . '::wpssw_plugin_page' );
		if ( ! isset( $_parent_pages['documentation'] ) ) {
			add_submenu_page( 'wpsyncsheets', 'Documentation', '<div class="wpssw-support">Documentation</div>', 'manage_options', 'documentation', __CLASS__ . '::wppsw_handle_external_redirects', 99 );
		}
		if ( ! isset( $_parent_pages['support'] ) ) {
			add_submenu_page( 'wpsyncsheets', 'Support', '<div class="wpssw-support">Support</div>', 'manage_options', 'support', __CLASS__ . '::wppsw_handle_external_redirects', 99 );
		}
		self::remove_duplicate_submenu_page();
	}

	/**
	 * License Key admin notice.
	 */
	public static function wpssw_license_notice() {
		$enable = self::wpssw_check_license_key();
		if ( ! $enable ) {
			echo '
			<div class="notice notice-info">
				<p><strong>' . esc_html__( 'Welcome to WPSyncSheets', 'wpssw' ) . '</strong></p>
				<p>' . esc_html__( 'Please', 'wpssw' ) . ' <a href="' . esc_url( 'admin.php?page=' . self::$wpssw_license_page ) . '">' . esc_html__( 'register', 'wpssw' ) . '</a> ' . esc_html__( 'this version of plugin to get an access for auto updates.', 'wpssw' ) . '</p>
				<p><strong>' . esc_html__( 'Important!', 'wpssw' ) . '</strong> ' . esc_html__( 'One', 'wpssw' ) . ' <a target="_blank" href="https://codecanyon.net/licenses/standard">' . esc_html__( 'standard license', 'wpssw' ) . '</a> ' . esc_html__( 'is valid only for', 'wpssw' ) . ' <strong>' . esc_html__( '1 website', 'wpssw' ) . '</strong>. ' . esc_html__( 'Running multiple websites on a single license is a copyright violation.', 'wpssw' ) . '</p>
			</div>
			';
		}
	}
	/**
	 * Documentation and Support Page Link.
	 *
	 * Redirect the documentation and support page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public static function wppsw_handle_external_redirects() {
		// phpcs:ignore
		if ( empty( $_GET['page'] ) ) {
			return;
		}
		// phpcs:ignore
		if ( 'documentation' === $_GET['page'] ) {
			// phpcs:ignore
			wp_redirect( WPSSW_DOC_MENU_URL );
			die;
		}
		// phpcs:ignore
		if ( 'support' === $_GET['page'] ) {
			// phpcs:ignore
			wp_redirect( WPSSW_SUPPORT_MENU_URL );
			die;
		}
	}
	/**
	 * Create Google Api Instance.
	 */
	public static function wpssw_google_api() {
		if ( null === self::$instance_api ) {
			self::$instance_api = new \WPSSW_Google_API_Functions();
		}
		return self::$instance_api;
	}
	/**
	 * Remove duplicate submenu
	 * Submenu page hack: Remove the duplicate WPSyncSheets Plugin link on subpages
	 */
	public static function remove_duplicate_submenu_page() {
		remove_submenu_page( 'wpsyncsheets', 'wpsyncsheets' );
	}
	/**
	 * Loads the plugin language files.
	 * Load only the wpssw translation.
	 */
	public static function wpssw_load_textdomain() {
		load_plugin_textdomain( 'wpssw', false, WPSSW_DIRECTORY . '/languages/' );
	}
	/**
	 * Enqueue CSS and JavaScript files
	 */
	public static function wpssw_load_custom_wp_admin_style() {
		// phpcs:ignore
		if ( isset( $_GET['page'] ) && ( 'wpsyncsheets-for-woocommerce' === (string) sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) ) {
			wp_register_script( 'wpssw-wp-admin-ui', WPSSW_URL . 'assets/js/wpssw-ui.js', array(), WPSSW_VERSION, true );
			wp_enqueue_script( 'wpssw-wp-admin-ui' );
			wp_register_script( 'wpssw-wp-admin', WPSSW_URL . 'assets/js/wpssw-admin-script.js', array(), WPSSW_VERSION, true );
			wp_localize_script(
				'wpssw-wp-admin',
				'admin_ajax_object',
				array(
					'ajaxurl'          => admin_url( 'admin-ajax.php' ),
					'sync_nonce_token' => wp_create_nonce( 'sync_nonce' ),
				)
			);
			wp_enqueue_script( 'wpssw-wp-admin' );
			wp_register_style( 'wpssw-wp-admin-style', WPSSW_URL . 'assets/css/wpssw-admin-style.css', false, WPSSW_VERSION );
			wp_enqueue_style( 'wpssw-wp-admin-style' );
			wp_register_style( 'wpssw-wp-admin-ui', WPSSW_URL . 'assets/css/wpssw-ui.css', false, WPSSW_VERSION );
			wp_enqueue_style( 'wpssw-wp-admin-ui' );
		}
		global $post_type;
		// phpcs:ignore
		if ( ( isset( $_GET['post'] ) && 'shop_order' === (string) $post_type ) || ( isset( $_GET['post_type'] ) && ( 'shop_order' === (string) sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) ) ) {
			wp_register_script( 'wpssw-wp-general', WPSSW_URL . 'assets/js/wpssw-general-script.js', array(), WPSSW_VERSION, true );
			wp_localize_script(
				'wpssw-wp-general',
				'admin_ajax_object',
				array(
					'ajaxurl'          => admin_url( 'admin-ajax.php' ),
					'sync_nonce_token' => wp_create_nonce( 'sync_nonce' ),
				)
			);
			wp_enqueue_script( 'wpssw-wp-general' );
			wp_register_style( 'wpssw-wp-general-style', WPSSW_URL . 'assets/css/wpssw-general-style.css', false, WPSSW_VERSION );
			wp_enqueue_style( 'wpssw-wp-general-style' );
		}
		wp_register_script( 'wpssw-general', WPSSW_URL . 'assets/js/wpssw-general.js', array(), WPSSW_VERSION, true );
		wp_enqueue_script( 'wpssw-general' );
	}
	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $wpssw_links Plugin Row Meta.
	 * @param mixed $wpssw_file  Plugin Base file.
	 * @return  array
	 */
	public static function wpssw_plugin_row_meta( $wpssw_links, $wpssw_file ) {
		if ( 'wpsyncsheets-for-woocommerce/wpsyncsheets-for-woocommerce.php' === (string) $wpssw_file ) {
			$wpssw_row_meta = array(
				'docs' => '<a href="' . self::$documentation . '" title="' . esc_attr( __( 'View Documentation', 'wpssw' ) ) . '" target="_blank">' . __( 'View Documentation', 'wpssw' ) . '</a>',
			);
			return array_merge( $wpssw_links, $wpssw_row_meta );
		}
		return (array) $wpssw_links;
	}
	/**
	 * Show wpssw plugin screen.
	 */
	public static function wpssw_plugin_page() {
		$wpssw_error           = '';
		$wpssw_error_general   = '';
		$wpssw_apisettings     = '';
		$wpssw_generalsettings = '';
		$wpssw_emsettings      = '';
		$wpssw_supportsettings = '';

		$disbledbtn = '';
		$enable     = self::wpssw_check_license_key();
		if ( ! $enable || ( isset( $_GET['tab'] ) && ! $enable && 'em-settings' !== (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) ) {
			$_GET['tab'] = 'em-settings';
		}

		// General Settings Tab.
		if ( isset( $_GET['tab'] ) && 'general-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
					$wpssw_error_general = '<strong class="err-msg">Error: Sorry, your nonce did not verify.</strong>';
				} else {
					WPSSW_Order::wpssw_update_settings();
				}
			}
		} elseif ( isset( $_GET['tab'] ) && 'product-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				WPSSW_Product::wpssw_update_product_settings();
			}
		} elseif ( isset( $_GET['tab'] ) && 'customer-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				WPSSW_Customer::wpssw_update_customer_settings();
			}
		} elseif ( isset( $_GET['tab'] ) && 'coupon-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				WPSSW_Coupon::wpssw_update_coupon_settings();
			}
		} elseif ( isset( $_GET['tab'] ) && 'event-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) && self::wpssw_is_event_calender_ticket_active() ) {
			if ( isset( $_POST['submit'] ) ) {
				WPSSW_Event::wpssw_update_event_settings();
			}
		} elseif ( isset( $_GET['tab'] ) && 'em-settings' === (string) sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) {
			if ( isset( $_POST['submit'] ) ) {
				if ( isset( $_POST['wpssw_license_key'] ) ) {
					$wpssw_lince = sanitize_text_field( wp_unslash( $_POST['wpssw_license_key'] ) );
					self::wpssw_update_option( 'wpssw_license_key', $wpssw_lince );
				}
			}
			if ( isset( $_POST['wpssw_license_activate'] ) ) {
				self::wpssw_activate_license( $_POST );
			}
			if ( isset( $_POST['wpssw_license_deactivate'] ) ) {
				self::wpssw_deactivate_license();
			}
		} else {
			// Google API Settings Tab.
			if ( isset( $_POST['submit'] ) || isset( $_POST['revoke'] ) ) {
				if ( ! isset( $_POST['wpssw_api_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_api_settings'] ) ), 'save_api_settings' ) ) {
					$wpssw_error = '<strong class="err-msg">Error: Sorry, your nonce did not verify.</strong>';
				} else {
					if ( isset( $_POST['client_token'] ) ) {
						$wpssw_clienttoken = sanitize_text_field( wp_unslash( $_POST['client_token'] ) );
					} else {
						$wpssw_clienttoken = '';
					}
					if ( isset( $_POST['client_id'] ) && isset( $_POST['client_secret'] ) ) {
						$wpssw_google_settings = array( sanitize_text_field( wp_unslash( $_POST['client_id'] ) ), sanitize_text_field( wp_unslash( $_POST['client_secret'] ) ), $wpssw_clienttoken );
					} else {
						$wpssw_google_settings_value = self::wpssw_option( 'wpssw_google_settings' );
						$wpssw_google_settings       = array( $wpssw_google_settings_value[0], $wpssw_google_settings_value[1], $wpssw_clienttoken );
					}
					self::wpssw_update_option( 'wpssw_google_settings', $wpssw_google_settings );
					if ( isset( $_POST['revoke'] ) ) {
						$wpssw_google_settings    = self::wpssw_option( 'wpssw_google_settings' );
						$wpssw_google_settings[2] = '';
						self::wpssw_update_option( 'wpssw_google_settings', $wpssw_google_settings );
						self::wpssw_update_option( 'wpssw_google_accessToken', '' );
					}
				}
			}
		}
		$wpssw_google_settings_value = self::wpssw_option( 'wpssw_google_settings' );
		if ( ! empty( $wpssw_google_settings_value[2] ) ) {
			if ( ! self::$instance_api->checkcredenatials() ) {
				$wpssw_error = self::$instance_api->getClient( 1 );
				if ( 'Invalid token format' === (string) $wpssw_error ) {
					$wpssw_error = '<div class="error token_error"><p><strong class="err-msg">Error: Invalid Token - Revoke Token with below settings and try again.</strong></p></div>';
				} else {
					$wpssw_error = '<div class="error token_error"><p><strong class="err-msg">Error: ' . $wpssw_error . '</strong></p></div>';
				}
			}
		}
		$disbledbtn = '';
		$enable     = self::wpssw_check_license_key();
		if ( ! $enable ) {
			$disbledbtn = 'disabled';
		}
		?>
		<!-- .wrap -->
		<div class="vertical-tabs">
			<div class="wpssw-logo-section">
				<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/logo.png?ver=' ); ?><?php echo esc_attr( WPSSW_VERSION ); ?>">
				<sup>V<?php echo esc_html( WPSSW_VERSION ); ?></sup>
				<div class="duc-btn1">
					<a target="_blank" href="<?php echo esc_url( self::$submit_ticket ); ?>"><?php echo esc_html__( 'Submit A Ticket', 'wpssw' ); ?></a>
				</div>
				<div class="duc-btn">
					<a target="_blank" href="<?php echo esc_url( self::$documentation ); ?>"><?php echo esc_html__( 'Documentation', 'wpssw' ); ?></a>
				</div>
			</div>
			<div class="tab">
				<button class="tablinks googleapi-settings" onclick="wpsswTab(event, 'googleapi-settings')"
				<?php
				if ( ! empty( $disbledbtn ) ) {
					echo 'disabled="disabled"'; }
				?>
				>
					<span class="tab-icon"></span>
					<?php echo esc_html__( 'Google API', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?></button>
				<button class="tablinks general-settings" onclick="wpsswTab(event, 'general-settings')" 
				<?php
				if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
					echo 'disabled="disabled"'; }
				?>
				> <span class="tab-icon"></span><?php echo esc_html__( 'General', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?></button>
				<button class="tablinks product-settings" onclick="wpsswTab(event, 'product-settings')" 
				<?php
				if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
					echo 'disabled="disabled"'; }
				?>
				> <span class="tab-icon"></span><?php echo esc_html__( 'Product', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?></button>
				<button class="tablinks customer-settings" onclick="wpsswTab(event, 'customer-settings')" 
				<?php
				if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
					echo 'disabled="disabled"'; }
				?>
				> <span class="tab-icon"></span><?php echo esc_html__( 'Customer', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?></button>
				<button class="tablinks coupon-settings" onclick="wpsswTab(event, 'coupon-settings')" 
				<?php
				if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
					echo 'disabled="disabled"'; }
				?>
				> <span class="tab-icon"></span> <?php echo esc_html__( 'Coupon', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?></button>
				<?php if ( self::wpssw_is_event_calender_ticket_active() ) { ?>
					<button class="tablinks event-settings" onclick="wpsswTab(event, 'event-settings')" 
					<?php
					if ( ! empty( $wpssw_error || ! empty( $disbledbtn ) ) ) {
						echo 'disabled="disabled"'; }
					?>
					> <span class="tab-icon"></span> <?php echo esc_html__( 'Event', 'wpssw' ); ?> <br><?php echo esc_html__( 'Settings', 'wpssw' ); ?></button>
				<?php } ?>
				<button class="tablinks export" onclick="wpsswTab(event, 'export')" 
				<?php
				if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
					echo 'disabled="disabled"'; }
				?>
				> <span class="tab-icon"></span> <?php echo esc_html__( 'Export Orders', 'wpssw' ); ?></button>
				<?php
					$wpssw_tabs = apply_filters( 'wpsyncsheets_settings_tabs', array() );
				if ( is_array( $wpssw_tabs ) && ! empty( $wpssw_tabs ) ) {
					foreach ( $wpssw_tabs as $tabkey => $tabname ) {
						?>
							<button class="tablinks <?php echo esc_html( $tabkey ); ?>" onclick="wpsswTab(event, '<?php echo esc_html( $tabkey ); ?>')" 
							<?php
							if ( ! empty( $wpssw_error ) || ! empty( $disbledbtn ) ) {
								echo 'disabled="disabled"'; }
							?>
							> <span class="tab-icon"></span> <?php echo esc_html( $tabname ); ?></button>
							<?php
					}
				}
				?>
				<button class="tablinks em-settings" onclick="wpsswTab(event, 'em-settings')"><span class="tab-icon"></span> <?php echo esc_html__( 'License', 'wpssw' ); ?></button>
			</div>
			<div id="googleapi-settings" class="tabcontent">
				<h3><?php echo esc_html__( 'Google API Settings', 'wpssw' ); ?></h3>
				<p><?php echo esc_html__( 'Create new google APIs with Client ID and Client Secret keys to get an access for the google drive and google sheets. Please follow the documentation, login to your Gmail Account and start with', 'wpssw' ); ?> <a href="<?php echo esc_url( self::$doc_sheet_setting ); ?>" target="_blank"><?php echo esc_html__( 'here', 'wpssw' ); ?>.</a></p>
				<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ) ); ?>">
				<?php wp_nonce_field( 'save_api_settings', 'wpssw_api_settings' ); ?>
					<div id="universal-message-container woocommerce">
						<br>
						<div class="options">
							<?php
							if ( ! empty( $wpssw_error ) ) {
								$allowed_html = wp_kses_allowed_html( 'post' );
								echo wp_kses( $wpssw_error, $allowed_html );
							}
							?>
							<table class="form-table">
								<tr>
									<th> <?php echo esc_html__( 'Client Id', 'wpssw' ); ?> </th>
									<td class="forminp forminp-text">
										<input type="text" id="client_id" name="client_id" value="<?php echo isset( $wpssw_google_settings_value[0] ) ? esc_attr( $wpssw_google_settings_value[0] ) : ''; ?>" size="80" class = "googlesettinginput" placeholder="Enter Client Id" 
											<?php
											if ( ! empty( $wpssw_google_settings_value[0] ) ) {
												echo 'readonly';
											}
											?>
										/>
									</td>
								</tr>
								<tr>
									<th> <?php echo esc_html__( 'Client Secret', 'wpssw' ); ?> </th>
									<td class="forminp forminp-text">
										<input type="text" id="client_secret" name="client_secret" value="<?php echo isset( $wpssw_google_settings_value[1] ) ? esc_attr( $wpssw_google_settings_value[1] ) : ''; ?>" size="80" class = "googlesettinginput" placeholder="Enter Client Secret" 
											<?php
											if ( ! empty( $wpssw_google_settings_value[1] ) ) {
												echo 'readonly';
											}
											?>
										/>
									</td>
								</tr>
								<?php
								if ( ! empty( $wpssw_google_settings_value[0] ) && ! empty( $wpssw_google_settings_value[1] ) ) {
										$wpssw_token_value = $wpssw_google_settings_value[2];
									?>
								<tr>
									<th><?php echo esc_html__( 'Client Token', 'wpssw' ); ?></th>
									<?php
									if ( empty( $wpssw_token_value ) && ! isset( $_GET['code'] ) ) {
										$wpssw_auth_url = self::$instance_api->getClient();
										?>
										<td id="authbtn">
											<a href="<?php echo esc_url( $wpssw_auth_url ); ?>" id="authlink" target="_blank" ><div class="wpssw-button wpssw-button-secondary"><?php echo esc_html__( 'Click here to generate an Authentication Token', 'wpssw' ); ?></div></a>
										</td>
										<?php
									}
									$wpssw_code = '';
									if ( isset( $_GET['code'] ) && ! empty( sanitize_text_field( wp_unslash( $_GET['code'] ) ) ) ) {
										$wpssw_code               = sanitize_text_field( wp_unslash( $_GET['code'] ) );
										$wpssw_token_value        = $wpssw_code;
										$wpssw_google_settings    = self::wpssw_option( 'wpssw_google_settings' );
										$wpssw_google_settings[2] = $wpssw_code;
										self::wpssw_update_option( 'wpssw_google_settings', $wpssw_google_settings );
									}
									$wpssw_google_settings_value = self::wpssw_option( 'wpssw_google_settings' );
									?>
									<td  id="authtext" 
									<?php
									if ( ! empty( $wpssw_token_value ) || $wpssw_code ) {
										?>
										class = "forminp forminp-text wpssw-authtext" 
										<?php
									} else {
										?>
										class="forminp forminp-text"<?php } ?> ><input type="text" name="client_token" value="<?php echo $wpssw_token_value ? esc_attr( $wpssw_token_value ) : esc_attr( $wpssw_code ); ?>" size="80" placeholder="Please enter authentication code" id="client_token" class="googlesettinginput" 
											<?php
											if ( ! empty( $wpssw_google_settings_value[2] ) ) {
												echo 'readonly';
											}
											?>
										/>
									</td>
								</tr>
								<?php } if ( ! empty( $wpssw_token_value ) ) { ?>
								<tr>
									<td></td>
									<td><input type="submit" name="revoke" id="revoke" value = "Revoke Token" class="wpssw-button wpssw-button-secondary"/></td>
								</tr>
								<?php } ?>
							</table>
						</div>
						<?php
						$site_url = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
						$site_url = str_replace( 'www.', '', $site_url );
						?>
						<p class="submit">
							<input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save">
							<?php
							if ( ! empty( $wpssw_token_value ) || ! empty( $wpssw_google_settings_value[0] ) || ! empty( $wpssw_google_settings_value[1] ) ) {
								?>
									<input type="submit" name="reset_settings" id="reset_settings" value = "Reset Settings" class="wpssw-button wpssw-button-primary reset_settings"/>
								<?php } ?>
						</p>
						<table class="copy-url-table" cellpadding="0" cellspacing="0" width="100%" border="0px">
							<tr>
								<td><?php echo esc_html__( 'Authorized Domain : ', 'wpssw' ); ?></td>
								<td><span id="authorized_domain"><?php echo esc_html( $site_url ); ?></span><span class="copy-icon wpssw-button wpssw-button-primary" id="a_domain" onclick="wpsswCopy('authorized_domain','a_domain');"></span></td>
							</tr>
							<tr>
								<td><?php echo esc_html__( 'Authorised redirect URIs : ', 'wpssw' ); ?></td>
								<td><span id="authorized_uri"><?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ) ); ?></span><span class="copy-icon wpssw-button wpssw-button-primary" onclick="wpsswCopy('authorized_uri','a_uri');" id="a_uri"></span></td>
							</tr>
						</table>
					</div>
				</form>
			</div>
			<div id="general-settings" class="tabcontent">
				<h3><?php echo esc_html__( 'General Settings', 'wpssw' ); ?></h3>
				<?php
				$wpssw_google_settings = self::wpssw_option( 'wpssw_google_settings' );
				if ( ! empty( $wpssw_google_settings[2] ) ) {
					?>
					<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=general-settings' ) ); ?>" id="mainform">
					<?php
					if ( ! empty( $wpssw_error_general ) ) {
						$allowed_html = wp_kses_allowed_html( 'post' );
						echo wp_kses( $wpssw_error_general, $allowed_html );
					}
					?>
					<?php
					wp_nonce_field( 'save_general_settings', 'wpssw_general_settings' );
					if ( \WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
						if ( self::$instance_api->checkcredenatials() ) {
							woocommerce_admin_fields( self::wpssw_get_settings() );
							?>
							<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
							<?php
						}
					} else {
						echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!';
					}
					?>
					</form>
					<?php
				} else {
					echo esc_html__( 'Please genearate authentication code from', 'wpssw' );
					?>
					<strong><?php echo esc_html__( 'Google API Setting', 'wpssw' ); ?></strong>
					<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
				<?php } ?>
			</div>
			<!-- Event settings block start -->
			<?php if ( self::wpssw_is_event_calender_ticket_active() ) { ?>
			<div id="event-settings" class="tabcontent">
				<?php
					$wpssw_event_spreadsheet_setting = self::wpssw_option( 'wpssw_event_spreadsheet_setting' );
					$wpssw_event_spreadsheet_id      = self::wpssw_option( 'wpssw_event_spreadsheet_id' );
					$wpssw_spreadsheet_id            = self::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
					$wpssw_eventsheet_id             = $wpssw_event_spreadsheet_id;
				if ( empty( $wpssw_event_spreadsheet_id ) ) {
					$wpssw_event_spreadsheet_id = $wpssw_spreadsheet_id;
				}
					$wpssw_checked = '';
				if ( 'yes' === (string) $wpssw_event_spreadsheet_setting ) {
					$wpssw_checked = 'checked';
				}
				$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
				?>
				<h3><?php echo esc_html__( 'Event Settings', 'wpssw' ); ?></h3>
				<?php
				if ( ! empty( $wpssw_google_settings[2] ) ) {
					if ( ! empty( $wpssw_event_spreadsheet_id ) && array_key_exists( $wpssw_event_spreadsheet_id, $spreadsheets_list ) ) {
						?>
				<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=event-settings' ) ); ?>" id="eventform">
						<?php wp_nonce_field( 'save_event_settings', 'wpssw_event_settings' ); ?>
					<table class="form-table">
						<tr valign="top" class="">
							<th scope="row" class="titledesc">
								<label for="event_settings_checkbox"><?php echo esc_html__( 'All Events Orders Settings', 'wpssw' ); ?></label>
							</th>
							<td class="forminp event-settings-checkbox">              
								<label for="event_settings_checkbox">
									<input name="event_settings_checkbox" id="event_settings_checkbox" type="checkbox" class="" value="1" <?php echo esc_attr( $wpssw_checked ); ?>><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 							
								</label>
								<br /><i class="event_spreadsheet_row"><strong><?php echo esc_html__( 'Note:', 'wpssw' ); ?></strong><br /><strong><?php echo esc_html__( 'Enabled', 'wpssw' ); ?></strong> - <?php echo esc_html__( 'All events are configure within the same spreadsheet as we have assign into the general settings spreadsheets. Sheet name(s) will be created as per the event categories selection as below.', 'wpssw' ); ?><br /></i>
							</td>
						</tr>
								<?php
								$wpssw_events_cat        = array();
								$wpssw_events_cat        = get_terms(
									array(
										'taxonomy'   => 'tribe_events_cat',
										'hide_empty' => 0,
									)
								);
								$wpssw_events_categories = array();
								if ( ! empty( $wpssw_events_cat ) ) {
									foreach ( $wpssw_events_cat as $cat ) {
										$wpssw_events_categories[] = $cat->name;
									}
								}
								?>
						<tr class="event_spreadsheet_row">
							<th><?php echo esc_html__( 'Events Categories', 'wpssw' ); ?></th>
						</tr>
						<tr class="event_spreadsheet_row">
								<?php if ( empty( $wpssw_events_categories ) ) { ?>
							<td colspan="2"> 
									<?php echo esc_html__( "All the event sheet headers will be append within general setting's active sheets. If you want to categorize your events orders then create new category, it will automatically create sheet within your existing spreadsheet.", 'wpssw' ); ?>
							</td>
							<?php } ?>
						</tr>
					</table>
					<table  class="wpssw-section-last event_spreadsheet_row form-table">
							<?php
							$i = 0;
							foreach ( $wpssw_events_categories as $category ) {
								$category_id            = strtolower( str_replace( ' ', '_', $category ) );
								$category_id            = 'event_category_' . $category_id;
								$wpssw_event_ischecked  = '';
								$wpssw_eventsheets_list = array();
								if ( is_array( self::wpssw_option( 'wpssw_eventsheets_list' ) ) ) {
									$wpssw_eventsheets_list = self::wpssw_option( 'wpssw_eventsheets_list' );
								}
								if ( in_array( $category, $wpssw_eventsheets_list, true ) ) {
									$wpssw_event_ischecked = 'checked';
								}
								?>
						<tr valign="top" class="
								<?php
								if ( 0 === $i % 4 ) {
									echo 'event-clear-both';}
								?>
						">
							<td scope="row" class="titledesc">
								<?php echo esc_html( $category ); ?>
							</td>
							<td class="forminp forminp-checkbox">  
								<label for="<?php echo esc_attr( $category_id ); ?>">
									<input name="event_categories_sheet[]" type="checkbox" id="<?php echo esc_attr( $category_id ); ?>" value="<?php echo esc_attr( $category ); ?>" <?php echo esc_html( $wpssw_event_ischecked ); ?>><span class="checkbox-switch"></span> 							
								</label>
							</td>
						</tr>
						<?php $i++;} ?>
					</table>
					<table class="form-table">
						<tr class="event_spreadsheet_row">
							<td colspan="2">
								<div class="wpssw_headers">
									<label for="sheet_headers"><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></label>
										<div id="wpssw-headers-notice">
											<i><strong><?php echo esc_html__( 'Note', 'wpssw' ); ?>: </strong><?php echo esc_html__( 'All the disabled sheet headers will be deleted from the current spreadsheet automatically.', 'wpssw' ); ?></i>
										</div>
									<ul id="woo-event-sortable">
											<?php
											$wpssw_woo_is_checked = '';
											$wpssw_woo_selections = stripslashes_deep( self::wpssw_option( 'wpssw_woo_event_headers' ) );
											if ( ! $wpssw_woo_selections ) {
												$wpssw_woo_selections = array();
											}
											$wpssw_woo_selections_custom = stripslashes_deep( self::wpssw_option( 'wpssw_woo_event_headers_custom' ) );
											if ( ! $wpssw_woo_selections_custom ) {
												$wpssw_woo_selections_custom = array();
											}
											$wpssw_include = new WPSSW_Include_Action();
											$wpssw_include->wpssw_include_event_compatibility_files();
											$wpssw_wooevent_headers = apply_filters( 'wpsyncsheets_event_headers', array() );
											$wpssw_wooevent_headers = self::wpssw_array_flatten( $wpssw_wooevent_headers );
											if ( ! empty( $wpssw_woo_selections ) ) {
												foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_val ) {
													$wpssw_woo_is_checked = 'checked';
													$wpssw_labelid        = strtolower( str_replace( ' ', '_', $wpssw_val ) );
													?>
										<li class="ui-state-default">
											<label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>">
												<span class="ui-icon ui-icon-caret-2-n-s"></span>
												<span class="wootextfield"><?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?></span>
												<span class="ui-icon ui-icon-pencil"></span>
												<input type="checkbox" name="wooevent_custom[]" value="<?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>" class="woo-event-headers-chk1" <?php echo esc_attr( $wpssw_woo_is_checked ); ?> hidden="true">
												<input type="checkbox" name="wooevent_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-event-headers-chk" <?php echo esc_html( $wpssw_woo_is_checked ); ?>>
												<span class="checkbox-switch-new"></span>
											</label>
										</li>
													<?php
												}
											}
											if ( ! empty( $wpssw_wooevent_headers ) ) {
												foreach ( $wpssw_wooevent_headers as $wpssw_key => $wpssw_val ) {
													$wpssw_woo_is_checked = '';
													if ( in_array( $wpssw_val, $wpssw_woo_selections, true ) ) {
														continue;
													}
													$wpssw_labelid = strtolower( str_replace( ' ', '_', $wpssw_val ) );
													?>
													<li class="ui-state-default"><label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>"><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield"><?php echo esc_html( $wpssw_val ); ?></span><span class="ui-icon ui-icon-pencil"></span><input type="checkbox" name="wooevent_custom[]" value="<?php echo esc_attr( $wpssw_val ); ?>" class="woo-event-headers-chk1" <?php echo esc_attr( $wpssw_woo_is_checked ); ?> hidden="true"><input type="checkbox" name="wooevent_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-event-headers-chk" <?php echo esc_attr( $wpssw_woo_is_checked ); ?>><span class="checkbox-switch-new"></span></label></li>
													<?php
												}
											}
											?>
									</ul>
									<button type="button" class="wpssw-button wpssw-button-secondary" id="woo-eventselectall"><?php echo esc_html__( 'Select all', 'wpssw' ); ?></button>                
									<button type="button" class="wpssw-button wpssw-button-secondary" id="woo-eventselectnone"><?php echo esc_html__( 'Select none', 'wpssw' ); ?></button>
								</div>
							</td>
						</tr>
							<?php
							$wpssw_eventsheets_list = array();
							if ( is_array( self::wpssw_option( 'wpssw_eventsheets_list' ) ) ) {
								$wpssw_eventsheets_list = self::wpssw_option( 'wpssw_eventsheets_list' );
							}
							if ( ! empty( $wpssw_event_spreadsheet_id ) && ! empty( $wpssw_events_categories ) && ! empty( $wpssw_eventsheets_list ) ) {
								?>
						<tr valign="top" class="event_spreadsheet_row" >
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Sync Events', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">              
								<img src="images/spinner.gif" id="eventsyncloader"><span id="eventsynctest"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span><a class="wpssw-button" href="javascript:void(0)" id="sync_event">
									<?php echo esc_html__( 'Click to Sync', 'wpssw' ); ?></a><br><br><i><strong><?php echo esc_html__( 'Note:', 'wpssw' ); ?></strong> <?php echo esc_html__( 'Click to Sync button will append all the event orders to the sheet.', 'wpssw' ); ?></i> 
							</td>
						</tr>
						<tr valign="top" class="event_spreadsheet_row" >
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Clear Spreadsheet', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">              
								<img src="images/spinner.gif" id="cleareventloader"><a class="wpssw-button" href="javascript:void(0)" id="clear_eventsheet">
									<?php echo esc_html__( 'Click to Clear Spreadsheet', 'wpssw' ); ?></a><br><br> 
							</td>
						</tr>
					<?php } ?>
					</table>
						<?php
						if ( WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
							?>
					<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
							<?php
						} else {
							echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!';
						}
						?>
				</form>
						<?php
					} else {
						echo esc_html__( 'Please select Spreadsheet from ', 'wpssw' );
						?>
						<strong><?php echo esc_html__( 'General Settings', 'wpssw' ); ?></strong>
						<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=general-settings' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
						<?php
					}
				} else {
					echo esc_html__( 'Please genearate authentication code from', 'wpssw' );
					?>
					<strong><?php echo esc_html__( 'Google API Setting', 'wpssw' ); ?></strong>
					<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
					<?php } ?>
			</div>
			<?php } ?>
			<!-- Event settings block end -->
			<div id="export" class="tabcontent">
				<h3><?php echo esc_html__( 'Export Orders', 'wpssw' ); ?></h3>
				<?php
				$wpssw_google_settings = self::wpssw_option( 'wpssw_google_settings' );
				if ( ! empty( $wpssw_google_settings[2] ) && WPSSW_Dependencies::wpssw_is_woocommerce_active() && self::$instance_api->checkcredenatials() ) {
					$wpssw_product_category_list = array();
					$args                        = array(
						'taxonomy' => 'product_cat',
						'orderby'  => 'name',
					);
					$product_categories          = get_terms( $args );
					foreach ( $product_categories as $prd_cat ) {
						$wpssw_product_category_list[ $prd_cat->term_id ] = $prd_cat->name;
					}
					?>
					<p><?php echo esc_html__( 'You can export all orders or select custom order date range with below settings. It will create new spreadsheet in your Google Drive.', 'wpssw' ); ?></p>
					<form method="post" action="" id="exportform">
					<?php wp_nonce_field( 'save_export_settings', 'wpssw_export_settings' ); ?>
						<table class="form-table wpssw-section-1">
							<tbody>
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label for="woocommerce_spreadsheet"><?php echo esc_html__( 'Enter Spreadsheet Name', 'wpssw' ); ?> <span class="woocommerce-help-tip" ></span></label>
									</th>
									<td class="forminp forminp-select"><input name="expspreadsheetname" id="expspreadsheetname" type="text" placeholder="<?php echo esc_attr__( 'Enter Spreadsheet Name', 'wpssw' ); ?>" required></td>
								</tr>
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label for="woocommerce_spreadsheet"><?php echo esc_html__( 'Order Date Range', 'wpssw' ); ?> <span class="woocommerce-help-tip" data-tip="Please select Google Spreadsheet."></span></label>
									</th>
									<td class="forminp forminp-select"> <?php echo esc_html__( 'From :', 'wpssw' ); ?>   <input name="expspreadsheetname" id="ordfromdate" type="date" required></td>
								</tr>
								<tr valign="top">
									<th scope="row" class="titledesc"></th>
									<td class="forminp forminp-select"><?php echo esc_html__( 'To :', 'wpssw' ); ?> <input name="expspreadsheetname" id="ordtodate" type="date" required></td>
								</tr>
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label for="exportall"><?php echo esc_html__( 'Export All Orders', 'wpssw' ); ?></label>
									</th>
									<td class="exportrow">              
										<label for="exportall">
											<input name="repeat_checkbox" id="exportall" type="checkbox" class="" value="1"><span class="checkbox-switch"></span> 							
										</label>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row" class="titledesc">
										<label for="category_select"><?php echo esc_html__( 'Select Category', 'wpssw' ); ?></label>
									</th>
									<td class="exportrow">              
										<label for="category_select">
											<input name="category_select" id="category_select" type="checkbox" class="" value="1"><span class="checkbox-switch"></span> 							
										</label>
									</td>
								</tr>
								<tr class="td-prdcat-wpssw">
									<td colspan="2" class="td-wpssw-headers">
										<div class='wpssw_headers'>
											<label for="sheet_headers"></label>
											<div id="wpssw-headers-notice">
												<i><?php echo esc_html__( 'Select orders of below product categories as you want export within the spreadsheet.', 'wpssw' ); ?></i>
											</div>
											<ul id="product-sortable">
												<?php
												foreach ( $wpssw_product_category_list as $wpssw_key => $wpssw_val ) {
													?>
													<li class="ui-state-default"><label for="<?php echo esc_attr( $wpssw_val ); ?>"><span class="ui-icon ui-icon-caret-2-n-s"></span><?php echo esc_html( $wpssw_val ); ?><input type="checkbox" name="productcat_header[]" value="<?php echo esc_attr( $wpssw_key ); ?>" id="<?php echo esc_attr( $wpssw_val ); ?>" class="prdcatheaders_chk"><span class="checkbox-switch-new"></span></label></li>
													<?php
												}
												?>
											</ul>
											<?php
											if ( ! empty( $wpssw_selections ) ) {
												$wpssw_class = 'wpssw-button wpssw-button-secondary wpssw-prdcatselect';
											} else {
												$wpssw_class = 'wpssw-button wpssw-button-secondary';
											}
											?>
											<button type="button" id="prdcatselectall" class="<?php echo esc_attr( $wpssw_class ); ?>"> <?php echo esc_html__( 'Select all', 'wpssw' ); ?></button> 
											<button type="button" id="prdcatselectnone" class="<?php echo esc_attr( $wpssw_class ); ?>"> <?php echo esc_html__( 'Select none', 'wpssw' ); ?></button>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
						<p class="submit"><input type="submit" name="submit" id="exportsubmit" class="wpssw-button wpssw-button-primary" value="Export"><span class='processbar'><img src="<?php dirname( __FILE__ ); ?>images/spinner.gif" id="expsyncloader"><span id="expsynctext"><?php echo esc_html__( 'Please wait...', 'wpssw' ); ?></span></span><a target='_blank' class="wpssw-button wpssw-button-primary" href="" id='spreadsheet_url'><?php echo esc_html__( 'View Spreadsheet', 'wpssw' ); ?></a><a target='_blank' class="wpssw-button wpssw-button-primary" href="" id='spreadsheet_xslxurl'><?php echo esc_html__( 'Download Spreadsheet (.xlsx)', 'wpssw' ); ?></a>
						</p>
						<?php
						if ( self::wpssw_option( 'wpssw_woocommerce_spreadsheet' ) ) {
							$spreadsheetid = self::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
							$xlsxurl       = "https://docs.google.com/spreadsheets/u/0/d/{$spreadsheetid}/export?exportFormat=xlsx";
							?>
							<table class="form-table wpssw-section-1">
								<tr valign="top">
									<th scope="row" class="titledesc downloadall">
										<label><?php echo esc_html__( 'Download Spreadsheet', 'wpssw' ); ?></label>
									</th>
									<td class="exportrow">              
										<a id="view_spreadsheet" download="" target="_blank" href="<?php echo esc_url( $xlsxurl ); ?>" class="wpssw-button"><?php echo esc_html__( 'Download Spreadsheet (.xlsx)', 'wpssw' ); ?></a>
									</td>
								</tr>
							</table>
							<?php } ?>  
					</form>
					<?php
				} else {
					if ( ! WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
						echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!';
					} elseif ( empty( $wpssw_google_settings[2] ) ) {
						echo esc_html__( 'Please genearate authentication code from', 'wpssw' );
						?>
						<strong><?php echo esc_html__( 'Google API Setting', 'wpssw' ); ?></strong>
						<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
						<?php
					}
				}
				?>
			</div>
			<?php
			if ( is_array( $wpssw_tabs ) && ! empty( $wpssw_tabs ) ) {
				foreach ( $wpssw_tabs as $tabkey => $tabname ) {
					?>
						<div id="<?php echo esc_attr( $tabkey ); ?>" class="tabcontent">
						<?php do_action( 'wpsyncsheets_tab_' . $tabkey ); ?>
						</div>
						<?php
				}
			}
			?>
			<div id="product-settings" class="tabcontent">
				<?php
					$wpssw_product_import              = self::wpssw_option( 'wpssw_product_import' );
					$wpssw_product_insert              = self::wpssw_option( 'wpssw_product_insert' );
					$wpssw_product_update              = self::wpssw_option( 'wpssw_product_update' );
					$wpssw_product_delete              = self::wpssw_option( 'wpssw_product_delete' );
					$wpssw_product_spreadsheet_setting = self::wpssw_option( 'wpssw_product_spreadsheet_setting' );
					$wpssw_product_spreadsheet_id      = self::wpssw_option( 'wpssw_product_spreadsheet_id' );
					$wpssw_spreadsheet_id              = self::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
					$wpssw_prdsheet_id                 = $wpssw_product_spreadsheet_id;

					$wpssw_productsheet_name = '';
				if ( self::wpssw_check_sheet_exist( $wpssw_prdsheet_id, 'All Products' ) ) {
					$wpssw_productsheet_name = 'All Products';
				}

				if ( empty( $wpssw_product_spreadsheet_id ) ) {
					$wpssw_product_spreadsheet_id = $wpssw_spreadsheet_id;
				}
					$wpssw_checked = '';
				if ( 'yes' === (string) $wpssw_product_spreadsheet_setting ) {
					$wpssw_checked = 'checked';
				}
				?>
				<h3><?php echo esc_html__( 'Product Settings', 'wpssw' ); ?></h3>
				<?php
				if ( ! empty( $wpssw_google_settings[2] ) ) {
					?>
				<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=product-settings' ) ); ?>" id="productform">
					<?php wp_nonce_field( 'save_product_settings', 'wpssw_product_settings' ); ?>
					<table class="form-table">
						<tr valign="top" class="">
							<th scope="row" class="titledesc">
								<label for="product_settings_checkbox"><?php echo esc_html__( 'All Products (Sheet)', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">              
								<label for="product_settings_checkbox">
									<input name="product_settings_checkbox" id="product_settings_checkbox" type="checkbox" class="" value="1" <?php echo esc_attr( $wpssw_checked ); ?>><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 		
								</label>
							</td>
						</tr>
							<?php
							$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
							?>
						<tr valign="top" class="prd_spreadsheet_row">
							<th scope="row" class="titledesc"> 
								<label for="product_spreadsheet">Select Spreadsheet <span class="woocommerce-help-tip" data-tip="Please select Google Spreadsheet."></span></label>
							</th>
							<td class="forminp forminp-select">
								<select name="product_spreadsheet" id="product_spreadsheet" style="min-width:150px;" class="">
								<?php
								$selected = '';
								foreach ( $spreadsheets_list as $spreadsheetid => $spreadsheetname ) {
									if ( (string) $wpssw_product_spreadsheet_id === $spreadsheetid ) {
										$selected = 'selected="selected"';
									}
									?>
									<option value="<?php echo esc_attr( $spreadsheetid ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $spreadsheetname ); ?></option>
									<?php $selected = ''; } ?>
								</select>
								<br />
								<i><strong>Note: </strong><?php echo esc_html__( 'By default select the current spreadsheet from General Settings tab, but users can create a new spreadsheet by selecting the option.', 'wpssw' ); ?></i>
							</td>
						</tr>
						<tr valign="top" class="prd_spreadsheet_inputrow">
							<th scope="row" class="titledesc">
								<label for="product_spreadsheet_name"><?php echo esc_html__( 'Enter Spreadsheet Name', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">              
								<input name="product_spreadsheet_name" id="product_spreadsheet_name" type="text" class="" value=""><span class="checkbox-switch"></span><span class="checkbox-switch"></span>
							</td>
						</tr>
						<tr class="prd_spreadsheet_row">
							<td colspan="2">
								<div class="wpssw_headers">
									<label for="sheet_headers"><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></label>
									<div id="wpssw-headers-notice">
										<i><strong><?php echo esc_html__( 'Note', 'wpssw' ); ?>: </strong><?php echo esc_html__( 'All the disabled sheet headers will be deleted from the current spreadsheet automatically. You can enable it again, clear the spreadsheet and click the "Click to Sync" button to make up to date data in the spreadsheet.', 'wpssw' ); ?></i>
									</div>
									<ul id="woo-product-sortable">
										<?php
										$wpssw_woo_is_checked = '';
										$wpssw_woo_selections = stripslashes_deep( self::wpssw_option( 'wpssw_woo_product_headers' ) );
										if ( ! $wpssw_woo_selections ) {
											$wpssw_woo_selections = array();
										}
										$wpssw_woo_selections_custom = stripslashes_deep( self::wpssw_option( 'wpssw_woo_product_headers_custom' ) );
										if ( ! $wpssw_woo_selections_custom ) {
											$wpssw_woo_selections_custom = array();
										}
										$wpssw_include = new WPSSW_Include_Action();
										$wpssw_include->wpssw_include_product_compatibility_files();
										$wpssw_wooproduct_headers = apply_filters( 'wpsyncsheets_product_headers', array() );
										$wpssw_wooproduct_headers = self::wpssw_array_flatten( $wpssw_wooproduct_headers );
										$wpssw_operation          = array( 'Insert', 'Update', 'Delete' );
										if ( ! empty( $wpssw_woo_selections ) ) {
											foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_val ) {
												$wpssw_woo_is_checked = 'checked';
												$wpssw_labelid        = strtolower( str_replace( ' ', '_', $wpssw_val ) );
												$wpssw_display        = true;
												$wpssw_classname      = '';
												if ( in_array( $wpssw_val, $wpssw_operation, true ) ) {
													$wpssw_display   = false;
													$wpssw_labelid   = '';
													$wpssw_classname = strtolower( $wpssw_val ) . 'product';
												}
												?>
										<li class="ui-state-default <?php echo esc_html( $wpssw_classname ); ?>">
											<label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>">
												<span class="ui-icon ui-icon-caret-2-n-s"></span>
												<span class="wootextfield"><?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?></span>
												<?php if ( $wpssw_display ) { ?>
												<span class="ui-icon ui-icon-pencil"></span>
												<?php } ?>
												<input type="checkbox" name="wooproduct_custom[]" value="<?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>" class="woo-pro-headers-chk1" <?php echo esc_attr( $wpssw_woo_is_checked ); ?> hidden="true">
												<input type="checkbox" name="wooproduct_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-pro-headers-chk" <?php echo esc_attr( $wpssw_woo_is_checked ); ?>>
												<?php if ( $wpssw_display ) { ?>
												<span class="checkbox-switch-new"></span>
												<?php } ?>
											</label>
										</li>
												<?php
											}
										}
										if ( ! empty( $wpssw_wooproduct_headers ) ) {
											foreach ( $wpssw_wooproduct_headers as $wpssw_key => $wpssw_val ) {
												$wpssw_woo_is_checked = '';
												if ( in_array( $wpssw_val, $wpssw_woo_selections, true ) ) {
													continue;
												}
												$wpssw_labelid = strtolower( str_replace( ' ', '_', $wpssw_val ) );
												?>
											<li class="ui-state-default"><label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>"><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield"><?php echo esc_attr( $wpssw_val ); ?></span><span class="ui-icon ui-icon-pencil"></span><input type="checkbox" name="wooproduct_custom[]" value="<?php echo esc_attr( $wpssw_val ); ?>" class="woo-pro-headers-chk1" <?php echo esc_attr( $wpssw_woo_is_checked ); ?> hidden="true"><input type="checkbox" name="wooproduct_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-pro-headers-chk" <?php echo esc_attr( $wpssw_woo_is_checked ); ?>><span class="checkbox-switch-new"></span></label></li>
													<?php
											}
										}
										?>
									</ul>
									<button type="button" class="wpssw-button wpssw-button-secondary" id="woo-proselectall"><?php echo esc_html__( 'Select all', 'wpssw' ); ?></button>
									<button type="button" class="wpssw-button wpssw-button-secondary" id="woo-proselectnone"><?php echo esc_html__( 'Select none', 'wpssw' ); ?></button>
								</div>
							</td>
						</tr>
						<?php
						if ( ! empty( $wpssw_prdsheet_id ) && ! empty( $wpssw_productsheet_name ) ) {
							?>
						<tr valign="top" id="prodsynctr" class="prd_spreadsheet_row" >
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Sync Products', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">              
								<img src="images/spinner.gif" id="prodsyncloader"><span id="prodsynctext"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span><a class="wpssw-button" href="javascript:void(0)" id="prodsync">
									<?php echo esc_html__( 'Click to Sync', 'wpssw' ); ?></a><br><br><i><strong><?php echo esc_html__( 'Note:', 'wpssw' ); ?></strong> <?php echo esc_html__( 'Click to Sync button will append all the product data to the sheet.', 'wpssw' ); ?></i> 
							</td>
						</tr>
						<tr valign="top" id="import_checkbox_row" class="prd_spreadsheet_row prd_import_row" >
							<th scope="row" class="titledesc">
								<label><?php esc_html_e( 'Import Products', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">      
								<label for="import_checkbox">
									<input name="import_checkbox" id="import_checkbox" type="checkbox" class="" value="1" 
									<?php
									if ( $wpssw_product_import ) {
										echo 'checked="checked"';}
									?>
									><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 		
								</label> 
							</td>
						</tr>
						<tr valign="top" id="insert_checkbox_row" class="prd_spreadsheet_row wpssw_crud_row " >
							<th scope="row" class="titledesc">
								<label><?php esc_html_e( 'Insert Product', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">    
								<label for="insert_checkbox">
									<input name="insert_checkbox" id="insert_checkbox" type="checkbox" class="" value="1" 
									<?php
									if ( $wpssw_product_insert ) {
										echo 'checked="checked"';}
									?>
									/><span class="checkbox-switch"></span><span class="checkbox-switch"></span>	
								</label> 
							</td>
						</tr>
						<tr valign="top" id="update_checkbox_row" class="prd_spreadsheet_row wpssw_crud_row " >
							<th scope="row" class="titledesc">
								<label><?php esc_html_e( 'Update Product', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">    
								<label for="update_checkbox">
									<input name="update_checkbox" id="update_checkbox" type="checkbox" class="" value="1" 
									<?php
									if ( $wpssw_product_update ) {
										echo 'checked="checked"';}
									?>
									><span class="checkbox-switch"></span><span class="checkbox-switch"></span>
								</label> 
							</td>
						</tr>
						<tr valign="top" id="delete_checkbox_row" class="prd_spreadsheet_row wpssw_crud_row " >
							<th scope="row" class="titledesc">
								<label><?php esc_html_e( 'Delete Product', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">     
								<label for="delete_checkbox">
									<input name="delete_checkbox" id="delete_checkbox" type="checkbox" class="" value="1" 
									<?php
									if ( $wpssw_product_delete ) {
										echo 'checked="checked"';}
									?>
									><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 			
								</label> 
							</td>
						</tr>
							<?php if ( 1 === (int) $wpssw_product_insert || 1 === (int) $wpssw_product_update || 1 === (int) $wpssw_product_delete ) { ?>
						<tr valign="top" class="wpssw_crud_row prd_spreadsheet_row">
							<th scope="row" class="titledesc">
								<label></label>
							</th>
							<td class="forminp"> 
								<img src="images/spinner.gif" id="importsyncloader"><span id="importsynctext"><?php esc_html_e( 'Checking...', 'wpssw' ); ?></span><a class="wpssw-button" href="javascript:void(0)" id="importsync"><?php echo esc_html__( 'Import Product', 'wpssw' ); ?>
									</a><br /><a class="wpssw-button" href="javascript:void(0)" id="importsyncbtm"><?php esc_html_e( 'Proceed', 'wpssw' ); ?></a><a class="wpssw-button" href="javascript:void(0)" id="cancelsyncbtm"><?php esc_html_e( 'Cancel', 'wpssw' ); ?></a><i class='prdnotice'><strong><?php echo esc_html__( 'Note', 'wpssw' ); ?>: </strong><?php echo esc_html__( 'Please take the backup of the database before you are going to click on the import button.', 'wpssw' ); ?></i>  
							</td>
						</tr>
						<?php } ?>
						<tr valign="top" class="prd_spreadsheet_row prd_import_row">
							<th scope="row" class="titledesc">
								<label></label>
							</th>
							<td class="forminp"> 
								<a href="<?php echo esc_url( 'https://docs.wpsyncsheets.com/how-to-import-products/' ); ?>" target="_blank"><?php echo esc_html__( 'How to Import Products?', 'wpssw' ); ?></a>  
							</td>
						</tr>
								<?php
						}
						?>
					</table>
					<?php
					if ( WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
						?>
					<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
						<?php
					} else {
						echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!';
					}
					?>
				</form>
					<?php
				} else {
					echo esc_html__( 'Please genearate authentication code from', 'wpssw' );
					?>
					<strong><?php echo esc_html__( 'Google API Setting', 'wpssw' ); ?></strong>
					<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
					<?php } ?>
			</div>
			<div id="customer-settings" class="tabcontent">
				<?php
					$wpssw_customer_spreadsheet_setting = self::wpssw_option( 'wpssw_customer_spreadsheet_setting' );
					$wpssw_customer_spreadsheet_id      = self::wpssw_option( 'wpssw_customer_spreadsheet_id' );
					$wpssw_spreadsheet_id               = self::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
					$wpssw_custsheet_id                 = $wpssw_customer_spreadsheet_id;
					$wpssw_customersheet_name           = '';
					$wpssw_spreadsheets_list            = self::$instance_api->get_spreadsheet_listing();

				if ( self::wpssw_check_sheet_exist( $wpssw_custsheet_id, 'All Customers' ) ) {
					$wpssw_customersheet_name = 'All Customers';
				}

				if ( empty( $wpssw_customer_spreadsheet_id ) ) {
					$wpssw_customer_spreadsheet_id = $wpssw_spreadsheet_id;
				}
					$wpssw_checked = '';
				if ( 'yes' === (string) $wpssw_customer_spreadsheet_setting ) {
					$wpssw_checked = 'checked';
				}
				?>
				<h3><?php echo esc_html__( 'Customer Settings', 'wpssw' ); ?></h3>
				<?php
				if ( ! empty( $wpssw_google_settings[2] ) ) {
					?>
				<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=customer-settings' ) ); ?>" id="customerform">
					<?php wp_nonce_field( 'save_customer_settings', 'wpssw_customer_settings' ); ?>
					<table class="form-table">
						<tr valign="top" class="">
							<th scope="row" class="titledesc">
								<label for="customer_settings_checkbox"><?php echo esc_html__( 'All Customers (Customer Sheet)', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">              
								<label for="customer_settings_checkbox">
									<input name="customer_settings_checkbox" id="customer_settings_checkbox" type="checkbox" class="" value="1" <?php echo esc_html( $wpssw_checked ); ?>><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 							
								</label>
							</td>
						</tr>
						<?php
						$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
						?>
						<tr valign="top" class="cust_spreadsheet_row">
							<th scope="row" class="titledesc"> 
								<label for="customer_spreadsheet">Select Spreadsheet <span class="woocommerce-help-tip" data-tip="Please select Google Spreadsheet."></span></label>
							</th>
							<td class="forminp forminp-select">
								<select name="customer_spreadsheet" id="customer_spreadsheet" style="min-width:150px;" class="">
								<?php
								$selected = '';
								foreach ( $spreadsheets_list as $spreadsheetid => $spreadsheetname ) {
									if ( (string) $wpssw_customer_spreadsheet_id === $spreadsheetid ) {
										$selected = 'selected="selected"';
									}
									?>
									<option value="<?php echo esc_attr( $spreadsheetid ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $spreadsheetname ); ?></option>
								<?php $selected = ''; } ?>
								</select>
								<br />
								<i><strong>Note: </strong><?php echo esc_html__( 'By default select the current spreadsheet from General Settings tab, but users can create a new spreadsheet by selecting the option.', 'wpssw' ); ?></i>
							</td>
						</tr>
						<tr valign="top" class="cust_spreadsheet_inputrow">
							<th scope="row" class="titledesc">
								<label for="customer_spreadsheet_name"><?php echo esc_html__( 'Enter Spreadsheet Name', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">              
								<input name="customer_spreadsheet_name" id="customer_spreadsheet_name" type="text" class="" value=""><span class="checkbox-switch"></span><span class="checkbox-switch"></span>
							</td>
						</tr>
						<tr class="cust_spreadsheet_row">
							<td colspan="2">
								<div class="wpssw_headers">
									<label for="sheet_headers"><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></label>
									<div id="wpssw-headers-notice">
										<i><strong><?php echo esc_html__( 'Note', 'wpssw' ); ?>: </strong><?php echo esc_html__( 'All the disabled sheet headers will be deleted from the current spreadsheet automatically. You can enable it again, clear the spreadsheet and click the "Click to Sync" button to make up to date data in the spreadsheet.', 'wpssw' ); ?></i>
									</div>
									<ul id="woo-customer-sortable">
										<?php
										$wpssw_woo_is_checked = '';
										$wpssw_woo_selections = stripslashes_deep( self::wpssw_option( 'wpssw_woo_customer_headers' ) );
										if ( ! $wpssw_woo_selections ) {
											$wpssw_woo_selections = array();
										}
										$wpssw_woo_selections_custom = stripslashes_deep( self::wpssw_option( 'wpssw_woo_customer_headers_custom' ) );
										if ( ! $wpssw_woo_selections_custom ) {
											$wpssw_woo_selections_custom = array();
										}
										$wpssw_include = new WPSSW_Include_Action();
										$wpssw_include->wpssw_include_customer_compatibility_files();
										$wpssw_woocustomer_headers = apply_filters( 'wpsyncsheets_customer_headers', array() );
										$wpssw_woocustomer_headers = self::wpssw_array_flatten( $wpssw_woocustomer_headers );
										if ( ! empty( $wpssw_woo_selections ) ) {
											foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_val ) {
												$wpssw_woo_is_checked = 'checked';
												$wpssw_labelid        = strtolower( str_replace( ' ', '_', $wpssw_val ) );
												?>
										<li class="ui-state-default">
											<label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>">
												<span class="ui-icon ui-icon-caret-2-n-s"></span>
												<span class="wootextfield"><?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?></span>
												<span class="ui-icon ui-icon-pencil"></span>
												<input type="checkbox" name="woocustomer_custom[]" value="<?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>" class="woo-cust-headers-chk1" <?php echo esc_html( $wpssw_woo_is_checked ); ?> hidden="true">
												<input type="checkbox" name="woocustomer_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-cust-headers-chk" <?php echo esc_html( $wpssw_woo_is_checked ); ?>>
												<span class="checkbox-switch-new"></span>
											</label>
										</li>
												<?php
											}
										}
										if ( ! empty( $wpssw_woocustomer_headers ) ) {
											foreach ( $wpssw_woocustomer_headers as $wpssw_key => $wpssw_val ) {
												$wpssw_woo_is_checked = '';
												if ( in_array( $wpssw_val, $wpssw_woo_selections, true ) ) {
													continue;
												}
												$wpssw_labelid = strtolower( str_replace( ' ', '_', $wpssw_val ) );
												?>
										<li class="ui-state-default"><label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>"><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield"><?php echo esc_attr( $wpssw_val ); ?></span><span class="ui-icon ui-icon-pencil"></span><input type="checkbox" name="woocustomer_custom[]" value="<?php echo esc_attr( $wpssw_val ); ?>" class="woo-cust-headers-chk1" <?php echo esc_html( $wpssw_woo_is_checked ); ?> hidden="true"><input type="checkbox" name="woocustomer_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-cust-headers-chk" <?php echo esc_attr( $wpssw_woo_is_checked ); ?>><span class="checkbox-switch-new"></span></label>
										</li>
												<?php
											}
										}
										?>
									</ul>
									<button type="button" class="wpssw-button wpssw-button-secondary" id="woo-custselectall"><?php echo esc_html__( 'Select all', 'wpssw' ); ?></button>                
									<button type="button" class="wpssw-button wpssw-button-secondary" id="woo-custselectnone"><?php echo esc_html__( 'Select none', 'wpssw' ); ?></button>
								</div>
							</td>
						</tr>
						<?php if ( ! empty( $wpssw_custsheet_id ) && ! empty( $wpssw_customersheet_name ) ) { ?>
						<tr valign="top" id="custsynctr" class="cust_spreadsheet_row" >
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Sync Customers', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">              
								<img src="images/spinner.gif" id="custsyncloader"><span id="custsynctext"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span><a class="wpssw-button" href="javascript:void(0)" id="custsync">
									<?php echo esc_html__( 'Click to Sync', 'wpssw' ); ?></a><br><br><i><strong><?php echo esc_html__( 'Note:', 'wpssw' ); ?></strong> <?php echo esc_html__( 'Click to Sync button will append all the customer data to the sheet.', 'wpssw' ); ?></i> 
							</td>
						</tr>
						<?php } ?>
					</table>
					<?php
					if ( WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
						?>
					<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
						<?php
					} else {
						echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!';
					}
					?>
				</form>
					<?php
				} else {
					echo esc_html__( 'Please genearate authentication code from', 'wpssw' );
					?>
				<strong><?php echo esc_html__( 'Google API Setting', 'wpssw' ); ?></strong>
				<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
				<?php } ?> 
			</div>
			<div id="coupon-settings" class="tabcontent">
				<?php
				$wpssw_coupon_spreadsheet_setting = self::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
				$wpssw_coupon_spreadsheet_id      = self::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
				$wpssw_spreadsheet_id             = self::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
				$wpssw_couponsheet_id             = $wpssw_coupon_spreadsheet_id;
				$wpssw_couponsheet_name           = '';
				$wpssw_spreadsheets_list          = self::$instance_api->get_spreadsheet_listing();

				if ( self::wpssw_check_sheet_exist( $wpssw_couponsheet_id, 'All Coupons' ) ) {
					$wpssw_couponsheet_name = 'All Coupons';
				}
				if ( empty( $wpssw_coupon_spreadsheet_id ) ) {
					$wpssw_coupon_spreadsheet_id = $wpssw_spreadsheet_id;
				}
				$wpssw_checked = '';
				if ( 'yes' === (string) $wpssw_coupon_spreadsheet_setting ) {
					$wpssw_checked = 'checked';
				}
				?>
				<h3><?php echo esc_html__( 'Coupon Settings', 'wpssw' ); ?></h3>
				<?php
				if ( ! empty( $wpssw_google_settings[2] ) ) {
					?>
				<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=coupon-settings' ) ); ?>" id="couponform">
					<?php wp_nonce_field( 'save_coupon_settings', 'wpssw_coupon_settings' ); ?>
					<table class="form-table">
						<tr valign="top" class="">
							<th scope="row" class="titledesc">
								<label for="coupon_settings_checkbox"><?php echo esc_html__( 'All Coupons (Coupon Sheet)', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">              
								<label for="coupon_settings_checkbox">
									<input name="coupon_settings_checkbox" id="coupon_settings_checkbox" type="checkbox" class="" value="1" <?php echo esc_html( $wpssw_checked ); ?>><span class="checkbox-switch"></span><span class="checkbox-switch"></span>
								</label>
							</td>
						</tr>
							<?php
							$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
							?>
						<tr valign="top" class="coupon_spreadsheet_row">
							<th scope="row" class="titledesc"> 
								<label for="coupon_spreadsheet">Select Spreadsheet <span class="woocommerce-help-tip" data-tip="Please select Google Spreadsheet."></span></label>
							</th>
							<td class="forminp forminp-select">
								<select name="coupon_spreadsheet" id="coupon_spreadsheet" style="min-width:150px;" class="">
									<?php
									$selected = '';
									foreach ( $spreadsheets_list as $spreadsheetid => $spreadsheetname ) {
										if ( (string) $wpssw_coupon_spreadsheet_id === $spreadsheetid ) {
											$selected = 'selected="selected"';
										}
										?>
									<option value="<?php echo esc_attr( $spreadsheetid ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $spreadsheetname ); ?></option>
								<?php $selected = ''; } ?>
								</select>
								<br />
								<i><strong>Note: </strong><?php echo esc_html__( 'By default select the current spreadsheet from General Settings tab, but users can create a new spreadsheet by selecting the option.', 'wpssw' ); ?></i>
							</td>
						</tr>
						<tr valign="top" class="coupon_spreadsheet_inputrow">
							<th scope="row" class="titledesc">
								<label for="coupon_spreadsheet_name"><?php echo esc_html__( 'Enter Spreadsheet Name', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">              
								<input name="coupon_spreadsheet_name" id="coupon_spreadsheet_name" type="text" class="" value=""><span class="checkbox-switch"></span><span class="checkbox-switch"></span>
							</td>
						</tr>
						<tr class="coupon_spreadsheet_row">
							<td colspan="2">
								<div class="wpssw_headers">
									<label for="sheet_headers"><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></label>
									<div id="wpssw-headers-notice">
										<i><strong><?php echo esc_html__( 'Note', 'wpssw' ); ?>: </strong><?php echo esc_html__( 'All the disabled sheet headers will be deleted from the current spreadsheet automatically. You can enable it again, clear the spreadsheet and click the "Click to Sync" button to make up to date data in the spreadsheet.', 'wpssw' ); ?></i>
									</div>
									<ul id="woo-coupon-sortable">
										<?php
										$wpssw_woo_is_checked = '';
										$wpssw_woo_selections = stripslashes_deep( self::wpssw_option( 'wpssw_woo_coupon_headers' ) );
										if ( ! $wpssw_woo_selections ) {
											$wpssw_woo_selections = array();
										}
										$wpssw_woo_selections_custom = stripslashes_deep( self::wpssw_option( 'wpssw_woo_coupon_headers_custom' ) );
										if ( ! $wpssw_woo_selections_custom ) {
											$wpssw_woo_selections_custom = array();
										}
										$wpssw_include = new WPSSW_Include_Action();
										$wpssw_include->wpssw_include_coupon_compatibility_files();
										$wpssw_woocoupon_headers = apply_filters( 'wpsyncsheets_coupon_headers', array() );
										$wpssw_woocoupon_headers = self::wpssw_array_flatten( $wpssw_woocoupon_headers );
										if ( ! empty( $wpssw_woo_selections ) ) {
											foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_val ) {
												$wpssw_woo_is_checked = 'checked';
												$wpssw_labelid        = strtolower( str_replace( ' ', '_', $wpssw_val ) );
												?>
											<li class="ui-state-default">
												<label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>">
													<span class="ui-icon ui-icon-caret-2-n-s"></span>
													<span class="wootextfield"><?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?></span>
													<span class="ui-icon ui-icon-pencil"></span>
													<input type="checkbox" name="woocoupon_custom[]" value="<?php echo isset( $wpssw_woo_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_woo_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>" class="woo-coupon-headers-chk1" <?php echo esc_html( $wpssw_woo_is_checked ); ?> hidden="true">
													<input type="checkbox" name="woocoupon_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-coupon-headers-chk" <?php echo esc_html( $wpssw_woo_is_checked ); ?>>
													<span class="checkbox-switch-new"></span>
												</label>
											</li>
												<?php
											}
										}
										if ( ! empty( $wpssw_woocoupon_headers ) ) {
											foreach ( $wpssw_woocoupon_headers as $wpssw_key => $wpssw_val ) {
												$wpssw_woo_is_checked = '';
												if ( in_array( $wpssw_val, $wpssw_woo_selections, true ) ) {
													continue;
												}
												$wpssw_labelid = strtolower( str_replace( ' ', '_', $wpssw_val ) );
												?>
											<li class="ui-state-default"><label for="woo-<?php echo esc_attr( $wpssw_labelid ); ?>"><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield"><?php echo esc_html( $wpssw_val ); ?></span><span class="ui-icon ui-icon-pencil"></span><input type="checkbox" name="woocoupon_custom[]" value="<?php echo esc_attr( $wpssw_val ); ?>" class="woo-coupon-headers-chk1" <?php echo esc_html( $wpssw_woo_is_checked ); ?> hidden="true"><input type="checkbox" name="woocoupon_header_list[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="woo-<?php echo esc_attr( $wpssw_labelid ); ?>" class="woo-coupon-headers-chk" <?php echo esc_html( $wpssw_woo_is_checked ); ?>><span class="checkbox-switch-new"></span></label></li>
												<?php
											}
										}
										?>
									</ul>
									<button type="button" class="wpssw-button wpssw-button-secondary" id="woo-couponselectall"><?php echo esc_html__( 'Select all', 'wpssw' ); ?></button>
									<button type="button" class="wpssw-button wpssw-button-secondary" id="woo-couponselectnone"><?php echo esc_html__( 'Select none', 'wpssw' ); ?></button>
								</div>
							</td>
						</tr>
						<?php if ( ! empty( $wpssw_couponsheet_id ) && ! empty( $wpssw_couponsheet_name ) ) { ?>
						<tr valign="top" id="couponsynctr" class="coupon_spreadsheet_row" >
							<th scope="row" class="titledesc">
								<label><?php echo esc_html__( 'Sync Coupons', 'wpssw' ); ?></label>
							</th>
							<td class="forminp">              
								<img src="images/spinner.gif" id="couponsyncloader"><span id="couponsynctext"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span><a class="wpssw-button" href="javascript:void(0)" id="couponsync">
									<?php echo esc_html__( 'Click to Sync', 'wpssw' ); ?></a><br><br><i><strong><?php echo esc_html__( 'Note:', 'wpssw' ); ?></strong> <?php echo esc_html__( 'Click to Sync button will append all the coupon data to the sheet.', 'wpssw' ); ?></i> 
							</td>
						</tr>
					<?php } ?>
					</table>
					<?php
					if ( WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
						?>
					<p class="submit"><input type="submit" name="submit" id="submit" class="wpssw-button wpssw-button-primary" value="Save"></p>
						<?php
					} else {
						echo 'WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!';
					}
					?>
				</form>
					<?php
				} else {
					echo esc_html__( 'Please genearate authentication code from', 'wpssw' );
					?>
				<strong><?php echo esc_html__( 'Google API Setting', 'wpssw' ); ?></strong>
				<a href='<?php echo esc_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ); ?>'> <?php echo esc_html__( 'Click', 'wpssw' ); ?></a>
				<?php } ?> 
			</div>
			<div id="em-settings" class="tabcontent">
				<h3><?php echo esc_html__( 'License', 'wpssw' ); ?></h3>
				<?php
				$wpssw_license = self::wpssw_option( 'wpssw_license_key' );
				$wpssw_status  = self::wpssw_option( 'wpssw_license_status' );
				?>
				<p class="f16"><strong><?php echo esc_html__( 'Where can I find my purchase code?', 'wpssw' ); ?></strong></p>
				<ol>
					<li><?php echo esc_html__( 'Please go to', 'wpssw' ); ?> <a target="_blank" href="https://codecanyon.net/downloads"><?php echo esc_html( 'Codecanyon.net/downloads' ); ?></a></li>
					<li><?php echo esc_html__( 'Click the', 'wpssw' ); ?> <strong><?php echo esc_html__( 'Download', 'wpssw' ); ?></strong> <?php echo esc_html__( 'button in WPSyncSheets For WooCommerce row.', 'wpssw' ); ?></li>
					<li><?php echo esc_html__( 'Select', 'wpssw' ); ?> <strong><?php echo esc_html__( 'License Certificate &amp; Purchase code.', 'wpssw' ); ?></strong></li>
					<li><?php echo esc_html__( 'Copy', 'wpssw' ); ?> <strong><?php echo esc_html__( 'Item Purchase Code', 'wpssw' ); ?></strong></li>
					<li><?php echo esc_html__( 'Please go to', 'wpssw' ); ?> <a target="_blank" href="https://www.wpsyncsheets.com/checkout/?edd_action=add_to_cart&download_id=1378&discount=ENVATO100"><?php echo esc_html__( 'WPSyncSheets Envato User Registration', 'wpssw' ); ?></a> <?php echo esc_html__( 'and register with item purchase code.', 'wpssw' ); ?></li>
					<li><?php echo esc_html__( 'After registration process click on View Licenses.', 'wpssw' ); ?></li>
					<li><?php echo esc_html__( 'Click on key icon', 'wpssw' ); ?> <span class="dashicons dashicons-admin-network"></span> <?php echo esc_html__( 'to copy license key &amp; paste below license key field.', 'wpssw' ); ?></li>
				</ol>
				<div class="data-collection">

						<p class="f16"><strong><?php echo esc_html__( 'Data collection', 'wpssw' ); ?></strong></p>
						<p><?php echo esc_html__( 'WPSyncSheets does not collect any personal data. However, we gather some basic information about your website to validate your license and product registration. These are:', 'wpssw' ); ?></p>

						<ol>
							<li><?php echo esc_html__( 'The purchase code that was used for product registration.', 'wpssw' ); ?></li>
							<li><?php echo esc_html__( 'The domain name that plugin uses.', 'wpssw' ); ?></li>
						</ol>

						<p><?php echo esc_html__( 'In order to serve and check for updates, from time to time, your WordPress installation establishes an anonymous connection to our servers.', 'wpssw' ); ?></p>

					</div>
				<div>
					<form method="post" action="<?php echo esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce&tab=em-settings' ) ); ?>">
						<?php
						if ( isset( $_GET['sl_activation'] ) && ! empty( sanitize_text_field( wp_unslash( $_GET['message'] ) ) ) ) {
							switch ( $_GET['sl_activation'] ) {
								case 'false':
									$message = urldecode( sanitize_text_field( wp_unslash( $_GET['message'] ) ) );
									?>
										<div class="error">
											<p><?php echo esc_html( $message ); ?></p>
										</div>
										<?php
									break;
								case 'true':
								default:
									break;
							}
						}
						?>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row" valign="top">
										<?php echo esc_html__( 'License Key', 'wpssw' ); ?>
									</th>
									<td>
										<?php
										$readonly = false;
										if ( false !== $wpssw_status && 'valid' === (string) $wpssw_status ) {
											$readonly = true;
										}
										if ( $readonly ) {
											?>
											<input id="wpssw_license_key" name="wpssw_license_key" type="text" class="regular-text" value="<?php echo esc_attr( $wpssw_license ); ?>" placeholder="<?php echo esc_html__( 'Enter your license key', 'wpssw' ); ?>" readonly/>
											<?php
										} else {
											?>
											<input id="wpssw_license_key" name="wpssw_license_key" type="text" class="regular-text" value="<?php echo esc_attr( $wpssw_license ); ?>" placeholder="<?php echo esc_html__( 'Enter your license key', 'wpssw' ); ?>"/>
											<?php
										}
										?>
										<label class="description" for="wpssw_license_key"></label>
										<a href="<?php echo esc_url( self::$wpssw_register_link ); ?>" target="_blank" class="wpssw-register-link"><?php echo esc_html__( 'Click to generate license key', 'wpssw' ); ?></a>
									</td>
								</tr>
					<?php if ( ! empty( $wpssw_license ) ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php echo esc_html__( 'Activate License', 'wpssw' ); ?>
							</th>
							<td>
								<?php if ( false !== $wpssw_status && 'valid' === (string) $wpssw_status ) { ?>
									<span class="wpssw-license-status"><?php echo esc_html__( 'Active', 'wpssw' ); ?></span>
									<?php wp_nonce_field( 'wpssw_edd_nonce', 'wpssw_edd_nonce' ); ?>
									<input type="submit" class="wpssw-button" name="wpssw_license_deactivate" value="<?php echo esc_html__( 'Deactivate License', 'wpssw' ); ?>"/>
									<?php
								} else {
									wp_nonce_field( 'wpssw_edd_nonce', 'wpssw_edd_nonce' );
									?>
									<input type="submit" class="wpssw-button" name="wpssw_license_activate" value="<?php echo esc_html__( 'Activate License', 'wpssw' ); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<input type="submit" name="submit"  class="wpssw-button wpssw-button-primary" value="Save">
		</form>
		<p class="box">
			<strong><?php echo esc_html__( 'Important!', 'wpssw' ); ?></strong> <?php echo esc_html__( 'One', 'wpssw' ); ?> <a target="_blank" href="https://codecanyon.net/licenses/standard">standard license</a> <?php echo esc_html__( 'is valid only for', 'wpssw' ); ?> <strong><?php echo esc_html__( '1 website', 'wpssw' ); ?></strong>. <?php echo esc_html__( 'Running multiple websites on a single license is a copyright violation.', 'wpssw' ); ?><br>
			<?php echo esc_html__( 'When moving a site from one domain to another please deregister the license key of the plugin first.', 'wpssw' ); ?>
		</p>
			</div>  
		</div>
		<?php
	}
	/**
	 * Get products meta values for WooCommerce Product Add on.
	 */
	public static function wpssw_get_all_meta_values() {
		global $wpdb;
		$wpssw_wc_cf_headers = array();
		$table_name          = $wpdb->prefix;
		$meta_key            = '_product_addons';
		// @codingStandardsIgnoreStart.
		$wpssw_querystr  = "SELECT {$wpdb->prefix}posts.* FROM {$wpdb->prefix}posts INNER JOIN {$wpdb->prefix}postmeta ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE 1=1 AND ( {$wpdb->prefix}postmeta.meta_key = '_product_addons' )"; //db call ok.
		$wpssw_postsmeta = $wpdb->get_results( $wpssw_querystr, ARRAY_A );
		// @codingStandardsIgnoreEnd.
		foreach ( $wpssw_postsmeta as $wpssw_cfield ) {
			$wpssw_value = get_post_meta( $wpssw_cfield['ID'], '_product_addons', true );
			if ( ! empty( $wpssw_value ) ) {
				foreach ( $wpssw_value as $wpssw_val ) {
					if ( 'heading' === (string) $wpssw_val['type'] ) {
						continue;
					}
					$wpssw_wc_cf_headers[] = $wpssw_val['name'];
				}
			}
		}
		return $wpssw_wc_cf_headers;
	}
	/**
	 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
	 *
	 * @return array Array of settings for @see woocommerce_admin_fields() function.
	 */
	public static function wpssw_get_settings() {
		$wpssw_status_array        = wc_get_order_statuses();
		$wpssw_settings            = array(
			'section_first_title'  => array(
				'name' => '',
				'type' => 'title',
				'desc' => 'Assign Google Spreadsheet will be automatically create sheet name and sheet headers as per the below settings and it will be create new rows whenever new orders has been placed.',
				'id'   => 'wc_google_sheet_settings_first_section_start',
			),
			array( 'type' => 'select_spreadsheet' ),
			array(
				'type' => 'new_spreadsheetname',
			),
			'section_first_end'    => array(
				'type' => 'sectionend',
				'id'   => 'wc_google_sheet_settings_first_section_end',
			),
			'section_second_title' => array(
				'name' => __( 'Default Order Status', 'wpssw' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'wc_google_sheet_settings_second_section_start',
			),
			array(
				'title'    => __( 'Pending Orders', 'wpssw' ),
				'id'       => 'pending_orders',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false,
			),
			array(
				'title'    => __( 'Processing Orders', 'wpssw' ),
				'id'       => 'processing_orders',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false,
			),
			array(
				'title'    => __( 'On hold Orders', 'wpssw' ),
				'id'       => 'on_hold_orders',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false,
			),
			array(
				'title'    => __( 'Completed Orders', 'wpssw' ),
				'id'       => 'completed_orders',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false,
			),
			array(
				'title'    => __( 'Cancelled Orders', 'wpssw' ),
				'id'       => 'cancelled_orders',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false,
			),
			array(
				'title'    => __( 'Refunded Orders', 'wpssw' ),
				'id'       => 'refunded_orders',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false,
			),
			array(
				'title'    => __( 'Failed Orders', 'wpssw' ),
				'id'       => 'failed_orders',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false,
			),
			array(
				'title'    => __( 'Trash Orders', 'wpssw' ),
				'id'       => 'trash',
				'default'  => 'yes',
				'type'     => 'checkbox',
				'autoload' => false,
			),
			array(
				'title'    => __( 'All Orders', 'wpssw' ),
				'id'       => 'all_orders',
				'default'  => 'no',
				'type'     => 'checkbox',
				'autoload' => false,
			),
			'section_second_end'   => array(
				'type' => 'sectionend',
				'id'   => 'wc_google_sheet_settings_second_section_end',
			),
			'section_third_title'  => array(
				'name' => '',
				'type' => 'title',
				'desc' => '',
				'id'   => 'wc_google_sheet_settings_third_section_start',
			),
			array( 'type' => 'manage_row_field' ),
			array( 'type' => 'set_headers' ),
			array( 'type' => 'product_category_as_order_filter' ),
			array( 'type' => 'product_as_sheet_header' ),
			array( 'type' => 'product_headers' ),
			array( 'type' => 'product_headers_append_after' ),
			'section_third_end'    => array(
				'type' => 'sectionend',
				'id'   => 'wc_google_sheet_settings_third_section_end',
			),
			'section_fourth_title' => array(
				'name'  => '',
				'type'  => 'title',
				'desc'  => '',
				'class' => 'section_fourth_end',
				'id'    => 'wc_google_sheet_settings_fourth_section_start',
			),
			array( 'type' => 'repeat_checkbox' ),
			array( 'type' => 'custom_headers_action' ),
			array( 'type' => 'sync_button' ),
			array(
				'title'    => __( 'Freeze Header', 'wpssw' ),
				'id'       => 'freeze_header',
				'default'  => 'no',
				'type'     => 'checkbox',
				'autoload' => false,
			),
			array( 'type' => 'order_asc_desc' ),
			array( 'type' => 'order_row_color' ),
			array( 'type' => 'price_format' ),
			array( 'type' => 'import_orders' ),
			'section_fourth_end'   => array(
				'type' => 'sectionend',
				'id'   => 'wc_google_sheet_settings_fourth_section_end',
			),
			'section_sixth_title'  => array(
				'name' => __( 'Graph Sheets', 'wpssw' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'wc_google_sheet_settings_sixth_section_start',
			),
			array( 'type' => 'add_graph_section' ),
			'section_sixth_end'    => array(
				'type' => 'sectionend',
				'id'   => 'wc_google_sheet_settings_sixth_section_end',
			),
		);
		$wpssw_custom_status_array = array();
		$wpssw_settingflag         = 0;
		foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
			$wpssw_status = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
			if ( ! in_array( $wpssw_status, self::$wpssw_default_status, true ) ) {
				$wpssw_settingflag++;
				if ( 1 === (int) $wpssw_settingflag ) {
					$wpssw_custom_status_array['section_fifth_title'] = array(
						'name' => __( 'Custom Order Status', 'wpssw' ),
						'type' => 'title',
						'desc' => '',
						'id'   => 'wc_google_sheet_settings_second_section_start',
					);
				}
				$wpssw_custom_status_array[ $wpssw_status ] = array(
					'title'    => $wpssw_val . ' Orders',
					'id'       => $wpssw_status,
					'default'  => 'No',
					'type'     => 'checkbox',
					'autoload' => false,
				);
			}
		}
		if ( $wpssw_settingflag > 0 ) {
			$wpssw_custom_status_array['section_fifth_end'] = array(
				'type' => 'sectionend',
				'id'   => 'wc_google_sheet_settings_second_section_end',
			);
		}
		if ( ! empty( $wpssw_custom_status_array ) ) {
			$wpssw_settings = array_slice( $wpssw_settings, 0, 15, true ) + $wpssw_custom_status_array + array_slice( $wpssw_settings, 15, count( $wpssw_settings ) - 1, true );
		}
		return $wpssw_settings;
	}
	/**
	 *
	 * Move a post or product to the Trash
	 *
	 * @param int $wpssw_order_id .
	 */
	public static function wpssw_wcgs_trash( $wpssw_order_id ) {

		$wpssw_order   = wc_get_order( $wpssw_order_id );
		$wpssw_product = wc_get_product( $wpssw_order_id );
		$wpssw_coupon  = new WC_Coupon( $wpssw_order_id );
		if ( isset( $wpssw_order ) && ! empty( $wpssw_order ) ) {
			global $post_type;
			if ( 'shop_order' !== (string) $post_type ) {
				return;
			}
			if ( $wpssw_order ) {
				$wpssw_old_status = $wpssw_order->get_status();
				/*Remove order detail from old status*/
				WPSSW_Order::wpssw_woo_order_status_change_custom( $wpssw_order_id, $wpssw_old_status, 'trash' );
			}

			/*
			* Move order detail to trash sheet
			*/
			if ( 'yes' === (string) self::wpssw_option( 'trash' ) ) {
				$wpssw_sheetname = 'Trash Orders';
			}
			if ( 'yes' === (string) self::wpssw_option( 'all_orders' ) ) {
				$wpssw_sheetname = 'All Orders';
				WPSSW_Order::wpssw_all_orders( $wpssw_order_id, $wpssw_sheetname );
			}
		}
		if ( isset( $wpssw_product ) && ! empty( $wpssw_product ) ) {
			$wpssw_spreadsheetid = self::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_sheetname     = 'All Products';
			if ( ! empty( $wpssw_spreadsheetid ) ) {
				if ( ! self::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
					return;
				}
				$wpssw_total          = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
				$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$wpssw_sheetid        = $wpssw_existingsheets[ $wpssw_sheetname ];
				if ( ! empty( $wpssw_product->get_parent_id() ) && 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_order_id = $wpssw_product->get_parent_id();
				}
				$wpssw_total_values      = $wpssw_total->getValues();
				$variation_product_id    = array_search( 'Product Id', $wpssw_total_values[0], true );
				$variation_product_index = array_column( $wpssw_total_values, $variation_product_id );
				foreach ( $variation_product_index as $index => $index_ids ) {
					if ( ! isset( $index_ids['0'] ) ) {
						unset( $variation_product_index[ $index ] );
					}
				}
				$product_keys = array_keys( self::wpssw_convert_int( $variation_product_index ), (int) $wpssw_order_id, true );
				if ( $wpssw_sheetid ) {
					$startindex           = $product_keys[0];
					$endindex             = $product_keys[0] + count( $product_keys );
					$param                = array();
					$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
					$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
				}
				try {
					if ( ! empty( $deleterequestarray ) ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpssw_spreadsheetid;
						$param['requestarray']  = $deleterequestarray;
						self::$instance_api->updatebachrequests( $param );
					}
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
		}
		if ( ! empty( $wpssw_coupon ) && empty( $wpssw_order ) && empty( $wpssw_product ) ) {
			$wpssw_spreadsheetid = self::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
			$wpssw_sheetname     = 'All Coupons';
			if ( ! empty( $wpssw_spreadsheetid ) ) {
				if ( ! self::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
					return;
				}
				$wpssw_allentry            = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
				$wpssw_data                = $wpssw_allentry->getValues();
				$wpssw_data                = array_map(
					function( $wpssw_element ) {
						if ( isset( $wpssw_element['0'] ) ) {
							return $wpssw_element['0'];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$response                  = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
				$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
				$wpssw_num                 = array_search( (int) $wpssw_order_id, self::wpssw_convert_int( $wpssw_data ), true );
				if ( $wpssw_num > 0 ) {
					$wpssw_startindex       = $wpssw_num;
					$wpssw_endindex         = $wpssw_num + 1;
					$param                  = array();
					$param                  = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
					$wpssw_requestbody      = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['requestarray']  = $wpssw_requestbody;
					self::$instance_api->updatebachrequests( $param );
				}
			}
		}
	}
	/**
	 * Untrash a products from the Trash
	 *
	 * @param int    $post_id .
	 * @param string $previous_status .
	 */
	public static function wpssw_wcgs_untrash( $post_id, $previous_status ) {
		$wpssw_product = wc_get_product( $post_id );
		$wpssw_coupon  = new WC_Coupon( $post_id );
		if ( isset( $wpssw_product ) && ! empty( $wpssw_product ) ) {
			$wpssw_spreadsheetid = self::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_sheetname     = 'All Products';
			if ( ! self::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				return;
			}
			if ( 0 !== (int) $wpssw_product->get_parent_id() ) {
				return;
			}
			if ( isset( $wpssw_product ) && ! empty( $wpssw_product ) ) {
				global $post_type;
				// @codingStandardsIgnoreStart.
				if ( ( 'product' !== (string) $post_type ) || ( isset( $_REQUEST['action'] ) && 'untrash' !== sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) ) {
					// @codingStandardsIgnoreEnd.
					return;
				}
				WPSSW_Product::wpssw_woocommerce_update_product( $post_id, $wpssw_product );
			}
		}
		if ( ! empty( $wpssw_coupon ) && empty( $wpssw_product ) ) {
			global $post_type;
			// @codingStandardsIgnoreStart.
			if ( ( 'shop_coupon' !== (string) $post_type ) || ( isset( $_REQUEST['action'] ) && 'untrash' !== sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) ) {
				// @codingStandardsIgnoreEnd.
				return;
			}
			$wpssw_spreadsheetid = self::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
			$wpssw_sheetname     = 'All Coupons';
			if ( ! self::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				return;
			}
			WPSSW_Coupon::wpssw_coupon_object_updated_props( $wpssw_coupon );
		}
	}
	/**
	 * Convert a multi-dimensional array into a single-dimensional array.
	 *
	 * @param array $wpssw_array .
	 * @return array
	 */
	public static function wpssw_array_flatten( $wpssw_array ) {
		if ( ! is_array( $wpssw_array ) ) {
			return false;
		}
		$wpssw_result = array();
		foreach ( $wpssw_array as $wpssw_key => $wpssw_value ) {
			if ( is_array( $wpssw_value ) ) {
				$wpssw_result = array_merge( $wpssw_result, self::wpssw_array_flatten( array_values( $wpssw_value ) ) );
			} else {
				$wpssw_result[ $wpssw_key ] = trim( $wpssw_value );
			}
		}
		return $wpssw_result;
	}
	/**
	 * Get headers using key.
	 *
	 * @param array  $wpssw_headers .
	 * @param string $array_key .
	 * @return array
	 */
	public static function get_headers_by_key( &$wpssw_headers = array(), $array_key = '' ) {
		if ( ! is_array( $wpssw_headers ) ) {
			return false;
		}
		if ( isset( $wpssw_headers['WPSSW_Default'][ $array_key ] ) ) {
			$wpssw_result = $wpssw_headers['WPSSW_Default'][ $array_key ];
			unset( $wpssw_headers['WPSSW_Default'][ $array_key ] );
		}
		return $wpssw_result;
	}
	/**
	 * Function to check string starting with given substring or not
	 *
	 * @param string $wpssw_string .
	 * @param string $wpssw_startstring .
	 */
	public static function wpssw_startswith( $wpssw_string, $wpssw_startstring ) {
		$wpssw_len = strlen( $wpssw_startstring );
		return ( substr( $wpssw_string, 0, $wpssw_len ) === $wpssw_startstring );
	}
	/**
	 * Delete sheets from spreadsheet
	 *
	 * @param string $wpssw_spreadsheetid .
	 * @param array  $wpssw_remove_sheet .
	 * @param array  $wpssw_existingsheets .
	 */
	public static function wpssw_delete_sheet( $wpssw_spreadsheetid, $wpssw_remove_sheet = array(), $wpssw_existingsheets = array() ) {
		foreach ( $wpssw_remove_sheet as $wpssw_sheetname ) {
			$wpssw_sid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
			try {
				$param                  = array();
				$param['spreadsheetid'] = $wpssw_spreadsheetid;
				$wpssw_response         = self::$instance_api->deletesheetobject( $param, $wpssw_sid );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}
	}
	/**
	 * Freeze the headers of the sheet
	 *
	 * @param string $wpssw_spreadsheetid .
	 * @param int    $wpssw_freeze .
	 * @param string $oddcolor .
	 * @param string $evencolor .
	 * @param string $wpssw_color .
	 * @param int    $wpssw_freeze_header .
	 */
	public static function wpssw_freeze_header( $wpssw_spreadsheetid, $wpssw_freeze, $oddcolor, $evencolor, $wpssw_color, $wpssw_freeze_header ) {
		$wpssw_response = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
		foreach ( $wpssw_response->getSheets() as $wpssw_key => $wpssw_value ) {
			// TODO: Assign values to desired properties of `requestBody`.
			if ( $wpssw_freeze_header ) {
				$wpssw_requestbody = self::$instance_api->freezeobject( $wpssw_value['properties']['sheetId'], $wpssw_freeze );
				try {
					$requestobject                  = array();
					$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
					$requestobject['requestbody']   = $wpssw_requestbody;
					$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			if ( $wpssw_color ) {
				self::wpssw_change_row_background_color( $wpssw_spreadsheetid, $wpssw_value['properties']['sheetId'], $oddcolor, $evencolor );
			}
		}
	}
	/**
	 * Change the row's background color
	 *
	 * @param string $wpssw_spreadsheetid .
	 * @param int    $sheetid .
	 * @param string $oddcolor .
	 * @param string $evencolor .
	 */
	public static function wpssw_change_row_background_color( $wpssw_spreadsheetid, $sheetid, $oddcolor, $evencolor ) {
		if ( ! self::$instance_api->checkcredenatials() ) {
			return;
		}
		list($r, $g, $b)    = array_map(
			function( $c ) {
				return hexdec( str_pad( $c, 2, $c ) );
			},
			str_split( ltrim( $oddcolor, '#' ), strlen( $oddcolor ) > 4 ? 2 : 1 )
		);
		list($er, $eg, $eb) = array_map(
			function( $c ) {
				return hexdec( str_pad( $c, 2, $c ) );
			},
			str_split( ltrim( $evencolor, '#' ), strlen( $evencolor ) > 4 ? 2 : 1 )
		);
		$range              = array(
			'sheetId' => $sheetid,
		);
		try {
			$param                  = array();
			$param['spreadsheetid'] = $wpssw_spreadsheetid;
			$param['range']         = $range;
			$param['r']             = $r;
			$param['g']             = $g;
			$param['b']             = $b;
			$param['er']            = $er;
			$param['eg']            = $eg;
			$param['eb']            = $eb;
			$wpssw_response         = self::$instance_api->addconditionalformatrule( $param );
		} catch ( Exception $e ) {
			echo esc_html( 'Message: ' . $e->getMessage() );
		}
	}
	/**
	 * Create new Spreadsheet
	 *
	 * @param string $wpssw_spreadsheetname .
	 */
	public static function wpssw_create_spreadsheet( $wpssw_spreadsheetname = '' ) {
		if ( ! empty( $wpssw_spreadsheetname ) ) {
			$wpssw_newsheetname = trim( $wpssw_spreadsheetname );

			/*
			*Create new spreadsheet
			*/
			$wpssw_requestbody   = self::$instance_api->createspreadsheetobject( $wpssw_newsheetname );
			$wpssw_response      = self::$instance_api->createspreadsheet( $wpssw_requestbody );
			$wpssw_spreadsheetid = $wpssw_response['spreadsheetId'];
			return $wpssw_spreadsheetid;
		}
		return '';
	}
	/**
	 * Get Sheet's Column index from total headers
	 *
	 * @param int $number .
	 * @return string $letter
	 */
	public static function wpssw_get_column_index( $number ) {
		if ( $number <= 0 ) {
			return null;
		}
		$temp;
		$letter = '';
		while ( $number > 0 ) {
			$temp   = ( $number - 1 ) % 26;
			$letter = chr( $temp + 65 ) . $letter;
			$number = ( $number - $temp - 1 ) / 26;
		}
		return $letter;
	}
	/**
	 * Get wpssw options from database
	 *
	 * @param string $key .
	 * @param string $type .
	 * @return string
	 */
	public static function wpssw_option( $key = '', $type = '' ) {
		$value = self::$instance_api->wpssw_option( $key, $type );
		return $value;
	}
	/**
	 * Update wpssw options
	 *
	 * @param string $key .
	 * @param string $value .
	 */
	public static function wpssw_update_option( $key = '', $value = '' ) {
		self::$instance_api->wpssw_update_option( $key, $value );
	}
	/**
	 * Reset Google API Settings
	 */
	public static function wpssw_reset_settings() {
		try {
			$wpssw_google_settings = self::wpssw_option( 'wpssw_google_settings' );
			$settings              = array();
			foreach ( $wpssw_google_settings as $key => $value ) {
				$settings[ $key ] = '';
			}
			self::wpssw_update_option( 'wpssw_google_settings', $settings );
			self::wpssw_update_option( 'wpssw_google_accessToken', '' );
			self::wpssw_update_option( 'wpssw_woocommerce_spreadsheet', '' );
			self::wpssw_update_option( 'wpssw_coupon_spreadsheet_id', '' );
			self::wpssw_update_option( 'wpssw_product_spreadsheet_id', '' );
			self::wpssw_update_option( 'wpssw_customer_spreadsheet_id', '' );
			self::wpssw_update_option( 'wpssw_event_spreadsheet_id', '' );
		} catch ( Exception $e ) {
			echo esc_html( 'Message: ' . $e->getMessage() );
		}
		echo 'successful';
		die();
	}
	/**
	 * Insert blank row into sheet
	 *
	 * @param string $wpssw_spreadsheetid .
	 * @param string $wpssw_sheetid .
	 * @param int    $product_id .
	 * @param array  $wpssw_array_value .
	 * @param array  $wpssw_data .
	 * @param int    $wpssw_startindex .
	 */
	public static function wpssw_insert_blankrow( $wpssw_spreadsheetid = '', $wpssw_sheetid = '', $product_id = 0, $wpssw_array_value = array(), $wpssw_data = array(), $wpssw_startindex = 0 ) {
		if ( ! self::$instance_api->checkcredenatials() ) {
			return;
		}
		if ( isset( $wpssw_array_value[0] ) && ! is_array( $wpssw_array_value[0] ) ) {
			$wpssw_array_value = array( $wpssw_array_value );
		}
		foreach ( $wpssw_data as $wpssw_key => $wpssw_value ) {
			if ( ! empty( $wpssw_value ) ) {
				if ( ( (int) $product_id < (int) $wpssw_value ) ) {
					$wpssw_varicount        = count( $wpssw_array_value );
					$wpssw_startindex       = $wpssw_key;
					$wpssw_endindex         = $wpssw_key + $wpssw_varicount;
					$param                  = array();
					$param                  = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
					$requestarray[]         = self::$instance_api->insertdimensionrequests( $param, 'ROWS' );
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['requestarray']  = $requestarray;
					$wpssw_response         = self::$instance_api->updatebachrequests( $param );
					break;
				}
			}
		}
		return $wpssw_startindex;
	}
	/**
	 * Added V5.7
	 * Check if The Events Calendar PRO and Event Tickets Plus plugins are active or not.
	 */
	public static function wpssw_is_event_calender_ticket_active() {
		if ( ! class_exists( 'Tribe__Tickets_Plus__Main' ) || ! class_exists( 'Tribe__Events__Pro__Main' ) || ! class_exists( 'Tribe__Tickets__Main' ) || ! class_exists( 'Tribe__Events__Main' ) ) {
			return false;
		}
		return true;
	}
	/**
	 * Convert to Integer.
	 *
	 * @param string $data Entry ids array.
	 */
	public static function wpssw_convert_string( $data ) {
		$data = array_map(
			function( $element ) {
				return ( is_string( $element ) ? (string) $element : $element );
			},
			$data
		);
		return $data;
	}
	/**
	 * Convert to Integer.
	 *
	 * @param string $data Entry ids array.
	 */
	public static function wpssw_convert_int( $data ) {
		$data = array_map(
			function( $element ) {
				return ( is_numeric( $element ) ? (int) $element : $element );
			},
			$data
		);
		return $data;
	}
	/**
	 * Find Related Class.
	 *
	 * @param array  $headers Headers array.
	 * @param string $headername Header Name.
	 */
	public static function wpssw_find_class( $headers, $headername ) {
		foreach ( $headers as $classname => $classheaders ) {
			if ( in_array( $headername, $classheaders, true ) ) {
				return $classname;
			}
		}
		return '';
	}
	/**
	 * Clean Order data array.
	 *
	 * @param array $wpssw_array Order data array.
	 * @param int   $wpssw_max max value.
	 * @return array $wpssw_array
	 */
	public static function wpssw_cleanarray( $wpssw_array, $wpssw_max ) {
		for ( $i = 0; $i < $wpssw_max; $i++ ) {
			if ( ! isset( $wpssw_array[ $i ] ) || is_null( $wpssw_array[ $i ] ) ) {
				$wpssw_array[ $i ] = '';
			} else {
				$wpssw_array[ $i ] = trim( $wpssw_array[ $i ] );
			}
		}
		ksort( $wpssw_array );
		return $wpssw_array;
	}
	/**
	 * Check that given spreadsheet and sheet exists or not.
	 *
	 * @param string $wpssw_spreadsheetid Spreadsheet ID.
	 * @param string $wpssw_sheetname Sheet Name.
	 */
	public static function wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) {
		$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
		if ( empty( $wpssw_spreadsheetid ) || empty( $wpssw_sheetname ) ) {
			return false;
		}
		if ( ! array_key_exists( $wpssw_spreadsheetid, $wpssw_spreadsheets_list ) ) {
			return false;
		} else {
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheets      = array_flip( $wpssw_existingsheetsnames );
			if ( ! in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
				return false;
			}
		}
		return true;
	}
	/**
	 * Activate License for site.
	 *
	 * @param string $data Posted Data.
	 */
	public static function wpssw_activate_license( $data = array() ) {
		// listen for our activate button to be clicked.

		if ( isset( $data['wpssw_license_activate'] ) ) {

			// run a quick security check.
			if ( ! check_admin_referer( 'wpssw_edd_nonce', 'wpssw_edd_nonce' ) ) {
				return; // get out if we didn't click the Activate button.
			}

			// retrieve the license from the database.
			$license = trim( self::wpssw_option( 'wpssw_license_key' ) );

			// data to send in our API request.
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => rawurlencode( WPSSW_PLUGIN_NAME ),
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post(
				self::$wpssw_store_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			// make sure the response came back okay.
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				$message = ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );

			} else {

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( false === $license_data->success ) {

					switch ( $license_data->error ) {

						case 'expired':
							$message = sprintf(
								/* translators: expiry date. */
								__( 'Your license key expired on %s.' ),
								date_i18n( self::wpssw_option( 'date_format' ), strtotime( $license_data->expires, mktime() ) )
							);
							break;

						case 'revoked':
							$message = __( 'Your license key has been disabled.' );
							break;

						case 'missing':
							$message = __( 'Invalid license.' );
							break;

						case 'invalid':
						case 'site_inactive':
							$message = __( 'Your license is not active for this URL.' );
							break;

						case 'item_name_mismatch':
							/* translators: the plugin name. */
							$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), 'wpssw' );
							break;

						case 'no_activations_left':
							$message = __( 'Your license key has reached its activation limit.' );
							break;

						default:
							$message = __( 'An error occurred, please try again.' );
							break;
					}
				}
			}

			// Check if anything passed on a message constituting a failure.
			if ( ! empty( $message ) ) {
				$base_url = admin_url( 'admin.php?page=' . self::$wpssw_license_page );
				$redirect = add_query_arg(
					array(
						'sl_activation' => 'false',
						'message'       => rawurlencode( $message ),
					),
					$base_url
				);

				wp_safe_redirect( $redirect );
				exit();
			}
			self::wpssw_update_option( 'wpssw_license_status', $license_data->license );
			wp_safe_redirect( admin_url( 'admin.php?page=' . self::$wpssw_license_page ) );
			exit();
		}
	}
	/**
	 * Deactivate License for site.
	 */
	public static function wpssw_deactivate_license() {

		$license = trim( self::wpssw_option( 'wpssw_license_key' ) );
		// run a quick security check.
		if ( ! check_admin_referer( 'wpssw_edd_nonce', 'wpssw_edd_nonce' ) ) {
			return; // get out if we didn't click the Deactivate button.
		}
		// data to send in our API request.
		$api_params = array(
			'edd_action'  => 'deactivate_license',
			'license'     => $license,
			'item_id'     => WPSSW_PLUGIN_ITEM_ID,
			'item_name'   => rawurlencode( WPSSW_PLUGIN_NAME ),
			'url'         => home_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		);

		$response = wp_remote_post(
			self::$wpssw_store_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

			$redirect = add_query_arg(
				array(
					'page'          => self::$wpssw_license_page,
					'sl_activation' => 'false',
					'message'       => rawurlencode( $message ),
				),
				admin_url( 'admin.php?page=' )
			);

			wp_safe_redirect( $redirect );
			exit();
		}

		// decode the license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 'deactivated' === $license_data->license ) {
			delete_option( 'wpssw_license_status' );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::$wpssw_license_page ) );
		exit();
	}

	/**
	 * Check License key.
	 */
	public static function wpssw_check_license_key() {
		$wpssw_license = self::wpssw_option( 'wpssw_license_key' );
		$wpssw_status  = self::wpssw_option( 'wpssw_license_status' );
		if ( false !== $wpssw_status && 'valid' === (string) $wpssw_status && ! empty( $wpssw_license ) ) {
			return true;
		} else {
			return false;
		}
	}
}
WPSSW_Setting::init();
