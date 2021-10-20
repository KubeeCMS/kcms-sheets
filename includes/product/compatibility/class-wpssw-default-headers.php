<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Default_Headers' ) ) :
	/**
	 * Class WPSSW_Default_Headers.
	 */
	class WPSSW_Default_Headers extends WPSSW_Product_Utils {
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

			add_filter( 'wpsyncsheets_product_headers', __CLASS__ . '::get_header_list', 10, 1 );
		}
		/**
		 * Prepare Header List.
		 */
		protected function prepare_headers() {

			$wpssw_wooproduct_headers['common'] = array(
				'post_title'               => 'Product Name',

				'post_status'              => 'Product Status',

				'post_name'                => 'Product Slug',

				'post_link'                => 'Product Link',

				'category_ids'             => 'Product Categories',

				'post_excerpt'             => 'Product Short Description',

				'post_content'             => 'Product Description',

				'_sku'                     => 'Product SKU',

				'type'                     => 'Product Type',

				'raw_image'                => 'Product Image',

				'tag_ids'                  => 'Product Tags',

				'attributes'               => 'Product Attribute',

				'_price'                   => 'Product Price',

				'_regular_price'           => 'Product Regular Price',

				'_sale_price'              => 'Product Sale Price',

				'_sale_price_dates_from'   => 'Product Sale Price Dates From',

				'_sale_price_dates_to'     => 'Product Sale Price Dates To',

				'_stock'                   => 'Product Stock',

				'_stock_status'            => 'Product Stock Status',

				'_weight'                  => 'Product Weight',

				'_height'                  => 'Product Height',

				'_width'                   => 'Product Width',

				'_length'                  => 'Product Length',

				'_dimensions'              => 'Product Dimensions',

				'_sold_individually'       => 'Product Sold Individually',

				'_manage_stock'            => 'Manage Product Stock',

				'_shipping_class_id'       => 'Product Shipping Class',

				'_tax_status'              => 'Product Tax Status',

				'_tax_class'               => 'Product Tax Class',

				'_virtual'                 => 'Virtual Product',

				'post_date'                => 'Product Created Date',

				'date_modified'            => 'Product Modified Date',

				'total_sales'              => 'Product Total Sale',

				'_purchase_note'           => 'Product Purchase Note',

				'_downloadable'            => 'Downloadable Product',

				'_downloadable_files'      => 'Product Downloadable Files',
				'_downloadable_file_names' => 'Product Downloadable File Names',
				'_download_limit'          => 'Product Download Limit',

				'_download_expiry'         => 'Product Download Expiry',

				'_backorders'              => 'Product Allow Backorders?',

			);
			$wpssw_wooproduct_headers['variable'] = array(
				'_sku'                     => 'Product Variation SKU',

				'_raw_image'               => 'Product Variation Image',

				'_price'                   => 'Product Variation Price',

				'_regular_price'           => 'Product Variation Regular Price',

				'_sale_price'              => 'Product Variation Sale Price',

				'_sale_price_dates_from'   => 'Product Variation Sale Price Dates From',

				'_sale_price_dates_to'     => 'Product Variation Sale Price Dates To',

				'_stock'                   => 'Product Variation Stock',

				'_stock_status'            => 'Product Variation Stock Status',

				'_weight'                  => 'Product Variation Weight',

				'dimensions'               => 'Product Variation Dimensions',

				'_manage_stock'            => 'Product Variation Manage Product Stock',

				'_shipping_class_id'       => 'Product Variation Shipping Class',

				'_virtual'                 => 'Virtual Product Variation',

				'_variation_description'   => 'Product Variation Description',
				'_downloadable'            => 'Downloadable Product Variation',

				'_downloadable_files'      => 'Product Variation Downloadable Files',
				'_downloadable_file_names' => 'Product Variation Downloadable File Names',
				'_download_limit'          => 'Product Variation Download Limit',

				'_download_expiry'         => 'Product Variation Download Expiry',

				'_backorders'              => 'Product Variation Allow Backorders?',
				'_total_sales'             => 'Product Variation Total Sale',
			);
			$wpssw_wooproduct_headers['external'] = array(
				'_product_url' => 'External Product Link',

				'_button_text' => 'External Product Button Text',
			);
			$wpssw_wooproduct_headers['grouped']  = array(
				'_children' => 'Grouped Products Ids',
			);
			// Add attributes to headers list.

			$wpssw_attribute_taxonomies                       = WPSSW_Product::wpssw_get_all_attributes();
			$wpssw_wooproduct_headers['attribute_taxonomies'] = array();
			if ( ! empty( $wpssw_attribute_taxonomies ) ) {

					$wpssw_wooproduct_headers['attribute_taxonomies'] = $wpssw_attribute_taxonomies;

					$wpssw_variation_array = preg_filter( '/^/', 'Variation: ', $wpssw_attribute_taxonomies );

					$wpssw_wooproduct_headers['variation'] = $wpssw_variation_array;
			}
			self::$wpssw_headers = $wpssw_wooproduct_headers;
		}
		/**
		 * Get Header List.
		 *
		 * @param array $headers .
		 * @return array $headers
		 */
		public static function get_header_list( $headers = array() ) {
			if ( ! empty( self::$wpssw_headers ) ) {
				$headers['WPSSW_Default_Headers'] = self::$wpssw_headers;
			}
			return $headers;
		}
		/**
		 * Get Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_product product object.
		 * @param bool   $wpssw_child true if child product.
		 */
		public static function get_value( $wpssw_headers_name, $wpssw_product, $wpssw_child ) {
			return self::prepare_value( $wpssw_headers_name, $wpssw_product, $wpssw_child );
		}
		/**
		 * Prepare Value for given header name.
		 *
		 * @param string $wpssw_headers_name Header name.
		 * @param object $wpssw_product product object.
		 * @param bool   $wpssw_child true if child product.
		 */
		public static function prepare_value( $wpssw_headers_name, $wpssw_product, $wpssw_child ) {

			$wpssw_value                = '';
			$wpssw_attribute_taxonomies = WPSSW_Product::wpssw_get_all_attributes();
			if ( ! is_array( $wpssw_attribute_taxonomies ) ) {
				$wpssw_attribute_taxonomies = array();
			}
			$wpssw_variation_array = array();

			if ( is_array( $wpssw_attribute_taxonomies ) && ! empty( $wpssw_attribute_taxonomies ) ) {
				$wpssw_variation_array = preg_filter( '/^/', 'Variation: ', $wpssw_attribute_taxonomies );
			}

			if ( 'Product Name' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() && ! empty( $wpssw_product->get_children() ) && true === (bool) $wpssw_child ) {
					$wpssw_value = $wpssw_product->get_name();
				} else {
					$wpssw_value = $wpssw_product->get_title();
				}
				return $wpssw_value;
			}
			if ( 'Product Short Description' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_short_description();
				return $wpssw_value;
			}
			if ( 'Product Description' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				if ( 'variation' !== (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_description();
				}
				return $wpssw_value;
			}
			if ( 'Product Status' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				if ( 'variation' !== (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_status();
				}
				return $wpssw_value;
			}
			if ( 'Product Slug' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_slug();
				return $wpssw_value;
			}
			if ( 'Product Link' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				if ( 'variation' === (string) $wpssw_product->get_type() && ! empty( $wpssw_product->get_children() ) ) {
					$wpssw_value = get_permalink( $wpssw_product->get_id() );
				} else {
					$wpssw_value = get_permalink( $wpssw_product->get_id() );
				}
				return $wpssw_value;
			}
			if ( 'Product Categories' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				if ( ! empty( $wpssw_product->get_parent_id() ) && 'grouped' !== (string) $wpssw_product->get_type() ) {
					$pid = $wpssw_product->get_parent_id();
				} else {
					$pid = $wpssw_product->get_id();
				}
				$product_cats     = wp_get_post_terms( $pid, 'product_cat', array( 'fields' => 'names' ) );
				$product_category = array();
				if ( is_array( $product_cats ) && ! empty( $product_cats ) ) {
					$product_category = $product_cats;
				}
				if ( 'variation' !== (string) $wpssw_product->get_type() ) {
					$wpssw_value = implode( ', ', $product_category );
				}
				return $wpssw_value;
			}
			if ( 'Product SKU' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				if ( 'variation' !== (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_sku();
				}
				return $wpssw_value;
			}
			if ( 'Product Type' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_type();
				return $wpssw_value;
			}
			if ( 'Product Image' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$attachment[0] = '';
				if ( has_post_thumbnail( $wpssw_product->get_id() ) ) {
					$attachment_ids = get_post_thumbnail_id( $wpssw_product->get_id() );
					$attachment     = wp_get_attachment_image_src( $attachment_ids, 'full' );
					if ( ! isset( $attachment[0] ) ) {
						$attachment[0] = '';
					}
					$image_attachment[] = $attachment[0];
				}
				$gallery_image_ids = $wpssw_product->get_gallery_image_ids();
				$gallery_image_ids = array_filter( $gallery_image_ids );
				if ( ! empty( $gallery_image_ids ) ) {
					foreach ( $gallery_image_ids as $gallery_image ) {
						$gallery_attachment = wp_get_attachment_image_src( $gallery_image, 'full' );

						if ( isset( $gallery_attachment[0] ) ) {
							$image_attachment[] = $gallery_attachment[0];
						}
					}
				}
				if ( is_array( $image_attachment ) && ! empty( $image_attachment ) ) {
					$images = implode( '|', $image_attachment );
				} else {
					$images = '';
				}
				$wpssw_value = $images;
				return $wpssw_value;
			}
			if ( 'Product Tags' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$taglist = array();
				// get an array of the WP_Term objects for a defined product ID.
				$terms = wp_get_post_terms( $wpssw_product->get_id(), 'product_tag' );
				// Loop through each product tag for the current product.
				if ( count( $terms ) > 0 ) {
					foreach ( $terms as $term ) {
						$term_name = $term->name; // Product tag Name.
						// Set the product tag names in an array.
						$taglist[] = $term_name;
					}
				}
				$wpssw_value = implode( ', ', $taglist );
				return $wpssw_value;
			}
			if ( 'Product Attribute' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$attributes_value = array();
				$attributes       = $wpssw_product->get_attributes();
				foreach ( $attributes as $attrkey => $attrvalue ) {
					$attributes_value[] = wc_attribute_label( $attrkey );
				}
				if ( 'variation' !== (string) $wpssw_product->get_type() ) {
					$wpssw_value = implode( '|', $attributes_value );
				}
				return $wpssw_value;
			}
			if ( 'Product Price' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_price();
				return $wpssw_value;
			}
			if ( 'Product Regular Price' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_regular_price();
				return $wpssw_value;
			}
			if ( 'Product Sale Price' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_sale_price();
				return $wpssw_value;
			}
			if ( 'Product Sale Price Dates From' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$date_from = '';
				if ( $wpssw_product->get_date_on_sale_from() ) {
					$date_from = $wpssw_product->get_date_on_sale_from()->format( WPSSW_Setting::wpssw_option( 'date_format' ) );
				}
				$wpssw_value = $date_from;
				return $wpssw_value;
			}
			if ( 'Product Sale Price Dates To' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$date_to = '';
				if ( $wpssw_product->get_date_on_sale_to() ) {
					$date_to = $wpssw_product->get_date_on_sale_to()->format( WPSSW_Setting::wpssw_option( 'date_format' ) );
				}
				$wpssw_value = $date_to;
				return $wpssw_value;
			}
			if ( 'Product Stock' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_stock_quantity();
				return $wpssw_value;
			}
			if ( 'Product Stock Status' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_stock_status();
				return $wpssw_value;
			}
			if ( 'Product Weight' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_weight();
				return $wpssw_value;
			}
			if ( 'Product Height' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_height();
				return $wpssw_value;
			}
			if ( 'Product Width' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_width();
				return $wpssw_value;
			}
			if ( 'Product Length' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_length();
				return $wpssw_value;
			}
			if ( 'Product Total Sale' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_total_sales();
				return $wpssw_value;
			}
			if ( 'Product Purchase Note' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_purchase_note();
				return $wpssw_value;
			}
			if ( 'Product Dimensions' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = html_entity_decode( wc_format_dimensions( $wpssw_product->get_dimensions( false ) ), ENT_QUOTES );
				return $wpssw_value;
			}
			if ( 'Product Sold Individually' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$is_sold_ind = 'No';
				if ( $wpssw_product->get_sold_individually() ) {
					$is_sold_ind = 'Yes';
				}
				$wpssw_value = $is_sold_ind;
				return $wpssw_value;
			}
			if ( 'Manage Product Stock' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$is_manage = 'No';
				if ( $wpssw_product->get_manage_stock() ) {
					$is_manage = 'Yes';
				}
				$wpssw_value = $is_manage;
				return $wpssw_value;
			}
			if ( 'Product Shipping Class' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_shipping_class_id();
				return $wpssw_value;
			}
			if ( 'Product Tax Status' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_tax_status();
				return $wpssw_value;
			}
			if ( 'Product Tax Class' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_tax_class();
				return $wpssw_value;
			}
			if ( 'Virtual Product' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				$is_virtual = 'No';
				if ( $wpssw_product->get_virtual() ) {
					$is_virtual = 'Yes';
				}
				$wpssw_value = $is_virtual;
				return $wpssw_value;
			}
			if ( 'Product Created Date' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				if ( null !== $wpssw_product->get_date_created() ) {
					if ( 'variation' !== (string) $wpssw_product->get_type() ) {
						$wpssw_value = $wpssw_product->get_date_created()->format( WPSSW_Setting::wpssw_option( 'date_format' ) );
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Modified Date' === (string) $wpssw_headers_name && true !== (bool) $wpssw_child ) {
				if ( 'variation' !== (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_date_modified()->format( WPSSW_Setting::wpssw_option( 'date_format' ) );
				}
				return $wpssw_value;
			}
			if ( 'Downloadable Product' === (string) $wpssw_headers_name ) {
				$is_downloadable = 'No';
				if ( 'simple' === (string) $wpssw_product->get_type() ) {
					if ( $wpssw_product->get_downloadable() ) {
						$is_downloadable = 'Yes';
					}
				}
				$wpssw_value = $is_downloadable;
				return $wpssw_value;
			}
			if ( 'Product Downloadable Files' === (string) $wpssw_headers_name || 'Product Downloadable File Names' === (string) $wpssw_headers_name ) {
				if ( 'simple' === (string) $wpssw_product->get_type() ) {
					$file  = array();
					$files = '';
					if ( ! empty( $wpssw_product->get_downloads() ) ) {
						foreach ( $wpssw_product->get_downloads() as $downloads ) {
							$file[]      = $downloads->get_file();
							$file_name[] = $downloads->get_name();
						}
						if ( 'Product Downloadable File Names' === (string) $wpssw_headers_name ) {
							$file = $file_name;
						}
						$files = implode( ', ', $file );
					}
					$wpssw_value = $files;
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Download Limit' === (string) $wpssw_headers_name ) {
				if ( 'simple' === (string) $wpssw_product->get_type() ) {
					if ( -1 === (int) $wpssw_product->get_download_limit() ) {
						$wpssw_value = 'Unlimited';
					} else {
						$wpssw_value = $wpssw_product->get_download_limit();
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Download Expiry' === (string) $wpssw_headers_name ) {
				if ( 'simple' === (string) $wpssw_product->get_type() ) {
					if ( -1 === (int) $wpssw_product->get_download_expiry() ) {
						$wpssw_value = 'Never';
					} else {
						$wpssw_value = $wpssw_product->get_download_expiry();
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Allow Backorders?' === (string) $wpssw_headers_name ) {
				if ( $wpssw_product->get_backorders() ) {
					$wpssw_value = $wpssw_product->get_backorders();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation SKU' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_sku();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Image' === (string) $wpssw_headers_name ) {
				$vattachment[0] = '';
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					if ( has_post_thumbnail( $wpssw_product->get_id() ) ) {
						$vattachment_ids = get_post_thumbnail_id( $wpssw_product->get_id() );
						$vattachment     = wp_get_attachment_image_src( $vattachment_ids, 'full' );
						if ( ! isset( $vattachment[0] ) ) {
							$vattachment[0] = '';
						}
					}
				}
				$wpssw_value = $vattachment[0];
				return $wpssw_value;
			}
			if ( 'Product Variation Price' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_price();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Regular Price' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_regular_price();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Sale Price' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_sale_price();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Sale Price Dates From' === (string) $wpssw_headers_name ) {
				$date_from = '';
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					if ( $wpssw_product->get_date_on_sale_from() ) {
						$date_from = $wpssw_product->get_date_on_sale_from()->format( WPSSW_Setting::wpssw_option( 'date_format' ) );
					}
				}
				$wpssw_value = $date_from;
				return $wpssw_value;
			}
			if ( 'Product Variation Sale Price Dates To' === (string) $wpssw_headers_name ) {
				$date_to = '';
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					if ( $wpssw_product->get_date_on_sale_to() ) {
						$date_to = $wpssw_product->get_date_on_sale_to()->format( WPSSW_Setting::wpssw_option( 'date_format' ) );
					}
				}
				$wpssw_value = $date_to;
				return $wpssw_value;
			}
			if ( 'Product Variation Stock' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_stock_quantity();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Stock Status' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_stock_status();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Weight' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_weight();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Dimensions' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = html_entity_decode( $wpssw_product->get_dimensions(), ENT_QUOTES );
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Manage Product Stock' === (string) $wpssw_headers_name ) {
				$is_manage = 'No';
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					if ( $wpssw_product->get_manage_stock() ) {
						$is_manage = 'Yes';
					}
				}
				$wpssw_value = $is_manage;
				return $wpssw_value;
			}
			if ( 'Product Variation Shipping Class' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_shipping_class_id();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Virtual Product Variation' === (string) $wpssw_headers_name ) {
				$is_virtual = 'No';
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					if ( $wpssw_product->get_virtual() ) {
						$is_virtual = 'Yes';
					}
				}
				$wpssw_value = $is_virtual;
				return $wpssw_value;
			}
			if ( 'Product Variation Description' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_description();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Downloadable Product Variation' === (string) $wpssw_headers_name ) {
				$is_downloadable = 'No';
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					if ( $wpssw_product->get_downloadable() ) {
						$is_downloadable = 'Yes';
					}
				}
				$wpssw_value = $is_downloadable;
				return $wpssw_value;
			}
			if ( 'Product Variation Downloadable Files' === (string) $wpssw_headers_name || 'Product Variation Downloadable File Names' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$file  = array();
					$files = '';
					if ( ! empty( $wpssw_product->get_downloads() ) ) {
						foreach ( $wpssw_product->get_downloads() as $downloads ) {
							$file[]      = $downloads->get_file();
							$file_name[] = $downloads->get_name();
						}
						if ( 'Product Variation Downloadable File Names' === (string) $wpssw_headers_name ) {
							$file = $file_name;
						}
						$files = implode( ', ', $file );
					}
					$wpssw_value = $files;
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Download Limit' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					if ( -1 === (int) $wpssw_product->get_download_limit() ) {
						$wpssw_value = 'Unlimited';
					} else {
						$wpssw_value = $wpssw_product->get_download_limit();
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Download Expiry' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					if ( -1 === (int) $wpssw_product->get_download_expiry() ) {
						$wpssw_value = 'Never';
					} else {
						$wpssw_value = $wpssw_product->get_download_expiry();
					}
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Grouped Products Ids' === (string) $wpssw_headers_name ) {
				if ( 'grouped' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = implode( ', ', $wpssw_product->get_children() );
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Allow Backorders?' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_backorders();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'Product Variation Total Sale' === (string) $wpssw_headers_name ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_total_sales();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'External Product Button Text' === (string) $wpssw_headers_name ) {
				if ( 'external' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_button_text();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}
			if ( 'External Product Link' === (string) $wpssw_headers_name ) {
				if ( 'external' === (string) $wpssw_product->get_type() ) {
					$wpssw_value = $wpssw_product->get_product_url();
				} else {
					$wpssw_value = '';
				}
				return $wpssw_value;
			}

			if ( in_array( $wpssw_headers_name, $wpssw_attribute_taxonomies, true ) && true !== (bool) $wpssw_child ) {
				$wpssw_value = $wpssw_product->get_attribute( strtolower( $wpssw_headers_name ) );
				return $wpssw_value;
			}

			if ( in_array( $wpssw_headers_name, $wpssw_variation_array, true ) && true === (bool) $wpssw_child ) {
				if ( 'variation' === (string) $wpssw_product->get_type() ) {
					$wpssw_attr_name          = 'attribute_';
					$wpssw_pattr_name         = 'attribute_pa_';
					$wpssw_attrname           = strtolower( trim( str_replace( 'Variation: ', '', $wpssw_headers_name ) ) );
					$wpssw_attr_name         .= $wpssw_attrname;
					$wpssw_pattr_name        .= $wpssw_attrname;
					$wpssw_selected_variation = $wpssw_product->get_variation_attributes();
					if ( isset( $wpssw_selected_variation[ $wpssw_attr_name ] ) ) {
						$wpssw_value = $wpssw_selected_variation[ $wpssw_attr_name ];
					} elseif ( isset( $wpssw_selected_variation[ $wpssw_pattr_name ] ) ) {
						$wpssw_value = $wpssw_selected_variation[ $wpssw_pattr_name ];
					} else {
						$wpssw_value = '';
					}
					return $wpssw_value;
				}
			}
		}
	}
	new WPSSW_Default_Headers();
endif;
