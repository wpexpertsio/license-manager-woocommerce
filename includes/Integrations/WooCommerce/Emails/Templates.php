<?php

namespace LicenseManagerForWooCommerce\Integrations\WooCommerce\Emails;

use WC_Email;
use WC_Order;

defined('ABSPATH') || exit;

class Templates
{
    /**
     * Templates constructor.
     */
    function __construct()
    {
        add_action('lmfwc_email_order_details',      array($this, 'addOrderDetails'),     10, 4);
        add_action('lmfwc_email_order_license_keys', array($this, 'addOrderLicenseKeys'), 10, 4);
    }

    /**
     * Adds the ordered license keys to the email body.
     *
     * @param WC_Order $order       WooCommerce Order
     * @param bool     $sentToAdmin Determines if the email is sent to the admin
     * @param bool     $plainText   Determines if a plain text or HTML email will be sent
     * @param WC_Email $email       WooCommerce Email
     */
    public function addOrderDetails($order, $sentToAdmin, $plainText, $email)
    {
        if ($plainText) {
            echo wp_kses_post(
                wc_get_template(
                    'emails/plain/lmfwc-email-order-details.php',
                    array(
                        'order'         => $order,
                        'sent_to_admin' => false,
                        'plain_text'    => false,
                        'email'         => $email,
                        'args'          => apply_filters('lmfwc_template_args_emails_email_order_details', array())
                    ),
                    '',
                    LMFWC_TEMPLATES_DIR
                )
            );
        } else {
            echo wp_kses_post(
                wc_get_template_html(
                    'emails/lmfwc-email-order-details.php',
                    array(
                        'order'         => $order,
                        'sent_to_admin' => false,
                        'plain_text'    => false,
                        'email'         => $email,
                        'args'          => apply_filters('lmfwc_template_args_emails_email_order_details', array())
                    ),
                    '',
                    LMFWC_TEMPLATES_DIR
                )
            );
        }
    }
    

    /**
     * Adds basic order info to the email body.
     *
     * @param WC_Order $order       WooCommerce Order
     * @param bool     $sentToAdmin Determines if the email is sent to the admin
     * @param bool     $plainText   Determines if a plain text or HTML email will be sent
     * @param WC_Email $email       WooCommerce Email
     */
    public function addOrderLicenseKeys($order, $sentToAdmin, $plainText, $email)
    {
        if ($plainText) {
            echo wp_kses_post(
                wc_get_template(
                    'emails/plain/lmfwc-email-order-license-keys.php',
                    array(
                        'heading'       => apply_filters('lmfwc_license_keys_table_heading', null),
                        'valid_until'   => apply_filters('lmfwc_license_keys_table_valid_until', null),
                        'data'          => apply_filters('lmfwc_get_customer_license_keys', $order),
                        'date_format'   => get_option('date_format'),
                        'order'         => $order,
                        'sent_to_admin' => false,
                        'plain_text'    => false,
                        'email'         => $email,
                        'args'          => apply_filters('lmfwc_template_args_emails_order_license_keys', array())
                    ),
                    '',
                    LMFWC_TEMPLATES_DIR
                )
            );
        } else {
            echo wp_kses_post(
                wc_get_template_html(
                    'emails/lmfwc-email-order-license-keys.php',
                    array(
                        'heading'       => apply_filters('lmfwc_license_keys_table_heading', null),
                        'valid_until'   => apply_filters('lmfwc_license_keys_table_valid_until', null),
                        'data'          => apply_filters('lmfwc_get_customer_license_keys', $order),
                        'date_format'   => get_option('date_format'),
                        'order'         => $order,
                        'sent_to_admin' => false,
                        'plain_text'    => false,
                        'email'         => $email,
                        'args'          => apply_filters('lmfwc_template_args_emails_order_license_keys', array())
                    ),
                    '',
                    LMFWC_TEMPLATES_DIR
                )
            );
        }
    }
}