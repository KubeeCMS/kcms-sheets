<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_YITH_WAPO' ) ) :
	/**
	 * Class WPSSW_YITH_WAPO.
	 */
	class WPSSW_YITH_WAPO extends WPSSW_Order_Utils {
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
		 *
		 * Check if YITH WooCommerce Product Add-Ons plugin is active or not.
		 */
		public static function wpssw_is_pugin_active() {
			if ( defined( 'YITH_WAPO' ) ) {
				return true;
			}
			return false;
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {
			$headers = array();
			global $wpdb;
			/*YITH*/
			$table_name = $wpdb->prefix;
			// @codingStandardsIgnoreStart.
			$wpssw_rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}yith_wapo_types WHERE del='0' ORDER BY priority ASC" ); //db call ok.
			// @codingStandardsIgnoreEnd.
			if ( ! empty( $wpssw_rows ) ) {
				foreach ( $wpssw_rows as $wpssw_key => $wpssw_value ) :
					if ( 'labels' === $wpssw_value->type || 'multiple_labels' === $wpssw_value->type ) {
						continue;
					}
					if ( ! in_array( $wpssw_value->type, array( 'checkbox', 'select', 'radio' ), true ) ) {
						if ( ! empty( $wpssw_value->options ) ) {
							$coloroption = maybe_unserialize( $wpssw_value->options );
							if ( ! empty( $coloroption['label'] ) ) {
								$headers = array_merge( $headers, $coloroption['label'] );
							}
						}
					} else {
						if ( ! empty( $wpssw_value->options ) ) {
							if ( ! empty( $wpssw_value->label ) ) {
								$headers[] = $wpssw_value->label;
							}
						}
					}
				endforeach;
			}
			self::$wpssw_headers = $headers;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_YITH_WAPO'] = self::$wpssw_headers;
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
			$wpssw_items = $wpssw_order->get_items();
			foreach ( $wpssw_items as $wpssw_item ) {
				$wpssw_metadata    = $wpssw_item->get_formatted_meta_data();
				$wpssw_yithmetaval = '';
				foreach ( $wpssw_metadata as $wpssw_meta ) {
					if ( strtolower( $wpssw_meta->key ) === strtolower( $wpssw_headers_name ) ) {
						$wpssw_yithmetaval .= $wpssw_meta->value . ',';
					}
				}
				$wpssw_yithmetaval = rtrim( $wpssw_yithmetaval, ',' );
				if ( is_array( $wpssw_yithmetaval ) ) {
					$wpssw_value[] = implode( ',', $wpssw_yithmetaval );
				} else {
					$wpssw_value[] = $wpssw_yithmetaval;
				}
			}
			return $wpssw_value;
		}
	}
	new WPSSW_YITH_WAPO();
endif;
