<?php defined('ABSPATH') || exit;
/**
 * @var string $migrationMode
 */
use LicenseManagerForWooCommerce\Migration;
/**
 * Upgrade
 */
if ($migrationMode === Migration::MODE_UP) {
    $lmfwc_settings_general = get_option('lmfwc_settings_general', array());
    $general_array = array(
        'lmfwc_enable_my_account_endpoint' => !empty($lmfwc_settings_general['lmfwc_enable_my_account_endpoint']) ? $lmfwc_settings_general['lmfwc_enable_my_account_endpoint'] : 1,
        'lmfwc_allow_users_to_activate' => !empty($lmfwc_settings_general['lmfwc_allow_users_to_activate']) ? $lmfwc_settings_general['lmfwc_allow_users_to_activate'] : 1,
        'lmfwc_allow_users_to_deactivate' => !empty($lmfwc_settings_general['lmfwc_allow_users_to_deactivate']) ? $lmfwc_settings_general['lmfwc_allow_users_to_deactivate'] : 1,
        'lmfwc_auto_delivery' => !empty($lmfwc_settings_general['lmfwc_auto_delivery']) ? $lmfwc_settings_general['lmfwc_auto_delivery'] : 1,
        'lmfwc_enable_stock_manager' => !empty($lmfwc_settings_general['lmfwc_enable_stock_manager']) ? $lmfwc_settings_general['lmfwc_enable_stock_manager'] : 1
    );
    $lmfwc_settings_orderStatus = get_option('lmfwc_settings_order_status', array());
    $lmfwc_settings_woocommerce = get_option('lmfwc_settings_woocommerce', array());
    $lmfwc_settings_woocommerce = array_merge($general_array, $lmfwc_settings_orderStatus, $lmfwc_settings_woocommerce);
    update_option('lmfwc_settings_woocommerce', $lmfwc_settings_woocommerce);
}

/**
 * Downgrade
 */
if ($migrationMode === Migration::MODE_DOWN) {
    delete_option('lmfwc_settings_woocommerce');
}