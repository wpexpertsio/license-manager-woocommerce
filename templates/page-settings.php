<?php

defined('ABSPATH') || exit;

/**
 * Available variables
 *
 * @var string $tab
 * @var string $urlGeneral
 * @var string $urlOrderStatus
 * @var string $urlRestApi
 * @var string $urlTools
 */

?>

<div class="wrap lmfwc">

    <?php settings_errors(); ?>
    <ul class="subsubsub"><li><a href="<?php echo esc_url($urlGeneral); ?>" class="<?=$tab === 'general' ? 'current' : '';?>">
        <span><?php esc_html_e('General', 'license-manager-for-woocommerce');?></span>
    </a> | </li><li><a href="<?php echo esc_url($urlOrderStatus); ?>" class="<?=$tab === 'order_status' ? 'current' : '';?>">
        <span><?php esc_html_e('Order status', 'license-manager-for-woocommerce');?></span>
    </a> | </li><li><a href="<?php echo esc_url($urlRestApi); ?>" class="<?=$tab === 'rest_api' ? 'current' : '';?>">
        <span><?php esc_html_e('REST API keys', 'license-manager-for-woocommerce');?></span>
    </a> | </li><li><a href="<?php echo esc_url($urlTools); ?>" class="<?=$tab === 'tools' ? 'current' : '';?>">
        <span><?php esc_html_e('Tools', 'license-manager-for-woocommerce');?></span>
    </a>  </li></ul>
    <br class="clear">

    <?php if ($tab == 'general'): ?>

        <form action="<?php echo admin_url('options.php'); ?>" method="POST">
            <?php settings_fields('lmfwc_settings_group_general'); ?>
            <?php do_settings_sections('lmfwc_license_keys'); ?>
            <?php do_settings_sections('lmfwc_my_account'); ?>
            <?php do_settings_sections('lmfwc_rest_api'); ?>
            <?php submit_button(); ?>
        </form>

    <?php elseif ($tab === 'order_status'): ?>

        <form action="<?php echo admin_url('options.php'); ?>" method="POST">
            <?php settings_fields('lmfwc_settings_group_order_status'); ?>
            <?php do_settings_sections('lmfwc_license_key_delivery'); ?>
            <?php submit_button(); ?>
        </form>

    <?php elseif ($tab === 'rest_api'): ?>

        <?php if ($action === 'list'): ?>

            <?php include_once 'settings/rest-api-list.php'; ?>

        <?php elseif ($action === 'show'): ?>

            <?php include_once 'settings/rest-api-show.php'; ?>

        <?php else: ?>

            <?php include_once 'settings/rest-api-key.php'; ?>

        <?php endif; ?>

    <?php elseif ($tab === 'tools'): ?>

        <form action="<?php echo admin_url('options.php'); ?>" method="POST">
            <?php settings_fields('lmfwc_settings_group_tools'); ?>
            <?php do_settings_sections('lmfwc_export'); ?>
            <?php submit_button(); ?>
        </form>

    <?php endif; ?>

</div>