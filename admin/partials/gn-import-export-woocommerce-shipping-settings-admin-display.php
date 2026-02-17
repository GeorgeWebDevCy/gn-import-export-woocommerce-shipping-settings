<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.georgenicolaou.me/
 * @since      1.0.0
 *
 * @package    Gn_Import_Export_Woocommerce_Shipping_Settings
 * @subpackage Gn_Import_Export_Woocommerce_Shipping_Settings/admin/partials
 */
?>

<div class="wrap gn-ie-wcss-admin">
	<h1><?php esc_html_e( 'WooCommerce Shipping Import', 'gn-import-export-woocommerce-shipping-settings' ); ?></h1>

	<p><?php esc_html_e( 'Upload a SQL dump file to import WooCommerce shipping zones, zone methods, and shipping method settings.', 'gn-import-export-woocommerce-shipping-settings' ); ?></p>
	<p>
		<strong><?php esc_html_e( 'Current database prefix:', 'gn-import-export-woocommerce-shipping-settings' ); ?></strong>
		<code><?php echo esc_html( $current_db_prefix ); ?></code>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="gn-ie-wcss-form">
		<input type="hidden" name="action" value="gn_import_export_woocommerce_shipping_settings_import" />
		<?php wp_nonce_field( 'gn_import_export_woocommerce_shipping_settings_import', 'gn_import_export_woocommerce_shipping_settings_nonce' ); ?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="gn_ie_wcss_dump_file"><?php esc_html_e( 'SQL dump file', 'gn-import-export-woocommerce-shipping-settings' ); ?></label>
				</th>
				<td>
					<input type="file" id="gn_ie_wcss_dump_file" name="gn_ie_wcss_dump_file" accept=".sql,.zip,.gz" required />
					<p class="description"><?php esc_html_e( 'Supported formats: .sql, .zip (containing SQL), and .gz SQL dumps.', 'gn-import-export-woocommerce-shipping-settings' ); ?></p>
					<p class="description"><?php esc_html_e( 'Before import, the plugin creates a backup of all WordPress tables that use the current DB prefix.', 'gn-import-export-woocommerce-shipping-settings' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Backup Database and Import Shipping Data', 'gn-import-export-woocommerce-shipping-settings' ) ); ?>
	</form>
</div>
