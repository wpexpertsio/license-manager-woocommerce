<?php

namespace LicenseManagerForWooCommerce\Models\Resources;

use LicenseManagerForWooCommerce\Abstracts\ResourceModel as AbstractResourceModel;
use LicenseManagerForWooCommerce\Interfaces\Model as ModelInterface;
use stdClass;

defined('ABSPATH') || exit;

class ApiKey extends AbstractResourceModel implements ModelInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $permissions;

    /**
     * @var string
     */
    protected $consumerKey;

    /**
     * @var string
     */
    protected $consumerSecret;

    /**
     * @var string
     */
    protected $nonces;

    /**
     * @var string
     */
    protected $truncatedKey;

    /**
     * @var string
     */
    protected $lastAccess;

    /**
     * ApiKey constructor.
     *
     * @param stdClass|null $apiKey
     */
    public function __construct($apiKey = null)
    {
        if (!$apiKey instanceof stdClass) {
            return;
        }

        $this->id             = $apiKey->id;
        $this->userId         = $apiKey->user_id;
        $this->description    = $apiKey->description;
        $this->permissions    = $apiKey->permissions;
        $this->consumerKey    = $apiKey->consumer_key;
        $this->consumerSecret = $apiKey->consumer_secret;
        $this->nonces         = $apiKey->nonces;
        $this->truncatedKey   = $apiKey->truncated_key;
        $this->lastAccess     = $apiKey->last_access;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param string $permissions
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @return string
     */
    public function getConsumerKey()
    {
        return $this->consumerKey;
    }

    /**
     * @param string $consumerKey
     */
    public function setConsumerKey($consumerKey)
    {
        $this->consumerKey = $consumerKey;
    }

    /**
     * @return string
     */
    public function getConsumerSecret()
    {
        return $this->consumerSecret;
    }

    /**
     * @param string $consumerSecret
     */
    public function setConsumerSecret($consumerSecret)
    {
        $this->consumerSecret = $consumerSecret;
    }

    /**
     * @return string
     */
    public function getNonces()
    {
        return $this->nonces;
    }

    /**
     * @param string $nonces
     */
    public function setNonces($nonces)
    {
        $this->nonces = $nonces;
    }

    /**
     * @return string
     */
    public function getTruncatedKey()
    {
        return $this->truncatedKey;
    }

    /**
     * @param string $truncatedKey
     */
    public function setTruncatedKey($truncatedKey)
    {
        $this->truncatedKey = $truncatedKey;
    }

    /**
     * @return string
     */
    public function getLastAccess()
    {
        return $this->lastAccess;
    }

    /**
     * @param string $lastAccess
     */
    public function setLastAccess($lastAccess)
    {
        $this->lastAccess = $lastAccess;
    }
}