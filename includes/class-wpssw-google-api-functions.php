<?php
/**
 * Main WPSyncSheets_For_WooCommerce\WPSSW_Google_API_Functions namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

/**
 * Google API Method Class
 *
 * @since 1.0.0
 */
class WPSSW_Google_API_Functions extends \WPSSW_Google_API {
	/**
	 * Google Sheet Object
	 *
	 * @var object
	 * @since 1.0.0
	 */
	private static $instance_service = null;
	/**
	 * Google Drive Object
	 *
	 * @var object
	 * @since 1.0.0
	 */
	private static $instance_drive = null;
	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( self::checkcredenatials() ) {
			self::loadobject();
		}
	}
	/**
	 * Load Google API Library.
	 *
	 * @since 1.0.0
	 */
	public function loadobject() {
		self::$instance_service = self::get_client_object();
		self::$instance_drive   = self::get_drive_object();
	}
	/**
	 * Include Google API Library.
	 *
	 * @since 1.0.0
	 */
	public function wpssw_load_library() {
		if ( ! function_exists( 'composerRequired7e59b00b8fddc8385312f117ab4f39c' ) ) {
			require_once WPSSW_PLUGIN_PATH . '/lib/vendor/autoload.php';
		}
	}
	/**
	 * Generate Google Sheet Object.
	 *
	 * @since 1.0.0
	 */
	public function get_client_object() {
		if ( null === self::$instance_service ) {
			$client                 = self::getClient();
			self::$instance_service = new \Google_Service_Sheets( $client );
		}
		return self::$instance_service;
	}
	/**
	 * Regenerate Google Sheet Object.
	 *
	 * @since 1.0.0
	 */
	public function refreshobject() {
		self::$instance_service = null;
		self::get_client_object();
	}
	/**
	 * Regenerate Google Drive Object.
	 *
	 * @since 1.0.0
	 */
	public function get_drive_object() {
		if ( null === self::$instance_drive ) {
			$client               = self::getClient();
			self::$instance_drive = new \Google_Service_Drive( $client );
		}
		return self::$instance_drive;
	}
	/**
	 * Check Google Credenatials.
	 *
	 * @since 1.0.0
	 */
	public function checkcredenatials() {
		$wpssw_google_settings_value = self::wpssw_option( 'wpssw_google_settings' );
		$clientid                    = isset( $wpssw_google_settings_value[0] ) ? $wpssw_google_settings_value[0] : '';
		$clientsecert                = isset( $wpssw_google_settings_value[1] ) ? $wpssw_google_settings_value[1] : '';
		$auth_token                  = isset( $wpssw_google_settings_value[2] ) ? $wpssw_google_settings_value[2] : '';
		if ( empty( $clientid ) || empty( $clientsecert ) || empty( $auth_token ) ) {
			return false;
		} else {
			try {
				if ( self::getClient() ) {
					return true;
				} else {
					return false;
				}
			} catch ( Exception $e ) {
				return false;
			}
		}
	}
	/**
	 * Get meta vlaue.
	 *
	 * @param object $key plugin meta key.
	 * @param string $type boolean value.
	 */
	public static function wpssw_option( $key = '', $type = '' ) {
		$value = parent::wpssw_option( $key, $type );
		return $value;
	}
	/**
	 * Update meta value.
	 *
	 * @param object $key plugin meta key.
	 * @param string $value plugin meta value.
	 */
	public static function wpssw_update_option( $key = '', $value = '' ) {
		$value = parent::wpssw_update_option( $key, $value );
		return $value;
	}
	/**
	 * Generate token for the user and refresh the token if it's expired.
	 *
	 * @param int $flag for getting error code.
	 * @return array
	 */
	public function getClient( $flag = 0 ) {
		$this->wpssw_load_library();
		$wpssw_google_settings_value = self::wpssw_option( 'wpssw_google_settings' );
		$clientid                    = isset( $wpssw_google_settings_value[0] ) ? $wpssw_google_settings_value[0] : '';
		$clientsecert                = isset( $wpssw_google_settings_value[1] ) ? $wpssw_google_settings_value[1] : '';
		$auth_token                  = isset( $wpssw_google_settings_value[2] ) ? $wpssw_google_settings_value[2] : '';
		$client                      = new \Google_Client();
		$client->setApplicationName( 'WPSyncSheets For WooCommerce - WooCommerce Google Spreadsheet Addon' );
		$client->setScopes( \Google_Service_Sheets::SPREADSHEETS_READONLY );
		$client->setScopes( \Google_Service_Drive::DRIVE_METADATA_READONLY );
		$client->addScope( \Google_Service_Sheets::SPREADSHEETS );
		$client->setClientId( $clientid );
		$client->setClientSecret( $clientsecert );
		$client->setRedirectUri( esc_html( admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ) ) );
		$client->setAccessType( 'offline' );
		$client->setApprovalPrompt( 'force' );
		// Load previously authorized credentials from a database.
		try {
			if ( empty( $auth_token ) ) {
				$auth_url = $client->createAuthUrl();
				return $auth_url;
			}
			$wpssw_accesstoken = parent::wpssw_option( 'wpssw_google_accessToken' );
			if ( ! empty( $wpssw_accesstoken ) ) {
				$accesstoken = json_decode( $wpssw_accesstoken, true );
			} else {
				if ( empty( $auth_token ) ) {
					$auth_url = $client->createAuthUrl();
					return $auth_url;
				} else {
					$authcode = trim( $auth_token );
					// Exchange authorization code for an access token.
					$accesstoken = $client->fetchAccessTokenWithAuthCode( $authcode );
					// Store the credentials to disk.
					parent::wpssw_update_option( 'wpssw_google_accessToken', wp_json_encode( $accesstoken ) );
				}
			}
			// Check for invalid token.
			if ( is_array( $accesstoken ) && isset( $accesstoken['error'] ) && ! empty( $accesstoken['error'] ) ) {
				if ( $flag ) {
					return $accesstoken['error'];
				}
				return false;
			}
			$client->setAccessToken( $accesstoken );
			// Refresh the token if it's expired.
			if ( $client->isAccessTokenExpired() ) {
				// save refresh token to some variable.
				$refreshtokensaved = $client->getRefreshToken();
				$client->fetchAccessTokenWithRefreshToken( $client->getRefreshToken() );
				// pass access token to some variable.
				$accesstokenupdated = $client->getAccessToken();
				// append refresh token.
				$accesstokenupdated['refresh_token'] = $refreshtokensaved;
				// Set the new acces token.
				$accesstoken = $refreshtokensaved;
				parent::wpssw_update_option( 'wpssw_google_accessToken', wp_json_encode( $accesstokenupdated ) );
				$accesstoken = json_decode( wp_json_encode( $accesstokenupdated ), true );
				$client->setAccessToken( $accesstoken );
			}
		} catch ( Exception $e ) {
			if ( $flag ) {
				return $e->getMessage();
			} else {
				return false;
			}
		}
		return $client;
	}
	/**
	 * Fetch Spreadsheet list from Google Drive.
	 *
	 * @param array $sheetarray Spreadsheet array.
	 * @since 1.0.0
	 *
	 * @return array.
	 */
	public function get_spreadsheet_listing( $sheetarray = array() ) {

		if ( self::checkcredenatials() ) {
			self::loadobject();
		} else {
			return $sheetarray;
		}
		// Print the names and IDs for up to 10 files.
		$optparams    = array(
			'fields' => 'nextPageToken, files(id, name, mimeType)',
			'q'      => "mimeType='application/vnd.google-apps.spreadsheet' and trashed = false",
		);
		$results      = self::$instance_drive->files->listFiles( $optparams );
		$sheetarray[] = __( 'Select Google Spreeadsheet', 'wpssw' );
		if ( count( $results->getFiles() ) === 0 ) {
			$sheetarray['new'] = __( 'Create New Spreadsheet', 'wpssw' );
		} else {
			foreach ( $results->getFiles() as $file ) {
				$sheetarray[ $file->getId() ] = $file->getName();
			}
			$sheetarray['new'] = __( 'Create New Spreadsheet', 'wpssw' );
		}
		return $sheetarray;
	}
	/**
	 * Retrieve the list of sheets from the Google Spreadsheet.
	 *
	 * @param string $spreadsheetid Spreadsheet id.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function get_sheet_listing( $spreadsheetid = '' ) {
		self::refreshobject();
		return parent::get_sheets( self::$instance_service, $spreadsheetid );
	}
	/**
	 * Fetch row from Google Sheet.
	 *
	 * @param array $spreadsheetid Spreadsheet ID.
	 * @param array $sheetname Sheet Name.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function get_row_list( $spreadsheetid, $sheetname ) {
		self::refreshobject();
		$param                  = array();
		$param['spreadsheetid'] = trim( $spreadsheetid );
		$param['sheetname']     = trim( $sheetname );
		return parent::get_values( self::$instance_service, $param );
	}
	/**
	 * Create sheet array.
	 *
	 * @param object $response_object google sheet object.
	 * @since 1.0.0
	 *
	 * @return array.
	 */
	public function get_sheet_list( $response_object ) {
		$sheets = array();
		foreach ( $response_object->getSheets() as $key => $value ) {
			$sheets[ $value['properties']['title'] ] = $value['properties']['sheetId'];
		}
		return $sheets;
	}
	/**
	 * Create deleteDimension Object.
	 *
	 * @param array  $param contains sheetid,startindex,endindex.
	 * @param string $dimension either COLUMN or ROW for request dimension.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function deleteDimensionrequests( $param = array(), $dimension = 'COLUMNS' ) {
		$requests = array(
			'deleteDimension' => array(
				'range' => array(
					'sheetId'    => $param['sheetid'],
					'dimension'  => $dimension,
					'startIndex' => $param['startindex'],
					'endIndex'   => $param['endindex'],
				),
			),
		);
		return $requests;
	}
	/**
	 * Create insertDimension Object.
	 *
	 * @param array $param contains sheetid,startindex,endindex.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function insertdimensionobject( $param = array() ) {
		$requests           = $this->insertdimensionrequests( $param, 'ROWS' );
		$batchupdaterequest = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => $requests,
			)
		);
		return $batchupdaterequest;
	}
	/**
	 * Freeze Row Object
	 *
	 * @param int $sheetid Sheet ID.
	 * @param int $wpssw_freeze 0 - Unfreeze Row, 1 - Freeze Row.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function freezeobject( $sheetid = 0, $wpssw_freeze = 0 ) {
		$requestbody = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => array(
					'updateSheetProperties' => array(
						'properties' => array(
							'sheetId'        => $sheetid,
							'gridProperties' => array(
								'frozenRowCount' => $wpssw_freeze,
							),
						),
						'fields'     => 'gridProperties.frozenRowCount',
					),
				),
			)
		);
		return $requestbody;
	}
	/**
	 * Google_Service_Sheets_Spreadsheet Object
	 *
	 * @param string $spreadsheetname Spreadsheet Name.
	 * @param string $sheetname Sheet Name.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function newspreadsheetobject( $spreadsheetname = '', $sheetname = '' ) {
		$requestbody = new \Google_Service_Sheets_Spreadsheet(
			array(
				'properties' => array(
					'title' => $spreadsheetname,
				),
				'sheets'     => array(
					'properties' => array(
						'title' => $sheetname,
					),
				),
			)
		);
		return $requestbody;
	}
	/**
	 * Prepare parameter array.
	 *
	 * @param string $spreadsheetid Spreadsheet Name.
	 * @param string $range Sheet Name.
	 * @param array  $requestbody requestbody param.
	 * @param array  $params array.
	 * @since 1.0.0
	 *
	 * @return array.
	 */
	public function setparamater( $spreadsheetid = '', $range = '', $requestbody = array(), $params = array() ) {
		$param                  = array();
		$param['spreadsheetid'] = $spreadsheetid;
		$param['range']         = $range;
		$param['requestbody']   = $requestbody;
		$param['params']        = $params;
		return $param;
	}
	/**
	 * Prepare parameter array.
	 *
	 * @param int $sheetid Sheet ID.
	 * @param int $startindex Start Index.
	 * @param int $endindex End Index.
	 * @since 1.0.0
	 *
	 * @return array.
	 */
	public function prepare_param( $sheetid, $startindex, $endindex ) {
		$param               = array();
		$param['sheetid']    = $sheetid;
		$param['startindex'] = $startindex;
		$param['endindex']   = $endindex;
		return $param;
	}
	/**
	 * Google_Service_Sheets_Spreadsheet Object
	 *
	 * @param string $spreadsheetname Spreadsheet Name.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function createspreadsheetobject( $spreadsheetname = '' ) {
		$wpsse_requestbody = new \Google_Service_Sheets_Spreadsheet(
			array(
				'properties' => array(
					'title' => $spreadsheetname,
				),
			)
		);
		return $wpsse_requestbody;
	}
	/**
	 * Create new sheet
	 *
	 * @param string $param .
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function newsheetobject( $param = array() ) {
		$batchupdaterequest   = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => array(
					'addSheet' => array(
						'properties' => array(
							'title' => $param['sheetname'],
						),
					),
				),
			)
		);
		$param['requestbody'] = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $param );
	}
	/**
	 * Update batch requests.
	 *
	 * @param array $param contains requests.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function updatebachrequests( $param = array() ) {
		$batchupdaterequest             = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => $param['requestarray'],
			)
		);
		$requestobject['spreadsheetid'] = $param['spreadsheetid'];
		$requestobject['requestbody']   = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $requestobject );
	}
	/**
	 * Create moveDimension Object.
	 *
	 * @param array $param contains sheetid,startindex,endindex,destinationIndex.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function moveDimensionrequests( $param = array() ) {
		$requests = new \Google_Service_Sheets_Request(
			array(
				'moveDimension' => array(
					'source'           => array(
						'dimension'  => 'COLUMNS',
						'sheetId'    => $param['sheetid'],
						'startIndex' => $param['startindex'],
						'endIndex'   => $param['endindex'],
					),
					'destinationIndex' => $param['destindex'],
				),
			)
		);
		return $requests;
	}
	/**
	 * Create insertDimension Object.
	 *
	 * @param array  $param contains sheetid,startindex,endindex.
	 * @param string $dimension either COLUMN or ROW for request dimension.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function insertdimensionrequests( $param = array(), $dimension = 'COLUMNS' ) {
		$requests = array(
			'insertDimension' => array(
				'range'             => array(
					'sheetId'    => $param['sheetid'],
					'dimension'  => $dimension,
					'startIndex' => $param['startindex'],
					'endIndex'   => $param['endindex'],
				),
				'inheritFromBefore' => true,
			),
		);
		return $requests;
	}
	/**
	 * Get Values from multiple sheets.
	 *
	 * @param array $param contains spreadsheetid,ranges.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function getbatchvalues( $param = array() ) {
		return parent::batchget( self::$instance_service, $param );
	}
	/**
	 * Create Google_Service_Sheets_ValueRange Object.
	 *
	 * @param array $values_data Values Array.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function valuerangeobject( $values_data = array() ) {
		$requestbody = new \Google_Service_Sheets_ValueRange( array( 'values' => $values_data ) );
		return $requestbody;
	}
	/**
	 * Create Google_Service_Sheets_ClearValuesRequest Object.
	 *
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function clearobject() {
		$requestbody = new \Google_Service_Sheets_ClearValuesRequest();
		return $requestbody;
	}
	/**
	 * Insert new column, Freeze first row to google spreadsheet.
	 *
	 * @param array $param contains spreadsheetid,requestbody.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function formatsheet( $param = array() ) {
		return parent::batchupdate( self::$instance_service, $param );
	}
	/**
	 * Update entry to google sheet.
	 *
	 * @param array $param contains spreadsheetid, range, requestbody, params.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function updateentry( $param = array() ) {
		return parent::update_entry( self::$instance_service, $param );
	}
	/**
	 * Append entry to google sheet.
	 *
	 * @param array $param contains spreadsheetid, range, requestbody, params.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function appendentry( $param = array() ) {
		return parent::append_entry( self::$instance_service, $param );
	}
	/**
	 * Create new spreadsheet in Google Drive.
	 *
	 * @param array $requestbody requestbody object.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function createspreadsheet( $requestbody = array() ) {
		return parent::create_spreadsheet( self::$instance_service, $requestbody );
	}
	/**
	 * Clear Sheet Value.
	 *
	 * @param array $param spreadsheetid,sheetname,requestbody.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function clear( $param = array() ) {
		return parent::clearsheet( self::$instance_service, $param );
	}
	/**
	 * Delete embeded object
	 *
	 * @param string $param .
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function deleteembeddedobject( $param = array() ) {
		$batchupdaterequest   = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => array(
					'deleteEmbeddedObject' => array(
						'objectId' => $param['chart_ID'],
					),
				),
			)
		);
		$param['requestbody'] = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $param );
	}
	/**
	 * Create AddChart object.
	 *
	 * @param string $param .
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function addchartobject( $param = array() ) {
		$batchupdaterequest   = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => array(
					'addChart' => array(
						'chart' => array(
							'spec'     => array(
								'title'      => $param['graph_title'],
								'basicChart' => array(
									'chartType'      => $param['graph_type'],
									'legendPosition' => 'BOTTOM_LEGEND',
									'axis'           => array(
										array(
											'position' => 'BOTTOM_AXIS',
											'title'    => $param['bottom_axisname'],
										),
										array(
											'position' => 'LEFT_AXIS',
											'title'    => $param['left_axisname'],
										),
									),
									'domains'        => array(
										'domain' => array(
											'sourceRange' => array(
												'sources' => array(
													'sheetId' => $param['graph_sheetID'],
													'startRowIndex' => $param['startRowIndex'],
													'endRowIndex' => $param['endRowIndex'],
													'startColumnIndex' => 0,
													'endColumnIndex' => 1,
												),
											),
										),
									),
									'series'         => array(
										'series'     => array(
											'sourceRange' => array(
												'sources' => array(
													'sheetId' => $param['graph_sheetID'],
													'startRowIndex' => $param['startRowIndex'],
													'endRowIndex' => $param['endRowIndex'],
													'startColumnIndex' => 1,
													'endColumnIndex' => $param['endColumnIndex'],
												),
											),
										),
										'targetAxis' => 'LEFT_AXIS',
									),
									'headerCount'    => 1,
								),
							),
							'position' => array(
								'overlayPosition' => array(
									'anchorCell'   => array(
										'sheetId'     => $param['graph_sheetID'],
										'rowIndex'    => $param['row_overlayPosition'],
										'columnIndex' => 1,
									),
									'widthPixels'  => 1215,
									'heightPixels' => 450,
								),
							),
						),
					),
				),
			)
		);
		$param['requestbody'] = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $param );
	}
	/**
	 * Delete default sheet
	 *
	 * @param string $param .
	 * @param int    $sheetid Sheet id.
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function deletesheetobject( $param = array(), $sheetid = 0 ) {
		$batchupdaterequest   = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => array(
					'deleteSheet' => array(
						'sheetId' => $sheetid,
					),
				),
			)
		);
		$param['requestbody'] = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $param );
	}
	/**
	 * Create Conditional Format Rule object.
	 *
	 * @param string $param .
	 * @since 1.0.0
	 *
	 * @return object.
	 */
	public function addconditionalformatrule( $param = array() ) {
		$requests             = array(
			new Google_Service_Sheets_Request(
				array(
					'addConditionalFormatRule' => array(
						'rule' => array(
							'ranges'      => array( $param['range'] ),
							'booleanRule' => array(
								'condition' => array(
									'type'   => 'CUSTOM_FORMULA',
									'values' => array( array( 'userEnteredValue' => '=MOD($A:$A;2)=1' ) ),
								),
								'format'    => array(
									'backgroundColor' => array(
										'red'   => $param['r'] / 255,
										'green' => $param['g'] / 255,
										'blue'  => $param['b'] / 255,
									),
								),
							),
						),
					),
				)
			),
			new Google_Service_Sheets_Request(
				array(
					'addConditionalFormatRule' => array(
						'rule' => array(
							'ranges'      => array( $param['range'] ),
							'booleanRule' => array(
								'condition' => array(
									'type'   => 'CUSTOM_FORMULA',
									'values' => array( array( 'userEnteredValue' => '=MOD($A:$A;2)=0' ) ),
								),
								'format'    => array(
									'backgroundColor' => array(
										'red'   => $param['er'] / 255,
										'green' => $param['eg'] / 255,
										'blue'  => $param['eb'] / 255,
									),
								),
							),
						),
					),
				)
			),
			new Google_Service_Sheets_Request(
				array(
					'addConditionalFormatRule' => array(
						'rule' => array(
							'ranges'      => array( $param['range'] ),
							'booleanRule' => array(
								'condition' => array(
									'type'   => 'CUSTOM_FORMULA',
									'values' => array( array( 'userEnteredValue' => '=$A:$A=""' ) ),
								),
								'format'    => array(
									'backgroundColor' => array(
										'red'   => 255 / 255,
										'green' => 255 / 255,
										'blue'  => 255 / 255,
									),
								),
							),
						),
					),
				)
			),
		);
		$batchupdaterequest   = new \Google_Service_Sheets_BatchUpdateSpreadsheetRequest(
			array(
				'requests' => $requests,
			)
		);
		$param['requestbody'] = $batchupdaterequest;
		return parent::batchupdate( self::$instance_service, $param );
	}
}
