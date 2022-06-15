<?php

defined('ABSPATH') || exit;

/**
 * @var string $migrationMode
 */

use LicenseManagerForWooCommerce\Setup;
use LicenseManagerForWooCommerce\Migration;

$tableLicenses = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;
$tableGenerators = $wpdb->prefix . Setup::GENERATORS_TABLE_NAME;

if ($wpdb->get_var("SHOW TABLES LIKE '{$tableLicenses}'") != $tableLicenses) {
    return;
}

if ($wpdb->get_var("SHOW TABLES LIKE '{$tableGenerators}'") != $tableGenerators) {
    return;
}

/**
 * Upgrade
 */
if ($migrationMode === Migration::MODE_UP) {
    $sql = "
        ALTER TABLE {$tableLicenses}
            CHANGE COLUMN `license_key` `license_key` LONGTEXT NOT NULL COMMENT 'Encrypted License Key' AFTER `product_id`,
            CHANGE COLUMN `hash` `hash` LONGTEXT NOT NULL COMMENT 'Hashed License Key ID' AFTER `license_key`,
            ADD COLUMN `times_activated` INT(10) NULL DEFAULT NULL COMMENT 'Number of activations' AFTER `status`,
            ADD COLUMN `times_activated_max` INT(10) NULL DEFAULT NULL COMMENT 'Maximum number of activations' AFTER `times_activated`,
            CHANGE COLUMN `created_at` `created_at` DATETIME NOT NULL COMMENT 'Creation timestamp' AFTER `times_activated_max`,
            ADD COLUMN `created_by` BIGINT(20) NULL DEFAULT NULL COMMENT 'Creation User' AFTER `created_at`,
            ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Update timestamp' AFTER `created_by`,
            ADD COLUMN `updated_by` BIGINT(20) NULL DEFAULT NULL COMMENT 'Update user' AFTER `updated_at`;
    ";

    $wpdb->query($sql);

    $sql ="
        ALTER TABLE {$tableGenerators}
            ADD COLUMN `times_activated_max` INT(10) NULL DEFAULT NULL COMMENT 'Maximum number of activations' AFTER `chunk_length`;
    ";

    $wpdb->query($sql);
}

/**
 * Downgrade
 */
if ($migrationMode === Migration::MODE_DOWN) {
    $sql = "
        ALTER TABLE {$tableLicenses}
            CHANGE COLUMN `license_key` `license_key` VARCHAR(4000) NOT NULL COMMENT 'Encrypted License Key' AFTER `product_id`,
            CHANGE COLUMN `created_at` `created_at` DATETIME NOT NULL COMMENT 'Creation timestamp' AFTER `hash`,
            DROP COLUMN `times_activated`,
            DROP COLUMN `times_activated_max`,
            DROP COLUMN `created_by`,
            DROP COLUMN `updated_at`,
            DROP COLUMN `updated_by`;
    ";

    $wpdb->query($sql);

    $sql = "
        ALTER TABLE {$tableGenerators}
            DROP COLUMN `times_activated_max`;
    ";

    $wpdb->query($sql);
}