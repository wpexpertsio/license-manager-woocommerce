<?php
/**
 * Some of the code written, maintained by Darko Gjorgjijoski
 */
namespace LicenseManagerForWooCommerce\Repositories\Resources;

use LicenseManagerForWooCommerce\Abstracts\ResourceRepository as AbstractResourceRepository;
use LicenseManagerForWooCommerce\Enums\ColumnType as ColumnTypeEnum;
use LicenseManagerForWooCommerce\Interfaces\ResourceRepository as ResourceRepositoryInterface;
use LicenseManagerForWooCommerce\Models\Resources\LicenseActivation as ActivationResourceModel;



defined('ABSPATH') || exit;

class LicenseActivations extends AbstractResourceRepository implements ResourceRepositoryInterface {

	  /**
     * @var string
     */
    const TABLE = 'lmfwc_activations';

	/**
     * Country constructor.
     */
    public function __construct()
    {
        global $wpdb;
		$this->table      = $wpdb->prefix . self::TABLE;
		$this->primaryKey = 'id';
		$this->model  = ActivationResourceModel::class;
		$this->mapping    = array(
            'token'            => ColumnTypeEnum::LONGTEXT,
            'license_id'       => ColumnTypeEnum::BIGINT,
            'label'            => ColumnTypeEnum::VARCHAR,
            'source'           => ColumnTypeEnum::VARCHAR,
            'ip_address'       => ColumnTypeEnum::VARCHAR,
            'user_agent'       => ColumnTypeEnum::TEXT,
            'meta_data'        => ColumnTypeEnum::SERIALIZED,
            'source'           => ColumnTypeEnum::TINYINT,
            'created_at'       => ColumnTypeEnum::DATETIME,
            'updated_at'       => ColumnTypeEnum::DATETIME,
            'deactivated_at'   => ColumnTypeEnum::DATETIME
        );
        $this->useCreatedBy = false;
        $this->useUpdatedBy = false;
	}

}