<?php
/**
 * LicenseManager for WooCommerce Core Functions
 *
 * General core functions available on both the front-end and admin.
 */

use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use LicenseManagerForWooCommerce\Settings;

defined('ABSPATH') || exit;

/**
 * Checks if a license key already exists inside the database table.
 *
 * @param string   $licenseKey
 * @param null|int $licenseKeyId
 *
 * @return bool
 */
function lmfwc_duplicate($licenseKey, $licenseKeyId = null)
{
    $duplicate = false;
    $hash      = apply_filters('lmfwc_hash', $licenseKey);

    // Add action
    if ($licenseKeyId === null) {
        $query = array('hash' => $hash);

        if (LicenseResourceRepository::instance()->findBy($query)) {
            $duplicate = true;
        }
    }

    // Update action
    elseif ($licenseKeyId !== null && is_numeric($licenseKeyId)) {
        $table = LicenseResourceRepository::instance()->getTable();

        $query = "
            SELECT
                id
            FROM
                {$table}
            WHERE
                1=1
                AND hash = '{$hash}'
                AND id NOT LIKE {$licenseKeyId}
            ;
        ";

        if (LicenseResourceRepository::instance()->query($query)) {
            $duplicate = true;
        }
    }

    return $duplicate;
}
add_filter('lmfwc_duplicate', 'lmfwc_duplicate', 10, 2);

/**
 * Generates a random hash.
 *
 * @return string
 */
function lmfwc_rand_hash()
{
    if ($hash = apply_filters('lmfwc_rand_hash', null)) {
        return $hash;
    }

    if (function_exists('wc_rand_hash')) {
        return wc_rand_hash();
    }

    if (!function_exists('openssl_random_pseudo_bytes')) {
        return sha1(wp_rand());
    }

    return bin2hex(openssl_random_pseudo_bytes(20));
}

/**
 * Converts dashes to camel case with first capital letter.
 *
 * @param string $input
 * @param string $separator
 *
 * @return string|string[]
 */
function lmfwc_camelize($input, $separator = '_')
{
    return str_replace($separator, '', ucwords($input, $separator));
}

/**
 * Returns a format string for expiration dates.
 *
 * @return string
 */
function lmfwc_expiration_format() {

    $expiration_format = Settings::get( 'lmfwc_expire_format', Settings::SECTION_GENERAL );
    if ( false === $expiration_format ) {
        $expiration_format = '{{DATE_FORMAT}}, {{TIME_FORMAT}} T';
    }

    if ( strpos( $expiration_format, '{{DATE_FORMAT}}' ) !== false ) {
        $date_format       = get_option( 'date_format', 'F j, Y' );
        $expiration_format = str_replace( '{{DATE_FORMAT}}', $date_format, $expiration_format );
    }

    if ( strpos( $expiration_format, '{{TIME_FORMAT}}' ) !== false ) {
        $time_format       = get_option( 'time_format', 'g:i a' );
        $expiration_format = str_replace( '{{TIME_FORMAT}}', $time_format, $expiration_format );
    }

    return $expiration_format;
}
