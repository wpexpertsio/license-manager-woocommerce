<?php

defined('ABSPATH') || exit;

/**
 * @var string $migrationMode
 */

use LicenseManagerForWooCommerce\Migration;

/**
 * Upgrade
 */
if ($migrationMode === Migration::MODE_UP) {
    $defaultSettingsTools = array(
        'lmfwc_csv_export_columns' => array(
            'id'                  => '1',
            'order_id'            => '1',
            'product_id'          => '1',
            'user_id'             => '1',
            'license_key'         => '1',
            'expires_at'          => '1',
            'valid_for'           => '1',
            'status'              => '1',
            'times_activated'     => '1',
            'times_activated_max' => '1',
            'created_at'          => '1',
            'created_by'          => '1',
            'updated_at'          => '1',
            'updated_by'          => '1',
        )
    );

    update_option('lmfwc_settings_tools', $defaultSettingsTools);
}

/**
 * Downgrade
 */
if ($migrationMode === Migration::MODE_DOWN) {
    delete_option('lmfwc_settings_tools');
}
