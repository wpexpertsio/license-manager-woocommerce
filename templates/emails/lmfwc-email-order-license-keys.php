<?php
/**
 * The template which adds the license keys to the "order complete" email (HTML).
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/lmfwc-email-order-license-keys.php.
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 2.0.0
 */

use LicenseManagerForWooCommerce\Models\Resources\License;

defined('ABSPATH') || exit; ?>

<h2><?php echo esc_html($heading); ?></h2>
<div style="margin-bottom: 40px;">
    <?php foreach ($data as $row): ?>
        <table class="td" cellspacing="0" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
            <tbody>
                <thead>
                    <tr>
                        <th class="td" scope="col" style="text-align: left;" colspan="2">
                            <span><?php echo esc_html($row['name']); ?></span>
                        </th>
                    </tr>
                </thead>
                <?php
                    /** @var License $license */
                    foreach ($row['keys'] as $license):
                ?>
                    <tr>
                        <td class="td" style="text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" colspan="<?php echo ($license->getExpiresAt()) ? '1' : '2'; ?>">
                            <code><?php echo esc_html($license->getDecryptedLicenseKey()); ?></code>
                        </td>

                        <?php if ($license->getExpiresAt()): ?>
                            <?php
                                try {
                                    $date = wp_date( lmfwc_expiration_format(), strtotime( $license->getExpiresAt() ) );
                                } catch (Exception $e) {
                                }
                            ?>
                            <td class="td" style="text-align: left; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                                <code><?php
                                printf(
                                    // translators: %1$s represents $valid_until, %2$s represents $date
                                    esc_html__( '%1$s <strong>%2$s</strong>', 'your-text-domain' ),
                                    esc_html( $valid_until ),
                                    esc_html( $date )
                                );
                                ?></code>

                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
</div>
