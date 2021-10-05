<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Event' ) ) :
	/**
	 * Class WPSSW_Event.
	 */
	class WPSSW_Event extends WPSSW_Setting {
		/**
		 * Initialization
		 */
		public function __construct() {
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_event_ajax_hook();
		}
		/**
		 * Save event settings tab's settings.
		 */
		public static function wpssw_update_event_settings() {
			if ( ! isset( $_POST['wpssw_event_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_event_settings'] ) ), 'save_event_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( isset( $_POST['wooevent_header_list'] ) && isset( $_POST['wooevent_custom'] ) ) {
				$wpssw_woo_event_headers        = array_map( 'sanitize_text_field', wp_unslash( $_POST['wooevent_header_list'] ) );
				$wpssw_woo_event_headers_custom = array_map( 'sanitize_text_field', wp_unslash( $_POST['wooevent_custom'] ) );
				if ( isset( $_POST['event_settings_checkbox'] ) ) {
					$wpssw_spreadsheetname = self::wpssw_create_eventsheets( $_POST );
					$wpssw_sheetname_list  = array();
					if ( isset( $_POST['event_categories_sheet'] ) && ! empty( $_POST['event_categories_sheet'] ) ) {
						$wpssw_sheetname_list = array_map( 'sanitize_text_field', wp_unslash( $_POST['event_categories_sheet'] ) );
					}
					parent::wpssw_update_option( 'wpssw_eventsheets_list', $wpssw_sheetname_list );
					parent::wpssw_update_option( 'wpssw_event_spreadsheet_id', $wpssw_spreadsheetname );
					parent::wpssw_update_option( 'wpssw_event_spreadsheet_setting', 'yes' );
				} else {
					parent::wpssw_update_option( 'wpssw_event_spreadsheet_setting', 'no' );
					parent::wpssw_update_option( 'wpssw_event_spreadsheet_id', '' );
					return;
				}
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetname );
				$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
				$wpssw_existingsheets      = array_flip( $wpssw_existingsheetsnames );
				$wpssw_inputoption         = parent::wpssw_option( 'wpssw_inputoption' );
				if ( ! $wpssw_inputoption ) {
					$wpssw_inputoption = 'USER_ENTERED';
				}
				if ( count( $wpssw_woo_event_headers ) > 0 ) {
					array_unshift( $wpssw_woo_event_headers, 'Order Id' );
				}
				if ( count( $wpssw_woo_event_headers_custom ) > 0 ) {
					array_unshift( $wpssw_woo_event_headers_custom, 'Order Id' );
				}
				$wpssw_old_header = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( empty( $wpssw_old_header ) && ! is_array( $wpssw_old_header ) ) {
					$wpssw_old_header = array();
				}
				if ( count( $wpssw_old_header ) > 0 ) {
					array_unshift( $wpssw_old_header, 'Order Id' );
				}
				$wpssw_color_code_enable   = parent::wpssw_option( 'wpssw_color_code' );
				$wpssw_oddcolor            = parent::wpssw_option( 'wpssw_oddcolor' );
				$wpssw_evencolor           = parent::wpssw_option( 'wpssw_evencolor' );
				$freeze_header             = parent::wpssw_option( 'freeze_header' );
				$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetname );
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
				foreach ( $wpssw_sheetname_list as $wpssw_sheetname ) {
					if ( in_array( $wpssw_sheetname, $wpssw_existingsheets, true ) ) {
						$wpssw_sheetid = array_search( $wpssw_sheetname, $wpssw_existingsheets, true );
						// Freeze event headers.
						$wpssw_requestbody = self::$instance_api->freezeobject( $wpssw_sheetid, $wpssw_freeze );
						try {
							$requestobject                  = array();
							$requestobject['spreadsheetid'] = $wpssw_spreadsheetname;
							$requestobject['requestbody']   = $wpssw_requestbody;
							self::$instance_api->formatsheet( $requestobject );
						} catch ( Exception $e ) {
							echo esc_html( 'Message: ' . $e->getMessage() );
						}
						// Row background color.
						parent::wpssw_change_row_background_color( $wpssw_spreadsheetname, $wpssw_sheetid, $oddcolor, $evencolor );
					}
				}
				parent::wpssw_update_option( 'wpssw_woo_event_headers', array_map( 'sanitize_text_field', wp_unslash( $_POST['wooevent_header_list'] ) ) );
				parent::wpssw_update_option( 'wpssw_woo_event_headers_custom', array_map( 'sanitize_text_field', wp_unslash( $_POST['wooevent_custom'] ) ) );
			}
		}
		/**
		 * Create new event categories sheet into general settings spreadsheet
		 *
		 * @param array $wpssw_data .
		 */
		public static function wpssw_create_eventsheets( $wpssw_data ) {
			$wpssw_inputoption = parent::wpssw_option( 'wpssw_inputoption' );
			$wpssw_event_array = array();
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_sheet_headers_list        = parent::wpssw_option( 'wpssw_sheet_headers_list' );
			$wpssw_sheet_headers_list_custom = parent::wpssw_option( 'wpssw_sheet_headers_list_custom' );
			if ( ! empty( $wpssw_data['wooevent_header_list'] ) ) {
				$wpssw_header        = array();
				$wpssw_header_custom = array();
				foreach ( $wpssw_data['wooevent_header_list'] as $headers ) {
					$wpssw_header[]      = $headers;
					$wpssw_header_custom = $wpssw_data['wooevent_custom'];
				}
				$wpssw_headers       = stripslashes_deep( $wpssw_header );
				$wpssw_header_custom = stripslashes_deep( $wpssw_header_custom );
			} else {
				$wpssw_headers       = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_event_headers' ) );
				$wpssw_header_custom = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_event_headers_custom' ) );
				if ( ! is_array( $wpssw_headers ) ) {
					$wpssw_headers = array();
				}
				if ( ! is_array( $wpssw_header_custom ) ) {
					$wpssw_header_custom = array();
				}
			}
			$wpssw_headers       = array_merge( $wpssw_sheet_headers_list, $wpssw_headers );
			$wpssw_header_custom = array_merge( $wpssw_sheet_headers_list_custom, $wpssw_header_custom );
			if ( count( $wpssw_headers ) > 0 ) {
				array_unshift( $wpssw_headers, 'Order Id' );
				$wpssw_value = array( $wpssw_headers );
			}
			if ( count( $wpssw_header_custom ) > 0 ) {
				array_unshift( $wpssw_header_custom, 'Order Id' );
				$wpssw_value_custom = array( $wpssw_header_custom );
			}
			$wpssw_remove_sheet      = array();
			$wpssw_sheetnames        = array();
			$wpssw_sheet_list        = array();
			$wpssw_all_sheetnames    = array();
			$wpssw_events_cat        = array();
			$wpssw_events_cat        = get_terms(
				array(
					'taxonomy'   => 'tribe_events_cat',
					'hide_empty' => 0,
				)
			);
			$wpssw_events_categories = array();
			foreach ( $wpssw_events_cat as $cat ) {
				$wpssw_events_categories[] = $cat->name;
			}
			if ( isset( $wpssw_data['event_categories_sheet'] ) && ! empty( $wpssw_data['event_categories_sheet'] ) ) {
				$wpssw_sheet_list = $wpssw_data['event_categories_sheet'];
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_response      = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			foreach ( $wpssw_response->getSheets() as $wpssw_key => $wpssw_value ) {
				$wpssw_existingsheetsnames[ $wpssw_value['properties']['title'] ] = $wpssw_value['properties']['sheetId'];
				if ( ! in_array( $wpssw_value['properties']['title'], $wpssw_sheet_list, true ) && in_array( $wpssw_value['properties']['title'], $wpssw_events_categories, true ) ) {
					$wpssw_remove_sheet[] = $wpssw_value['properties']['title'];
				}
			}
			$i                              = 0;
			$wpssw_old_header               = array();
			$wpssw_newsheet                 = 0;
			$wpssw_general_sheetnames       = array( 'Pending Orders', 'Processing Orders', 'On Hold Orders', 'Completed Orders', 'Cancelled Orders', 'Refunded Orders', 'Failed Orders', 'Trash Orders', 'All Orders' );
			$wpssw_general_sheets           = array();
			$wpssw_general_sheetnames_count = count( $wpssw_general_sheetnames );
			for ( $i = 0; $i < $wpssw_general_sheetnames_count; $i++ ) {
				$i = (int) $i;
				if ( ( 0 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'pending_orders' ) ) ) {
					$wpssw_general_sheets[] = 'Pending Orders';
				} elseif ( ( 1 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'processing_orders' ) ) ) {
					$wpssw_general_sheets[] = 'Processing Orders';
				} elseif ( ( 2 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'on_hold_orders' ) ) ) {
					$wpssw_general_sheets[] = 'On Hold Orders';
				} elseif ( ( 3 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'completed_orders' ) ) ) {
					$wpssw_general_sheets[] = 'Completed Orders';
				} elseif ( ( 4 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'cancelled_orders' ) ) ) {
					$wpssw_general_sheets[] = 'Cancelled Orders';
				} elseif ( ( 5 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'refunded_orders' ) ) ) {
					$wpssw_general_sheets[] = 'Refunded Orders';
				} elseif ( ( 6 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'failed_orders' ) ) ) {
					$wpssw_general_sheets[] = 'Failed Orders';
				} elseif ( ( 7 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'trash' ) ) ) {
					$wpssw_general_sheets[] = 'Trash Orders';
				} elseif ( ( 8 === $i ) && ( 'yes' === (string) parent::wpssw_option( 'all_orders' ) ) ) {
					$wpssw_general_sheets[] = 'All Orders';
				}
			}

			/*
			*Custom Order Status sheet setting
			*/
			$wpssw_custom_status_array = array();
			$wpssw_status_array        = wc_get_order_statuses();
			foreach ( $wpssw_status_array as $wpssw_key => $wpssw_val ) {
				$wpssw_status = substr( $wpssw_key, strpos( $wpssw_key, '-' ) + 1 );
				if ( ! in_array( $wpssw_status, self::$wpssw_default_status, true ) && 'yes' === (string) parent::wpssw_option( $wpssw_status ) ) {
					$wpssw_general_sheets[] = $wpssw_val . ' Orders';
				}
			}
			$wpssw_sheet_list       = array_merge( $wpssw_sheet_list, $wpssw_general_sheets );
			$wpssw_sheet_list_count = 0;
			foreach ( $wpssw_sheet_list as $sheet ) {
				$wpssw_event_array[ $wpssw_sheet_list_count ] = 1;
				if ( isset( $wpssw_existingsheetsnames[ $sheet ] ) && ! empty( $wpssw_existingsheetsnames[ $sheet ] ) ) {
					$wpssw_old_header = parent::wpssw_option( 'wpssw_woo_event_headers' );
					if ( ! is_array( $wpssw_old_header ) ) {
						$wpssw_old_header = array();
					}
					$wpssw_old_header = array_merge( $wpssw_sheet_headers_list, $wpssw_old_header );
					array_unshift( $wpssw_old_header, 'Order Id' );
					if ( $wpssw_old_header !== $wpssw_headers ) {
						$wpssw_event_array[ $wpssw_sheet_list_count ] = 0;
					}
					$wpssw_all_sheetnames[] = $sheet;
				} else {
					$param                  = array();
					$param['spreadsheetid'] = $wpssw_spreadsheetid;
					$param['sheetname']     = $sheet;
					$wpssw_response         = self::$instance_api->newsheetobject( $param );
					$wpssw_range            = trim( $sheet ) . '!A1';
					$wpssw_requestbody      = self::$instance_api->valuerangeobject( $wpssw_value_custom );
					$wpssw_params           = array( 'valueInputOption' => $wpssw_inputoption );
					$param                  = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response         = self::$instance_api->appendentry( $param );
					$wpssw_newsheet         = 1;
				}
				$wpssw_sheet_list_count++;
			}
			$wpssw_sheetnames          = array();
			$wpssw_sheetnames          = $wpssw_all_sheetnames;
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheets      = array_flip( $wpssw_existingsheetsnames );
			if ( 'new' !== (string) $wpssw_spreadsheetid && 0 !== (int) $wpssw_spreadsheetid ) {
				$requestarray       = array();
				$deleterequestarray = array();
				$wpssw_old_header   = parent::wpssw_option( 'wpssw_woo_event_headers' );
				if ( ! is_array( $wpssw_old_header ) ) {
					$wpssw_old_header = array();
				}
				$wpssw_old_header = array_merge( $wpssw_sheet_headers_list, $wpssw_old_header );
				array_unshift( $wpssw_old_header, 'Order Id' );
				if ( $wpssw_old_header !== $wpssw_headers ) {
					// Delete deactivate column from sheet.
					$wpssw_column = array_diff( $wpssw_old_header, $wpssw_headers );
					if ( ! empty( $wpssw_column ) ) {
						$wpssw_column = array_reverse( $wpssw_column, true );
						foreach ( $wpssw_column as $columnindex => $columnval ) {
							unset( $wpssw_old_header[ $columnindex ] );
							$wpssw_old_header       = array_values( $wpssw_old_header );
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
				if ( $wpssw_old_header !== $wpssw_headers ) {
					foreach ( $wpssw_headers as $key => $hname ) {
						if ( 'Order Id' === (string) $hname ) {
							continue;
						}
						$wpssw_startindex = array_search( $hname, $wpssw_old_header, true );
						if ( false !== $wpssw_startindex && ( isset( $wpssw_old_header[ $key ] ) && $wpssw_old_header[ $key ] !== $hname ) ) {
							unset( $wpssw_old_header[ $wpssw_startindex ] );
							$wpssw_old_header       = array_merge( array_slice( $wpssw_old_header, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header, $key, count( $wpssw_old_header ) - $key ) );
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
							$wpssw_old_header       = array_merge( array_slice( $wpssw_old_header, 0, $key ), array( 0 => $hname ), array_slice( $wpssw_old_header, $key, count( $wpssw_old_header ) - $key ) );
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
				if ( in_array( $wpssw_sheetnames[ $i ], $wpssw_existingsheets, true ) && 0 === (int) $wpssw_event_array[ $i ] ) {
					$wpssw_range       = trim( $wpssw_sheetnames[ $i ] ) . '!A1';
					$wpssw_params      = array( 'valueInputOption' => $wpssw_inputoption );
					$wpssw_requestbody = self::$instance_api->valuerangeobject( $wpssw_value_custom );
					$param             = self::$instance_api->setparamater( $wpssw_spreadsheetid, $wpssw_range, $wpssw_requestbody, $wpssw_params );
					$wpssw_response    = self::$instance_api->updateentry( $param );
				}
			}
			// Delete sheet from spreadsheet on deactivate event category.
			if ( ! empty( $wpssw_remove_sheet ) ) {
				parent::wpssw_delete_sheet( $wpssw_spreadsheetid, $wpssw_remove_sheet, $wpssw_existingsheets );
			}
			return $wpssw_spreadsheetid;
		}
		/**
		 * Clear Event settings sheets
		 */
		public static function wpssw_clear_eventsheet() {
			$wpssw_event_spreadsheet_setting = parent::wpssw_option( 'wpssw_event_spreadsheet_setting' );
			$wpssw_spreadsheetid             = parent::wpssw_option( 'wpssw_event_spreadsheet_id' );

			$wpssw_checked = '';
			if ( 'yes' !== (string) $wpssw_event_spreadsheet_setting ) {
				echo 'Please save settings.';
				die();
			}
			$requestbody              = self::$instance_api->clearobject();
			$wpssw_sheet_headers_list = parent::wpssw_option( 'wpssw_sheet_headers_list' );
			$wpssw_woo_event_headers  = parent::wpssw_option( 'wpssw_woo_event_headers' );
			if ( ! is_array( $wpssw_sheet_headers_list ) ) {
				$wpssw_sheet_headers_list = array();
			}
			if ( ! is_array( $wpssw_woo_event_headers ) ) {
				$wpssw_woo_event_headers = array();
			}
			$total_headers             = count( $wpssw_sheet_headers_list ) + count( $wpssw_woo_event_headers ) + 1;
			$last_column               = parent::wpssw_get_column_index( $total_headers );
			$wpssw_existingsheetsnames = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_existingsheetsnames = array_flip( $wpssw_existingsheetsnames );
			$wpssw_event_sheets        = parent::wpssw_option( 'wpssw_eventsheets_list' );
			if ( ! is_array( $wpssw_event_sheets ) ) {
				$wpssw_event_sheets = array();
			}
			foreach ( $wpssw_event_sheets as $wpssw_sheet ) {
				if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheet ) ) {
					echo 'sheetnotexist';
					die;
				}
				if ( in_array( $wpssw_sheet, $wpssw_existingsheetsnames, true ) ) {
					try {
						$range                  = $wpssw_sheet . '!A2:' . $last_column . '10000';
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
			echo 'successful';
			die();
		}
		/**
		 * Get event orders count for syncronization
		 */
		public static function wpssw_get_events_count() {
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_spreadsheetid       = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_existingsheetsnames = array();
			$wpssw_activesheets        = array();
			$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
			$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
			$wpssw_event_sheets        = parent::wpssw_option( 'wpssw_eventsheets_list' );
			if ( ! is_array( $wpssw_event_sheets ) ) {
				$wpssw_event_sheets = array();
			}
			$wpssw_sheetname          = '';
			$wpssw_event_sheets_count = count( $wpssw_event_sheets );
			$wpssw_getactivesheets    = array();
			for ( $i = 0; $i < $wpssw_event_sheets_count; $i++ ) {
				$i               = (int) $i;
				$wpssw_sheetname = $wpssw_event_sheets[ $i ];
				if ( ! empty( $wpssw_sheetname ) ) {
					if ( ! parent::wpssw_check_sheet_exist( $wpssw_spreadsheetid, $wpssw_sheetname ) ) {
						echo 'sheetnotexist';
						die;
					}
					$wpssw_activesheets[]    = $wpssw_sheetname;
					$wpssw_getactivesheets[] = "'" . $wpssw_sheetname . "'!A:A";
				}
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
			$wpssw_existingevents = array();
			foreach ( $wpssw_response->getValueRanges() as $wpssw_order ) {
				if ( strpos( $wpssw_order->range, "'!A" ) ) {
					$wpssw_rangetitle = explode( "'!A", $wpssw_order->range );
				} else {
					$wpssw_rangetitle = explode( '!A', $wpssw_order->range );
				}
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
				$wpssw_existingevents[ $wpssw_sheettitle ] = $wpssw_data;
			}
			$wpssw_dataarray = array();
			$wpssw_isexecute = 0;
			$response        = array();
			foreach ( $wpssw_event_sheets as $wpssw_sheetname ) {
				$wpssw_query_args  = array(
					'post_type'      => 'shop_order',
					'posts_per_page' => -1,
					'order'          => 'ASC',
					'post_status'    => 'any',
				);
				$wpssw_orders_list = array();
				$wpsswcustom_query = new WP_Query( $wpssw_query_args );
				$wpssw_all_orders  = $wpsswcustom_query->posts;
				if ( empty( $wpssw_all_orders ) ) {
					continue;
				}
				$wpssw_values_array = array();
				$eventcount         = 0;
				foreach ( $wpssw_all_orders as $wpssw_order ) {
					$wpssw_order       = wc_get_order( $wpssw_order->ID );
					$wpssw_ticket_meta = get_post_meta( $wpssw_order->ID );
					if ( isset( $wpssw_ticket_meta['_tribe_has_tickets'] ) && 1 === (int) $wpssw_ticket_meta['_tribe_has_tickets'][0] ) {
						$wpssw_items = $wpssw_order->get_items();
						foreach ( $wpssw_items as $wpssw_item ) {
							$wpssw_ticket      = wc_get_product( $wpssw_item['product_id'] );
							$wpssw_ticket_meta = get_post_meta( $wpssw_item['product_id'] );
							if ( isset( $wpssw_ticket_meta['_tribe_wooticket_for_event'] ) ) {
								$wpssw_eventid = $wpssw_ticket_meta['_tribe_wooticket_for_event'][0];
								$term_obj_list = get_the_terms( $wpssw_eventid, 'tribe_events_cat' );
								if ( ! empty( $term_obj_list ) ) {
									foreach ( $term_obj_list as $term ) {
										if ( $wpssw_sheetname === $term->name ) {

											if ( in_array( (int) $wpssw_order->ID, parent::wpssw_convert_int( $wpssw_existingevents[ $wpssw_sheetname ] ), true ) ) {
												continue;
											}
											if ( ! empty( $wpssw_orders_list ) ) {
												if ( in_array( (int) $wpssw_order->ID, parent::wpssw_convert_int( $wpssw_orders_list ), true ) ) {
													continue;
												}
											}
											$wpssw_orders_list[] = $wpssw_order->ID;
											$eventcount++;
										}
									}
								}
							}
						}
					}
				}
				if ( $eventcount > 0 ) {
					$response[] = array(
						'sheet_name'  => $wpssw_sheetname,
						'totalorders' => $eventcount,
					);
				}
			}
			echo wp_json_encode( $response );
			die;
		}
		/**
		 * Syncronize event orders
		 */
		public static function wpssw_sync_events() {
			if ( ! isset( $_POST['wpssw_event_settings'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpssw_event_settings'] ) ), 'save_event_settings' ) ) {
				echo esc_html__( 'Sorry, your nonce did not verify.', 'wpssw' );
				die();
			}
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_spreadsheetid = parent::wpssw_option( 'wpssw_woocommerce_spreadsheet' );
			$wpssw_inputoption   = parent::wpssw_option( 'wpssw_inputoption' );
			if ( ! $wpssw_inputoption ) {
				$wpssw_inputoption = 'USER_ENTERED';
			}
			$wpssw_sheetname  = isset( $_POST['sheetname'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetname'] ) ) : '';
			$wpssw_ordercount = isset( $_POST['ordercount'] ) ? sanitize_text_field( wp_unslash( $_POST['ordercount'] ) ) : '';
			$wpssw_orderlimit = isset( $_POST['orderlimit'] ) ? sanitize_text_field( wp_unslash( $_POST['orderlimit'] ) ) : '';
			$order_ascdesc    = parent::wpssw_option( 'wpssw_order_ascdesc' );
			$wpssw_query_args = array(
				'post_type'      => 'shop_order',
				'posts_per_page' => -1,
				'order'          => 'ASC',
				'post_status'    => 'any',
			);
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

			$wpssw_values_array = array();
			$wpssw_orders_list  = array();
			$neworder           = 0;
			foreach ( $wpssw_all_orders as $wpssw_order ) {
				$wpssw_order       = wc_get_order( $wpssw_order->ID );
				$wpssw_ticket_meta = get_post_meta( $wpssw_order->ID );

				if ( isset( $wpssw_ticket_meta['_tribe_has_tickets'] ) && 1 === (int) $wpssw_ticket_meta['_tribe_has_tickets'][0] ) {
					$wpssw_items = $wpssw_order->get_items();
					foreach ( $wpssw_items as $wpssw_item ) {
						$wpssw_ticket      = wc_get_product( $wpssw_item['product_id'] );
						$wpssw_ticket_meta = get_post_meta( $wpssw_item['product_id'] );
						if ( isset( $wpssw_ticket_meta['_tribe_wooticket_for_event'] ) ) {
							$wpssw_eventid = $wpssw_ticket_meta['_tribe_wooticket_for_event'][0];
							$term_obj_list = get_the_terms( $wpssw_eventid, 'tribe_events_cat' );

							foreach ( $term_obj_list as $term ) {
								if ( $wpssw_sheetname === $term->name ) {
									if ( in_array( (int) $wpssw_order->ID, parent::wpssw_convert_int( $wpssw_data ), true ) ) {
										continue;
									}
									if ( ! empty( $wpssw_orders_list ) ) {
										if ( in_array( (int) $wpssw_order->ID, parent::wpssw_convert_int( $wpssw_orders_list ), true ) ) {
											continue;
										}
									}

									if ( WPSSW_Order::wpssw_check_product_category( $wpssw_order->ID ) ) {
										continue;
									}

									if ( $neworder < $wpssw_orderlimit ) {
										set_time_limit( 999 );
										$wpssw_order_data = $wpssw_order->get_data();
										$wpssw_status     = $wpssw_order_data['status'];

										$wpssw_value = WPSSW_Order::wpssw_make_value_array( 'insert', $wpssw_order->get_id() );

										$wpssw_values_array = array_merge( $wpssw_values_array, $wpssw_value );

										$neworder++;
										$wpssw_orders_list[] = $wpssw_order->get_id();
									}
								}
							}
						}
					}
				}
			}

			$total       = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
			$numrows     = null !== $total->getValues() ? count( $total->getValues() ) : 0;
			$rangetofind = $wpssw_sheetname . '!A1:A' . $numrows;
			if ( ! empty( $wpssw_values_array ) ) {
				try {
					if ( 'descorder' === (string) $order_ascdesc ) {
						if ( $numrows > 0 ) {
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
			echo 'successful';
			die;
		}

	}
	new WPSSW_Event();
endif;
