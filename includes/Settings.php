<?php

namespace LicenseManagerForWooCommerce;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key as DefuseCryptoKey;
use LicenseManagerForWooCommerce\Enums\LicenseStatus;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\Generator as GeneratorResourceRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\ApiKey as ApiResourceRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\LicenseActivations as ActivationResourceRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\LicenseMeta as LicenseMetaResourceRepository;
use LicenseManagerForWooCommerce\Enums\ActivationProcessor;
use LicenseManagerForWooCommerce\Enums\LicenseSource;
use Exception;

defined('ABSPATH') || exit;

class Settings
{
    private static $upload_dir;
    /**
     * @var string
     */
    const SECTION_GENERAL = 'lmfwc_settings_general';

    /**
     * @var string
     */
    const SECTION_WOOCOMMERCE = 'lmfwc_settings_woocommerce';

    /**
     * @var string
     */
    const SECTION_TOOLS = 'lmfwc_settings_tools';

    /**
     * Settings Constructor.
     */
    public function __construct()
    {
        // Initialize the settings classes
        new Settings\General();
        new Settings\WooSettings();
        new Settings\Tools();
        add_action( 'wp_ajax_lmfwc_handle_tool_process', array( $this, 'handleToolProcess' ), 50 );

    }

    /**
     * Handles tool process
     * @return void
     */
    public function handleToolProcess() {


        if ( ! check_ajax_referer( 'lmfwc_dropdown_search', 'security', false ) || ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied.' ) ] );
            exit;
        } 
        $identifier = isset( $_POST['identifier'] ) ? $_POST['identifier'] : '';

        if( $identifier  ==  'generate' ) {
            $page           = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : null;
            $generatorId    = isset( $_POST['generator'] ) ? intval( $_POST['generator'] ) : 0;
            $useProductConf = isset( $_POST['use_product_licensing_configuration'] ) ? intval( $_POST['use_product_licensing_configuration'] ) : 0;

            $next = $this->getNextPage( $page );

            if ( is_wp_error( $next ) ) {
                wp_send_json_error( [ 'message' => $next->get_error_message(), 'percent' => 100 ] );
                exit;
            } else {

                $result               = $this->doStep( $useProductConf, $generatorId, $page );
                $next['order_ids'] = $result;
                $step_message = sprintf(
                    /* translators: %1$d is the current page number, %2$d is the total number of pages */
                    __( 'Page %1$d of %2$d completed successfully.', 'your-text-domain' ),
                    $page,
                    $next['total']
                );                
                $next['error_message'] = is_wp_error( $result ) ? $result->get_error_message() : $step_message;
                wp_send_json_success( $next );
                exit;
            }
            
        }
        elseif( $identifier  ==  'migrate' ){
            $pluginSlug = isset( $_POST['plugin_name'] ) ? $_POST['plugin_name'] : '';
            $result  = $this->migrateData( $pluginSlug );
            wp_send_json_success( $result );
            exit;
        }




    }

    public function migrateData( $pluginSlug ) {

        global $wpdb;
        $preserve_ids = isset( $_POST['preserve_ids'] ) ? intval( $_POST['preserve_ids'] ) : 0;

         if ( $preserve_ids ) {
                LicenseResourceRepository::instance()->truncate();
                ActivationResourceRepository::instance()->truncate();
                LicenseMetaResourceRepository::instance()->truncate();
                GeneratorResourceRepository::instance()->truncate();  
                ApiResourceRepository::instance()->truncate();

            }

        if ( 'dlm' == $pluginSlug ) {

            $settings_general = get_option( 'dlm_settings_general', array() );
            $settings_woocommerce = get_option( 'dlm_settings_woocommerce', array() );

            $table1 = $wpdb->prefix . 'dlm_licenses';
            $table2 = $wpdb->prefix . 'dlm_generators';
            $table3 = $wpdb->prefix . 'dlm_api_keys';
            $table4 = $wpdb->prefix . 'dlm_license_activations';
            $table5 = $wpdb->prefix . 'dlm_license_meta';

            $licenses = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s', $table1 ), ARRAY_A );

            foreach ( $licenses as $row ) {
                $license_key = self::decrypt( $row['license_key'] );
                $activationCount = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %1s where license_id = %d', $table4, $row['id'] ) );
                $new_row_data = array(
                    'order_id'          => $row['order_id'],
                    'product_id'        => $row['product_id'],
                    'user_id'           => $row['user_id'],
                    'license_key'       => apply_filters('lmfwc_encrypt', $license_key ),
                    'hash'              => apply_filters('lmfwc_hash', $license_key ),
                    'expires_at'        => $row['expires_at'],
                    'valid_for'         => $row['valid_for'],
                    'source'            => LicenseSource::MIGRATION,
                    'status'            => $row['status'],
                    'times_activated'   => (int)$activationCount,
                    'times_activated_max' => $row['activations_limit'],
                    'created_at'        => $row['created_at'],
                    'created_by'        => $row['created_by'],
                    'updated_at'        => $row['updated_at'],
                    'updated_by'        => $row['updated_by']
                );

                if ( $preserve_ids ) {
                    $new_row_data['id'] = $row['id'];
                }
                $new_row = LicenseResourceRepository::instance()->insert( $new_row_data );

                if ( ! empty( $new_row ) ) {
                $activations = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s where license_id = %d', $table4, $row['id'] ), ARRAY_A );

                    if ( ! empty( $activations ) ) {
                        foreach ( $activations as $oldActivation ) {
                            $oldActivation['license_id'] = $new_row->getId();


                            if ( !$preserve_ids ) {
                                unset($oldActivation['id']);
                            }

                            $new_activation = ActivationResourceRepository::instance()->insert( $oldActivation );
                        }
                    }
                    $old_meta_rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s where license_id = %d', $table5, $row['id'] ), ARRAY_A );
                    if ( ! empty( $old_meta_rows ) ) {
                        foreach ( $old_meta_rows as $old_meta_row ) {
                            $old_meta_row['license_id'] = $new_row->getId();
                            if ( ! $preserve_ids ) {
                                unset( $old_meta_row['meta_id'] );

                            }
                            LicenseMetaResourceRepository::instance()->insert( $old_meta_row );
                        }
                    }
                }
            }
           //generators
            $generators = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s', $table2 ), ARRAY_A );
            foreach ( $generators as $row ) {
                $row['times_activated_max'] = $row['activations_limit'];
                unset($row['activations_limit']);
                if ( !$preserve_ids ) {
                    unset($row['id']);
                }
                GeneratorResourceRepository::instance()->insert( $row );
            }

            //apikeys
             $apikeys = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s', $table3 ), ARRAY_A );
            foreach ( $apikeys as $row ) {
              
                unset($row['endpoints']);
                if ( ! $preserve_ids ) {
                    unset($row['id']);
                }
                
                ApiResourceRepository::instance()->insert( $row );
            }

            if ( ! function_exists( 'wc_get_products' ) ) {
                return new WP_Error( 'WooCommerce is not active.' );
            }

            $args = array(
                'meta_key'     => 'dlm_licensed_product',
                'meta_value'   => 1,
                'meta_compare' => '=',
                'type'         => array( 'simple', 'variation' ),
                'return'       => 'objects',
            );

            $query = (array) wc_get_products( $args );
            if ( ! empty( $query['products'] ) ) {
                foreach ( $query['products'] as $product ) {
                    /* @var \WC_Product $product */
                    $quantity      = (int) $product->get_meta( 'dlm_licensed_product_delivered_quantity', true );
                    $generator     = (int) $product->get_meta(  'dlm_licensed_product_assigned_generator', true );
                    $product->update_meta_data( 'lmfwc_licensed_product', 1 );
                    $product->update_meta_data( 'lmfwc_licensed_product_delivered_quantity', $quantity );
                    if ( $generator ) {
                        $product->update_meta_data( 'lmfwc_licensed_product_use_generator', '1' );
                        $product->update_meta_data( 'lmfwc_licensed_product_assigned_generator', $generator );
                    } else {
                        $product->update_meta_data( 'lmfwc_licensed_product_use_stock', '1' );
                    }
                    $product->save();
                }
            }

            $general_array = array(
                'lmfwc_hide_license_keys' => isset($settings_general['hide_license_keys']) ? $settings_general['hide_license_keys'] : '',
                'lmfwc_allow_duplicates' => isset($settings_general['allow_duplicates']) ? $settings_general['allow_duplicates'] : '',
                'lmfwc_disable_api_ssl' => isset($settings_general['disable_api_ssl']) ? $settings_general['disable_api_ssl'] : '',
                'lmfwc_expire_format' => isset($settings_general['expiration_format']) ? $settings_general['expiration_format'] : ''
            );
            $woocommerce_array = array(
                'lmfwc_company_logo' => isset($settings_woocommerce['company_logo']) ? $settings_woocommerce['company_logo'] : '',
                'lmfwc_auto_delivery' => isset($settings_woocommerce['auto_delivery']) ? $settings_woocommerce['auto_delivery'] : '' ,
                'lmfwc_enable_stock_manager' => isset($settings_woocommerce['stock_management']) ? $settings_woocommerce['stock_management'] : '',
                'lmfwc_enable_my_account_endpoint' => isset($settings_woocommerce['myaccount_endpoint']) ? $settings_woocommerce['myaccount_endpoint'] : '',
                'lmfwc_allow_users_to_activate' => isset($settings_woocommerce['enable_manual_activations']) ? $settings_woocommerce['enable_manual_activations'] : '',
                'lmfwc_license_key_delivery_options' => isset($settings_woocommerce[ 'order_delivery_statuses' ]) ? $settings_woocommerce['order_delivery_statuses'] : array('wc-completed' => array( 'send' => '1'  ) ),
                'lmfwc_download_certificates' => isset($settings_woocommerce[ 'enable_certificates' ]) ? $settings_woocommerce['enable_certificates'] : ''
            );

            update_option('lmfwc_settings_general', $general_array);
            update_option('lmfwc_settings_woocommerce', $woocommerce_array);
            $next = array( 'next_page' => -1 , 'message' => __('Operation Completed' , 'license-manager-for-woocommerce'), 'percent' => 100 );
            wp_send_json_success( $next );
        }
    }

    protected static function decrypt( $license_key ) {
        return \Defuse\Crypto\Crypto::decrypt( $license_key, DefuseCryptoKey::loadFromAsciiSafeString( self::find3rdPartyDefuse() ) );
    }
    protected static function find3rdPartyDefuse() {

        if ( defined( 'DLM_PLUGIN_DEFUSE' ) ) {
            return DLM_PLUGIN_DEFUSE;
        }

        if ( is_null( self::$upload_dir ) ) {
            self::$upload_dir = wp_upload_dir()['basedir'] . '/dlm-files/';
        }

        if ( file_exists( self::$upload_dir . 'defuse.txt' ) ) {
            return (string) file_get_contents( self::$upload_dir . 'defuse.txt' );
        }

        return null;
    }

    /**
     * Initializes the process
     *
     * @param $step
     * @param $page
     *
     * @return bool|\WP_Error
     */
    public function doStep( $useProductConf, $generatorId, $page ) {

        $query = array_merge( $this->getOrdersQuery(), [
            'page' => 1,
        ] );

        $results = wc_get_orders( $query );

        if ( empty( $results->orders ) ) {
            delete_transient('max_order_pages');
            return new \WP_Error(
                'not_found',
                /* translators: %s is the page number */
                sprintf( __( 'No orders found for page %s', 'your-text-domain' ), $page )
            );            
        }
        $generator      = GeneratorResourceRepository::instance()->find( $generatorId );

        static $productGenerators = [];

        $generated = 0;

        foreach ( $results->orders as $order ) {
            $generatedForOrder = 0;
            /* @var \WC_Order $order */
            $skip_order = (bool) $order->get_meta( '_subscription_renewal' ); // Skip renewal orders?
            if ( apply_filters( 'lmfwc_tool_generate_past_order_licenses_skip_order', $skip_order, $order ) ) {
                continue;
            }
            $order_ids[] = $order->get_id();
            foreach ( $order->get_items( [ 'line_item' ] ) as $item ) {
                if ( apply_filters( 'lmfwc_tool_generate_past_order_licenses_skip_order_item', false, $item, $order ) ) {
                    continue;
                }
                /* @var \WC_Order_Item_Product $item */
                $productId = $item->get_product_id();
                $quantity  = $item->get_quantity();
                if ( $useProductConf ) {
                    $product = $item->get_product();
                    if ( $product ) {
                        $productGeneratorId = $product->get_meta( 'lmfwc_licensed_product_assigned_generator' );

                        if ( $productGeneratorId ) {
                            $productGenerator = GeneratorResourceRepository::instance()->find( $productGeneratorId );
                            if ( ! is_wp_error( $productGenerator ) ) {
                                $productGenerators[ $productId ] = $productGenerator;
                            }
                        }
                    }
                }

                if ( ! isset( $productGenerators[ $productId ] ) ) {
                    if ( ! is_wp_error( $generator ) ) {
                        $productGenerators[ $productId ] = $generator;
                    }
                }

                if ( isset( $productGenerators[ $productId ] ) ) {
                    $licenses = apply_filters( 'lmfwc_generate_license_keys', $quantity, $generator );

                    if ( ! is_wp_error( $licenses ) ) {

                        $status = apply_filters( 'lmfwc_insert_generated_license_keys',
                            $item->get_order_id(),
                            $productId,
                            $licenses,
                            LicenseStatus::SOLD,
                            $generator
                        );

                        if ( ! is_wp_error( $status ) ) {
                            $order->add_order_note(
                                sprintf(
                                    /* translators: 1: Number of licenses generated, 2: Order item ID, 3: Product ID, 4: Generator ID */
                                    __( 'Generated %1$d license(s) for order item #%2$d (product #%3$d) with generator #%4$d via the "Past Orders License Generator" tool.', 'license-manager-for-woocommerce' ),
                                    count( $licenses ),
                                    $item->get_id(),
                                    $item->get_product_id(),
                                    $productGenerators[ $productId ]->getId()
                                )
                            );
                            
                            $generated ++;
                        }
                    }
                }
            }
        }

        return $order_ids ? $order_ids : new \WP_Error( 'not_generated', __( 'No licenses generated for this page.', 'license-manager-for-woocommerce' ) );

    }

    /**
     * Return the next step
     *
     * @param $step
     * @param $page
     *
     * @return array|\WP_Error
     */
    public function getNextPage( $page ) {


        $page = is_null( $page ) ? 1 : (int) $page;

        $total_pages = (int) $this->getPagesCount();
        
        $data = [
            'next_page' => 0,
            'message'   => '',
            'total'     => $total_pages,
            'current'   => 0,
            'percent'   => 0,
        ];


        if ( ! $total_pages ) {
            return new \WP_Error( '500', __( 'All past order licenses have been generated', 'license-manager-for-woocommerce' ) );
        }

        $next_page   = $page + 1;

        if ( $page === $total_pages ) {
            $data['next_page'] = $next_page <= $total_pages ? $nextpage : -1;
            $data['message'] = sprintf(
                /* translators: 1: Current page number, 2: Total number of pages */
                __( 'Operation Complete (%1$d/%2$d)', 'digital-license-manager' ),
                $page,
                $total_pages
            );
            delete_transient('max_order_pages');

        } else if ( $page < $total_pages ) {
            $data['next_page'] = $next_page;
            $data['message'] = sprintf(
                /* translators: 1: Current page number, 2: Total number of pages */
                __( 'Processing (%1$d/%2$d)', 'digital-license-manager' ),
                $page,
                $total_pages
            );
            
        } else {
            $data['next_page'] = - 1;
            $data['message']   = __( 'Operation complete.', 'digital-license-manager' );
            delete_transient('max_order_pages');
        }
        $current = 0;
        $current += $page;
        $data['percent'] = $current > 0 && $total_pages > 0 ? round( $page / $total_pages * 100, 2 ) : 0;

        return $data;

    }
    
    /**
     * Returns the orders query
     * @return mixed|null
     */
    private function getOrdersQuery() {
        return apply_filters( 'lmfwc_tool_generate_past_order_licenses_query', [
            'paginate'     => true,
            'status'       => array( 'wc-processing', 'wc-completed' ),
            'limit'        => 10,
            'meta_key'     => 'lmfwc_order_complete',
            'meta_compare' => 'NOT EXISTS',
        ] );
    }

    /**
     * Returns the count of the records
     * @return int
     */
    private function getPagesCount() {

        $query  = array_merge( $this->getOrdersQuery(), [
            'page'   => 1,
            'format' => 'ids'
        ] );

        $orders = wc_get_orders( $query );
        $max_order_pages = isset( $orders->max_num_pages ) ? (int) $orders->max_num_pages : 0;
        $max_order_pages = !empty(get_transient( 'max_order_pages' )) ? get_transient( 'max_order_pages' ) : $max_order_pages;
        set_transient( 'max_order_pages', $max_order_pages, apply_filters( 'lmfwc_tool_data_expiration', 48 * HOUR_IN_SECONDS, $this ) );
        return $max_order_pages;
    }

    /**
     * Helper function to get a setting by name.
     *
     * @param string $field
     * @param string $section
     *
     * @return bool|mixed
     */
    public static function get($field, $section = self::SECTION_GENERAL)
    {
        $settings = get_option($section, array());
        $value    = false;

        if (!$settings) {
            $settings = array();
        }

        if (array_key_exists($field, $settings)) {
            $value = $settings[$field];
        }
        
        return $value;
    }
}