<?php

namespace LicenseManagerForWooCommerce\Integrations\WooCommerce;

use LicenseManagerForWooCommerce\Enums\LicenseStatus;
use LicenseManagerForWooCommerce\Repositories\Resources\License;
use LicenseManagerForWooCommerce\Settings;
use WC_Product;
use WP_Query;

defined('ABSPATH') || exit;

class Stock
{
    /**
     * Stock constructor.
     */
    public function __construct()
    {
        add_filter('lmfwc_stock_increase',    array($this, 'increase'),    10, 2);
        add_filter('lmfwc_stock_decrease',    array($this, 'decrease'),    10, 2);
        add_filter('lmfwc_stock_synchronize', array($this, 'synchronize'), 10, 0);

        add_filter('woocommerce_product_data_store_cpt_get_products_query', array($this, 'handleCustomQueryVar'), 10, 2 );
    }

    /**
     * Class internal function used to modify the stock amount.
     *
     * @param int|WC_Product $product
     * @param string         $action
     * @param int            $amount
     *
     * @return bool|WC_Product
     */
    private function modify($product, $action, $amount = 1)
    {
        // Check if the setting is enabled
        if (!Settings::get('lmfwc_enable_stock_manager' , Settings::SECTION_WOOCOMMERCE)) {
            return false;
        }

        // Retrieve the WooCommerce Product if we're given an ID
        if (is_numeric($product)) {
            $product = wc_get_product($product);
        }

        // No need to modify if WooCommerce is not managing the stock
        if (!$product instanceof WC_Product || !$product->managing_stock()) {
            return false;
        }

        // Retrieve the current stock
        $stock = $product->get_stock_quantity();

        // Normalize
        if ($stock === null) {
            $stock = 0;
        }

        // Add or subtract the given amount to the stock
        if ($action === 'increase') {
            $stock += $amount;
        } elseif ($action === 'decrease') {
            $stock -= $amount;
        }

        // Set and save
        $product->set_stock_quantity($stock);
        $product->save();

        return $product;
    }

    /**
     * Increases the available stock of a WooCommerce Product by $amount.
     *
     * @param int|WC_Product $product WooCommerce Product object
     * @param int            $amount  Increment amount
     *
     * @return bool|WC_Product
     */
    public function increase($product, $amount = 1)
    {
        return $this->modify($product,'increase', $amount);
    }

    /**
     * Decreases the available stock of a WooCommerce Product by $amount.
     *
     * @param WC_Product $product WooCommerce Product object
     * @param int        $amount  Decrement amount
     *
     * @return bool|WC_Product
     */
    public function decrease($product, $amount = 1)
    {
        return $this->modify($product,'decrease', $amount);
    }

    /**
     * Synchronizes the license stock with the WooCommerce products stock.
     * Returns the number of synchronized WooCommerce products.
     *
     * @return int
     */
    public function synchronize() {
        // For the query to return any results, the following WooCommerce Product settings need to be enabled:
        // 1. Inventory       -> Manage stock?
        // 2. License Manager -> Sell license keys
        // 3. License Manager -> Sell from stock
        $args = array(
            'limit'                            => - 1,
            'orderBy'                          => 'id',
            'order'                            => 'ASC',
            'manage_stock'                     => true,
            'lmfwc_licensed_product'           => true,
            'lmfwc_licensed_product_use_stock' => true,
        );
        
        $products     = wc_get_products( $args );
        $synchronized = 0;
        
        $variation_products = wc_get_products( array(
            'type' => 'variation',
            'limit' => -1,
            'manage_stock'                     => true,
            'lmfwc_licensed_product'           => true,
            'lmfwc_licensed_product_use_stock' => true,
        ) );
        
        // No such products, nothing to do
        if ( count( $products ) === 0 ) {
            return $synchronized;
        }
        foreach ( $variation_products as $product ) {
            $woocommerceStock = (int) $product->get_stock_quantity();
            $licenseStock     = License::instance()->countBy(
                array(
                    'status'     => LicenseStatus::ACTIVE,
                    'product_id' => $product->get_id(),
                )
            );

            // Nothing to do in this case
            if ( $woocommerceStock === $licenseStock ) {
                continue;
            }

            // Update the stock
            $product->set_stock_quantity( $licenseStock );
            $product->save();
            $synchronized ++;
        }
        foreach ( $products as $product ) {
            $woocommerceStock = (int) $product->get_stock_quantity();
            $licenseStock     = License::instance()->countBy(
                array(
                    'status'     => LicenseStatus::ACTIVE,
                    'product_id' => $product->get_id(),
                )
            );

            // Nothing to do in this case
            if ( $woocommerceStock === $licenseStock ) {
                continue;
            }

            // Update the stock
            $product->set_stock_quantity( $licenseStock );
            $product->save();
            $synchronized ++;
        }

        return $synchronized;
    }


    /**
     * @param array $query
     * @param array $query_vars
     *
     * @return mixed
     */
    public function handleCustomQueryVar($query, $query_vars)
    {
        if (!empty($query_vars['lmfwc_licensed_product'])) {
            $query['meta_query'][] = array(
                'key'   => 'lmfwc_licensed_product',
                'value' => esc_attr($query_vars['lmfwc_licensed_product'])
            );
        }

        if (!empty($query_vars['lmfwc_licensed_product_use_stock'])) {
            $query['meta_query'][] = array(
                'key'   => 'lmfwc_licensed_product_use_stock',
                'value' => esc_attr($query_vars['lmfwc_licensed_product_use_stock'])
            );
        }

        return $query;
    }
}
