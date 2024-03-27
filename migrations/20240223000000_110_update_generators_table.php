<?php

defined('ABSPATH') || exit;

/**
 * @var string $migrationMode
 */

use LicenseManagerForWooCommerce\Setup;
use LicenseManagerForWooCommerce\Migration;

$tableGenerators = $wpdb->prefix . Setup::GENERATORS_TABLE_NAME;

if ($wpdb->get_var("SHOW TABLES LIKE '{$tableGenerators}'") != $tableGenerators) {
    return;
}

/**
 * Upgrade
 */
if ($migrationMode === Migration::MODE_UP) {
    $wpdb->query("ALTER TABLE {$tableGenerators} ADD COLUMN `license_tags` JSON NOT NULL AFTER `expires_in`;");
    $wpdb->query("UPDATE {$tableGenerators} SET license_tags='[]';");
}

/**
 * Downgrade
 */
if ($migrationMode === Migration::MODE_DOWN) {
    $wpdb->query("ALTER TABLE {$tableGenerators} DROP COLUMN `license_tags`;");
}