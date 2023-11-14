<?php defined('ABSPATH') || exit; ?>

<div style="padding: 30px 30px;">
	<?php if (  ! empty( $logo ) ) : ?>
        <div style="margin-bottom: 30px;">
            <img alt="Logo" src="<?php echo $logo; ?>" style="max-width: 100px;">
        </div>
    <?php else : ?>
        <h3><?php echo esc_html( $title ); ?></h3>
    <?php endif; ?>
    <div style="border: 1px solid #ccc;padding:20px; width:80%;">
        <h1 style="font-size: 30px;margin-top: 0; margin-bottom: 15px;">
			<?php esc_html_e( 'License Certificate', 'license-manager-for-woocommerce' ); ?>
        </h1>
        <p style="font-size: 16px; margin-bottom: 15px; margin-top:0;">
			<?php printf( __( ' This document certifies the purchase of license key for: <strong>%s</strong>.', 'license-manager-for-woocommerce' ), esc_attr( wp_strip_all_tags( $license_product_name ) ) ); ?>
        </p>
        <p style="font-size: 16px; margin-bottom: 15px; margin-top:0;">
			<?php esc_html_e( 'Details of the license can be accessed from your dashboard page.', 'license-manager-for-woocommerce' ); ?>
        </p>
		<?php if ( ! empty( $license_details ) ): ?>
            <table align="left" style="text-align: left;margin-bottom: 15px;font-size: 16px; border-spacing: 5px;">
                <tbody>
				<?php foreach ( $license_details as $detail ): ?>
                    <tr>
                        <th style="text-align:left;"><?php echo esc_html( $detail['title'] ); ?>:</th>
                        <td><?php echo esc_html( wp_strip_all_tags( $detail['value'] ) ); ?></td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>
		<?php endif; ?>
        <p style="font-size: 16px; margin-bottom: 15px; margin-top:0;">
			<?php esc_html_e( 'Thanks for using our services. If you have any questions feel free to reach out and ask.', 'license-manager-for-woocommerce' ); ?>
        </p>
    </div>
</div>
