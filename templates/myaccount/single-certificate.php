<?php 

defined('ABSPATH') || exit; 
/**
 * Some of the code written, maintained by Darko Gjorgjijoski
 */
?>

<div style="padding: 30px 30px; background-color: #f5f5f5; font-family: 'Arial', sans-serif;">

    <?php if (!empty($logo)) : ?>
        <div style="margin-bottom: 30px;">
        <img alt="Logo" src="<?php echo esc_url($logo); ?>" style="max-width: 100px; display: block; margin: 0 auto;">
        </div>
    <?php else : ?>
        <h3 style="text-align: center; color: #333;"><?php echo esc_html($title); ?></h3>
    <?php endif; ?>

    <div style="border: 1px solid #ccc; padding: 20px; width: 80%; margin: 0 auto; background-color: #fff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">

        <h1 style="font-size: 30px; margin-top: 0; margin-bottom: 15px; color: #333;">
            <?php esc_html_e('License Certificate', 'license-manager-for-woocommerce'); ?>
        </h1>

        <p style="font-size: 16px; margin-bottom: 15px; margin-top: 0; color: #555;">
        <?php
            printf(
                 // Translators: Placeholder 1 is the license product name.
                esc_html__(
                    'This document certifies the purchase of a license key for: <strong>%s</strong>.',
                    'license-manager-for-woocommerce'
                ),
                esc_html( wp_strip_all_tags( $license_product_name ) )
            );
        ?>
        </p>

        <p style="font-size: 16px; margin-bottom: 15px; margin-top: 0; color: #555;">
            <?php esc_html_e('Details of the license can be accessed from your dashboard page.', 'license-manager-for-woocommerce'); ?>
        </p>

        <?php if (!empty($license_details)) : ?>
            <table style="width: 100%; text-align: left; margin-bottom: 15px; font-size: 16px; border-spacing: 5px; border-collapse: collapse;">
                <tbody>
                    <?php foreach ($license_details as $detail) : ?>
                        <tr>
                            <th style="text-align: left; padding: 8px; background-color: #f2f2f2; border: 1px solid #ccc;"><?php echo esc_html($detail['title']); ?>:</th>
                            <td style="padding: 8px; border: 1px solid #ccc;"><?php echo esc_html(wp_strip_all_tags($detail['value'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p style="font-size: 16px; margin-bottom: 15px; margin-top: 0; color: #555;">
            <?php esc_html_e('Thanks for using our services. If you have any questions, feel free to reach out and ask.', 'license-manager-for-woocommerce'); ?>
        </p>

    </div>
</div>
