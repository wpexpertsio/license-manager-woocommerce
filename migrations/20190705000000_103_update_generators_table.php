<?php

defined('ABSPATH') || exit;

/**
 * @var string $migrationMode
 */

use LicenseManagerForWooCommerce\Setup;
use LicenseManagerForWooCommerce\Migration;

$tableGenerators = $wpdb->prefix . Setup::GENERATORS_TABLE_NAME;
$tableApiKeys    = $wpdb->prefix . Setup::API_KEYS_TABLE_NAME;

if ($wpdb->get_var("SHOW TABLES LIKE '{$tableGenerators}'") != $tableGenerators) {
    return;
}

if ($wpdb->get_var("SHOW TABLES LIKE '{$tableApiKeys}'") != $tableApiKeys) {
    return;
}

/**
 * Upgrade
 */
if ($migrationMode === Migration::MODE_UP) {
    $sql ="
        ALTER TABLE {$tableGenerators}
            ADD COLUMN `created_at` DATETIME NULL COMMENT 'Creation Date' AFTER `expires_in`,
            ADD COLUMN `created_by` BIGINT(20) NULL COMMENT 'WP User ID' AFTER `created_at`,
            ADD COLUMN `updated_at` DATETIME NULL COMMENT 'Update Date' AFTER `created_by`,
            ADD COLUMN `updated_by` BIGINT(20) NULL COMMENT 'WP User ID' AFTER `updated_at`;
    ";

    $wpdb->query($sql);

    $sql ="
        ALTER TABLE {$tableApiKeys}
            ADD COLUMN `created_at` DATETIME NULL COMMENT 'Creation Date' AFTER `last_access`,
            ADD COLUMN `created_by` BIGINT(20) NULL COMMENT 'WP User ID' AFTER `created_at`,
            ADD COLUMN `updated_at` DATETIME NULL COMMENT 'Update Date' AFTER `created_by`,
            ADD COLUMN `updated_by` BIGINT(20) NULL COMMENT 'WP User ID' AFTER `updated_at`;
    ";

    $wpdb->query($sql);
}

/**
 * Downgrade
 */
if ($migrationMode === Migration::MODE_DOWN) {
    $sql = "
        ALTER TABLE {$tableGenerators}
            DROP COLUMN `created_at`,
            DROP COLUMN `created_by`,
            DROP COLUMN `updated_at`,
            DROP COLUMN `updated_by`;
    ";

    $wpdb->query($sql);

    $sql = "
        ALTER TABLE {$tableApiKeys}
            DROP COLUMN `created_at`,
            DROP COLUMN `created_by`,
            DROP COLUMN `updated_at`,
            DROP COLUMN `updated_by`;
    ";

    $wpdb->query($sql);
}