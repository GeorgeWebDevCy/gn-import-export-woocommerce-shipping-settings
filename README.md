# gn-import-export-woocommerce-shipping-settings

## WooCommerce Shipping Storage Context

In WooCommerce, shipping rates are not stored in a single table. They are stored across related tables, depending on how each shipping method is configured.

### 1) Shipping Zones

Table: `wp_woocommerce_shipping_zones`

Important columns:
- `zone_id`
- `zone_name`
- `zone_order`

### 2) Shipping Zone Locations

Table: `wp_woocommerce_shipping_zone_locations`

Important columns:
- `zone_id`
- `location_code`
- `location_type` (`country`, `state`, `postcode`, `continent`)

### 3) Shipping Methods per Zone

Table: `wp_woocommerce_shipping_zone_methods`

Important columns:
- `instance_id` (critical for linking settings)
- `zone_id`
- `method_id` (for example: `flat_rate`, `free_shipping`, `local_pickup`)
- `method_order`
- `is_enabled`

### 4) Shipping Method Settings (Actual Rates)

Table: `wp_options`

Actual shipping prices are stored in options keyed by shipping method instance.

Option name pattern:
`woocommerce_{method_id}_{instance_id}_settings`

Example:
`woocommerce_flat_rate_3_settings`

`option_value` is serialized data that includes fields like:

```php
a:3:{
   s:4:"cost";s:5:"10.00";
   s:14:"tax_status";s:4:"none";
   s:8:"class_cost";a:0:{}
}
```

Example query to get Flat Rate settings:

```sql
SELECT
    o.option_name,
    o.option_value
FROM wp_options o
WHERE o.option_name LIKE 'woocommerce_flat_rate_%_settings';
```
