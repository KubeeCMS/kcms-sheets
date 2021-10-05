<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

// Direct access security.
if ( ! defined( 'WPSSW_PLUGIN_SECURITY' ) ) {
	die();
}
/**
 * Class WPSSW_License.
 */
final class WPSSW_License {
	/**
	 * Instance of this WPSSW_License class
	 *
	 * @var $instance
	 */
	protected static $instance = null;
	/**
	 * Create self Instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_wpssw_activate_license', array( $this, 'wpssw_activate' ) );
		add_action( 'wp_ajax_wpssw_deactivate_license', array( $this, 'wpssw_deactivate' ) );
	}
	/**
	 * Initialization
	 */
	public function init() {
	}
	/**
	 * Check license.
	 *
	 * @return string $wpsswapikey envato apikey
	 */
	public function check_license() {
		$wpsswapikey = get_option( 'wpssw_envato_apikey' );
		return $wpsswapikey;
	}
	/**
	 * Request activation.
	 */
	public function wpssw_activate() {
		$this->wpssw_request( 'activation' );
	}
	/**
	 * Request deactivation.
	 */
	public function wpssw_deactivate() {
		$this->wpssw_request( 'deactivation' );
	}
	/**
	 * Function to Request for activation or deactivation.
	 *
	 * @param string $action activation|deactivation action.
	 */
	public function wpssw_request( $action = '' ) {
		if ( ! isset( $_POST['wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpnonce'] ) ), 'wpssw-special-string' ) ) {
			print 'Sorry, your nonce did not verify.';
			exit;
		} else {
			$id       = WPSSW_PLUGIN_ID;
			$url      = 'https://api.envato.com/v2/market/catalog/item?id=' . $id;
			$api_key  = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';
			$defaults = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'User-Agent'    => 'WordPress - Envato Market 2.0.3',
				),
				'timeout' => 14,
			);
			$args     = wp_parse_args( $args, $defaults );
			$token    = trim( str_replace( 'Bearer', '', $args['headers']['Authorization'] ) );
			if ( empty( $token ) ) {
				return new WP_Error( 'api_token_error', __( 'An API token is required.', 'envato-market' ) );
			}

			$debugging_information = array(
				'request_url' => $url,
			);
			// Make an API request.
			$response     = wp_remote_get( esc_url_raw( $url ), $args );
			$result       = json_decode( $response['body'] );
			$message_wrap = '<div class="%s"><p>%s</p></div>';

			if ( is_wp_error( $response ) || isset( $result->error ) ) {
				$message_wrap = '<div class="%s"><p>%s</p></div>';
				$message      = 'Please enter valid Envato API Token';
				$status       = 'error';
				echo wp_json_encode(
					array(
						'result'  => '-2',
						'message' => sprintf(
							$message_wrap,
							$status,
							$result->error
						),
					)
				);
				die();
			} else {
				update_option( 'wpssw_envato_apikey', $api_key );
				echo wp_json_encode(
					array(
						'result'  => '4',
						'message' => sprintf(
							$message_wrap,
							'updated',
							__( 'Your OAuth Personal Token has been verified.', 'woocommerce-settings-googlesheet' )
						),
					)
				);
				die();
			}
		}
	}
}



