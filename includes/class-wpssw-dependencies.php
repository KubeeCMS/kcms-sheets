<?php
/**
 * Main WPSyncSheets_For_WooCommerce namespace.
 *
 * @since 1.0.0
 * @package wpsyncsheets-for-woocommerce
 */

/**
 * WPSSW Dependency Checker
 */
class WPSSW_Dependencies {
	/**
	 * Active plugins
	 *
	 * @var $active_plugins
	 */
	private static $active_plugins;
	/**
	 * Initialization
	 */
	public static function init() {
		if ( is_multisite() ) {
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			// Check WooCommerce active at the network site.
			if ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
				self::$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
			} else { // Check WooCommerce active at the network individual site.
				self::$active_plugins = (array) get_option( 'active_plugins', array() );
			}
		} else {
			self::$active_plugins = (array) get_option( 'active_plugins', array() );
		}
	}
	/**
	 * Check woocommerce exist
	 *
	 * @return Boolean
	 */
	public static function wpssw_woocommerce_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}
		return in_array( 'woocommerce/woocommerce.php', self::$active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins );
	}
	/**
	 * Check if woocommerce active
	 *
	 * @return Boolean
	 */
	public static function wpssw_is_woocommerce_active() {
		return self::wpssw_woocommerce_active_check();
	}
}
