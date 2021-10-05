<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
/**
 * Class WPSSW_Include_Action.
 */
class WPSSW_Include_Action {
	/**
	 * Include Order compatibility files.
	 */
	public function wpssw_include_order_compatibility_files() {
		require_once WPSSW_PLUGIN_PATH . '/includes/order/class-wpssw-order-utils.php';
		foreach ( glob( WPSSW_PLUGIN_PATH . '/includes/order/compatibility/*.php' ) as $filename ) {
			include $filename;
		}
	}
	/**
	 * Include Product compatibility files.
	 */
	public function wpssw_include_product_compatibility_files() {
		require_once WPSSW_PLUGIN_PATH . '/includes/product/class-wpssw-product-utils.php';
		foreach ( glob( WPSSW_PLUGIN_PATH . '/includes/product/compatibility/*.php' ) as $filename ) {
			include $filename;
		}
	}
	/**
	 * Include Customer compatibility files.
	 */
	public function wpssw_include_customer_compatibility_files() {
		require_once WPSSW_PLUGIN_PATH . '/includes/customer/class-wpssw-customer-utils.php';
		foreach ( glob( WPSSW_PLUGIN_PATH . '/includes/customer/compatibility/*.php' ) as $filename ) {
			include $filename;
		}
	}
	/**
	 * Include Coupon compatibility files.
	 */
	public function wpssw_include_coupon_compatibility_files() {
		require_once WPSSW_PLUGIN_PATH . '/includes/coupon/class-wpssw-coupon-utils.php';
		foreach ( glob( WPSSW_PLUGIN_PATH . '/includes/coupon/compatibility/*.php' ) as $filename ) {
			include $filename;
		}
	}
	/**
	 * Include Customer compatibility files.
	 */
	public function wpssw_include_event_compatibility_files() {
		require_once WPSSW_PLUGIN_PATH . '/includes/event/class-wpssw-event-utils.php';
		foreach ( glob( WPSSW_PLUGIN_PATH . '/includes/event/compatibility/*.php' ) as $filename ) {
			include $filename;
		}
	}
	/**
	 * Include Coupon hooks.
	 */
	public function wpssw_include_coupon_hook() {
		add_action( 'woocommerce_coupon_object_updated_props', 'WPSSW_Coupon::wpssw_coupon_object_updated_props' );
	}
	/**
	 * Include Coupon ajax hooks.
	 */
	public function wpssw_include_coupon_ajax_hook() {
		add_action( 'wp_ajax_wpssw_clear_couponsheet', 'WPSSW_Coupon::wpssw_clear_couponsheet' );
		add_action( 'wp_ajax_wpssw_get_coupon_count', 'WPSSW_Coupon::wpssw_get_coupon_count' );
		add_action( 'wp_ajax_wpssw_sync_coupons', 'WPSSW_Coupon::wpssw_sync_coupons' );
	}
	/**
	 * Include Event ajax hooks.
	 */
	public function wpssw_include_event_ajax_hook() {
		add_action( 'wp_ajax_wpssw_clear_eventsheet', 'WPSSW_Event::wpssw_clear_eventsheet' );
		add_action( 'wp_ajax_wpssw_get_events_count', 'WPSSW_Event::wpssw_get_events_count' );
		add_action( 'wp_ajax_wpssw_sync_events', 'WPSSW_Event::wpssw_sync_events' );
	}
	/**
	 * Include Customer hooks.
	 */
	public function wpssw_include_customer_hook() {
		add_action( 'edit_user_profile_update', 'WPSSW_Customer::edit_user_profile_update', 10, 1 );
		add_action( 'delete_user', 'WPSSW_Customer::wpssw_delete_user' );
		add_action( 'user_register', 'WPSSW_Customer::wpssw_user_registration_save' );
		add_action( 'woocommerce_save_account_details', 'WPSSW_Customer::edit_user_profile_update' );
		add_action( 'woocommerce_checkout_update_user_meta', 'WPSSW_Customer::action_woocommerce_checkout_update_customer', 10, 2 );
	}
	/**
	 * Include Customer ajax hooks.
	 */
	public function wpssw_include_customer_ajax_hook() {
		add_action( 'wp_ajax_wpssw_clear_custmoersheet', 'WPSSW_Customer::wpssw_clear_custmoersheet' );
		add_action( 'wp_ajax_wpssw_get_customer_count', 'WPSSW_Customer::wpssw_get_customer_count' );
		add_action( 'wp_ajax_wpssw_sync_customers', 'WPSSW_Customer::wpssw_sync_customers' );
	}
	/**
	 * Include Product hooks.
	 */
	public function wpssw_include_product_hook() {
		add_action( 'woocommerce_update_product', 'WPSSW_Product::wpssw_woocommerce_update_product', 99, 2 );
	}
	/**
	 * Include Product ajax hooks.
	 */
	public function wpssw_include_product_ajax_hook() {
		add_action( 'wp_ajax_wpssw_get_product_count', 'WPSSW_Product::wpssw_get_product_count' );
		add_action( 'wp_ajax_wpssw_sync_products', 'WPSSW_Product::wpssw_sync_products' );
		add_action( 'wp_ajax_wpssw_clear_productsheet', 'WPSSW_Product::wpssw_clear_productsheet' );
	}
	/**
	 * Include Product import ajax hooks.
	 */
	public function wpssw_include_product_import_ajax_hook() {
		add_action( 'wp_ajax_wpssw_get_product_import_count', 'WPSSW_Product_Import::wpssw_get_product_import_count' );
		add_action( 'wp_ajax_wpssw_product_import', 'WPSSW_Product_Import::wpssw_product_import' );
	}
	/**
	 * Include Order import ajax hooks.
	 */
	public function wpssw_include_order_import_ajax_hook() {
		add_action( 'wp_ajax_wpssw_get_order_import_count', 'WPSSW_Order_Import::wpssw_get_order_import_count' );
		add_action( 'wp_ajax_wpssw_order_import', 'WPSSW_Order_Import::wpssw_order_import' );
	}
	/**
	 * Include Order hooks.
	 */
	public function wpssw_include_order_hook() {
		add_filter( 'manage_edit-shop_order_columns', 'WPSSW_Order::wpssw_shop_order_page_syncbtn_column' );
		add_action( 'manage_shop_order_posts_custom_column', 'WPSSW_Order::wpssw_shop_order_page_syncbtn', 10, 2 );
		add_action( 'add_meta_boxes', 'WPSSW_Order::wpssw_add_syncbtn_meta_box' );
		add_action( 'woocommerce_order_status_changed', 'WPSSW_Order::wpssw_woo_order_status_change_custom', 40, 3 );
		add_action( 'woocommerce_process_shop_order_meta', 'WPSSW_Order::wpssw_wc_woocommerce_process_post_meta', 60, 2 );
		add_action( 'woocommerce_update_options_google_sheet_settings', 'WPSSW_Order::wpssw_update_settings' );
		add_filter( 'woocommerce_get_cart_item_from_session', 'WPSSW_Order::wpssw_get_cart_item_from_session', 20, 2 );
		add_filter( 'woocommerce_add_cart_item_data', 'WPSSW_Order::wpssw_add_cart_item_data', 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', 'WPSSW_Order::wpssw_order_line_item', 10, 3 );
		add_action( 'transition_post_status', 'WPSSW_Order::wpssw_wcgs_restore', 10, 3 );
		add_action( 'woocommerce_checkout_update_order_meta', 'WPSSW_Order::wpssw_woocommerce_checkout_update_order_meta', 20, 1 );
		add_action( 'wpssw_cron_run', 'WPSSW_Order::wpssw_cron_run', 10 );
		// phpcs:ignore
		add_filter( 'cron_schedules', 'WPSSW_Order::wpssw_add_cron_interval' );
	}
	/**
	 * Include Order fields hooks.
	 */
	public function wpssw_include_orderfield_hook() {
		add_action( 'woocommerce_admin_field_set_headers', 'WPSSW_Order::wpssw_woocommerce_admin_field_set_headers', 10, 0 );
		add_action( 'woocommerce_admin_field_product_headers', 'WPSSW_Order::wpssw_woocommerce_admin_field_product_headers', 10, 0 );
		add_action( 'woocommerce_admin_field_product_as_sheet_header', 'WPSSW_Order::wpssw_woocommerce_admin_field_product_as_sheet_header', 10, 0 );
		add_action( 'woocommerce_admin_field_product_headers_append_after', 'WPSSW_Order::wpssw_woocommerce_admin_field_product_headers_append_after', 10, 0 );
		add_action( 'woocommerce_admin_field_manage_row_field', 'WPSSW_Order::wpssw_woocommerce_admin_field_manage_row_field', 10, 0 );
		add_action( 'woocommerce_admin_field_sync_button', 'WPSSW_Order::wpssw_woocommerce_admin_field_sync_button', 10, 0 );
		add_action( 'woocommerce_admin_field_custom_headers_action', 'WPSSW_Order::wpssw_woocommerce_admin_field_custom_headers_action', 10, 0 );
		add_action( 'woocommerce_admin_field_repeat_checkbox', 'WPSSW_Order::wpssw_woocommerce_admin_field_repeat_checkbox', 10, 0 );
		add_action( 'woocommerce_admin_field_new_spreadsheetname', 'WPSSW_Order::wpssw_woocommerce_admin_field_new_spreadsheetname', 10, 0 );
		add_action( 'woocommerce_admin_field_product_category_as_order_filter', 'WPSSW_Order::wpssw_woocommerce_admin_field_product_category_as_order_filter', 10, 0 );
		add_action( 'woocommerce_admin_field_select_spreadsheet', 'WPSSW_Order::wpssw_woocommerce_admin_field_select_spreadsheet' );
		add_action( 'woocommerce_admin_field_order_row_color', 'WPSSW_Order::wpssw_woocommerce_admin_field_order_row_color' );
		add_action( 'woocommerce_admin_field_price_format', 'WPSSW_Order::wpssw_woocommerce_admin_field_price_format' );
		add_action( 'woocommerce_admin_field_order_asc_desc', 'WPSSW_Order::wpssw_woocommerce_admin_field_order_asc_desc' );
		add_action( 'woocommerce_admin_field_add_graph_section', 'WPSSW_Order::wpssw_woocommerce_admin_field_add_graph_section', 10, 0 );
		add_action( 'woocommerce_admin_field_import_orders', 'WPSSW_Order::wpssw_woocommerce_admin_field_import_orders' );
	}
	/**
	 * Include Order ajax hooks.
	 */
	public function wpssw_include_order_ajax_hook() {
		add_action( 'wp_ajax_wpssw_sync_single_order_data', 'WPSSW_Order::wpssw_sync_single_order_data' );
		add_action( 'wp_ajax_wpssw_clear_all_sheet', 'WPSSW_Order::wpssw_clear_all_sheet' );
		add_action( 'wp_ajax_wpssw_check_existing_sheet', 'WPSSW_Order::wpssw_check_existing_sheet' );
		add_action( 'wp_ajax_wpssw_export_order', 'WPSSW_Order::wpssw_export_order' );
		add_action( 'wp_ajax_wpssw_get_product_list', 'WPSSW_Order::wpssw_get_product_list' );
		add_action( 'wp_ajax_wpssw_get_category_list', 'WPSSW_Order::wpssw_get_category_list' );
		add_action( 'wp_ajax_wpssw_get_orders_count', 'WPSSW_Order::wpssw_get_orders_count' );
		add_action( 'wp_ajax_wpssw_sync_sheetswise', 'WPSSW_Order::wpssw_sync_sheetswise' );
		add_action( 'wp_ajax_wpssw_regenerate_graph', 'WPSSW_Order::wpssw_regenerate_graph' );
	}
	/**
	 * Include Plugin hooks.
	 */
	public function wpssw_include_plugin_hook() {
		add_action( 'admin_menu', 'WPSSW_Setting::wpssw_menu_page' );
		add_action( 'wp_trash_post', 'WPSSW_Setting::wpssw_wcgs_trash' );
		add_action( 'untrashed_post', 'WPSSW_Setting::wpssw_wcgs_untrash', 10, 2 );
		add_action( 'admin_enqueue_scripts', 'WPSSW_Setting::wpssw_load_custom_wp_admin_style', 30 );
		add_filter( 'plugin_row_meta', 'WPSSW_Setting::wpssw_plugin_row_meta', 10, 2 );
		add_action( 'plugins_loaded', 'WPSSW_Setting::wpssw_load_textdomain', 10 );
		add_action( 'wp_ajax_wpssw_reset_settings', 'WPSSW_Setting::wpssw_reset_settings' );
	}
}
