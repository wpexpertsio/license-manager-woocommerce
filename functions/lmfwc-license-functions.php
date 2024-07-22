<?php
/**
 * LicenseManager for WooCommerce - License functions
 *
 * Functions for license key manipulation.
 */

use LicenseManagerForWooCommerce\Enums\LicenseSource;
use LicenseManagerForWooCommerce\Enums\LicenseStatus as LicenseStatusEnum;
use LicenseManagerForWooCommerce\Models\Resources\License as LicenseResourceModel;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\LicenseActivations as ActivationResourceRepository;
use LicenseManagerForWooCommerce\Enums\ActivationProcessor;

defined('ABSPATH') || exit;

/**
 * Adds a new license to the database.
 *
 * @param string $licenseKey  The license key being added
 * @param array  $licenseData Key/value pairs with the license table column names as keys
 *
 * @return bool|LicenseResourceModel
 * @throws Exception
 */
function lmfwc_add_license($licenseKey, $licenseData = array())
{
    $status            = LicenseStatusEnum::INACTIVE;
    $orderId           = null;
    $productId         = null;
    $userId            = null;
    $expiresAt         = null;
    $validFor          = null;
    $timesActivatedMax = null;

    if (array_key_exists('status', $licenseData)) {
        $status = $licenseData['status'];
    }

    if (array_key_exists('order_id', $licenseData)) {
        $orderId = $licenseData['order_id'];
    }

    if (array_key_exists('product_id', $licenseData)) {
        $productId = $licenseData['product_id'];
    }

    if (array_key_exists('user_id', $licenseData)) {
        $userId = $licenseData['user_id'];
    }

    if (array_key_exists('expires_at', $licenseData)) {
        $expiresAt = $licenseData['expires_at'];
    }

    if (array_key_exists('valid_for', $licenseData)) {
        $validFor = $licenseData['valid_for'];
    }

    if (array_key_exists('times_activated_max', $licenseData)) {
        $timesActivatedMax = $licenseData['times_activated_max'];
    }

    if (!in_array($status, LicenseStatusEnum::$status)) {
        throw new Exception('\'status\' array key not valid. Possible values are: 1 for SOLD, 2 for DELIVERED,
            3 for ACTIVE, and 4 for INACTIVE.');
    }

    if (apply_filters('lmfwc_duplicate', $licenseKey)) {
        throw new Exception(esc_html("The license key '{$licenseKey}' already exists."));
    }

    if ($expiresAt !== null) {
        new DateTime($expiresAt);
    }

    $encryptedLicenseKey = apply_filters('lmfwc_encrypt', $licenseKey);
    $hashedLicenseKey    = apply_filters('lmfwc_hash', $licenseKey);

    $queryData = array(
        'order_id'            => $orderId,
        'product_id'          => $productId,
        'user_id'             => $userId,
        'license_key'         => $encryptedLicenseKey,
        'hash'                => $hashedLicenseKey,
        'expires_at'          => $expiresAt,
        'valid_for'           => $validFor,
        'source'              => LicenseSource::IMPORT,
        'status'              => $status,
        'times_activated_max' => $timesActivatedMax
    );

    /** @var LicenseResourceModel $license */
    $license = LicenseResourceRepository::instance()->insert($queryData);

    if (!$license) {
        return false;
    }

    // Update the stock
    if ($license->getProductId() !== null && $license->getStatus() === LicenseStatusEnum::ACTIVE) {
        apply_filters('lmfwc_stock_increase', $license->getProductId());
    }

    return $license;
}

/**
 * Retrieves a single license from the database.
 *
 * @param string $licenseKey The license key to be deleted.
 *
 * @return bool|LicenseResourceModel
 * @throws Exception
 */
function lmfwc_get_license($licenseKey)
{
    /** @var LicenseResourceModel $license */
    $license = LicenseResourceRepository::instance()->findBy(
        array(
            'hash' => apply_filters('lmfwc_hash', $licenseKey)
        )
    );

    if (!$license) {
        return false;
    }

    return $license;
}

/**
 * Retrieves multiple license keys by a query array.
 *
 * @param array $query Key/value pairs with the license table column names as keys
 *
 * @return bool|LicenseResourceModel[]
 */
function lmfwc_get_licenses($query)
{
    if (array_key_exists('license_key', $query)) {
        $query['hash'] = apply_filters('lmfwc_hash', $query['license_key']);
        unset($query['license_key']);
    }

    return LicenseResourceRepository::instance()->findAllBy($query);
}

/**
 * Updates the specified license.
 *
 * @param string $licenseKey  The license key being updated.
 * @param array  $licenseData Key/value pairs of the updated data.
 *
 * @return bool|LicenseResourceModel
 * @throws Exception
 */
function lmfwc_update_license($licenseKey, $licenseData)
{
    $updateData = array();

    /** @var LicenseResourceModel $oldLicense */
    $oldLicense = LicenseResourceRepository::instance()->findBy(
        array(
            'hash' => apply_filters('lmfwc_hash', $licenseKey)
        )
    );

    if (!$oldLicense) {
        return false;
    }

    // Order ID
    if (array_key_exists('order_id', $licenseData)) {
        if ($licenseData['order_id'] === null) {
            $updateData['order_id'] = null;
        } else {
            $updateData['order_id'] = intval($licenseData['order_id']);
        }
    }

    // Product ID
    if (array_key_exists('product_id', $licenseData)) {
        if ($licenseData['product_id'] === null) {
            $updateData['product_id'] = null;
        } else {
            $updateData['product_id'] = intval($licenseData['product_id']);
        }
    }

    // User ID
    if (array_key_exists('user_id', $licenseData)) {
        if ($licenseData['user_id'] === null) {
            $updateData['user_id'] = null;
        } else {
            $updateData['user_id'] = intval($licenseData['user_id']);
        }
    }

    // License key
    if (array_key_exists('license_key', $licenseData)) {
        // Check for possible duplicates
        if (apply_filters('lmfwc_duplicate', $licenseData['license_key'], $oldLicense->getId())) {
            throw new Exception(esc_html("The license key '{$licenseData['license_key']}' already exists."));
        }

        $updateData['license_key'] = apply_filters('lmfwc_encrypt', $licenseData['license_key']);
        $updateData['hash']        = apply_filters('lmfwc_hash', $licenseData['license_key']);
    }

    // Expires at
    if (array_key_exists('expires_at', $licenseData)) {
        if ($licenseData['expires_at'] !== null) {
            new DateTime($licenseData['expires_at']);
        }

        $updateData['expires_at'] = $licenseData['expires_at'];
    }

    // Valid for
    if (array_key_exists('valid_for', $licenseData)) {
        if ($licenseData['valid_for'] === null) {
            $updateData['valid_for'] = null;
        } else {
            $updateData['valid_for'] = intval($licenseData['valid_for']);
        }
    }

    // Status
    if (array_key_exists('status', $licenseData)) {
        if (!in_array(intval($licenseData['status']), LicenseStatusEnum::$status)) {
            throw new Exception('The \'status\' array key not valid. Possible values are: 1 for SOLD, 2 for
                DELIVERED, 3 for ACTIVE, and 4 for INACTIVE.');
        }

        $updateData['status'] = intval($licenseData['status']);
    }

    // Times activated
    if (array_key_exists('times_activated', $licenseData)) {
        if ($licenseData['times_activated'] === null) {
            $updateData['times_activated'] = null;
        } else {
            $updateData['times_activated'] = intval($licenseData['times_activated']);
        }
    }

    // Times activated max
    if (array_key_exists('times_activated_max', $licenseData)) {
        if ($licenseData['times_activated_max'] === null) {
            $updateData['times_activated_max'] = null;
        } else {
            $updateData['times_activated_max'] = intval($licenseData['times_activated_max']);
        }
    }

    // Update the stock
    if ($oldLicense->getProductId() !== null && $oldLicense->getStatus() === LicenseStatusEnum::ACTIVE) {
        apply_filters('lmfwc_stock_decrease', $oldLicense->getProductId());
    }

    /** @var LicenseResourceModel $license */
    $license = LicenseResourceRepository::instance()->updateBy(
        array(
            'hash' => $oldLicense->getHash()
        ),
        $updateData
    );

    if (!$license) {
        return false;
    }

    $newLicenseHash = apply_filters('lmfwc_hash', $licenseKey);

    if (array_key_exists('hash', $updateData)) {
        $newLicenseHash = $updateData['hash'];
    }

    /** @var LicenseResourceModel $newLicense */
    $newLicense = LicenseResourceRepository::instance()->findBy(
        array(
            'hash' => $newLicenseHash
        )
    );

    if (!$newLicense) {
        return false;
    }

    // Update the stock
    if ($newLicense->getProductId() !== null && $newLicense->getStatus() === LicenseStatusEnum::ACTIVE) {
        apply_filters('lmfwc_stock_increase', $newLicense->getProductId());
    }

    return $newLicense;
}

/**
 * Deletes the specified license.
 *
 * @param string $licenseKey The license key to be deleted.
 *
 * @return bool
 * @throws Exception
 */
function lmfwc_delete_license($licenseKey)
{
    /** @var LicenseResourceModel $oldLicense */
    $oldLicense = LicenseResourceRepository::instance()->findBy(
        array(
            'hash' => apply_filters('lmfwc_hash', $licenseKey)
        )
    );

    // Update the stock
    if ($oldLicense
        && $oldLicense->getProductId() !== null
        && $oldLicense->getStatus() === LicenseStatusEnum::ACTIVE
    ) {
        apply_filters('lmfwc_stock_decrease', $oldLicense->getProductId());
    }

    /** @var LicenseResourceModel $license */
    $license = LicenseResourceRepository::instance()->deleteBy(
        array(
            'hash' => apply_filters('lmfwc_hash', $licenseKey)
        )
    );

    if (!$license) {
        return false;
    }

    return true;
}

/**
 * Increments the "times_activated" column, if "times_activated_max" allows it.
 *
 * @param string $licenseKey The license key to be activated.
 *
 * @return bool|LicenseResourceModel
 * @throws Exception
 */
function lmfwc_activate_license($licenseKey, $args)
{

   try {
        /** @var LicenseResourceModel $license */
        $license = LicenseResourceRepository::instance()->findBy(
            array(
                'hash' => apply_filters('lmfwc_hash', $licenseKey)
            )
        );
    } catch (Exception $e) {
        return new WP_Error(
            'lmfwc_rest_data_error',
            $e->getMessage(),
            array('status' => 404)
        );
    }

    if (!$license) {
        return new WP_Error(
            'lmfwc_rest_data_error',
            sprintf(
                'License Key: %s could not be found.',
                $licenseKey
            ),
            array('status' => 404)
        );
    }

    if ($expiresAt = $license->getExpiresAt()) {
        try {
            $dateExpiresAt = new DateTime($expiresAt);
            $dateNow = new DateTime('now', new DateTimeZone('UTC'));
        } catch (Exception $e) {
            return new WP_Error('lmfwc_rest_license_expired', $e->getMessage());
        }

        if ($dateNow > $dateExpiresAt) {
            return new WP_Error(
                'lmfwc_rest_license_expired',
                sprintf('The license Key expired on %s (UTC).', $license->getExpiresAt()),
                array('status' => 404)
            );
        }
    }

    $timesActivated    = null;
    $timesActivatedMax = null;

    if ($license->getTimesActivated() !== null) {
        $timesActivated = absint($license->getTimesActivated());
    }

    if ($license->getTimesActivatedMax() !== null) {
        $timesActivatedMax = absint($license->getTimesActivatedMax());
    }

    if ($timesActivatedMax && ($timesActivated >= $timesActivatedMax)) {
        return new WP_Error(
            'lmfwc_rest_data_error',
            sprintf(
                'License Key: %s reached maximum activation count.',
                $licenseKey
            ),
            array('status' => 404)
        );
    }

    if ( isset($args['token']) && !empty($args['token']) ) {
        $licenseData = lmfwc_reactivate_license($args['token']);
        if ( is_wp_error($licenseData) ) {
            return $licenseData;
        }
        return $licenseData;
    }
    try {
        if (!$timesActivated) {
            $timesActivatedNew = 1;
        }

        else {
            $timesActivatedNew = intval($timesActivated) + 1;
        }
        
        $newToken = lmfwc_generateToken( $licenseKey );
        if ( is_null( $newToken ) ) {
            return new WP_Error( 'data_error', sprintf( 'Unable to generate activation token hash for license: %s', $licenseKey ), array( 'status' => 404 ) );
        }

        /* @var LicenseActivation $licenseActivation */
        $activationParams = array(
            'license_id' => $license->getId(),
            'token'      => $newToken,
            'source'     => isset($args['source']) ? $args['source'] : ActivationProcessor::API,
            'ip_address' => lmfwc_clientIp(),
            'user_agent' => lmfwc_userAgent()
        );

                // Set label
        if (isset($args['label']) && ! empty( $args['label'] ) ) {
            $activationParams['label'] = $args['label'];
        }

                // Set metadata
        if (isset($args['meta_data']) && is_array( $args['meta_data'] ) ) {
            $activationParams['meta_data'] = $args['meta_data'];
        }

                // Store.
        $licenseActivation = ActivationResourceRepository::instance()->insert( $activationParams );

        if ( ! $licenseActivation ) {
            return new WP_Error( 'server_error', __( 'Unable to activate key', 'license-manager-for-woocommerce' ), array( 'status' => 500 ) );
        }
        /** @var LicenseResourceModel $updatedLicense */
        $updatedLicense = LicenseResourceRepository::instance()->update(
            $license->getId(),
            array(
                'times_activated' => $timesActivatedNew
            )
        );
    } catch (Exception $e) {
        return new WP_Error(
            'lmfwc_rest_data_error',
            $e->getMessage(),
            array('status' => 404)
        );
    }

    $licenseData = $updatedLicense->toArray();
    $licenseData['activationData'] = $licenseActivation->toArray();
            // Remove the hash and decrypt the license key
    unset($licenseData['hash']);
    $licenseData['licenseKey'] = $updatedLicense->getDecryptedLicenseKey();
    return $licenseData;
    }

    /**
     * Decrements the "times_activated" column, if possible.
     *
     * @param string $licenseKey The license key to be deactivated.
     *
     * @return bool|LicenseResourceModel
     * @throws Exception
     */
     function lmfwc_deactivate_license($licenseKey, $args)
    {
        if ( isset($args['token']) && !empty($args['token']) ) :
            try {
                /** @var LicenseResourceModel $license */
                $activation = ActivationResourceRepository::instance()->findBy(
                    array(
                        'token' => $args['token'],
                        'deactivated_at' => null
                    )
                );

            
            } catch (Exception $e) {
                return new WP_Error(
                    'lmfwc_rest_data_error',
                    $e->getMessage(),
                    array('status' => 404)
                );
            }

            if ( ! $activation ) {
                return new WP_Error(
                    'lmfwc_rest_data_error',
                    sprintf(
                        'Activation Token: %s could not be found or is deactivated.',
                        $args['token']
                    ),
                    array('status' => 404)
                );
            }

            $license = $activation->getLicense();
            $timesActivated = null;

            if ($license->getTimesActivated() !== null) {
                $timesActivated = absint($license->getTimesActivated());
            }

            if (!$timesActivated || $timesActivated == 0) {
                return new WP_Error(
                    'lmfwc_rest_data_error',
                    sprintf(
                        'License Key: %s has not been activated yet.',
                        $licenseKey
                    ),
                    array('status' => 404)
                );
            }

            try {
                $timesActivatedNew = intval($timesActivated) - 1;

                /** @var LicenseResourceModel $updatedLicense */
                $updatedActivation = ActivationResourceRepository::instance()->update(
                    $activation->getId(),
                    array(
                        'deactivated_at' =>  gmdate( 'Y-m-d H:i:s' )
                    )
                );
                $updatedLicense = LicenseResourceRepository::instance()->update(
                    $license->getId(),
                    array(
                        'times_activated' => $timesActivatedNew
                    )
                );
            } catch (Exception $e) {
                return new WP_Error(
                    'lmfwc_rest_data_error',
                    $e->getMessage(),
                    array('status' => 404)
                );
            }

            $licenseData = $updatedLicense->toArray();
            $licenseData['activationData'] = $updatedActivation->toArray();
            // Remove the hash and decrypt the license key
            unset($licenseData['hash']);
            $licenseData['licenseKey'] = $updatedLicense->getDecryptedLicenseKey();
        elseif ( isset($licenseKey) && !empty($licenseKey) ) :
            try {
                /** @var LicenseResourceModel $license */
                $license = LicenseResourceRepository::instance()->findBy(
                    array(
                        'hash' => apply_filters('lmfwc_hash', $licenseKey)
                    )
                );

            
            } catch (Exception $e) {
                return new WP_Error(
                    'lmfwc_rest_data_error',
                    $e->getMessage(),
                    array('status' => 404)
                );
            }

            if ( ! $license ) {
                return new WP_Error(
                    'lmfwc_rest_data_error',
                    sprintf(
                        'License Key: %s could not be found or is deactivated.',
                        $licenseKey
                    ),
                    array('status' => 404)
                );
            }

            $timesActivated = null;

            if ($license->getTimesActivated() !== null) {
                $timesActivated = absint($license->getTimesActivated());
            }

            if (!$timesActivated || $timesActivated == 0) {
                return new WP_Error(
                    'lmfwc_rest_data_error',
                    sprintf(
                        'License Key: %s has not been activated yet.',
                        $licenseKey
                    ),
                    array('status' => 404)
                );
            }

            // Deactivate the license key
            try {
                $timesActivatedNew = 0;
                $activations = ActivationResourceRepository::instance()->findAllBy(
                    array(
                        'license_id' => $license->getId()
                    )
                );
                   
                foreach ( $activations as $activation ) {

                    /** @var LicenseResourceModel $updatedLicense */
                    $updatedActivation = ActivationResourceRepository::instance()->update(
                        $activation->getId(),
                        array(
                            'deactivated_at' =>  gmdate( 'Y-m-d H:i:s' )
                        )
                    );
                    
                    $updatedActivations[] = $updatedActivation->toArray();
                }
                $updatedLicense = LicenseResourceRepository::instance()->update(
                    $license->getId(),
                    array(
                        'times_activated' => $timesActivatedNew
                    )
                );
            } catch (Exception $e) {
                return new WP_Error(
                    'lmfwc_rest_data_error',
                    $e->getMessage(),
                    array('status' => 404)
                );
            }

            $licenseData = $updatedLicense->toArray();
            $licenseData['activationData'] = $updatedActivations;
            // Remove the hash and decrypt the license key
            unset($licenseData['hash']);
            $licenseData['licenseKey'] = $updatedLicense->getDecryptedLicenseKey();
        endif;
        return $licenseData;
    }


    function lmfwc_delete_activation($activation_id, $license_id)
    {

        $deleteActivation = ActivationResourceRepository::instance()->deleteBy(
            array(
                'id' => $activation_id
            )
        );

        $count = ActivationResourceRepository::instance()->countBy( 
            array(
                'license_id' => $license_id,
                'deactivated_at' => null
            )
        );
        $updatedLicense = LicenseResourceRepository::instance()->update(
            $license_id,
            array(
                'times_activated' => $count
            )
        );
        return $updatedLicense;
    }

 /**
     * Decrements the "times_activated" column, if possible.
     *
     * @param string $licenseKey The license key to be deactivated.
     *
     * @return bool|LicenseResourceModel
     * @throws Exception
     */
 function lmfwc_reactivate_license($activation_token)
 {

    try {
            /** @var LicenseResourceModel $license */
            $activation = ActivationResourceRepository::instance()->findBy(
                array(
                    'token' => $activation_token
                )
            );

        
        } catch (Exception $e) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }
        $license = $activation->getLicense();

         if ($expiresAt = $license->getExpiresAt()) {
        try {
            $dateExpiresAt = new DateTime($expiresAt);
            $dateNow = new DateTime('now', new DateTimeZone('UTC'));
        } catch (Exception $e) {
            return new WP_Error('lmfwc_rest_license_expired', $e->getMessage());
        }

            if ($dateNow > $dateExpiresAt) {
                
                return new WP_Error(
                    'lmfwc_rest_license_expired',
                    sprintf('The license Key expired on %s (UTC).', $license->getExpiresAt()),
                    array('status' => 404)
                );
            }
         }



        if ( ! $activation ) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                sprintf(
                    'Activation Token: %s could not be found.',
                    $activation_token
                ),
                array('status' => 404)
            );
        }
     
        if ( ! $activation->getDeactivatedAt() ) {
            
            return new WP_Error(
                'lmfwc_rest_data_error',
                sprintf(
                    'Activation Token: %s is already activated.',
                    $activation_token
                ),
                array('status' => 404)
            );
        }

       
        $license = $activation->getLicense();
        $timesActivated = null;

        // if ($license->getTimesActivated() !== null) {
        //     $timesActivated = absint($license->getTimesActivated());
        // }

        // if (!$timesActivated || $timesActivated == 0) {
        //     return new WP_Error(
        //         'lmfwc_rest_data_error',
        //         sprintf(
        //             'License Key: %s has not been activated yet.',
        //             $licenseKey
        //         ),
        //         array('status' => 404)
        //     );
        // }

        // Deactivate the license key
        try {
            $timesActivatedNew = intval($timesActivated) + 1;

            /** @var LicenseResourceModel $updatedLicense */
            $updatedActivation = ActivationResourceRepository::instance()->update(
                $activation->getId(),
                array(
                    'deactivated_at' =>  null
                )
            );
            $updatedLicense = LicenseResourceRepository::instance()->update(
                $license->getId(),
                array(
                    'times_activated' => $timesActivatedNew
                )
            );
        } catch (Exception $e) {
            return new WP_Error(
                'lmfwc_rest_data_error',
                $e->getMessage(),
                array('status' => 404)
            );
        }

        $licenseData = $updatedLicense->toArray();
        $licenseData['activationData'] = $updatedActivation->toArray();
        // Remove the hash and decrypt the license key
        unset($licenseData['hash']);
        $licenseData['licenseKey'] = $updatedLicense->getDecryptedLicenseKey();
        return $licenseData;
}


    /**
     * Generates activation token
     *
     * @param $licenseKey
     *
     * @return string|null
     */
    function lmfwc_generateToken( $licenseKey ) {
        $token    = apply_filters('lmfwc_activation_hash',  $licenseKey );
        $reps     = 0;
        $max_reps = 20;
        while ( true ) {
            if ( (int) ActivationResourceRepository::instance()->countBy( [ 'token' => $token ] ) === 0 ) {
                break;
            } else if ( $reps > $max_reps ) {
                $token = null; // Do not enter in infinite loop.
                break;
            } else {
                $token = apply_filters('lmfwc_activation_hash',  $licenseKey );
            }
            $reps ++;
        }

        return $token;
    }

    function lmfwc_clientIp() {

        $addr = null;
        if ( getenv( 'HTTP_CLIENT_IP' ) ) {
            $addr = getenv( 'HTTP_CLIENT_IP' );
        } else if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
            $addr = getenv( 'HTTP_X_FORWARDED_FOR' );
        } else if ( getenv( 'HTTP_X_FORWARDED' ) ) {
            $addr = getenv( 'HTTP_X_FORWARDED' );
        } else if ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
            $addr = getenv( 'HTTP_FORWARDED_FOR' );
        } else if ( getenv( 'HTTP_FORWARDED' ) ) {
            $addr = getenv( 'HTTP_FORWARDED' );
        } else if ( getenv( 'REMOTE_ADDR' ) ) {
            $addr = getenv( 'REMOTE_ADDR' );
        }

        return $addr;

    }

    /**
     * Return the client user agent
     */
    function lmfwc_userAgent() {
        return isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : null;
    }