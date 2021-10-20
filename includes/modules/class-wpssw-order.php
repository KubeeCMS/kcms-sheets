<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Order' ) ) :
	/**
	 * Class WPSSW_Order.
	 */
	class WPSSW_Order extends WPSSW_Setting {
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
			$wpssw_include->wpssw_include_orderfield_hook();
			$wpssw_include->wpssw_include_order_hook();
			$wpssw_include->wpssw_include_order_ajax_hook();
			self::wpssw_google_api();
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
		 * Insert WPSyncSheets column at shop order page
		 *
		 * @param array $columns shop order columns array.
		 * @return array $columns
		 */
		public static function wpssw_shop_order_page_syncbtn_column( $columns ) {
			$reordered_columns   = array();
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$spreadsheets_list   = self::$instance_api->get_spreadsheet_listing();
			if ( ! self::$instance_api->checkcredenatials() || empty( $wpssw_spreadsheetid ) || ( ! empty( $wpssw_spreadsheetid ) && ! array_key_exists( $wpssw_spreadsheetid, $spreadsheets_list ) ) ) {
				return $columns;
			}
			// @codingStandardsIgnoreStart.
			if ( ! isset( $_GET['post_status'] ) || ( isset( $_GET['post_status'] ) && 'trash' !== sanitize_text_field( wp_unslash( $_GET['post_status'] ) ) ) ) {
			// @codingStandardsIgnoreEnd.
				// Inserting columns to a specific location.
				foreach ( $columns as $key => $column ) {
					$reordered_columns[ $key ] = $column;
					if ( 'order_total' === (string) $key ) {
						// Inserting after "Total" column.
						$reordered_columns['wpssw_syncbtn_column'] = __( 'WPSyncSheets', 'wpssw' );
					}
				}
				return $reordered_columns;
			}
			return $columns;
		}
		/**
		 * Insert Sync button in WPSyncSheets column at shop order page
		 *
		 * @param string $column shop order WPSyncSheets column.
		 * @param int    $post_id .
		 */
		public static function wpssw_shop_order_page_syncbtn( $column, $post_id ) {
			if ( 'wpssw_syncbtn_column' === (string) $column ) {
				echo '<a href="#" class="button wpssw_single_order_sync_btn">' . esc_html__( 'Click to Sync', 'wpssw' ) . '</a>
					<img src="' . esc_url( admin_url( 'images/spinner.gif' ) ) . '" class="syncbtnloader">';
			}
		}
		/**
		 * Insert Meta box in entry page
		 */
		public static function wpssw_add_syncbtn_meta_box() {
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$spreadsheets_list   = self::$instance_api->get_spreadsheet_listing();
			if ( ! self::$instance_api->checkcredenatials() || empty( $wpssw_spreadsheetid ) || ( ( ! empty( $wpssw_spreadsheetid ) && ! array_key_exists( $wpssw_spreadsheetid, $spreadsheets_list ) ) ) ) {
				return;
			}
			add_meta_box( 'wpssw_syncbtn_meta_box', 'WPSyncSheets', __CLASS__ . '::wpssw_syncbtn_meta_box_content', 'shop_order', 'side', 'default', null );
		}

		/**
		 * Save IP Address, User Name, User Agent into order meta.
		 *
		 * @param int $order_id Order id.
		 */
		public static function wpssw_woocommerce_checkout_update_order_meta( $order_id ) {
			if ( ! $order_id ) {
				return;
			}

			$wpssw_user      = wp_get_current_user();
			$wpssw_user_name = $wpssw_user->data;
			$wpssw_username  = $wpssw_user_name->display_name;

			if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && ! empty( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) ) ) ) {
				$wpssw_ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
			} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && ! empty( sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) {
				$wpssw_ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			} else {
				$wpssw_ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			}

			$wpssw_user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

			$wpssw_saved_ip_address = get_post_meta( $order_id, 'wpssw_ip_address', true );
			$wpssw_saved_user_agent = get_post_meta( $order_id, 'wpssw_user_agent', true );
			$wpssw_saved_username   = get_post_meta( $order_id, 'wpssw_username', true );

			if ( ! empty( $wpssw_saved_ip_address ) ) {
				$wpssw_ip_address = $wpssw_saved_ip_address;
			}

			if ( ! empty( $wpssw_saved_user_agent ) ) {
				$wpssw_user_agent = $wpssw_saved_user_agent;
			}

			if ( ! empty( $wpssw_saved_username ) ) {
				$wpssw_username = $wpssw_saved_username;
			}

			update_post_meta( $order_id, 'wpssw_ip_address', $wpssw_ip_address );
			update_post_meta( $order_id, 'wpssw_user_agent', $wpssw_user_agent );
			update_post_meta( $order_id, 'wpssw_username', $wpssw_username );
		}
		/**
		 * Change order status
		 *
		 * @param int    $wpssw_order_id .
		 * @param string $wpssw_old_status .
		 * @param string $wpssw_new_status .
		 */
		public static function wpssw_woo_order_status_change_custom( $wpssw_order_id, $wpssw_old_status, $wpssw_new_status ) {
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}

			/*
			 * Custom Order Status sheet setting
			 */
			$wpssw_custom_status_array = array();
			$wpssw_status_array        = wc_get_order_statuses();
			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				$wpssw_status = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
				if ( ! in_array( $wpssw_status, self::$wpssw_default_status, true ) ) {
					$wpssw_custom_status_array[ $wpssw_status ] = $wpssw_val;
				}
			}
			$wpssw_spreadsheetid       = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_old_staus_name      = '';
			$response                  = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
			if ( 'processing' === (string) $wpssw_old_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'processing_orders' ) ) {
					$wpssw_sheetid        = $wpssw_existingsheetsnames['Processing Orders'];
					$wpssw_old_staus_name = 'Processing Orders';
				}
			}
			if ( 'processing' === (string) $wpssw_new_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'processing_orders' ) ) {
					$wpssw_sheetname = 'Processing Orders';
				}
			}
			if ( 'on-hold' === (string) $wpssw_old_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'on_hold_orders' ) ) {
					$wpssw_sheetid        = $wpssw_existingsheetsnames['On Hold Orders'];
					$wpssw_old_staus_name = 'On Hold Orders';
				}
			}
			if ( 'on-hold' === (string) $wpssw_new_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'on_hold_orders' ) ) {
					$wpssw_sheetname = 'On Hold Orders';
				}
			}
			if ( 'pending' === (string) $wpssw_old_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'pending_orders' ) ) {
					$wpssw_sheetid        = $wpssw_existingsheetsnames['Pending Orders'];
					$wpssw_old_staus_name = 'Pending Orders';
				}
			}
			if ( 'pending' === (string) $wpssw_new_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'pending_orders' ) ) {
					$wpssw_sheetname = 'Pending Orders';
				}
			}
			if ( 'cancelled' === (string) $wpssw_old_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'cancelled_orders' ) ) {
					$wpssw_sheetid        = $wpssw_existingsheetsnames['Cancelled Orders'];
					$wpssw_old_staus_name = 'Cancelled Orders';
				}
			}
			if ( 'cancelled' === (string) $wpssw_new_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'cancelled_orders' ) ) {
					$wpssw_sheetname = 'Cancelled Orders';
				}
			}
			if ( 'refunded' === (string) $wpssw_old_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'refunded_orders' ) ) {
					$wpssw_sheetid        = $wpssw_existingsheetsnames['Refunded Orders'];
					$wpssw_old_staus_name = 'Refunded Orders';
				}
			}
			if ( 'refunded' === (string) $wpssw_new_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'refunded_orders' ) ) {
					$wpssw_sheetname = 'Refunded Orders';
				}
			}
			if ( 'failed' === (string) $wpssw_old_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'failed_orders' ) ) {
					$wpssw_sheetid        = $wpssw_existingsheetsnames['Failed Orders'];
					$wpssw_old_staus_name = 'Failed Orders';
				}
			}
			if ( 'failed' === (string) $wpssw_new_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'failed_orders' ) ) {
					$wpssw_sheetname = 'Failed Orders';
				}
			}
			if ( 'completed' === (string) $wpssw_old_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'completed_orders' ) ) {
					$wpssw_sheetid        = $wpssw_existingsheetsnames['Completed Orders'];
					$wpssw_old_staus_name = 'Completed Orders';
				}
			}
			if ( 'completed' === (string) $wpssw_new_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'completed_orders' ) ) {
					$wpssw_sheetname = 'Completed Orders';
				}
			}
			if ( 'trash' === (string) $wpssw_old_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'trash' ) ) {
					$wpssw_sheetid        = $wpssw_existingsheetsnames['Trash Orders'];
					$wpssw_old_staus_name = 'Trash Orders';
				}
			}
			if ( 'trash' === (string) $wpssw_new_status ) {
				if ( 'yes' === (string) parent::wpssw_option( 'trash' ) ) {
					$wpssw_sheetname = 'Trash Orders';
				}
			}
			if ( array_key_exists( $wpssw_old_status, $wpssw_custom_status_array ) ) {
				if ( 'yes' === (string) parent::wpssw_option( $wpssw_old_status ) ) {
					$wpssw_sheetid        = $wpssw_existingsheetsnames[ $wpssw_custom_status_array[ $wpssw_old_status ] . ' Orders' ];
					$wpssw_old_staus_name = $wpssw_custom_status_array[ $wpssw_old_status ] . ' Orders';
				}
			}
			if ( array_key_exists( $wpssw_new_status, $wpssw_custom_status_array ) ) {
				if ( 'yes' === (string) parent::wpssw_option( $wpssw_new_status ) ) {
					$wpssw_sheetname = $wpssw_custom_status_array[ $wpssw_new_status ] . ' Orders';
				}
			}
			/* Event settings function start */
			$wpssw_eventsheetnames = array();
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$response                  = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $response );
				$wpssw_order               = wc_get_order( $wpssw_order_id );
				$wpssw_items               = $wpssw_order->get_items();
				$wpssw_eventsheetnames     = array();
				foreach ( $wpssw_items as $wpssw_item ) {
					$wpssw_subvalue    = '';
					$wpssw_ticket      = wc_get_product( $wpssw_item['product_id'] );
					$wpssw_tickettitle = $wpssw_item['name'];
					$wpssw_ticket_meta = get_post_meta( $wpssw_item['product_id'] );
					if ( isset( $wpssw_ticket_meta['_tribe_wooticket_for_event'] ) ) {
						$wpssw_eventid = $wpssw_ticket_meta['_tribe_wooticket_for_event'][0];
						$term_obj_list = get_the_terms( $wpssw_eventid, 'tribe_events_cat' );
						foreach ( $term_obj_list as $term ) {
							if ( isset( $wpssw_existingsheetsnames[ $term->name ] ) ) {
								$wpssw_eventsheetnames[] = $term->name;
							}
						}
					}
				}
				$wpssw_eventsheetnames = array_values( array_unique( $wpssw_eventsheetnames ) );
				foreach ( $wpssw_eventsheetnames as $wpssw_sheet_name ) {
					if ( 'trash' === (string) $wpssw_new_status ) {
						$wpssw_eventsheetid = $wpssw_existingsheetsnames[ $wpssw_sheet_name ];
						$wpssw_sheet        = "'" . $wpssw_sheet_name . "'!A:A";
						$wpssw_allentry     = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
						$wpssw_data         = $wpssw_allentry->getValues();
						$wpssw_data         = array_map(
							function( $wpssw_element ) {
								if ( isset( $wpssw_element['0'] ) ) {
									return $wpssw_element['0'];
								} else {
									return '';
								}
							},
							$wpssw_data
						);
						$order_keys         = array_keys( parent::wpssw_convert_int( $wpssw_data ), (int) $wpssw_order_id, true );

						if ( $wpssw_sheetid ) {
							$param                = self::$instance_api->prepare_param( $wpssw_eventsheetid, $order_keys[0], $order_keys[0] + count( $order_keys ) );
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
					} else {
						self::wpssw_insert_data_into_sheet( $wpssw_order_id, $wpssw_sheet_name, 0, $wpssw_old_staus_name );
					}
				}
			}
			/* Event settings function end */
			if ( ! empty( $wpssw_sheetname ) ) {
				self::wpssw_insert_data_into_sheet( $wpssw_order_id, $wpssw_sheetname, 0, $wpssw_old_staus_name );
			}
			if ( ! empty( $wpssw_old_staus_name ) && ! empty( $wpssw_sheetid ) ) {
				self::wpssw_move_order( $wpssw_order_id, $wpssw_sheetid, $wpssw_old_staus_name );
			}
			if ( 'yes' === (string) parent::wpssw_option( 'all_orders' ) ) {
				$wpssw_sheetname = 'All Orders';
				self::wpssw_all_orders( $wpssw_order_id, $wpssw_sheetname );
			}
		}
		/**
		 * Process Update of shop orders
		 *
		 * @param int    $wpssw_post_id .
		 * @param object $wpssw_post .
		 */
		public static function wpssw_wc_woocommerce_process_post_meta( $wpssw_post_id, $wpssw_post ) {
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			if ( self::wpssw_check_product_category( $wpssw_post_id ) ) {
				return;
			}
			self::wpssw_wc_woocommerce_update_post_meta( $wpssw_post_id, $wpssw_post );
		}
		/**
		 * Added v4.6
		 * Check if orders are of in selected product categories from general settings or not.
		 *
		 * @param int $wpssw_order_id .
		 */
		public static function wpssw_check_product_category( $wpssw_order_id ) {
			$wpssw_category_filter     = parent::wpssw_option( 'wpssw_category_filter' );
			$wpssw_category_filter_ids = parent::wpssw_option( 'wpssw_category_filter_ids' );
			if ( $wpssw_category_filter && $wpssw_order_id ) {
				$wpssw_order = wc_get_order( $wpssw_order_id );
				$wpssw_items = $wpssw_order->get_items();
				foreach ( $wpssw_items as $wpssw_id => $wpssw_item ) {
					$wpssw_product_id = $wpssw_item['product_id'] ? $wpssw_item['product_id'] : '';
					if ( $wpssw_product_id > 0 ) {
						$product_cats_ids  = wc_get_product_term_ids( $wpssw_product_id, 'product_cat' );
						$is_allowed        = array_intersect( $product_cats_ids, $wpssw_category_filter_ids );
						$wpssw_ticket_meta = get_post_meta( $wpssw_item['product_id'] );
						if ( isset( $wpssw_ticket_meta['_tribe_wooticket_for_event'] ) ) {
							$wpssw_eventid = $wpssw_ticket_meta['_tribe_wooticket_for_event'][0];
							if ( parent::wpssw_is_event_calender_ticket_active() ) {
								$term_obj_list = get_the_terms( $wpssw_eventid, 'tribe_events_cat' );
								if ( ! empty( $term_obj_list ) && is_array( $term_obj_list ) ) {
									foreach ( $term_obj_list as $term ) {
										$is_allowed[] = $term->term_id;
									}
								} elseif ( $wpssw_eventid ) {
									$is_allowed[] = $wpssw_eventid;
								}
							}
							if ( $wpssw_eventid ) {
								$is_allowed[] = $wpssw_eventid;
							}
						}
						if ( ! empty( $is_allowed ) ) {
							return false;
						}
					}
				}
				return true;
			} else {
				return false;
			}
		}
		/**
		 * Update shop orders
		 *
		 * @param int    $wpssw_post_id .
		 * @param object $wpssw_post .
		 */
		public static function wpssw_wc_woocommerce_update_post_meta( $wpssw_post_id, $wpssw_post ) {
			if ( 'shop_order' !== (string) $wpssw_post->post_type ) {
				return;
			}
			$wpssw_order         = wc_get_order( $wpssw_post->ID );
			$wpssw_items         = $wpssw_order->get_items();
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_headers_name  = parent::wpssw_option( 'wpssw_sheet_headers_list' );
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				$wpssw_headers_name = array_merge( $wpssw_headers_name, $wpssw_woo_event_headers );
			}
			$wpssw_header_type = self::wpssw_is_productwise();
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			// @codingStandardsIgnoreStart.
			$wpssw_order_status = isset( $_REQUEST['order_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order_status'] ) ) : '';
			$wpssw_post_status  = isset( $_REQUEST['post_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_status'] ) ) : '';
			// @codingStandardsIgnoreEnd.
			if ( ! empty( $wpssw_spreadsheetid ) && ( $wpssw_order_status === $wpssw_post_status ) ) {
				$wpssw_values = self::wpssw_make_value_array( 'update', $wpssw_order->get_id() );
				do_action( 'wpssw_update_order', $wpssw_order->get_id(), $wpssw_values, $wpssw_order_status );
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_sheetname           = '';
				if ( 'wc-pending' === (string) $wpssw_order_status ) {
					if ( 'yes' === (string) parent::wpssw_option( 'pending_orders' ) ) {
						$wpssw_sheetid   = $wpssw_existingsheetsnames['Pending Orders'];
						$wpssw_sheetname = 'Pending Orders';
					}
				}
				if ( 'wc-processing' === (string) $wpssw_order_status ) {
					if ( 'yes' === (string) parent::wpssw_option( 'processing_orders' ) ) {
						$wpssw_sheetid   = $wpssw_existingsheetsnames['Processing Orders'];
						$wpssw_sheetname = 'Processing Orders';
					}
				}
				if ( 'wc-on-hold' === (string) $wpssw_order_status ) {
					if ( 'yes' === (string) parent::wpssw_option( 'on_hold_orders' ) ) {
						$wpssw_sheetid   = $wpssw_existingsheetsnames['On Hold Orders'];
						$wpssw_sheetname = 'On Hold Orders';
					}
				}
				if ( 'wc-failed' === (string) $wpssw_order_status ) {
					if ( 'yes' === (string) parent::wpssw_option( 'failed_orders' ) ) {
						$wpssw_sheetid   = $wpssw_existingsheetsnames['Failed Orders'];
						$wpssw_sheetname = 'Failed Orders';
					}
				}
				if ( 'wc-completed' === (string) $wpssw_order_status ) {
					if ( 'yes' === (string) parent::wpssw_option( 'completed_orders' ) ) {
						$wpssw_sheetid   = $wpssw_existingsheetsnames['Completed Orders'];
						$wpssw_sheetname = 'Completed Orders';
					}
				}
				if ( 'wc-cancelled' === (string) $wpssw_order_status ) {
					if ( 'yes' === (string) parent::wpssw_option( 'cancelled_orders' ) ) {
						$wpssw_sheetid   = $wpssw_existingsheetsnames['Cancelled Orders'];
						$wpssw_sheetname = 'Cancelled Orders';
					}
				}
				if ( 'wc-refunded' === (string) $wpssw_order_status ) {
					if ( 'yes' === (string) parent::wpssw_option( 'refunded_orders' ) ) {
						$wpssw_sheetid   = $wpssw_existingsheetsnames['Refunded Orders'];
						$wpssw_sheetname = 'Refunded Orders';
					}
				}
				/* Custom Order Status*/
				if ( empty( $wpssw_sheetname ) ) {
					$wpssw_custom_status_array = array();
					$wpssw_status_array        = wc_get_order_statuses();
					foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
						if ( ! in_array( $wpssw_key, self::$wpssw_default_status_slug, true ) ) {
							if ( (string) $wpssw_key === $wpssw_order_status ) {
								$wpssw_status = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
								if ( 'yes' === (string) parent::wpssw_option( $wpssw_status ) ) {
									$wpssw_sheetid   = $wpssw_existingsheetsnames[ $wpssw_val ];
									$wpssw_sheetname = $wpssw_val . ' Orders';
								}
							}
						}
					}
				}
				$wpssw_eventsheetnames = array();
				if ( parent::wpssw_is_event_calender_ticket_active() ) {
					$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
					$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
					$wpssw_items               = $wpssw_order->get_items();
					$wpssw_eventsheetnames     = array();
					foreach ( $wpssw_items as $wpssw_item ) {
						$wpssw_subvalue    = '';
						$wpssw_ticket      = wc_get_product( $wpssw_item['product_id'] );
						$wpssw_tickettitle = $wpssw_item['name'];
						$wpssw_ticket_meta = get_post_meta( $wpssw_item['product_id'] );
						if ( isset( $wpssw_ticket_meta['_tribe_wooticket_for_event'] ) ) {
							$wpssw_eventid = $wpssw_ticket_meta['_tribe_wooticket_for_event'][0];
							$term_obj_list = get_the_terms( $wpssw_eventid, 'tribe_events_cat' );
							foreach ( $term_obj_list as $term ) {
								if ( isset( $wpssw_existingsheetsnames[ $term->name ] ) ) {
									$wpssw_eventsheetnames[] = $term->name;
								}
							}
						}
					}
					$wpssw_eventsheetnames = array_unique( $wpssw_eventsheetnames );
					foreach ( $wpssw_eventsheetnames as  $wpsswsheetname ) {
						self::wpssw_insert_data_into_sheet( $wpssw_order->get_id(), $wpsswsheetname, 0 );
					}
				}
				if ( ! empty( $wpssw_sheetname ) ) {
					$wpssw_values      = self::wpssw_set_static_values( $wpssw_order->get_id(), $wpssw_sheetname, $wpssw_values );
					$wpssw_rangetofind = $wpssw_sheetname . '!A:A';
					$wpssw_allentry    = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
					$wpssw_data        = $wpssw_allentry->getValues();
					$wpssw_data        = array_map(
						function( $element ) {
							if ( isset( $element['0'] ) ) {
								return $element['0'];
							} else {
								return '';
							}
						},
						$wpssw_data
					);
					$wpssw_num         = array_search( (int) $wpssw_order->get_id(), parent::wpssw_convert_int( $wpssw_data ), true );
					if ( $wpssw_num > 0 ) {
						$wpssw_rownum = $wpssw_num + 1;
						// Add or Remove Row at spreadsheet.
						$wpssw_ordrow   = 0;
						$wpssw_notempty = 0;
						end( $wpssw_data );
						$wpssw_lastelement = key( $wpssw_data );
						reset( $wpssw_data );
						$wpssw_data_count = count( $wpssw_data );
						for ( $i = $wpssw_rownum; $i < $wpssw_data_count; $i++ ) {
							if ( (int) $wpssw_data[ $i ] === (int) $wpssw_order->get_id() ) {
								$wpssw_ordrow++;
								if ( (int) $wpssw_lastelement === (int) $i ) {
									$wpssw_ordrow++;
								}
							} else {
								if ( (int) $wpssw_lastelement === (int) $i ) {
									$wpssw_notempty = 1;
									if ( $wpssw_ordrow > 0 ) {
										$wpssw_ordrow++;
									}
								} else {
									$wpssw_ordrow++;
								}
								break;
							}
						}
						$wpssw_samerow = 0;
						if ( 0 === (int) $wpssw_ordrow ) {
							$wpssw_samerow = 1;
						}
						if ( 1 === (int) $wpssw_samerow && $wpssw_header_type && 0 === (int) $wpssw_notempty ) {
							$wpssw_alphabet   = range( 'A', 'Z' );
							$wpssw_alphaindex = '';
							$wpssw_is_id      = array_search( 'Product ID', $wpssw_headers_name, true );
							if ( $wpssw_is_id ) {
								$wpssw_alphaindex = $wpssw_alphabet[ $wpssw_is_id + 1 ];
							} else {
								$wpssw_is_name = array_search( 'Product Name', $wpssw_headers_name, true );
								if ( $wpssw_is_name ) {
									$wpssw_alphaindex = $wpssw_alphabet[ $wpssw_is_name + 1 ];
								}
							}
							if ( '' !== (string) $wpssw_alphaindex ) {
								$wpssw_rangetofind = $wpssw_sheetname . '!' . $wpssw_alphaindex . $wpssw_rownum . ':' . $wpssw_alphaindex;
								$wpssw_allentry    = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
								$wpssw_data        = $wpssw_allentry->getValues();
								$wpssw_data        = array_map(
									function( $wpssw_element ) {
										if ( isset( $wpssw_element['0'] ) ) {
											return $wpssw_element['0'];
										} else {
											return '';
										}
									},
									$wpssw_data
								);
								if ( ( count( $wpssw_values ) < count( $wpssw_data ) ) ) {
									$wpssw_ordrow  = count( $wpssw_data );
									$wpssw_samerow = 0;
								}
							}
						}
						if ( 1 === (int) $wpssw_notempty && 0 === (int) $wpssw_ordrow ) {
							$wpssw_samerow = 0;
							$wpssw_ordrow  = 1;
						}
						if ( ( count( $wpssw_values ) > (int) $wpssw_ordrow ) && 0 === (int) $wpssw_samerow ) {// Insert blank row into spreadsheet.
							$wpssw_endindex                 = count( $wpssw_values ) - (int) $wpssw_ordrow;
							$wpssw_endindex                 = (int) $wpssw_endindex + (int) $wpssw_rownum;
							$param                          = array();
							$param                          = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_rownum, $wpssw_endindex );
							$wpssw_batchupdaterequest       = self::$instance_api->insertdimensionobject( $param );
							$requestobject                  = array();
							$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
							$requestobject['requestbody']   = $wpssw_batchupdaterequest;
							$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
						} elseif ( count( $wpssw_values ) < (int) $wpssw_ordrow && 0 === (int) $wpssw_samerow ) {// Remove extra row from spreadhseet.
							$wpssw_endindex         = (int) $wpssw_ordrow - count( $wpssw_values );
							$wpssw_endindex         = (int) $wpssw_endindex + (int) $wpssw_rownum;
							$param                  = array();
							$param                  = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_rownum, $wpssw_endindex );
							$deleterequest          = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
							$param                  = array();
							$param['spreadsheetid'] = $wpssw_spreadsheetid;
							$param['requestarray']  = $deleterequest;
							$wpssw_response         = self::$instance_api->updatebachrequests( $param );
						}
						// End of add- remove row at spreadsheet.
						$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . $wpssw_rownum;
						$wpssw_requestbody   = self::$instance_api->valuerangeobject( $wpssw_values );
						$wpssw_params        = array( 'valueInputOption' => $wpssw_inputoption ); // USER_ENTERED.
						$param               = array();
						$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
						$wpssw_response      = self::$instance_api->updateentry( $param );
					} else {
						self::wpssw_insert_data_into_sheet( (int) $wpssw_order->get_id(), $wpssw_sheetname, 0 );
					}
				}
				// All Order.
				if ( 'yes' === (string) parent::wpssw_option( 'all_orders' ) ) {
					$wpssw_sheetname = 'All Orders';
					self::wpssw_all_orders( $wpssw_order->get_id(), $wpssw_sheetname );
				}
			}
		}
		/**
		 * Get all orders
		 *
		 * @param int    $wpssw_order_id .
		 * @param string $wpssw_sheetname .
		 */
		public static function wpssw_all_orders( $wpssw_order_id, $wpssw_sheetname ) {
			if ( self::wpssw_check_product_category( $wpssw_order_id ) ) {
				return;
			}
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_order         = wc_get_order( $wpssw_order_id );
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				return;
			}
			$wpssw_headers_name = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
			$wpssw_header_type  = self::wpssw_is_productwise();
			$wpssw_sheet        = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry     = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data         = $wpssw_allentry->getValues();
			$wpssw_data         = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$wpssw_num          = array_search( (int) $wpssw_order_id, parent::wpssw_convert_int( $wpssw_data ), true );
			if ( $wpssw_num > 0 ) {
				$wpssw_values              = self::wpssw_make_value_array( 'insert', $wpssw_order_id );
				$wpssw_values              = self::wpssw_set_static_values( $wpssw_order_id, $wpssw_sheetname, $wpssw_values );
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_sheetid             = $wpssw_existingsheetsnames['All Orders'];
				$wpssw_rownum              = $wpssw_num + 1;
				// Add or Remove Row at spreadsheet.
				$wpssw_ordrow   = 0;
				$wpssw_notempty = 0;
				end( $wpssw_data );
				$wpssw_lastelement = key( $wpssw_data );
				reset( $wpssw_data );
				$wpssw_data_count = count( $wpssw_data );
				for ( $i = $wpssw_rownum; $i < $wpssw_data_count; $i++ ) {
					if ( (int) $wpssw_data[ $i ] === (int) $wpssw_order->get_id() ) {
						$wpssw_ordrow++;
						if ( (int) $wpssw_lastelement === (int) $i ) {
							$wpssw_ordrow++;
						}
					} else {
						if ( (int) $wpssw_lastelement === (int) $i ) {
							$wpssw_notempty = 1;
							if ( $wpssw_ordrow > 0 ) {
								$wpssw_ordrow++;
							}
						} else {
							$wpssw_ordrow++;
						}
						break;
					}
				}
				$wpssw_samerow = 0;
				if ( 0 === (int) $wpssw_ordrow ) {
					$wpssw_samerow = 1;
				}
				if ( 1 === (int) $wpssw_samerow && $wpssw_header_type && 0 === (int) $wpssw_notempty ) {
					$wpssw_alphabet   = range( 'A', 'Z' );
					$wpssw_alphaindex = '';
					$wpssw_is_id      = array_search( 'Product ID', $wpssw_headers_name, true );
					if ( $wpssw_is_id ) {
						$wpssw_alphaindex = $wpssw_alphabet[ $wpssw_is_id + 1 ];
					} else {
						$wpssw_is_name = array_search( 'Product Name', $wpssw_headers_name, true );
						if ( $wpssw_is_name ) {
							$wpssw_alphaindex = $wpssw_alphabet[ $wpssw_is_name + 1 ];
						}
					}
					if ( '' !== (string) $wpssw_alphaindex ) {
						$wpssw_rangetofind = $wpssw_sheetname . '!' . $wpssw_alphaindex . $wpssw_rownum . ':' . $wpssw_alphaindex;
						$wpssw_allentry    = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
						$wpssw_data        = $wpssw_allentry->getValues();
						$wpssw_data        = array_map(
							function( $wpssw_element ) {
								if ( isset( $wpssw_element['0'] ) ) {
									return $wpssw_element['0'];
								} else {
									return '';
								}
							},
							$wpssw_data
						);
						if ( ( count( $wpssw_values ) < count( $wpssw_data ) ) ) {
							$wpssw_ordrow  = count( $wpssw_data );
							$wpssw_samerow = 0;
						}
					}
				}
				if ( 1 === (int) $wpssw_notempty && 0 === (int) $wpssw_ordrow ) {
					$wpssw_samerow = 0;
					$wpssw_ordrow  = 1;
				}
				if ( ( count( $wpssw_values ) > (int) $wpssw_ordrow ) && 0 === (int) $wpssw_samerow ) {// Insert blank row into spreadsheet.
					$wpssw_endindex                 = count( $wpssw_values ) - (int) $wpssw_ordrow;
					$wpssw_endindex                 = (int) $wpssw_endindex + (int) $wpssw_rownum;
					$param                          = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_rownum, $wpssw_endindex );
					$wpssw_batchupdaterequest       = self::$instance_api->insertdimensionobject( $param );
					$requestobject                  = array();
					$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
					$requestobject['requestbody']   = $wpssw_batchupdaterequest;
					$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
				} elseif ( count( $wpssw_values ) < (int) $wpssw_ordrow && 0 === (int) $wpssw_samerow ) {// Remove extra row from spreadhseet.
					$wpssw_endindex         = (int) $wpssw_ordrow - count( $wpssw_values );
					$wpssw_endindex         = (int) $wpssw_endindex + (int) $wpssw_rownum;
					$param                  = array();
					$param                  = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_rownum, $wpssw_endindex );
					$deleterequest          = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['requestarray']  = $deleterequest;
					$wpssw_response         = self::$instance_api->updatebachrequests( $param );
				}
				// End of add- remove row at spreadsheet.
				$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . $wpssw_rownum;
				$wpssw_requestbody   = self::$instance_api->valuerangeobject( $wpssw_values );
				$wpssw_params        = array( 'valueInputOption' => $wpssw_inputoption ); // USER_ENTERED.
				$param               = array();
				$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
				$wpssw_response      = self::$instance_api->updateentry( $param );
			} else {
				self::wpssw_insert_data_into_sheet( $wpssw_order_id, $wpssw_sheetname, 0 );
			}
		}
		/**
		 * Insert Order data into sheet provided by $wpssw_sheetname.
		 *
		 * @param int    $wpssw_order_id .
		 * @param string $wpssw_sheetname .
		 * @param int    $wpssw_flag .
		 * @param string $wpssw_old_staus_name .
		 */
		public static function wpssw_insert_data_into_sheet( $wpssw_order_id, $wpssw_sheetname, $wpssw_flag = 0, $wpssw_old_staus_name = '' ) {
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			if ( self::wpssw_check_product_category( $wpssw_order_id ) ) {
				return;
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_inputoption   = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_order        = wc_get_order( $wpssw_order_id );
			$wpssw_headers_name = parent::wpssw_option( 'wpssw_sheet_headers_list' );
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				$wpssw_headers_name = array_merge( $wpssw_headers_name, $wpssw_woo_event_headers );
			}
			$wpssw_header_type = self::wpssw_is_productwise();
			$order_ascdesc     = parent::wpssw_option( 'wpssw_order_ascdesc' );
			if ( ! empty( $wpssw_spreadsheetid ) ) {
				$wpssw_prdarray = self::wpssw_make_value_array( 'insert', $wpssw_order_id );
				$wpssw_prdarray = self::wpssw_set_static_values( $wpssw_order_id, $wpssw_old_staus_name, $wpssw_prdarray );
				do_action( 'wpssw_insert_new_order', $wpssw_order_id, $wpssw_prdarray, $wpssw_sheetname );
				if ( 1 === (int) $wpssw_flag ) {
					return $wpssw_prdarray;
				}
				if ( 0 === (int) $wpssw_flag ) {
					$wpssw_values              = $wpssw_prdarray;
					$wpssw_sheet               = "'" . $wpssw_sheetname . "'!A:A";
					$wpssw_allentry            = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
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
					$wpssw_append              = 0;
					$wpssw_desc_append         = 0;
					$wpssw_event_data_append   = 0;
					$wpssw_num                 = array_search( (int) $wpssw_order_id, parent::wpssw_convert_int( $wpssw_data ), true );

					foreach ( $wpssw_data as $wpssw_key => $wpssw_value ) {
						if ( ! empty( $wpssw_value ) ) {
							if ( parent::wpssw_is_event_calender_ticket_active() ) {
								if ( 0 === (int) $wpssw_event_data_append ) {
									$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values );
									$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
									if ( $wpssw_num > 0 ) {
										$wpssw_event_data_append = 1;
										self::wpssw_insert_event_data_into_sheet( $wpssw_order_id, $wpssw_sheetname );
									}
								}
							}
							if ( ( (int) $wpssw_order_id < (int) $wpssw_value ) && 'descorder' !== (string) $order_ascdesc && 0 === (int) $wpssw_event_data_append && $wpssw_num < 1 ) {
								$wpssw_append            = 1;
								$wpssw_event_data_append = 1;
								if ( $wpssw_header_type ) {
									$wpssw_prdcount   = count( $wpssw_order->get_items() );
									$wpssw_startindex = $wpssw_key;
									$wpssw_endindex   = $wpssw_key + $wpssw_prdcount;
								} else {
									$wpssw_startindex = $wpssw_key;
									$wpssw_endindex   = $wpssw_key + 1;
								}
								$param                          = array();
								$param                          = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
								$wpssw_batchupdaterequest       = self::$instance_api->insertdimensionobject( $param );
								$requestobject                  = array();
								$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
								$requestobject['requestbody']   = $wpssw_batchupdaterequest;
								$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
								$wpssw_start_index              = $wpssw_startindex + 1;
								$wpssw_rangetoupdate            = $wpssw_sheetname . '!A' . $wpssw_start_index;
								$wpssw_requestbody              = self::$instance_api->valuerangeobject( $wpssw_values );
								$wpssw_params                   = array( 'valueInputOption' => $wpssw_inputoption );
								$param                          = array();
								$param                          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
								$wpssw_response                 = self::$instance_api->updateentry( $param );
								break;
							}
							if ( 'descorder' === (string) $order_ascdesc ) {
								if ( ( (int) $wpssw_order_id > (int) $wpssw_value ) && (int) $wpssw_value > 0 && 0 === (int) $wpssw_event_data_append && $wpssw_num < 1 ) {
									$wpssw_append            = 1;
									$wpssw_desc_append       = 1;
									$wpssw_event_data_append = 1;
									if ( $wpssw_header_type ) {
										$wpssw_prdcount   = count( $wpssw_order->get_items() );
										$wpssw_startindex = $wpssw_key;
										$wpssw_endindex   = $wpssw_key + $wpssw_prdcount;
									} else {
										$wpssw_startindex = $wpssw_key;
										$wpssw_endindex   = $wpssw_key + 1;
									}
									$param                          = array();
									$param                          = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
									$wpssw_batchupdaterequest       = self::$instance_api->insertdimensionobject( $param );
									$requestobject                  = array();
									$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
									$requestobject['requestbody']   = $wpssw_batchupdaterequest;
									$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
									$wpssw_start_index              = $wpssw_startindex + 1;
									$wpssw_rangetoupdate            = $wpssw_sheetname . '!A' . $wpssw_start_index;
									$wpssw_requestbody              = self::$instance_api->valuerangeobject( $wpssw_values );
									$wpssw_params                   = array( 'valueInputOption' => $wpssw_inputoption );
									$param                          = array();
									$param                          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
									$wpssw_response                 = self::$instance_api->updateentry( $param );
									break;
								}
							}
						}
					}
					if ( 0 === (int) $wpssw_desc_append && 'descorder' === (string) $order_ascdesc && 0 === (int) $wpssw_event_data_append && $wpssw_num < 1 ) {
						$wpssw_append            = 1;
						$wpssw_event_data_append = 1;
						$wpssw_requestbody       = self::$instance_api->valuerangeobject( $wpssw_values );
						$wpssw_params            = array( 'valueInputOption' => $wpssw_inputoption );
						$param                   = array();
						$param                   = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_sheetname, $wpssw_requestbody, $wpssw_params );
						$wpssw_response          = self::$instance_api->appendentry( $param );
					}
					if ( 0 === (int) $wpssw_append && 0 === (int) $wpssw_event_data_append ) {
						$wpssw_isupdated         = 0;
						$wpssw_event_data_append = 1;
						$wpssw_requestbody       = self::$instance_api->valuerangeobject( $wpssw_values );
						$wpssw_params            = array( 'valueInputOption' => $wpssw_inputoption );
						if ( count( $wpssw_data ) > 1 ) {
							$wpssw_num = array_search( (int) $wpssw_order_id, parent::wpssw_convert_int( $wpssw_data ), true );
							if ( $wpssw_num > 0 ) {
								$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . ( $wpssw_num + 1 );
							} else {
								$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . ( count( $wpssw_data ) + 1 );
							}
							$param           = array();
							$param           = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
							$wpssw_response  = self::$instance_api->updateentry( $param );
							$wpssw_isupdated = 1;
						}
						if ( 0 === (int) $wpssw_isupdated ) {
							$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_sheetname, $wpssw_requestbody, $wpssw_params );
							$wpssw_response = self::$instance_api->appendentry( $param );
						}
					}
				}
			}
		}
		/**
		 * Equal value array.
		 *
		 * @param array $temp Value array.
		 * @param array $wpssw_items Item array.
		 *
		 * @return array
		 */
		public static function wpssw_make_equal( $temp, $wpssw_items ) {
			$wpssw_is_repeat = parent::wpssw_option( 'wpssw_repeat_checkbox' );
			$wpssw_new_array = array();
			$value           = self::wpssw_array_flatten( $temp );
			if ( 'yes' === (string) $wpssw_is_repeat ) {
				for ( $i = 0; $i < $wpssw_items; $i++ ) {
					$wpssw_new_array[] = isset( $value[0] ) ? array( $value[0] ) : array();
				}
			} else {
				for ( $i = 0; $i < $wpssw_items; $i++ ) {
					$wpssw_new_array[] = isset( $value[ $i ] ) ? array( $value[ $i ] ) : array( 0 => '' );
				}
			}
			return $wpssw_new_array;
		}
		/**
		 * Mearge arrays
		 *
		 * @param array $array1 .
		 * @param array $array2 .
		 *
		 * @return array
		 */
		public static function wpssw_array_merge_recursive_distinct( &$array1, &$array2 ) {
			$merged = $array1;
			foreach ( $array2 as $key => &$value ) {
				if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
					$merged[ $key ] = array_merge( $merged[ $key ], $value );
				} else {
					$merged[ $key ] = $value;
				}
			}
			return $merged;
		}
		/**
		 * Prepare array value of order data to insert into sheet.
		 *
		 * @param string $wpssw_operation operation to perfom on sheet.
		 * @param int    $wpssw_order_id .
		 */
		public static function wpssw_make_value_array( $wpssw_operation = 'insert', $wpssw_order_id = 0 ) {
			$wpssw_order                = wc_get_order( $wpssw_order_id );
			$wpssw_order_data           = $wpssw_order->get_data();
			$wpssw_inputoption          = parent::wpssw_option( 'wpssw_inputoption' );
			$wpssw_static_header_values = stripslashes_deep( parent::wpssw_option( 'wpssw_static_header_values' ) );
			$wpssw_custom_value         = array();
			$wpssw_static_header_name   = array();
			if ( ! empty( $wpssw_static_header_values ) ) {
				foreach ( $wpssw_static_header_values as $wpssw_static_header_value ) {
					if ( strpos( $wpssw_static_header_value, ',(static_header),' ) ) {
						$wpssw_static_header_value = str_replace( ',(static_header),', ',', $wpssw_static_header_value );
						$wpssw_custom_value[]      = explode( ',', $wpssw_static_header_value );
					}
				}
				if ( ! empty( $wpssw_custom_value ) ) {
					$wpssw_static_header_name = array_column( $wpssw_custom_value, 0 );
				}
			}
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_filterd = array();
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_order_compatibility_files();
			$wpssw_header_type = self::wpssw_is_productwise();
			$wpssw_headers     = apply_filters( 'wpsyncsheets_order_headers', array() );

			$wpssw_headers_name = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
			$wpssw_items        = $wpssw_order->get_items();
			/**
			 * Get custom headers
			 */
			$wpssw_extra_headers     = array();
			$wpssw_extra_headers     = apply_filters( 'woosheets_custom_headers', array() );
			$wpssw_temp_headers      = array();
			$wpssw_value             = array();
			$wpssw_prdarray          = array();
			$wpssw_value[0]          = $wpssw_order_id;
			$wpssw_headers_name      = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
			$wpssw_woo_event_headers = array();
			$wpssw_eventheaders      = array();
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				$wpssw_headers_name = array_merge( $wpssw_headers_name, $wpssw_woo_event_headers );
				$wpssw_include->wpssw_include_event_compatibility_files();
				$wpssw_eventheaders = apply_filters( 'wpsyncsheets_event_headers', array() );
				if ( ! is_array( $wpssw_eventheaders ) ) {
					$wpssw_eventheaders = array();
				}
				$wpssw_headers = array_merge( $wpssw_headers, $wpssw_eventheaders );
			}

			$wpssw_product_headers = stripslashes_deep( parent::wpssw_option( 'wpssw_product_sheet_headers_list' ) );

			$wpssw_header_type              = self::wpssw_is_productwise();
			$wpssw_headers['WPSSW_Default'] = parent::wpssw_array_flatten( $wpssw_headers['WPSSW_Default'] );
			$wpssw_classarray               = array();
			$wpssw_headers_name_count       = count( $wpssw_headers_name );
			for ( $i = 0; $i < $wpssw_headers_name_count; $i++ ) {
				if ( in_array( $wpssw_headers_name[ $i ], $wpssw_static_header_name, true ) ) {
					$wpssw_classarray[ $wpssw_headers_name[ $i ] ] = 'WPSSW_Default';
					continue;
				}
				if ( in_array( $wpssw_headers_name[ $i ], $wpssw_product_headers, true ) ) {
					$wpssw_classarray[ $wpssw_headers_name[ $i ] ] = 'WPSSW_Default';
					continue;
				}
				$wpssw_classarray[ $wpssw_headers_name[ $i ] ] = parent::wpssw_find_class( $wpssw_headers, $wpssw_headers_name[ $i ] );
			}
			$wpssw_order_row = array();
			$wpssw_temp      = array();
			$wpssw_items     = $wpssw_order->get_items();
			if ( $wpssw_header_type ) {
				$wpssw_rcount = 0;
				foreach ( $wpssw_items as $wpssw_item ) {
					$wpssw_order_row[ $wpssw_rcount ][] = $wpssw_order_id;
					$wpssw_temp[ $wpssw_rcount ][]      = '';
					$wpssw_rcount++;
				}
			}
			if ( ! $wpssw_header_type ) {
				$wpssw_order_row   = array();
				$wpssw_order_row[] = $wpssw_order_id;
			}

			foreach ( $wpssw_classarray as $headername => $classname ) {
				$temp = array();
				if ( ! empty( $classname ) ) {
					$header_value = $classname::get_value( $headername, $wpssw_order, 'insert', $wpssw_custom_value, $wpssw_product_headers );
					if ( is_array( $header_value ) ) {
						$temp = array_chunk( $header_value, 1 );
					}
					if ( empty( $temp ) ) {
						$temp = $wpssw_temp;
					}
				} else {
					$temp = $wpssw_temp;
				}
				if ( $wpssw_header_type ) {
					if ( count( $temp ) !== count( $wpssw_items ) ) {
						$temp = self::wpssw_make_equal( $temp, count( $wpssw_items ) );
					}
					$wpssw_order_row = self::wpssw_array_merge_recursive_distinct( $wpssw_order_row, $temp );
				} else {
					$wpssw_val = parent::wpssw_array_flatten( $temp );
					if ( ! empty( array_filter( $wpssw_val ) ) ) {
						$wpssw_order_row[] = implode( ', ', array_filter( $wpssw_val ) );
					} else {
						$wpssw_order_row[] = '';
					}
				}
			}
			if ( $wpssw_header_type ) {
				foreach ( $wpssw_order_row as $wpssw_arrykey => $wpssw_valarray ) {
					$wpssw_order_row[ $wpssw_arrykey ] = self::wpssw_order_clean_array( $wpssw_valarray );
				}
			} else {

				$wpssw_order_row = self::wpssw_order_clean_array( $wpssw_order_row );
				$wpssw_order_row = array( $wpssw_order_row );
			}
			$wpssw_order_row = apply_filters( 'woosheets_values', $wpssw_order_row );

			return $wpssw_order_row;
		}
		/**
		 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
		 *
		 * @uses woocommerce_update_options()
		 * @uses self::wpssw_get_settings()
		 */
		public static function wpssw_update_settings() {
			if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$wpssw_spreadsheetid = self::wpssw_create_sheet( $_POST );
			if ( isset( $_POST['header_fields'] ) ) {
				$wpssw_header        = array();
				$wpssw_header_custom = array();
				if ( isset( $_POST['prdassheetheaders'] ) && isset( $_POST['wpssw_append_after'] ) && in_array(
					$_POST['wpssw_append_after'],
					array_map(
						function( $key ) {
							return str_replace( ' ', '-', strtolower( $key ) ); },
						array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields'] ) )
					),
					true
				) ) {
					$flag = 0;
					foreach ( array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields'] ) ) as $headers ) {
						$wpssw_header[]        = $headers;
						$wpssw_header_custom[] = isset( $_POST['header_fields_custom'][ $flag ] ) ? sanitize_text_field( wp_unslash( $_POST['header_fields_custom'][ $flag ] ) ) : '';
						if ( str_replace( ' ', '-', strtolower( $headers ) ) === str_replace( ' ', '-', strtolower( sanitize_text_field( wp_unslash( $_POST['wpssw_append_after'] ) ) ) ) && isset( $_POST['product_header_fields'] ) ) {
							foreach ( array_map( 'sanitize_text_field', wp_unslash( $_POST['product_header_fields'] ) ) as $prd_header ) {
								$wpssw_header[] = $prd_header;
							}
							if ( isset( $_POST['product_header_fields_custom'] ) ) {
								foreach ( array_map( 'sanitize_text_field', wp_unslash( $_POST['product_header_fields_custom'] ) ) as $prd_header ) {
									$wpssw_header_custom[] = $prd_header;
								}
							}
						}
						$flag++;
					}
				} else {
					if ( isset( $_POST['prdassheetheaders'] ) && isset( $_POST['product_header_fields'] ) && is_array( sanitize_text_field( wp_unslash( $_POST['product_header_fields'] ) ) ) && isset( $_POST['prdassheetheaders'] ) ) {
						$wpssw_header        = array_merge( sanitize_text_field( wp_unslash( $_POST['header_fields'] ) ), sanitize_text_field( wp_unslash( $_POST['product_header_fields'] ) ) );
						$wpssw_header_custom = array_merge( sanitize_text_field( wp_unslash( $_POST['header_fields_custom'] ) ), sanitize_text_field( wp_unslash( $_POST['product_header_fields_custom'] ) ) );
					} else {
						$wpssw_header        = array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields'] ) );
						$wpssw_header_custom = array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields_custom'] ) );
					}
				}
				if ( isset( $_POST['prdassheetheaders'] ) ) {
					parent::wpssw_update_option( 'wpssw_prdassheetheaders', sanitize_text_field( wp_unslash( $_POST['prdassheetheaders'] ) ) );
					parent::wpssw_update_option( 'wpssw_append_after', sanitize_text_field( wp_unslash( $_POST['wpssw_append_after'] ) ) );
					if ( isset( $_POST['product_header_fields'] ) ) {
						$wpssw_product_headers = array_map( 'sanitize_text_field', stripslashes_deep( $_POST['product_header_fields'] ) );
						parent::wpssw_update_option( 'wpssw_product_sheet_headers_list', $wpssw_product_headers );
					}
					if ( isset( $_POST['product_header_fields_custom'] ) ) {
						$product_header_fields_custom = array_map( 'sanitize_text_field', stripslashes_deep( $_POST['product_header_fields_custom'] ) );
						parent::wpssw_update_option( 'wpssw_product_sheet_headers_list_custom', $product_header_fields_custom );
					}
				} else {
					parent::wpssw_update_option( 'wpssw_prdassheetheaders', '' );
					parent::wpssw_update_option( 'wpssw_append_after', '' );
					$wpssw_product_headers = array();
					parent::wpssw_update_option( 'wpssw_product_sheet_headers_list', $wpssw_product_headers );
					parent::wpssw_update_option( 'wpssw_product_sheet_headers_list_custom', $wpssw_product_headers );
				}
				if ( isset( $_POST['category_filter'] ) ) {
					$wpssw_category_filter = sanitize_text_field( wp_unslash( $_POST['category_filter'] ) );
					$productcat_filter     = isset( $_POST['productcat_filter'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['productcat_filter'] ) ) : '';
					parent::wpssw_update_option( 'wpssw_category_filter', $wpssw_category_filter );
					parent::wpssw_update_option( 'wpssw_category_filter_ids', $productcat_filter );
				} else {
					parent::wpssw_update_option( 'wpssw_category_filter', '' );
					parent::wpssw_update_option( 'wpssw_category_filter_ids', '' );
				}
				$wpssw_my_headers = stripslashes_deep( $wpssw_header );
				parent::wpssw_update_option( 'wpssw_sheet_headers_list', $wpssw_my_headers );
				$wpssw_mycustom_headers = stripslashes_deep( $wpssw_header_custom );
				parent::wpssw_update_option( 'wpssw_sheet_headers_list_custom', $wpssw_mycustom_headers );
				// Append after dropdown value array.
				if ( isset( $_POST['header_fields'] ) ) {
					$keys                     = array_map(
						function( $key ) {
							return str_replace( ' ', '-', strtolower( $key ) );
						},
						array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields'] ) )
					);
					$wpssw_append_after_array = array_combine( $keys, array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields_custom'] ) ) );
					parent::wpssw_update_option( 'wpssw_append_after_array', $wpssw_append_after_array );
				}
				woocommerce_update_options( self::wpssw_get_settings() );
				parent::wpssw_update_option( 'wpssw_woocommerce_spreadsheet', $wpssw_spreadsheetid );
				if ( isset( $_POST['header_format'] ) ) {
					$wpssw_header_format = sanitize_text_field( wp_unslash( $_POST['header_format'] ) );
					parent::wpssw_update_option( 'wpssw_header_format', $wpssw_header_format );
				}
				if ( isset( $_POST['repeat_checkbox'] ) ) {
					parent::wpssw_update_option( 'wpssw_repeat_checkbox', 'yes' );
				} else {
					parent::wpssw_update_option( 'wpssw_repeat_checkbox', 'no' );
				}
				if ( isset( $_POST['color_code'] ) && isset( $_POST['oddcolor'] ) && isset( $_POST['evencolor'] ) ) {
					$oddcolor  = sanitize_text_field( wp_unslash( $_POST['oddcolor'] ) );
					$evencolor = sanitize_text_field( wp_unslash( $_POST['evencolor'] ) );
					parent::wpssw_update_option( 'wpssw_color_code', 1 );
				} else {
					parent::wpssw_update_option( 'wpssw_color_code', 0 );
					$oddcolor  = '#ffffff';
					$evencolor = '#ffffff';
				}
				if ( isset( $_POST['inputoption'] ) ) {
					$wpssw_inputoption = sanitize_text_field( wp_unslash( $_POST['inputoption'] ) );
					parent::wpssw_update_option( 'wpssw_inputoption', $wpssw_inputoption );
				}
				if ( isset( $_POST['wpssw_price_format'] ) ) {
					$wpssw_price_format = sanitize_text_field( wp_unslash( $_POST['wpssw_price_format'] ) );
					parent::wpssw_update_option( 'wpssw_price_format', $wpssw_price_format );
				}
				if ( isset( $_POST['import_order_checkbox'] ) ) {
					parent::wpssw_update_option( 'wpssw_order_import', 1 );
				} else {
					parent::wpssw_update_option( 'wpssw_order_import', '' );
				}
				if ( isset( $_POST['update_order_checkbox'] ) ) {
					parent::wpssw_update_option( 'wpssw_order_update', 1 );
				} else {
					parent::wpssw_update_option( 'wpssw_order_update', '' );
				}
				if ( isset( $_POST['delete_order_checkbox'] ) ) {
					parent::wpssw_update_option( 'wpssw_order_delete', 1 );
				} else {
					parent::wpssw_update_option( 'wpssw_order_delete', '' );
				}

				/*
				 *Update Static Headers
				 */
				if ( isset( $_POST['header_fields_static'] ) && is_array( $_POST['header_fields_static'] ) ) {
					$wpssw_static_header = array_map( 'sanitize_text_field', wp_unslash( $_POST['header_fields_static'] ) );
					parent::wpssw_update_option( 'wpssw_static_header', $wpssw_static_header );
				}
				if ( isset( $_POST['wpssw_static_header_values'] ) && is_array( $_POST['wpssw_static_header_values'] ) ) {
					$wpssw_static_header_values = array_map( 'sanitize_text_field', wp_unslash( $_POST['wpssw_static_header_values'] ) );
					parent::wpssw_update_option( 'wpssw_static_header_values', $wpssw_static_header_values );
				}
				if ( isset( $_POST['order_ascdesc'] ) ) {
					$order_ascdesc = sanitize_text_field( wp_unslash( $_POST['order_ascdesc'] ) );
					parent::wpssw_update_option( 'wpssw_order_ascdesc', $order_ascdesc );
				}
				parent::wpssw_update_option( 'wpssw_oddcolor', $oddcolor );
				parent::wpssw_update_option( 'wpssw_evencolor', $evencolor );
			}
			/* Chart Display Function Start */
			$graphsheets_list = array();
			if ( isset( $_POST['graphsheets_list'] ) ) {
				$graphsheets_list = array_map( 'sanitize_text_field', wp_unslash( $_POST['graphsheets_list'] ) );
			}
			parent::wpssw_update_option( 'wpssw_graphsheets_list', $graphsheets_list );
			if ( isset( $_POST['sales_orders_graph_type'] ) ) {
				parent::wpssw_update_option( 'wpssw_sales_orders_graph_type', sanitize_text_field( wp_unslash( $_POST['sales_orders_graph_type'] ) ) );
			}
			if ( isset( $_POST['total_orders_graph_type'] ) ) {
				parent::wpssw_update_option( 'wpssw_total_orders_graph_type', sanitize_text_field( wp_unslash( $_POST['total_orders_graph_type'] ) ) );
			}
			if ( isset( $_POST['products_sold_graph_type'] ) ) {
				parent::wpssw_update_option( 'wpssw_products_sold_graph_type', sanitize_text_field( wp_unslash( $_POST['products_sold_graph_type'] ) ) );
			}
			if ( isset( $_POST['total_customers_graph_type'] ) ) {
				parent::wpssw_update_option( 'wpssw_total_customers_graph_type', sanitize_text_field( wp_unslash( $_POST['total_customers_graph_type'] ) ) );
			}
			if ( isset( $_POST['total_used_coupons_graph_type'] ) ) {
				parent::wpssw_update_option( 'wpssw_total_used_coupons_graph_type', sanitize_text_field( wp_unslash( $_POST['total_used_coupons_graph_type'] ) ) );
			}
			/* Chart Display Function End */

			/* Schedule Auto Sync */
			if ( isset( $_POST['scheduling_enable'] ) ) {
				self::wpssw_update_schedule_data( $_POST );
			}
		}
		/**
		 * Update sync schedule data.
		 *
		 * @param array $data .
		 */
		public static function wpssw_update_schedule_data( $data = array() ) {
			if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$wpssw_schedule_enable = isset( $_POST['scheduling_enable'] ) ? sanitize_text_field( wp_unslash( $_POST['scheduling_enable'] ) ) : '';
			parent::wpssw_update_option( 'wpssw_scheduling_enable', $wpssw_schedule_enable );
			if ( 1 === (int) $wpssw_schedule_enable ) {

				$wpssw_scheduling_run_on = parent::wpssw_option( 'wpssw_scheduling_run_on' );
				$scheduling_run_on       = isset( $_POST['scheduling_run_on'] ) ? sanitize_text_field( wp_unslash( $_POST['scheduling_run_on'] ) ) : '';
				if ( (string) $wpssw_scheduling_run_on !== (string) $scheduling_run_on ) {
					self::wpssw_remove_cron_job();
				}

				parent::wpssw_update_option( 'wpssw_scheduling_run_on', $scheduling_run_on );
				if ( 'recurrence' === (string) $scheduling_run_on ) {
					$wpssw_interval            = array(
						'once_daily'   => 'daily',
						'twice_daily'  => 'twicedaily',
						'fifteen_days' => 'fifteen_days',
						'monthly'      => 'monthly',
					);
					$schedule_recurrence       = isset( $_POST['schedule_recurrence'] ) ? sanitize_text_field( wp_unslash( $_POST['schedule_recurrence'] ) ) : '';
					$wpssw_schedule_recurrence = parent::wpssw_option( 'wpssw_schedule_recurrence' );
					parent::wpssw_update_option( 'wpssw_schedule_recurrence', $schedule_recurrence );
					if ( isset( $wpssw_interval[ $schedule_recurrence ] ) ) {
						if ( (string) $schedule_recurrence !== (string) $wpssw_schedule_recurrence ) {
							self::wpssw_remove_cron_job();
						}
						self::wpssw_set_cron_job( time(), $wpssw_interval[ $schedule_recurrence ] );
					}
				}
				if ( 'weekly' === (string) $scheduling_run_on ) {
					$scheduling_weekly_days       = isset( $_POST['scheduling_weekly_days'] ) ? sanitize_text_field( wp_unslash( $_POST['scheduling_weekly_days'] ) ) : '';
					$wpssw_scheduling_weekly_days = parent::wpssw_option( 'wpssw_scheduling_weekly_days' );
					if ( ! empty( $scheduling_weekly_days ) ) {
						self::wpssw_remove_cron_job();
						$wpssw_weekdays = explode( ',', $scheduling_weekly_days );
						if ( is_array( $wpssw_weekdays ) && ! empty( $wpssw_weekdays ) ) {
							parent::wpssw_update_option( 'wpssw_scheduling_weekly_days', $scheduling_weekly_days );
							foreach ( $wpssw_weekdays as $weekday ) {
								$startcron = strtotime( 'next ' . $weekday );
								self::wpssw_set_cron_job( $startcron, 'weekly' );
							}
						}
					}
				}
				if ( 'onetime' === (string) $scheduling_run_on ) {
					$scheduling_date       = isset( $_POST['scheduling_date'] ) ? sanitize_text_field( wp_unslash( $_POST['scheduling_date'] ) ) : '';
					$scheduling_time       = isset( $_POST['scheduling_time'] ) ? sanitize_text_field( wp_unslash( $_POST['scheduling_time'] ) ) : '';
					$wpssw_scheduling_date = parent::wpssw_option( 'wpssw_scheduling_date' );
					$wpssw_scheduling_time = parent::wpssw_option( 'wpssw_scheduling_time' );
					parent::wpssw_update_option( 'wpssw_scheduling_date', $scheduling_date );
					parent::wpssw_update_option( 'wpssw_scheduling_time', $scheduling_time );
					if ( ! empty( $scheduling_date ) && ! empty( $scheduling_time ) ) {
						$starttime = strtotime( $scheduling_date . ' ' . $scheduling_time );
						if ( ! empty( $wpssw_scheduling_date ) && ! empty( $wpssw_scheduling_time ) ) {
							$previousstarttime = strtotime( $wpssw_scheduling_date . ' ' . $wpssw_scheduling_time );
							if ( (int) $starttime !== (int) $previousstarttime ) {
								self::wpssw_remove_cron_job();
							}
						}
						self::wpssw_set_cron_job( $starttime, '', true );
					}
				}
			} else {
				self::wpssw_remove_cron_job();
			}
		}
		/**
		 * Set cron job.
		 *
		 * @param int  $startcron .
		 * @param int  $interval .
		 * @param bool $once .
		 */
		public static function wpssw_set_cron_job( $startcron, $interval, $once = false ) {
			if ( $once ) {
				wp_schedule_single_event( $startcron, 'wpssw_cron_run' );
			} else {
				wp_schedule_event( $startcron, $interval, 'wpssw_cron_run' );
			}
		}
		/**
		 * Remove cron job.
		 */
		public static function wpssw_remove_cron_job() {
			wp_clear_scheduled_hook( 'wpssw_cron_run' );
		}
		/**
		 * Add cron interval.
		 *
		 * @param array $schedules .
		 */
		public static function wpssw_add_cron_interval( $schedules ) {
			$wpssw_interval            = 86400 * 14;
			$schedules['fifteen_days'] = array(
				'interval' => $wpssw_interval,
				'display'  => esc_html__( 'Every Fifteen Days', 'wpssw' ),
			);
			return $schedules;
		}
		/**
		 * Run cron job.
		 */
		public static function wpssw_cron_run() {
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}

			$wpssw_firstcolumns = self::wpssw_get_orders_count( true );
			$wpssw_all_status   = wc_get_order_statuses();
			$wpssw_all_status   = array_map( 'strtolower', $wpssw_all_status );
			$wpssw_allorders    = parent::wpssw_array_flatten( $wpssw_firstcolumns );

			$wpssw_smallestid = min( $wpssw_allorders );
			if ( ! is_numeric( $wpssw_smallestid ) ) {
				$wpssw_smallestid = 0;
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_inputoption   = parent::wpssw_option( 'wpssw_inputoption' );

			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$order_ascdesc = parent::wpssw_option( 'wpssw_order_ascdesc' );

			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );

			foreach ( $wpssw_firstcolumns as $wpssw_sheetname => $wpssw_data ) {
				$wpssw_sheet_slug = trim( strtolower( str_replace( 'Orders', '', $wpssw_sheetname ) ) );
				if ( 'pending' === $wpssw_sheet_slug ) {
					$wpssw_sheet_slug = 'pending payment';
				}
				if ( in_array( $wpssw_sheet_slug, $wpssw_all_status, true ) ) {
					$wpssw_sheet_slug = array_search( $wpssw_sheet_slug, $wpssw_all_status, true );
				}
				if ( 'All Orders' === (string) $wpssw_sheetname ) {
					$wpssw_query_args = array(
						'post_type'      => 'shop_order',
						'posts_per_page' => -1,
						'order'          => 'ASC',
						'post_status'    => array_keys( wc_get_order_statuses() ),
					);
				} else {
					$wpssw_query_args = array(
						'post_type'      => 'shop_order',
						'post_status'    => $wpssw_sheet_slug,
						'posts_per_page' => -1,
						'order'          => 'ASC',
					);
				}
				if ( 'descorder' === (string) $order_ascdesc ) {
					$wpssw_query_args['order'] = 'DESC';
				}
				$wpsswcustom_query = new WP_Query( $wpssw_query_args );
				$wpssw_all_orders  = $wpsswcustom_query->posts;
				if ( empty( $wpssw_all_orders ) ) {
					continue;
				}

				$wpssw_values_array = array();
				$neworder           = 0;
				foreach ( $wpssw_all_orders as $wpssw_order ) {
					if ( in_array( (int) $wpssw_order->ID, parent::wpssw_convert_int( $wpssw_data ), true ) ) {
						continue;
					}
					if ( self::wpssw_check_product_category( $wpssw_order->ID ) ) {
						continue;
					}
					if ( $wpssw_smallestid > $wpssw_order->ID ) {
						continue;
					}
					$wpssw_order = wc_get_order( $wpssw_order->ID );
					set_time_limit( 999 );
					$wpssw_order_data   = $wpssw_order->get_data();
					$wpssw_status       = $wpssw_order_data['status'];
					$wpssw_value        = self::wpssw_make_value_array( 'insert', $wpssw_order->get_id() );
					$wpssw_values_array = array_merge( $wpssw_values_array, $wpssw_value );
				}
				$wpssw_sheetid = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
				$rangetofind   = $wpssw_sheetname . '!A:A';
				if ( ! empty( $wpssw_values_array ) ) {
					try {
						if ( 'descorder' === (string) $order_ascdesc ) {
							if ( count( $wpssw_data ) > 0 ) {

								$param                          = array();
								$startindex                     = 1;
								$endindex                       = count( $wpssw_values_array ) + 1;
								$param                          = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
								$wpssw_batchupdaterequest       = self::$instance_api->insertdimensionobject( $param );
								$requestobject                  = array();
								$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
								$requestobject['requestbody']   = $wpssw_batchupdaterequest;
								$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
								$rangetofind                    = $wpssw_sheetname . '!A2';
							}
						}
						$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
						$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
						$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetofind, $wpssw_requestbody, $wpssw_params );
						$wpssw_response    = self::$instance_api->appendentry( $param );
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
			}
		}

		/**
		 * Get cart item from session.
		 *
		 * @param array $wpssw_cart_item Cart item data.
		 * @param array $wpssw_values    Cart item values.
		 * @return array
		 */
		public static function wpssw_get_cart_item_from_session( $wpssw_cart_item, $wpssw_values ) {
			if ( ! empty( $wpssw_values['wpssw_headers'] ) ) {
				$wpssw_cart_item['wpssw_headers'] = $wpssw_values['wpssw_headers'];
			}
			return $wpssw_cart_item;
		}
		/**
		 * Add item data to the cart.
		 *
		 * @param array $wpssw_cart_item_data Cart item data.
		 * @param int   $wpssw_product_id .
		 */
		public static function wpssw_add_cart_item_data( $wpssw_cart_item_data, $wpssw_product_id ) {
			if ( ! class_exists( 'WC_Product_Addons' ) ) {
				return $wpssw_cart_item_data;
			}
			// @codingStandardsIgnoreStart.
			if ( isset( $_POST ) && ! empty( $wpssw_product_id ) ) {
				$wpssw_post_data = $_POST;
				// @codingStandardsIgnoreEnd.
			} else {
				return;
			}
			$wpssw_metavalues     = parent::wpssw_get_all_meta_values();
			$wpssw_product_addons = WC_Product_Addons_Helper::get_product_addons( $wpssw_product_id );
			if ( empty( $wpssw_cart_item_data['wpssw_headers'] ) ) {
				$wpssw_cart_item_data['wpssw_headers'] = array();
			}
			if ( is_array( $wpssw_product_addons ) && ! empty( $wpssw_product_addons ) ) {
				foreach ( $wpssw_product_addons as $wpssw_addon ) {
					// If type is heading, skip.
					if ( 'heading' === (string) $wpssw_addon['type'] ) {
						continue;
					}
					$wpssw_value = isset( $wpssw_post_data[ 'addon-' . $wpssw_addon['field_name'] ] ) ? $wpssw_post_data[ 'addon-' . $wpssw_addon['field_name'] ] : '';
					$wpssw_key   = strtolower( str_replace( ' ', '_', $wpssw_addon['name'] ) );
					if ( is_array( $wpssw_value ) ) {
						$wpssw_value = array_map( 'stripslashes', $wpssw_value );
					} else {
						$wpssw_value = stripslashes( $wpssw_value );
					}
					$wpssw_data[ $wpssw_addon['field_name'] ] = $wpssw_addon['name'];
					$wpssw_cart_item_data['wpssw_headers']    = array_merge( $wpssw_cart_item_data['wpssw_headers'], apply_filters( 'woocommerce_product_addon_cart_item_data', $wpssw_data, $wpssw_addon, $wpssw_product_id, $wpssw_post_data ) );
				}
			}
			return $wpssw_cart_item_data;
		}
		/**
		 * Include add-ons line item meta.
		 *
		 * @param  WC_Order_Item_Product $wpssw_item          Order item data.
		 * @param  string                $wpssw_cart_item_key Cart item key.
		 * @param  array                 $wpssw_values        Order item values.
		 */
		public static function wpssw_order_line_item( $wpssw_item, $wpssw_cart_item_key, $wpssw_values ) {
			if ( ! class_exists( 'WC_Product_Addons' ) ) {
				return;
			}
			if ( ! empty( $wpssw_values['addons'] ) ) {
				foreach ( $wpssw_values['addons'] as $wpssw_addon ) {
					$wpssw_key           = $wpssw_addon['name'];
					$wpssw_price_type    = $wpssw_addon['price_type'];
					$wpssw_product       = $wpssw_item->get_product();
					$wpssw_product_price = $wpssw_product->get_price();
					$wpssw_price         = html_entity_decode( wp_strip_all_tags( wc_price( WC_Product_Addons_Helper::get_product_addon_price_for_display( $wpssw_addon['price'], $wpssw_values['data'], true ) ) ) );

					/*
					* For percentage based price type we want
					* to show the calculated price instead of
					* the price of the add-on itself and in this
					* case its not a price but a percentage.
					* Also if the product price is zero, then there
					* is nothing to calculate for percentage so
					* don't show any price.
					*/
					if ( $wpssw_addon['price'] && 'percentage_based' === (string) $wpssw_price_type && 0 !== (int) $wpssw_product_price ) {
						$wpssw_price = html_entity_decode( wp_strip_all_tags( wc_price( WC_Product_Addons_Helper::get_product_addon_price_for_display( ( $wpssw_product_price * ( $wpssw_addon['price'] / 100 ) ) ) ) ) );
					}
					if ( 'custom_price' === (string) $wpssw_addon['field_type'] ) {
						$wpssw_addon['value'] = $wpssw_addon['price'];
					}
					$wpssw_key = strtolower( str_replace( ' ', '_', $wpssw_addon['name'] ) );
					if ( is_array( $wpssw_value ) ) {
						$wpssw_value = array_map( 'stripslashes', $wpssw_value );
					} else {
						$wpssw_value = stripslashes( $wpssw_value );
					}
					$wpssw_item->add_meta_data( $wpssw_addon['field_name'], $wpssw_addon['value'] );
				}
			}
			if ( ! empty( $wpssw_values['wpssw_headers'] ) ) {
				$wpssw_item->add_meta_data( 'wpssw_headers', $wpssw_values['wpssw_headers'] );
			}
		}
		/**
		 * Output sheet header settings.
		 */
		public static function wpssw_woocommerce_admin_field_set_headers() {
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_order_compatibility_files();
			$wpssw_header_type         = self::wpssw_is_productwise();
			$wpssw_headers             = array();
			$wpssw_shipping_fields     = array();
			$wpssw_billing_fields      = array();
			$wpssw_additional_fields   = array();
			$wpssw_headers             = apply_filters( 'wpsyncsheets_order_headers', array() );
			$wpssw_productwise_headers = self::get_headers_by_key( $wpssw_headers, 'ProductWise' );
			$wpssw_orderwise_headers   = self::get_headers_by_key( $wpssw_headers, 'OrderWise' );
			$wpssw_headers             = array_values( parent::wpssw_array_flatten( $wpssw_headers ) );
			$wpssw_selections          = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
			if ( ! $wpssw_selections ) {
				$wpssw_selections = array();
			}
			$wpssw_selections_custom = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list_custom' ) );
			if ( ! $wpssw_selections_custom ) {
				$wpssw_selections_custom = array();
			}
			$wpssw_prdwise = array_merge( $wpssw_productwise_headers, $wpssw_headers );
			$wpssw_ordwise = array_merge( $wpssw_orderwise_headers, $wpssw_headers );
			$wpssw_headers = stripslashes_deep( $wpssw_headers );
			global $wpssw_global_headers;
			$wpssw_global_headers       = $wpssw_headers;
			$wpssw_product_selections   = stripslashes_deep( parent::wpssw_option( 'wpssw_product_sheet_headers_list' ) );
			$wpssw_static_header        = stripslashes_deep( parent::wpssw_option( 'wpssw_static_header' ) );
			$wpssw_static_header_values = stripslashes_deep( parent::wpssw_option( 'wpssw_static_header_values' ) );
			$wpssw_custom_value         = array();
			if ( ! $wpssw_product_selections ) {
				$wpssw_product_selections = array();
			}
			if ( ! $wpssw_static_header ) {
				$wpssw_static_header = array();
			}
			if ( ! $wpssw_static_header_values ) {
				$wpssw_static_header_values = array();
			}
			if ( ! empty( $wpssw_static_header_values ) ) {
				foreach ( $wpssw_static_header_values as $wpssw_static_header_value ) {
					if ( strpos( $wpssw_static_header_value, ',(static_header),' ) ) {
						$wpssw_static_header_value = str_replace( ',(static_header),', ',', $wpssw_static_header_value );
						$wpssw_custom_value[]      = explode( ',', $wpssw_static_header_value );
					}
				}
			}
			?>
			<tr>
				<td colspan="2" class="td-wpssw-headers">
					<div class='wpssw_headers'>
						<label for="sheet_headers"><?php echo esc_html__( 'Sheet Headers', 'wpssw' ); ?></label>
						<div id="wpssw-headers-notice">
							<i><strong><?php echo esc_html__( 'Note', 'wpssw' ); ?>: </strong><?php echo esc_html__( 'All the disabled sheet headers will be deleted from the current spreadsheet automatically. You can enable again, clear spreadsheet and click with below "Click to Sync" button to make up to date data in spreadsheet.', 'wpssw' ); ?></i>
						</div>
						<ul id="sortable">
							<?php
							$wpssw_operation = array( 'Update', 'Delete' );
							if ( ! empty( $wpssw_selections ) ) {
								foreach ( $wpssw_selections as $wpssw_key => $wpssw_val ) {
									if ( in_array( $wpssw_val, $wpssw_product_selections, true ) ) {
										continue;
									}
									$wpssw_static_field        = '';
									$wpssw_static_field_values = '';
									$wpssw_is_check            = 'checked';
									$wpssw_labelid             = strtolower( str_replace( ' ', '_', $wpssw_val ) );

									$wpssw_display   = true;
									$wpssw_classname = '';
									if ( in_array( $wpssw_val, $wpssw_operation, true ) ) {
										$wpssw_display   = false;
										$wpssw_labelid   = '';
										$wpssw_classname = strtolower( $wpssw_val ) . 'order';
									}
									?>
									<li class="ui-state-default <?php echo esc_html( $wpssw_classname ); ?>"><label for="<?php echo esc_attr( $wpssw_labelid ); ?>"><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield"><?php echo isset( $wpssw_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?></span>
									<?php if ( $wpssw_display ) { ?>
									<span class="ui-icon ui-icon-pencil"></span>
									<?php } ?>
									<input type="checkbox" name="header_fields_custom[]" value="<?php echo isset( $wpssw_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>" class="headers_chk1" <?php echo esc_html( $wpssw_is_check ); ?> hidden="true">
										<?php
										if ( in_array( $wpssw_val, $wpssw_static_header, true ) ) {
											?>
										<input type="checkbox" name="header_fields_static[]" value="<?php echo esc_attr( $wpssw_val ); ?>" hidden="true" checked>
											<?php
										}
										if ( in_array( $wpssw_val, array_column( $wpssw_custom_value, 0 ), true ) ) {
											$search_key          = array_search( $wpssw_val, array_column( $wpssw_custom_value, 0 ), true );
											$wpssw_search_array  = $wpssw_custom_value[ $search_key ];
											$wpssw_search_keyval = implode( ',(static_header),', $wpssw_search_array );
											?>
										<input type="checkbox" name="wpssw_static_header_values[]" value="<?php echo esc_attr( $wpssw_search_keyval ); ?>" hidden="true" checked>
											<?php
										}
										?>
										<input type="checkbox" name="header_fields[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="<?php echo esc_attr( $wpssw_labelid ); ?>" class="headers_chk" <?php echo esc_html( $wpssw_is_check ); ?>>
										<?php if ( $wpssw_display ) { ?>
										<span class="checkbox-switch-new"></span>
										<?php } ?>
										</label>
									</li>
									<?php
								}
							}
							if ( ! empty( $wpssw_headers ) ) {
								foreach ( $wpssw_headers as $wpssw_key => $wpssw_val ) {
									$wpssw_is_check = '';
									if ( in_array( $wpssw_val, $wpssw_selections, true ) ) {
										continue;
									}
									$wpssw_labelid = strtolower( str_replace( ' ', '_', $wpssw_val ) );
									?>
									<li class="ui-state-default"><label for="<?php echo esc_attr( $wpssw_labelid ); ?>"><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield"><?php echo esc_html( $wpssw_val ); ?></span><span class="ui-icon ui-icon-pencil"></span><input type="checkbox" name="header_fields_custom[]" value="<?php echo esc_attr( $wpssw_val ); ?>" class="headers_chk1" <?php echo esc_html( $wpssw_is_check ); ?> hidden="true"><input type="checkbox" name="header_fields[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="<?php echo esc_attr( $wpssw_labelid ); ?>" class="headers_chk" <?php echo esc_html( $wpssw_is_check ); ?>><span class="checkbox-switch-new"></span></label>
									</li>
									<?php
								}
							}
							?>
						</ul>
						<input type="hidden" id="prdwise" value="<?php echo esc_attr( implode( ',', $wpssw_prdwise ) ); ?>">
						<input type="hidden" id="ordwise" value="<?php echo esc_attr( implode( ',', $wpssw_ordwise ) ); ?>">
						<button type="button" class="wpssw-button wpssw-button-secondary" id="selectall"><?php esc_html_e( 'Select all', 'wpssw' ); ?></button>                
						<button type="button" class="wpssw-button wpssw-button-secondary" id="selectnone"><?php esc_html_e( 'Select none', 'wpssw' ); ?></button>
					</div>
				</td>
			</tr>
			<?php
		}
		/**
		 * Output product names as headers.
		 *
		 * @param int $flag .
		 */
		public static function wpssw_woocommerce_admin_field_product_headers( $flag = 0 ) {
			$wpssw_selections        = stripslashes_deep( parent::wpssw_option( 'wpssw_product_sheet_headers_list' ) );
			$wpssw_selections_custom = stripslashes_deep( parent::wpssw_option( 'wpssw_product_sheet_headers_list_custom' ) );
			if ( ! $wpssw_selections ) {
				$wpssw_selections = array();
			}
			if ( ! $wpssw_selections_custom ) {
				$wpssw_selections_custom = array();
			}
			$prdassheetheaders = parent::wpssw_option( 'wpssw_prdassheetheaders' );
			if ( ! empty( $prdassheetheaders ) || $flag ) {
				$args               = array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
				);
				$wpssw_product_list = array();
				$loop               = new WP_Query( $args );
				while ( $loop->have_posts() ) :
					$loop->the_post();
					global $product;
					$wpssw_product_list[] = html_entity_decode( get_the_title(), ENT_QUOTES );
				endwhile;
				wp_reset_postdata();
				?>
				<tr class="td-prd-wpssw-headers">
					<td colspan="2" class="td-wpssw-headers">
						<div class='wpssw_headers'>
							<label for="sheet_headers"></label>
							<div id="wpssw-headers-notice">
								<i><?php echo esc_html__( 'Below all the product names will automatically create columns in spreadsheet with value as product quantity and Append after dropdown will add inbetween all the product names as per your dropdown selection in spreadsheet.', 'wpssw' ); ?></i>
							</div>
							<ul id="product-sortable">
								<?php
								if ( ! empty( $wpssw_selections ) ) {
									foreach ( $wpssw_selections as $wpssw_key => $wpssw_val ) {
										$wpssw_is_check = 'checked';
										$wpssw_labelid  = strtolower( str_replace( ' ', '_', $wpssw_val ) );
										?>
										<li class="ui-state-default"><label for="<?php echo esc_attr( $wpssw_labelid ); ?>"><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield"><?php echo isset( $wpssw_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?></span><span class="ui-icon ui-icon-pencil"></span><input type="checkbox" name="product_header_fields_custom[]" value="<?php echo isset( $wpssw_selections_custom[ $wpssw_key ] ) ? esc_attr( $wpssw_selections_custom[ $wpssw_key ] ) : esc_attr( $wpssw_val ); ?>" class="prdheaders_chk1" <?php echo esc_html( $wpssw_is_check ); ?> hidden="true"><input type="checkbox" name="product_header_fields[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="<?php echo esc_attr( $wpssw_labelid ); ?>" class="prdheaders_chk" <?php echo esc_html( $wpssw_is_check ); ?> ><span class="checkbox-switch-new"></span></label></li>
										<?php
									}
								}
								if ( ! empty( $wpssw_product_list ) ) {
									foreach ( $wpssw_product_list as $wpssw_key => $wpssw_val ) {
										$wpssw_is_check = '';
										if ( in_array( $wpssw_val, $wpssw_selections, true ) ) {
											continue;
										}
										$wpssw_labelid = strtolower( str_replace( ' ', '_', $wpssw_val ) );
										?>
										<li class="ui-state-default"><label for="<?php echo esc_html( $wpssw_labelid ); ?>"><span class="ui-icon ui-icon-caret-2-n-s"></span><span class="wootextfield"><?php echo esc_attr( $wpssw_val ); ?></span><span class="ui-icon ui-icon-pencil"></span><input type="checkbox" name="product_header_fields_custom[]" value="<?php echo esc_attr( $wpssw_val ); ?>" class="prdheaders_chk1" <?php echo esc_html( $wpssw_is_check ); ?> hidden="true"><input type="checkbox" name="product_header_fields[]" value="<?php echo esc_attr( $wpssw_val ); ?>" id="<?php echo esc_attr( $wpssw_labelid ); ?>" class="prdheaders_chk" <?php echo esc_html( $wpssw_is_check ); ?>><span class="checkbox-switch-new"></span></label></li>
										<?php
									}
								}
								?>
							</ul>
							<button type="button" class="wpssw-button wpssw-button-secondary" id="prdselectall" ><?php esc_html_e( 'Select all', 'wpssw' ); ?></button>                
							<button type="button" class="wpssw-button wpssw-button-secondary" id="prdselectnone" ><?php esc_html_e( 'Select none', 'wpssw' ); ?></button>
						</div>
					</td>
				</tr>
				<?php } else { ?>
			<tr class="td-prd-wpssw-headers">
			</tr>
					<?php
				}
		}
		/**
		 * Output Product name as sheets headers.
		 */
		public static function wpssw_woocommerce_admin_field_product_as_sheet_header() {
			$prdassheetheaders = parent::wpssw_option( 'wpssw_prdassheetheaders' );
			if ( ! $prdassheetheaders ) {
				$prdassheetheaders = '';
			}
			?>
			<tr valign="top" id="custom-disable-id">
				<th scope="row" class="titledesc">
					<label for="prdassheetheaders"><?php echo esc_html__( 'Product Name as sheets headers', 'wpssw' ); ?></label>
				</th>
				<td class="forminp custom-onoff">              
					<label for="prdassheetheaders">
						<input name="prdassheetheaders" id="prdassheetheaders" type="checkbox" class="" value="yes" 
				<?php
				if ( 'yes' === (string) $prdassheetheaders ) {
					echo 'checked';}
				?>
						><span class="checkbox-switch"></span><img id="loaderprdheader" src="<?php dirname( __FILE__ ); ?>images/spinner.gif">					
					</label>
				</td>
			</tr>
					<?php
		}
		/**
		 *
		 * Output append after dropdown setting for product names as sheet headers.
		 */
		public static function wpssw_woocommerce_admin_field_product_headers_append_after() {
			global $wpssw_global_headers;
			$wpssw_append_after_array = parent::wpssw_option( 'wpssw_append_after_array' );
			$wpssw_append_after       = parent::wpssw_option( 'wpssw_append_after' );
			$wpssw_global_headers     = $wpssw_append_after_array ? $wpssw_append_after_array : $wpssw_global_headers;
			$selected                 = '';
			?>
			<tr valign="top" class="td-prd-append-after">
				<th scope="row" class="titledesc">
					<label for="wpssw_append_after"><?php echo esc_html__( 'Append After', 'wpssw' ); ?> <span class="woocommerce-help-tip" data-tip="Product Headers Append After."></span></label>
				</th>
				<td class="forminp forminp-select">
					<select name="wpssw_append_after" id="wpssw_append_after" class="">
				<?php
				foreach ( $wpssw_global_headers as $key => $headers ) {
					if ( str_replace( ' ', '-', strtolower( $wpssw_append_after ) ) === (string) $key || $headers === $wpssw_append_after ) {
						$selected = 'selected="selected"';
					}
					$headerkey = '';
					?>
							<option value="<?php echo ! is_numeric( $key ) ? esc_attr( $key ) : esc_attr( $headers ); ?>" <?php echo esc_html( $selected ); ?>><?php echo esc_html( $headers ); ?></option>    
							<?php
							$selected = '';
				}
				?>
					</select> 							
				</td>
			</tr>
			<?php
		}
		/**
		 * Output radio button field.
		 */
		public static function wpssw_woocommerce_admin_field_manage_row_field() {
			$wpssw_header_format = parent::wpssw_option( 'wpssw_header_format' );
			if ( ! empty( $wpssw_header_format ) ) {
				if ( 'orderwise' === (string) $wpssw_header_format ) {
					$wpssw_orderwise   = "checked='checked' disabled='disabled'";
					$wpssw_productwise = "disabled='disabled'";
				} else {
					$wpssw_productwise = "checked='checked' disabled='disabled'";
					$wpssw_orderwise   = "disabled='disabled'";
				}
				$wpssw_disableclass = 'disabled';
			} else {
				$wpssw_orderwise    = "checked='checked'";
				$wpssw_productwise  = '';
				$wpssw_disableclass = '';
			}
			?>
			<tr valign="top" id="header_format">
				<th scope="row" class="titledesc">
					<label for="sheet_headers"><?php echo esc_html__( 'Manage Row Data', 'wpssw' ); ?></label>
				</th>
				<td class="forminp radio-box-td ">
					<input name="header_format" id="orderwise" class="manage-row <?php echo esc_attr( $wpssw_disableclass ); ?>" value="orderwise" type="radio" <?php echo esc_html( $wpssw_orderwise ); ?>><label for="orderwise"><?php echo esc_html__( 'Order Wise', 'wpssw' ); ?></label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
					<input name="header_format" id="productwise" class="manage-row <?php echo esc_attr( $wpssw_disableclass ); ?>" value="productwise" type="radio" <?php echo esc_html( $wpssw_productwise ); ?>><label for="productwise"><?php echo esc_html__( 'Product Wise', 'wpssw' ); ?></label>
				</td>
			</tr>
					<?php
		}
		/**
		 * Output Syncronization Button
		 */
		public static function wpssw_woocommerce_admin_field_sync_button() {
			$wpssw_selections                   = parent::wpssw_option( 'wpssw_sheet_headers_list' );
			$wpssw_scheduling_enable            = parent::wpssw_option( 'wpssw_scheduling_enable' );
			$wpssw_scheduling_run_on            = parent::wpssw_option( 'wpssw_scheduling_run_on' );
			$wpssw_schedule_recurrence          = parent::wpssw_option( 'wpssw_schedule_recurrence' );
			$wpssw_scheduling_weekly_days       = parent::wpssw_option( 'wpssw_scheduling_weekly_days' );
			$wpssw_scheduling_date              = parent::wpssw_option( 'wpssw_scheduling_date' );
			$wpssw_scheduling_time              = parent::wpssw_option( 'wpssw_scheduling_time' );
			$wpssw_scheduling_weekly_days_array = array();
			if ( ! empty( $wpssw_scheduling_weekly_days ) ) {
				$wpssw_scheduling_weekly_days_array = explode( ',', $wpssw_scheduling_weekly_days );
			}
			$wpssw_active     = '';
			$wpssw_deactive   = '';
			$wpssw_recurrence = '';
			$wpssw_weekly     = '';
			$wpssw_onetime    = '';
			if ( 1 === (int) $wpssw_scheduling_enable ) {
				$wpssw_active = 'checked=checked';
			} else {
				$wpssw_deactive = 'checked=checked';
			}

			if ( 'recurrence' === (string) $wpssw_scheduling_run_on ) {
				$wpssw_recurrence = 'checked=checked';
			}
			if ( 'onetime' === (string) $wpssw_scheduling_run_on ) {
				$wpssw_onetime = 'checked=checked';
			}
			if ( 'weekly' === (string) $wpssw_scheduling_run_on ) {
				$wpssw_weekly = 'checked=checked';
			}
			if ( empty( $wpssw_recurrence ) && empty( $wpssw_onetime ) && empty( $wpssw_weekly ) ) {
				$wpssw_recurrence = 'checked=checked';
			}
			$wpssw_interval = array(
				'once_daily'   => 'Once Daily',
				'twice_daily'  => 'Twice Daily',
				'fifteen_days' => 'Every Fifteen Days',
				'monthly'      => 'Monthly',
			);
			$wpssw_weekday  = array(
				'monday'    => 'Mon',
				'tuesday'   => 'Tue',
				'wednesday' => 'Wed',
				'thursday'  => 'Thu',
				'friday'    => 'Fri',
				'saturday'  => 'Sat',
				'sunday'    => 'Sun',
			);
			if ( ! empty( $wpssw_selections ) ) {
				?>
				<tr valign="top" class="synctr">
					<th scope="row" class="titledesc">
						<label><?php echo esc_html__( 'Sync Range ( All / Custom )', 'wpssw' ); ?></label>
					</th>
					<td>
						<label for="sync_all">
							<input name="sync_all_checkbox" id="sync_all" type="checkbox" class="" value="1" checked><span class="checkbox-switch"></span>
						</label>
						<br /><i><strong><?php echo esc_html__( 'Note:', 'wpssw' ); ?></strong><br /><strong><?php echo esc_html__( 'Enabled', 'wpssw' ); ?></strong> - <?php echo esc_html__( 'It will sync all the orders.', 'wpssw' ); ?><br /><strong><?php echo esc_html__( 'Disabled', 'wpssw' ); ?></strong> - <?php echo esc_html__( 'It has custom date range option.', 'wpssw' ); ?></i>
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="forminp forminp-select sync_all_fromtodate"> 
						<label for="sync_all_fromdate" >
					<?php echo esc_html__( 'From :', 'wpssw' ); ?>   <input name="sync_all_fromdate" id="sync_all_fromdate" class="sync_all_fromdate" type="date" >
						</label>
						<label for="sync_all_todate" class="sync_todate_label">
					<?php echo esc_html__( 'To :', 'wpssw' ); ?> <input name="sync_all_todate" id="sync_all_todate" class="sync_all_todate" type="date" >
						</label>
					</td>
				</tr>
				<tr valign="top" class="synctr">
					<th scope="row" class="titledesc">
						<label><?php echo esc_html__( 'Sync Orders', 'wpssw' ); ?></label>
					</th>
					<td class="forminp">              
						<img src="<?php dirname( __FILE__ ); ?>images/spinner.gif" id="syncloader"><span id="synctext"><?php echo esc_html__( 'Synchronizing...', 'wpssw' ); ?></span><a class="wpssw-button" href="javascript:void(0)" id="sync">
						<?php
						esc_html_e( 'Click to Sync', 'wpssw' );
						?>
						</a><br><br>
						<i><strong><?php echo esc_html__( 'Note', 'wpssw' ); ?>:</strong> <?php echo esc_html__( 'Click to Sync button will be append all the existing orders to the selected sheets as above.', 'wpssw' ); ?></i>	
					</td>
				</tr>
				<tr valign="top" class="autosynctr">
					<th scope="row" class="titledesc">
						<label><?php echo esc_html__( 'Schedule Auto Sync', 'wpssw' ); ?></label>
					</th>
					<td class="forminp">              
						<div class="schedulecontainer">
							<input type="radio" name="scheduling_enable" value="0" <?php echo esc_attr( $wpssw_deactive ); ?> id="donotschedule">
							<label for="donotschedule">Do Not Schedule</label>
						</div>
						<div class="schedulecontainer">
							<input type="radio" name="scheduling_enable" value="1" id="autoschedule" <?php echo esc_attr( $wpssw_active ); ?>>
							<label for="autoschedule">Automatic Scheduling</label>
						</div>
						<div id="automatic-scheduling">
							<div class="schedulecontainer">
								<div class="input">
									<input type="radio" name="scheduling_run_on" value="recurrence" id="recurrenceradio" <?php echo esc_attr( $wpssw_recurrence ); ?> >
									<label for="recurrenceradio">Schedule Recurrence</label>
								</div>
								<select name="schedule_recurrence" id="schedule_recurrence">
									<?php
									foreach ( $wpssw_interval as $intkey => $intvalue ) {
										$selected = '';
										if ( (string) $intkey === (string) $wpssw_schedule_recurrence ) {
											$selected = 'selected="selected"';
										}
										?>
										<option value="<?php echo esc_attr( $intkey ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $intvalue ); ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="schedulecontainer">
								<div class="input">
									<input type="radio" name="scheduling_run_on" value="weekly" id="weeklyradio" <?php echo esc_attr( $wpssw_weekly ); ?>>
									<label for="weeklyradio">Every week on...</label>
								</div>
								<input type="hidden" name="scheduling_weekly_days" value="<?php echo esc_attr( $wpssw_scheduling_weekly_days ); ?>" id="weekly_days">
								<ul class="days-of-week" id="weekly">
									<?php
									foreach ( $wpssw_weekday as $weekname => $weekshortname ) {
										$selectedday = '';
										if ( in_array( $weekname, $wpssw_scheduling_weekly_days_array, true ) ) {
											$selectedday = 'selected';
										}
										?>
									<li data-day="<?php echo esc_attr( $weekname ); ?>" class="<?php echo esc_attr( $selectedday ); ?>"><?php echo esc_html( $weekshortname ); ?></li>
								<?php } ?>
								</ul>
							</div>
							<div class="schedulecontainer">
								<div class="input">
									<input type="radio" name="scheduling_run_on" value="onetime" id="onetime" <?php echo esc_attr( $wpssw_onetime ); ?>>
									<label for="onetime">One time run at</label>
								</div>
								<div id="scheduling_date">
									<input type="date" name="scheduling_date" autocomplete="off" value="<?php echo esc_attr( $wpssw_scheduling_date ); ?>">
									<input type="time" name="scheduling_time" autocomplete="off" value="<?php echo esc_attr( $wpssw_scheduling_time ); ?>">
								</div>
							</div>
						</div>
					</td>
				</tr>
						<?php
			}
		}
		/**
		 * Output Custom Static headers with dropdown field.
		 */
		public static function wpssw_woocommerce_admin_field_custom_headers_action() {
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="custom_header_action"><?php echo esc_html__( 'Custom Static Headers', 'wpssw' ); ?></label>
				</th>
				<td class="forminp">              
					<label for="custom_header_action">
						<input name="custom_header_action" id="custom_header_action" type="checkbox" class="" value="1"><span class="checkbox-switch"></span> 							
					</label> <br><br>
					<div class="custom-input-div">
						<input type="text" name="custom_headers_val" id="custom_headers_val" placeholder="Enter Header Name"><br><br>
						<select name="custom_headers_val_dropdown" id="custom_headers_val_dropdown">
							<option value="IP_address">IP Address</option>
							<option value="site_name">Site Name</option>
							<option value="site_URL">Site URL</option>
							<option value="user_agent">User Agent</option>
							<option value="user_name">User Name</option>
							<option value="blank">Blank</option>
						</select>
						<button class="wpssw-button" type="button" id='add_ctm_val'><?php echo esc_html__( 'Add', 'wpssw' ); ?></button>
					</div>   
				</td>
			</tr>
					<?php
		}
		/**
		 * Output Allow to copy same columns field for orders of sheet
		 */
		public static function wpssw_woocommerce_admin_field_repeat_checkbox() {
			$wpssw_is_checked = '';
			$wpssw_is_repeat  = parent::wpssw_option( 'wpssw_repeat_checkbox' );
			if ( 'yes' === (string) $wpssw_is_repeat ) {
				$wpssw_is_checked = 'checked';
			}
			?>
			<tr valign="top" class="repeat_checkbox">
				<th scope="row" class="titledesc">
					<label for="id_repeat_checkbox"><?php echo esc_html__( 'Allow to copy same columns', 'wpssw' ); ?></label>
				</th>
				<td class="forminp">              
					<label for="id_repeat_checkbox">
						<input name="repeat_checkbox" id="id_repeat_checkbox" type="checkbox" class="" value="1" <?php echo esc_html( $wpssw_is_checked ); ?>><span class="checkbox-switch"></span> 							
					</label>
					<br><br><i><strong><?php echo esc_html__( 'Note', 'wpssw' ); ?>:</strong> <?php echo esc_html__( 'It will allow to copy same columns into the rows i.e. Billing First Name, Billing Last Name etc.', 'wpssw' ); ?> <br><?php echo esc_html__( 'For More Details', 'wpssw' ); ?> <a href="<?php echo esc_url( parent::$doc_sheet_setting_allowtocopy ); ?>" target="_blank"> <?php echo esc_html__( 'click here.', 'wpssw' ); ?></a></i>
				</td>
			</tr>
					<?php
		}
		/**
		 * Output new spreadsheet name input field.
		 */
		public static function wpssw_woocommerce_admin_field_new_spreadsheetname() {
			?>
			<tr valign="top" id="newsheet" class="newsheetinput">
				<th scope="row" class="titledesc">
					<label for="sheet_headers"><?php echo esc_html__( 'Enter New Spreadsheet name', 'wpssw' ); ?></label>
				</th>
				<td class="forminp">
					<input name="spreadsheetname" id="spreadsheetname" type="text">
				</td>
			</tr>
					<?php
		}
		/**
		 * Added v4.6
		 * Output Category filter field for Product
		 *
		 * @param int $flag .
		 */
		public static function wpssw_woocommerce_admin_field_product_category_as_order_filter( $flag = 0 ) {
			$wpssw_category_filter       = parent::wpssw_option( 'wpssw_category_filter' );
			$wpssw_category_filter_ids   = parent::wpssw_option( 'wpssw_category_filter_ids' );
			$wpssw_product_category_list = self::wpssw_get_product_categories();
			if ( 0 === (int) $flag ) {
				?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="product_category_select"><?php echo esc_html__( 'Select Category', 'wpssw' ); ?></label>
					</th>
					<td class="exportrow">              
						<label for="product_category_select">
							<input name="category_filter" id="product_category_select" type="checkbox" class="" value="1" 
					<?php
					if ( $wpssw_category_filter ) {
						echo 'checked'; }
					?>
							><span class="checkbox-switch"></span> 	<img id="loaderprdcatheader" src="<?php dirname( __FILE__ ); ?>images/spinner.gif">						
						</label>
					</td>
				</tr>
						<?php
			}
			if ( ! empty( $wpssw_category_filter ) || $flag ) {
				if ( is_array( $wpssw_category_filter_ids ) && ! empty( $wpssw_category_filter_ids ) ) {
					$wpssw_category_filter_ids = parent::wpssw_convert_int( $wpssw_category_filter_ids );
				}
				?>
				<tr class="td-producat-wpssw">
					<td colspan="2" class="td-wpssw-headers">
						<div class='wpssw_headers'>
							<label for="sheet_headers"></label>
							<div id="wpssw-headers-notice">
								<i><?php echo esc_html__( 'Select orders of below product categories as you want export within the spreadsheet.', 'wpssw' ); ?></i>
							</div> 
							<ul id="productcategory-sortable">
					<?php
					foreach ( $wpssw_product_category_list as $wpssw_key => $wpssw_val ) {
						$wpssw_checked = '';
						if ( is_array( $wpssw_category_filter_ids ) && in_array( (int) $wpssw_key, $wpssw_category_filter_ids, true ) ) {
							$wpssw_checked = 'checked';
						}
						?>
								<li class="ui-state-default"><label for="<?php echo esc_html( 'cat_' . $wpssw_val ); ?>"><span class="ui-icon ui-icon-caret-2-n-s"></span><?php echo esc_html( $wpssw_val ); ?><input type="checkbox" name="productcat_filter[]" value="<?php echo esc_attr( $wpssw_key ); ?>" id="<?php echo esc_attr( 'cat_' . $wpssw_val ); ?>" class="producatheaders_chk" <?php echo esc_html( $wpssw_checked ); ?>><span class="checkbox-switch-new"></span></label></li>
							<?php } ?>
							</ul>
							<button type="button" id="producatselectall" 
							<?php
							if ( ! empty( $wpssw_selections ) ) {
								?>
								class="wpssw-button wpssw-button-secondary wpssw-prdcatselect"
								<?php
							} else {
								?>
								class="wpssw-button wpssw-button-secondary" <?php } ?>> <?php esc_html_e( 'Select all', 'wpssw' ); ?></button>                
							<button type="button" id="producatselectnone" 
									<?php
									if ( ! empty( $wpssw_selections ) ) {
										?>
								class="wpssw-button wpssw-button-secondary wpssw-prdcatselect"
										<?php
									} else {
										?>
								class="wpssw-button wpssw-button-secondary"<?php } ?> > <?php esc_html_e( 'Select none', 'wpssw' ); ?></button>
						</div>
					</td>
				</tr>
							<?php } else { ?>
			<tr class="td-producat-wpssw">
			</tr>
						<?php
							}
		}
		/**
		 * Output Spreadsheet dropdown field.
		 */
		public static function wpssw_woocommerce_admin_field_select_spreadsheet() {
			$spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
			?>
			<tr valign="top">
				<th scope="row" class="titledesc"> <label for="woocommerce_spreadsheet">Select Spreadsheet <span class="woocommerce-help-tip" data-tip="Please select Google Spreadsheet."></span></label>
				</th>
				<td class="forminp forminp-select">
					<select name="woocommerce_spreadsheet" id="woocommerce_spreadsheet"  class="">
				<?php
				$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
				$selected            = '';

				foreach ( $spreadsheets_list as $spreadsheetid => $spreadsheetname ) {
					if ( (string) $wpssw_spreadsheetid === $spreadsheetid ) {
						$selected = 'selected="selected"';
					}
					?>
							<option value="<?php echo esc_attr( $spreadsheetid ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $spreadsheetname ); ?></option>
						<?php $selected = ''; } ?>
					</select>
				</td>
			</tr>
					<?php
		}
		/**
		 * Added v4.6
		 * Output Row Background Color field for orders of sheet
		 */
		public static function wpssw_woocommerce_admin_field_order_row_color() {
			$wpssw_color_code_enable = parent::wpssw_option( 'wpssw_color_code' );
			$wpssw_oddcolor          = parent::wpssw_option( 'wpssw_oddcolor' );
			$wpssw_evencolor         = parent::wpssw_option( 'wpssw_evencolor' );
			$wpssw_inputoption       = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_user = '';
			$wpssw_raw  = '';
			if ( 'USER_ENTERED' === (string) $wpssw_inputoption ) {
				$wpssw_user = 'selected';
			} else {
				$wpssw_raw = 'selected';
			}
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="color_code"><?php echo esc_html__( 'Row Background Color', 'wpssw' ); ?></label>
				</th>
				<td>              
					<label for="color_code">
						<input name="color_code" id="color_code" type="checkbox" class="" value="1" 
				<?php
				if ( $wpssw_color_code_enable ) {
					echo 'checked'; }
				?>
						><span class="checkbox-switch"></span>					
					</label>
				</td>
			</tr>
			<tr valign="top" id="color_selection">
				<th scope="row">
				</th>
				<td class="forminp radio-box-td ">
					<input type="color" id="color_code_odd" name="oddcolor" value="<?php echo esc_attr( $wpssw_oddcolor ); ?>"><label for="color_code_odd"><?php echo esc_html__( ' Odd Rows', 'wpssw' ); ?></label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type="color" id="color_code_even" name="evencolor" value="<?php echo esc_attr( $wpssw_evencolor ); ?>"><label for="color_code_even"><?php echo esc_html__( ' Even Rows', 'wpssw' ); ?></label>
					<br><br><i><strong><?php echo esc_html__( 'Note', 'wpssw' ); ?>:</strong> <?php echo esc_html__( 'Odd & Even Rows are calculated as per the order id.', 'wpssw' ); ?></i>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="inputoption"><?php echo esc_html__( 'Row Input Format Option', 'wpssw' ); ?></label>
				</th>
				<td>              
					<label for="inputoption">
						<select name="inputoption">
							<option value="USER_ENTERED" <?php echo esc_attr( $wpssw_user ); ?> >USER ENTERED</option>
							<option value="RAW" <?php echo esc_attr( $wpssw_raw ); ?>>RAW</option>
						</select>					
					</label>
					<br><br><i><strong><?php echo esc_html__( 'Note', 'wpssw' ); ?>: </strong><?php echo esc_html__( 'Determines how input data should be interpreted. For More Details', 'wpssw' ); ?><a href="<?php echo esc_url( 'https://developers.google.com/sheets/api/reference/rest/v4/ValueInputOption', 'wpssw' ); ?>" target="_blank"> <?php echo esc_html__( 'click here.', 'wpssw' ); ?></a></i>
				</td>
			</tr>
					<?php
		}
		/**
		 * Added v5.1
		 * Output Price Format field for orders of sheet
		 */
		public static function wpssw_woocommerce_admin_field_price_format() {
			$wpssw_price_format = parent::wpssw_option( 'wpssw_price_format' );
			if ( ! $wpssw_price_format ) {
				$wpssw_price_format = 'plain';
			}
			$wpssw_plain     = '';
			$wpssw_formatted = '';
			if ( 'plain' === (string) $wpssw_price_format ) {
				$wpssw_plain = 'selected';
			} else {
				$wpssw_formatted = 'selected';
			}
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="wpssw_price_format"><?php echo esc_html__( 'Price Format', 'wpssw' ); ?></label>
				</th>
				<td>              
					<label for="wpssw_price_format">
						<select name="wpssw_price_format">
							<option value="plain" <?php echo esc_html( $wpssw_plain ); ?> ><?php echo esc_html__( 'Only Values', 'wpssw' ); ?></option>
							<option value="formatted" <?php echo esc_html( $wpssw_formatted ); ?>><?php echo esc_html__( 'Formatted Values with Symbol', 'wpssw' ); ?></option>
						</select>					
					</label>
				</td>
			</tr>
					<?php
		}
		/**
		 * Added v5.1
		 * Output Price Format field for orders of sheet
		 */
		public static function wpssw_woocommerce_admin_field_import_orders() {
			$wpssw_order_import = parent::wpssw_option( 'wpssw_order_import' );
			$wpssw_order_update = parent::wpssw_option( 'wpssw_order_update' );
			$wpssw_order_delete = parent::wpssw_option( 'wpssw_order_delete' );

			?>
			<tr valign="top" id="import_order_checkbox_row" class="ord_import_row" >
				<th scope="row" class="titledesc">
					<label><?php esc_html_e( 'Import Orders', 'wpssw' ); ?></label>
				</th>
				<td class="forminp">      
					<label for="import_order_checkbox">
						<input name="import_order_checkbox" id="import_order_checkbox" type="checkbox" class="" value="1" 
						<?php
						if ( $wpssw_order_import ) {
							echo 'checked="checked"';}
						?>
						><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 		
					</label> 
				</td>
			</tr>
			<tr valign="top" id="update_order_checkbox_row" class="wpssw_crud_ord_row" >
				<th scope="row" class="titledesc">
					<label><?php esc_html_e( 'Update Order', 'wpssw' ); ?></label>
				</th>
				<td class="forminp">    
					<label for="update_order_checkbox">
						<input name="update_order_checkbox" id="update_order_checkbox" type="checkbox" class="" value="1" 
						<?php
						if ( $wpssw_order_update ) {
							echo 'checked="checked"';}
						?>
						><span class="checkbox-switch"></span><span class="checkbox-switch"></span>
					</label> 
				</td>
			</tr>
			<tr valign="top" id="delete_order_checkbox_row" class="wpssw_crud_ord_row" >
				<th scope="row" class="titledesc">
					<label><?php esc_html_e( 'Delete Order', 'wpssw' ); ?></label>
				</th>
				<td class="forminp">     
					<label for="delete_order_checkbox">
						<input name="delete_order_checkbox" id="delete_order_checkbox" type="checkbox" class="" value="1" 
						<?php
						if ( $wpssw_order_delete ) {
							echo 'checked="checked"';}
						?>
						><span class="checkbox-switch"></span><span class="checkbox-switch"></span> 			
					</label> 
				</td>
			</tr>
			<?php if ( 1 === (int) $wpssw_order_update || 1 === (int) $wpssw_order_delete ) { ?>
				<tr valign="top" class="wpssw_crud_ord_row ord_import_row">
					<th scope="row" class="titledesc">
						<label></label>
					</th>
					<td class="forminp"> 
						<img src="images/spinner.gif" id="ordimportsyncloader"><span id="ordimportsynctext"><?php esc_html_e( 'Checking...', 'wpssw' ); ?></span><a class="wpssw-button" href="javascript:void(0)" id="ordimportsync"><?php echo esc_html__( 'Import Order', 'wpssw' ); ?>
							</a><br /><a class="wpssw-button" href="javascript:void(0)" id="ordimportsyncbtm"><?php esc_html_e( 'Proceed', 'wpssw' ); ?></a><a class="wpssw-button" href="javascript:void(0)" id="ordcancelsyncbtm"><?php esc_html_e( 'Cancel', 'wpssw' ); ?></a><i class='ordnotice'><strong><?php echo esc_html__( 'Note', 'wpssw' ); ?>: </strong><?php echo esc_html__( 'Please take the backup of the database before you are going to click on the import button.', 'wpssw' ); ?></i>  
					</td>
				</tr>
				<?php } ?>
				<tr valign="top" class="ord_import_row">
					<th scope="row" class="titledesc">
						<label></label>
					</th>
					<td class="forminp"> 
						<a href="<?php echo esc_url( 'https://docs.wpsyncsheets.com/how-to-import-orders/' ); ?>" target="_blank"><?php echo esc_html__( 'How to Import Orders?', 'wpssw' ); ?></a>  
					</td>
				</tr>
				<?php
		}
		/**
		 * Order ID in Ascending Order and Descending Order
		 */
		public static function wpssw_woocommerce_admin_field_order_asc_desc() {
			$order_ascdesc = parent::wpssw_option( 'wpssw_order_ascdesc' );
			if ( ! empty( $order_ascdesc ) ) {
				if ( 'ascorder' === (string) $order_ascdesc ) {
					$wpssw_ascorder  = "checked='checked' disabled='disabled'";
					$wpssw_descorder = "disabled='disabled'";
				} else {
					$wpssw_descorder = "checked='checked' disabled='disabled'";
					$wpssw_ascorder  = "disabled='disabled'";
				}
				$wpssw_disableclass = 'disabled';
			} else {
				$wpssw_ascorder     = "checked='checked' disabled='disabled'";
				$wpssw_descorder    = "disabled='disabled'";
				$wpssw_disableclass = 'disabled';
			}
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="order_asc_desc"><?php echo esc_html__( 'Spreadsheet Row Order', 'wpssw' ); ?></label>
				</th>
				<td class="forminp radio-box-td ">
					<input type="radio" id="asc_order" name="order_ascdesc" class="manage_order <?php echo esc_attr( $wpssw_disableclass ); ?>" value="ascorder"<?php echo esc_html( $wpssw_ascorder ); ?>><label for="asc_order"><?php echo esc_html__( ' Ascending Order', 'wpssw' ); ?></label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type="radio" id="desc_order" name="order_ascdesc" class="manage_order <?php echo esc_attr( $wpssw_disableclass ); ?>" value="descorder" <?php echo esc_html( $wpssw_descorder ); ?>><label for="desc_order"><?php echo esc_html__( ' Descending Order', 'wpssw' ); ?></label>
				</td>
			</tr>
					<?php
		}
		/**
		 * Chart Display Function Start
		 * Add Graph section
		 */
		public static function wpssw_woocommerce_admin_field_add_graph_section() {
			$wpssw_graphsheets_list = array();
			if ( ! empty( parent::wpssw_option( 'wpssw_graphsheets_list' ) ) ) {
				$wpssw_graphsheets_list = parent::wpssw_option( 'wpssw_graphsheets_list' );
			}
			$wpssw_products_sold_graph_type      = parent::wpssw_option( 'wpssw_products_sold_graph_type' );
			$wpssw_sales_orders_graph_type       = parent::wpssw_option( 'wpssw_sales_orders_graph_type' );
			$wpssw_total_orders_graph_type       = parent::wpssw_option( 'wpssw_total_orders_graph_type' );
			$wpssw_total_customers_graph_type    = parent::wpssw_option( 'wpssw_total_customers_graph_type' );
			$wpssw_total_used_coupons_graph_type = parent::wpssw_option( 'wpssw_total_used_coupons_graph_type' );
			$wpssw_is_checked_total              = '';
			$wpssw_is_checked_sales              = '';
			$wpssw_is_disabled_total             = '';
			$wpssw_is_disabled_sales             = '';
			$wpssw_is_checked_product            = '';
			$wpssw_is_disabled_product           = '';
			$wpssw_is_checked_customers          = '';
			$wpssw_is_disabled_customers         = '';
			$wpssw_is_checked_used_coupons       = '';
			$wpssw_is_disabled_used_coupons      = '';
			if ( in_array( 'sales_orders_graph', $wpssw_graphsheets_list, true ) ) {
				$wpssw_is_checked_sales = 'checked';
			} else {
				$wpssw_is_disabled_sales = 'disabled-regenerate-graph';
			}
			if ( in_array( 'total_orders_graph', $wpssw_graphsheets_list, true ) ) {
				$wpssw_is_checked_total = 'checked';
			} else {
				$wpssw_is_disabled_total = 'disabled-regenerate-graph';
			}
			if ( in_array( 'products_sold_graph', $wpssw_graphsheets_list, true ) ) {
				$wpssw_is_checked_product = 'checked';
			} else {
				$wpssw_is_disabled_product = 'disabled-regenerate-graph';
			}
			if ( in_array( 'total_customers_graph', $wpssw_graphsheets_list, true ) ) {
				$wpssw_is_checked_customers = 'checked';
			} else {
				$wpssw_is_disabled_customers = 'disabled-regenerate-graph';
			}
			if ( in_array( 'total_used_coupons_graph', $wpssw_graphsheets_list, true ) ) {
				$wpssw_is_checked_used_coupons = 'checked';
			} else {
				$wpssw_is_disabled_used_coupons = 'disabled-regenerate-graph';
			}
			$wpssw_is_sales_line        = '';
			$wpssw_is_total_line        = '';
			$wpssw_is_product_line      = '';
			$wpssw_is_customers_line    = '';
			$wpssw_is_used_coupons_line = '';
			$wpssw_is_sales_bar         = '';
			$wpssw_is_total_bar         = '';
			$wpssw_is_product_bar       = '';
			$wpssw_is_customers_bar     = '';
			$wpssw_is_used_coupons_bar  = '';
			if ( 'line' === (string) $wpssw_sales_orders_graph_type ) {
				$wpssw_is_sales_line = 'checked';
			}
			if ( 'column' === (string) $wpssw_sales_orders_graph_type ) {
				$wpssw_is_sales_bar = 'checked';
			}
			if ( 'line' === (string) $wpssw_total_orders_graph_type ) {
				$wpssw_is_total_line = 'checked';
			}
			if ( 'column' === (string) $wpssw_total_orders_graph_type ) {
				$wpssw_is_total_bar = 'checked';
			}
			if ( 'line' === (string) $wpssw_products_sold_graph_type ) {
				$wpssw_is_product_line = 'checked';
			}
			if ( 'column' === (string) $wpssw_products_sold_graph_type ) {
				$wpssw_is_product_bar = 'checked';
			}
			if ( 'line' === (string) $wpssw_total_customers_graph_type ) {
				$wpssw_is_customers_line = 'checked';
			}
			if ( 'column' === (string) $wpssw_total_customers_graph_type ) {
				$wpssw_is_customers_bar = 'checked';
			}
			if ( 'line' === (string) $wpssw_total_used_coupons_graph_type ) {
				$wpssw_is_used_coupons_line = 'checked';
			}
			if ( 'column' === (string) $wpssw_total_used_coupons_graph_type ) {
				$wpssw_is_used_coupons_bar = 'checked';
			}
			?>
				<tr valign="top">
					<th scope="row" class="titledesc">
				<?php echo esc_html__( 'Sales Orders', 'wpssw' ); ?>
					</th>
					<td class="forminp forminp-checkbox">  
						<label for="sales_orders_graph">
							<input name="graphsheets_list[]" id="sales_orders_graph" type="checkbox" class="sales" value="sales_orders_graph" <?php echo esc_html( $wpssw_is_checked_sales ); ?>><span class="checkbox-switch"></span> 							
						</label>
					</td>
					<td class="sales-tdclass">
						<label>
							<input type="radio" name="sales_orders_graph_type" value="column" <?php echo esc_html( $wpssw_is_sales_bar ); ?> class="graph-radio-button" >
							<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/bar_chart.png' ); ?>" class="graph_type_radio">
						</label>
						<label>
							<input type="radio" name="sales_orders_graph_type" value="line" <?php echo esc_html( $wpssw_is_sales_line ); ?> class="graph-radio-button">
							<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/line_chart.png' ); ?>" class="graph_type_radio">
						</label>
					</td>
					<td class="sales-tdclass">  
						<a href="javascript:void(0)" id="regenerate_sales_graph" class="graph-regenerate wpssw-button <?php echo esc_html( $wpssw_is_disabled_sales ); ?>" ><?php echo esc_html__( 'Regenerate', 'wpssw' ); ?> </a>
						<img src="images/spinner.gif" id="sales_chartloader" class="chartloader">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
				<?php echo esc_html__( 'Total Orders', 'wpssw' ); ?>
					</th>
					<td class="forminp forminp-checkbox">  
						<label for="total_orders_graph">
							<input name="graphsheets_list[]" id="total_orders_graph" type="checkbox" class="total" value="total_orders_graph" <?php echo esc_html( $wpssw_is_checked_total ); ?>><span class="checkbox-switch"></span>				
						</label>
					</td>
					<td class="total-tdclass">
						<label>
							<input type="radio" name="total_orders_graph_type" value="column" <?php echo esc_html( $wpssw_is_total_bar ); ?> class="graph-radio-button">
							<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/bar_chart.png' ); ?>" class="graph_type_radio">
						</label>
						<label>
							<input type="radio" name="total_orders_graph_type" value="line" <?php echo esc_html( $wpssw_is_total_line ); ?> class="graph-radio-button">
							<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/line_chart.png' ); ?>" class="graph_type_radio">
						</label>
					</td>
					<td class="total-tdclass">  	
						<a href="javascript:void(0)" id="regenerate_total_graph" class="graph-regenerate wpssw-button <?php echo esc_html( $wpssw_is_disabled_total ); ?>" ><?php echo esc_html__( 'Regenerate', 'wpssw' ); ?> </a>
						<img src="images/spinner.gif" id="total_chartloader" class="chartloader">			
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
				<?php echo esc_html__( 'Products Sold', 'wpssw' ); ?>
					</th>
					<td class="forminp forminp-checkbox">  
						<label for="products_sold_graph">
							<input name="graphsheets_list[]" id="products_sold_graph" type="checkbox" class="product" value="products_sold_graph"  <?php echo esc_html( $wpssw_is_checked_product ); ?>><span class="checkbox-switch"></span> 							
							</label>
					</td>
					<td class="product-tdclass">
						<label>
							<input type="radio" name="products_sold_graph_type" value="column" <?php echo esc_html( $wpssw_is_product_bar ); ?> class="graph-radio-button">
							<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/bar_chart.png' ); ?>" class="graph_type_radio">
						</label>
						<label>
							<input type="radio" name="products_sold_graph_type" value="line" <?php echo esc_html( $wpssw_is_product_line ); ?> class="graph-radio-button">
							<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/line_chart.png' ); ?>" class="graph_type_radio">
						</label>
					</td>
					<td class="product-tdclass">  	
						<a href="javascript:void(0)" id="regenerate_product_graph" class="graph-regenerate wpssw-button <?php echo esc_html( $wpssw_is_disabled_product ); ?>" ><?php echo esc_html__( 'Regenerate', 'wpssw' ); ?> </a>
						<img src="images/spinner.gif" id="product_chartloader" class="chartloader">			
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
				<?php echo esc_html__( 'Total Customers', 'wpssw' ); ?>
					</th>
					<td class="forminp forminp-checkbox">  
						<label for="total_customers_graph">
							<input name="graphsheets_list[]" id="total_customers_graph" type="checkbox" class="customers" value="total_customers_graph" <?php echo esc_html( $wpssw_is_checked_customers ); ?>><span class="checkbox-switch"></span> 							
						</label>
					</td>
					<td class="customers-tdclass">
						<label>
							<input type="radio" name="total_customers_graph_type" value="column" <?php echo esc_html( $wpssw_is_customers_bar ); ?> class="graph-radio-button">
							<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/bar_chart.png' ); ?>" class="graph_type_radio">
						</label>
						<label>
							<input type="radio" name="total_customers_graph_type" value="line" <?php echo esc_html( $wpssw_is_customers_line ); ?> class="graph-radio-button">
							<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/line_chart.png' ); ?>" class="graph_type_radio">
						</label>
					</td>
					<td class="customers-tdclass">  	
						<a href="javascript:void(0)" id="regenerate_customers_graph" class="graph-regenerate wpssw-button <?php echo esc_html( $wpssw_is_disabled_customers ); ?>" ><?php echo esc_html__( 'Regenerate', 'wpssw' ); ?> </a>
						<img src="images/spinner.gif" id="customers_chartloader" class="chartloader">			
					</td>
				</tr>
				<tr valign="top" class="last-section-tr">
					<th scope="row" class="titledesc">
				<?php echo esc_html__( 'Total Used Coupons Graph', 'wpssw' ); ?>
					</th>
					<td class="forminp forminp-checkbox">  
						<label for="total_used_coupons_graph">
							<input name="graphsheets_list[]" id="total_used_coupons_graph" type="checkbox" class="used-coupons" value="total_used_coupons_graph" <?php echo esc_html( $wpssw_is_checked_used_coupons ); ?>><span class="checkbox-switch"></span> 							
						</label>
					</td>
					<td class="used-coupons-tdclass">
						<label>
							<input type="radio" name="total_used_coupons_graph_type" value="column" <?php echo esc_html( $wpssw_is_used_coupons_bar ); ?> class="graph-radio-button">
							<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/bar_chart.png' ); ?>" class="graph_type_radio">
						</label>
						<label>
							<input type="radio" name="total_used_coupons_graph_type" value="line" <?php echo esc_html( $wpssw_is_used_coupons_line ); ?> class="graph-radio-button">
							<img src="<?php echo esc_url( WPSSW_URL . 'assets/images/line_chart.png' ); ?>" class="graph_type_radio">
						</label>
					</td>
					<td class="used-coupons-tdclass">  	
						<a href="javascript:void(0)" id="regenerate_used_coupons_graph" class="graph-regenerate wpssw-button <?php echo esc_html( $wpssw_is_disabled_used_coupons ); ?>" ><?php echo esc_html__( 'Regenerate', 'wpssw' ); ?> </a>
						<img src="images/spinner.gif" id="used_coupons_chartloader" class="chartloader">			
					</td>
				</tr>
					<?php
		}
		/**
		 * Sync single order
		 */
		public static function wpssw_sync_single_order_data() {
			if ( ! isset( $_POST['sync_nonce_token'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sync_nonce_token'] ) ), 'sync_nonce' )
			) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );

			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_order               = wc_get_order( $order_id );
			$wpssw_old_status          = $wpssw_order->get_status();
			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			if ( strpos( $wpssw_old_status, '-' ) ) {
				$str              = str_replace( '-', '_', $wpssw_old_status );
				$status           = $str . '_orders';
				$wpssw_old_status = str_replace( '-', ' ', $wpssw_old_status );
			} else {
				$status = $wpssw_old_status . '_orders';
			}
			if ( 'yes' === (string) parent::wpssw_option( $status ) ) {
				$wpssw_sheetname[ $status ] = ucwords( $wpssw_old_status ) . ' Orders';
				if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname[ $status ] ) ) {
					echo 'statussheetnotexist';
					die;
				}
				$wpssw_getactivesheets[] = $wpssw_sheetname[ $status ] . '!A:A';
			}
			if ( 'yes' === (string) parent::wpssw_option( 'all_orders' ) ) {
				if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, 'All Orders' ) ) {
					echo 'allordersheetnotexist';
					die;
				}
				$wpssw_sheetname['all_order'] = 'All Orders';
				$wpssw_getactivesheets[]      = 'All Orders!A:A';
			}
			/*Get First Column Value from all sheets*/
			try {
				$param                  = array();
				$param['spreadsheetid'] = $wpssw_spreadsheetid;
				$param['ranges']        = array( 'ranges' => $wpssw_getactivesheets );
				$wpssw_response         = self::$instance_api->getbatchvalues( $param );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
			$wpssw_existingorders = array();
			foreach ( $wpssw_response->getValueRanges() as $wpssw_order ) {
				$wpssw_rangetitle                          = explode( "'!A", $wpssw_order->range );
				$wpssw_sheettitle                          = str_replace( "'", '', $wpssw_rangetitle[0] );
				$wpssw_data                                = array_map(
					function( $wpssw_element ) {
						if ( isset( $wpssw_element['0'] ) ) {
							return $wpssw_element['0'];
						} else {
							return '';
						}
					},
					$wpssw_order->values
				);
				$wpssw_existingorders[ $wpssw_sheettitle ] = $wpssw_data;
			}
			$wpssw_dataarray = array();
			$wpssw_isexecute = 0;
			foreach ( $wpssw_sheetname as $wpssw_sheet_slug => $sheetname ) {
				$wpssw_values_array = array();
				if ( self::wpssw_check_product_category( $order_id ) ) {
					continue;
				}
				if ( in_array( $order_id, $wpssw_existingorders[ $sheetname ], true ) ) {
					if ( parent::wpssw_is_event_calender_ticket_active() ) {
						self::wpssw_insert_event_data_into_sheet( $order_id, $sheetname );
						continue;
					}
				}
				self::wpssw_insert_data_into_sheet( $order_id, $sheetname );
			}
			echo 'successful';
			die;
		}
		/**
		 * Clear General settings sheets
		 */
		public static function wpssw_clear_all_sheet() {
			if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$requestbody         = self::$instance_api->clearobject();
			$total_headers       = count( parent::wpssw_option( 'wpssw_sheet_headers_list' ) ) + 1;
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				$total_headers = count( $wpssw_woo_event_headers ) + $total_headers;
			}
			$last_column              = parent::wpssw_get_column_index( $total_headers );
			$wpssw_order_status_array = self::$wpssw_default_status_slug;
			/* Custom Order Status*/
			$wpssw_status_array             = wc_get_order_statuses();
			$wpssw_status_array['wc-trash'] = 'Trash';
			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				if ( ! in_array( $wpssw_key, $wpssw_order_status_array, true ) ) {
					$wpssw_order_status_array[]              = $wpssw_key;
					$wpssw_custom_order_status[ $wpssw_key ] = $wpssw_val;
				}
			}
			$wpssw_existingsheetsnames      = array();
			$wpssw_response                 = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames      = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_order_status_array_count = count( $wpssw_order_status_array );
			for ( $i = 0; $i < $wpssw_order_status_array_count; $i++ ) {
				$i               = (int) $i;
				$wpssw_sheetname = '';
				if ( ( 0 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'pending_orders' ) ) ) {
					$wpssw_sheetname = 'Pending Orders';
				} elseif ( ( 1 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'processing_orders' ) ) ) {
					$wpssw_sheetname = 'Processing Orders';
				} elseif ( ( 2 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'on_hold_orders' ) ) ) {
					$wpssw_sheetname = 'On Hold Orders';
				} elseif ( ( 3 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'completed_orders' ) ) ) {
					$wpssw_sheetname = 'Completed Orders';
				} elseif ( ( 4 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'cancelled_orders' ) ) ) {
					$wpssw_sheetname = 'Cancelled Orders';
				} elseif ( ( 5 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'refunded_orders' ) ) ) {
					$wpssw_sheetname = 'Refunded Orders';
				} elseif ( ( 6 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'failed_orders' ) ) ) {
					$wpssw_sheetname = 'Failed Orders';
				}
				if ( $i > 6 ) {
					$wpssw_status = substr( $wpssw_order_status_array[ $i ], strpos( $wpssw_order_status_array[ $i ], '-' ) + 1 );
					if ( 'yes' === (string) parent::wpssw_option( $wpssw_status ) ) {
						$wpssw_sheetname = $wpssw_custom_order_status[ $wpssw_order_status_array[ $i ] ] . ' Orders';
					}
				}
				if ( 'wc-trash' === (string) $wpssw_order_status_array[ $i ] ) {
					$wpssw_order_status_array[ $i ] = 'trash';
				}
				if ( ! empty( $wpssw_sheetname ) ) {
					if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
						echo 'sheetnotexist';
						die;
					}
					try {
						$range                  = $wpssw_sheetname . '!A2:' . $last_column . '10000';
						$param                  = array();
						$param['spreadsheetid'] = $wpssw_spreadsheetid;
						$param['sheetname']     = $range;
						$param['requestbody']   = $requestbody;
						$response               = self::$instance_api->clear( $param );
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
			}
			if ( 'yes' === (string) parent::wpssw_option( 'all_orders' ) ) {
				$wpssw_sheetname = 'All Orders';
				if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
					echo 'sheetnotexist';
					die;
				}
				try {
					$range                  = $wpssw_sheetname . '!A2:' . $last_column . '10000';
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['sheetname']     = $range;
					$param['requestbody']   = $requestbody;
					$response               = self::$instance_api->clear( $param );
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			echo 'successful';
			die();
		}
		/**
		 * Check for existing spreadsheet
		 */
		public static function wpssw_check_existing_sheet() {

			if ( ! isset( $_POST['sync_nonce_token'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sync_nonce_token'] ) ), 'sync_nonce' )
			) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$wpssw_sheetnames = array( 'Pending Orders', 'Processing Orders', 'On Hold Orders', 'Completed Orders', 'Cancelled Orders', 'Refunded Orders', 'Failed Orders', 'Trash Orders', 'All Orders' );
			if ( ! isset( $_POST['id'] ) ) {
				echo esc_html__( 'Spreadsheet id not found.', 'wpssw' );
				die();
			}
			$wpssw_spreadsheetid = sanitize_text_field( wp_unslash( $_POST['id'] ) );
			if ( 'new' !== (string) $wpssw_spreadsheetid && 0 !== (int) $wpssw_spreadsheetid ) {
				$wpssw_exist               = 0;
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );
				foreach ( $wpssw_existingsheetsnames as $sheetname ) {
					if ( in_array( $sheetname, $wpssw_sheetnames, true ) ) {
						$wpssw_exist = 1;
						break;
					}
				}
				if ( $wpssw_exist ) {
					echo 'successful';
					die();
				}
			}
			die();
		}
		/**
		 * Save Export orders settings
		 */
		public static function wpssw_export_order() {
			if ( ! isset( $_POST['wpssw_export_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_export_settings'] ) ), 'save_export_settings' ) ) {
				echo 'error';
				die();
			}
			$wpssw_fromdate        = isset( $_POST['from_date'] ) ? sanitize_text_field( wp_unslash( $_POST['from_date'] ) ) : '';
			$wpssw_todate          = isset( $_POST['to_date'] ) ? sanitize_text_field( wp_unslash( $_POST['to_date'] ) ) : '';
			$wpssw_exportall       = isset( $_POST['exportall'] ) ? sanitize_text_field( wp_unslash( $_POST['exportall'] ) ) : '';
			$wpssw_spreadsheetname = isset( $_POST['spreadsheetname'] ) ? sanitize_text_field( wp_unslash( $_POST['spreadsheetname'] ) ) : '';
			$iscategory_enable     = isset( $_POST['category_select'] ) ? sanitize_text_field( wp_unslash( $_POST['category_select'] ) ) : '';
			$wpssw_category_ids    = array();
			if ( isset( $_POST['category_ids'] ) ) {
				$wpssw_category_ids = array_map( 'sanitize_text_field', wp_unslash( $_POST['category_ids'] ) );
			}
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_spreadsheetid = self::wpssw_create_spreadsheet( $wpssw_spreadsheetname );
			if ( empty( $wpssw_spreadsheetid ) ) {
				return false;
			}
			$wpssw_order_status_array = self::$wpssw_default_status_slug;
			/* Custom Order Status*/
			$wpssw_status_array             = wc_get_order_statuses();
			$wpssw_status_array['wc-trash'] = 'Trash';
			if ( 'yes' === (string) parent::wpssw_option( 'all_orders' ) ) {
				$wpssw_status_array['wc-allorders'] = 'All Orders';
			}
			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				if ( ! in_array( $wpssw_key, $wpssw_order_status_array, true ) ) {
					$wpssw_order_status_array[]              = $wpssw_key;
					$wpssw_custom_order_status[ $wpssw_key ] = $wpssw_val;
				}
			}
			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_headers             = stripslashes_deep( parent::wpssw_option( 'sheet_headers' ) );
			$wpssw_headers_custom      = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list_custom', array() ) );
			if ( ! empty( $wpssw_headers_custom ) ) {
				$wpssw_headers = $wpssw_headers_custom;
			}
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				$wpssw_headers = array_merge( $wpssw_headers, $wpssw_woo_event_headers );
			}
			array_unshift( $wpssw_headers, 'Order Id' );
			$wpssw_value                    = array( $wpssw_headers );
			$wpssw_order_status_array_count = count( $wpssw_order_status_array );
			for ( $i = 0; $i < $wpssw_order_status_array_count; $i++ ) {
				$i               = (int) $i;
				$wpssw_sheetname = '';
				if ( ( 0 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'pending_orders' ) ) ) {
					$wpssw_sheetname = 'Pending Orders';
				} elseif ( ( 1 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'processing_orders' ) ) ) {
					$wpssw_sheetname = 'Processing Orders';
				} elseif ( ( 2 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'on_hold_orders' ) ) ) {
					$wpssw_sheetname = 'On Hold Orders';
				} elseif ( ( 3 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'completed_orders' ) ) ) {
					$wpssw_sheetname = 'Completed Orders';
				} elseif ( ( 4 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'cancelled_orders' ) ) ) {
					$wpssw_sheetname = 'Cancelled Orders';
				} elseif ( ( 5 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'refunded_orders' ) ) ) {
					$wpssw_sheetname = 'Refunded Orders';
				} elseif ( ( 6 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'failed_orders' ) ) ) {
					$wpssw_sheetname = 'Failed Orders';
				}
				if ( $i > 6 ) {
					$wpssw_status = substr( $wpssw_order_status_array[ $i ], strpos( $wpssw_order_status_array[ $i ], '-' ) + 1 );
					if ( 'yes' === (string) parent::wpssw_option( $wpssw_status ) ) {
						$wpssw_sheetname = $wpssw_custom_order_status[ $wpssw_order_status_array[ $i ] ] . ' Orders';
					}
				}
				if ( 'wc-trash' === (string) $wpssw_order_status_array[ $i ] ) {
					$wpssw_order_status_array[ $i ] = 'trash';
				}
				if ( 'wc-allorders' === (string) $wpssw_order_status_array[ $i ] ) {
					$wpssw_sheetname = 'All Orders';
				}
				if ( ! empty( $wpssw_sheetname ) ) {
					$wpssw_activesheets[ $wpssw_order_status_array[ $i ] ] = $wpssw_sheetname;
					/*Create new sheet into spreadsheet*/
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['sheetname']     = $wpssw_sheetname;
					$wpssw_response         = self::$instance_api->newsheetobject( $param );
					$wpssw_range            = trim( $wpssw_sheetname ) . '!A1';
					$wpssw_requestbody      = self::$instance_api->valuerangeobject( $wpssw_value );
					$wpssw_params           = array( 'valueInputOption' => $wpssw_inputoption );
					$param                  = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response         = self::$instance_api->appendentry( $param );
				}
			}
			/*Delete Default sheet*/
			$param                  = array();
			$param['spreadsheetid'] = $wpssw_spreadsheetid;
			$wpssw_response         = self::$instance_api->deletesheetobject( $param );
			$wpssw_dataarray        = array();
			$wpssw_isexecute        = 0;
			foreach ( $wpssw_activesheets as $wpssw_sheet_slug => $wpssw_sheetname ) {
				if ( 'All Orders' === (string) $wpssw_sheetname ) {
					$wpssw_query_args = array(
						'post_type'      => 'shop_order',
						'posts_per_page' => -1,
						'order'          => 'ASC',
						'post_status'    => array_keys( wc_get_order_statuses() ),
					);
				} else {
					$wpssw_query_args = array(
						'post_type'      => 'shop_order',
						'post_status'    => $wpssw_sheet_slug,
						'posts_per_page' => -1,
						'order'          => 'ASC',
					);
				}
				$wpssw_all_orders = get_posts( $wpssw_query_args );
				if ( empty( $wpssw_all_orders ) ) {
					continue;
				}
				$wpssw_values_array = array();
				foreach ( $wpssw_all_orders as $wpssw_order ) {
					set_time_limit( 999 );
					$wpssw_order = wc_get_order( $wpssw_order->ID );
					if ( 'yes' === (string) $iscategory_enable ) {
						$wpssw_flag  = 'exclude';
						$wpssw_items = $wpssw_order->get_items();
						foreach ( $wpssw_items as $item ) {
							$terms = get_the_terms( $item->get_product_id(), 'product_cat' );
							foreach ( $terms as $term ) {
								if ( in_array( (int) $term->term_id, parent::wpssw_convert_int( $wpssw_category_ids ), true ) ) {
									$wpssw_flag = 'include';
									break;
								}
							}
						}
						if ( 'exclude' === (string) $wpssw_flag ) {
							continue;
						}
					}
					if ( 'no' === (string) $wpssw_exportall ) {
						$wpssw_orderdate = new DateTime( $wpssw_order->get_date_created()->format( 'Y-m-d' ) );
						$wpssw_datefrom  = new DateTime( $wpssw_fromdate );
						$wpssw_dateto    = new DateTime( $wpssw_todate );
						if ( $wpssw_orderdate < $wpssw_datefrom || $wpssw_orderdate > $wpssw_dateto ) {
							continue;
						}
					}
					$wpssw_order_data   = $wpssw_order->get_data();
					$wpssw_status       = $wpssw_order_data['status'];
					$wpssw_value        = self::wpssw_make_value_array( 'insert', $wpssw_order->get_id() );
					$wpssw_values_array = array_merge( $wpssw_values_array, $wpssw_value );
				}
				if ( ! empty( $wpssw_values_array ) ) {
					try {
						$wpssw_newarray = array();
						foreach ( $wpssw_values_array as $key => $rowvalue ) {
							$wpssw_newarray[] = array_map( 'trim', $rowvalue );
						}
						$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_newarray );
						$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
						$param             = array();
						$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_sheetname, $wpssw_requestbody, $wpssw_params );
						$wpssw_response    = self::$instance_api->appendentry( $param );
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
			}
			$wpssw_resultdata                  = array();
			$wpssw_resultdata['result']        = 'successful';
			$wpssw_resultdata['spreadsheetid'] = $wpssw_spreadsheetid;
			echo wp_json_encode( $wpssw_resultdata );
			die;
		}
		/**
		 * Get list of all products
		 */
		public static function wpssw_get_product_list() {
			$flag = 1;
			return self::wpssw_woocommerce_admin_field_product_headers( $flag );
		}
		/**
		 * Get list of all products category
		 */
		public static function wpssw_get_category_list() {
			$flag = 1;
			return self::wpssw_woocommerce_admin_field_product_category_as_order_filter( $flag );
		}
		/**
		 * Get orders count for syncronization
		 *
		 * @param bool $wpssw_getfirst .
		 */
		public static function wpssw_get_orders_count( $wpssw_getfirst = false ) {
			if ( ! $wpssw_getfirst ) {
				if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
					echo 'error';
					die();
				}
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$spreadsheets_list   = self::$instance_api->get_spreadsheet_listing();
			if ( ! empty( $wpssw_spreadsheetid ) && ! array_key_exists( $wpssw_spreadsheetid, $spreadsheets_list ) ) {
				echo 'spreadsheetnotexist';
				die;
			}
			$wpssw_fromdate           = isset( $_POST['sync_all_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all_fromdate'] ) ) : '';
			$wpssw_todate             = isset( $_POST['sync_all_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all_todate'] ) ) : '';
			$wpssw_syncall            = isset( $_POST['sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all'] ) ) : '';
			$wpssw_order_status_array = self::$wpssw_default_status_slug;
			/* Custom Order Status*/
			$wpssw_status_array             = wc_get_order_statuses();
			$wpssw_status_array['wc-trash'] = 'Trash';
			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				if ( ! in_array( $wpssw_key, $wpssw_order_status_array, true ) ) {
					$wpssw_order_status_array[]              = $wpssw_key;
					$wpssw_custom_order_status[ $wpssw_key ] = $wpssw_val;
				}
			}
			$wpssw_existingsheetsnames      = array();
			$wpssw_response                 = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames      = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheetsnames      = array_flip( $wpssw_existingsheetsnames );
			$wpssw_order_status_array_count = count( $wpssw_order_status_array );
			for ( $i = 0; $i < $wpssw_order_status_array_count; $i++ ) {
				$i               = (int) $i;
				$wpssw_sheetname = '';
				if ( ( 0 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'pending_orders' ) ) ) {
					$wpssw_sheetname = 'Pending Orders';
				} elseif ( ( 1 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'processing_orders' ) ) ) {
					$wpssw_sheetname = 'Processing Orders';
				} elseif ( ( 2 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'on_hold_orders' ) ) ) {
					$wpssw_sheetname = 'On Hold Orders';
				} elseif ( ( 3 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'completed_orders' ) ) ) {
					$wpssw_sheetname = 'Completed Orders';
				} elseif ( ( 4 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'cancelled_orders' ) ) ) {
					$wpssw_sheetname = 'Cancelled Orders';
				} elseif ( ( 5 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'refunded_orders' ) ) ) {
					$wpssw_sheetname = 'Refunded Orders';
				} elseif ( ( 6 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'failed_orders' ) ) ) {
					$wpssw_sheetname = 'Failed Orders';
				}
				if ( $i > 6 ) {
					$wpssw_status = substr( $wpssw_order_status_array[ $i ], strpos( $wpssw_order_status_array[ $i ], '-' ) + 1 );
					if ( 'yes' === (string) parent::wpssw_option( $wpssw_status ) ) {
						$wpssw_sheetname = $wpssw_custom_order_status[ $wpssw_order_status_array[ $i ] ] . ' Orders';
					}
				}
				if ( 'wc-trash' === (string) $wpssw_order_status_array[ $i ] ) {
					$wpssw_order_status_array[ $i ] = 'trash';
				}
				if ( ! empty( $wpssw_sheetname ) ) {
					if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
						echo 'sheetnotexist';
						die;
					}
					$wpssw_activesheets[ $wpssw_order_status_array[ $i ] ] = $wpssw_sheetname;
					$wpssw_getactivesheets[]                               = "'" . $wpssw_sheetname . "'!A:A";
				}
			}
			if ( 'yes' === (string) parent::wpssw_option( 'all_orders' ) ) {
				if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, 'All Orders' ) ) {
					echo 'sheetnotexist';
					die;
				}
				$wpssw_getactivesheets[]         = "'All Orders'!A:A";
				$wpssw_activesheets['all_order'] = 'All Orders';
			}
			/*Get First Column Value from all sheets*/
			try {
				$param                  = array();
				$param['spreadsheetid'] = $wpssw_spreadsheetid;
				$param['ranges']        = array( 'ranges' => $wpssw_getactivesheets );
				$wpssw_response         = self::$instance_api->getbatchvalues( $param );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
			$wpssw_existingorders = array();
			foreach ( $wpssw_response->getValueRanges() as $wpssw_order ) {
				$wpssw_rangetitle                          = explode( "'!A", $wpssw_order->range );
				$wpssw_sheettitle                          = str_replace( "'", '', $wpssw_rangetitle[0] );
				$wpssw_data                                = array_map(
					function( $wpssw_element ) {
						if ( isset( $wpssw_element['0'] ) ) {
							return $wpssw_element['0'];
						} else {
							return '';
						}
					},
					$wpssw_order->values
				);
				$wpssw_existingorders[ $wpssw_sheettitle ] = $wpssw_data;
			}
			if ( $wpssw_getfirst ) {
				return $wpssw_existingorders;
			}
			$wpssw_dataarray = array();
			$wpssw_isexecute = 0;
			$response        = array();
			foreach ( $wpssw_activesheets as $wpssw_sheet_slug => $wpssw_sheetname ) {
				if ( 'All Orders' === (string) $wpssw_sheetname ) {
					if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) ) {
						$wpssw_query_args = array(
							'post_type'      => 'shop_order',
							'posts_per_page' => -1,
							'order'          => 'ASC',
							'post_status'    => array_keys( wc_get_order_statuses() ),
							'date_query'     => array(
								array(
									'after'     => $wpssw_fromdate,
									'before'    => $wpssw_todate,
									'inclusive' => true,
								),
							),
						);
					} else {
						$wpssw_query_args = array(
							'post_type'      => 'shop_order',
							'posts_per_page' => -1,
							'order'          => 'ASC',
							'post_status'    => array_keys( wc_get_order_statuses() ),
						);
					}
				} else {
					if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) ) {
						$wpssw_query_args = array(
							'post_type'      => 'shop_order',
							'posts_per_page' => -1,
							'order'          => 'ASC',
							'post_status'    => $wpssw_sheet_slug,
							'date_query'     => array(
								array(
									'after'     => $wpssw_fromdate,
									'before'    => $wpssw_todate,
									'inclusive' => true,
								),
							),
						);
					} else {
						$wpssw_query_args = array(
							'post_type'      => 'shop_order',
							'post_status'    => $wpssw_sheet_slug,
							'posts_per_page' => -1,
							'order'          => 'ASC',
						);
					}
				}
				$wpsswcustom_query = new WP_Query( $wpssw_query_args );
				$wpssw_all_orders  = $wpsswcustom_query->posts;
				if ( empty( $wpssw_all_orders ) ) {
					continue;
				}
				$wpssw_values_array = array();
				$ordercount         = 0;
				foreach ( $wpssw_all_orders as $wpssw_order ) {
					if ( in_array( (int) $wpssw_order->ID, parent::wpssw_convert_int( $wpssw_existingorders[ $wpssw_sheetname ] ), true ) ) {
						continue;
					}
					if ( self::wpssw_check_product_category( $wpssw_order->ID ) ) {
						continue;
					}
					$wpssw_order = wc_get_order( $wpssw_order->ID );
					if ( 'false' === (string) $wpssw_syncall ) {
						$wpssw_orderdate = new DateTime( $wpssw_order->get_date_created()->format( 'Y-m-d' ) );
						$wpssw_datefrom  = new DateTime( $wpssw_fromdate );
						$wpssw_dateto    = new DateTime( $wpssw_todate );
						if ( $wpssw_orderdate < $wpssw_datefrom || $wpssw_orderdate > $wpssw_dateto ) {
							continue;
						}
					}
					$ordercount++;
				}
				if ( $ordercount > 0 ) {
					$response[] = array(
						'sheet_name'  => $wpssw_sheetname,
						'sheet_slug'  => $wpssw_sheet_slug,
						'totalorders' => $ordercount,
					);
				}
			}
			echo wp_json_encode( $response );
			die;
		}
		/**
		 * Syncronize order data sheetwise
		 */
		public static function wpssw_sync_sheetswise() {
			if ( ! isset( $_POST['sync_nonce_token'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sync_nonce_token'] ) ), 'sync_nonce' )
			) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_inputoption   = parent::wpssw_option( 'wpssw_inputoption' );
			$wpssw_fromdate      = isset( $_POST['sync_all_fromdate'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all_fromdate'] ) ) : '';
			$wpssw_todate        = isset( $_POST['sync_all_todate'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all_todate'] ) ) : '';
			$wpssw_syncall       = isset( $_POST['sync_all'] ) ? sanitize_text_field( wp_unslash( $_POST['sync_all'] ) ) : '';
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_sheet_slug = isset( $_POST['sheetslug'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetslug'] ) ) : '';
			$wpssw_sheetname  = isset( $_POST['sheetname'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetname'] ) ) : '';
			$wpssw_ordercount = isset( $_POST['ordercount'] ) ? sanitize_text_field( wp_unslash( $_POST['ordercount'] ) ) : '';
			$wpssw_orderlimit = isset( $_POST['orderlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['orderlimit'] ) ) : '';
			$order_ascdesc    = parent::wpssw_option( 'wpssw_order_ascdesc' );
			if ( 'all_order' === (string) $wpssw_sheet_slug ) {
				if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) && ! empty( $wpssw_fromdate ) && ! empty( $wpssw_todate ) ) {
					$wpssw_query_args = array(
						'post_type'      => 'shop_order',
						'posts_per_page' => -1,
						'order'          => 'ASC',
						'post_status'    => array_keys( wc_get_order_statuses() ),
						'date_query'     => array(
							array(
								'after'     => $wpssw_fromdate,
								'before'    => $wpssw_todate,
								'inclusive' => true,
							),
						),
					);
				} else {
					$wpssw_query_args = array(
						'post_type'      => 'shop_order',
						'posts_per_page' => -1,
						'order'          => 'ASC',
						'post_status'    => array_keys( wc_get_order_statuses() ),
					);
				}
			} else {
				if ( isset( $wpssw_fromdate ) && isset( $wpssw_todate ) && ! empty( $wpssw_fromdate ) && ! empty( $wpssw_todate ) ) {
					$wpssw_query_args = array(
						'post_type'      => 'shop_order',
						'posts_per_page' => -1,
						'order'          => 'ASC',
						'post_status'    => $wpssw_sheet_slug,
						'date_query'     => array(
							array(
								'after'     => $wpssw_fromdate,
								'before'    => $wpssw_todate,
								'inclusive' => true,
							),
						),
					);
				} else {
					$wpssw_query_args = array(
						'post_type'      => 'shop_order',
						'post_status'    => $wpssw_sheet_slug,
						'posts_per_page' => -1,
						'order'          => 'ASC',
					);
				}
			}
			if ( 'descorder' === (string) $order_ascdesc ) {
				$wpssw_query_args['order'] = 'DESC';
			}
			$wpsswcustom_query = new WP_Query( $wpssw_query_args );
			$wpssw_all_orders  = $wpsswcustom_query->posts;
			if ( empty( $wpssw_all_orders ) ) {
				die();
			}
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
			$wpssw_sheet               = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry            = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
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
			$wpssw_values_array        = array();
			$neworder                  = 0;
			foreach ( $wpssw_all_orders as $wpssw_order ) {
				if ( in_array( (int) $wpssw_order->ID, parent::wpssw_convert_int( $wpssw_data ), true ) ) {
					continue;
				}
				if ( self::wpssw_check_product_category( $wpssw_order->ID ) ) {
					continue;
				}
				$wpssw_order = wc_get_order( $wpssw_order->ID );
				if ( 'false' === (string) $wpssw_syncall ) {
					$wpssw_orderdate = new DateTime( $wpssw_order->get_date_created()->format( 'Y-m-d' ) );
					$wpssw_datefrom  = new DateTime( $wpssw_fromdate );
					$wpssw_dateto    = new DateTime( $wpssw_todate );
					if ( $wpssw_orderdate < $wpssw_datefrom || $wpssw_orderdate > $wpssw_dateto ) {
						continue;
					}
				}
				if ( $neworder < $wpssw_orderlimit ) {
					set_time_limit( 999 );
					$wpssw_order_data   = $wpssw_order->get_data();
					$wpssw_status       = $wpssw_order_data['status'];
					$wpssw_value        = self::wpssw_make_value_array( 'insert', $wpssw_order->get_id() );
					$wpssw_values_array = array_merge( $wpssw_values_array, $wpssw_value );
					$neworder++;
				}
			}
				$rangetofind = $wpssw_sheetname . '!A:A';
			if ( ! empty( $wpssw_values_array ) ) {
				try {
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetofind, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->appendentry( $param );
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			echo 'successful';
			die;
		}
		/**
		 * Regenerate Graph
		 */
		public static function wpssw_regenerate_graph() {
			if ( ! isset( $_POST['sync_nonce_token'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sync_nonce_token'] ) ), 'sync_nonce' )
			) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			$wpssw_sheet          = isset( $_POST['sheet_id'] ) ? sanitize_text_field( wp_unslash( $_POST['sheet_id'] ) ) : '';
			$graph_type           = isset( $_POST['graph_type'] ) ? sanitize_text_field( wp_unslash( $_POST['graph_type'] ) ) : '';
			$wpssw_graphsheetname = '';
			if ( 'regenerate_total_graph' === (string) $wpssw_sheet ) {
				$wpssw_graphsheetname = 'Total Orders Graph';
			}
			if ( 'regenerate_sales_graph' === (string) $wpssw_sheet ) {
				$wpssw_graphsheetname = 'Sales Orders Graph';
			}
			if ( 'regenerate_product_graph' === (string) $wpssw_sheet ) {
				$wpssw_graphsheetname = 'Products Sold Graph';
			}
			if ( 'regenerate_customers_graph' === (string) $wpssw_sheet ) {
				$wpssw_graphsheetname = 'Total Customers Graph';
			}
			if ( 'regenerate_used_coupons_graph' === (string) $wpssw_sheet ) {
				$wpssw_graphsheetname = 'Total Used Coupons Graph';
			}
			try {
				$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
				$spreadsheets_list   = self::$instance_api->get_spreadsheet_listing();
				if ( ! empty( $wpssw_spreadsheetid ) && ! array_key_exists( $wpssw_spreadsheetid, $spreadsheets_list ) ) {
					echo 'spreadsheetnotexist';
					die;
				} elseif ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_graphsheetname ) ) {
					echo 'sheetnotexist';
					die;
				}
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = array();
				$wpssw_charts_data         = array();
				foreach ( $wpssw_response->getSheets() as $wpssw_key => $wpssw_value ) {
					$wpssw_existingsheetsnames[ $wpssw_value['properties']['title'] ] = $wpssw_value['properties']['sheetId'];
					if ( $wpssw_value['charts'] ) {
						$wpssw_charts_data[ $wpssw_value['properties']['title'] ] = $wpssw_value['charts'];
					}
				}
				$wpssw_chartid       = array();
				$wpssw_graph_sheetid = $wpssw_existingsheetsnames[ $wpssw_graphsheetname ];
				if ( isset( $wpssw_charts_data[ $wpssw_graphsheetname ] ) && $wpssw_charts_data[ $wpssw_graphsheetname ] ) {
					$wpssw_chart_data = $wpssw_charts_data[ $wpssw_graphsheetname ];
					foreach ( $wpssw_chart_data as $chart_data ) {
						$chart_data_array = (array) $chart_data;
						$wpssw_chartid[]  = $chart_data_array['chartId'];
					}
				}
				$total_headers = 2;
				$last_column   = parent::wpssw_get_column_index( $total_headers );
				$requestbody   = self::$instance_api->clearobject();
				if ( isset( $wpssw_existingsheetsnames[ $wpssw_graphsheetname ] ) ) {
					try {
						$range                  = $wpssw_graphsheetname . '!A1:' . $last_column . '10000';
						$param                  = array();
						$param['spreadsheetid'] = $wpssw_spreadsheetid;
						$param['sheetname']     = $range;
						$param['requestbody']   = $requestbody;
						$response               = self::$instance_api->clear( $param );
						if ( ! empty( $wpssw_chartid ) ) {
							foreach ( $wpssw_chartid as $chart_id ) {
								$param                  = array();
								$param['spreadsheetid'] = $wpssw_spreadsheetid;
								$param['chart_ID']      = $chart_id;
								$wpssw_response         = self::$instance_api->deleteembeddedobject( $param );
							}
						}
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
				self::wpssw_add_graph( $wpssw_graphsheetname, $graph_type );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
			echo 'successful';
			die();
		}
		/**
		 * Insert event data into sheet provided by $wpssw_sheetname
		 *
		 * @param int    $wpssw_orderid .
		 * @param string $wpssw_sheetname .
		 */
		public static function wpssw_insert_event_data_into_sheet( $wpssw_orderid, $wpssw_sheetname ) {
			try {
				if ( ! self::$instance_api->checkcredenatials() ) {
					return;
				}
				if ( ! $wpssw_orderid ) {
					return;
				}
				if ( ! empty( $wpssw_sheetname ) ) {
						$wpssw_order         = wc_get_order( $wpssw_orderid );
						$wpssw_values        = self::wpssw_make_value_array( 'insert', $wpssw_order->get_id() );
						$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_event_spreadsheet_id' );
						$wpssw_header_type   = self::wpssw_is_productwise();
						$wpssw_headers_name  = parent::wpssw_option( 'wpssw_sheet_headers_list' );
					if ( parent::wpssw_is_event_calender_ticket_active() ) {
						$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
						if ( ! is_array( $wpssw_woo_event_headers ) ) {
							$wpssw_woo_event_headers = array();
						}
						$wpssw_headers_name = array_merge( $wpssw_headers_name, $wpssw_woo_event_headers );
					}
						$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
						$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
						$wpssw_existingsheets      = array_flip( $wpssw_existingsheetsnames );

						$wpssw_sheetid     = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
						$wpssw_rangetofind = $wpssw_sheetname . '!A:A';
						$wpssw_allentry    = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
						$wpssw_data        = $wpssw_allentry->getValues();
						$wpssw_data        = array_map(
							function( $element ) {
								if ( isset( $element['0'] ) ) {
									return $element['0'];
								} else {
									return '';
								}
							},
							$wpssw_data
						);
						$wpssw_num         = array_search( (int) $wpssw_order->get_id(), parent::wpssw_convert_int( $wpssw_data ), true );

					if ( $wpssw_num > 0 ) {
						$wpssw_rownum = $wpssw_num + 1;
						// Add or Remove Row at spreadsheet.
						$wpssw_ordrow   = 0;
						$wpssw_notempty = 0;
						end( $wpssw_data );
						$wpssw_lastelement = key( $wpssw_data );
						reset( $wpssw_data );

						$wpssw_data_count = count( $wpssw_data );
						for ( $i = $wpssw_rownum; $i < $wpssw_data_count; $i++ ) {
							if ( (int) $wpssw_data[ $i ] === (int) $wpssw_order->get_id() ) {
								$wpssw_ordrow++;
								if ( (int) $wpssw_lastelement === (int) $i ) {
									$wpssw_ordrow++;
								}
							} else {
								if ( (int) $wpssw_lastelement === (int) $i ) {
									$wpssw_notempty = 1;
									if ( $wpssw_ordrow > 0 ) {
										$wpssw_ordrow++;
									}
								} else {
									$wpssw_ordrow++;
								}
								break;
							}
						}
						$wpssw_samerow = 0;
						if ( 0 === (int) $wpssw_ordrow ) {
							$wpssw_samerow = 1;
						}
						if ( 1 === (int) $wpssw_samerow && $wpssw_header_type && 0 === (int) $wpssw_notempty ) {

							$wpssw_alphabet   = range( 'A', 'Z' );
							$wpssw_alphaindex = '';
							$wpssw_is_id      = array_search( 'Product ID', $wpssw_headers_name, true );
							if ( $wpssw_is_id ) {
								$wpssw_alphaindex = $wpssw_alphabet[ $wpssw_is_id + 1 ];
							} else {
								$wpssw_is_name = array_search( 'Product Name', $wpssw_headers_name, true );
								if ( $wpssw_is_name ) {
									$wpssw_alphaindex = $wpssw_alphabet[ $wpssw_is_name + 1 ];
								}
							}
							if ( '' !== (string) $wpssw_alphaindex ) {
								$wpssw_rangetofind = $wpssw_sheetname . '!' . $wpssw_alphaindex . $wpssw_rownum . ':' . $wpssw_alphaindex;
								$wpssw_allentry    = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
								$wpssw_data        = $wpssw_allentry->getValues();
								$wpssw_data        = array_map(
									function( $wpssw_element ) {
										if ( isset( $wpssw_element['0'] ) ) {
											return $wpssw_element['0'];
										} else {
											return '';
										}
									},
									$wpssw_data
								);
								if ( ( count( $wpssw_values ) < count( $wpssw_data ) ) ) {
									$wpssw_ordrow  = count( $wpssw_data );
									$wpssw_samerow = 0;
								}
							}
						}
						if ( 1 === (int) $wpssw_notempty && 0 === (int) $wpssw_ordrow ) {
							$wpssw_samerow = 0;
							$wpssw_ordrow  = 1;
						}
						if ( ( count( $wpssw_values ) > (int) $wpssw_ordrow ) && 0 === (int) $wpssw_samerow ) {// Insert blank row into spreadsheet.

							$wpssw_endindex                 = count( $wpssw_values ) - (int) $wpssw_ordrow;
							$wpssw_endindex                 = (int) $wpssw_endindex + (int) $wpssw_rownum;
							$wpssw_param                    = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_rownum, $wpssw_endindex );
							$wpssw_batchupdaterequest       = self::$instance_api->insertdimensionobject( $wpssw_param );
							$requestobject                  = array();
							$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
							$requestobject['requestbody']   = $wpssw_batchupdaterequest;
							$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
						} elseif ( count( $wpssw_values ) < (int) $wpssw_ordrow && 0 === (int) $wpssw_samerow ) {// Remove extra row from spreadhseet.

							$wpssw_endindex         = (int) $wpssw_ordrow - count( $wpssw_values );
							$wpssw_endindex         = (int) $wpssw_endindex + (int) $wpssw_rownum;
							$param                  = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_rownum, $wpssw_endindex );
							$deleterequest          = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
							$param                  = array();
							$param['spreadsheetid'] = $wpssw_spreadsheetid;
							$param['requestarray']  = $deleterequest;
							self::$instance_api->updatebachrequests( $param );
						}
						// End of add- remove row at spreadsheet.

						$wpssw_rangetoupdate1 = $wpssw_sheetname . '!A' . $wpssw_rownum;
						$wpssw_requestbody1   = self::$instance_api->valuerangeobject( $wpssw_values );
						$wpssw_inputoption    = parent::wpssw_option( 'wpssw_inputoption' );
						if ( ! $wpssw_inputoption ) {
							$wpssw_inputoption = 'USER_ENTERED';
						}
						$wpssw_params1 = array( 'valueInputOption' => $wpssw_inputoption ); // USER_ENTERED.
						$param         = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate1, $wpssw_requestbody1, $wpssw_params1 );

						$wpssw_response = self::$instance_api->updateentry( $param );
					}
				}
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}
		/**
		 * Add Graph(Chart) into sheet
		 *
		 * @param string $wpssw_graphsheetname .
		 * @param string $graph_type .
		 * @param string $wpssw_spreadsheetid .
		 */
		public static function wpssw_add_graph( $wpssw_graphsheetname = '', $graph_type = '', $wpssw_spreadsheetid = '' ) {
			$wpssw_spreadsheetid = ! empty( $wpssw_spreadsheetid ) ? $wpssw_spreadsheetid : parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_inputoption   = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_charts_data         = array();
			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_chart_sheet_months  = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
			$wpssw_query_args          = array(
				'post_type'      => 'shop_order',
				'posts_per_page' => -1,
				'order'          => 'ASC',
				'post_status'    => array_keys( wc_get_order_statuses() ),
			);
			$wpssw_all_orders          = get_posts( $wpssw_query_args );
			$months_array              = array();
			$wpssw_years               = array();
			foreach ( $wpssw_all_orders as $wpssw_order ) {
				$wpssw_order        = wc_get_order( $wpssw_order->ID );
				$wpssw_version      = '3.7.0';
				$used_coupons_count = 0;
				global $woocommerce;
				if ( version_compare( $woocommerce->version, $wpssw_version, '>=' ) ) {
					$used_coupons_count = count( $wpssw_order->get_coupon_codes() );
				} else {
					$used_coupons_count = count( $wpssw_order->get_used_coupons() );
				}
				$time  = strtotime( $wpssw_order->get_date_created() );
				$month = date_i18n( 'F', $time );
				if ( 'Total Used Coupons Graph' === (string) $wpssw_graphsheetname ) {
					if ( $used_coupons_count > 0 ) {
						$wpssw_years[] = (int) date_i18n( 'Y', $time );
					}
				} elseif ( 'Total Customers Graph' === (string) $wpssw_graphsheetname ) {
					$args            = array(
						'role'    => 'customer',
						'orderby' => 'ID',
						'order'   => 'ASC',
					);
					$wpssw_customers = get_users( $args );
					if ( ! empty( $wpssw_customers ) && count( $wpssw_customers ) > 0 ) {
						$wpssw_years[] = (int) date_i18n( 'Y', $time );
					}
				} else {
					$wpssw_years[] = (int) date_i18n( 'Y', $time );
				}
			}
			if ( empty( $wpssw_years ) ) {
				$wpssw_year_range = array();
			} else {
				$year_from        = min( $wpssw_years );
				$year_to          = max( $wpssw_years );
				$wpssw_year_range = array();
				if ( $year_from === $year_to ) {
					$wpssw_year_range = array( $year_from );
				} else {
					$wpssw_year_range = array();
					$range            = $year_to - $year_from;
					for ( $i = 0;$i <= $range;$i++ ) {
						if ( in_array( $year_from, $wpssw_years, true ) ) {
							$wpssw_year_range[] = $year_from;
						}
						$year_from++;
					}
				}
				rsort( $wpssw_year_range );
			}
			$order_data = array();
			foreach ( $wpssw_all_orders as $wpssw_order ) {
				$wpssw_order        = wc_get_order( $wpssw_order->ID );
				$wpssw_order_data   = $wpssw_order->get_data();
				$wpssw_items        = $wpssw_order->get_items();
				$time               = strtotime( $wpssw_order->get_date_created() );
				$month              = date_i18n( 'F', $time );
				$year               = date_i18n( 'Y', $time );
				$wpssw_order_status = $wpssw_order->get_status();
				foreach ( $wpssw_year_range as $wpssw_yrange ) {
					if ( $wpssw_yrange === (int) $year ) {
						if ( 'pending' === (string) $wpssw_order_status || 'cancelled' === (string) $wpssw_order_status || 'failed' === (string) $wpssw_order_status || 'refunded' === (string) $wpssw_order_status ) {
								continue;
						}
						if ( isset( $order_data[ $year ][ $month ] ) ) {
							$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ];
						} else {
							$order_data[ $year ][ $month ] = 0;
						}
						if ( 'Sales Orders Graph' === (string) $wpssw_graphsheetname ) {
							$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ] + (int) $wpssw_order_data['total'] - (int) $wpssw_order->get_total_refunded();
						}
						if ( 'Total Orders Graph' === (string) $wpssw_graphsheetname ) {
								$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ] + 1;
						}
						if ( 'Products Sold Graph' === (string) $wpssw_graphsheetname ) {
							$wpssw_prod_qty = 0;
							foreach ( $wpssw_items as $wpssw_item ) {
								$wpssw_prod_qty = $wpssw_prod_qty + $wpssw_item['quantity'];
							}
							$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ] + (int) $wpssw_prod_qty;
						}
						if ( 'Total Used Coupons Graph' === (string) $wpssw_graphsheetname ) {
							$wpssw_version      = '3.7.0';
							$used_coupons_count = 0;
							global $woocommerce;
							if ( version_compare( $woocommerce->version, $wpssw_version, '>=' ) ) {
								$used_coupons_count = count( $wpssw_order->get_coupon_codes() );
							} else {
								$used_coupons_count = count( $wpssw_order->get_used_coupons() );
							}
							if ( $used_coupons_count > 0 ) {
								$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ] + $used_coupons_count;
							}
						}
					}
				}
			}
			if ( 'Sales Orders Graph' === (string) $wpssw_graphsheetname ) {
				$count_sales_year  = 0;
				$wpssw_year_range1 = array();
				foreach ( $wpssw_year_range as $wpssw_yrange ) {
					foreach ( $order_data[ $wpssw_yrange ] as $key => $value ) {
						$count_sales_year = $value;
					}
					if ( (int) $count_sales_year > 0 ) {
						$wpssw_year_range1[] = $wpssw_yrange;
					}
				}
				if ( $wpssw_year_range1 !== $wpssw_year_range ) {
					$wpssw_year_range = array();
					$wpssw_year_range = $wpssw_year_range1;
				}
			}
			if ( 'Total Customers Graph' === (string) $wpssw_graphsheetname ) {
					$args            = array(
						'role'    => 'customer',
						'orderby' => 'ID',
						'order'   => 'ASC',
					);
					$wpssw_customers = get_users( $args );
					$wpssw_years     = array();
					foreach ( $wpssw_customers as $customer ) {
						$time          = strtotime( $customer->user_registered );
						$month         = date_i18n( 'F', $time );
						$wpssw_years[] = (int) date_i18n( 'Y', $time );
					}
					if ( ! empty( $wpssw_years ) ) {
						$year_from        = min( $wpssw_years );
						$year_to          = max( $wpssw_years );
						$wpssw_year_range = array();
						if ( $year_from === $year_to ) {
							$wpssw_year_range = array( $year_from );
						} else {
							$wpssw_year_range = array();
							$range            = $year_to - $year_from;
							for ( $i = 0;$i <= $range;$i++ ) {
								if ( in_array( $year_from, $wpssw_years, true ) ) {
									$wpssw_year_range[] = $year_from;
								}
								$year_from++;
							}
						}
						rsort( $wpssw_year_range );
					} else {
						$wpssw_year_range = array();
					}
					$order_data = array();
					foreach ( $wpssw_customers as $customer ) {
						$time  = strtotime( $customer->user_registered );
						$month = date_i18n( 'F', $time );
						$year  = date_i18n( 'Y', $time );
						foreach ( $wpssw_year_range as $wpssw_yrange ) {
							if ( $wpssw_yrange === (int) $year ) {
								if ( isset( $order_data[ $year ][ $month ] ) ) {
									$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ];
								} else {
									$order_data[ $year ][ $month ] = 0;
								}
								$order_data[ $year ][ $month ] = $order_data[ $year ][ $month ] + 1;
							}
						}
					}
			}
			$wpssw_values       = array();
			$wpssw_values_array = array();
			foreach ( $wpssw_year_range as $wpssw_yrange ) {
				if ( 'Total Orders Graph' === (string) $wpssw_graphsheetname ) {
					$wpssw_values_array[ $wpssw_yrange ][] = array( 'Months of ' . $wpssw_yrange, 'Orders' );
					$wpssw_axisname                        = 'Orders';
					$wpssw_graphtitle                      = 'Total Orders';
					if ( empty( $graph_type ) ) {
						// phpcs:ignore.
						$graph_type = isset( $_POST['total_orders_graph_type'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['total_orders_graph_type'] ) ) ) : '';
					}
				}
				if ( 'Sales Orders Graph' === (string) $wpssw_graphsheetname ) {
					if ( empty( $graph_type ) ) {
						// phpcs:ignore.
						$graph_type = isset( $_POST['sales_orders_graph_type'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['sales_orders_graph_type'] ) ) ) : '';
					}
					$priceargs                             = wp_parse_args(
						array(),
						array(
							'ex_tax_label'       => false,
							'currency'           => '',
							'decimal_separator'  => wc_get_price_decimal_separator(),
							'thousand_separator' => wc_get_price_thousand_separator(),
							'decimals'           => wc_get_price_decimals(),
							'price_format'       => get_woocommerce_price_format(),
						)
					);
					$wpssw_axisname                        = 'Sales (' . get_woocommerce_currency_symbol( $priceargs['currency'] ) . ')';
					$wpssw_axisname                        = html_entity_decode( $wpssw_axisname );
					$wpssw_values_array[ $wpssw_yrange ][] = array( 'Months of ' . $wpssw_yrange, $wpssw_axisname );
					$wpssw_graphtitle                      = 'Total Sales';
				}
				if ( 'Products Sold Graph' === (string) $wpssw_graphsheetname ) {
					if ( empty( $graph_type ) ) {
						// phpcs:ignore.
						$graph_type = isset( $_POST['products_sold_graph_type'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['products_sold_graph_type'] ) ) ) : '';
					}
					$wpssw_values_array[ $wpssw_yrange ][] = array( 'Months of ' . $wpssw_yrange, 'Products Sold' );
					$wpssw_axisname                        = 'Products Sold';
					$wpssw_graphtitle                      = 'Total Sold Products';
				}
				if ( 'Total Customers Graph' === (string) $wpssw_graphsheetname ) {
					if ( empty( $graph_type ) ) {
						// phpcs:ignore.
						$graph_type = isset( $_POST['total_customers_graph_type'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['total_customers_graph_type'] ) ) ) : '';
					}
					$wpssw_values_array[ $wpssw_yrange ][] = array( 'Months of ' . $wpssw_yrange, 'Customers' );
					$wpssw_axisname                        = 'Customers';
					$wpssw_graphtitle                      = 'Total Customers';
				}
				if ( 'Total Used Coupons Graph' === (string) $wpssw_graphsheetname ) {
					if ( empty( $graph_type ) ) {
						// phpcs:ignore.
						$graph_type = isset( $_POST['total_used_coupons_graph_type'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_POST['total_used_coupons_graph_type'] ) ) ) : '';
					}
					$wpssw_values_array[ $wpssw_yrange ][] = array( 'Months of ' . $wpssw_yrange, 'Used Coupons' );
					$wpssw_axisname                        = 'Used Coupons';
					$wpssw_graphtitle                      = 'Total Used Coupons';
				}
				foreach ( $wpssw_chart_sheet_months as $m1 ) {
					$wpssw_values   = array();
					$wpssw_values[] = $m1;
					if ( array_key_exists( $m1, $order_data[ $wpssw_yrange ] ) ) {
						$wpssw_values[] = $order_data[ $wpssw_yrange ][ $m1 ];
					} else {
						$wpssw_values[] = 0;
					}
					$wpssw_values_array[ $wpssw_yrange ][] = $wpssw_values;
				}
			}
			if ( count( $wpssw_year_range ) > 0 ) {
				$yeardata_index = 25 * count( $wpssw_year_range ) + 2;
			}
			foreach ( $wpssw_year_range as $yrange ) {
				$wpssw_range       = trim( $wpssw_graphsheetname ) . '!A' . $yeardata_index;
				$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array[ $yrange ] );
				$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
				$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
				$wpssw_response1   = self::$instance_api->appendentry( $param );
				$yeardata_index    = $yeardata_index + count( $wpssw_values_array[ $yrange ] ) + 2;
			}
			$wpssw_graph_sheetid = $wpssw_existingsheetsnames[ $wpssw_graphsheetname ];
			$wpssw_sheet         = "'" . $wpssw_graphsheetname . "'!A:A";
			$wpssw_allentry      = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data          = $wpssw_allentry->getValues();
			if ( ! empty( $wpssw_data ) ) {
				foreach ( $wpssw_data as $key => $value ) {
					if ( ! isset( $value['0'] ) ) {
						unset( $wpssw_data[ $key ] );
					}
				}
				$endcolumnindex_domain  = 2;
				$overlayposition_row    = 2;
				$wpssw_year_range_count = count( $wpssw_year_range );
				for ( $i = 0;$i < $wpssw_year_range_count;$i++ ) {
					foreach ( $wpssw_data as $key => $value ) {
						if ( in_array( 'Months of ' . $wpssw_year_range[ $i ], $value, true ) ) {
							$startrowindex_domain = (int) $key;
						}
					}
					$endrowindex_domain = $startrowindex_domain + 13;
					try {
						$param                        = array();
						$param['graph_title']         = $wpssw_graphtitle . ' of Year ' . $wpssw_year_range[ $i ];
						$param['graph_type']          = $graph_type;
						$param['bottom_axisname']     = 'Months';
						$param['left_axisname']       = $wpssw_axisname;
						$param['graph_sheetID']       = $wpssw_graph_sheetid;
						$param['startRowIndex']       = $startrowindex_domain;
						$param['endRowIndex']         = $endrowindex_domain;
						$param['endColumnIndex']      = $endcolumnindex_domain;
						$param['row_overlayPosition'] = $overlayposition_row;
						$param['spreadsheetid']       = $wpssw_spreadsheetid;
						$wpssw_response               = self::$instance_api->addchartobject( $param );
						$overlayposition_row          = $overlayposition_row + 25;
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
			} else {
				$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}
				$wpssw_range       = trim( $wpssw_graphsheetname ) . '!A1';
				$wpssw_array       = array();
				$wpssw_array []    = 'There is no data to generate the graph';
				$wpssw_requestbody = self::$instance_api->valuerangeobject( array( $wpssw_array ) );
				$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
				$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
				$wpssw_response    = self::$instance_api->appendentry( $param );
			}
		}
		/**
		 * Add Sync button in meta box.
		 */
		public static function wpssw_syncbtn_meta_box_content() {
			echo '<a href="#" id="wpssw_metabox_sync_btn" class="button wpssw_single_order_sync_btn">' . esc_html__( 'Click to Sync', 'wpssw' ) . '</a>
					<img src="' . esc_url( admin_url( 'images/spinner.gif' ) ) . '" id="wpssw_metabox_syncbtnloader" class="syncbtnloader">';
		}
		/**
		 * Check which header type is selected productwise or orderwise.
		 */
		public static function wpssw_is_productwise() {
			$wpssw_header_type = parent::wpssw_option( 'wpssw_header_format' );
			if ( 'productwise' === (string) $wpssw_header_type ) {
				return true;
			}
			return false;
		}
		/**
		 * Get order items meta
		 *
		 * @param object $wpssw_item .
		 */
		public static function wpssw_getItemmeta( $wpssw_item = '' ) {
			$wpssw_meta_html = '';
			if ( ! empty( $wpssw_item ) ) {
				$wpssw_variationame = '';
				if ( $wpssw_item->get_variation_id() ) {
					$wpssw_variation    = wc_get_product( $wpssw_item->get_variation_id() );
					$wpssw_variationame = wp_strip_all_tags( $wpssw_variation->get_formatted_name() );
				}
				$wpssw_meta_html .= 'Variation Name: ' . $wpssw_variationame . '(' . $wpssw_item->get_variation_id() . ') ,'; // the Variation id.
				if ( $wpssw_item->get_tax_class() ) {
					$wpssw_meta_html .= 'Tax Class:' . $wpssw_item->get_tax_class() . ',';
				}
				$wpssw_meta_html .= 'Line subtotal:' . $wpssw_item->get_subtotal() . ','; // Line subtotal (non discounted).
				if ( $wpssw_item->get_subtotal_tax() ) {
					$wpssw_meta_html .= 'Line subtotal tax:' . $wpssw_item->get_subtotal_tax() . ','; // Line subtotal tax (non discounted).
				}
				$wpssw_meta_html .= 'Line total:' . $wpssw_item->get_total() . ','; // Line total (discounted).
				if ( $wpssw_item->get_total_tax() ) {
					$wpssw_meta_html .= 'Line total tax:' . $wpssw_item->get_total_tax(); // Line total tax (discounted).
				}
			}
			$wpssw_meta_html = rtrim( $wpssw_meta_html, ',' );
			return $wpssw_meta_html;
		}
		/**
		 * Clean Order data array.
		 *
		 * @param array $wpssw_array Order data array.
		 * @return array $wpssw_array
		 */
		public static function wpssw_order_clean_array( $wpssw_array ) {
			$wpssw_max = count( parent::wpssw_option( 'wpssw_sheet_headers_list' ) ) + 1;
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				$wpssw_max = count( $wpssw_woo_event_headers ) + $wpssw_max;
			}
			$wpssw_array = parent::wpssw_cleanarray( $wpssw_array, $wpssw_max );
			return $wpssw_array;
		}
		/**
		 * Get all order notes of given order id.
		 *
		 * @param int $order_id .
		 * @return array
		 */
		public static function wpssw_get_all_order_notes( $order_id ) {
			$order_notes = array();
			$args        = array(
				'post_id' => $order_id,
				'orderby' => 'comment_ID',
				'order'   => 'DESC',
				'approve' => 'approve',
				'type'    => 'order_note',
			);
			remove_filter(
				'comments_clauses',
				array(
					'WC_Comments',
					'exclude_order_comments',
				),
				10,
				1
			);
			$notes = get_comments( $args );
			if ( $notes ) {
				foreach ( $notes as $note ) {
					$order_notes[] = wp_kses_post( $note->comment_content );
				}
			}
			return $order_notes;
		}
		/**
		 * Format the price values on selecting Price Format option
		 *
		 * @param int|float $price .
		 */
		public static function wpssw_get_formatted_values( $price ) {
			$wpssw_price_format = parent::wpssw_option( 'wpssw_price_format' );
			if ( ! $wpssw_price_format ) {
				$wpssw_price_format = 'plain';
			}
			$wpssw_plain     = '';
			$wpssw_formatted = '';
			if ( 'plain' === (string) $wpssw_price_format || (int) $price < 1 ) {
				return $price;
			}
			$priceargs         = wp_parse_args(
				array(),
				array(
					'ex_tax_label'       => false,
					'currency'           => '',
					'decimal_separator'  => wc_get_price_decimal_separator(),
					'thousand_separator' => wc_get_price_thousand_separator(),
					'decimals'           => wc_get_price_decimals(),
					'price_format'       => get_woocommerce_price_format(),
				)
			);
			$unformatted_price = $price;
			$negative          = $price < 0;
			$price             = floatval( $negative ? (int) $price * -1 : $price );
			$price             = number_format( $price, $priceargs['decimals'], $priceargs['decimal_separator'], $priceargs['thousand_separator'] );
			$formatted_price   = ( $negative ? '-' : '' ) . sprintf( $priceargs['price_format'], get_woocommerce_currency_symbol( $priceargs['currency'] ), $price );
			return html_entity_decode( $formatted_price );
		}
		/**
		 * Prepare static array value of order data.
		 *
		 * @param int    $wpssw_order_id .
		 * @param string $wpssw_old_staus_name .
		 * @param array  $wpssw_prdarray .
		 * @return array $wpssw_prdarray
		 */
		public static function wpssw_set_static_values( $wpssw_order_id = 0, $wpssw_old_staus_name = '', $wpssw_prdarray = array() ) {
			// Add Static Values to new values array.
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_headers_name  = parent::wpssw_option( 'wpssw_sheet_headers_list' );
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				$wpssw_headers_name = array_merge( $wpssw_headers_name, $wpssw_woo_event_headers );
			}
			$wpssw_static_header = stripslashes_deep( parent::wpssw_option( 'wpssw_static_header' ) );
			if ( ! $wpssw_static_header ) {
				$wpssw_static_header = array();
			}
			if ( ! empty( $wpssw_old_staus_name ) && ! empty( $wpssw_static_header ) ) {
				$wpssw_rangetofind = $wpssw_old_staus_name . '!A:A';
				$wpssw_allentry    = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
				$wpssw_data        = $wpssw_allentry->getValues();
				$wpssw_data        = array_map(
					function( $element ) {
						if ( isset( $element['0'] ) ) {
							return $element['0'];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_num         = array_search( (int) $wpssw_order_id, parent::wpssw_convert_int( $wpssw_data ), true );
				if ( $wpssw_num > 0 ) {
					$wpssw_rownum = $wpssw_num + 1;
					// Add or Remove Row at spreadsheet.
					$wpssw_ordrow   = 0;
					$wpssw_notempty = 0;
					end( $wpssw_data );
					$wpssw_lastelement = key( $wpssw_data );
					reset( $wpssw_data );
					$wpssw_data_count = count( $wpssw_data );
					for ( $i = $wpssw_rownum; $i < $wpssw_data_count; $i++ ) {
						if ( (int) $wpssw_data[ $i ] === (int) $wpssw_order_id ) {
							$wpssw_ordrow++;
							if ( (int) $wpssw_lastelement === (int) $i ) {
								break;
							}
						} else {
							break;
						}
					}
					$wpssw_start_row       = $wpssw_rownum;
					$wpssw_end_row         = (int) $wpssw_rownum + (int) $wpssw_ordrow;
					$wpssw_old_sheet_range = $wpssw_old_staus_name . '!A' . $wpssw_start_row . ':ZZZ' . $wpssw_end_row;
					try {
						$wpssw_response    = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_old_sheet_range );
						$wpssw_olddata     = $wpssw_response->getValues();
						$wpss_static_index = array();
						foreach ( $wpssw_static_header as $h ) {
							if ( in_array( $h, $wpssw_headers_name, true ) ) {
								$wpss_static_index[] = array_search( $h, $wpssw_headers_name, true ) + 1;
							}
						}
						$wpssw_cnt = 0;
						foreach ( $wpssw_prdarray as &$newvalue ) {
							foreach ( $wpss_static_index as $inx ) {
								if ( isset( $wpssw_olddata[ $wpssw_cnt ][ $inx ] ) ) {
									$newvalue[ $inx ] = $wpssw_olddata[ $wpssw_cnt ][ $inx ];
								}
							}
							$wpssw_cnt++;
						}
						return $wpssw_prdarray;
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
			}
			return $wpssw_prdarray;
		}
		/**
		 * Restore a post from the Trash
		 *
		 * @param string $wpssw_new_status New status of post.
		 * @param string $wpssw_old_status Old status of post.
		 * @param object $wpssw_post Post to restore.
		 */
		public static function wpssw_wcgs_restore( $wpssw_new_status, $wpssw_old_status, $wpssw_post ) {
			global $post_type;
			// @codingStandardsIgnoreStart.
			if ( ( 'shop_order' !== (string) $post_type ) || ( isset( $_REQUEST['action'] ) && 'untrash' !== sanitize_text_field( wp_unslash($_REQUEST['action'] ) ) ) ) {
			// @codingStandardsIgnoreEnd.
				return;
			}
			$wpssw_order = wc_get_order( $wpssw_post->ID );
			if ( isset( $wpssw_order ) && ! empty( $wpssw_order ) ) {
				$wpssw_sheetname = substr( $wpssw_new_status, strpos( $wpssw_new_status, '-' ) + 1 );
				if ( 'trash' === (string) $wpssw_old_status ) {
					self::wpssw_woo_order_status_change_custom( $wpssw_post->ID, 'trash', $wpssw_sheetname );
				}
			}
		}
		/**
		 * Move (Delete) order data from sheet provided by $wpssw_sheetname.
		 *
		 * @param int    $wpssw_order_id .
		 * @param string $wpssw_sheetid .
		 * @param string $wpssw_sheetname .
		 */
		public static function wpssw_move_order( $wpssw_order_id, $wpssw_sheetid, $wpssw_sheetname ) {
			if ( self::wpssw_check_product_category( $wpssw_order_id ) ) {
				return;
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_rangetofind   = $wpssw_sheetname . '!A:A';
			$wpssw_allentry      = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_rangetofind );
			$wpssw_data          = $wpssw_allentry->getValues();
			do_action( 'wpssw_move_order', $wpssw_order_id, $wpssw_sheetname );
			$wpssw_data       = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$wpssw_order      = wc_get_order( $wpssw_order_id );
			$wpssw_item_count = $wpssw_order->get_items();
			$wpssw_num        = array_search( (int) $wpssw_order_id, parent::wpssw_convert_int( $wpssw_data ), true );
			if ( $wpssw_num > 0 ) {
				$wpssw_startindex  = $wpssw_num;
				$wpssw_header_type = self::wpssw_is_productwise();
				if ( $wpssw_header_type ) {
					$wpssw_endindex = count( $wpssw_item_count );
					$wpssw_endindex = $wpssw_num + $wpssw_endindex;
				} else {
					$wpssw_endindex = $wpssw_num + 1;
				}
				$param                  = array();
				$param                  = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
				$wpssw_requestbody      = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
				$param                  = array();
				$param['spreadsheetid'] = $wpssw_spreadsheetid;
				$param['requestarray']  = $wpssw_requestbody;
				$wpssw_response         = self::$instance_api->updatebachrequests( $param );
			}
		}
		/**
		 * Added v4.6
		 * Get product categories
		 *
		 * @return array $wpssw_product_category_list product category listing array
		 */
		public static function wpssw_get_product_categories() {
			$wpssw_product_category_list = array();
			$args                        = array(
				'taxonomy' => 'product_cat',
				'orderby'  => 'name',
			);
			$product_categories          = get_terms( $args );
			foreach ( $product_categories as $prd_cat ) {
				$wpssw_product_category_list[ $prd_cat->term_id ] = $prd_cat->name;
			}
			return $wpssw_product_category_list;
		}
		/**
		 * Create new sheets in selected spreadsheet for General settings Default Order Status options
		 *
		 * @param array $wpssw_data .
		 * @return string $wpssw_spreadsheetid
		 */
		public static function wpssw_create_sheet( $wpssw_data ) {
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_graphsheets_list = array();
			if ( isset( $wpssw_data['graphsheets_list'] ) ) {
				$wpssw_graphsheets_list = $wpssw_data['graphsheets_list'];
			}
			foreach ( $wpssw_graphsheets_list as $list ) {
				$wpssw_data[ $list ] = 1;
			}
			if ( 'new' === (string) $wpssw_data['woocommerce_spreadsheet'] ) {
				$wpssw_newsheetname = trim( $wpssw_data['spreadsheetname'] );

				/*
				*Create new spreadsheet
				*/
				$requestbody         = self::$instance_api->createspreadsheetobject( $wpssw_newsheetname );
				$wpssw_response      = self::$instance_api->createspreadsheet( $requestbody );
				$wpssw_spreadsheetid = $wpssw_response['spreadsheetId'];
			} else {
				$wpssw_spreadsheetid = $wpssw_data['woocommerce_spreadsheet'];
			}
			if ( ! empty( $wpssw_data['header_fields'] ) ) {
				if ( isset( $wpssw_data['prdassheetheaders'] ) && isset( $wpssw_data['wpssw_append_after'] ) && in_array(
					$wpssw_data['wpssw_append_after'],
					array_map(
						function( $key ) {
							return str_replace( ' ', '-', strtolower( $key ) ); },
						$wpssw_data['header_fields']
					),
					true
				) ) {
					$flag                = 0;
					$wpssw_header        = array();
					$wpssw_header_custom = array();
					foreach ( $wpssw_data['header_fields'] as $headers ) {
						$wpssw_header[]        = $headers;
						$wpssw_header_custom[] = $wpssw_data['header_fields_custom'][ $flag ];
						if ( str_replace( ' ', '-', strtolower( $headers ) ) === $wpssw_data['wpssw_append_after'] && ! empty( $wpssw_data['product_header_fields'] ) ) {
							foreach ( $wpssw_data['product_header_fields'] as $prd_header ) {
								$wpssw_header[] = $prd_header;
							}
							foreach ( $wpssw_data['product_header_fields_custom'] as $prd_header ) {
								$wpssw_header_custom[] = $prd_header;
							}
						}
						$flag++;
					}
				} else {
					if ( isset( $wpssw_data['prdassheetheaders'] ) && ! empty( $wpssw_data['product_header_fields'] ) && isset( $wpssw_data['wpssw_append_after'] ) && ! empty( $wpssw_data['wpssw_append_after'] ) ) {
						$wpssw_header        = array_merge( $wpssw_data['header_fields'], $wpssw_data['product_header_fields'] );
						$wpssw_header_custom = array_merge( $wpssw_data['header_fields_custom'], $wpssw_data['product_header_fields_custom'] );
					} else {
						$wpssw_header        = $wpssw_data['header_fields'];
						$wpssw_header_custom = $wpssw_data['header_fields_custom'];
					}
				}
				$wpssw_headers       = stripslashes_deep( $wpssw_header );
				$wpssw_header_custom = stripslashes_deep( $wpssw_header_custom );
			} else {
				$wpssw_headers       = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
				$wpssw_header_custom = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list_custom' ) );
			}
			$wpssw_eventsheet_list          = array();
			$wpssw_woo_event_headers        = array();
			$wpssw_woo_event_headers_custom = array();
			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				$wpssw_woo_event_headers        = parent::wpssw_option( 'wpssw_woo_event_headers' );
				$wpssw_woo_event_headers_custom = parent::wpssw_option( 'wpssw_woo_event_headers_custom' );
				if ( ! is_array( $wpssw_woo_event_headers ) ) {
					$wpssw_woo_event_headers = array();
				}
				if ( ! is_array( $wpssw_woo_event_headers_custom ) ) {
					$wpssw_woo_event_headers_custom = array();
				}
				$wpssw_headers         = array_merge( $wpssw_headers, $wpssw_woo_event_headers );
				$wpssw_header_custom   = array_merge( $wpssw_header_custom, $wpssw_woo_event_headers_custom );
				$wpssw_eventsheet_list = parent::wpssw_option( 'wpssw_eventsheets_list' );
			}
			if ( ! is_array( $wpssw_eventsheet_list ) ) {
				$wpssw_eventsheet_list = array();
			}
			if ( count( $wpssw_headers ) > 0 ) {
				array_unshift( $wpssw_headers, 'Order Id' );
				$wpssw_value = array( $wpssw_headers );
			}
			if ( count( $wpssw_header_custom ) > 0 ) {
				array_unshift( $wpssw_header_custom, 'Order Id' );
				$wpssw_value_custom = array( $wpssw_header_custom );
			}
			$wpssw_remove_sheet = array();
			if ( isset( $wpssw_data['pending_orders'] ) ) {
				$wpssw_pendingorder = $wpssw_data['pending_orders'];
			} else {
				$wpssw_pendingorder   = 0;
				$wpssw_remove_sheet[] = 'Pending Orders';
			}
			if ( isset( $wpssw_data['processing_orders'] ) ) {
				$wpssw_processingorder = $wpssw_data['processing_orders'];
			} else {
				$wpssw_processingorder = 0;
				$wpssw_remove_sheet[]  = 'Processing Orders';
			}
			if ( isset( $wpssw_data['on_hold_orders'] ) ) {
				$wpssw_onholdorder = $wpssw_data['on_hold_orders'];
			} else {
				$wpssw_onholdorder    = 0;
				$wpssw_remove_sheet[] = 'On Hold Orders';
			}
			if ( isset( $wpssw_data['completed_orders'] ) ) {
				$wpssw_completedorders = $wpssw_data['completed_orders'];
			} else {
				$wpssw_completedorders = 0;
				$wpssw_remove_sheet[]  = 'Completed Orders';
			}
			if ( isset( $wpssw_data['cancelled_orders'] ) ) {
				$wpssw_cancelledorders = $wpssw_data['cancelled_orders'];
			} else {
				$wpssw_cancelledorders = 0;
				$wpssw_remove_sheet[]  = 'Cancelled Orders';
			}
			if ( isset( $wpssw_data['refunded_orders'] ) ) {
				$wpssw_refundedorders = $wpssw_data['refunded_orders'];
			} else {
				$wpssw_refundedorders = 0;
				$wpssw_remove_sheet[] = 'Refunded Orders';
			}
			if ( isset( $wpssw_data['failed_orders'] ) ) {
				$wpssw_failedorders = $wpssw_data['failed_orders'];
			} else {
				$wpssw_failedorders   = 0;
				$wpssw_remove_sheet[] = 'Failed Orders';
			}
			if ( isset( $wpssw_data['trash'] ) ) {
				$wpssw_trashorders = $wpssw_data['trash'];
			} else {
				$wpssw_trashorders    = 0;
				$wpssw_remove_sheet[] = 'Trash Orders';
			}
			if ( isset( $wpssw_data['all_orders'] ) ) {
				$wpssw_allorders = $wpssw_data['all_orders'];
			} else {
				$wpssw_allorders      = 0;
				$wpssw_remove_sheet[] = 'All Orders';
			}
			if ( isset( $wpssw_data['sales_orders_graph'] ) ) {
				$wpssw_sales_orders_graph = $wpssw_data['sales_orders_graph'];
			} else {
				$wpssw_sales_orders_graph = 0;
				$wpssw_remove_sheet[]     = 'Sales Orders Graph';
			}
			if ( isset( $wpssw_data['total_orders_graph'] ) ) {
				$wpssw_total_orders_graph = $wpssw_data['total_orders_graph'];
			} else {
				$wpssw_total_orders_graph = 0;
				$wpssw_remove_sheet[]     = 'Total Orders Graph';
			}
			if ( isset( $wpssw_data['products_sold_graph'] ) ) {
				$wpssw_products_sold_graph = $wpssw_data['products_sold_graph'];
			} else {
				$wpssw_products_sold_graph = 0;
				$wpssw_remove_sheet[]      = 'Products Sold Graph';
			}
			if ( isset( $wpssw_data['total_customers_graph'] ) ) {
				$wpssw_total_customers_graph = $wpssw_data['total_customers_graph'];
			} else {
				$wpssw_total_customers_graph = 0;
				$wpssw_remove_sheet[]        = 'Total Customers Graph';
			}
			if ( isset( $wpssw_data['total_used_coupons_graph'] ) ) {
				$wpssw_total_used_coupons_graph = $wpssw_data['total_used_coupons_graph'];
			} else {
				$wpssw_total_used_coupons_graph = 0;
				$wpssw_remove_sheet[]           = 'Total Used Coupons Graph';
			}
			$wpssw_order_array       = array( $wpssw_pendingorder, $wpssw_processingorder, $wpssw_onholdorder, $wpssw_completedorders, $wpssw_cancelledorders, $wpssw_refundedorders, $wpssw_failedorders, $wpssw_trashorders, $wpssw_allorders );
			$wpssw_graphsheets_array = array( $wpssw_sales_orders_graph, $wpssw_total_orders_graph, $wpssw_products_sold_graph, $wpssw_total_customers_graph, $wpssw_total_used_coupons_graph );
			$wpssw_sheetnames        = array( 'Pending Orders', 'Processing Orders', 'On Hold Orders', 'Completed Orders', 'Cancelled Orders', 'Refunded Orders', 'Failed Orders', 'Trash Orders', 'All Orders' );
			$wpssw_graph_sheetnames  = array( 'Sales Orders Graph', 'Total Orders Graph', 'Products Sold Graph', 'Total Customers Graph', 'Total Used Coupons Graph' );

			/*
			*Custom Order Status sheet setting .
			*/
			$wpssw_custom_status_array = array();
			$wpssw_status_array        = wc_get_order_statuses();
			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				$wpssw_status = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
				if ( isset( $wpssw_data[ $wpssw_status ] ) ) {
					$wpssw_order_array[] = 1;
					$wpssw_sheetnames[]  = $wpssw_val . ' Orders';
				} else {
					if ( ! in_array( $wpssw_status, self::$wpssw_default_status, true ) ) {
						$wpssw_remove_sheet[] = $wpssw_val . ' Orders';
					}
				}
			}
			$response               = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheets   = self::$instance_api->get_sheet_list( $response );
			$wpssw_existingsheets   = array_flip( $wpssw_existingsheets );
			$wpssw_sheetnames       = array_merge( $wpssw_sheetnames, $wpssw_eventsheet_list );
			$wpssw_sheetnames_count = count( $wpssw_eventsheet_list );
			for ( $i = 0; $i < $wpssw_sheetnames_count; $i++ ) {
				if ( in_array( $wpssw_eventsheet_list[ $i ], $wpssw_existingsheets, true ) ) {
					$wpssw_order_array[] = 0;
				} else {
					$wpssw_order_array[] = 1;
				}
			}
			if ( 'new' !== (string) $wpssw_data['woocommerce_spreadsheet'] ) {
				$wpssw_sheetnames_count = count( $wpssw_sheetnames );
				for ( $i = 0; $i < $wpssw_sheetnames_count; $i++ ) {
					if ( in_array( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true ) ) {
						$wpssw_order_array[ $i ] = 0;
					} else {
						if ( 1 === (int) $wpssw_order_array[ $i ] && ! in_array( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true ) ) {
							$wpssw_order_array[ $i ] = 1;
						}
					}
				}
				$wpssw_graph_sheetnames_count = count( $wpssw_graph_sheetnames );
				for ( $j = 0; $j < $wpssw_graph_sheetnames_count; $j++ ) {
					if ( in_array( $wpssw_graph_sheetnames[ $j ], $wpssw_existingsheets, true ) ) {
						$wpssw_graphsheets_array[ $j ] = 0;
					} else {
						if ( 1 === (int) $wpssw_graphsheets_array[ $j ] && ! in_array( $wpssw_graph_sheetnames[ $j ], $wpssw_existingsheets, true ) ) {
							$wpssw_graphsheets_array[ $j ] = 1;
						}
					}
				}
			}
			$wpssw_newsheet          = 0;
			$wpssw_order_array_count = count( $wpssw_order_array );
			for ( $i = 0; $i < $wpssw_order_array_count; $i++ ) {
				$i = (int) $i;
				if ( 0 === $i ) {
					$wpssw_sheetname = 'Pending Orders';
				}
				if ( 1 === $i ) {
					$wpssw_sheetname = 'Processing Orders';
				}
				if ( 2 === $i ) {
					$wpssw_sheetname = 'On Hold Orders';
				}
				if ( 3 === $i ) {
					$wpssw_sheetname = 'Completed Orders';
				}
				if ( 4 === $i ) {
					$wpssw_sheetname = 'Cancelled Orders';
				}
				if ( 5 === $i ) {
					$wpssw_sheetname = 'Refunded Orders';
				}
				if ( 6 === $i ) {
					$wpssw_sheetname = 'Failed Orders';
				}
				if ( 7 === $i ) {
					$wpssw_sheetname = 'Trash Orders';
				}
				if ( 8 === $i ) {
					$wpssw_sheetname = 'All Orders';
				}
				// Create Custom order sheet.
				if ( $i > 8 ) {
					$wpssw_sheetname = $wpssw_sheetnames[ $i ];
				}
				if ( 1 === (int) $wpssw_order_array[ $i ] ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['sheetname']     = $wpssw_sheetname;
					$wpssw_response         = self::$instance_api->newsheetobject( $param );
					$wpssw_range            = trim( $wpssw_sheetname ) . '!A1';
					$wpssw_params           = array( 'valueInputOption' => $wpssw_inputoption );
					$wpssw_requestbody      = self::$instance_api->valuerangeobject( $wpssw_value_custom );
					$param                  = array();
					$param                  = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response         = self::$instance_api->appendentry( $param );
					$wpssw_newsheet         = 1;
				}
			}
			$wpssw_graphsheets_array_count = count( $wpssw_graphsheets_array );
			for ( $j = 0; $j < $wpssw_graphsheets_array_count; $j++ ) {
				$j = (int) $j;
				if ( 0 === $j ) {
					$wpssw_graphsheetname = 'Sales Orders Graph';
				}
				if ( 1 === $j ) {
					$wpssw_graphsheetname = 'Total Orders Graph';
				}
				if ( 2 === $j ) {
					$wpssw_graphsheetname = 'Products Sold Graph';
				}
				if ( 3 === $j ) {
					$wpssw_graphsheetname = 'Total Customers Graph';
				}
				if ( 4 === $j ) {
					$wpssw_graphsheetname = 'Total Used Coupons Graph';
				}
				if ( 1 === (int) $wpssw_graphsheets_array[ $j ] ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['sheetname']     = $wpssw_graphsheetname;
					$wpssw_response         = self::$instance_api->newsheetobject( $param );
					self::wpssw_add_graph( $wpssw_graphsheetname, '', $wpssw_spreadsheetid );
				}
			}
			if ( 'new' === (string) $wpssw_data['woocommerce_spreadsheet'] ) {
				$param                  = array();
				$param['spreadsheetid'] = $wpssw_spreadsheetid;
				$wpssw_response         = self::$instance_api->deletesheetobject( $param );
			}
			if ( 'new' !== (string) $wpssw_data['woocommerce_spreadsheet'] ) {
				$requestarray           = array();
				$deleterequestarray     = array();
				$wpssw_old_header_order = parent::wpssw_option( 'wpssw_sheet_headers_list' );
				if ( parent::wpssw_is_event_calender_ticket_active() ) {
					if ( ! is_array( $wpssw_old_header_order ) ) {
						$wpssw_old_header_order = array();
					}
					$wpssw_old_header_order = array_merge( $wpssw_old_header_order, $wpssw_woo_event_headers );
				}
				array_unshift( $wpssw_old_header_order, 'Order Id' );
				if ( $wpssw_old_header_order !== $wpssw_headers ) {
					// Delete deactivate column from sheet.
					$wpssw_column = array_diff( $wpssw_old_header_order, $wpssw_headers );
					if ( ! empty( $wpssw_column ) ) {
						$wpssw_column = array_reverse( $wpssw_column, true );
						foreach ( $wpssw_column as $columnindex => $columnval ) {
							unset( $wpssw_old_header_order[ $columnindex ] );
							$wpssw_old_header_order = array_values( $wpssw_old_header_order );
							$wpssw_sheetnames_count = count( $wpssw_sheetnames );
							for ( $i = 0; $i < $wpssw_sheetnames_count; $i++ ) {
								if ( in_array( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true ) ) {
									$wpssw_sheetid = array_search( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true );
									if ( $wpssw_sheetid ) {
										$param                = array();
										$startindex           = $columnindex;
										$endindex             = $columnindex + 1;
										$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
										$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param );
									}
								}
							}
						}
					}
					try {
						if ( ! empty( $deleterequestarray ) ) {
							$param                  = array();
							$param['spreadsheetid'] = $wpssw_spreadsheetid;
							$param['requestarray']  = $deleterequestarray;
							$wpssw_response         = self::$instance_api->updatebachrequests( $param );
						}
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
				if ( $wpssw_old_header_order !== $wpssw_headers ) {
					foreach ( $wpssw_headers as $key => $hname ) {
						if ( 'Order Id' === (string) $hname ) {
							continue;
						}
						$wpssw_startindex = array_search( $hname, $wpssw_old_header_order, true );
						if ( false !== $wpssw_startindex && ( isset( $wpssw_old_header_order[ $key ] ) && $wpssw_old_header_order[ $key ] !== $hname ) ) {
							unset( $wpssw_old_header_order[ $wpssw_startindex ] );
							$wpssw_old_header_order = array_merge( array_slice( $wpssw_old_header_order, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header_order, $key, count( $wpssw_old_header_order ) - $key ) );
							$wpssw_endindex         = $wpssw_startindex + 1;
							$wpssw_destindex        = $key;
							$wpssw_sheetnames_count = count( $wpssw_sheetnames );
							for ( $i = 0; $i < $wpssw_sheetnames_count; $i++ ) {
								if ( in_array( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true ) ) {
									$wpssw_sheetid = array_search( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true );
									if ( $wpssw_sheetid ) {
										$param              = array();
										$param              = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
										$param['destindex'] = $wpssw_destindex;
										$requestarray[]     = self::$instance_api->moveDimensionrequests( $param );
									}
								}
							}
						} elseif ( false === (bool) $wpssw_startindex ) {
							$wpssw_old_header_order = array_merge( array_slice( $wpssw_old_header_order, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header_order, $key, count( $wpssw_old_header_order ) - $key ) );
							$wpssw_sheetnames_count = count( $wpssw_sheetnames );
							for ( $i = 0; $i < $wpssw_sheetnames_count; $i++ ) {
								if ( in_array( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true ) ) {
									$wpssw_sheetid = array_search( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true );
									if ( $wpssw_sheetid ) {
										$param            = array();
										$wpssw_startindex = $key;
										$wpssw_endindex   = $key + 1;
										$param            = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
										$requestarray[]   = self::$instance_api->insertdimensionrequests( $param );
									}
								}
							}
						}
					}
					if ( ! empty( $requestarray ) ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpssw_spreadsheetid;
						$param['requestarray']  = $requestarray;
						$wpssw_response         = self::$instance_api->updatebachrequests( $param );
					}
				}
			}
			$wpssw_sheetnames_count = count( $wpssw_sheetnames );
			for ( $i = 0; $i < $wpssw_sheetnames_count; $i++ ) {
				if ( in_array( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true ) && 0 === (int) $wpssw_order_array[ $i ] ) {
					$wpssw_range       = trim( $wpssw_sheetnames[ $i ] ) . '!A1';
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_value_custom );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->updateentry( $param );
				}
			}
			// Delete sheet from spreadsheet on deactivate order status.
			if ( ! empty( $wpssw_remove_sheet ) ) {
				foreach ( $wpssw_remove_sheet as $key => $name ) {
					if ( ! in_array( $name, $wpssw_existingsheets, true ) ) {
						unset( $wpssw_remove_sheet[ $key ] );
					}
				}
				$wpssw_remove_sheet = array_values( $wpssw_remove_sheet );
			}
			if ( ! empty( $wpssw_remove_sheet ) && 'new' !== (string) $wpssw_data['woocommerce_spreadsheet'] ) {
				parent::wpssw_delete_sheet( $wpssw_spreadsheetid, $wpssw_remove_sheet, $wpssw_existingsheets );
			}
			if ( isset( $wpssw_data['freeze_header'] ) ) {
				$wpssw_freeze = 1;
			} else {
				$wpssw_freeze = 0;
			}
			if ( isset( $wpssw_data['color_code'] ) ) {
				$oddcolor  = $wpssw_data['oddcolor'];
				$evencolor = $wpssw_data['evencolor'];
			} else {
				$oddcolor  = '#ffffff';
				$evencolor = '#ffffff';
			}
			$color_code_saved = parent::wpssw_option( 'wpssw_color_code' );
			$oddcolor_saved   = parent::wpssw_option( 'wpssw_oddcolor' );
			$evencolor_saved  = parent::wpssw_option( 'wpssw_evencolor' );
			$wpssw_color      = 1;
			if ( isset( $wpssw_data['color_code'] ) && (string) $wpssw_data['color_code'] === $color_code_saved && isset( $wpssw_data['oddcolor'] ) && (string) $wpssw_data['oddcolor'] === $oddcolor_saved && isset( $wpssw_data['evencolor'] ) && (string) $wpssw_data['evencolor'] === $evencolor_saved ) {
				$wpssw_color = 0;
			}
			$wpssw_freeze_header = 1;
			$freeze_header       = parent::wpssw_option( 'freeze_header' );
			if ( ( $wpssw_freeze && 'yes' === (string) $freeze_header ) || ( ! $wpssw_freeze && 'no' === (string) $freeze_header ) ) {
				$wpssw_freeze_header = 0;
			}
			if ( 'new' === (string) $wpssw_data['woocommerce_spreadsheet'] || $wpssw_newsheet ) {
				$wpssw_freeze_header = 1;
				$wpssw_color         = 1;
			}
			if ( $wpssw_freeze_header || $wpssw_color ) {
				parent::wpssw_freeze_header( $wpssw_spreadsheetid, $wpssw_freeze, $oddcolor, $evencolor, $wpssw_color, $wpssw_freeze_header );
			}
			return $wpssw_spreadsheetid;
		}
	}
				WPSSW_Order::init();
				endif;
