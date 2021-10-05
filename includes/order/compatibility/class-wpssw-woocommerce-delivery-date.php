<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WooCommerce_Delivery_Date' ) ) :
	/**
	 * Class WPSSW_WooCommerce_Delivery_Date.
	 */
	class WPSSW_WooCommerce_Delivery_Date extends WPSSW_Order_Utils {
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
		 * Check if WooCommerce Order Delivery Date plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( ! class_exists( 'order_delivery_date' ) ) {
				return false;
			}
			return true;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = WPSSW_Setting::wpssw_option( 'orddd_delivery_date_field_label' );
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WooCommerce_Delivery_Date'] = self::$wpssw_headers;
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
			$wpssw_value          = array();
			$wpssw_delivery_date  = '';
			$wpssw_delivery_label = WPSSW_Setting::wpssw_option( 'orddd_delivery_date_field_label' );
			if ( ! empty( $wpssw_delivery_label ) ) {
				$wpssw_delivery_date = get_post_meta( $wpssw_order->get_id(), $wpssw_delivery_label, true );
			}
			if ( (string) $wpssw_headers_name === $wpssw_delivery_label ) {
				$wpssw_value[] = $wpssw_delivery_date;
			}
			return $wpssw_value;
		}
	}
	new WPSSW_WooCommerce_Delivery_Date();
endif;
