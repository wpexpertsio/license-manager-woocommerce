<?php

namespace LicenseManagerForWooCommerce\Abstracts;

defined('ABSPATH') || exit;

abstract class ResourceModel
{
    /**
     * Returns the class properties as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}