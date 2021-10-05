<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Coupon' ) ) :
	/**
	 * Class WPSSW_Coupon.
	 */
	class WPSSW_Coupon extends WPSSW_Setting {
		/**
		 * Initialization
		 */
		public function __construct() {
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_coupon_hook();
			$wpssw_include->wpssw_include_coupon_ajax_hook();
		}
		/**
		 * Save Settings of Coupon settings tab.
		 */
		public static function wpssw_update_coupon_settings() {
			if ( ! isset( $_POST['wpssw_coupon_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_coupon_settings'] ) ), 'save_coupon_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( isset( $_POST['woocoupon_header_list'] ) && isset( $_POST['woocoupon_custom'] ) ) {
				$wpssw_woo_coupon_headers        = array_map( 'sanitize_text_field', wp_unslash( $_POST['woocoupon_header_list'] ) );
				$wpssw_woo_coupon_headers_custom = array_map( 'sanitize_text_field', wp_unslash( $_POST['woocoupon_custom'] ) );
				if ( isset( $_POST['coupon_settings_checkbox'] ) ) {
					if ( isset( $_POST['coupon_spreadsheet'] ) && 'new' === (string) sanitize_text_field( wp_unslash( $_POST['coupon_spreadsheet'] ) ) ) {
						$wpssw_newsheetname = isset( $_POST['coupon_spreadsheet_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['coupon_spreadsheet_name'] ) ) ) : '';

						/*
						*Create new spreadsheet
						*/
						$wpssw_requestbody   = self::$instance_api->createspreadsheetobject( $wpssw_newsheetname );
						$wpssw_response      = self::$instance_api->createspreadsheet( $wpssw_requestbody );
						$wpssw_spreadsheetid = $wpssw_response['spreadsheetId'];
					} else {
						$wpssw_spreadsheetid = sanitize_text_field( wp_unslash( $_POST['coupon_spreadsheet'] ) );
					}
					parent::wpssw_update_option( 'wpssw_coupon_spreadsheet_id', $wpssw_spreadsheetid );
					parent::wpssw_update_option( 'wpssw_coupon_spreadsheet_setting', 'yes' );
				} else {
					parent::wpssw_update_option( 'wpssw_coupon_spreadsheet_setting', 'no' );
					parent::wpssw_update_option( 'wpssw_coupon_spreadsheet_id', '' );
					return;
				}
				$wpssw_sheetname           = 'All Coupons';
				$requestarray              = array();
				$deleterequestarray        = array();
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_existingsheets      = array_flip( $wpssw_existingsheetsnames );
				$wpssw_inputoption         = parent::wpssw_option( 'wpssw_inputoption' );
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}
				if ( count( $wpssw_woo_coupon_headers ) > 0 ) {
					array_unshift( $wpssw_woo_coupon_headers, 'Coupon Id' );
				}
				if ( count( $wpssw_woo_coupon_headers_custom ) > 0 ) {
					array_unshift( $wpssw_woo_coupon_headers_custom, 'Coupon Id' );
				}
				$wpssw_old_header_order = parent::wpssw_option( 'wpssw_woo_coupon_headers' );
				if ( empty( $wpssw_old_header_order ) ) {
					$wpssw_old_header_order = array();
				}
				if ( count( $wpssw_old_header_order ) > 0 ) {
					array_unshift( $wpssw_old_header_order, 'Coupon Id' );
				}
				if ( ! in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['sheetname']     = $wpssw_sheetname;
					$wpssw_response         = self::$instance_api->newsheetobject( $param );
					$wpssw_range            = trim( $wpssw_sheetname ) . '!A1';
					$wpssw_requestbody      = self::$instance_api->valuerangeobject( array( $wpssw_woo_coupon_headers_custom ) );
					$wpssw_params           = array( 'valueInputOption' => $wpssw_inputoption );
					$param                  = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response         = self::$instance_api->appendentry( $param );
				}
				if ( 'new' === (string) sanitize_text_field( wp_unslash( $_POST['coupon_spreadsheet'] ) ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$wpssw_response         = self::$instance_api->deletesheetobject( $param );
				}
				if ( $wpssw_old_header_order !== $wpssw_woo_coupon_headers && in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$wpssw_existingsheets      = array();
					$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
					$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
					$wpssw_existingsheets      = array_flip( $wpssw_existingsheetsnames );
					// Delete deactivate column from sheet.
					$wpssw_column = array_diff( $wpssw_old_header_order, $wpssw_woo_coupon_headers );
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
				if ( $wpssw_old_header_order !== $wpssw_woo_coupon_headers ) {
					foreach ( $wpssw_woo_coupon_headers as $key => $hname ) {
						if ( 'Coupon Id' === (string) $hname ) {
							continue;
						}
						$wpssw_startindex = array_search( (string) $hname, parent::wpssw_convert_string( $wpssw_old_header_order ), true );

						if ( false !== $wpssw_startindex && ( isset( $wpssw_old_header_order[ $key ] ) && $wpssw_old_header_order[ $key ] !== $hname ) ) {
							unset( $wpssw_old_header_order[ $wpssw_startindex ] );
							$wpssw_old_header_order = array_merge( array_slice( $wpssw_old_header_order, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header_order, $key, count( $wpssw_old_header_order ) - $key ) );
							$wpssw_endindex         = $wpssw_startindex + 1;
							$wpssw_destindex        = $key;
							if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
								$wpssw_sheetid = array_search( (string) $wpssw_sheetname, parent::wpssw_convert_string( $wpssw_existingsheets ), true );
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
								$wpssw_sheetid = array_search( (string) $wpssw_sheetname, parent::wpssw_convert_string( $wpssw_existingsheets ), true );
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
				$wpssw_color_code_enable   = parent::wpssw_option( 'wpssw_color_code' );
				$wpssw_oddcolor            = parent::wpssw_option( 'wpssw_oddcolor' );
				$wpssw_evencolor           = parent::wpssw_option( 'wpssw_evencolor' );
				$freeze_header             = parent::wpssw_option( 'freeze_header' );
				$wpssw_existingsheets      = array();
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_existingsheets      = array_flip( $wpssw_existingsheetsnames );
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
					// freeze coupon headers.
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
					$wpssw_requestbody = self::$instance_api->valuerangeobject( array( $wpssw_woo_coupon_headers_custom ) );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->updateentry( $param );
				}
				parent::wpssw_update_option( 'wpssw_woo_coupon_headers', array_map( 'sanitize_text_field', wp_unslash( $_POST['woocoupon_header_list'] ) ) );
				parent::wpssw_update_option( 'wpssw_woo_coupon_headers_custom', array_map( 'sanitize_text_field', wp_unslash( $_POST['woocoupon_custom'] ) ) );
			}
		}
		/**
		 * Coupon headers
		 *
		 * @retun array $headers
		 */
		public static function wpssw_woo_coupon_headers() {
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_coupon_compatibility_files();
			$headers = WPSSW_Coupon_Headers::get_header_list( array() );
			return $headers['WPSSW_Coupon_Headers'];
		}
		/**
		 * Insert / Update coupon data into sheet on coupon update
		 *
		 * @param object $coupon .
		 */
		public static function wpssw_coupon_object_updated_props( $coupon ) {
			self::wpssw_insert_coupon_data_into_sheet( $coupon );
		}
		/**
		 * Clear Coupon sheet
		 */
		public static function wpssw_clear_couponsheet() {
			$wpssw_coupon_spreadsheet_setting = parent::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
			$wpssw_spreadsheetid              = parent::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
			$wpssw_checked                    = '';
			if ( 'yes' !== (string) $wpssw_coupon_spreadsheet_setting ) {
				echo 'Please save settings.';
				die();
			}
			$requestbody               = self::$instance_api->clearobject();
			$total_headers             = count( parent::wpssw_option( 'wpssw_woo_coupon_headers' ) ) + 1;
			$last_column               = parent::wpssw_get_column_index( $total_headers );
			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );
			$wpssw_sheetname           = 'All Coupons';
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
		 * Get coupons count for syncronization
		 */
		public static function wpssw_get_coupon_count() {
			if ( ! isset( $_POST['wpssw_coupon_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_coupon_settings'] ) ), 'save_coupon_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_sheetname   = 'All Coupons';
			$wpssw_query_args  = array(
				'post_type'      => 'shop_coupon',
				'posts_per_page' => -1,
				'order'          => 'ASC',
			);
			$wpssw_all_coupons = get_posts( $wpssw_query_args );
			if ( empty( $wpssw_all_coupons ) ) {
				return;
			}
			$wpssw_values_array               = array();
			$total                            = count( $wpssw_all_coupons );
			$wpssw_coupon_spreadsheet_setting = parent::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
			$wpssw_spreadsheetid              = parent::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
			$wpssw_checked                    = '';
			if ( 'yes' !== (string) $wpssw_coupon_spreadsheet_setting ) {
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
			$couponcount    = 0;
			foreach ( $wpssw_all_coupons as $wpssw_coupon ) {
				if ( in_array( (int) $wpssw_coupon->ID, parent::wpssw_convert_int( $wpssw_data ), true ) ) {
					continue;
				}
				$couponcount++;
			}
			echo wp_json_encode( array( 'totalcoupons' => $couponcount ) );
			die;
		}
		/**
		 * Sync coupon data to spreadsheet
		 */
		public static function wpssw_sync_coupons() {
			if ( ! isset( $_POST['couponnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['couponnonce'] ) ), 'save_coupon_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				return;
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_coupon_spreadsheet_setting = parent::wpssw_option( 'wpssw_coupon_spreadsheet_setting' );
			$wpssw_spreadsheetid              = parent::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
			$wpssw_checked                    = '';
			if ( 'yes' !== (string) $wpssw_coupon_spreadsheet_setting ) {
				return;
			}
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_sheetname   = 'All Coupons';
			$wpssw_couponcount = isset( $_POST['couponcount'] ) ? sanitize_text_field( wp_unslash( $_POST['couponcount'] ) ) : '';
			$wpssw_couponlimit = isset( $_POST['couponlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['couponlimit'] ) ) : '';
			$wpssw_query_args  = array(
				'post_type'      => 'shop_coupon',
				'posts_per_page' => -1,
				'order'          => 'ASC',
			);
			$wpssw_all_coupons = get_posts( $wpssw_query_args );
			if ( empty( $wpssw_all_coupons ) ) {
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
			$newcoupon          = 0;
			foreach ( $wpssw_all_coupons as $wpssw_coupon ) {
				if ( in_array( (int) $wpssw_coupon->ID, parent::wpssw_convert_int( $wpssw_data ), true ) ) {
					continue;
				}
				if ( ! empty( $wpssw_coupon ) ) {
					$wpssw_value        = self::wpssw_make_coupon_value_array( 'insert', $wpssw_coupon->post_title );
					$wpssw_values_array = array_merge( $wpssw_values_array, $wpssw_value );
					$newcoupon++;
				}
			}
			$wpssw_sheet = "'" . $wpssw_sheetname . "'!A:A2";
			if ( ! empty( $wpssw_values_array ) ) {
				try {
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
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
		 *  Prepare array value of coupon data to insert into sheet.
		 *
		 * @param string $wpssw_operation operation to perfom on sheet.
		 * @param string $wpssw_coupon_code Coupon Code.
		 * @return array $coupon_value_array
		 */
		public static function wpssw_make_coupon_value_array( $wpssw_operation = 'insert', $wpssw_coupon_code = '' ) {
			if ( ! $wpssw_coupon_code ) {
				return array();
			}
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_coupon_compatibility_files();
			$wpssw_headers              = apply_filters( 'wpsyncsheets_coupon_headers', array() );
			$wpssw_coupon               = new WC_Coupon( $wpssw_coupon_code );
			$wpssw_coupon_row           = array();
			$wpssw_coupon_row[]         = $wpssw_coupon->get_id();
			$wpssw_woo_selections       = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_coupon_headers' ) );
			$wpssw_classarray           = array();
			$wpssw_woo_selections_count = count( $wpssw_woo_selections );
			for ( $i = 0; $i < $wpssw_woo_selections_count; $i++ ) {
				$wpssw_classarray[ $wpssw_woo_selections[ $i ] ] = parent::wpssw_find_class( $wpssw_headers, $wpssw_woo_selections[ $i ] );
			}
			foreach ( $wpssw_classarray as $headername => $classname ) {
				$wpssw_coupon_row[] = $classname::get_value( $headername, $wpssw_coupon );
			}
			$wpssw_coupon_row = self::wpssw_couponcleanArray( $wpssw_coupon_row );
			return array( $wpssw_coupon_row );
		}
		/**
		 *  Insert coupon data into sheet
		 *
		 * @param object $wpssw_coupon .
		 */
		public static function wpssw_insert_coupon_data_into_sheet( $wpssw_coupon ) {
			try {
				if ( ! self::$instance_api->checkcredenatials() ) {
					return;
				}
				if ( ! $wpssw_coupon ) {
					return;
				}
				$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_coupon_spreadsheet_id' );
				$wpssw_sheetname     = 'All Coupons';
				if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
					return;
				}
				$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}
				$wpssw_headers_name        = parent::wpssw_option( 'wpssw_woo_coupon_headers' );
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
				$is_exists                 = array_search(
					(int) $wpssw_coupon->get_id(),
					parent::wpssw_convert_int( $wpssw_data ),
					true
				);
				$wpssw_values_array        = self::wpssw_make_coupon_value_array( 'update', $wpssw_coupon->get_code() );
				$wpssw_append              = 0;
				if ( $is_exists > 0 ) {
					if ( 0 === (int) $wpssw_append ) {
						$wpssw_append   = 1;
						$rownum         = $is_exists + 1;
						$rangetoupdate  = $wpssw_sheetname . '!A' . $rownum;
						$params         = array( 'valueInputOption' => 'USER_ENTERED' );
						$requestbody    = self::$instance_api->valuerangeobject( $wpssw_values_array );
						$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $rangetoupdate, $requestbody, $params );
						$wpssw_response = self::$instance_api->updateentry( $param );
					}
				} else {
					foreach ( $wpssw_data as $wpssw_key => $wpssw_value ) {
						if ( ! empty( $wpssw_value ) ) {
							if ( ( (int) $wpssw_coupon->get_id() < (int) $wpssw_value ) ) {
								$wpssw_append                   = 1;
								$wpssw_startindex               = $wpssw_key;
								$wpssw_endindex                 = $wpssw_key + 1;
								$param                          = array();
								$param                          = self::$instance_api->prepare_param( $wpssw_sheetid, $wpssw_startindex, $wpssw_endindex );
								$wpssw_batchupdaterequest       = self::$instance_api->insertdimensionobject( $param );
								$requestobject                  = array();
								$requestobject['spreadsheetid'] = $wpssw_spreadsheetid;
								$requestobject['requestbody']   = $wpssw_batchupdaterequest;
								$wpssw_response                 = self::$instance_api->formatsheet( $requestobject );
								$wpssw_start_index              = $wpssw_startindex + 1;
								$wpssw_rangetoupdate            = $wpssw_sheetname . '!A' . $wpssw_start_index;
								$wpssw_params                   = array( 'valueInputOption' => $wpssw_inputoption );
								$wpssw_requestbody              = self::$instance_api->valuerangeobject( $wpssw_values_array );
								$param                          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
								$wpssw_response                 = self::$instance_api->updateentry( $param );
								break;
							}
						}
					}
				}
				if ( 0 === (int) $wpssw_append ) {
					$wpssw_isupdated   = 0;
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_values_array );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					if ( count( $wpssw_data ) > 1 ) {
						$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . ( count( $wpssw_data ) + 1 );
						$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
						$wpssw_response      = self::$instance_api->updateentry( $param );
						$wpssw_isupdated     = 1;
					} else {
						$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_sheetname, $wpssw_requestbody, $wpssw_params );
						$wpssw_response = self::$instance_api->appendentry( $param );
					}
				}
			} catch ( Exception $e ) {
				echo esc_html( 'Message: ' . $e->getMessage() );
			}
		}
		/**
		 * Clean coupon data array.
		 *
		 * @param array $wpssw_array coupon data array.
		 * @return array $wpssw_array
		 */
		public static function wpssw_couponcleanArray( $wpssw_array ) {
			$wpssw_max   = count( parent::wpssw_option( 'wpssw_woo_coupon_headers' ) ) + 1;
			$wpssw_array = parent::wpssw_cleanarray( $wpssw_array, $wpssw_max );
			return $wpssw_array;
		}
	}
	new WPSSW_Coupon();
endif;
