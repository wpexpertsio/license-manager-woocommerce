<?php
/**
 * The template for the overview of all customer license keys, across all orders, inside "My Account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/lmfwc-view-license-keys.php.
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 2.1.0
 *
 * Default variables
 *
 * @var $licenseKeys array
 * @var $page        int
 * @var $dateFormat  string
 */

use LicenseManagerForWooCommerce\Models\Resources\License as LicenseResourceModel;
use LicenseManagerForWooCommerce\Settings;
use LicenseManagerForWooCommerce\Integrations\WooCommerce\Controller;

defined('ABSPATH') || exit; ?>

<?php 

if ( ! empty( $licenseKeys ) ): ?>

<h2><?php esc_html_e('Your license keys', 'license-manager-for-woocommerce'); ?></h2>

<?php foreach ($licenseKeys as $productId => $licenseKeyData): ?>
    <?php $product = wc_get_product($productId); ?>

    <h3 class="product-name">
        <?php if ($product): ?>
            <a href="<?php echo esc_url(get_post_permalink($productId)); ?>">
                <span><?php echo esc_html($licenseKeyData['name']); ?></span>
            </a>
        <?php else: ?>
            <span><?php echo esc_html(__('Product', 'license-manager-for-woocommerce') . ' #' . $productId); ?></span>
        <?php endif; ?>
    </h3>

    <table class="shop_table shop_table_responsive my_account_orders">
        <thead>
            <tr>
                <th class="license-key"><?php esc_html_e('License key', 'license-manager-for-woocommerce'); ?></th>
                <th class="activation"><?php esc_html_e('Activation status', 'license-manager-for-woocommerce'); ?></th>
                <th class="valid-until"><?php esc_html_e('Valid until', 'license-manager-for-woocommerce'); ?></th>
                <th class="actions"></th>
            </tr>
        </thead>

        <tbody>

            <?php
            /** @var LicenseResourceModel $license */
            foreach ($licenseKeyData['licenses'] as $license):
                $timesActivated    = $license->getTimesActivated() ? $license->getTimesActivated() : '0';
                $timesActivatedMax = $license->getTimesActivatedMax() ? $license->getTimesActivatedMax() : '&infin;';
                $order             = wc_get_order($license->getOrderId());
                ?>
                <tr>
                    <td><span class="lmfwc-myaccount-license-key"><?php echo esc_html($license->getDecryptedLicenseKey()); ?></span></td>
                    <td>
                        <span><?php echo esc_html($timesActivated); ?></span>
                        <span>/</span>
                        <span><?php echo esc_html($timesActivatedMax); ?></span>
                    </td>
                    <td><?php
                    if ($license->getExpiresAt()) {
                        printf('<b>%s</b>', esc_html(wp_date(lmfwc_expiration_format(), strtotime($license->getExpiresAt()))));
                    } elseif ($license->getValidFor()) {
                        $validDate = date('Y-m-d', strtotime($order->get_date_paid() . ' + ' . $license->getValidFor() . ' days'));
                        printf('<b>%s</b>', esc_html(wp_date(lmfwc_expiration_format(), strtotime($validDate))));
                    } else {
                        echo esc_html__('Never Expires', 'license-manager-for-woocommerce');
                    } ?>
                    
                </td>
                <td class="license-key-actions">
                    
                    <a href="<?php echo esc_url(Controller::getAccountLicenseUrl($license->getId())); ?>" class="button view"><?php esc_html_e('View', 'license-manager-for-woocommerce'); ?></a>

                    <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="button view"><?php esc_html_e('Order', 'license-manager-for-woocommerce'); ?></a>
                </td>
            </tr>
        <?php endforeach; 
        ?>
    </tbody>
    </table>
<?php endforeach; ?>

<?php else: ?>

    <div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
        <?php esc_html_e('No licenses available yet', 'license-manager-for-woocommerce'); ?>
    </div>
<?php endif; ?>
