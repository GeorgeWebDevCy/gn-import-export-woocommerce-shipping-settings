<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.georgenicolaou.me/
 * @since      1.0.0
 *
 * @package    Gn_Import_Export_Woocommerce_Shipping_Settings
 * @subpackage Gn_Import_Export_Woocommerce_Shipping_Settings/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Gn_Import_Export_Woocommerce_Shipping_Settings
 * @subpackage Gn_Import_Export_Woocommerce_Shipping_Settings/includes
 * @author     George Nicolaou <orionas.elite@gmail.com>
 */
class Gn_Import_Export_Woocommerce_Shipping_Settings_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'gn-import-export-woocommerce-shipping-settings',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
