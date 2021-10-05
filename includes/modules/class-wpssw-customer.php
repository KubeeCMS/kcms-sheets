<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Customer' ) ) :
	/**
	 * Class WPSSW_Customer.
	 */
	class WPSSW_Customer extends WPSSW_Setting {
		/**
		 * Initialization
		 */
		public function __construct() {
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_customer_hook();
			$wpssw_include->wpssw_include_customer_ajax_hook();
		}
		/**
		 *
		 * Save Customer settings tab's setting
		 */
		public static function wpssw_update_customer_settings() {

			if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( isset( $_POST['woocustomer_header_list'] ) && isset( $_POST['woocustomer_custom'] ) ) {
				$wpssw_woo_customer_headers        = array_map( 'sanitize_text_field', wp_unslash( $_POST['woocustomer_header_list'] ) );
				$wpssw_woo_customer_headers_custom = array_map( 'sanitize_text_field', wp_unslash( $_POST['woocustomer_custom'] ) );
				if ( isset( $_POST['customer_settings_checkbox'] ) ) {
					if ( isset( $_POST['customer_spreadsheet'] ) && 'new' === (string) sanitize_text_field( wp_unslash( $_POST['customer_spreadsheet'] ) ) ) {
						$wpssw_newsheetname = isset( $_POST['customer_spreadsheet_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['customer_spreadsheet_name'] ) ) ) : '';

						/*
						 *Create new spreadsheet
						 */
						$wpssw_requestbody   = self::$instance_api->createspreadsheetobject( $wpssw_newsheetname );
						$wpssw_response      = self::$instance_api->createspreadsheet( $wpssw_requestbody );
						$wpssw_spreadsheetid = $wpssw_response['spreadsheetId'];
					} else {
						$wpssw_spreadsheetid = sanitize_text_field( wp_unslash( $_POST['customer_spreadsheet'] ) );
					}
					parent::wpssw_update_option( 'wpssw_customer_spreadsheet_id', $wpssw_spreadsheetid );
					parent::wpssw_update_option( 'wpssw_customer_spreadsheet_setting', 'yes' );
				} else {
					parent::wpssw_update_option( 'wpssw_customer_spreadsheet_setting', 'no' );
					parent::wpssw_update_option( 'wpssw_customer_spreadsheet_id', '' );
					return;
				}
				$wpssw_sheetname      = 'All Customers';
				$requestarray         = array();
				$deleterequestarray   = array();
				$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$wpssw_existingsheets = array_flip( $wpssw_existingsheets );
				$wpssw_inputoption    = parent::wpssw_option( 'wpssw_inputoption' );
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}
				if ( count( $wpssw_woo_customer_headers ) > 0 ) {
					array_unshift( $wpssw_woo_customer_headers, 'Customer Id' );
				}
				if ( count( $wpssw_woo_customer_headers_custom ) > 0 ) {
					array_unshift( $wpssw_woo_customer_headers_custom, 'Customer Id' );
				}
				$wpssw_old_header_order = parent::wpssw_option( 'wpssw_woo_customer_headers' );
				if ( empty( $wpssw_old_header_order ) ) {
					$wpssw_old_header_order = array();
				}
				if ( count( $wpssw_old_header_order ) > 0 ) {
					array_unshift( $wpssw_old_header_order, 'Customer Id' );
				}
				if ( ! in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['sheetname']     = $wpssw_sheetname;
					$wpssw_response         = self::$instance_api->newsheetobject( $param );
					$wpssw_range            = trim( $wpssw_sheetname ) . '!A1';
					$wpssw_requestbody      = self::$instance_api->valuerangeobject( array( $wpssw_woo_customer_headers_custom ) );
					$wpssw_params           = array( 'valueInputOption' => $wpssw_inputoption );
					$param                  = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response         = self::$instance_api->appendentry( $param );
				}
				if ( 'new' === (string) sanitize_text_field( wp_unslash( $_POST['customer_spreadsheet'] ) ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$wpssw_response         = self::$instance_api->deletesheetobject( $param );
				}
				if ( $wpssw_old_header_order !== $wpssw_woo_customer_headers && in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$wpssw_existingsheets = array();
					$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
					$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
					$wpssw_existingsheets = array_flip( $wpssw_existingsheets );
					// Delete deactivate column from sheet.
					$wpssw_column = array_diff( $wpssw_old_header_order, $wpssw_woo_customer_headers );
					if ( ! empty( $wpssw_column ) ) {
						$wpssw_column = array_reverse( $wpssw_column, true );
						foreach ( $wpssw_column as $columnindex => $columnval ) {
							unset( $wpssw_old_header_order[ $columnindex ] );
							$wpssw_old_header_order = array_values( $wpssw_old_header_order );
							if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
								$wpssw_sheetid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
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
				if ( $wpssw_old_header_order !== $wpssw_woo_customer_headers ) {
					foreach ( $wpssw_woo_customer_headers as $key => $hname ) {
						if ( 'Customer Id' === (string) $hname ) {
							continue;
						}
						$wpssw_startindex = array_search( $hname, $wpssw_old_header_order, true );
						if ( false !== $wpssw_startindex && ( isset( $wpssw_old_header_order[ $key ] ) && $wpssw_old_header_order[ $key ] !== $hname ) ) {
							unset( $wpssw_old_header_order[ $wpssw_startindex ] );
							$wpssw_old_header_order = array_merge( array_slice( $wpssw_old_header_order, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header_order, $key, count( $wpssw_old_header_order ) - $key ) );
							$wpssw_endindex         = $wpssw_startindex + 1;
							$wpssw_destindex        = $key;
							if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
								$wpssw_sheetid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
								if ( $wpssw_sheetid ) {
									$param              = array();
									$param              = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
									$param['destindex'] = $wpssw_destindex;
									$requestarray[]     = self::$instance_api->moveDimensionrequests( $param );
								}
							}
						} elseif ( false === (bool) $wpssw_startindex ) {
							$wpssw_old_header_order = array_merge( array_slice( $wpssw_old_header_order, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header_order, $key, count( $wpssw_old_header_order ) - $key ) );
							if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
								$wpssw_sheetid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
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
					if ( ! empty( $requestarray ) ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpssw_spreadsheetid;
						$param['requestarray']  = $requestarray;
						$wpssw_response         = self::$instance_api->updatebachrequests( $param );
					}
				}
				$wpssw_color_code_enable = parent::wpssw_option( 'wpssw_color_code' );
				$wpssw_oddcolor          = parent::wpssw_option( 'wpssw_oddcolor' );
				$wpssw_evencolor         = parent::wpssw_option( 'wpssw_evencolor' );
				$freeze_header           = parent::wpssw_option( 'freeze_header' );
				$wpssw_existingsheets    = array();
				$response                = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheets    = self::$instance_api->get_sheet_list( $response );
				$wpssw_existingsheets    = array_flip( $wpssw_existingsheets );
				if ( 'yes' === (string) $freeze_header ) {
					$wpssw_freeze = 1;
				} else {
					$wpssw_freeze = 0;
				}
				if ( $wpssw_color_code_enable ) {
					$oddcolor  = $wpssw_oddcolor;
					$evencolor = $wpssw_evencolor;
				} else {
					$oddcolor  = '#ffffff';
					$evencolor = '#ffffff';
				}
				if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$wpssw_sheetid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
					// freeze customer headers.
					$wpssw_requestbody = self::$instance_api->freezeobject( $wpssw_sheetid, $wpssw_freeze );
					try {
						$requestobject                  = array();
						$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
						$requestobject['requestbody']   = $wpssw_requestbody;
						$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
					// Row background color.
					parent::wpssw_change_row_background_color( $wpssw_spreadsheetid, $wpssw_sheetid, $oddcolor, $evencolor );
				}
				if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$wpssw_range       = trim( $wpssw_sheetname ) . '!A1';
					$wpssw_requestbody = self::$instance_api->valuerangeobject( array( $wpssw_woo_customer_headers_custom ) );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->updateentry( $param );
				}
				parent::wpssw_update_option( 'wpssw_woo_customer_headers', array_map( 'sanitize_text_field', wp_unslash( $_POST['woocustomer_header_list'] ) ) );
				parent::wpssw_update_option( 'wpssw_woo_customer_headers_custom', array_map( 'sanitize_text_field', wp_unslash( $_POST['woocustomer_custom'] ) ) );
			}
		}
		/**
		 * Add new user (created while checkout) data into sheet
		 *
		 * @param int   $customer_id .
		 * @param array $data contains user data.
		 */
		public static function action_woocommerce_checkout_update_customer( $customer_id, $data ) {
			self::wpssw_insert_customer_data_into_sheet( $customer_id );
		}
		/**
		 * Insert / Update user data into sheet on user update
		 *
		 * @param int $user_id user id.
		 */
		public static function edit_user_profile_update( $user_id ) {
			// @codingStandardsIgnoreStart.
			$data = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
			// @codingStandardsIgnoreEnd.
			if ( isset( $data['action'] ) && 'update' === (string) $data['action'] ) {
				$role = $data['role'];
			}
			if ( isset( $data['action'] ) && 'save_account_details' === (string) $data['action'] ) {
				$user                 = new WP_User( $user_id );
				$wpssw_customer_roles = $user->roles;
				$role                 = $wpssw_customer_roles[0];
				$data['user_id']      = $user_id;
				$data['role']         = $role;
			}
			if ( 'customer' === (string) $role ) {
				self::wpssw_insert_customer_data_into_sheet( $user_id, $data );
			}
		}
		/**
		 * Add new user data into sheet
		 *
		 * @param int $user_id user id.
		 */
		public static function wpssw_user_registration_save( $user_id ) {
			// @codingStandardsIgnoreStart.
			$data = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
			// @codingStandardsIgnoreEnd.
			if ( isset( $data['action'] ) && 'createuser' === (string) $data['action'] ) {
				$role            = $data['role'];
				$data['user_id'] = $user_id;
			}
			if ( 'customer' === (string) $role ) {
				self::wpssw_insert_customer_data_into_sheet( $user_id, $data );
			}
		}
		/**
		 * Delete customer's data from sheet on trashing user
		 *
		 * @param int $user_id user id.
		 */
		public static function wpssw_delete_user( $user_id ) {
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_customer_spreadsheet_id' );
			$wpssw_sheetname     = 'All Customers';
			if ( ! self::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
				return;
			}
			$customer             = get_userdata( $user_id );
			$wpssw_customer_roles = $customer->roles;
			$role                 = $wpssw_customer_roles[0];
			if ( 'customer' !== (string) $role ) {
				return;
			}
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
			$wpssw_num                 = array_search( (int) $user_id, parent::wpssw_convert_int( $wpssw_data ), true );
			if ( $wpssw_num > 0 ) {
				$wpssw_startindex = $wpssw_num;
				$wpssw_endindex   = $wpssw_num + 1;
				$param            = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
				$deleterequest    = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
				try {
					if ( ! empty( $deleterequest ) ) {
						$param                  = array();
						$param['spreadsheetid'] = $wpssw_spreadsheetid;
						$param['requestarray']  = $deleterequest;
						self::$instance_api->updatebachrequests( $param );
					}
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
		}
		/**
		 * Clean Customer data array.
		 *
		 * @param array $wpssw_array customer data array.
		 */
		public static function wpssw_customercleanArray( $wpssw_array ) {
			$wpssw_max = count( parent::wpssw_option( 'wpssw_woo_customer_headers' ) ) + 1;
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
		 *
		 * Clear Customer settings sheet
		 */
		public static function wpssw_clear_customersheet() {
			$wpssw_customer_spreadsheet_setting = parent::wpssw_option( 'wpssw_customer_spreadsheet_setting' );
			$wpssw_spreadsheetid                = parent::wpssw_option( 'wpssw_customer_spreadsheet_id' );
			$wpssw_checked                      = '';
			if ( 'yes' !== (string) $wpssw_customer_spreadsheet_setting ) {
				echo esc_html__( 'Please save settings.', 'wpssw' );
				die();
			}
			$requestbody               = self::$instance_api->clearobject();
			$total_headers             = count( parent::wpssw_option( 'wpssw_woo_customer_headers' ) ) + 1;
			$last_column               = parent::wpssw_get_column_index( $total_headers );
			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );
			$wpssw_sheetname           = 'All Customers';
			if ( in_array( $wpssw_sheetname, $wpssw_existingsheetsnames, true ) ) {
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
		 *
		 * Get customers count for syncronization
		 */
		public static function wpssw_get_customer_count() {
			if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_sheetname = 'All Customers';
			$args            = array(
				'role'    => 'customer',
				'orderby' => 'ID',
				'order'   => 'ASC',
			);
			$customers       = get_users( $args );
			foreach ( $customers as $c ) {
				$customers_metadata = get_user_meta( $c->ID );
			}
			$total                              = count( $customers );
			$wpssw_customer_spreadsheet_setting = parent::wpssw_option( 'wpssw_customer_spreadsheet_setting' );
			$wpssw_spreadsheetid                = parent::wpssw_option( 'wpssw_customer_spreadsheet_id' );
			$wpssw_checked                      = '';
			if ( 'yes' !== (string) $wpssw_customer_spreadsheet_setting ) {
				return;
			}
			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheet );
			$wpssw_data     = $wpssw_allentry->getValues();
			$wpssw_data     = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element['0'] ) ) {
						return $wpssw_element['0'];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$ordercount     = 0;
			foreach ( $customers as $customer ) {
				if ( in_array( (int) $customer->ID, parent::wpssw_convert_int( $wpssw_data ), true ) ) {
					continue;
				}
				$ordercount++;
			}
			echo wp_json_encode( array( 'totalcustomers' => $ordercount ) );
			die;
		}
		/**
		 *
		 * Sync Customers
		 */
		public static function wpssw_sync_customers() {
			if ( ! isset( $_POST['wpssw_customer_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_customer_settings'] ) ), 'save_customer_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_customer_spreadsheet_setting = parent::wpssw_option( 'wpssw_customer_spreadsheet_setting' );
			$wpssw_spreadsheetid                = parent::wpssw_option( 'wpssw_customer_spreadsheet_id' );
			$wpssw_checked                      = '';
			if ( 'yes' !== (string) $wpssw_customer_spreadsheet_setting ) {
				return;
			}
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_sheetname  = 'All Customers';
			$wpssw_ordercount = isset( $_POST['ordercount'] ) ? sanitize_text_field( wp_unslash( $_POST['ordercount'] ) ) : '';
			$wpssw_orderlimit = isset( $_POST['orderlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['orderlimit'] ) ) : '';
			$args             = array(
				'role'    => 'customer',
				'orderby' => 'ID',
				'order'   => 'ASC',
			);
			$customers        = get_users( $args );
			if ( empty( $customers ) ) {
				die();
			}
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
			$rangetofind        = $wpssw_sheetname . '!A' . ( count( $wpssw_data ) + 1 );
			$wpssw_values_array = array();
			$neworder           = 0;
			foreach ( $customers as $customer ) {
				global $product;
				if ( in_array( (int) $customer->ID, parent::wpssw_convert_int( $wpssw_data ), true ) ) {
					continue;
				}
				if ( ! empty( $customer ) ) {
					$wpssw_value        = self::wpssw_make_customer_value_array( 'insert', $customer );
					$wpssw_values_array = array_merge( $wpssw_values_array, $wpssw_value );
					$neworder++;
				}
			}
			$wpssw_sheet = "'" . $wpssw_sheetname . "'!A:A2";
			if ( ! empty( $wpssw_values_array ) ) {
				try {
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					if ( count( $wpssw_data ) > 1 ) {
						$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetofind, $wpssw_requestbody, $wpssw_params );
						$wpssw_response = self::$instance_api->appendentry( $param );
					} else {
						$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_sheet, $wpssw_requestbody, $wpssw_params );
						$wpssw_response = self::$instance_api->appendentry( $param );
					}
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			echo 'successful';
			die;
		}
		/**
		 * Prepare array value of customer data to insert into sheet.
		 *
		 * @param string       $wpssw_operation operation to perform on sheet.
		 * @param array|object $wpssw_customer customer data.
		 * @return array $customer_value_array
		 */
		public static function wpssw_make_customer_value_array( $wpssw_operation = 'insert', $wpssw_customer = '' ) {
			if ( ! $wpssw_customer ) {
				return array();
			}
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_customer_compatibility_files();
			$wpssw_headers       = apply_filters( 'wpsyncsheets_customer_headers', array() );
			$wpssw_profile_image = '';
			if ( 'object' === gettype( $wpssw_customer ) ) {
				$wpssw_customer_row[]  = $wpssw_customer->ID;
				$customers_metadata    = get_user_meta( $wpssw_customer->ID );
				$wpssw_customer_roles  = $wpssw_customer->roles;
				$wpssw_customer_values = $wpssw_customer->data;
				$wpssw_profile_image   = '=IMAGE("' . get_avatar_url( $wpssw_customer->ID ) . '")';
			}
			if ( 'array' === gettype( $wpssw_customer ) ) {
				$wpssw_customer_row[]  = $wpssw_customer['user_id'];
				$user                  = new WP_User( $wpssw_customer['user_id'] );
				$customers_metadata    = get_user_meta( $wpssw_customer['user_id'] );
				$wpssw_customer_values = $user->data;
				$wpssw_customer_roles  = array( $wpssw_customer['role'] );
				$wpssw_profile_image   = '=IMAGE("' . get_avatar_url( $wpssw_customer['user_id'] ) . '")';
				if ( 'save_account_details' === (string) $wpssw_customer['action'] ) {
					$customers_metadata['first_name'][0] = $wpssw_customer['account_first_name'];
					$customers_metadata['last_name'][0]  = $wpssw_customer['account_last_name'];
					$wpssw_customer_values->user_email   = $wpssw_customer['account_email'];
				} elseif ( 'update' === (string) $wpssw_customer['action'] ) {
					$wpssw_customer_values->user_email            = $wpssw_customer['email'];
					$wpssw_customer_values->user_url              = $wpssw_customer['url'];
					$customers_metadata['first_name'][0]          = $wpssw_customer['first_name'];
					$customers_metadata['last_name'][0]           = $wpssw_customer['last_name'];
					$customers_metadata['nickname'][0]            = $wpssw_customer['nickname'];
					$customers_metadata['description'][0]         = $wpssw_customer['description'];
					$customers_metadata['billing_first_name'][0]  = $wpssw_customer['billing_first_name'];
					$customers_metadata['billing_last_name'][0]   = $wpssw_customer['billing_last_name'];
					$customers_metadata['billing_company'][0]     = $wpssw_customer['billing_company'];
					$customers_metadata['billing_address_1'][0]   = $wpssw_customer['billing_address_1'];
					$customers_metadata['billing_address_2'][0]   = $wpssw_customer['billing_address_2'];
					$customers_metadata['billing_city'][0]        = $wpssw_customer['billing_city'];
					$customers_metadata['billing_postcode'][0]    = $wpssw_customer['billing_postcode'];
					$customers_metadata['billing_country'][0]     = $wpssw_customer['billing_country'];
					$customers_metadata['billing_state'][0]       = $wpssw_customer['billing_state'];
					$customers_metadata['billing_phone'][0]       = $wpssw_customer['billing_phone'];
					$customers_metadata['billing_email'][0]       = $wpssw_customer['billing_email'];
					$customers_metadata['shipping_first_name'][0] = $wpssw_customer['shipping_first_name'];
					$customers_metadata['shipping_last_name'][0]  = $wpssw_customer['shipping_last_name'];
					$customers_metadata['shipping_company'][0]    = $wpssw_customer['shipping_company'];
					$customers_metadata['shipping_address_1'][0]  = $wpssw_customer['shipping_address_1'];
					$customers_metadata['shipping_address_2'][0]  = $wpssw_customer['shipping_address_2'];
					$customers_metadata['shipping_city'][0]       = $wpssw_customer['shipping_city'];
					$customers_metadata['shipping_postcode'][0]   = $wpssw_customer['shipping_postcode'];
					$customers_metadata['shipping_country'][0]    = $wpssw_customer['shipping_country'];
					$customers_metadata['shipping_state'][0]      = $wpssw_customer['shipping_state'];
				} elseif ( 'createuser' === (string) $wpssw_customer['action'] ) {
					$customers_metadata['first_name'][0] = $wpssw_customer['first_name'];
					$customers_metadata['last_name'][0]  = $wpssw_customer['last_name'];
				}
			}
			$customers_metadata['profile_image'] = $wpssw_profile_image;
			$customers_metadata['user_url']      = $wpssw_customer_values->user_url;
			$customers_metadata['user_email']    = $wpssw_customer_values->user_email;
			$customers_metadata['roles']         = $wpssw_customer_roles;
			$customers_metadata['user_login']    = $wpssw_customer_values->user_login;
			$wpssw_woo_selections                = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_customer_headers' ) );
			$wpssw_classarray                    = array();
			$wpssw_woo_selections_count          = count( $wpssw_woo_selections );
			for ( $i = 0; $i < $wpssw_woo_selections_count; $i++ ) {
				$wpssw_classarray[ $wpssw_woo_selections[ $i ] ] = parent::wpssw_find_class( $wpssw_headers, $wpssw_woo_selections[ $i ] );
			}
			foreach ( $wpssw_classarray as $headername => $classname ) {
				$wpssw_customer_row[] = $classname::get_value( $headername, $customers_metadata );
			}
			$wpssw_customer_row = self::wpssw_customercleanArray( $wpssw_customer_row );
			return array( $wpssw_customer_row );
		}
		/**
		 * Insert customers data into sheet.
		 *
		 * @param int   $user_id customer id.
		 * @param array $data customer data array.
		 */
		public static function wpssw_insert_customer_data_into_sheet( $user_id, $data = array() ) {
			try {
				if ( ! self::$instance_api->checkcredenatials() ) {
					return;
				}
				$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_customer_spreadsheet_id' );
				$wpssw_sheetname     = 'All Customers';
				if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
					return;
				}
				$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}
				$wpssw_headers_name = parent::wpssw_option( 'wpssw_woo_customer_headers' );
				if ( ! empty( $data ) ) {
					$customer = $data;
					$role     = $data['role'];
				} else {
					$customer             = get_userdata( $user_id );
					$wpssw_customer_roles = $customer->roles;
					$role                 = $wpssw_customer_roles[0];
				}
				if ( 'customer' !== (string) $role ) {
					return;
				}
				$wpssw_sheetname           = 'All Customers';
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
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
				$is_exists                 = array_search( (int) $user_id, parent::wpssw_convert_int( $wpssw_data ), true );
				if ( $is_exists > 0 ) {
					$wpssw_values_array = self::wpssw_make_customer_value_array( 'update', $customer );
					$rownum             = $is_exists + 1;
					$rangetoupdate      = $wpssw_sheetname . '!A' . $rownum;
					$params             = array( 'valueInputOption' => 'USER_ENTERED' );
					$requestbody        = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$param              = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetoupdate, $requestbody, $params );
					$wpssw_response     = self::$instance_api->updateentry( $param );
				} else {
					$wpssw_values_array = self::wpssw_make_customer_value_array( 'insert', $customer );
					$key                = count( $wpssw_data ) + 1;
					if ( $wpssw_sheetid ) {
						$rangetoupdate  = $wpssw_sheetname . '!A' . $key;
						$params         = array( 'valueInputOption' => 'USER_ENTERED' );
						$requestbody    = self::$instance_api->valuerangeobject( $wpssw_values_array );
						$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetoupdate, $requestbody, $params );
						$wpssw_response = self::$instance_api->updateentry( $param );
					}
				}
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}
		/**
		 * Clear Customer settings sheet
		 */
		public static function wpssw_clear_custmoersheet() {
			$wpssw_client                       = self::$instance_api->getClient();
			$wpssw_service                      = new Google_Service_Sheets( $wpssw_client );
			$wpssw_customer_spreadsheet_setting = WPSSW_Setting::wpssw_option( 'wpssw_customer_spreadsheet_setting' );
			$wpssw_spreadsheetid                = WPSSW_Setting::wpssw_option( 'wpssw_customer_spreadsheet_id' );
			$wpssw_checked                      = '';
			if ( 'yes' !== (string) $wpssw_customer_spreadsheet_setting ) {
				echo esc_html__( 'Please save settings.', 'wpssw' );
				die();
			}
			$requestbody               = new Google_Service_Sheets_ClearValuesRequest();
			$total_headers             = count( WPSSW_Setting::wpssw_option( 'wpssw_woo_customer_headers' ) ) + 1;
			$last_column               = WPSSW_Setting::wpssw_get_column_index( $total_headers );
			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );
			$wpssw_sheetname           = 'All Customers';
			if ( in_array( $wpssw_sheetname, $wpssw_existingsheetsnames, true ) ) {
				try {
					$range    = $wpssw_sheetname . '!A2:' . $last_column . '10000';
					$response = $wpssw_service->spreadsheets_values->clear( $wpssw_spreadsheetid, $range, $requestbody );
				} catch ( Exception $e ) {
					echo esc_html( 'Message: ' . $e->getMessage() );
				}
			}
			echo 'successful';
			die();
		}
	}
	new WPSSW_Customer();
endif;
