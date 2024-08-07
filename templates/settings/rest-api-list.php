<?php defined('ABSPATH') || exit; ?>

<h1>
    <span><?php esc_html_e('REST API', 'license-manager-for-woocommerce'); ?></span>
    <a class="add-new-h2" href="<?php echo esc_url( admin_url( sprintf( 'admin.php?page=%s&tab=%2s&section=rest_api&create_key=1', \LicenseManagerForWooCommerce\AdminMenus::WC_SETTINGS_PAGE, \LicenseManagerForWooCommerce\AdminMenus::SETTINGS_PAGE ) ) ); ?>">
        <span><?php esc_html_e( 'Add key', 'license-manager-for-woocommerce' ); ?></span>
    </a>

</h1>
<hr class="wp-header-end">

<form method="post">
    <?php
        $keys->prepare_items();
        $keys->views();
        $keys->search_box(__('Search key', 'license-manager-for-woocommerce'), 'key');
        $keys->display();
    ?>
</form>
