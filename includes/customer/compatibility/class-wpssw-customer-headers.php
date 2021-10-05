<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_Customer_Headers' ) ) :
	/**
	 * Class WPSSW_Customer_Headers.
	 */
	class WPSSW_Customer_Headers extends WPSSW_Customer_Utils {
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
			$this->prepare_headers();
			add_filter( 'wpsyncsheets_customer_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array( 'Customer Username', 'Customer Role', 'Customer FirstName', 'Customer LastName', 'Customer Nickname', 'Customer EmailID', 'Customer Website', 'Customer Biographical Info', 'Customer Profile Image', 'Billing FirstName', 'Billing LastName', 'Billing Company Name', 'Billing Address1', 'Billing Address2', 'Billing City', 'Billing Postcode / ZIP', 'Billing Country', 'Billing State', 'Billing Phone Number', 'Billing EmailID', 'Shipping FirstName', 'Shipping LastName', 'Shipping Company Name', 'Shipping Address1', 'Shipping Address2', 'Shipping City', 'Shipping Postcode / ZIP', 'Shipping Country', 'Shipping State' );
		}

		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Customer_Headers'] = self::$wpssw_headers;
			}
			return $headers;
		}

		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param array  $customers_metadata metadata array of customer.
		 */
		public static function get_value( $wpssw_headers_name, $customers_metadata ) {
			return self::prepare_value( $wpssw_headers_name, $customers_metadata );
		}

		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param array  $customers_metadata metadata array of customer.
		 */
		public static function prepare_value( $wpssw_headers_name, $customers_metadata ) {

			$wpssw_value = '';
			if ( 'Customer Username' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $customers_metadata['user_login'];
				return $wpssw_value;
			}
			if ( 'Customer Role' === (string) $wpssw_headers_name ) {
				$role        = $customers_metadata['roles'];
				$wpssw_value = $role[0];
				return $wpssw_value;
			}
			if ( 'Customer FirstName' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $customers_metadata['first_name'][0];
				return $wpssw_value;
			}
			if ( 'Customer LastName' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $customers_metadata['last_name'][0];
				return $wpssw_value;
			}
			if ( 'Customer Nickname' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $customers_metadata['nickname'][0];
				return $wpssw_value;
			}
			if ( 'Customer EmailID' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $customers_metadata['user_email'];
				return $wpssw_value;
			}
			if ( 'Customer Website' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $customers_metadata['user_url'];
				return $wpssw_value;
			}
			if ( 'Customer Biographical Info' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $customers_metadata['description'][0];
				return $wpssw_value;
			}
			if ( 'Customer Profile Image' === (string) $wpssw_headers_name ) {
				$wpssw_value = $customers_metadata['profile_image'];
				return $wpssw_value;
			}
			if ( 'Billing FirstName' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_first_name'] ) && ! empty( $customers_metadata['billing_first_name'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_first_name'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing LastName' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_last_name'] ) && ! empty( $customers_metadata['billing_last_name'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_last_name'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing Company Name' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_company'] ) && ! empty( $customers_metadata['billing_company'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_company'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing Address1' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_address_1'] ) && ! empty( $customers_metadata['billing_address_1'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_address_1'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing Address2' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_address_2'] ) && ! empty( $customers_metadata['billing_address_2'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_address_2'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing City' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_city'] ) && ! empty( $customers_metadata['billing_city'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_city'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing Postcode / ZIP' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_postcode'] ) && ! empty( $customers_metadata['billing_postcode'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_postcode'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing Country' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_country'] ) && ! empty( $customers_metadata['billing_country'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_country'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing State' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_state'] ) && ! empty( $customers_metadata['billing_state'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_state'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing Phone Number' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_phone'] ) && ! empty( $customers_metadata['billing_phone'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_phone'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Billing EmailID' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['billing_email'] ) && ! empty( $customers_metadata['billing_email'] ) ) {
					$wpssw_value = (string) $customers_metadata['billing_email'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping FirstName' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_first_name'] ) && ! empty( $customers_metadata['shipping_first_name'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_first_name'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping LastName' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_last_name'] ) && ! empty( $customers_metadata['shipping_last_name'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_last_name'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping Company Name' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_company'] ) && ! empty( $customers_metadata['shipping_company'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_company'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping Address1' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_address_1'] ) && ! empty( $customers_metadata['shipping_address_1'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_address_1'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping Address2' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_address_2'] ) && ! empty( $customers_metadata['shipping_address_2'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_address_2'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping City' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_city'] ) && ! empty( $customers_metadata['shipping_city'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_city'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping Postcode / ZIP' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_postcode'] ) && ! empty( $customers_metadata['shipping_postcode'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_postcode'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping Country' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_country'] ) && ! empty( $customers_metadata['shipping_country'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_country'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Shipping State' === (string) $wpssw_headers_name ) {
				if ( isset( $customers_metadata['shipping_state'] ) && ! empty( $customers_metadata['shipping_state'] ) ) {
					$wpssw_value = (string) $customers_metadata['shipping_state'][0];
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
		}
	}
	new WPSSW_Customer_Headers();
endif;
