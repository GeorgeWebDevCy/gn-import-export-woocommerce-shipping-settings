# GN Import Export WooCommerce Shipping Settings

Import and migrate WooCommerce shipping zones, methods, locations, and shipping instance settings from SQL dump files across WordPress installations with different DB prefixes.

## What This Plugin Does

- Imports WooCommerce shipping data from `.sql`, `.zip` (containing SQL), or `.gz` dump files.
- Detects source dump table names and source DB prefix automatically.
- Maps imported records into the current site's DB prefix.
- Creates a full backup of current prefix tables before import.
- Replaces existing shipping structures and shipping instance option settings with dump values.

## Admin Preview (Source vs Destination)

Before importing, use **Analyze Dump Preview** on the admin screen to inspect:

- Source (Dump) detected DB prefix.
- Source detected table names from inside the dump.
- Source sample content (rows) per shipping table.
- Destination (Current Site) DB prefix and current table content preview.
- Side-by-side count comparison with statuses:
  - `Match`
  - `Different`
  - `Missing table`

### Visual Legend

- **Source (Dump):** blue-accented panels and rows.
- **Destination (Current Site):** green-accented panels and rows.
- **Differences/Missing:** highlighted with warning/error colors in comparison badges.

## Import Workflow

1. Open **Shipping Import** from the WordPress admin menu.
2. Upload a dump file (`.sql`, `.zip`, `.gz`).
3. Click **Analyze Dump Preview** and review source vs destination.
4. Click **Backup Database and Import Shipping Data**.
5. Review admin notice details for imported counts, detected source prefix, target prefix, and backup file path.

## Release Notes

### 1.1.0

- Added a standalone top-level **Shipping Import** admin menu.
- Added a Plugins screen quick link that opens the plugin admin page directly.
- Added source vs destination preview with detected tables, detected prefixes, and sample row content.
- Added color-highlighted comparison statuses for easier pre-import review.

## WooCommerce Shipping Storage Context

WooCommerce shipping is split across multiple related tables, not one table.

| Entity | Typical Table (with `wp_` prefix) | Key Columns |
|---|---|---|
| Shipping Zones | `wp_woocommerce_shipping_zones` | `zone_id`, `zone_name`, `zone_order` |
| Zone Locations | `wp_woocommerce_shipping_zone_locations` | `location_id`, `zone_id`, `location_code`, `location_type` |
| Zone Methods | `wp_woocommerce_shipping_zone_methods` | `zone_id`, `instance_id`, `method_id`, `method_order`, `is_enabled` |
| Method Settings | `wp_options` | `option_name`, `option_value`, `autoload` |

### Shipping Method Settings Pattern

Shipping method instance settings are stored in `wp_options` with this option key pattern:

`woocommerce_{method_id}_{instance_id}_settings`

Example:

`woocommerce_flat_rate_3_settings`

`option_value` is serialized data, for example:

```php
a:3:{
   s:4:"cost";s:5:"10.00";
   s:14:"tax_status";s:4:"none";
   s:8:"class_cost";a:0:{}
}
```

Example query:

```sql
SELECT
    o.option_name,
    o.option_value
FROM wp_options o
WHERE o.option_name LIKE 'woocommerce_flat_rate_%_settings';
```

## Prefix Behavior

- **Source prefix:** detected from dump table names like `{prefix}woocommerce_shipping_zones`.
- **Destination prefix:** always the current site's active `$wpdb->prefix`.
- Import writes to destination tables using the destination prefix, regardless of source prefix.

## Backup and Safety

- A backup of all current-prefix tables is created before import.
- Backup path is shown in the success notice.
- If import fails mid-process, transaction rollback logic is used where available.

## Notes

- This tool is intended for administrators who understand WooCommerce shipping structure.
- Always validate preview data before running a production import.
