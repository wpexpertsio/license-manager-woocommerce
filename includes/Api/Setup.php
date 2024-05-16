<?php

namespace LicenseManagerForWooCommerce\Api;

defined('ABSPATH') || exit;

class Setup
{
    /**
     * Setup class constructor.
     */
    public function __construct()
    {
        // REST API was included starting WordPress 4.4.
        if (!class_exists('\WP_REST_Server')) {
            return;
        }

        // Init REST API routes.
        add_action('rest_api_init', array($this, 'registerRoutes'), 10);
    }

    /**
     * Initializes the plugin API controllers.
     */
    public function registerRoutes()
    {
        $controllers = array(
            // REST API v2 controllers.
            '\LicenseManagerForWooCommerce\Api\V2\Licenses',
            '\LicenseManagerForWooCommerce\Api\V2\Generators'
        );

        foreach ($controllers as $controller) {
            $controller = new $controller();
            $controller->register_routes();
        }
    }
}