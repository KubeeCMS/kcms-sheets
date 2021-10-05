<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

if ( ! class_exists( 'WPSSW_Coupon_Headers' ) ) :
	/**
	 * Class WPSSW_Coupon_Headers.
	 */
	class WPSSW_Coupon_Headers extends WPSSW_Coupon_Utils {
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
			add_filter( 'wpsyncsheets_coupon_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}

		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			self::$wpssw_headers = array( 'Coupon Code', 'Description', 'Discount Type', 'Coupon Amount', 'Allow Free Shipping', 'Coupon Expiry Date', 'Minimum Spend', 'Maximum Spend', 'Individual Use Only', 'Exclude Sale Items', 'Products', 'Exclude Products', 'Applied Product Categories', 'Exclude Categories', 'Allowed Emails', 'Usage Limit Per Coupon', 'Usage Limit Per User', 'Limit Usage To X Items' );
		}

		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Coupon_Headers'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_coupon coupon object.
		 */
		public static function get_value( $wpssw_headers_name, $wpssw_coupon ) {
			return self::prepare_value( $wpssw_headers_name, $wpssw_coupon );
		}

		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_coupon coupon object.
		 */
		public static function prepare_value( $wpssw_headers_name, $wpssw_coupon ) {
			$wpssw_value = '';
			if ( 'Coupon Code' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $wpssw_coupon->get_code();
				return $wpssw_value;
			}
			if ( 'Description' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $wpssw_coupon->get_description();
				return $wpssw_value;
			}
			if ( 'Discount Type' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $wpssw_coupon->get_discount_type();
				return $wpssw_value;
			}
			if ( 'Coupon Amount' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $wpssw_coupon->get_amount();
				return $wpssw_value;
			}
			if ( 'Allow Free Shipping' === (string) $wpssw_headers_name ) {
				$is_free_shipping = 'No';
				if ( $wpssw_coupon->get_free_shipping() ) {
					$is_free_shipping = 'Yes';
				}
				$wpssw_value = $is_free_shipping;
				return $wpssw_value;
			}
			if ( 'Coupon Expiry Date' === (string) $wpssw_headers_name ) {
				if ( $wpssw_coupon->get_date_expires() ) {
					$wpssw_value = (string) $wpssw_coupon->get_date_expires()->format( WPSSW_Setting::wpssw_option( 'date_format' ) . ' ' . WPSSW_Setting::wpssw_option( 'time_format' ) );
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Minimum Spend' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $wpssw_coupon->get_minimum_amount();
				return $wpssw_value;
			}
			if ( 'Maximum Spend' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $wpssw_coupon->get_maximum_amount();
				return $wpssw_value;
			}
			if ( 'Individual Use Only' === (string) $wpssw_headers_name ) {
				$is_individual = 'No';
				if ( $wpssw_coupon->get_individual_use() ) {
					$is_individual = 'Yes';
				}
				$wpssw_value = $is_individual;
				return $wpssw_value;
			}

			if ( 'Exclude Sale Items' === (string) $wpssw_headers_name ) {
				$is_exclude_sale_items = 'No';
				if ( $wpssw_coupon->get_exclude_sale_items() ) {
					$is_exclude_sale_items = 'Yes';
				}
				$wpssw_value = $is_exclude_sale_items;
				return $wpssw_value;
			}
			if ( 'Products' === (string) $wpssw_headers_name ) {
				if ( is_array( $wpssw_coupon->get_product_ids() ) && ! empty( $wpssw_coupon->get_product_ids() ) ) {
					$meta_post = array();
					foreach ( $wpssw_coupon->get_product_ids() as $product_id ) {
						$meta_post[] = get_post( $product_id )->post_title;
					}
					$wpssw_value = implode( ',', $meta_post );
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Exclude Products' === (string) $wpssw_headers_name ) {
				if ( is_array( $wpssw_coupon->get_excluded_product_ids() ) && ! empty( $wpssw_coupon->get_excluded_product_ids() ) ) {
					$meta_post = array();
					foreach ( $wpssw_coupon->get_excluded_product_ids() as $product_id ) {
						$meta_post[] = get_post( $product_id )->post_title;
					}
					$wpssw_value = implode( ',', $meta_post );
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Applied Product Categories' === (string) $wpssw_headers_name ) {
				$category_names = array();
				if ( is_array( $wpssw_coupon->get_product_categories() ) && ! empty( $wpssw_coupon->get_product_categories() ) ) {
					$categories   = (array) get_terms( 'product_cat' );
					$category_ids = array_column( $categories, 'term_id', 'name' );
					foreach ( $wpssw_coupon->get_product_categories() as $categorie ) {
						if ( in_array( $categorie, $category_ids, true ) ) {
							$category_names[] = array_search( $categorie, $category_ids, true );
						}
					}
					$wpssw_value = implode( ',', $category_names );
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Exclude Categories' === (string) $wpssw_headers_name ) {
				$category_names = array();
				if ( is_array( $wpssw_coupon->get_excluded_product_categories() ) && ! empty( $wpssw_coupon->get_excluded_product_categories() ) ) {
					$categories   = (array) get_terms( 'product_cat' );
					$category_ids = array_column( $categories, 'term_id', 'name' );
					foreach ( $wpssw_coupon->get_excluded_product_categories() as $categorie ) {
						if ( in_array( $categorie, $category_ids, true ) ) {
							$category_names[] = array_search( $categorie, $category_ids, true );
						}
					}
					$wpssw_value = implode( ',', $category_names );
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Allowed Emails' === (string) $wpssw_headers_name ) {
				if ( is_array( $wpssw_coupon->get_email_restrictions() ) && ! empty( $wpssw_coupon->get_email_restrictions() ) ) {
					$wpssw_value = implode( ',', $wpssw_coupon->get_email_restrictions() );
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Usage Limit Per Coupon' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $wpssw_coupon->get_usage_limit();
				return $wpssw_value;
			}
			if ( 'Usage Limit Per User' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $wpssw_coupon->get_usage_limit_per_user();
				return $wpssw_value;
			}
			if ( 'Limit Usage To X Items' === (string) $wpssw_headers_name ) {
				$wpssw_value = (string) $wpssw_coupon->get_limit_usage_to_x_items();
				return $wpssw_value;
			}
		}
	}
	new WPSSW_Coupon_Headers();
endif;
