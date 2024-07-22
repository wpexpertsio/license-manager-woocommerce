<?php

namespace LicenseManagerForWooCommerce\Settings;

defined('ABSPATH') || exit;

class General
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
        $this->settings = get_option('lmfwc_settings_general', array());

        /**
         * @see https://developer.wordpress.org/reference/functions/register_setting/#parameters
         */
        $args = array(
            'sanitize_callback' => array($this, 'sanitize')
        );
    

        // Register the initial settings group.
        register_setting('lmfwc_settings_group_general', 'lmfwc_settings_general', $args) ;

        // Initialize the individual sections
        $this->initSectionLicenseKeys();
        $this->initSectionAPI();
        
    }

    /**
     * Sanitizes the settings input.
     *
     * @param array $settings
     *
     * @return array
     */
    public function sanitize($settings)
    {
    
        return $settings;
    }

    

    /**
     * Initializes the "lmfwc_license_keys" section.
     *
     * @return void
     */
    private function initSectionLicenseKeys()
    {
        // Add the settings sections.
        add_settings_section(
            'license_keys_section',
            __('License keys', 'license-manager-for-woocommerce'),
            null,
            'lmfwc_license_keys'
        );

        // lmfwc_security section fields.
        add_settings_field(
            'lmfwc_hide_license_keys',
            __('Obscure licenses', 'license-manager-for-woocommerce'),
            array($this, 'fieldHideLicenseKeys'),
            'lmfwc_license_keys',
            'license_keys_section'
        );

        add_settings_field(
            'lmfwc_allow_duplicates',
            __('Allow duplicates', 'license-manager-for-woocommerce'),
            array($this, 'fieldAllowDuplicates'),
            'lmfwc_license_keys',
            'license_keys_section'
        );
        add_settings_field(
            'lmfwc_expire_format',
            __('License expiration format', 'license-manager-for-woocommerce'),
            array($this, 'fieldExpireFormat'),
            'lmfwc_license_keys',
            'license_keys_section'
        );
    }

    public function fieldExpireFormat()
    {
        $field = 'lmfwc_expire_format';
        $value = isset($this->settings[$field]) ? $this->settings[$field] : '';
        $html = '<fieldset>';
        $html .= sprintf(
            '<input type="text" id="%s" name="lmfwc_settings_general[%s]" value="%s" >',
            esc_attr($field), // Escape field ID
            esc_attr($field), // Escape field name
            esc_attr($value)  // Escape field value
        );
        $html .= '<br><br>';
        $html .= sprintf(
            /* translators: %1$s: date format merge code, %2$s: time format merge code, %3$s: general settings URL, %4$s: link to date and time formatting documentation */
            __(
                '<code>%1$s</code> and <code>%2$s</code> will be replaced by formats from <a href="%3$s">Administration > Settings > General</a>. %4$s',
                'license-manager-for-woocommerce'
            ),
            '{{DATE_FORMAT}}',
            '{{TIME_FORMAT}}',
            esc_url(admin_url('options-general.php')), // Escape admin URL
            __(
                '<a href="https://wordpress.org/support/article/formatting-date-and-time/">Documentation on date and time formatting</a>.'
            )
        );
        $html .= '</fieldset>';
        echo wp_kses($html, lmfwc_shapeSpace_allowed_html());
    }
    

    
    

    /**
     * Initializes the "lmfwc_rest_api" section.
     *
     * @return void
     */
    private function initSectionAPI()
    {
        // Add the settings sections.
        add_settings_section(
            'lmfwc_rest_api_section',
            __('REST API', 'license-manager-for-woocommerce'),
            null,
            'lmfwc_rest_api'
        );

        add_settings_field(
            'lmfwc_disable_api_ssl',
            __('API & SSL', 'license-manager-for-woocommerce'),
            array($this, 'fieldEnableApiOnNonSsl'),
            'lmfwc_rest_api',
            'lmfwc_rest_api_section'
        );

        add_settings_field(
            'lmfwc_enabled_api_routes',
            __('Enable/disable API routes', 'license-manager-for-woocommerce'),
            array($this, 'fieldEnabledApiRoutes'),
            'lmfwc_rest_api',
            'lmfwc_rest_api_section'
        );
    }

    /**
     * Callback for the "hide_license_keys" field.
     *
     * @return void
     */
    public function fieldHideLicenseKeys()
    {
        $field = 'lmfwc_hide_license_keys';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf('<span>%s</span>', __('Hide license keys in the admin dashboard.', 'license-manager-for-woocommerce'));
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('All license keys will be hidden and only displayed when the \'Show\' action is clicked.', 'license-manager-for-woocommerce')
        );
        $html .= '</fieldset>';

        echo wp_kses($html, lmfwc_shapeSpace_allowed_html());
    }

    /**
     * Callback for the "lmfwc_allow_duplicates" field.
     *
     * @return void
     */
    public function fieldAllowDuplicates()
    {
        $field = 'lmfwc_allow_duplicates';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Allow duplicate license keys inside the licenses database table.', 'license-manager-for-woocommerce')
        );
        $html .= '</label>';

        $html .= '</fieldset>';

        echo wp_kses($html, lmfwc_shapeSpace_allowed_html());
    }

   
    /**
     * Callback for the "lmfwc_disable_api_ssl" field.
     *
     * @return void
     */
    public function fieldEnableApiOnNonSsl()
    {
        $field = 'lmfwc_disable_api_ssl';
        (array_key_exists($field, $this->settings)) ? $value = true : $value = false;

        $html = '<fieldset>';
        $html .= sprintf('<label for="%s">', $field);
        $html .= sprintf(
            '<input id="%s" type="checkbox" name="lmfwc_settings_general[%s]" value="1" %s/>',
            $field,
            $field,
            checked(true, $value, false)
        );
        $html .= sprintf(
            '<span>%s</span>',
            __('Enable the plugin API routes over insecure HTTP connections.', 'license-manager-for-woocommerce')
        );
        $html .= '</label>';
        $html .= sprintf(
            '<p class="description">%s</p>',
            __('This should only be activated for development purposes.', 'license-manager-for-woocommerce')
        );
        $html .= '</fieldset>';

        echo wp_kses($html, lmfwc_shapeSpace_allowed_html());
    }

    /**
     * Callback for the "lmfwc_enabled_api_routes" field.
     *
     * @return void
     */
    public function fieldEnabledApiRoutes()
    {
        $field = 'lmfwc_enabled_api_routes';
        $value = array();
        $routes = array(
            array(
                'id'         => '010',
                'name'       => 'v2/licenses',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '011',
                'name'       => 'v2/licenses/{license_key}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '012',
                'name'       => 'v2/licenses',
                'method'     => 'POST',
                'deprecated' => false,
            ),
            array(
                'id'         => '013',
                'name'       => 'v2/licenses/{license_key}',
                'method'     => 'PUT',
                'deprecated' => false,
            ),
             array(
                'id'         => '014',
                'name'       => 'v2/licenses/{license_key}',
                'method'     => 'DELETE',
                'deprecated' => false,
            ),
            array(
                'id'         => '015',
                'name'       => 'v2/licenses/activate/{license_key}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '016',
                'name'       => 'v2/licenses/deactivate/{activation_token}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '017',
                'name'       => 'v2/licenses/validate/{license_key}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '018',
                'name'       => 'v2/generators',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '019',
                'name'       => 'v2/generators/{id}',
                'method'     => 'GET',
                'deprecated' => false,
            ),
            array(
                'id'         => '020',
                'name'       => 'v2/generators',
                'method'     => 'POST',
                'deprecated' => false,
            ),
            array(
                'id'         => '021',
                'name'       => 'v2/generators/{id}',
                'method'     => 'PUT',
                'deprecated' => false,
            ),
             array(
                'id'         => '022',
                'name'       => 'v2/generators/{id}',
                'method'     => 'DELETE',
                'deprecated' => false,
            ),
        );
        $classList = array(
            'GET'    => 'text-success',
            'PUT'    => 'text-primary',
            'POST'   => 'text-primary',
            'DELETE' => 'text-danger '
        );

        if (array_key_exists($field, $this->settings)) {
            $value = $this->settings[$field];
        }

        $html = '<fieldset>';

        foreach ($routes as $route) {
            $checked = false;

            if (array_key_exists($route['id'], $value) && $value[$route['id']] === '1') {
                $checked = true;
            }

            $html .= sprintf('<label for="%s-%s">', $field, $route['id']);
            $html .= sprintf(
                '<input id="%s-%s" type="checkbox" name="lmfwc_settings_general[%s][%s]" value="1" %s>',
                $field,
                $route['id'],
                $field,
                $route['id'],
                checked(true, $checked, false)
            );
            $html .= sprintf('<code><b class="%s">%s</b> - %s</code>', $classList[$route['method']], $route['method'], $route['name']);

            if (true === $route['deprecated']) {
                $html .= sprintf(
                    '<code class="text-info"><b>%s</b></code>',
                    strtoupper(__('Deprecated', 'license-manager-for-woocommerce'))
                );
            }

            $html .= '</label>';
            $html .= '<br>';
        }

        $html .= sprintf(
            '<p class="description" style="margin-top: 1em;">%s</p>',
            sprintf(
                 /* translators: %1$s: date format merge code, %2$s: time format merge code, %3$s: general settings URL, %4$s: link to date and time formatting documentation */
                __('The complete <b>API documentation</b> can be found <a href="%s" target="_blank" rel="noopener">here</a>.', 'license-manager-for-woocommerce'),
                'https://www.licensemanager.at/docs/rest-api/getting-started/api-keys'
            )
        );
        
        $html .= '</fieldset>';

        echo wp_kses($html, lmfwc_shapeSpace_allowed_html());
    }
}