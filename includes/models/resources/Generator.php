<?php

namespace LicenseManagerForWooCommerce\Models\Resources;

use LicenseManagerForWooCommerce\Abstracts\ResourceModel as AbstractResourceModel;
use LicenseManagerForWooCommerce\Interfaces\Model as ModelInterface;
use stdClass;

defined('ABSPATH') || exit;

class Generator extends AbstractResourceModel implements ModelInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $charset;

    /**
     * @var int
     */
    protected $chunks;

    /**
     * @var int
     */
    protected $chunkLength;

    /**
     * @var int
     */
    protected $timesActivatedMax;

    /**
     * @var string
     */
    protected $separator;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $suffix;

    /**
     * @var int
     */
    protected $expiresIn;

    /**
     * @var string
     */
    protected $createdAt;

    /**
     * @var int
     */
    protected $createdBy;

    /**
     * @var string
     */
    protected $updatedAt;

    /**
     * @var int
     */
    protected $updatedBy;

    /**
     * Generator constructor.
     *
     * @param stdClass $generator
     */
    public function __construct($generator)
    {
        if (!$generator instanceof stdClass) {
            return;
        }

        $this->id                = intval($generator->id);
        $this->name              = $generator->name;
        $this->charset           = $generator->charset;
        $this->chunks            = intval($generator->chunks);
        $this->chunkLength       = intval($generator->chunk_length);
        $this->timesActivatedMax = $generator->times_activated_max;
        $this->separator         = $generator->separator;
        $this->prefix            = $generator->prefix;
        $this->suffix            = $generator->suffix;
        $this->expiresIn         = $generator->expires_in;
        $this->createdAt         = $generator->created_at;
        $this->createdBy         = $generator->created_by;
        $this->updatedAt         = $generator->updated_at;
        $this->updatedBy         = $generator->updated_by;
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * @return int
     */
    public function getChunks()
    {
        return $this->chunks;
    }

    /**
     * @param int $chunks
     */
    public function setChunks($chunks)
    {
        $this->chunks = $chunks;
    }

    /**
     * @return int
     */
    public function getChunkLength()
    {
        return $this->chunkLength;
    }

    /**
     * @param int $chunkLength
     */
    public function setChunkLength($chunkLength)
    {
        $this->chunkLength = $chunkLength;
    }

    /**
     * @return int
     */
    public function getTimesActivatedMax()
    {
        return $this->timesActivatedMax;
    }

    /**
     * @param int $timesActivatedMax
     */
    public function setTimesActivatedMax($timesActivatedMax)
    {
        $this->timesActivatedMax = $timesActivatedMax;
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * @param string $separator
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @param string $suffix
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * @return int
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * @param int $expiresIn
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return int
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param int $createdBy
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param string $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return int
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param int $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    }
}

