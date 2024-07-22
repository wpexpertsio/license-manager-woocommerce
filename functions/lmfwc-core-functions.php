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

/**
 * Returns a format string for expiration dates.
 *
 * @return array
 */

 if ( ! function_exists( 'lmfwc_shapeSpace_allowed_html' ) ) :
    function lmfwc_shapeSpace_allowed_html() {
        $allowed_atts = array(
            'h1'          => array(),
            'h2'          => array(),
            'h3'          => array(),
            'h4'          => array(),
            'h5'          => array(),
            'accept'          => array(),
            'accept-charset'  => array(),
            'kbd'  => array(),
            'accesskey'       => array(),
            'action'          => array(),
            'align'           => array(),
            'div'           => array(),
            'p'           => array(),
            'ul'           => array(),
            'li'           => array(),
            'b'           => array(),
            'alt'             => array(),
            'aria-describedby' => array(),
            'aria-hidden'     => array(),
			'style'           => array(),
            'aria-label'      => array(),
            'aria-labelledby' => array(),
            'async'           => array(),
            'autocomplete'    => array(),
            'autofocus'       => array(),
            'autoplay'        => array(),
            'form'        => array(),
            'bgcolor'         => array(),
            'border'          => array(),
            'cellpadding'     => array(),
            'cellspacing'     => array(),
            'charset'         => array(),
            'checked'         => array(),
            'cite'            => array(),
            'class'           => array(),
            'cols'            => array(),
            'colspan'         => array(),
            'content'         => array(),
            'contenteditable' => array(),
            'controls'        => array(),
            'coords'          => array(),
			'data-order-id' => array(),
			'data-show-attachment-preview' => array(),
			'data-src' => array(),
			'data-target' => array(),
			'data-id'                 => array(),
			'data-id'                 => array(),
			'data-left'                 => array(),
            'data'            => array(
                'show-attachment-preview' => array(),
                'src'                    => array(),
                'target'                 => array(),
                'order-id'                 => array(),
                'data-id'                 => array(),
                'data-left'                 => array(),
            ),
            'datetime'        => array(),
            'default'         => array(),
            'dir'             => array(),
            'disabled'        => array(),
            'download'        => array(),
            'draggable'       => array(),
            'dropzone'        => array(),
            'enctype'         => array(),
            'for'             => array(),
            'form'            => array(),
            'headers'         => array(),
            'height'          => array(),
            'hidden'          => array(),
            'href'            => array(),
            'hreflang'        => array(),
            'http-equiv'      => array(),
            'id'              => array(),
            'ismap'           => array(),
            'kind'            => array(),
            'label'           => array(),
            'lang'            => array(),
            'list'            => array(),
            'loop'            => array(),
            'max'             => array(),
            'maxlength'       => array(),
            'media'           => array(),
            'method'          => array(),
            'min'             => array(),
            'multiple'        => array(),
            'name'            => array(),
            'novalidate'      => array(),
            'open'            => array(),
            'optimum'         => array(),
            'pattern'         => array(),
            'ping'            => array(),
            'placeholder'     => array(),
            'poster'          => array(),
            'preload'         => array(),
            'readonly'        => array(),
            'rel'             => array(),
            'required'        => array(),
            'reversed'        => array(),
            'rows'            => array(),
            'rowspan'         => array(),
            'sandbox'         => array(),
            'scope'           => array(),
            'selected'        => array(),
            'code'        => array(),
            'shape'           => array(),
            'size'            => array(),
            'sizes'           => array(),
            'span'            => array(),
            'spellcheck'      => array(),
            'src'             => array(),
            'srcset'          => array(),
            'start'           => array(),
            'step'            => array(),
            'style'           => array(),
            'tabindex'        => array(),
            'target'          => array(),
            'title'           => array(),
            'translate'       => array(),
            'type'            => array(),
            'usemap'          => array(),
            'value'           => array(),
            'width'           => array(),
            'wrap'            => array(),
        );

        // Adding global attributes to all tags
        $global_attributes = array(
            'accesskey',
            'class',
            'contenteditable',
            'dir',
            'draggable',
            'dropzone',
            'hidden',
            'id',
            'form',
            'lang',
            'spellcheck',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
			'style',
            'tabindex',
            'title',
            'translate',
        );

        // Apply global attributes to all allowed tags
        foreach ( $allowed_atts as $tag => $attributes ) {
            $allowed_atts[ $tag ] = array_merge( $attributes, $global_attributes );
        }

        // Additional specific tags used in the example
        $additional_tags = array(
            'form' => $allowed_atts,
            'fieldset' => $allowed_atts,
            'h2' => $allowed_atts,
            'label'    => $allowed_atts,
            'input'    => array_merge( $allowed_atts, array(
                'checked' => array(),
                'type'    => array(),
                'name'    => array(),
                'value'   => array(),
            ) ),
            'button'   => array_merge( $allowed_atts, array(
                'type' => array(),
                'name' => array(),
                'value' => array(),
            ) ),
            'p'        => array_merge( $allowed_atts, array(
                'class' => array(),
            ) ),
            'table'    => $allowed_atts,
            'thead'    => $allowed_atts,
            'tbody'    => $allowed_atts,
            'tr'       => $allowed_atts,
            'td'       => $allowed_atts,
            'th'       => $allowed_atts,

			'ul'         => $allowed_atts,
			'li'         => $allowed_atts,
			'kbd'      => $allowed_atts,
			'a'			=> $allowed_atts,
			'h1'			=> $allowed_atts,
			'h2'			=> $allowed_atts,
			'h3'			=> $allowed_atts,
			'h4'			=> $allowed_atts,
			'h5'			=> $allowed_atts,
			'style'			=> $allowed_atts,
			'div'		=> $allowed_atts,
			'p'			=> $allowed_atts,
			'b'        => $allowed_atts,
            'code'     => $allowed_atts,
            'strong'   => $allowed_atts,
            'span'     => $allowed_atts,
            'br'       => $allowed_atts,
            'img'      => array_merge( $allowed_atts, array(
                'class' => array(),
                'src'   => array(),
                'alt'   => array(),
            ) ),
			'style'    => array(
                'type' => array(),
                'media' => array(),
            ),
            'input'    => array_merge( $allowed_atts, array(
                'id'    => array(),
                'type'  => array(),
                'name'  => array(),
                'value' => array(),
            ) ),
        );

        return $additional_tags;
    }
endif;
