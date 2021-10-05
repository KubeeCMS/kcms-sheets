<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
if ( ! class_exists( 'WPSSW_Product_Import' ) ) :
	/**
	 * Class WPSSW_Product_Import.
	 */
	class WPSSW_Product_Import extends WPSSW_Setting {
		/**
		 * Instance of WPSSW_Google_API_Functions
		 *
		 * @var $instance_api
		 */
		protected static $instance_api = null;
		/**
		 * Initialization
		 */
		public function __construct() {
			self::wpssw_google_api();
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_product_import_ajax_hook();
		}
		/**
		 * Create Google Api Instance.
		 */
		public static function wpssw_google_api() {
			if ( null === self::$instance_api ) {
				self::$instance_api = new \WPSSW_Google_API_Functions();
			}
			return self::$instance_api;
		}
		/**
		 * Get products count for syncronization
		 */
		public static function wpssw_get_product_import_count() {

			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_sheetname                   = 'All Products';
			$wpssw_checked                     = '';
			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}
			$wpssw_sheet           = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry        = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
			$wpssw_data            = $wpssw_allentry->getValues();
			$wpssw_headers         = array_shift( $wpssw_data );
			$wpssw_insert_products = array();
			$wpssw_update_products = array();
			$wpssw_delete_products = array();
			if ( in_array( 'Insert', $wpssw_headers, true ) ) {
				$wpssw_insert_key      = array_search( 'Insert', $wpssw_headers, true );
				$wpssw_insert_products = array_values( array_filter( array_column( $wpssw_data, $wpssw_insert_key ) ) );
			}
			if ( in_array( 'Update', $wpssw_headers, true ) ) {
				$wpssw_update_key      = array_search( 'Update', $wpssw_headers, true );
				$wpssw_update_products = array_values( array_filter( array_column( $wpssw_data, $wpssw_update_key ) ) );
			}
			if ( in_array( 'Delete', $wpssw_headers, true ) ) {
				$wpssw_delete_key      = array_search( 'Delete', $wpssw_headers, true );
				$wpssw_delete_products = array_values( array_filter( array_column( $wpssw_data, $wpssw_delete_key ) ) );
			}
			$wpssw_result_array = array();
			if ( count( $wpssw_insert_products ) > 0 ) {
				$wpssw_result_array['insertproducts'] = count( $wpssw_insert_products );
			}
			if ( count( $wpssw_update_products ) > 0 ) {
				$wpssw_result_array['updateproducts'] = count( $wpssw_update_products );
			}
			if ( count( $wpssw_delete_products ) > 0 ) {
				$wpssw_result_array['deleteproducts'] = count( $wpssw_delete_products );
			}
			echo wp_json_encode( $wpssw_result_array );
			die;
		}
		/**
		 * Import product
		 */
		public static function wpssw_product_import() {
			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			$wpssw_product_spreadsheet_setting = parent::wpssw_option( 'wpssw_product_spreadsheet_setting' );
			$wpssw_spreadsheetid               = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
			$wpssw_sheetname                   = 'All Products';
			$wpssw_checked                     = '';
			if ( 'yes' !== (string) $wpssw_product_spreadsheet_setting ) {
				return;
			}
			$wpssw_sheet    = "'" . $wpssw_sheetname . "'!A:A";
			$wpssw_allentry = self::$instance_api->get_row_list( $wpssw_spreadsheetid, $wpssw_sheetname );
			$wpssw_data     = $wpssw_allentry->getValues();

			$wpssw_product_ids           = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element[0] ) ) {
						return $wpssw_element[0];
					} else {
						return '';
					}
				},
				$wpssw_data
			);
			$wpssw_product_variation_ids = array_map(
				function( $wpssw_element ) {
					if ( isset( $wpssw_element[1] ) ) {
						return $wpssw_element[1];
					} else {
						return '';
					}
				},
				$wpssw_data
			);

			$wpssw_headers = array_shift( $wpssw_data );

			$wpssw_insert_products = array();
			$wpssw_update_products = array();
			$wpssw_delete_products = array();
			if ( in_array( 'Insert', $wpssw_headers, true ) ) {
				$wpssw_insert_key      = array_search( 'Insert', $wpssw_headers, true );
				$wpssw_insert_products = array_map(
					function( $wpssw_element ) use ( $wpssw_insert_key ) {
						if ( isset( $wpssw_element[ $wpssw_insert_key ] ) ) {
							return $wpssw_element[ $wpssw_insert_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_insert_products = array_filter( $wpssw_insert_products );
			}
			if ( in_array( 'Update', $wpssw_headers, true ) ) {
				$wpssw_update_key      = array_search( 'Update', $wpssw_headers, true );
				$wpssw_update_products = array_map(
					function( $wpssw_element ) use ( $wpssw_update_key ) {
						if ( isset( $wpssw_element[ $wpssw_update_key ] ) ) {
							return $wpssw_element[ $wpssw_update_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);

				$wpssw_update_products = array_filter( $wpssw_update_products );
			}
			if ( in_array( 'Delete', $wpssw_headers, true ) ) {
				$wpssw_delete_key      = array_search( 'Delete', $wpssw_headers, true );
				$wpssw_delete_products = array_map(
					function( $wpssw_element ) use ( $wpssw_delete_key ) {
						if ( isset( $wpssw_element[ $wpssw_delete_key ] ) ) {
							return $wpssw_element[ $wpssw_delete_key ];
						} else {
							return '';
						}
					},
					$wpssw_data
				);
				$wpssw_delete_products = array_filter( $wpssw_delete_products );
			}

			if ( ! empty( $wpssw_update_products ) ) {
				foreach ( $wpssw_update_products as $wpssw_productid => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}

					$wpssw_product_index = $wpssw_productid + 1;

					if ( ! isset( $wpssw_data[ $wpssw_productid ] ) ) {
						continue;
					}

					if ( isset( $wpssw_product_variation_ids[ $wpssw_product_index ] ) && ! empty( $wpssw_product_variation_ids[ $wpssw_product_index ] ) ) {

						$prd_id = $wpssw_product_variation_ids[ $wpssw_product_index ];

						self::wpssw_update_variation_product( $prd_id, $wpssw_data[ $wpssw_productid ] );
					} elseif ( isset( $wpssw_product_ids[ $wpssw_product_index ] ) && ! empty( $wpssw_product_ids[ $wpssw_product_index ] ) ) {
						$prd_id = $wpssw_product_ids[ $wpssw_product_index ];
						self::wpssw_update_product( $prd_id, $wpssw_data[ $wpssw_productid ] );
					} else {
						echo esc_html__( 'addproductId', 'wpssw' );
						die;
					}
				}
			}
			if ( ! empty( $wpssw_delete_products ) ) {
				foreach ( $wpssw_delete_products as $wpssw_productid => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}

					if ( ! isset( $wpssw_data[ $wpssw_productid ] ) ) {
						continue;
					}
					$wpssw_product_index = $wpssw_productid + 1;
					if ( isset( $wpssw_product_variation_ids[ $wpssw_product_index ] ) && ! empty( $wpssw_product_variation_ids[ $wpssw_product_index ] ) ) {

						$prd_id = $wpssw_product_variation_ids[ $wpssw_product_index ];
						wp_delete_post( $prd_id );
						$wpssw_product = wc_get_product( $wpssw_product_ids[ $wpssw_product_index ] );
						$update        = WPSSW_Product::wpssw_woocommerce_update_product( $wpssw_product_ids[ $wpssw_product_index ], $wpssw_product );
					} elseif ( isset( $wpssw_product_ids[ $wpssw_product_index ] ) && ! empty( $wpssw_product_ids[ $wpssw_product_index + 1 ] ) ) {
						$prd_id = $wpssw_product_ids[ $wpssw_product_index ];
						wp_trash_post( $prd_id );
					} else {
						echo esc_html__( 'addproductId', 'wpssw' );
						die;
					}
				}
			}
			if ( ! empty( $wpssw_insert_products ) ) {
				foreach ( $wpssw_insert_products as $wpssw_product_index => $wpssw_val ) {
					if ( 1 !== (int) $wpssw_val ) {
						continue;
					}
					if ( ! isset( $wpssw_data[ $wpssw_product_index ] ) ) {
						continue;
					}
					$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_product_headers' ) );
					if ( ! $wpssw_woo_selections ) {
						return;
					}
					array_unshift( $wpssw_woo_selections, 'Product Id', 'Product Variation Id' );
					$wpssw_product_values   = $wpssw_data[ $wpssw_product_index ];
					$wpssw_product_name_key = array_search( 'Product Name', $wpssw_woo_selections, true );
					$wpssw_product_name     = isset( $wpssw_product_values[ $wpssw_product_name_key ] ) ? $wpssw_product_values[ $wpssw_product_name_key ] : '';
					if ( isset( $wpssw_product_ids[ $wpssw_product_index - 1 ] ) && isset( $wpssw_product_ids[ $wpssw_product_index ] ) ) {
						if ( $wpssw_product_ids[ $wpssw_product_index - 1 ] === $wpssw_product_ids[ $wpssw_product_index ] ) {
							$wpssw_product_type = 'variation';
						}
					}
					$wpssw_product_id = isset( $wpssw_product_values[0] ) ? $wpssw_product_values[0] : '';
					if ( ! empty( $wpssw_product_id ) ) {
						$product = wc_get_product( $wpssw_product_id );
					}
					if ( ! empty( $wpssw_product_name ) && empty( $wpssw_product_id ) ) {

						$new_post = array(
							'post_title' => $wpssw_product_name,
							'post_type'  => 'product',
							'post_staus' => 'publish',
						);

						if ( post_exists( $wpssw_product_name ) ) {
							echo esc_html__( 'productnameexist', 'wpssw' );
							die;
						} else {
							$wpssw_spreadsheetid       = parent::wpssw_option( 'wpssw_product_spreadsheet_id' );
							$wpssw_sheetname           = 'All Products';
							$wpssw_response            = self::$instance_api->get_sheet_listing( $wpssw_spreadsheetid );
							$wpssw_existingsheetsnames = self::$instance_api->get_sheet_list( $wpssw_response );
							$wpssw_sheetid             = $wpssw_existingsheetsnames[ $wpssw_sheetname ];

							if ( $wpssw_sheetid ) {
								$param                = array();
								$startindex           = $wpssw_product_index + 1;
								$endindex             = $wpssw_product_index + 2;
								$param                = self::$instance_api->prepare_param( $wpssw_sheetid, $startindex, $endindex );
								$deleterequestarray[] = self::$instance_api->deleteDimensionrequests( $param, 'ROWS' );
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
							// Creating the Product .
							$post_id = wp_insert_post( $new_post );
							self::wpssw_update_product( $post_id, $wpssw_product_values );
						}
					} elseif ( ! empty( $wpssw_product_id ) && 'variation' === (string) trim( $wpssw_product_type ) && 'variable' === (string) $product->get_type() ) {
						$variation_post = array(
							'post_title'  => $product->get_name(),
							'post_status' => 'publish',
							'post_parent' => $wpssw_product_id,
							'post_type'   => 'product_variation',
							'guid'        => $product->get_permalink(),
						);
						// Creating the Product Variation.
						$variation_id = wp_insert_post( $variation_post );
						self::wpssw_update_product( $wpssw_product_id, $wpssw_product_values, 'insert' );
						self::wpssw_update_variation_product( $variation_id, $wpssw_product_values );
					} elseif ( ! empty( $wpssw_product_id ) ) {
						echo esc_html__( 'productIdexist', 'wpssw' );
						die;
					} elseif ( false === $wpssw_product_name_key ) {
						echo esc_html__( 'addproductnamecolumn', 'wpssw' );
						die;
					} else {
						echo esc_html__( 'addproductname', 'wpssw' );
						die;
					}
				}
			}
			echo esc_html__( 'successful', 'wpssw' );
			die;
		}
		/**
		 * Update imported product
		 *
		 * @param int    $wpssw_productid product id.
		 * @param array  $wpssw_data product data array.
		 * @param string $wpssw_opration opration to perform on product.
		 */
		public static function wpssw_update_product( $wpssw_productid, $wpssw_data, $wpssw_opration = 'update' ) {

			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			if ( (int) $wpssw_productid < 1 ) {
				return;
			}
			$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_product_headers' ) );
			if ( ! $wpssw_woo_selections ) {
				return;
			}
			array_unshift( $wpssw_woo_selections, 'Product Id', 'Product Variation Id' );
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_product_compatibility_files();
			$wpssw_wooproduct_headers   = apply_filters( 'wpsyncsheets_product_headers', array() );
			$wpssw_default_header       = array_merge( $wpssw_wooproduct_headers['WPSSW_Default_Headers']['common'], $wpssw_wooproduct_headers['WPSSW_Default_Headers']['attribute_taxonomies'], $wpssw_wooproduct_headers['WPSSW_Default_Headers']['external'], $wpssw_wooproduct_headers['WPSSW_Default_Headers']['grouped'] );
			$wpssw_attribute_taxonomies = WPSSW_Product::wpssw_get_all_attributes();
			$wpssw_updated_array        = array();
			$wpssw_attributes           = array();
			$wpssw_crud_operation       = array( 'Insert', 'Update', 'Delete' );
			$wpssw_attributes           = array();
			foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_header ) {
				if ( in_array( $wpssw_header, $wpssw_crud_operation, true ) ) {
					continue;
				}
				if ( in_array( $wpssw_header, $wpssw_default_header, true ) ) {
					$wpssw_meta_key           = array_search( $wpssw_header, $wpssw_default_header, true );
					$wpssw_data[ $wpssw_key ] = isset( $wpssw_data[ $wpssw_key ] ) ? $wpssw_data[ $wpssw_key ] : '';
					if ( 'post_name' === (string) $wpssw_meta_key || 'post_link' === (string) $wpssw_meta_key || '_dimensions' === (string) $wpssw_meta_key || '_downloadable_file_names' === (string) $wpssw_meta_key || ( 'post_title' === (string) $wpssw_meta_key && empty( $wpssw_data[ $wpssw_key ] ) ) ) {
						continue;
					}
					if ( isset( $wpssw_data[ $wpssw_key ] ) && $wpssw_meta_key ) {
						if ( is_numeric( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_data[ $wpssw_key ] = (int) $wpssw_data[ $wpssw_key ];
						}
					}
					if ( 'raw_image' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_images = explode( '|', $wpssw_data[ $wpssw_key ] );
						} else {
							$wpssw_images = array();
						}
						$wpssw_imagedata['raw_image'] = array_shift( $wpssw_images );
						if ( ! empty( $wpssw_images ) ) {
							$wpssw_imagedata['raw_gallery_image'] = $wpssw_images;
						}
						self::wpssw_set_image_data( $wpssw_productid, $wpssw_imagedata );
						continue;
					}
					if ( 'category_ids' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_categories = explode( ',', $wpssw_data[ $wpssw_key ] );
						} else {
							$wpssw_categories = array();
						}
						self::wpssw_parse_categories_field( $wpssw_categories );
						wp_set_object_terms( $wpssw_productid, $wpssw_categories, 'product_cat' );
						continue;
					}
					if ( 'tag_ids' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_tag = explode( ',', $wpssw_data[ $wpssw_key ] );
						} else {
							$wpssw_tag = array();
						}
						wp_set_object_terms( $wpssw_productid, $wpssw_tag, 'product_tag', false );
						continue;
					}
					if ( 'post_date' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_created_date                   = date_create( $wpssw_data[ $wpssw_key ] );
							$wpssw_data[ $wpssw_key ]             = date_format( $wpssw_created_date, 'Y-m-d H:i:s' );
							$wpssw_updated_array['post_date_gmt'] = $wpssw_data[ $wpssw_key ];
						} else {
							$wpssw_data[ $wpssw_key ] = '';
						}
					}
					if ( 'type' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) && 'variation' !== (string) trim( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_product_type = trim( $wpssw_data[ $wpssw_key ] );
						} elseif ( ! empty( $wpssw_data[ $wpssw_key ] ) && 'variation' === (string) trim( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_product_type = 'variable';
						} else {
							$wpssw_product_type = '';
						}

						wp_set_object_terms( $wpssw_productid, $wpssw_product_type, 'product_type' );
						continue;
					}

					if ( '_children' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_childrens_array = explode( ',', $wpssw_data[ $wpssw_key ] );
						} else {
							$wpssw_childrens_array = array();
						}
						self::wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_childrens_array );
						continue;
					}
					if ( '_price' === (string) $wpssw_meta_key ) {
						$wpssw_regular_price_key = array_search( 'Product Regular Price', $wpssw_woo_selections, true );
						$wpssw_sale_price_key    = array_search( 'Product Sale Price', $wpssw_woo_selections, true );
						$wpssw_regular_price     = isset( $wpssw_data[ $wpssw_regular_price_key ] ) ? $wpssw_data[ $wpssw_regular_price_key ] : '';
						$wpssw_sale_price        = isset( $wpssw_data[ $wpssw_sale_price_key ] ) ? $wpssw_data[ $wpssw_sale_price_key ] : '';

						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							if ( ! empty( $wpssw_sale_price ) ) {
								$wpssw_data[ $wpssw_key ] = $wpssw_data[ $wpssw_sale_price_key ];
							} elseif ( ! empty( $wpssw_regular_price ) ) {
								$wpssw_data[ $wpssw_key ] = $wpssw_data[ $wpssw_regular_price_key ];
							} elseif ( empty( $wpssw_regular_price ) && empty( $wpssw_sale_price ) ) {
								$wpssw_data[ $wpssw_regular_price_key ] = $wpssw_data[ $wpssw_key ];
							}
						}
						self::wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
						continue;
					}
					if ( '_downloadable_files' === (string) $wpssw_meta_key ) {
						$product = wc_get_product( $wpssw_productid );
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {

							$wpssw_downloadable_filenames    = array();
							$wpssw_downloadable_files        = array();
							$wpssw_downloadable_files        = explode( ',', $wpssw_data[ $wpssw_key ] );
							$wpssw_downloadable_filename_key = array_search( 'Product Downloadable File Names', $wpssw_woo_selections, true );
							if ( false !== $wpssw_downloadable_filename_key ) {
								$wpssw_downloadable_filenames = isset( $wpssw_data[ $wpssw_downloadable_filename_key ] ) ? explode( ',', $wpssw_data[ $wpssw_downloadable_filename_key ] ) : array();
							}
							$files_data = array();
							foreach ( $product->get_downloads() as $downloads ) {
								$file_data['url']  = $downloads->get_file();
								$file_data['name'] = $downloads->get_name();
								$file_data['id']   = $downloads->get_id();
								$files_data[]      = $file_data;
							}
							$wpssw_downloadable_files_count = count( $wpssw_downloadable_files );
							for ( $i = 0;$i < $wpssw_downloadable_files_count;$i++ ) {

								if ( in_array( $wpssw_downloadable_files[ $i ], array_column( $files_data, 'url' ), true ) ) {
									$key  = array_search( $wpssw_downloadable_files[ $i ], array_column( $files_data, 'url' ), true );
									$name = isset( $files_data[ $key ]['name'] ) ? $files_data[ $key ]['name'] : '';
									$id   = isset( $files_data[ $key ]['id'] ) ? $files_data[ $key ]['id'] : '';

									$download = array();
									if ( isset( $wpssw_downloadable_filenames[ $i ] ) && $name !== $wpssw_downloadable_filenames[ $i ] ) {
										$download['name'] = $wpssw_downloadable_filenames[ $i ];
									} else {
										$download['name'] = $name;
									}
									$download['download_id'] = $id;
									$download['file']        = $wpssw_downloadable_files[ $i ];
									$downloads_array[]       = $download;
								} else {
									$download                = array();
									$download['download_id'] = '';
									$name                    = '';
									if ( isset( $wpssw_downloadable_filenames[ $i ] ) && ! empty( $wpssw_downloadable_filenames[ $i ] ) ) {
										$name = $wpssw_downloadable_filenames[ $i ];
									} else {
										$pathinfo = pathinfo( $wpssw_downloadable_files[ $i ] );
										$name     = isset( $pathinfo['filename'] ) ? $pathinfo['filename'] : '';
									}
									$download['name']  = $name;
									$download['file']  = $wpssw_downloadable_files[ $i ];
									$downloads_array[] = $download;
								}
							}
							if ( ! empty( $downloads_array ) ) {
								$product->set_downloads( $downloads_array );
							}
						} else {
							$downloads_array = array();
							$product->set_downloads( $downloads_array );
						}
						continue;
					}
					$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data[ $wpssw_key ];
					self::wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
				}

				if ( in_array( $wpssw_header, $wpssw_attribute_taxonomies, true ) ) {
					$product = wc_get_product( $wpssw_productid );
					$color   = $product->get_attribute( strtolower( $wpssw_header ) );
					if ( isset( $wpssw_data[ $wpssw_key ] ) && ! empty( $wpssw_data[ $wpssw_key ] ) ) {

						$attribute_object = new WC_Product_Attribute();
						if ( 'insert' === (string) trim( $wpssw_opration ) ) {
							$wpssw_data[ $wpssw_key ] = str_replace( ',', ' | ', $wpssw_data[ $wpssw_key ] );
							$attribute_object->set_variation( true );
						}
						$attribute_object->set_name( $wpssw_header );
						$attribute_object->set_options( array( $wpssw_data[ $wpssw_key ] ) );

						$wpssw_attributes[] = $attribute_object;
					} else {
						$wpssw_attributes = array();
					}
					$product->set_attributes( $wpssw_attributes );
				}
			}

			$wpssw_updated_array['ID'] = $wpssw_productid;

			wp_update_post( $wpssw_updated_array );
			$product = wc_get_product( $wpssw_productid );
			$product->save();
		}
		/**
		 * Update imported product
		 *
		 * @param int   $wpssw_productid product id.
		 * @param array $wpssw_data product data array.
		 */
		public static function wpssw_update_variation_product( $wpssw_productid, $wpssw_data ) {

			if ( ! self::$instance_api->checkcredenatials() ) {
				return;
			}
			if ( (int) $wpssw_productid < 1 ) {
				return;
			}
			$wpssw_woo_selections = stripslashes_deep( parent::wpssw_option( 'wpssw_woo_product_headers' ) );

			if ( ! $wpssw_woo_selections ) {
				return;
			}
			array_unshift( $wpssw_woo_selections, 'Product Id', 'Product Variation Id' );
			$wpssw_include = new WPSSW_Include_Action();
			$wpssw_include->wpssw_include_product_compatibility_files();
			$wpssw_wooproduct_headers = apply_filters( 'wpsyncsheets_product_headers', array() );
			$wpssw_default_header     = $wpssw_wooproduct_headers['WPSSW_Default_Headers']['variable'];

			$wpssw_variation_attribute_header = $wpssw_wooproduct_headers['WPSSW_Default_Headers']['variation'];
			$wpssw_attribute_taxonomies       = WPSSW_Product::wpssw_get_all_attributes();
			$wpssw_updated_array              = array();
			$wpssw_attributes                 = array();
			$wpssw_crud_operation             = array( 'Insert', 'Update', 'Delete' );

			foreach ( $wpssw_woo_selections as $wpssw_key => $wpssw_header ) {
				if ( in_array( $wpssw_header, $wpssw_crud_operation, true ) ) {
					continue;
				}
				if ( in_array( $wpssw_header, $wpssw_default_header, true ) ) {
					$wpssw_meta_key = array_search( $wpssw_header, $wpssw_default_header, true );

					if ( isset( $wpssw_data[ $wpssw_key ] ) && $wpssw_meta_key ) {
						if ( is_numeric( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_data[ $wpssw_key ] = (int) $wpssw_data[ $wpssw_key ];
						}
					}
					if ( '_downloadable_file_names' === (string) $wpssw_meta_key ) {
						continue;
					}
					if ( '_raw_image' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_images = explode( '|', $wpssw_data[ $wpssw_key ] );
						} else {
							$wpssw_images = array();
						}
						$wpssw_imagedata['raw_image'] = array_shift( $wpssw_images );
						if ( ! empty( $wpssw_images ) ) {
							$wpssw_imagedata['raw_gallery_image'] = $wpssw_images;
						}
						self::wpssw_set_image_data( $wpssw_productid, $wpssw_imagedata );
						continue;
					}
					if ( 'dimensions' === (string) $wpssw_meta_key ) {
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_data[ $wpssw_key ] = str_replace( array( ' ', 'cm' ), '', $wpssw_data[ $wpssw_key ] );
							$dimensions               = explode( '×', $wpssw_data[ $wpssw_key ] );
							$length                   = isset( $dimensions[0] ) ? $dimensions[0] : '';
							$width                    = isset( $dimensions[1] ) ? $dimensions[1] : '';
							$height                   = isset( $dimensions[2] ) ? $dimensions[2] : '';

							self::wpssw_update_post_meta( $wpssw_productid, '_length', $length );
							self::wpssw_update_post_meta( $wpssw_productid, '_width', $width );
							self::wpssw_update_post_meta( $wpssw_productid, '_height', $height );
						}
						continue;
					}
					if ( '_downloadable_files' === (string) $wpssw_meta_key ) {
						$wpssw_product = wc_get_product( $wpssw_productid );
						if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
							$wpssw_downloadable_files        = explode( ',', $wpssw_data[ $wpssw_key ] );
							$wpssw_downloadable_filename_key = array_search( 'Product Variation Downloadable File Names', $wpssw_woo_selections, true );
							$wpssw_downloadable_filenames    = array();
							if ( false !== $wpssw_downloadable_filename_key ) {
								$wpssw_downloadable_filenames = isset( $wpssw_data[ $wpssw_downloadable_filename_key ] ) ? explode( ',', $wpssw_data[ $wpssw_downloadable_filename_key ] ) : array();
							}
							$files_data = array();
							foreach ( $wpssw_product->get_downloads() as $downloads ) {
								$file_data['url']  = $downloads->get_file();
								$file_data['name'] = $downloads->get_name();
								$file_data['id']   = $downloads->get_id();
								$files_data[]      = $file_data;
							}
							$wpssw_downloadable_files_count = count( $wpssw_downloadable_files );
							for ( $i = 0;$i < $wpssw_downloadable_files_count;$i++ ) {

								if ( in_array( $wpssw_downloadable_files[ $i ], array_column( $files_data, 'url' ), true ) ) {
									$key  = array_search( $wpssw_downloadable_files[ $i ], array_column( $files_data, 'url' ), true );
									$name = isset( $files_data[ $key ]['name'] ) ? $files_data[ $key ]['name'] : '';
									$id   = isset( $files_data[ $key ]['id'] ) ? $files_data[ $key ]['id'] : '';

									$download = array();
									if ( isset( $wpssw_downloadable_filenames[ $i ] ) && $name !== $wpssw_downloadable_filenames[ $i ] ) {
										$download['name'] = $wpssw_downloadable_filenames[ $i ];
									} else {
										$download['name'] = $name;
									}
									$download['download_id'] = $id;
									$download['file']        = $wpssw_downloadable_files[ $i ];
									$downloads_array[]       = $download;
								} else {
									$download                = array();
									$download['download_id'] = '';
									$name                    = '';
									if ( isset( $wpssw_downloadable_filenames[ $i ] ) && ! empty( $wpssw_downloadable_filenames[ $i ] ) ) {
										$name = $wpssw_downloadable_filenames[ $i ];
									} else {
										$pathinfo = pathinfo( $wpssw_downloadable_files[ $i ] );
										$name     = isset( $pathinfo['filename'] ) ? $pathinfo['filename'] : '';
									}
									$download['name']  = $name;
									$download['file']  = $wpssw_downloadable_files[ $i ];
									$downloads_array[] = $download;
								}
							}
							if ( ! empty( $downloads_array ) ) {
								$wpssw_product->set_downloads( $downloads_array );
							}
						} else {
							$downloads_array = array();
							$wpssw_product->set_downloads( $downloads_array );
						}
						continue;
					}
					$wpssw_updated_array[ $wpssw_meta_key ] = $wpssw_data[ $wpssw_key ];
					self::wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_data[ $wpssw_key ] );
				}
				if ( in_array( $wpssw_header, $wpssw_variation_attribute_header, true ) ) {
					$wpssw_product    = wc_get_product( $wpssw_productid );
					$wpssw_attr_name  = 'attribute_';
					$wpssw_pattr_name = 'attribute_pa_';
					$wpssw_attrname   = strtolower( trim( str_replace( 'Variation: ', '', $wpssw_header ) ) );

					$wpssw_attr_name         .= $wpssw_attrname;
					$wpssw_pattr_name        .= $wpssw_attrname;
					$wpssw_selected_variation = $wpssw_product->get_variation_attributes();

					if ( ! empty( $wpssw_data[ $wpssw_key ] ) ) {
						if ( isset( $wpssw_selected_variation[ $wpssw_attr_name ] ) ) {
							self::wpssw_update_post_meta( $wpssw_productid, $wpssw_attr_name, $wpssw_data[ $wpssw_key ] );
						} elseif ( isset( $wpssw_selected_variation[ $wpssw_pattr_name ] ) ) {
							self::wpssw_update_post_meta( $wpssw_productid, $wpssw_pattr_name, $wpssw_data[ $wpssw_key ] );
						}
					}
				}
			}
			$wpssw_updated_array['ID'] = $wpssw_productid;
			wp_update_post( $wpssw_updated_array );
			$wpssw_product = wc_get_product( $wpssw_productid );
			$wpssw_product->save();
		}
		/**
		 * Set image data for product
		 *
		 * @param int   $wpssw_productid product id for which image data need to set.
		 * @param array $wpssw_data image data array.
		 */
		public static function wpssw_set_image_data( $wpssw_productid, $wpssw_data ) {
			$wpssw_product = wc_get_product( $wpssw_productid );

			// Image URLs need converting to IDs before inserting.
			if ( isset( $wpssw_data['raw_image'] ) ) {
				$wpssw_image_id = self::wpssw_get_attachment_id_from_url( $wpssw_data['raw_image'], $wpssw_product->get_id() );

				if ( $wpssw_image_id ) {
					$wpssw_product->set_image_id( $wpssw_image_id );
				} else {
					$wpssw_product->set_image_id( '' );
					$wpssw_image_id = '';
				}
				self::wpssw_update_post_meta( $wpssw_productid, '_thumbnail_id', $wpssw_image_id );
			}
			// Gallery image URLs need converting to IDs before inserting.
			if ( isset( $wpssw_data['raw_gallery_image'] ) ) {
				$gallery_image_ids = array();
				foreach ( $wpssw_data['raw_gallery_image'] as $image_id ) {
					$gallery_image_id = self::wpssw_get_attachment_id_from_url( $image_id, $wpssw_product->get_id() );
					if ( $gallery_image_id ) {
						$gallery_image_ids[] = $gallery_image_id;
					}
				}
				self::wpssw_update_post_meta( $wpssw_productid, '_product_image_gallery', implode( ',', array_filter( $gallery_image_ids ) ) );
			}
		}
		/**
		 * Get image attachment id from image url
		 *
		 * @param string $url image url.
		 * @param int    $product_id .
		 */
		public static function wpssw_get_attachment_id_from_url( $url, $product_id ) {
			if ( empty( $url ) ) {
				return 0;
			}
			$id         = 0;
			$upload_dir = wp_upload_dir( null, false );
			$base_url   = $upload_dir['baseurl'] . '/';
			// Check first if attachment is inside the WordPress uploads directory, or we're given a filename only.
			if ( false !== strpos( $url, $base_url ) || false === strpos( $url, '://' ) ) {
				// Search for yyyy/mm/slug.extension or slug.extension - remove the base URL.
				$file = str_replace( $base_url, '', $url );
				$args = array(
					'post_type'   => 'attachment',
					'post_status' => 'any',
					'fields'      => 'ids',
					'meta_query' => array(// @codingStandardsIgnoreLine.
						'relation' => 'OR',
						array(
							'key'     => '_wp_attached_file',
							'value'   => '^' . $file,
							'compare' => 'REGEXP',
						),
						array(
							'key'     => '_wp_attached_file',
							'value'   => '/' . $file,
							'compare' => 'LIKE',
						),
						array(
							'key'     => '_wpssw_attachment_source',
							'value'   => '/' . $file,
							'compare' => 'LIKE',
						),
					),
				);
			} else {
				// This is an external URL, so compare to source.
				$args = array(
					'post_type'   => 'attachment',
					'post_status' => 'any',
					'fields'      => 'ids',
					'meta_query' => array(// @codingStandardsIgnoreLine.
						array(
							'value' => $url,
							'key'   => '_wpssw_attachment_source',
						),
					),
				);
			}
			$ids = get_posts($args); // @codingStandardsIgnoreLine.
			if ( $ids ) {
				$id = current( $ids );
			}
			// Upload if attachment does not exists.
			if ( ! $id && stristr( $url, '://' ) ) {
				add_filter( 'https_ssl_verify', '__return_false' );
				$upload = wc_rest_upload_image_from_url( $url );
				if ( is_wp_error( $upload ) ) {
					return;
				}
				$id = wc_rest_set_uploaded_image_as_attachment( $upload, $product_id );
				if ( ! wp_attachment_is_image( $id ) ) {
					return;
				}
				// Save attachment source for future reference.
				self::wpssw_update_post_meta( $id, '_wpssw_attachment_source', $url );
			}
			if ( ! $id ) {
				return;
			}
			return $id;
		}
		/**
		 * Parse categories field
		 *
		 * @param array $value category value array.
		 */
		public static function wpssw_parse_categories_field( $value ) {
			if ( empty( $value ) ) {
					return array();
			}
			$row_terms  = $value;
			$categories = array();
			foreach ( $row_terms as $row_term ) {
					$parent = null;
					$_terms = array_map( 'trim', explode( '>', $row_term ) );
					$total  = count( $_terms );
				foreach ( $_terms as $index => $_term ) {
						// Check if category exists. Parent must be empty string or null if doesn't exists.
						$term = term_exists( $_term, 'product_cat', $parent );
					if ( is_array( $term ) ) {
							$term_id = $term['term_id'];
							// Don't allow users without capabilities to create new categories.
					} elseif ( ! current_user_can( 'manage_product_terms' ) ) {
							break;
					} else {
							$term = wp_insert_term( $_term, 'product_cat', array( 'parent' => intval( $parent ) ) );
						if ( is_wp_error( $term ) ) {
								break; // We cannot continue if the term cannot be inserted.
						}
							$term_id = $term['term_id'];
					}
						// Only requires assign the last category.
					if ( ( 1 + $index ) === $total ) {
							$categories[] = $term_id;
					} else {
							// Store parent to be able to insert or query categories based in parent ID.
							$parent = $term_id;
					}
				}
			}
			return $categories;
		}
		/**
		 * Update post meta
		 *
		 * @param int          $wpssw_productid post id to update post meta.
		 * @param string       $wpssw_meta_key meta key to update.
		 * @param string|array $wpssw_data value for meta key.
		 */
		public static function wpssw_update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_data ) {
			if ( ! $wpssw_productid ) {
				return;
			}
			update_post_meta( $wpssw_productid, $wpssw_meta_key, $wpssw_data );
		}
	}
	new WPSSW_Product_Import();
endif;
