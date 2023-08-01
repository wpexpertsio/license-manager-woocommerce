<?php
/**
 * Plugin Name: License Manager for WooCommerce
 * Plugin URI: https://www.wpexperts.io/
 * Description: Easily sell and manage software license keys through your WooCommerce shop.
 * Version: 2.2.10
 * Author: LicenseManager
 * Author URI: https://www.licensemanager.at/
 * Requires at least: 4.7
 * Tested up to: 6.2.2
 * Requires PHP: 7.0
 * WC requires at least: 2.7
 * WC tested up to: 7.9.0
 */

namespace LicenseManagerForWooCommerce;

defined('ABSPATH') || exit;

require_once __DIR__ . '/freemius_integration.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions/lmfwc-core-functions.php';
require_once __DIR__ . '/functions/lmfwc-license-functions.php';
require_once __DIR__ . '/functions/lmfwc-meta-functions.php';

// Define LMFWC_PLUGIN_FILE.
if (!defined('LMFWC_PLUGIN_FILE')) {
    define('LMFWC_PLUGIN_FILE', __FILE__);
    define('LMFWC_PLUGIN_DIR', __DIR__);
}

// Define LMFWC_PLUGIN_URL.
if (!defined('LMFWC_PLUGIN_URL')) {
    define('LMFWC_PLUGIN_URL', plugins_url('', __FILE__) . '/');
}

// Define LMFWC_VERSION.
if (!defined('LMFWC_VERSION')) {
    define('LMFWC_VERSION', '2.2.10');
}

/**
 * Main instance of LicenseManagerForWooCommerce.
 *
 * Returns the main instance of SN to prevent the need to use globals.
 *
 * @return Main
 */
function lmfwc()
{
    return Main::instance();
}

// Global for backwards compatibility.
$GLOBALS['license-manager-for-woocommerce'] = lmfwc();