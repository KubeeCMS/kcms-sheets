<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Product' ) ) :
	/**
	 * Class WPSSW_Product.
	 */
	class WPSSW_Product extends WPSSW_Setting {
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
			$wpssw_include->wpssw_include_product_hook();
			$wpssw_include->wpssw_include_product_ajax_hook();
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
		 *
		 * Save Product settings tab's settings.
		 */
		public static function wpssw_update_product_settings() {
			if ( ! isset( $_POST['wpssw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_product_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( isset( $_POST['wooproduct_header_list'] ) && isset( $_POST['wooproduct_custom'] ) ) {
				$wpssw_woo_product_headers        = array_map( 'sanitize_text_field', wp_unslash( $_POST['wooproduct_header_list'] ) );
				$wpssw_woo_product_headers_custom = array_map( 'sanitize_text_field', wp_unslash( $_POST['wooproduct_custom'] ) );
				if ( isset( $_POST['product_settings_checkbox'] ) ) {
					if ( isset( $_POST['product_spreadsheet'] ) && 'new' === (string) sanitize_text_field( wp_unslash( $_POST['product_spreadsheet'] ) ) ) {
						$wpssw_newsheetname = isset( $_POST['product_spreadsheet_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['product_spreadsheet_name'] ) ) ) : '';

						/*
						 *Create new spreadsheet
						 */
						$wpssw_newsheetname  = trim( $wpssw_newsheetname );
						$requestbody         = self::$instance_api->createspreadsheetobject( $wpssw_newsheetname );
						$wpssw_response      = self::$instance_api->createspreadsheet( $requestbody );
						$wpssw_spreadsheetid = $wpssw_response['spreadsheetId'];
					} else {
						$wpssw_spreadsheetid = sanitize_text_field( wp_unslash( $_POST['product_spreadsheet'] ) );
					}
					parent::wpssw_update_option( 'wpssw_product_spreadsheet_id', $wpssw_spreadsheetid );
					parent::wpssw_update_option( 'wpssw_product_spreadsheet_setting', 'yes' );
				} else {
					parent::wpssw_update_option( 'wpssw_product_spreadsheet_setting', 'no' );
					parent::wpssw_update_option( 'wpssw_product_spreadsheet_id', '' );
					return;
				}

				if ( isset( $_POST['import_checkbox'] ) ) {
					parent::wpssw_update_option( 'wpssw_product_import', 1 );

				} else {

					parent::wpssw_update_option( 'wpssw_product_import', '' );

				}

				if ( isset( $_POST['insert_checkbox'] ) ) {

					parent::wpssw_update_option( 'wpssw_product_insert', 1 );

				} else {

					parent::wpssw_update_option( 'wpssw_product_insert', '' );

				}

				if ( isset( $_POST['update_checkbox'] ) ) {

					parent::wpssw_update_option( 'wpssw_product_update', 1 );

				} else {

					parent::wpssw_update_option( 'wpssw_product_update', '' );

				}

				if ( isset( $_POST['delete_checkbox'] ) ) {

					parent::wpssw_update_option( 'wpssw_product_delete', 1 );

				} else {

					parent::wpssw_update_option( 'wpssw_product_delete', '' );

				}

				$wpssw_sheetname      = 'All Products';
				$requestarray         = array();
				$deleterequestarray   = array();
				$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
				$wpssw_existingsheets = array_flip( $wpssw_existingsheets );
				$wpssw_inputoption    = parent::wpssw_option( 'wpssw_inputoption' );
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}
				if ( count( $wpssw_woo_product_headers ) > 0 ) {
					array_unshift( $wpssw_woo_product_headers, 'Product Id', 'Product Variation Id' );
				}
				if ( count( $wpssw_woo_product_headers_custom ) > 0 ) {
					array_unshift( $wpssw_woo_product_headers_custom, 'Product Id', 'Product Variation Id' );
				}

				$wpssw_old_header_order = parent::wpssw_option( 'wpssw_woo_product_headers' );
				if ( empty( $wpssw_old_header_order ) ) {
					$wpssw_old_header_order = array();
				}
				if ( count( $wpssw_old_header_order ) > 0 ) {
					array_unshift( $wpssw_old_header_order, 'Product Id', 'Product Variation Id' );
				}
				if ( ! in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['sheetname']     = $wpssw_sheetname;
					$wpssw_response         = self::$instance_api->newsheetobject( $param );
					$wpssw_range            = trim( $wpssw_sheetname ) . '!A1';
					$wpssw_requestbody      = self::$instance_api->valuerangeobject( array( $wpssw_woo_product_headers_custom ) );
					$wpssw_params           = array( 'valueInputOption' => $wpssw_inputoption );
					$param                  = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response         = self::$instance_api->appendentry( $param );
				}
				if ( 'new' === (string) sanitize_text_field( wp_unslash( $_POST['product_spreadsheet'] ) ) ) {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$wpssw_response         = self::$instance_api->deletesheetobject( $param );
				}
				if ( $wpssw_old_header_order !== $wpssw_woo_product_headers && in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
					$wpssw_existingsheets = array();
					$response             = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
					$wpssw_existingsheets = self::$instance_api->get_sheet_list( $response );
					$wpssw_existingsheets = array_flip( $wpssw_existingsheets );
					// Delete deactivate column from sheet.
					$wpssw_column = array_diff( $wpssw_old_header_order, $wpssw_woo_product_headers );
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
				if ( $wpssw_old_header_order !== $wpssw_woo_product_headers ) {
					foreach ( $wpssw_woo_product_headers as $key => $hname ) {
						if ( 'Product Id' === (string) $hname || 'Product Variation Id' === (string) $hname ) {
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
					// Freeze product headers.
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
					$wpssw_requestbody = self::$instance_api->valuerangeobject( array( $wpssw_woo_product_headers_custom ) );
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->updateentry( $param );
				}
				parent::wpssw_update_option( 'wpssw_woo_product_headers', array_map( 'sanitize_text_field', wp_unslash( $_POST['wooproduct_header_list'] ) ) );
				parent::wpssw_update_option( 'wpssw_woo_product_headers_custom', array_map( 'sanitize_text_field', wp_unslash( $_POST['wooproduct_custom'] ) ) );
			}
		}
		/**
		 * Update Products
		 *
		 * @param int    $product_id .
		 * @param object $wpssw_product .
		 */
		public static function wpssw_woocommerce_update_product( $product_id, $wpssw_product ) {

			if ( parent::wpssw_is_event_calender_ticket_active() ) {
				// @codingStandardsIgnoreStart.
				if ( isset( $_POST['post_id'] ) && 'tribe_events' === (string) get_post_type( sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) ) ) {
				// @codingStandardsIgnoreEnd.
					return;
				}
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_checked                     = '';
			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			$wpssw_sheetname   = 'All Products';
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			if ( ! empty( $wpssw_spreadsheetid ) ) {
				$wpssw_spreadsheets_list = self::$instance_api->get_spreadsheet_listing();
				if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
					return;
				}
				$product                   = wc_get_product( $product_id );
				$wpssw_total               = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];
				$wpssw_total_values        = $wpssw_total->getValues();
				$variation_productid       = array_search( 'Product Id', $wpssw_total_values[0], true );
				$variation_product_index   = array_column( $wpssw_total_values, $variation_productid );
				$product_added_childs      = array();
				$add_varaition_row         = 0;
				$remove_varaition_row      = 0;
				$product_keys              = array_filter( array_keys( parent::wpssw_convert_int( $variation_product_index ), (int) $product_id, true ) );
				$lastkey_value             = ( array_slice( $product_keys, -1, 1 ) );
				$lastkey                   = '';
				if ( isset( $lastkey_value[0] ) ) {
					$lastkey = $lastkey_value[0];
				}
				if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
					$product_childs = $product->get_children();
					if ( ! empty( $product_keys ) ) {
						if ( count( $product_childs ) > count( $product_keys ) ) {
							$add_varaition_row = count( $product_childs ) - count( $product_keys ) + 1;
						} elseif ( count( $product_childs ) < count( $product_keys ) ) {
							$remove_varaition_row = count( $product_keys ) - count( $product_childs ) - 1;
						}
						if ( $remove_varaition_row > 0 ) {
							if ( $wpssw_sheetid ) {
								$param                = array();
								$startindex           = $product_keys[0];
								$endindex             = $product_keys[0] + $remove_varaition_row;
								$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
								$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
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
					}
					$wpssw_array_value   = array();
					$wpssw_array_value[] = self::wpssw_make_product_value_array( 'insert', $product_id );
					foreach ( $product->get_children() as $child ) {
						$wpssw_child_array   = self::wpssw_make_product_value_array( 'insert', $child, true );
						$wpssw_array_value[] = $wpssw_child_array;
					}
				} else {
					if ( 'variable' !== (string) $product->get_type() || ( empty( $product->get_children() ) && 'variable' === (string) $product->get_type() ) ) {
						if ( count( $product_keys ) > 1 ) {
							if ( $wpssw_sheetid ) {
								$param                = array();
								$deleterequestarray   = array();
								$startindex           = $product_keys[0];
								$endindex             = $product_keys[0] + count( $product_keys ) - 1;
								$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
								$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
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
					}
					$wpssw_values = self::wpssw_make_product_value_array( 'insert', $product_id );
				}
				if ( $add_varaition_row > 0 && ! empty( $product_keys ) ) {
					$insert      = 0;
					$start_index = $lastkey + 1;
					$end_index   = $lastkey + $add_varaition_row + 1;
					if ( $wpssw_sheetid ) {
						$param          = array();
						$param          = self::$instance_api->prepare_param( $wpssw_sheetid, $start_index, $end_index );
						$requestarray[] = self::$instance_api->insertdimensionrequests( $param, 'ROWS' );
					}
					try {
						if ( ! empty( $requestarray ) ) {
							$param                  = array();
							$param['spreadsheetid'] = $wpssw_spreadsheetid;
							$param['requestarray']  = $requestarray;
							$wpssw_response         = self::$instance_api->updatebachrequests( $param );
						}
					} catch ( Exception $e ) {
						echo esc_html( 'Message: ' . $e->getMessage() );
					}
				}
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
				$wpssw_num         = array_search( (int) $product_id, parent::wpssw_convert_int( $wpssw_data ), true );
				if ( $wpssw_num > 0 ) {
					if ( isset( $wpssw_array_value ) && ! empty( $wpssw_array_value ) ) {
						$wpssw_rangenum      = $wpssw_num + 1;
						$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . $wpssw_rangenum;
						$wpssw_requestbody   = self::$instance_api->valuerangeobject( $wpssw_array_value );
						$wpssw_params        = array( 'valueInputOption' => $wpssw_inputoption ); // USER_ENTERED.
						$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
						$wpssw_response      = self::$instance_api->updateentry( $param );
					} else {
						$wpssw_rangenum      = $wpssw_num + 1;
						$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . $wpssw_rangenum;
						$wpssw_requestbody   = self::$instance_api->valuerangeobject( array( $wpssw_values ) );
						$wpssw_params        = array( 'valueInputOption' => $wpssw_inputoption ); // USER_ENTERED.
						$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
						$wpssw_response      = self::$instance_api->updateentry( $param );
					}
				} else {
					$wpssw_isupdated = 0;
					if ( isset( $wpssw_array_value ) && ! empty( $wpssw_array_value ) ) {
						$requestarray     = array();
						$wpssw_startindex = '';
						$wpssw_endindex   = '';
						if ( count( $wpssw_data ) > 1 ) {
							$wpssw_startindex = count( $wpssw_data );
						}
						$wpssw_startindex  = parent::wpssw_insert_blankrow( $wpssw_spreadsheetid, $wpssw_sheetid, $product_id, $wpssw_array_value, $wpssw_data, $wpssw_startindex );
						$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_array_value );
						$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
						if ( count( $wpssw_data ) > 1 ) {
							if ( ! $wpssw_startindex ) {
								$wpssw_startindex = count( $wpssw_data );
							}
							$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . ( $wpssw_startindex + 1 );
							$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
							$wpssw_response      = self::$instance_api->updateentry( $param );
							$wpssw_isupdated     = 1;
							$i++;
						}
						if ( 0 === (int) $wpssw_isupdated ) {
							$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_sheetname, $wpssw_requestbody, $wpssw_params );
							$wpssw_response = self::$instance_api->appendentry( $param );
						}
					} else {
						$requestarray     = array();
						$wpssw_startindex = '';
						$wpssw_endindex   = '';
						if ( count( $wpssw_data ) > 1 ) {
							$wpssw_startindex = count( $wpssw_data );
						}
						$wpssw_startindex  = parent::wpssw_insert_blankrow( $wpssw_spreadsheetid, $wpssw_sheetid, $product_id, $wpssw_values, $wpssw_data, $wpssw_startindex );
						$wpssw_requestbody = self::$instance_api->valuerangeobject( array( $wpssw_values ) );
						$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
						if ( $wpssw_startindex ) {
							$wpssw_rangetoupdate = $wpssw_sheetname . '!A' . ( $wpssw_startindex + 1 );
							$param               = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_rangetoupdate, $wpssw_requestbody, $wpssw_params );
							$wpssw_response      = self::$instance_api->updateentry( $param );
							$wpssw_isupdated     = 1;
						}
						if ( 0 === (int) $wpssw_isupdated ) {
							$param          = array();
							$param          = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_sheetname, $wpssw_requestbody, $wpssw_params );
							$wpssw_response = self::$instance_api->appendentry( $param );
						}
					}
				}
			}
			remove_action( 'woocommerce_update_product', __CLASS__ . '::wpssw_woocommerce_update_product', 10, 2 );
		}
		/**
		 * Get products count for syncronization
		 */
		public static function wpssw_get_product_count() {
			if ( ! isset( $_POST['wpssw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_product_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_sheetname                   = 'All Products';
			$args                              = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
			);
			$products                          = new WP_Query( $args );
			$total                             = $products->found_posts;
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_checked                     = '';
			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
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
			while ( $products->have_posts() ) :
				$products->the_post();
				global $product;
				if ( in_array( (int) get_the_ID(), parent::wpssw_convert_int( $wpssw_data ), true ) ) {
					continue;
				}
				$ordercount++;
			endwhile;
			wp_reset_postdata();
			echo wp_json_encode( array( 'totalproducts' => $ordercount ) );
			die;
		}
		/**
		 * Sync Products
		 */
		public static function wpssw_sync_products() {
			if ( ! isset( $_POST['wpssw_product_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_product_settings'] ) ), 'save_product_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_checked                     = '';
			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_sheetname  = 'All Products';
			$wpssw_ordercount = isset( $_POST['ordercount'] ) ? sanitize_text_field( wp_unslash( $_POST['ordercount'] ) ) : '';
			$wpssw_orderlimit = isset( $_POST['orderlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['orderlimit'] ) ) : '';
			$args             = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'orderby'        => 'ID',
				'order'          => 'ASC',
			);
			$products         = new WP_Query( $args );
			if ( empty( $products ) ) {
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
			while ( $products->have_posts() ) :
				$products->the_post();
				global $product;
				if ( in_array( (int) get_the_ID(), parent::wpssw_convert_int( $wpssw_data ), true ) ) {
					continue;
				}
				$product = wc_get_product( get_the_ID() );
				if ( ! empty( $product->get_children() ) && 'grouped' !== (string) $product->get_type() ) {
					$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', get_the_ID() );
					foreach ( $product->get_children() as $child ) {
						$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', $child, true );
					}
				} else {
					$wpssw_values_array[] = self::wpssw_make_product_value_array( 'insert', get_the_ID() );
				}
			endwhile;
			wp_reset_postdata();
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
		 * Clear Product settings sheet
		 */
		public static function wpssw_clear_productsheet() {
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_checked                     = '';
			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				echo 'Please save settings.';
				die();
			}
			$requestbody               = self::$instance_api->clearobject();
			$total_headers             = count( parent::wpssw_option( 'wpssw_woo_product_headers' ) ) + 1;
			$last_column               = parent::wpssw_get_column_index( $total_headers );
			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );
			$wpssw_sheetname           = 'All Products';
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
		 * Prepare array value of product data to insert into sheet.
		 *
		 * @param string  $wpssw_operation operation to perfom on sheet.
		 * @param int     $wpssw_product_id Produt ID.
		 * @param boolean $wpssw_child True if child product.
		 * @return array $product_value_array
		 */
		public static function wpssw_make_product_value_array( $wpssw_operation = 'insert', $wpssw_product_id = 0, $wpssw_child = false ) {
			if ( ! $wpssw_product_id ) {
				return array();
			}
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_product_compatibility_files();
			$wpssw_product                          = wc_get_product( $wpssw_product_id );
			$wpssw_woo_selections                   = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_product_headers' ) );
			$wpssw_headers                          = apply_filters( 'wpsyncsheets_product_headers', array() );
			$wpssw_classarray                       = array();
			$wpssw_headers['WPSSW_Default_Headers'] = parent::wpssw_array_flatten( $wpssw_headers['WPSSW_Default_Headers'] );

			$wpssw_woo_selections_count = count( $wpssw_woo_selections );
			for ( $i = 0; $i < $wpssw_woo_selections_count; $i++ ) {
				$wpssw_classarray[ $wpssw_woo_selections[ $i ] ] = parent::wpssw_find_class( $wpssw_headers, $wpssw_woo_selections[ $i ] );
			}
			$wpssw_product_row = array();
			if ( ! empty( $wpssw_product->get_parent_id() ) && 'grouped' !== (string) $wpssw_product->get_type() ) {
				$pid                 = $wpssw_product->get_parent_id();
				$wpssw_product_row[] = $pid;
			} else {
				$wpssw_product_row[] = $wpssw_product_id;
			}
			if ( ! empty( $wpssw_product->get_parent_id() ) && 'variation' === (string) $wpssw_product->get_type() ) {
				$wpssw_product_row[] = $wpssw_product->get_id();
			} else {
				$wpssw_product_row[] = '';
			}
			foreach ( $wpssw_classarray as $headername => $classname ) {
				if ( ! empty( $classname ) ) {
					$wpssw_product_row[] = $classname::get_value( $headername, $wpssw_product, $wpssw_child );
				} else {
					$wpssw_product_row[] = '';
				}
			}
			$wpssw_product_row = parent::wpssw_cleanarray( $wpssw_product_row, count( $wpssw_woo_selections ) + 1 );
			return $wpssw_product_row;
		}
		/**
		 * Get products all attributes.
		 */
		public static function wpssw_get_all_attributes() {
			global $wpdb;
			$wpssw_attribute_taxonomies = array();
			$table_name                 = $wpdb->prefix;
			$attribute_name             = '';
			// @codingStandardsIgnoreStart.
			$wpssw_attribute_taxonomies = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name != '' ORDER BY attribute_name ASC;" ); // db call ok.
			// @codingStandardsIgnoreEnd.
			set_transient( 'wc_attribute_taxonomies', $wpssw_attribute_taxonomies );
			$wpssw_attribute_taxonomies = array_column( array_filter( $wpssw_attribute_taxonomies ), 'attribute_name' );
			$wpssw_attribute_taxonomies = array_map(
				function( $e ) {
					return str_replace( '-', ' ', ucfirst( str_replace( 'pa_', '', $e ) ) );
				},
				$wpssw_attribute_taxonomies
			);
			$wpssw_product_attr         = self::wpssw_get_meta_values( '_product_attributes' );
			$wpssw_product_attr         = array_map(
				function( $e ) {
					return array_keys( $e );
				},
				$wpssw_product_attr
			);
			$wpssw_product_attr         = self::wpssw_array_flatten( $wpssw_product_attr );
			$wpssw_product_attr         = array_map(
				function( $e ) {
					return str_replace( '-', ' ', ucfirst( str_replace( 'pa_', '', $e ) ) );
				},
				$wpssw_product_attr
			);
			$wpssw_product_attr         = array_unique( $wpssw_product_attr );
			$wpssw_attribute_taxonomies = array_unique( array_merge( $wpssw_attribute_taxonomies, $wpssw_product_attr ) );
			return $wpssw_attribute_taxonomies;
		}
		/**
		 * Get product meta value for wpssw_get_all_attributes function
		 *
		 * @param string $wpssw_meta_key .
		 * @param string $wpssw_post_type .
		 */
		public static function wpssw_get_meta_values( $wpssw_meta_key, $wpssw_post_type = 'product' ) {
			// @codingStandardsIgnoreStart.
			$wpssw_posts       = get_posts(
				array(
					'post_type'      => $wpssw_post_type,
					'meta_key'       => $wpssw_meta_key,
					'posts_per_page' => -1,
				)
			);
			// @codingStandardsIgnoreEnd.
			$wpssw_meta_values = array();
			foreach ( $wpssw_posts as $wpssw_post ) {
				$wpssw_meta = get_post_meta( $wpssw_post->ID, $wpssw_meta_key, true );
				if ( $wpssw_meta ) {
					$wpssw_meta_values[] = $wpssw_meta;
				}
			}
			return $wpssw_meta_values;
		}
	}
	new WPSSW_Product();
endif;
