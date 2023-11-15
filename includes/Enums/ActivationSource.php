<?php


namespace LicenseManagerForWooCommerce\Enums;

/**
 * Class ActivationSource
 * @package LicenseManagerForWooCommerce\Enums
 */
abstract class ActivationSource {

	/**
	 * Enumerator value
	 *
	 * @var int
	 */
	const WEB = 1;

	/**
	 * Enumerator value
	 *
	 * @var int
	 */
	const API = 2;

	/**
	 * Enumerator value
	 */
	const MIGRATION = 3;


	/**
	 * Format source
	 *
	 * @param int $src
	 *
	 * @return string
	 */
	public static function format( $src ) {
		$src = (int) $src;
		if ( $src === self::WEB ) {
			$str = __( 'Web', 'license-manager-for-woocommerce' );
		} else if ( $src === self::API ) {
			$str = __( 'API', 'license-manager-for-woocommerce' );
		} else if ( $src === self::MIGRATION ) {
			$str = __( 'Migration', 'license-manager-for-woocommerce' );
		} else {
			$str = __( 'Other', 'license-manager-for-woocommerce' );
		}

		return $str;
	}

	/**
	 * Returns all sources formatted
	 * @return array
	 */
	public static function all() {
		$sources = array();
		foreach ( array( self::WEB, self::API ) as $source ) {
			$sources[ $source ] = self::format( $source );
		}

		return $sources;
	}

}
