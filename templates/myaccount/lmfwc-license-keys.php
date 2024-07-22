<?php
/**
 * The template for the purchased license keys inside "My account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/lmfwc-license-keys.php.
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 2.0.0
 */

use LicenseManagerForWooCommerce\Models\Resources\License as LicenseResourceModel;

defined('ABSPATH') || exit; ?>

<h2><?php echo esc_html($heading); ?></h2>

<?php foreach ($data as $productId => $row): ?>
    <table class="shop_table">
        <tbody>
        <thead>
        <tr>
            <th colspan="2"><?php echo esc_html($row['name']); ?></th>
        </tr>
        </thead>
        <?php
        /** @var LicenseResourceModel $license */
        foreach ($row['keys'] as $license):
            ?>
            <tr>
                <td colspan="<?php echo ($license->getExpiresAt()) ? '1' : '2'; ?>">
                    <span class="lmfwc-myaccount-license-key"><?php echo esc_html($license->getDecryptedLicenseKey()); ?></span>
                </td>
                <?php if ($license->getExpiresAt()): ?>
                    <?php
                    try {
                        $date = wp_date( $date_format, strtotime( $license->getExpiresAt() ) );
                    } catch (Exception $e) {
                    }
                    ?>
                    <td>
                    <span class="lmfwc-myaccount-license-key"><?php printf('%s <strong>%s</strong>', esc_html($valid_until), esc_html($date)); ?></span>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
      
        </tbody>
    </table>
<?php endforeach; ?>

