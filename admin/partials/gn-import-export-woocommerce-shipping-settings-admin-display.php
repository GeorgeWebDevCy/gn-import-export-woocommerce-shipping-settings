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

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="gn-ie-wcss-form" id="gn-ie-wcss-import-form">
		<input type="hidden" name="action" value="gn_import_export_woocommerce_shipping_settings_import" />
		<?php wp_nonce_field( 'gn_import_export_woocommerce_shipping_settings_import', 'gn_import_export_woocommerce_shipping_settings_nonce' ); ?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="gn_ie_wcss_dump_file"><?php esc_html_e( 'SQL dump file', 'gn-import-export-woocommerce-shipping-settings' ); ?></label>
				</th>
				<td>
					<input type="file" id="gn_ie_wcss_dump_file" name="gn_ie_wcss_dump_file" accept=".sql,.zip,.gz" required />
					<div class="gn-ie-wcss-preview-actions">
						<button type="button" class="button button-secondary" id="gn_ie_wcss_preview_button"><?php esc_html_e( 'Analyze Dump Preview', 'gn-import-export-woocommerce-shipping-settings' ); ?></button>
						<span class="description gn-ie-wcss-preview-status" id="gn_ie_wcss_preview_status"><?php esc_html_e( 'Select a dump file and click "Analyze Dump Preview".', 'gn-import-export-woocommerce-shipping-settings' ); ?></span>
					</div>
					<p class="description"><?php esc_html_e( 'Supported formats: .sql, .zip (containing SQL), and .gz SQL dumps.', 'gn-import-export-woocommerce-shipping-settings' ); ?></p>
					<p class="description"><?php esc_html_e( 'Before import, the plugin creates a backup of all WordPress tables that use the current DB prefix.', 'gn-import-export-woocommerce-shipping-settings' ); ?></p>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Backup Database and Import Shipping Data', 'gn-import-export-woocommerce-shipping-settings' ) ); ?>
	</form>

	<h2><?php esc_html_e( 'Source and Destination Preview', 'gn-import-export-woocommerce-shipping-settings' ); ?></h2>
	<p class="description"><?php esc_html_e( 'The source panel is populated from the uploaded dump. The destination panel shows current site data using the active DB prefix.', 'gn-import-export-woocommerce-shipping-settings' ); ?></p>

	<div class="gn-ie-wcss-preview-legend">
		<span class="gn-ie-wcss-legend-item gn-ie-wcss-legend-source"><?php esc_html_e( 'Source (Dump)', 'gn-import-export-woocommerce-shipping-settings' ); ?></span>
		<span class="gn-ie-wcss-legend-item gn-ie-wcss-legend-destination"><?php esc_html_e( 'Destination (Current Site)', 'gn-import-export-woocommerce-shipping-settings' ); ?></span>
	</div>

	<div class="gn-ie-wcss-preview-grid">
		<section class="gn-ie-wcss-preview-card gn-ie-wcss-preview-card-source">
			<h3><?php esc_html_e( 'Source (Dump File)', 'gn-import-export-woocommerce-shipping-settings' ); ?></h3>
			<div id="gn_ie_wcss_source_preview"></div>
		</section>
		<section class="gn-ie-wcss-preview-card gn-ie-wcss-preview-card-destination">
			<h3><?php esc_html_e( 'Destination (Current Site)', 'gn-import-export-woocommerce-shipping-settings' ); ?></h3>
			<div id="gn_ie_wcss_destination_preview"></div>
		</section>
	</div>

	<div class="gn-ie-wcss-comparison-wrap">
		<h3><?php esc_html_e( 'Count Comparison', 'gn-import-export-woocommerce-shipping-settings' ); ?></h3>
		<div id="gn_ie_wcss_comparison_preview"></div>
	</div>
</div>
