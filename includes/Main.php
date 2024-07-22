<?php
/**
 * Main plugin file.
 * PHP Version: 5.6
 *
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     https://www.licensemanager.at/
 */

namespace LicenseManagerForWooCommerce;

use LicenseManagerForWooCommerce\Abstracts\Singleton;
use LicenseManagerForWooCommerce\Integrations\WooCommerce\Controller;
use LicenseManagerForWooCommerce\Controllers\ApiKey as ApiKeyController;
use LicenseManagerForWooCommerce\Controllers\Generator as GeneratorController;
use LicenseManagerForWooCommerce\Controllers\License as LicenseController;
use LicenseManagerForWooCommerce\Controllers\Dropdowns as DropdownsController;


use LicenseManagerForWooCommerce\Enums\LicenseStatus;

defined('ABSPATH') || exit;

/**
 * LicenseManagerForWooCommerce
 *
 * @category WordPress
 * @package  LicenseManagerForWooCommerce
 * @author   Dražen Bebić <drazen.bebic@outlook.com>
 * @license  GNUv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version  Release: <2.2.0>
 * @link     https://www.licensemanager.at/
 */
final class Main extends Singleton
{
    /**
     * Main constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_defineConstants();
        $this->_initHooks();
        
        add_action('init', array($this, 'init'));

        new Api\Authentication();
    }

    /**
     * Define plugin constants.
     *
     * @return void
     */
    private function _defineConstants()
    {
        if (!defined('ABSPATH_LENGTH')) {
            define('ABSPATH_LENGTH', strlen(ABSPATH));
        }

        define('LMFWC_ABSPATH',         dirname(LMFWC_PLUGIN_FILE) . '/');
        define('LMFWC_PLUGIN_BASENAME', plugin_basename(LMFWC_PLUGIN_FILE));

        // Directories
        define('LMFWC_ASSETS_DIR',     LMFWC_ABSPATH       . 'assets/');
        define('LMFWC_LOG_DIR',        LMFWC_ABSPATH       . 'logs/');
        define('LMFWC_TEMPLATES_DIR',  LMFWC_ABSPATH       . 'templates/');
        define('LMFWC_MIGRATIONS_DIR', LMFWC_ABSPATH       . 'migrations/');
        define('LMFWC_CSS_DIR',        LMFWC_ASSETS_DIR    . 'css/');

        // URL's
        define('LMFWC_ASSETS_URL', LMFWC_PLUGIN_URL . 'assets/');
        define('LMFWC_ETC_URL',    LMFWC_ASSETS_URL . 'etc/');
        define('LMFWC_CSS_URL',    LMFWC_ASSETS_URL . 'css/');
        define('LMFWC_JS_URL',     LMFWC_ASSETS_URL . 'js/');
        define('LMFWC_IMG_URL',    LMFWC_ASSETS_URL . 'img/');
    }
    /**
     * Include JS and CSS files.
     *
     * @param string $hook
     *
     * @return void
     */
    public function adminEnqueueScripts($hook)
    {
        // Select2
        wp_register_style(
            'lmfwc_select2_cdn',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css'
        );
        wp_register_script(
            'lmfwc_select2_cdn',
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js'
        );
        wp_register_style(
            'lmfwc_select2',
            LMFWC_CSS_URL . 'select2.css'
        );

        // CSS
        wp_enqueue_style(
            'lmfwc_admin_css',
            LMFWC_CSS_URL . 'main.css',
            array(),
            LMFWC_VERSION
        );

        $current_screen = get_current_screen();
        if ( $hook === 'woocommerce_page_lmfwc_licenses' || $current_screen->id === 'shop_order' || $current_screen->id === 'woocommerce_page_wc-orders' ) {
            // JavaScript
            wp_enqueue_script(
                'lmfwc_admin_js',
                LMFWC_JS_URL . 'script.js',
                array(),
                LMFWC_VERSION
            );

            // Script localization
            wp_localize_script(
                'lmfwc_admin_js',
                'license',
                array(
                    'show'     => wp_create_nonce('lmfwc_show_license_key'),
                    'show_all' => wp_create_nonce('lmfwc_show_all_license_keys'),
                )
            );
        }

        // jQuery UI
        wp_register_style(
            'lmfwc-jquery-ui-datepicker',
            'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
            array(),
            '1.12.1'
        );
        if ( $hook === 'woocommerce_page_wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] === 'lmfwc_settings' ) {
            $extra_css = 'p.submit:not(.wrap.lmfwc p.submit){display:none;}';
            wp_add_inline_style('lmfwc_admin_css', $extra_css);
        }
        if ($hook === 'woocommerce_page_lmfwc_licenses' || $hook === 'woocommerce_page_lmfwc_generators' || $hook === 'woocommerce_page_lmfwc_activations' || ( $hook === 'woocommerce_page_wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] === 'lmfwc_settings' ) ) {
            wp_enqueue_script('lmfwc_select2_cdn');
            wp_enqueue_style('lmfwc_select2_cdn');
            wp_enqueue_style('lmfwc_select2');
            wp_enqueue_script('select2');
        }

        // Licenses page
        if ($hook === 'woocommerce_page_lmfwc_licenses') {
            wp_enqueue_script('lmfwc_licenses_page_js', LMFWC_JS_URL . 'licenses_page.js');

            wp_localize_script(
                'lmfwc_licenses_page_js',
                'i18n',
                array(
                    'placeholderSearchOrders'    => __('Search by order ID or customer email', 'license-manager-for-woocommerce'),
                    'placeholderSearchProducts'  => __('Search by product ID or product name', 'license-manager-for-woocommerce'),
                    'placeholderSearchUsers'     => __('Search by user login, name or email', 'license-manager-for-woocommerce')
                )
            );

            wp_localize_script(
                'lmfwc_licenses_page_js',
                'security',
                array(
                    'dropdownSearch' => wp_create_nonce('lmfwc_dropdown_search')
                )
            );
        }

        // Generators page
        if ($hook === 'woocommerce_page_lmfwc_generators') {
            wp_enqueue_script('lmfwc_generators_page_js', LMFWC_JS_URL . 'generators_page.js');

            wp_localize_script(
                'lmfwc_generators_page_js',
                'i18n',
                array(
                    'placeholderSearchOrders'   => __('Search by order ID or customer email', 'license-manager-for-woocommerce'),
                    'placeholderSearchProducts' => __('Search by product ID or product name', 'license-manager-for-woocommerce')
                )
            );

            wp_localize_script(
                'lmfwc_generators_page_js',
                'security',
                array(
                    'dropdownSearch' => wp_create_nonce('lmfwc_dropdown_search')
                )
            );
        }


        // Activations page
        if ($hook === 'woocommerce_page_lmfwc_activations') {
            wp_enqueue_script('lmfwc_activations_page_js', LMFWC_JS_URL . 'activations_page.js');

            wp_localize_script(
                'lmfwc_activations_page_js',
                'i18n',
                array(
                    'placeholderSearchLicenses' => __( 'Search by license ID', 'license-manager-for-woocommerce' ),
                    'placeholderSearchSources'  => __( 'Search by source', 'license-manager-for-woocommerce' ),
                )
            );

            wp_localize_script(
                'lmfwc_activations_page_js',
                'security',
                array(
                    'dropdownSearch' => wp_create_nonce('lmfwc_dropdown_search')
                )
            );
        }

        // Settings page
        if ( $hook === 'woocommerce_page_wc-settings' && isset( $_GET['tab'] ) && $_GET['tab'] === 'lmfwc_settings' ) {
            wp_enqueue_media();
            wp_enqueue_script('lmfwc_select2_cdn');
            wp_enqueue_style('lmfwc_select2_cdn');
            wp_enqueue_script('select2');
            wp_enqueue_script('lmfwc_settings_page_js', LMFWC_JS_URL . 'settings_page.js');
            wp_localize_script(
                'lmfwc_settings_page_js',
                'security',
                array(
                    'dropdownSearch' => wp_create_nonce('lmfwc_dropdown_search'),
                    'ajaxurl' => admin_url('admin-ajax.php')
                )
            );
        }
    }

    /**
     * Add additional links to the plugin row meta.
     *
     * @param array  $links Array of already present links
     * @param string $file  File name
     *
     * @return array
     */
    public function pluginRowMeta($links, $file)
    {
        if (strpos($file, 'license-manager-for-woocommerce.php') !== false ) {
            $newLinks = array(
                'github' => sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    'https://github.com/drazenbebic/license-manager',
                    'GitHub'
                ),
                'docs' => sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    'https://www.licensemanager.at/docs/',
                    __('Documentation', 'license-manager-for-woocommerce')
                ),
                'donate' => sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    'https://www.licensemanager.at/donate/',
                    __('Donate', 'license-manager-for-woocommerce')
                )
            );

            $links = array_merge($links, $newLinks);
        }

        return $links;
    }

    /**
     * Hook into actions and filters.
     *
     * @return void
     */
    private function _initHooks()
    {
        register_activation_hook(
            LMFWC_PLUGIN_FILE,
            array('\LicenseManagerForWooCommerce\Setup', 'install')
        );
        register_deactivation_hook(
            LMFWC_PLUGIN_FILE,
            array('\LicenseManagerForWooCommerce\Setup', 'deactivate')
        );
        register_uninstall_hook(
            LMFWC_PLUGIN_FILE,
            array('\LicenseManagerForWooCommerce\Setup', 'uninstall')
        );

        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
        add_filter('plugin_row_meta', array($this, 'pluginRowMeta'), 10, 2);
    }

    /**
     * Init LicenseManagerForWooCommerce when WordPress Initialises.
     *
     * @return void
     */
    public function init()
    {
        
        Setup::migrate();

        $this->publicHooks();

        new Crypto();
        new Import();
        new Export();
        new AdminMenus();
        new AdminNotice();
        new Generator();
        new Repositories\PostMeta();
        new LicenseController();
        new GeneratorController();
        new DropdownsController();
        new ApiKeyController();
        new Api\Setup();

        if ($this->isPluginActive('woocommerce/woocommerce.php')) {
            new Integrations\WooCommerce\Controller();
        }

        if (Settings::get('lmfwc_allow_duplicates')) {
            add_filter('lmfwc_duplicate', '__return_false', PHP_INT_MAX);
        }
    }

    /**
     * Defines all public hooks
     *
     * @return void
     */
    protected function publicHooks()
    {
        add_filter(
            'lmfwc_license_keys_table_heading',
            function($text) {
                $default = __('Your license key(s)', 'license-manager-for-woocommerce');

                if (!$text) {
                    return $default;
                }

                return sanitize_text_field($text);
            },
            10,
            1
        );

        add_filter(
            'lmfwc_license_keys_table_valid_until',
            function($text) {
                $default = __('Valid until', 'license-manager-for-woocommerce');

                if (!$text) {
                    return $default;
                }

                return sanitize_text_field($text);
            },
            10,
            1
        );
    }

    /**
     * Checks if a plugin is active.
     *
     * @param string $pluginName
     *
     * @return bool
     */
    private function isPluginActive($pluginName)
    {
        return in_array($pluginName, apply_filters('active_plugins', get_option('active_plugins')));
    }
}
