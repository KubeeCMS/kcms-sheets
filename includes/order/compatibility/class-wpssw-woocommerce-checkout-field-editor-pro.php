<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_WooCommerce_Checkout_Field_Editor_Pro' ) ) :
	/**
	 * Class WPSSW_WooCommerce_Checkout_Field_Editor_Pro.
	 */
	class WPSSW_WooCommerce_Checkout_Field_Editor_Pro extends WPSSW_Order_Utils {
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
				self::prepare_headers();
				add_filter( 'wpsyncsheets_order_headers', __CLASS__ . '::get_header_list', 10, 1 );
			}
		}
		/**
		 * Check if WooCommerce Checkout Field Editor Pro – By ThemeHigh plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( class_exists( 'THWCFD' ) ) {
				return true;
			}
			return false;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_WooCommerce_Checkout_Field_Editor_Pro'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Prepare Header List.
		 */
		public static function prepare_headers() {
			$wpssw_th_checkout_fields = array();
			$wpssw_billing_fields     = WPSSW_Setting::wpssw_option( 'wc_fields_billing', array() );
			$wpssw_billing_fields     = self::wpssw_get_checkout_field( $wpssw_billing_fields, 1 );
			$wpssw_shipping_fields    = WPSSW_Setting::wpssw_option( 'wc_fields_shipping', array() );
			$wpssw_shipping_fields    = self::wpssw_get_checkout_field( $wpssw_shipping_fields, 1 );
			$wpssw_additional_fields  = WPSSW_Setting::wpssw_option( 'wc_fields_additional', array() );
			$wpssw_additional_fields  = self::wpssw_get_checkout_field( $wpssw_additional_fields, 1 );
			$wpssw_th_checkout_fields = array_merge( $wpssw_billing_fields, $wpssw_shipping_fields, $wpssw_additional_fields );
			self::$wpssw_headers      = $wpssw_th_checkout_fields;
		}
		/**
		 * WooCommerce Checkout Field headers.
		 *
		 * @param array  $wpssw_checkout_fields .
		 * @param string $withkey .
		 * @return array $wpssw_checkout_fields
		 */
		public static function wpssw_get_checkout_field( $wpssw_checkout_fields = array(), $withkey = 0 ) {
			if ( ! empty( $wpssw_checkout_fields ) ) {
				$wpssw_checkout_fields = array_map(
					function( $element ) {
						if ( 1 === (int) $element['custom'] ) {
							return $element['label'] ? $element['label'] : $element['name'];
						}
					},
					$wpssw_checkout_fields
				);
			}
			if ( $withkey ) {
				$wpssw_checkout_fields = array_filter(
					$wpssw_checkout_fields,
					function( $wpssw_value ) {
						return ! is_null( $wpssw_value ) && '' !== $wpssw_value;
					}
				);
			} else {
				$wpssw_checkout_fields = array_values(
					array_filter(
						$wpssw_checkout_fields,
						function( $wpssw_value ) {
							return ! is_null( $wpssw_value ) && '' !== $wpssw_value;
						}
					)
				);
			}
			return $wpssw_checkout_fields;
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
			self::prepare_headers();
			$wpssw_th_checkout_fields = self::$wpssw_headers;
			$th_meta_key              = array_search( $wpssw_headers_name, $wpssw_th_checkout_fields, true );
			$wpssw_value[]            = $wpssw_order->get_meta( '_' . $th_meta_key );
			return $wpssw_value;
		}
	}
	new WPSSW_WooCommerce_Checkout_Field_Editor_Pro();
endif;
