=== GN Import Export WooCommerce Shipping Settings ===
Contributors: orionaselite
Donate link: https://www.georgenicolaou.me/
Tags: woocommerce, shipping, import, export, migration, sql
Requires at least: 5.8
Requires PHP: 7.4
Tested up to: 6.9.1
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Import WooCommerce shipping zones, methods, locations, and shipping settings from SQL dump files, with source vs destination preview.

== Description ==

GN Import Export WooCommerce Shipping Settings helps you migrate WooCommerce shipping configuration between WordPress sites.

This plugin imports shipping data from `.sql`, `.zip` (with SQL inside), and `.gz` dump files and maps it to the current site's active DB prefix.

= Key features =

* Detects source DB prefix from the uploaded dump.
* Detects source shipping tables found in the dump file.
* Provides source and destination side-by-side preview before import.
* Shows sample content rows for each shipping table.
* Highlights source, destination, and change status with clear colors.
* Creates a database backup (for current DB prefix tables) before import.
* Imports WooCommerce shipping data into destination tables.

= What gets imported =

* Shipping zones table: `{prefix}woocommerce_shipping_zones`
* Shipping zone locations table: `{prefix}woocommerce_shipping_zone_locations`
* Shipping zone methods table: `{prefix}woocommerce_shipping_zone_methods`
* Shipping settings in `{prefix}options` where `option_name` matches:
  `woocommerce_{method_id}_{instance_id}_settings`

= Admin preview =

Before importing, click **Analyze Dump Preview** on the plugin admin page.

You can review:

* Source prefix and detected source table names.
* Source row counts and sample rows.
* Destination (current site) prefix, row counts, and sample rows.
* Comparison status for each table:
  * Match
  * Different
  * Missing table

= Prefix behavior =

* Source prefix is detected from the uploaded dump.
* Destination prefix is always the current site's `$wpdb->prefix`.
* Import writes data into destination tables using destination prefix.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install it via Plugins > Add New.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Make sure WooCommerce is installed and active.
4. Go to **Shipping Import** in the WordPress admin menu.
5. Upload your dump file and run **Analyze Dump Preview**.
6. Click **Backup Database and Import Shipping Data** to complete import.

== Frequently Asked Questions ==

= Does this plugin work without WooCommerce? =

No. WooCommerce must be active. The plugin self-deactivates if WooCommerce is missing or deactivated.

= Which file formats are supported? =

`.sql`, `.zip` (containing SQL), and `.gz` SQL dump files.

= Will this overwrite existing shipping settings? =

Yes. Existing shipping zones, methods, locations, and matching shipping settings are reset/replaced during import.

= Is a backup created before import? =

Yes. The plugin creates a backup of current-prefix tables before applying import changes.

== Screenshots ==

1. Shipping Import admin page with dump upload and preview button.
2. Source (dump) and destination (current site) side-by-side preview.
3. Count comparison table with match/different/missing status badges.

== Changelog ==

= 1.1.1 =
* Maintenance release.
* Bumped plugin version metadata and readme release tags.

= 1.1.0 =
* Added standalone top-level admin menu for Shipping Import.
* Added Plugins screen action link to open Shipping Import directly.
* Added source vs destination preview (detected tables, prefixes, sample content).
* Added color-highlighted comparison statuses (match, different, missing table).
* Improved readme documentation and admin workflow clarity.

= 1.0.0 =
* Initial public release.
* Added SQL/GZ/ZIP shipping import support.
* Added pre-import backup of current prefix tables.
* Added source vs destination preview with detected tables and sample rows.
* Added color-highlighted comparison statuses for easier review.

== Upgrade Notice ==

= 1.1.1 =
Maintenance release with version and documentation metadata updates.

= 1.1.0 =
Adds a standalone admin menu, a Plugins page quick link, and a richer pre-import source/destination preview.

= 1.0.0 =
Initial release of GN Import Export WooCommerce Shipping Settings.
