<?php

namespace LicenseManagerForWooCommerce\Repositories;

defined('ABSPATH') || exit;

class Users
{
    /**
     * Adds all filters for interaction with the database table.
     */
    public function __construct()
    {
        // SELECT
        add_filter('lmfwc_get_users', array($this, 'getUsers'), 10, 0);
    }

    /**
     * Retrieve assigned products for a specific generator.
     *
     * @return array
     */
    public function getUsers()
    {
        global $wpdb;

        return $wpdb->get_results( 
            "
                SELECT
                    ID
                    , user_login
                    , user_email
                FROM
                    {$wpdb->users}
            ",
            OBJECT
        );
    }

}