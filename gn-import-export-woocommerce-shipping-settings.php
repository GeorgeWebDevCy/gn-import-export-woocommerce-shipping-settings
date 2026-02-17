<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.georgenicolaou.me/
 * @since             1.0.0
 * @package           Gn_Import_Export_Woocommerce_Shipping_Settings
 *
 * @wordpress-plugin
 * Plugin Name:       GN Import Export Woocommerce Shipping Settings
 * Plugin URI:        https://www.georgenicolaou.me/gn-import-export-woocommerce-shipping-settings
 * Description:       Import & Export Woocommerce shipping zones, rates and other hsipping settings easily
 * Version:           1.0.0
 * Author:            George Nicolaou
 * Author URI:        https://www.georgenicolaou.me//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gn-import-export-woocommerce-shipping-settings
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load Composer dependencies when available.
$gn_import_export_woocommerce_shipping_settings_autoloader = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
if ( file_exists( $gn_import_export_woocommerce_shipping_settings_autoloader ) ) {
	require_once $gn_import_export_woocommerce_shipping_settings_autoloader;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'GN_IMPORT_EXPORT_WOOCOMMERCE_SHIPPING_SETTINGS_VERSION', '1.0.0' );

/**
 * Check whether WooCommerce is active.
 *
 * @since    1.0.0
 * @return   bool True when WooCommerce is active.
 */
function gn_import_export_woocommerce_shipping_settings_is_woocommerce_active() {
	if ( class_exists( 'WooCommerce' ) ) {
		return true;
	}

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		return true;
	}

	return is_multisite() && is_plugin_active_for_network( 'woocommerce/woocommerce.php' );
}

/**
 * Deactivate this plugin.
 *
 * @since    1.0.0
 * @param    bool|null $network_wide Whether to deactivate network-wide.
 */
function gn_import_export_woocommerce_shipping_settings_deactivate_self( $network_wide = null ) {
	if ( ! function_exists( 'deactivate_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	deactivate_plugins( plugin_basename( __FILE__ ), true, $network_wide );
}

/**
 * Prevent this plugin from staying active when WooCommerce is missing/inactive.
 *
 * @since    1.0.0
 */
function gn_import_export_woocommerce_shipping_settings_deactivate_without_woocommerce() {
	if ( gn_import_export_woocommerce_shipping_settings_is_woocommerce_active() ) {
		return;
	}

	gn_import_export_woocommerce_shipping_settings_deactivate_self();
}

/**
 * Deactivate this plugin if WooCommerce is deactivated.
 *
 * @since    1.0.0
 * @param    string $plugin            The plugin being deactivated.
 * @param    bool   $network_deactivate Whether deactivation is network-wide.
 */
function gn_import_export_woocommerce_shipping_settings_handle_woocommerce_deactivation( $plugin, $network_deactivate ) {
	if ( 'woocommerce/woocommerce.php' !== $plugin ) {
		return;
	}

	gn_import_export_woocommerce_shipping_settings_deactivate_self( $network_deactivate );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gn-import-export-woocommerce-shipping-settings-activator.php
 */
function activate_gn_import_export_woocommerce_shipping_settings( $network_wide = false ) {
	if ( ! gn_import_export_woocommerce_shipping_settings_is_woocommerce_active() ) {
		gn_import_export_woocommerce_shipping_settings_deactivate_self( $network_wide );

		wp_die(
			esc_html__( 'GN Import Export WooCommerce Shipping Settings requires WooCommerce to be installed and active.', 'gn-import-export-woocommerce-shipping-settings' ),
			esc_html__( 'Plugin dependency check', 'gn-import-export-woocommerce-shipping-settings' ),
			array( 'back_link' => true )
		);
	}

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gn-import-export-woocommerce-shipping-settings-activator.php';
	Gn_Import_Export_Woocommerce_Shipping_Settings_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-gn-import-export-woocommerce-shipping-settings-deactivator.php
 */
function deactivate_gn_import_export_woocommerce_shipping_settings() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gn-import-export-woocommerce-shipping-settings-deactivator.php';
	Gn_Import_Export_Woocommerce_Shipping_Settings_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gn_import_export_woocommerce_shipping_settings' );
register_deactivation_hook( __FILE__, 'deactivate_gn_import_export_woocommerce_shipping_settings' );
add_action( 'deactivated_plugin', 'gn_import_export_woocommerce_shipping_settings_handle_woocommerce_deactivation', 10, 2 );

if ( ! gn_import_export_woocommerce_shipping_settings_is_woocommerce_active() ) {
	if ( is_admin() ) {
		add_action( 'admin_init', 'gn_import_export_woocommerce_shipping_settings_deactivate_without_woocommerce' );
	}
	return;
}

/**
 * Register update checks against the public GitHub repository.
 *
 * @since    1.0.0
 */
function gn_import_export_woocommerce_shipping_settings_register_updates() {
	if ( ! class_exists( '\YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
		return;
	}

	$update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		'https://github.com/GeorgeWebDevCy/gn-import-export-woocommerce-shipping-settings/',
		__FILE__,
		'gn-import-export-woocommerce-shipping-settings'
	);

	$update_checker->setBranch( 'main' );
}
gn_import_export_woocommerce_shipping_settings_register_updates();

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gn-import-export-woocommerce-shipping-settings.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_gn_import_export_woocommerce_shipping_settings() {

	$plugin = new Gn_Import_Export_Woocommerce_Shipping_Settings();
	$plugin->run();

}
run_gn_import_export_woocommerce_shipping_settings();
