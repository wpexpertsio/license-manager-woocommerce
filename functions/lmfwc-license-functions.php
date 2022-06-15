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
        $productId = $licenseData['user_id'];
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
        throw new Exception("The license key '{$licenseKey}' already exists.");
    }

    if ($expiresAt !== null) {
        new DateTime($expiresAt);
    }

    $encryptedLicenseKey = apply_filters('lmfwc_encrypt', $licenseKey);
    $hashedLicenseKey    = apply_filters('lmfwc_hash', $licenseKey);

    $queryData = array(
        'order_id'            => $orderId,
        'product_id'          => $productId,
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
            throw new Exception("The license key '{$licenseData['license_key']}' already exists.");
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
function lmfwc_activate_license($licenseKey)
{
    $license = LicenseResourceRepository::instance()->findBy(
        array(
            'hash' => apply_filters('lmfwc_hash', $licenseKey)
        )
    );

    if (!$license) {
        return false;
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
        throw new Exception("License Key: {$licenseKey} reached maximum activation count.");
    }

    if (!$timesActivated) {
        $timesActivatedNew = 1;
    } else {
        $timesActivatedNew = intval($timesActivated) + 1;
    }

    /** @var LicenseResourceModel $updatedLicense */
    $updatedLicense = LicenseResourceRepository::instance()->update(
        $license->getId(),
        array(
            'times_activated' => $timesActivatedNew
        )
    );

    if (!$updatedLicense) {
        return false;
    }

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
function lmfwc_deactivate_license($licenseKey)
{
    $license = LicenseResourceRepository::instance()->findBy(
        array(
            'hash' => apply_filters('lmfwc_hash', $licenseKey)
        )
    );

    if (!$license) {
        return false;
    }

    $timesActivated = null;

    if ($license->getTimesActivated() !== null) {
        $timesActivated = absint($license->getTimesActivated());
    }

    if (!$timesActivated || $timesActivated === 0) {
        throw new Exception("License Key: {$licenseKey} has not been activated yet.");
    }

    $timesActivatedNew = intval($timesActivated) - 1;

    /** @var LicenseResourceModel $updatedLicense */
    $updatedLicense = LicenseResourceRepository::instance()->update(
        $license->getId(),
        array(
            'times_activated' => $timesActivatedNew
        )
    );

    if (!$updatedLicense) {
        return false;
    }

    return $updatedLicense;
}
