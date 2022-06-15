<?php

defined('ABSPATH') || exit;

/**
 * @var string $migrationMode
 */

use LicenseManagerForWooCommerce\Setup;
use LicenseManagerForWooCommerce\Migration;

$tableLicenseMeta = $wpdb->prefix . Setup::LICENSE_META_TABLE_NAME;

/**
 * Upgrade
 */
if ($migrationMode === Migration::MODE_UP) {

    if (!function_exists('dbDelta')) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    }

    dbDelta("
        CREATE TABLE IF NOT EXISTS $tableLicenseMeta (
            `meta_id` BIGINT(20) UNSIGNED AUTO_INCREMENT,
            `license_id` BIGINT(20) UNSIGNED DEFAULT 0 NOT NULL,
            `meta_key` VARCHAR(255) NULL,
            `meta_value` LONGTEXT NULL,
            `created_at` DATETIME NULL,
            `created_by` BIGINT(20) NULL DEFAULT NULL,
            `updated_at` DATETIME NULL DEFAULT NULL,
            `updated_by` BIGINT(20) NULL DEFAULT NULL,
            PRIMARY KEY (`meta_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");

    $settingsGeneral = get_option('lmfwc_settings');

    if ($settingsGeneral) {
        add_option('lmfwc_settings_general', $settingsGeneral);
        delete_option('lmfwc_settings');
    }
}

/**
 * Downgrade
 */
if ($migrationMode === Migration::MODE_DOWN) {
    $wpdb->query("DROP TABLE IF EXISTS {$tableLicenseMeta}");

    $settingsGeneral = get_option('lmfwc_settings_general');

    if ($settingsGeneral) {
        add_option('lmfwc_settings', $settingsGeneral);
        delete_option('lmfwc_settings_general');
    }
}