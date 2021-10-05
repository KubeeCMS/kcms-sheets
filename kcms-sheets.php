<?php
/**
 * Plugin Name: KCMS Sheets
 * Plugin URI: https://github.com/KubeeCMS/kcms-sheets/
 * Description: Sync with your Orders, Products, Customers, Coupons, and Events to a single Google Spreadsheet.
 * Author: KubeeCMS
 * Author URI: https://github.com/KubeeCMS/
 * Text Domain: wpssw
 * Domain Path: /languages
 * Version: 6.3
 * WC tested up to: 4.6.0
 *
 * @package     wpsyncsheets-for-woocommerce
 * @author      KubeeCMS
 * @category    Plugin
 * @copyright   Copyright (c) 2021 KubeeCMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
define( 'WPSSW_PLUGIN_SECURITY', 1 );
define( 'WPSSW_URL', plugin_dir_url( __FILE__ ) );
define( 'WPSSW_VERSION', '6.3' );
define( 'WPSSW_PLUGIN_ID', '22636997' );
define( 'WPSSW_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WPSSW_DIRECTORY', dirname( plugin_basename( __FILE__ ) ) );
define( 'WPSSW_PLUGIN_SLUG', WPSSW_DIRECTORY . '/' . basename( __FILE__ ) );
define( 'WPSSW_BASE_FILE', basename( dirname( __FILE__ ) ) . '/wpsyncsheets-for-woocommerce.php' );
define( 'WPSSW_DOC_MENU_URL', 'https://docs.wpsyncsheets.com' );
define( 'WPSSW_SUPPORT_MENU_URL', 'https://support.wpsyncsheets.com/index.php/signup' );
if ( ! class_exists( 'WPSSW_Dependencies' ) ) {
	require_once trailingslashit( dirname( __FILE__ ) ) . 'includes/class-wpssw-dependencies.php';
}
// Check WPSSW Dependency Class.
if ( WPSSW_Dependencies::wpssw_is_woocommerce_active() ) {
	// Add methods if WooCommerce is active.
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wpssw_add_action_links' );
	/**
	 * Add Settings Link.
	 *
	 * @param array $links Links array.
	 */
	function wpssw_add_action_links( $links ) {
		$mylinks = array(
			'<a href="' . admin_url( 'admin.php?page=wpsyncsheets-for-woocommerce' ) . '">Settings</a>',
		);
		return array_merge( $mylinks, $links );
	}
	require_once dirname( __FILE__ ) . '/src/class-wpsyncsheets-for-woocommerce.php';
	wpssw();
} else {
	add_action( 'admin_notices', 'wpssw_wc_admin_notice' );
	if ( ! function_exists( 'wpssw_wc_admin_notice' ) ) {
		/**
		 * Add plugin missing notice.
		 */
		function wpssw_wc_admin_notice() {
			global $pagenow;
			// phpcs:ignore
			if ( 'plugins.php' === (string) $pagenow || ( isset( $_GET['page'] ) && ( 'wpsyncsheets-for-woocommerce' === (string) sanitize_text_field( $_GET['page'] ) ) ) ) {
				echo '<div class="notice error wpssw-error">
				<div>
					<p>WPSyncSheets For WooCommerce plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!</p>
				</div>
			</div>';
			}
		}
	}
}
