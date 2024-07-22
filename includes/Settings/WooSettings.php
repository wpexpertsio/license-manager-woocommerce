<?php

namespace LicenseManagerForWooCommerce\Settings;

defined('ABSPATH') || exit;

class WooSettings
{
    /**
     * @var array
     */
    private $settings;

    /**
     * General constructor.
     */
    public function __construct()
    {
        $this->settings = get_option('lmfwc_settings_woocommerce', array());

        /**
         * @see https://developer.wordpress.org/reference/functions/register_setting/#parameters
         */
        $args = array(
            'sanitize_callback' => array($this, 'sanitize')
        );

        // Register the initial settings group.

        // Initialize the individual sections
        $this->initSectionsLicenseDelivery();
        $this->initSectionBranding();
        $this->initSectionMyAccount();
       

        register_setting('lmfwc_settings_group_woocommerce', 'lmfwc_settings_woocommerce', $args) ;
    }

    /**
     * @param array $settings
     *
     * @return array
     */
    public function sanitize($settings)
    {

            if (isset($_POST['lmfwc_stock_synchronize'])) {
            // Permission check
            if (!current_user_can('manage_options')) {
                return $settings;
            }

            /** @var int $productsSynchronized Number of synchronized products */
            $productsSynchronized = apply_filters('lmfwc_stock_synchronize', null);

            if ($productsSynchronized > 0) {
                add_settings_error(
                    'lmfwc_settings_group_woocommerce',
                    'lmfwc_stock_update',
                    sprintf(
                        /* translators: %d is the number of WooCommerce products synchronized */
                        __('Successfully updated the stock of %d WooCommerce products.', 'license-manager-for-woocommerce'),
                        $productsSynchronized
                    ),
                    'success'
                );
            } else {
                add_settings_error(
                    'lmfwc_settings_group_woocommerce',
                    'lmfwc_stock_update',
                    __('The stock of all WooCommerce products is already synchronized.', 'license-manager-for-woocommerce'),
                    'success'
                );
            }
        }
        

        if ($settings === null) {
            return array();
        }
        if ( isset( $settings['lmfwc_enable_my_account_endpoint'] ) ) {
            flush_rewrite_rules( true );
        }
        return $settings;
    }

    /**
     * Initializes the "lmfwc_my_account" section.
     *
     * @return void
     */

     /**
     * Initializes the "lmfwc_branding" section.
     *
     * @return void
     */
    private function initSectionBranding()
    {

        // Add the Branding sections.
        add_settings_section(
            'branding_section',
            __('Branding', 'license-manager-for-woocommerce'),
            null,
            'lmfwc_branding'
        );

        // lmfwc_logo_field.
        add_settings_field(
            'lmfwc_company_logo',
            __('Company Logo', 'license-manager-for-woocommerce'),
            array($this, 'fieldImageUpload'),
            'lmfwc_branding',
            'branding_section'
        );
    }

    
    private function initSectionMyAccount()
    {
      
        // Add the settings sections.
        add_settings_section(
            'my_account_section',
            __('My account', 'license-manager-for-woocommerce'),
            null,
            'lmfwc_my_account'
        );

        // lmfwc_my_account section fields.
        add_settings_field(
            'lmfwc_enable_my_account_endpoint',
            __('Enable "License keys"', 'license-manager-for-woocommerce'),
            array($this, 'fieldEnableMyAccountEndpoint'),
            'lmfwc_my_account',
            'my_account_section'
        );

        add_settings_field(
            'lmfwc_allow_users_to_activate',
            __('User activation', 'license-manager-for-woocommerce'),
            array($this, 'fieldAllowUsersToActivate'),
            'lmfwc_my_account',
            'my_account_section'
        );

        add_settings_field(
            'lmfwc_allow_users_to_deactivate',
            __('User deactivation', 'license-manager-for-woocommerce'),
            array($this, 'fieldAllowUsersToDeactivate'),
            'lmfwc_my_account',
            'my_account_section'
        );
         add_settings_field(
            'lmfwc_download_certificates',
            __('Enable Certificates', 'license-manager-for-woocommerce'),
            array($this, 'fieldEnableLicenseCertificates'),
            'lmfwc_my_account',
            'my_account_section'
        );
    }


    private function initSectionsLicenseDelivery()
    {
        add_settings_section(
            'license_key_delivery_section',
            __('License key delivery', 'license-manager-for-woocommerce'),
            null,
            'lmfwc_license_key_delivery'
        );

        add_settings_field(
            'lmfwc_auto_delivery',
            __('Automatic delivery', 'license-manager-for-woocommerce'),
            array($this, 'fieldAutoDelivery'),
            'lmfwc_license_key_delivery',
            'license_key_delivery_section'
        );

        add_settings_field(
            'lmfwc_license_key_delivery_options',
            __('Define license key delivery', 'license-manager-for-woocommerce'),
            array($this, 'fieldLicenseKeyDeliveryOptions'),
            'lmfwc_license_key_delivery',
            'license_key_delivery_section'
        );

        add_settings_field(
            'lmfwc_enable_stock_manager',
            __('Stock management', 'license-manager-for-woocommerce'),
            array($this, 'fieldEnableStockManager'),
            'lmfwc_license_key_delivery',
            'license_key_delivery_section'
        );
    }

    /**
     * Render the image upload field
     *
     * @return void
     */
    public function fieldImageUpload() {

        $field = 'lmfwc_company_logo';
        $placeholder = LMFWC_PLUGIN_URL . 'assets/img/logo.jpg';
        $value = isset($this->settings[$field]) ? $this->settings[$field] : ''; 
        $current_src = !empty($value) ? wp_get_attachment_image_src($value, 'large') : '';
        $current_src = !empty($current_src) && is_array($current_src) ? $current_src[0] : $placeholder;
        $html = '<fieldset>';
        $html .= sprintf( '<div class="lmfwc-field-upload" data-show-attachment-preview="1"><img class="lmfwc-field-placeholder" data-src="%s" src="%s" alt="File" /><div class="lmfwc-field-submit"><input id="%s" type="hidden" name="lmfwc_settings_woocommerce[%s]" value="%s" />', esc_url( $placeholder ), esc_url( $current_src), esc_attr( $field ), esc_attr( $field ), esc_attr( $value ) );
        $html .= '<button type="submit" class="lmfwc-field-upload-button button">'. __('Upload', 'license-manager-for-woocommerce') . '</button><button type="submit" class="lmfwc-field-remove-button button">&times;</button></div></div>';
        $html .= '</fieldset>';

        echo wp_kses( $html, lmfwc_shapeSpace_allowed_html() );
    }


    public function fieldEnableLicenseCertificates(){

       $field = 'lmfwc_download_certificates';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_woocommerce[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Allow users to download license certificates', 'license-manager-for-woocommerce')
        );
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('Use this option if you want to allow customers to download license certificate from the single license page.', 'license-manager-for-woocommerce')
        );
        $html .= '</fieldset>';

        echo wp_kses( $html, lmfwc_shapeSpace_allowed_html() );
    }

   
    public function fieldAutoDelivery()
    {
        $field = 'lmfwc_auto_delivery';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_woocommerce[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Automatically send license keys when an order is set to \'Complete\'.', 'license-manager-for-woocommerce')
        );
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('If this setting is off, you must manually send out all license keys for completed orders.', 'license-manager-for-woocommerce')
        );
        $html .= '</fieldset>';

        echo wp_kses( $html, lmfwc_shapeSpace_allowed_html() );
    }

    /**
     * Callback for the "lmfwc_enable_my_account_endpoint" field.
     *
     * @return void
     */
    public function fieldEnableMyAccountEndpoint()
    {
        $field = 'lmfwc_enable_my_account_endpoint';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_woocommerce[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Display the \'License keys\' section inside WooCommerce\'s \'My Account\'.', 'license-manager-for-woocommerce')
        );
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('You might need to save your permalinks after enabling this option.', 'license-manager-for-woocommerce')
        );
        $html .= '</fieldset>';

        echo wp_kses( $html, lmfwc_shapeSpace_allowed_html() );
    }

    /**
     * Callback for the "lmfwc_allow_users_to_activate" field.
     */
    public function fieldAllowUsersToActivate()
    {
        $field = 'lmfwc_allow_users_to_activate';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_woocommerce[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Allow users to activate their license keys.', 'license-manager-for-woocommerce')
        );
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('The option will be visible from the \'License keys\' section inside WooCommerce\'s \'My Account\'', 'license-manager-for-woocommerce')
        );
        $html .= '</fieldset>';

        echo wp_kses( $html, lmfwc_shapeSpace_allowed_html() );
    }

     /**
     * Callback for the "lmfwc_allow_users_to_deactivate" field.
     */
    public function fieldAllowUsersToDeactivate()
    {
        $field = 'lmfwc_allow_users_to_deactivate';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_woocommerce[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Allow users to deactivate their license keys.', 'license-manager-for-woocommerce')
        );
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('The option will be visible from the \'License keys\' section inside WooCommerce\'s \'My Account\'', 'license-manager-for-woocommerce')
        );
        $html .= '</fieldset>';

        echo wp_kses( $html, lmfwc_shapeSpace_allowed_html() );
    }


    public function fieldLicenseKeyDeliveryOptions($foo)
    {
        $field = 'lmfwc_license_key_delivery_options';
        $html  = '';

        $html .= '<table class="wp-list-table widefat fixed striped posts">';

        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<td><strong>' . __('Order status', 'license-manager-for-woocommerce') .'</strong></td>';
        $html .= '<td><strong>' . __('Send', 'license-manager-for-woocommerce') .'</strong></td>';
        $html .= '</tr>';
        $html .= '</thead>';

        $html .= '<tbody>';

        foreach (wc_get_order_statuses() as $slug => $name) {
            $send   = false;
            

            if (array_key_exists($field, $this->settings) && array_key_exists($slug, $this->settings[$field])) {
                if (array_key_exists('send', $this->settings[$field][$slug]) && $this->settings[$field][$slug]) {
                    $send = true;
                }
            }

            $html .= '<tr>';
            $html .= '<td>' . $name . '</td>';

            $html .= '<td>';
            $html .= sprintf(
                '<input type="checkbox" name="lmfwc_settings_woocommerce[%s][%s][send]" value="1" %s>',
                $field,
                $slug,
                $send ? 'checked="checked"' : ''
            );
            $html .= '</td>';


            $html .= '</tr>';
        }

        $html .= '</tbody>';

        $html .= '</table>';

        echo wp_kses( $html, lmfwc_shapeSpace_allowed_html() );
    }

     /**
     * Callback for the "lmfwc_enable_stock_manager" field.
     *
     * @return void
     */
    public function fieldEnableStockManager()
    {
        $field = 'lmfwc_enable_stock_manager';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset style="margin-bottom: 0;">';
        $html .= '<label for="' . $field . '">';
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_woocommerce[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );

        $html .= '<span>' . __('Enable automatic stock management for WooCommerce products.', 'license-manager-for-woocommerce') . '</span>';
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s<br/>1. %s<br/>2. %s<br/>3. %s</p>',
            __('To use this feature, you also need to enable the following settings at a product level:', 'license-manager-for-woocommerce'),
            __('Inventory &rarr; Manage stock?', 'license-manager-for-woocommerce'),
            __('License Manager &rarr; Sell license keys', 'license-manager-for-woocommerce'),
            __('License Manager &rarr; Sell from stock', 'license-manager-for-woocommerce')
        );
        $html .= '</fieldset>';

        $html .= '
            <fieldset style="margin-top: 1em;">
                <button class="button button-secondary"
                        type="submit"
                        name="lmfwc_stock_synchronize"
                        value="1">' . __('Synchronize', 'license-manager-for-woocommerce') . '</button>
                <p class="description" style="margin-top: 1em;">
                    ' . __('The "Synchronize" button can be used to manually synchronize the product stock.', 'license-manager-for-woocommerce') . '
                </p>
            </fieldset>
        ';

        echo wp_kses( $html, lmfwc_shapeSpace_allowed_html() );
    }

}