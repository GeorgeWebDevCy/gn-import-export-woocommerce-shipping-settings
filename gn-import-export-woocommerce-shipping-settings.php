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

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'GN_IMPORT_EXPORT_WOOCOMMERCE_SHIPPING_SETTINGS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gn-import-export-woocommerce-shipping-settings-activator.php
 */
function activate_gn_import_export_woocommerce_shipping_settings() {
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
