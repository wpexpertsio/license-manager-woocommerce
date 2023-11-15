<?php

defined('ABSPATH') || exit;

/**
 * @var string $migrationMode
 */

use LicenseManagerForWooCommerce\Setup;
use LicenseManagerForWooCommerce\Migration;

$tableActivations = $wpdb->prefix . Setup::ACTIVATIONS_TABLE_NAME;

/**
 * Upgrade
 */
if ($migrationMode === Migration::MODE_UP) {

    if (!function_exists('dbDelta')) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    }
    dbDelta( "
        CREATE TABLE IF NOT EXISTS $tableActivations (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `token` LONGTEXT NOT NULL COMMENT 'Public identifier',
            `license_id` BIGINT(20) UNSIGNED NOT NULL,
            `label` VARCHAR(255) NULL DEFAULT NULL,
            `source` VARCHAR(255) NOT NULL,
            `ip_address` VARCHAR(255) NULL DEFAULT NULL,
            `user_agent` TEXT NULL DEFAULT NULL,
            `meta_data` LONGTEXT NULL DEFAULT NULL,
            `created_at` DATETIME NULL DEFAULT NULL,
            `updated_at` DATETIME NULL DEFAULT NULL,
            `deactivated_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    " );

}

/**
 * Downgrade
 */
if ($migrationMode === Migration::MODE_DOWN) {
    $wpdb->query("DROP TABLE IF EXISTS {$tableActivations}");
}