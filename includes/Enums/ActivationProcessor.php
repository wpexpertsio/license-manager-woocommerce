<?php


namespace LicenseManagerForWooCommerce\Enums;

/**
 * Class ActivationProcessor
 * @package LicenseManagerForWooCommerce\Enums
 */
abstract class ActivationProcessor {

	/**
     * Enumerator value used for Web.
     *
     * @var int
     */
	const WEB = 1;

	/**
     * Enumerator value used for Api.
     *
     * @var int
     */
	const API = 2;

	/**
     * Enumerator value used for Migration.
     *
     * @var int
     */
	const MIGRATION = 3;

	/**
     * Available enumerator values.
     *
     * @var array
     */
    public static $sources = array(
        self::WEB,
        self::API,
        self::MIGRATION
    );

	/**
     * Returns the string representation of a specific enumerator value.
     *
     * @param int $source Source enumerator value
     *
     * @return string
     */
    public static function getLabel($source)
    {
        $labels = array(
            self::WEB    	=> __('Web', 'license-manager-for-woocommerce'),
            self::API       => __('API', 'license-manager-for-woocommerce'),
            self::MIGRATION => __('Migration', 'license-manager-for-woocommerce'),
        );
        if( !in_array($source, self::$sources) ) {
        	$labels[$source] = __('Other', 'license-manager-for-woocommerce');
        }
        return $labels[$source];
    }

	/**
	 * Returns all sources formatted
	 * @return array
	 */
	public static function getAllSources() {
		$activationSources = array();
		foreach ( self::$sources as $source ) {
			$activationSources[ $source ] = self::getLabel( $source );
		}

		return $activationSources;
	}

}
