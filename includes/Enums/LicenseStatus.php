<?php

namespace LicenseManagerForWooCommerce\Enums;

use ReflectionClass;
use ReflectionException;

defined('ABSPATH') || exit;

abstract class LicenseStatus
{
    /**
     * Enumerator value used for sold licenses.
     *
     * @var int
     */
    const SOLD = 1;

    /**
     * Enumerator value used for delivered licenses.
     *
     * @var int
     */
    const DELIVERED = 2;

    /**
     * Enumerator value used for active licenses.
     *
     * @var int
     */
    const ACTIVE = 3;

    /**
     * Enumerator value used for inactive licenses.
     *
     * @var int
     */
    const INACTIVE = 4;

    /**
     * Available enumerator values.
     *
     * @var array
     */
    public static $status = array(
        self::SOLD,
        self::DELIVERED,
        self::ACTIVE,
        self::INACTIVE
    );

    /**
     * Available text representations of the enumerator
     *
     * @var array
     */
    public static $enumArray = array(
        'sold',
        'delivered',
        'active',
        'inactive'
    );

    /**
     * Key/value pairs of text representations and actual enumerator values.
     *
     * @var array
     */
    public static $values = array(
        'sold'      => self::SOLD,
        'delivered' => self::DELIVERED,
        'active'    => self::ACTIVE,
        'inactive'  => self::INACTIVE
    );

    /**
     * Returns the string representation of a specific enumerator value.
     *
     * @param int $status Status enumerator value
     *
     * @return string
     */
    public static function getExportLabel($status)
    {
        $labels = array(
            self::SOLD      => 'SOLD',
            self::DELIVERED => 'DELIVERED',
            self::ACTIVE    => 'ACTIVE',
            self::INACTIVE  => 'INACTIVE'
        );

        return $labels[$status];
    }

    /**
     * Returns an array of enumerators to be used as a dropdown.
     *
     * @return array
     */
    public static function dropdown()
    {
        return array(
            array(
                'value' => self::ACTIVE,
                'name' => __('Active', 'license-manager-for-woocommerce')
            ),
            array(
                'value' => self::INACTIVE,
                'name' => __('Inactive', 'license-manager-for-woocommerce')
            ),
            array(
                'value' => self::SOLD,
                'name' => __('Sold', 'license-manager-for-woocommerce')
            ),
            array(
                'value' => self::DELIVERED,
                'name' => __('Delivered', 'license-manager-for-woocommerce')
            )
        );
    }

    /**
     * Returns the class constants as an array.
     *
     * @return array
     * @throws ReflectionException
     */
    public static function getConstants()
    {
        $oClass = new ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }

    /**
     * Creates "Expired" status
     * @return string
     */
    public static function toHtmlExpired( $license, $args = [] ) {

        $args     = wp_parse_args( $args, [ 'style' => 'normal', 'text' => '' ] );
        $cssClass = $args['style'] === 'normal' ? 'lmfwc-status' : 'lmfwc-status-' . $args['style'];

        return sprintf(
            '<div class="%s lmfwc-status-inactive"><span class="dashicons dashicons-marker"></span> %s</div>',
            $cssClass,
            ! empty( $args['text'] ) ? esc_html( $args['text'] ) : __( 'Expired', 'license-manager-for-woocommerce' )
        );
    }

    /**
     * Show the license status
     *
     * @param License $license
     */
    public static function toHtml( $license, $args = [] ) {

        $status = ! empty( $license ) ? $license->getStatus() : 'unknown';

        return self::statusToHtml( $status, $args );
    }

    /**
     * Returns the license status
     *
     * @param $status
     * @param array $args
     *
     * @return string
     */
    public static function statusToHtml( $status, $args = [] ) {

        $args     = wp_parse_args( $args, [ 'style' => 'normal', 'text' => '' ] );
        $cssClass = $args['style'] === 'normal' ? 'lmfwc-status' : 'lmfwc-status-' . $args['style'];

        switch ( $status ) {
            case 'sold':
            case LicenseStatus::SOLD:
                $markup = sprintf(
                    '<div class="%s lmfwc-status-sold"><span class="dashicons dashicons-saved"></span> %s</div>',
                    $cssClass,
                    ! empty( $args['text'] ) ? esc_html( $args['text'] ) : __( 'Sold&nbsp;&nbsp;&nbsp;', 'license-manager-for-woocommerce' )
                );
                break;
            case 'delivered':
            case LicenseStatus::DELIVERED:
                $markup = sprintf(
                    '<div class="%s lmfwc-status-delivered"><span class="dashicons dashicons-saved"></span> %s</div>',
                    $cssClass,
                    ! empty( $args['text'] ) ? esc_html( $args['text'] ) : __( 'Delivered', 'license-manager-for-woocommerce' )
                );
                break;
            case 'active':
            case LicenseStatus::ACTIVE:
                $markup = sprintf(
                    '<div class="%s lmfwc-status-active"><span class="dashicons dashicons-marker"></span> %s</div>',
                    $cssClass,
                    ! empty( $args['text'] ) ? esc_html( $args['text'] ) : __( 'Active', 'license-manager-for-woocommerce' )
                );
                break;
            case 'inactive':
            case LicenseStatus::INACTIVE:
                $markup = sprintf(
                    '<div class="%s lmfwc-status-inactive"><span class="dashicons dashicons-marker"></span> %s</div>',
                    $cssClass,
                    ! empty( $args['text'] ) ? esc_html( $args['text'] ) : __( 'Inactive', 'license-manager-for-woocommerce' )
                );
                break;
            case 'disabled':
            case LicenseStatus::DISABLED:
                $markup = sprintf(
                    '<div class="%s lmfwc-status-disabled"><span class="dashicons dashicons-warning"></span> %s</div>',
                    $cssClass,
                    ! empty( $args['text'] ) ? esc_html( $args['text'] ) : __( 'Disabled', 'license-manager-for-woocommerce' )
                );
                break;
            default:
                $markup = sprintf(
                    '<div class="%s lmfwc-status-unknown">%s</div>',
                    $cssClass,
                    __( 'Unknown', 'license-manager-for-woocommerce' )
                );
                break;
        }

        return $markup;
    }
}
