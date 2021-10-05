<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_RP_Donation' ) ) :
	/**
	 * Class WPSSW_RP_Donation.
	 */
	class WPSSW_RP_Donation extends WPSSW_Order_Utils {
		/**
		 * Store header list.
		 *
		 * @var array $wpssw_headers.
		 */
		public static $wpssw_headers = array();
		/**
		 * Class Contructor.
		 */
		public function __construct() {
			if ( $this->wpssw_is_pugin_active() ) {
				$this->prepare_headers();
				add_filter( 'wpsyncsheets_order_headers', __CLASS__ . '::get_header_list', 10, 1 );
			}
		}
		/**
		 * Check if WooCommerce Stripe Payment Gateway plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'rp_donation' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array( 'Tip' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_RP_Donation'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_order order object.
		 */
		public static function get_value( $wpssw_headers_name, $wpssw_order ) {
			return self::prepare_value( $wpssw_headers_name, $wpssw_order );
		}
		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_order order object.
		 */
		public static function prepare_value( $wpssw_headers_name, $wpssw_order ) {
			$wpssw_value = array();
			if ( 'Tip' === (string) $wpssw_headers_name ) {
				$wpssw_insert_val = '';
				foreach ( $wpssw_order->get_items( 'fee' ) as $item_id => $item_fee ) {
					// The fee name.
					$fee_name = $item_fee->get_name();
					if ( 'Tip' === (string) $fee_name ) {
						$wpssw_insert_val = $item_fee->get_total();
					}
				}
				$wpssw_value[] = $wpssw_insert_val;
			}
			return $wpssw_value;
		}
	}
	new WPSSW_RP_Donation();
endif;
