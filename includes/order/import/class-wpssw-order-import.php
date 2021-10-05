<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Order_Import' ) ) :
	/**
	 * Class WPSSW_Order_Import.
	 */
	class WPSSW_Order_Import extends WPSSW_Setting {
		/**
		 * Instance of WPSSW_Google_API_Functions
		 *
		 * @var $instance_api
		 */
		protected static $instance_api = null;
		/**
		 * Initialization
		 */
		public function __construct() {
			self::wpssw_google_api();
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_order_import_ajax_hook();
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
		 * Get products count for syncronization
		 */
		public static function wpssw_get_order_import_count() {

			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}

			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$spreadsheets_list   = self::$instance_api->get_spreadsheet_listing();
			if ( ! empty( $wpssw_spreadsheetid ) && ! array_key_exists( $wpssw_spreadsheetid, $spreadsheets_list ) ) {
				echo 'spreadsheetnotexist';
				die;
			}
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
				$param['ranges']        = array( 'ranges' => $wpssw_activesheets );
				$wpssw_response         = self::$instance_api->getbatchvalues( $param );
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
			$wpssw_existingorders = array();
			$response             = array();

			foreach ( $wpssw_response->getValueRanges() as $wpssw_order ) {
				$wpssw_rangetitle = explode( "'!A", $wpssw_order->range );
				$wpssw_sheettitle = str_replace( "'", '', $wpssw_rangetitle[0] );

				$wpssw_data = $wpssw_order->values;

				$wpssw_existingorders[ $wpssw_sheettitle ] = $wpssw_data;
				$wpssw_headers                             = array_shift( $wpssw_data );
				$wpssw_update_orders                       = array();
				$wpssw_delete_orders                       = array();
				if ( in_array( 'Update', $wpssw_headers, true ) ) {
					$wpssw_update_key    = array_search( 'Update', $wpssw_headers, true );
					$wpssw_update_orders = array_values( array_filter( array_column( $wpssw_data, $wpssw_update_key ) ) );
				}
				if ( in_array( 'Delete', $wpssw_headers, true ) ) {
					$wpssw_delete_key    = array_search( 'Delete', $wpssw_headers, true );
					$wpssw_delete_orders = array_values( array_filter( array_column( $wpssw_data, $wpssw_delete_key ) ) );
				}
				$wpssw_result_array = array();

				if ( count( $wpssw_update_orders ) > 0 ) {
					$wpssw_result_array['updateorders'] = count( $wpssw_update_orders );
				}
				if ( count( $wpssw_delete_orders ) > 0 ) {
					$wpssw_result_array['deleteorders'] = count( $wpssw_delete_orders );
				}
				if ( count( $wpssw_update_orders ) > 0 || count( $wpssw_delete_orders ) > 0 ) {
					$wpssw_result_array['sheet_name'] = $wpssw_sheettitle;
					$response[]                       = $wpssw_result_array;
				}
			}
			echo wp_json_encode( $response );
			die;
		}
		/**
		 * Import order
		 */
		public static function wpssw_order_import() {
			if ( ! isset( $_POST['wpssw_general_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_general_settings'] ) ), 'save_general_settings' ) ) {
				echo 'error';
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}

			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );

			$wpssw_sheetname = isset( $_POST['sheetname'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetname'] ) ) : '';

			$wpssw_checked  = '';
			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
			$wpssw_data     = $wpssw_allentry->getValues();
			$wpssw_headers  = array_shift( $wpssw_data );

			$wpssw_order_ids = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element[0] ) ) {
						return $wpssw_element[0];
					} else {
						return '';
					}
				},
				$wpssw_data
			);

			$wpssw_update_orders = array();
			$wpssw_delete_orders = array();

			if ( in_array( 'Update', $wpssw_headers, true ) ) {
				$wpssw_update_key    = array_search( 'Update', $wpssw_headers, true );
				$wpssw_update_orders = array_map(
					function( $wpssw_element ) use ( $wpssw_update_key ) {
						if ( isset( $wpssw_element[ $wpssw_update_key ] ) ) {
							return $wpssw_element[ $wpssw_update_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);

				$wpssw_update_orders = array_filter( $wpssw_update_orders );
			}
			if ( in_array( 'Delete', $wpssw_headers, true ) ) {
				$wpssw_delete_key    = array_search( 'Delete', $wpssw_headers, true );
				$wpssw_delete_orders = array_map(
					function( $wpssw_element ) use ( $wpssw_delete_key ) {
						if ( isset( $wpssw_element[ $wpssw_delete_key ] ) ) {
							return $wpssw_element[ $wpssw_delete_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_delete_orders = array_unique( array_filter( $wpssw_delete_orders ) );
			}

			if ( ! empty( $wpssw_update_orders ) ) {
				foreach ( $wpssw_update_orders as $wpssw_order_index => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}
					if ( ! isset( $wpssw_data[ $wpssw_order_index ] ) ) {
						continue;
					}
					if ( isset( $wpssw_order_ids[ $wpssw_order_index ] ) && ! empty( $wpssw_order_ids[ $wpssw_order_index ] ) ) {
						$ord_id = $wpssw_order_ids[ $wpssw_order_index ];
						$order  = wc_get_order( $ord_id );
						if ( ! empty( $order ) ) {
							$status = $order->get_status();
							if ( ! empty( $status ) && 'trash' === (string) $status ) {
								echo esc_html__( 'orderintrash', 'wpssw' );
								die;
							} else {
								self::wpssw_update_order( $ord_id, $wpssw_data[ $wpssw_order_index ] );
							}
						} else {
							echo esc_html__( 'ordernotexists', 'wpssw' );
							die;
						}
					} else {
						echo esc_html__( 'addorderId', 'wpssw' );
						die;
					}
				}
			}
			if ( ! empty( $wpssw_delete_orders ) ) {
				foreach ( $wpssw_delete_orders as $wpssw_order_index => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}

					if ( ! isset( $wpssw_data[ $wpssw_order_index ] ) ) {
						continue;
					}
					if ( isset( $wpssw_order_ids[ $wpssw_order_index ] ) && ! empty( $wpssw_order_ids[ $wpssw_order_index ] ) ) {

						$ord_id      = $wpssw_order_ids[ $wpssw_order_index ];
						$wpssw_order = wc_get_order( $ord_id );
						if ( ! empty( $wpssw_order ) ) {
							$wpssw_old_status = $wpssw_order->get_status();
							if ( ! empty( $wpssw_old_status ) && 'trash' === (string) $wpssw_old_status ) {
								echo esc_html__( 'orderintrash', 'wpssw' );
								die;
							} else {
								WPSSW_Order::wpssw_woo_order_status_change_custom( $ord_id, $wpssw_old_status, 'trash' );
								wp_trash_post( $ord_id );
							}
						} else {
							echo esc_html__( 'ordernotexists', 'wpssw' );
							die;
						}
					} else {
						echo esc_html__( 'addorderId', 'wpssw' );
						die;
					}
				}
			}
			echo esc_html__( 'successful', 'wpssw' );
			die;
		}
		/**
		 * Update imported order
		 *
		 * @param int    $wpssw_orderid order id.
		 * @param array  $wpssw_data order data array.
		 * @param string $wpssw_opration opration to perform on order.
		 */
		public static function wpssw_update_order( $wpssw_orderid, $wpssw_data, $wpssw_opration = 'update' ) {

			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			if ( (int) $wpssw_orderid < 1 ) {
				return;
			}
			$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_sheet_headers_list' ) );
			if ( ! $wpssw_woo_selections ) {
				return;
			}
			array_unshift( $wpssw_woo_selections, 'Order Id' );
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_order_compatibility_files();
			$wpssw_wooorder_headers = apply_filters( 'wpsyncsheets_order_headers', array() );
			$wpssw_default_header   = $wpssw_wooorder_headers['WPSSW_Default']['Essential'];

			$wpssw_exclude_headers  = array( 'Order Number', 'Product Name', 'SKU', 'Product Quantity', 'Prices Include Tax', 'Order Currency', 'Tax Total', 'Order Total', 'Order Discount Total', 'Order Discount Tax', 'Payment Method', 'Transaction ID', 'Shipping Method Title', 'Shipping Total', 'Coupons Codes', 'Customer ID', 'Client Role', 'Order URL', 'Created Date', 'Status Updated Date', 'Order Completion Date', 'Order Paid Date', 'Order Notes' );
			$wpssw_updated_array    = array();
			$wpssw_crud_operation   = array( 'Update', 'Delete' );
			$wpssw_old_order_status = '';
			$wpssw_order_status     = '';

			$wpssw_order      = wc_get_order( $wpssw_orderid );
			$shipping_country = '';
			$billing_country  = '';

			global $woocommerce;
			$countries_obj         = new WC_Countries();
			$countries             = $countries_obj->get_countries();
			$billing_country_key   = array_search( 'Billing Country', $wpssw_woo_selections, true );
			$billing_country_blank = 0;
			if ( false !== $billing_country_key ) {
				$billing_country = isset( $wpssw_data[ $billing_country_key ] ) ? ucfirst( strtolower( trim( $wpssw_data[ $billing_country_key ] ) ) ) : '';
				if ( ! empty( $billing_country ) && in_array( $billing_country, $countries, true ) ) {
					$billing_country                    = array_search( $billing_country, $countries, true );
					$wpssw_data[ $billing_country_key ] = $billing_country;
				} elseif ( empty( $billing_country ) ) {
					$billing_country_blank = 1;
				} else {
					$billing_country = '';
				}
			} else {
				$billing_country = $wpssw_order->get_billing_country();
			}
			$shipping_country_key   = array_search( 'Shipping Country', $wpssw_woo_selections, true );
			$shipping_country_blank = 0;
			if ( false !== $shipping_country_key ) {
				$shipping_country = isset( $wpssw_data[ $shipping_country_key ] ) ? ucfirst( strtolower( trim( $wpssw_data[ $shipping_country_key ] ) ) ) : '';
				if ( ! empty( $shipping_country ) && in_array( $shipping_country, $countries, true ) ) {
					$shipping_country                    = array_search( $shipping_country, $countries, true );
					$wpssw_data[ $shipping_country_key ] = $shipping_country;
				} elseif ( empty( $shipping_country ) ) {
					$shipping_country_blank = 1;
				} else {
					$shipping_country = '';
				}
			} else {
				$shipping_country = $wpssw_order->get_shipping_country();
			}

			foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_header ) {
				if ( in_array( $wpssw_header, $wpssw_crud_operation, true ) ) {
					continue;
				}
				if ( in_array( $wpssw_header, $wpssw_default_header, true ) ) {
					$wpssw_meta_key           = array_search( $wpssw_header, $wpssw_default_header, true );
					$wpssw_data[ $wpssw_key ] = isset( $wpssw_data[ $wpssw_key ] ) ? $wpssw_data[ $wpssw_key ] : '';

					if ( isset( $wpssw_data[ $wpssw_key ] ) && $wpssw_meta_key ) {
						if ( is_numeric( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_data[ $wpssw_key ] = (int) $wpssw_data[ $wpssw_key ];
						}
					}

					if ( in_array( $wpssw_header, $wpssw_exclude_headers, true ) ) {
						continue;
					}
					if ( ( '_billing_country' === (string) $wpssw_meta_key && empty( $billing_country ) && 0 === (int) $billing_country_blank ) || ( '_shipping_country' === (string) $wpssw_meta_key && empty( $shipping_country ) && 0 === (int) $shipping_country_blank ) ) {
							continue;
					}
					if ( '_billing_state' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$state_name = ucfirst( strtolower( trim( $wpssw_data[ $wpssw_key ] ) ) );
							if ( ! empty( $billing_country ) ) {
								$states = $countries_obj->get_states( $billing_country );

								if ( ! empty( $states ) && in_array( $state_name, $states, true ) ) {
									$wpssw_data[ $wpssw_key ]               = array_search( $state_name, $states, true );
									$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data[ $wpssw_key ];
									self::wpssw_update_post_meta( $wpssw_orderid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
								} elseif ( empty( $states ) ) {
									$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data[ $wpssw_key ];
									self::wpssw_update_post_meta( $wpssw_orderid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
								}
							}
						} else {
							$wpssw_data[ $wpssw_key ]               = '';
							$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data[ $wpssw_key ];
							self::wpssw_update_post_meta( $wpssw_orderid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
						}
						continue;
					}
					if ( '_shipping_state' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$state_name = ucfirst( strtolower( trim( $wpssw_data[ $wpssw_key ] ) ) );

							if ( ! empty( $shipping_country ) ) {
								$states = $countries_obj->get_states( $shipping_country );
								if ( ! empty( $states ) && in_array( $state_name, $states, true ) ) {
									$wpssw_data[ $wpssw_key ]               = array_search( $state_name, $states, true );
									$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data[ $wpssw_key ];
									self::wpssw_update_post_meta( $wpssw_orderid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
								} elseif ( empty( $states ) ) {
									$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data[ $wpssw_key ];
									self::wpssw_update_post_meta( $wpssw_orderid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
								}
							}
						} else {
							$wpssw_data[ $wpssw_key ]               = '';
							$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data[ $wpssw_key ];
							self::wpssw_update_post_meta( $wpssw_orderid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
						}
						continue;
					}
					if ( '_status' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_order_status = strtolower( trim( $wpssw_data[ $wpssw_key ] ) );
						} else {
							$wpssw_order_status = '';
						}
						$wpssw_order                        = wc_get_order( $wpssw_orderid );
						$wpssw_old_order_status             = strtolower( $wpssw_order->get_status() );
						$wpssw_updated_array['post_status'] = $wpssw_order_status;
						continue;
					}
					if ( '_customer_note' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_customer_note = trim( $wpssw_data[ $wpssw_key ] );
						} else {
							$wpssw_customer_note = '';
						}
						$wpssw_updated_array['post_excerpt'] = $wpssw_customer_note;
						continue;
					}
					$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data[ $wpssw_key ];
					self::wpssw_update_post_meta( $wpssw_orderid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
				}
			}
			$wpssw_updated_array['ID'] = $wpssw_orderid;
			wp_update_post( $wpssw_updated_array );
			$wpssw_order = wc_get_order( $wpssw_orderid );
			$wpssw_order->save();

			if ( $wpssw_old_order_status !== $wpssw_order_status ) {
				WPSSW_Order::wpssw_woo_order_status_change_custom( $wpssw_orderid, $wpssw_old_order_status, $wpssw_order_status );
			} else {
				$wpssw_order_post         = get_post( $wpssw_orderid );
				$order_status             = $wpssw_order->get_status();
				$_REQUEST['order_status'] = 'wc-' . $order_status;
				$_REQUEST['post_status']  = 'wc-' . $order_status;
				WPSSW_Order::wpssw_wc_woocommerce_update_post_meta( $wpssw_orderid, $wpssw_order_post );
			}
		}
		/**
		 * Update post meta
		 *
		 * @param int          $wpssw_productid post id to update post meta.
		 * @param string       $wpssw_meta_key meta key to update.
		 * @param string|array $wpssw_data value for meta key.
		 */
		public static function wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_data ) {
			if ( ! $wpssw_productid ) {
				return;
			}
			update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_data );
		}
	}
	new WPSSW_Order_Import();
endif;
