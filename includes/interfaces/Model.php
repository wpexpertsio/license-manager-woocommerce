<?php

namespace LicenseManagerForWooCommerce\Interfaces;

defined('ABSPATH') || exit();

interface Model
{
    /**
     * @return array
     */
    public function toArray();
}