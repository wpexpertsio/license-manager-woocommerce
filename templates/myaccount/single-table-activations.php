<?php
/**
 * The template for the overview of all license activations on the single license page in "My Account"
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/lmfwc/myaccount/licenses/single-table-activations.php
 *
 * HOWEVER, on occasion I will need to update template files and you
 * (the developer) will need to copy the new files to your theme to
 * maintain compatibility. I try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 *
 * Default variables
 *
 * @var License $license
 * @var WC_Order $order
 * @var WC_Product $product
 * @var string $date_format
 * @var string $license_key
 * @var int $manual_activations_enabled
 * @var array $rowActions
 * @var \LicenseManagerForWooCommerce\Database\Models\LicenseActivation[] $activations
 *
 * Some of the code written, maintained by Darko Gjorgjijoski
 *
 */

use LicenseManagerForWooCommerce\Models\Resources\License;
use LicenseManagerForWooCommerce\Enums\ActivationProcessor;
use LicenseManagerForWooCommerce\Enums\LicenseStatus;
use LicenseManagerForWooCommerce\Integrations\WooCommerce\Activations;
use LicenseManagerForWooCommerce\Integrations\WooCommerce\Controller;
use LicenseManagerForWooCommerce\Settings;

?>

<div class="lmfwc-license-activations">
    <div class="lmfwc-header">
        <h3 class="product-name"><?php _e( 'Activations', 'license-manager-for-woocommerce' ); ?></h3>
    </div>

    <table class="shop_table shop_table_responsive my_account_orders">
        <thead>
        <tr>
            <th class="table-col table-col-label"><?php _e( 'Label', 'license-manager-for-woocommerce' ); ?></th>
            <th class="table-col table-col-status"><?php _e( 'Status', 'license-manager-for-woocommerce' ); ?></th>
            <th class="table-col table-col-source"><?php _e( 'Source', 'license-manager-for-woocommerce' ); ?></th>
            <th class="table-col table-col-date"><?php _e( 'Date', 'license-manager-for-woocommerce' ); ?></th>
            <th class="table-col table-col-actions"><?php _e( '', 'license-manager-for-woocommerce' ); ?></th>
			

        </tr>
        </thead>
        <tbody>
       
		<?php if ( count( $activations ) > 0 ): ?>
			<?php foreach ( $activations as $activation ): ?>
                <tr>
                    <td>
						<?php
						$label = $activation->getLabel();
						if ( empty( $label ) ) {
							$label =  substr($activation->getToken(),0 ,12 );
						}
						echo esc_html( $label );
						?>
                    </td>
                    <td>
						<?php
						if ( $activation->getDeactivatedAt() ) {
							echo LicenseStatus::statusToHtml( 'disabled', [
								'style' => 'inline',
								'text'  => __( 'Not Active', 'license-manager-for-woocommerce' )
							] );
						} else {
							echo LicenseStatus::statusToHtml( 'delivered', [
								'style' => 'inline',
								'text'  => __( 'Active', 'license-manager-for-woocommerce' )
							] );
						}
						?>
                    </td>
                    <td>
						<?php
						echo ActivationProcessor::getLabel( $activation->getSource() );
						?>
                    </td>
                    <td>
						<?php

						if ( $activation->getCreatedAt() ) {
							try {
								$date = new \DateTime( $activation->getCreatedAt() );
								printf( '<b>%s</b>', $date->format( $date_format ) );
							} catch ( Exception $e ) {
								_e( 'N/A', 'license-manager-for-woocommerce' );
							}
						} else {
							_e( 'N/A', 'license-manager-for-woocommerce' );
						}
						?>
                    </td>
					
                    <td>
                    	
						<?php if (Settings::get('lmfwc_allow_users_to_deactivate' , Settings::SECTION_WOOCOMMERCE)): ?>
			            <form method="post" style="display: inline-block; margin: 0;">
			                <input type="hidden" name="token" value="<?php echo $activation->getToken();?>"/>
			                <?php if ( $activation->getDeactivatedAt() ) { ?>
							<input type="hidden" name="action" value="reactivate">
			                <?php wp_nonce_field('lmfwc_myaccount_reactivate_license'); ?>
			                <button class="button" type="submit"><?php _e('Reactivate', 'license-manager-for-woocommerce');?></button>
						<?php } else { ?>
							<input type="hidden" name="action" value="deactivate">
			                <?php wp_nonce_field('lmfwc_myaccount_deactivate_license'); ?>
			                <button class="button" type="submit"><?php _e('Deactivate', 'license-manager-for-woocommerce');?></button>
						<?php } ?>
			            </form>
        				<?php endif; ?>

			            <form method="post" style="display: inline-block; margin: 0;">
			                <input type="hidden" name="activation_id" value="<?php echo $activation->getId();?>"/>
			                <input type="hidden" name="license_id" value="<?php echo $activation->getLicenseId();?>"/>
			                <input type="hidden" name="action" value="delete">
			                <?php wp_nonce_field('lmfwc_myaccount_delete_license'); ?>
			                <button class="button" type="submit"><?php _e('Delete', 'license-manager-for-woocommerce');?></button>
			            </form>
    					

                    </td>
					
                </tr>

			<?php endforeach; ?>

		<?php else: ?>

            <tr>
                <td colspan="4">
                    <p><?php _e( 'No activations found.', 'license-manager-for-woocommerce' ); ?></p>
                </td>
            </tr>

		<?php endif; ?>

        </tbody>
    </table>
 		<?php if ( Settings::get('lmfwc_allow_users_to_activate' , Settings::SECTION_WOOCOMMERCE)): ?>
        <form method="post" style="display: inline-block; margin: 0;">
            <input type="hidden" name="license" value="<?php echo $license->getDecryptedLicenseKey();?>"/>
            <input type="hidden" name="action" value="activate">
            <?php wp_nonce_field('lmfwc_myaccount_activate_license'); ?>
            <button class="button" type="submit"><?php _e('Activate', 'license-manager-for-woocommerce');?></button>
        </form>
        <?php endif; ?>
       