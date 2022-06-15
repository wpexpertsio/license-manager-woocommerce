<?php

defined('ABSPATH') || exit;

/**
 * @var string $migrationMode
 */

use LicenseManagerForWooCommerce\Setup;
use LicenseManagerForWooCommerce\Migration;

$tableApiKeys      = $wpdb->prefix . Setup::API_KEYS_TABLE_NAME;
$tableGenerators   = $wpdb->prefix . Setup::GENERATORS_TABLE_NAME;
$tableLicenses     = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;
$tableLicensesMeta = $wpdb->prefix . Setup::LICENSE_META_TABLE_NAME;

if ($wpdb->get_var("SHOW TABLES LIKE '{$tableLicenses}'") != $tableLicenses) {
    return;
}

/**
 * Upgrade
 */
if ($migrationMode === Migration::MODE_UP) {
    $sql = "
        ALTER TABLE {$tableApiKeys}
            CHANGE `created_by` `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
            CHANGE `updated_by` `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL;
    ";

    $wpdb->query($sql);

    $sql = "
        ALTER TABLE {$tableGenerators}
            CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE `chunks` `chunks` INT(10) UNSIGNED NOT NULL,
            CHANGE `chunk_length` `chunk_length` INT(10) UNSIGNED NOT NULL,
            CHANGE `times_activated_max` `times_activated_max` INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE `expires_in` `expires_in` INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE `created_by` `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
            CHANGE `updated_by` `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL;
    ";

    $wpdb->query($sql);

    $sql = "
        ALTER TABLE {$tableLicenses}
            ADD COLUMN `user_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL AFTER `product_id`,
            CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE `order_id` `order_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
            CHANGE `product_id` `product_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
            CHANGE `valid_for` `valid_for` INT(32) UNSIGNED NULL DEFAULT NULL,
            CHANGE `source` `source` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
            CHANGE `status` `status` TINYINT(1) UNSIGNED NOT NULL,
            CHANGE `times_activated` `times_activated` INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE `times_activated_max` `times_activated_max` INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE `created_by` `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
            CHANGE `updated_by` `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL;
    ";

    $wpdb->query($sql);

    $sql = "
        ALTER TABLE {$tableLicensesMeta}
            CHANGE `created_by` `created_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
            CHANGE `updated_by` `updated_by` BIGINT(20) UNSIGNED NULL DEFAULT NULL;
    ";

    $wpdb->query($sql);
}

/**
 * Downgrade
 */
if ($migrationMode === Migration::MODE_DOWN) {
    $sql = "
        ALTER TABLE {$tableApiKeys}
            CHANGE `created_by` `created_by` BIGINT(20) NULL DEFAULT NULL,
            CHANGE `updated_by` `updated_by` BIGINT(20) NULL DEFAULT NULL;
    ";

    $wpdb->query($sql);

    $sql = "
        ALTER TABLE {$tableGenerators}
            CHANGE `id` `id` INT(20) NOT NULL AUTO_INCREMENT,
            CHANGE `chunks` `chunks` INT(10) NOT NULL,
            CHANGE `chunk_length` `chunk_length` INT(10) NOT NULL,
            CHANGE `times_activated_max` `times_activated_max` INT(10) NULL DEFAULT NULL,
            CHANGE `expires_in` `expires_in` INT(10) NULL DEFAULT NULL,
            CHANGE `created_by` `created_by` BIGINT(20) NULL DEFAULT NULL,
            CHANGE `updated_by` `updated_by` BIGINT(20) NULL DEFAULT NULL;
    ";

    $wpdb->query($sql);

    $sql = "
        ALTER TABLE {$tableLicenses}
            DROP COLUMN `user_id`,
            CHANGE `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
            CHANGE `order_id` `order_id` BIGINT(20) NULL DEFAULT NULL,
            CHANGE `product_id` `product_id` BIGINT(20) NULL DEFAULT NULL,
            CHANGE `valid_for` `valid_for` INT(32) NULL DEFAULT NULL,
            CHANGE `status` `status` TINYINT(1) NOT NULL,
            CHANGE `times_activated` `times_activated` INT(10) NULL DEFAULT NULL,
            CHANGE `times_activated_max` `times_activated_max` INT(10) NULL DEFAULT NULL,
            CHANGE `created_by` `created_by` BIGINT(20) NULL DEFAULT NULL,
            CHANGE `updated_by` `updated_by` BIGINT(20) NULL DEFAULT NULL;
    ";

    $wpdb->query($sql);

    $sql = "
        ALTER TABLE {$tableLicensesMeta}
            CHANGE `created_by` `created_by` BIGINT(20) NULL DEFAULT NULL,
            CHANGE `updated_by` `updated_by` BIGINT(20) NULL DEFAULT NULL;
    ";

    $wpdb->query($sql);
}