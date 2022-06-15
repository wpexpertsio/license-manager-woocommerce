<?php

namespace LicenseManagerForWooCommerce\Models\Resources;

use stdClass;

defined('ABSPATH') || exit;

class LicenseMeta
{
    /**
     * @var int
     */
    protected $metaId;

    /**
     * @var int
     */
    protected $licenseId;

    /**
     * @var string
     */
    protected $metaKey;

    /**
     * @var mixed
     */
    protected $metaValue;

    /**
     * License constructor.
     *
     * @param stdClass $licenseMeta
     */
    public function __construct($licenseMeta)
    {
        if (!$licenseMeta instanceof stdClass) {
            return;
        }

        $this->metaId    = intval($licenseMeta->meta_id);
        $this->licenseId = intval($licenseMeta->license_id);
        $this->metaKey   = $licenseMeta->meta_key;
        $this->metaValue = maybe_unserialize($licenseMeta->meta_value);
    }

    /**
     * @return int
     */
    public function getMetaId()
    {
        return $this->metaId;
    }

    /**
     * @param int $metaId
     */
    public function setMetaId($metaId)
    {
        $this->metaId = $metaId;
    }

    /**
     * @return int
     */
    public function getLicenseId()
    {
        return $this->licenseId;
    }

    /**
     * @param int $licenseId
     */
    public function setLicenseId($licenseId)
    {
        $this->licenseId = $licenseId;
    }

    /**
     * @return string
     */
    public function getMetaKey()
    {
        return $this->metaKey;
    }

    /**
     * @param string $metaKey
     */
    public function setMetaKey($metaKey)
    {
        $this->metaKey = $metaKey;
    }

    /**
     * @return mixed
     */
    public function getMetaValue()
    {
        return $this->metaValue;
    }

    /**
     * @param mixed $metaValue
     */
    public function setMetaValue($metaValue)
    {
        $this->metaValue = $metaValue;
    }
}