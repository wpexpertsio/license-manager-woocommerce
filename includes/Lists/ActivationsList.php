<?php
/**
 * Some of the code written, maintained by Darko Gjorgjijoski
 */
namespace LicenseManagerForWooCommerce\Lists;

use DateTime;
use Exception;
use LicenseManagerForWooCommerce\Abstracts\AbstractListTable;
use LicenseManagerForWooCommerce\Models\Resources\LicenseActivation;
use LicenseManagerForWooCommerce\Repositories\Resources\LicenseActivations as ActivationResourceRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use LicenseManagerForWooCommerce\Enums\ActivationProcessor;
use LicenseManagerForWooCommerce\AdminNotice;
use LicenseManagerForWooCommerce\AdminMenus;
use LicenseManagerForWooCommerce\Setup;
use WP_List_Table;

defined( 'ABSPATH' ) || exit;

/**
 * Class Activations
 * @package LicenseManagerForWooCommerce\ListTables
 */
class ActivationsList extends WP_List_Table {
	   /**
     * @var string
     */
    protected $table;
	/**
	 *  Whether user can activate records
	 *
	 * @var bool
	 */
	protected $canActivate;

	/**
	 *  Whether user can deactivate records
	 *
	 * @var bool
	 */
	protected $canDeactivate;

	/**
	 *  Whether user can deactivate records
	 *
	 * @var bool
	 */
	protected $canDelete;

	/**
	 *  Page slug
	 *
	 * @var bool
	 */
	protected $slug;

	/**
	 *  DateFormat
	 *
	 * @var bool
	 */
	protected $dateFormat;

	/**
	 *  TimeFormat
	 *
	 * @var bool
	 */
	protected $timeFormat;

	/**
	 *  gmtOffset
	 *
	 * @var bool
	 */
	protected $gmtOffset;

	/**
	 * ActivationsList constructor.
	 */
	public function __construct() {
		global $wpdb;

		parent::__construct(
			array(
				'singular' => __( 'Activation', 'license-manager-for-woocommerce' ),
				'plural'   => __( 'Activations', 'license-manager-for-woocommerce' ),
				'ajax'     => false
			)
		);

		$this->slug       = AdminMenus::ACTIVATIONS_PAGE;
		$this->table      = $wpdb->prefix . Setup::ACTIVATIONS_TABLE_NAME;
		$this->dateFormat = get_option( 'date_format' );
		$this->timeFormat = get_option( 'time_format' );
		$this->gmtOffset  = get_option( 'gmt_offset' );

		$this->canActivate   = current_user_can( 'lmfwc_activate_licenses' );
		$this->canDeactivate = current_user_can( 'lmfwc_deactivate_licenses' );
		$this->canDelete     = current_user_can( 'lmfwc_delete_activations' );
		
	}

	/**
	 * Retrieves the records from the database.
	 *
	 * @param int $perPage Default amount of records per page
	 * @param int $pageNumber Default page number
	 *
	 * @return array
	 */
	public function getRecords( $perPage = 20, $pageNumber = 1 ) {
		global $wpdb;

		$perPage    = (int) $perPage;
		$pageNumber = (int) $pageNumber;

		$sql = $this->getRecordsQuery();
		$sql .= " LIMIT {$perPage}";
		$sql .= ' OFFSET ' . ( $pageNumber - 1 ) * $perPage;

		$results = $wpdb->get_results( $sql , ARRAY_A );

		return $results;
	}

	/**
	 * Returns records query
	 *
	 * @return string
	 */
	private function getRecordsQuery( $status = '', $count = false ) {

		global $wpdb;
		$tblLicenses = $wpdb->prefix . esc_sql( Setup::LICENSES_TABLE_NAME );


		$what = $count ? 'COUNT(*)' : " {$this->table}.*";
		$sql  = esc_sql( "SELECT {$what} FROM {$this->table} INNER JOIN {$tblLicenses} ON {$tblLicenses}.id={$this->table}.license_id WHERE 1 = 1" );
		
		// Applies the view filter
		if ( ! empty( $status ) || $this->isViewFilterActive() ) {

			if ( empty( $status ) ) {
				$status = isset($_GET['status']) ? sanitize_text_field( $_GET['status'] ) : '';
			}

			if ( 'inactive' === $status ) {
				$sql .= esc_sql( ' AND ' . $this->table . '.deactivated_at IS NOT NULL' );
			} else {
				$sql .= esc_sql( ' AND ' . $this->table . '.deactivated_at IS NULL' );
			}

		}

		// Applies the search box filter
		if ( array_key_exists( 's', $_REQUEST ) && ! empty( $_REQUEST['s'] ) ) {
			$sql .= $wpdb->prepare(
				' AND ( %1s.hash=%s OR %2s.label LIKE %s )', $tblLicenses,
				apply_filters('lmfwc_hash', sanitize_text_field( $_REQUEST['s'] ) ),
				$this->table,
				'%' . $wpdb->esc_like( sanitize_text_field( $_REQUEST['s'] ) ) . '%'
			);
			
			
		}

		// Applies the order filter
		if ( isset( $_REQUEST['license-id'] ) && is_numeric( $_REQUEST['license-id'] ) ) {
			$sql .= $wpdb->prepare( ' AND %1s.id=%d', $tblLicenses, (int) $_REQUEST['license-id'] );
		}

		// Applies the order filter
		if ( isset( $_REQUEST['license-source'] ) && is_numeric( $_REQUEST['license-source'] ) ) {
			$sql .= $wpdb->prepare( ' AND %1s.source=%d', $this->table, (int) $_REQUEST['license-source'] );
		}

		$sql .= isset($_REQUEST['orderby']) && !empty(sanitize_sql_orderby($_REQUEST['orderby'])) ? ' ORDER BY ' . $this->table . '.' . sanitize_sql_orderby($_REQUEST['orderby'] ) : ' ORDER BY ' . $this->table . '.id';
		$sql .= isset($_REQUEST['order']) && !empty(sanitize_sql_orderby($_REQUEST['order']))   ? ' ' . sanitize_sql_orderby($_REQUEST['order']) : sanitize_sql_orderby(' DESC');
		

		return $sql;
	}

	/**
	 * Retrieves the number of records in the database
	 *
	 * @return int
	 */
	private function getRecordsCount( $status = '' ) {

		global $wpdb;
		$sql = $this->getRecordsQuery( $status, true );

		return $wpdb->get_var( $wpdb->prepare('%1s', $sql) );
	}
	/**
	 * Checkbox column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="id[]" value="%s" />', $item['id'] );
	}

	/**
	 * Token column.
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_token( $item ) {
		$html = '';
		if ( $item['token'] ) {
			$html = sprintf( '<span title="%s">%s</span>', __( 'Unique activation token', 'license-manager-for-woocommerce' ), $item['token'] );
		}

		return $html;
	}

	/**
	 * Name column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_label( $item ) {
		$actions = array();

		if ( empty( $item['label'] ) ) {

			$license_id = $item['license_id'];
			$license = LicenseResourceRepository::instance()->findBy( array( 'id' => $license_id ) ) ;
			$title = $license->getDecryptedLicenseKey() .'  '. substr( $item['token'] ,0 ,4 );

		} else {
			$title = esc_attr( $item['label'] );
		}
		$title         = '<strong>' . $title . '</strong>';
		/* translators: %d is the License $id */
		$actions['id'] = sprintf( __( 'ID: %d', 'license-manager-for-woocommerce' ), (int) $item['id'] );

		if ( ! empty( $item['deactivated_at'] ) && $this->canActivate ) {
			$actions['activate'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url(
					sprintf(
						'admin.php?page=%s&action=activate&id=%d&_wpnonce=%s',
						$this->slug,
						(int) $item['id'],
						wp_create_nonce( 'activate' )
					)
				),
				__( 'Activate', 'license-manager-for-woocommerce' )
			);
		} else if ( empty( $item['deactivated_at'] ) && $this->canDeactivate ) {
			$actions['deactivate'] = sprintf(
				'<a href="%s">%s</a>',
				admin_url(
					sprintf(
						'admin.php?page=%s&action=deactivate&id=%d&_wpnonce=%s',
						$this->slug,
						(int) $item['id'],
						wp_create_nonce( 'deactivate' )
					)
				),
				__( 'Deactivate', 'license-manager-for-woocommerce' )
			);
		}

		if ( $this->canDelete ) {
			$actions['delete'] = sprintf(
				'<a href="%s" class="lmfwc-confirm-dialog">%s</a>',
				admin_url(
					sprintf(
						'admin.php?page=%s&action=delete&id=%d&_wpnonce=%s',
						$this->slug,
						(int) $item['id'],
						wp_create_nonce( 'delete' )
					)
				),
				__( 'Delete', 'license-manager-for-woocommerce' )
			);
		}

		return $title . $this->row_actions( $actions );
	}

	/**
	 * License ID column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 */
	public function column_license_id( $item ) {
		$html = '';

		if ( $item['license_id'] ) {
			$html = sprintf(
				'<a href="%s" target="_blank">#%s</a>',
				esc_url( admin_url( sprintf( 'admin.php?page=%s&action=edit&id=%s', AdminMenus::LICENSES_PAGE, $item['license_id'] ) ) ),
				$item['license_id']
			);
		}

		return $html;
	}

	/**
	 * IP Address column.
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_ip_address( $item ) {

		$html = '';
		if ( $item['ip_address'] ) {
			$html = esc_attr( $item['ip_address'] );
		}

		return $html;
	}

	/**
	 * IP Address column.
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_source( $item ) {

		$html = __( 'Other', 'license-manager-for-woocommerce' );
		if ( $item['source'] ) {
			$html = ActivationProcessor::getLabel( (int) $item['source'] );
		}

		return $html;
	}

	/**
	 * IP Address column.
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_status( $item ) {

		$html = '';
		if ( ! empty( $item['deactivated_at'] ) ) {
			$html = sprintf(
				'<div class="lmfwc-status lmfwc-status-inactive"><span class="dashicons dashicons-marker"></span> %s</div>',
				__( 'Inactive', 'license-manager-for-woocommerce' )
			);
		} else {
			$html = sprintf(
				'<div class="lmfwc-status lmfwc-status-delivered"><span class="dashicons dashicons-marker"></span> %s</div>',
				__( 'Active', 'license-manager-for-woocommerce' )
			);
		}

		return $html;
	}

	/**
	 * Created column.
	 *
	 * @param array $item Associative array of column name and value pairs
	 *
	 * @return string
	 * @throws Exception
	 */
	public function column_created( $item ) {
		$html = '';

		if ( $item['created_at'] ) {
			$offsetSeconds = floatval( $this->gmtOffset ) * 60 * 60;
			$timestamp     = strtotime( $item['created_at'] ) + $offsetSeconds;
			$result        = gmdate( 'Y-m-d H:i:s', $timestamp );
			$date          = new DateTime( $result );

			$html .= sprintf(
				'<span><strong>%s, %s</strong></span>',
				$date->format( $this->dateFormat ),
				$date->format( $this->timeFormat )
			);
		}

		return $html;
	}


	/**
	 * Default column value.
	 *
	 * @param array $item Associative array of column name and value pairs
	 * @param string $column_name Name of the current column
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? esc_attr( $item[ $column_name ] ) : '';
	}

	/**
	 * Set the table columns.
	 */
	public function get_columns() {
		return array(
			'cb'         => '<input type="checkbox" />',
			'label'      => __( 'Label', 'license-manager-for-woocommerce' ),
			'license_id' => __( 'License', 'license-manager-for-woocommerce' ),
			'token'      => __( 'Token', 'license-manager-for-woocommerce' ),
			'source'     => __( 'Source', 'license-manager-for-woocommerce' ),
			'ip_address' => __( 'IP Address', 'license-manager-for-woocommerce' ),
			'status'     => __( 'Status', 'license-manager-for-woocommerce' ),
			'created_at' => __( 'Created', 'license-manager-for-woocommerce' )
		);
	}

	/**
	 * Defines sortable columns and their sort value.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'label'      => 'label',
			'created_at' => array( 'created_at', true )
		);
	}

	/**
	 * Processes the currently selected action.
	 */
	private function processBulkActions() {
		$action = $this->current_action();
		

		switch ( $action ) {
			case 'activate':
				if ( $this->canActivate ) {
					$this->toggleStatus( 'activate' );
				}
				break;
			case 'deactivate':
				if ( $this->canDeactivate ) {
					$this->toggleStatus( 'deactivate' );
				}
				break;
			case 'delete':
				if ( $this->canDelete ) {
					$this->handleDelete();
				}
				break;
			default:
				break;
		}
	}


	/**
	 * Changes the license key status
	 *
	 * @param $status
	 *
	 * @throws Exception
	 */
	protected function toggleStatus( $status ) {

		switch ( $status ) {
			case 'activate':
			case 'deactivate':
				$nonce = $status;
				break;
			default:
				$nonce = null;
				break;
		}

		$this->verifyNonce( $nonce );
		$this->verifySelection();

		$recordIds = isset( $_REQUEST['id'] ) ? (array) $_REQUEST['id'] : array();
		if ( ! empty( $recordIds ) ) {
			$recordIds = array_map( 'intval', $recordIds );
		}
		$count = 0;

		foreach ( $recordIds as $recordId ) {
			$new_value = 'activate' === $status ? null : date( 'Y-m-d H:i:s' );
			ActivationResourceRepository::instance()->update( $recordId, array( 'deactivated_at' => $new_value ) );
			$count ++;
		}
		
		AdminNotice::success(
			sprintf(
				// Translators: %1$d is the number of items updated, %2$s is the plural noun for the items being updated.
				esc_html__('%1$d %2$s(s) updated successfully.', 'license-manager-for-woocommerce'),
				$count,
				strtolower($this->_args['plural'])
			)
		);
		wp_redirect( admin_url( sprintf( 'admin.php?page=%s', $this->slug ) ) );
	}




	/**
	 * Checks if there are currently any license view filters active.
	 *
	 * @return bool
	 */
	private function isViewFilterActive() {
		if ( array_key_exists( 'status', $_GET )
		     && in_array( $_GET['status'], array( 'active', 'inactive' ) )
		) {
			return true;
		}

		return false;
	}


	/**
	 * Creates the different status filter links at the top of the table.
	 *
	 * @return array
	 */
	protected function get_views() {

		$statusLinks = array();
		$current     = ! empty( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'all';

		$total_active   = $this->getRecordsCount( 'active' );
		$total_inactive = $this->getRecordsCount( 'inactive' );

		// All link
		$class              = $current == 'all' ? ' class="current"' : '';
		$allUrl             = remove_query_arg( 'status' );
		$statusLinks['all'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$allUrl,
			$class,
			__( 'All', 'license-manager-for-woocommerce' ),
			$total_active + $total_inactive
		);

		// Active link
		$class                 = $current == 'active' ? ' class="current"' : '';
		$activeUrl             = esc_url( add_query_arg( 'status', 'active' ) );
		$statusLinks['active'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$activeUrl,
			$class,
			__( 'Active', 'license-manager-for-woocommerce' ),
			$total_active
		);

		// Inactive link
		$class                   = $current == 'inactive' ? ' class="current"' : '';
		$inactiveUrl             = esc_url( add_query_arg( 'status', 'inactive' ) );
		$statusLinks['inactive'] = sprintf(
			'<a href="%s" %s>%s <span class="count">(%d)</span></a>',
			$inactiveUrl,
			$class,
			__( 'Inactive', 'license-manager-for-woocommerce' ),
			$total_inactive
		);

		return $statusLinks;
	}

	/**
	 * Removes the records permanently from the database.
	 * @throws Exception
	 */
	private function handleDelete() {

		$this->verifyNonce( 'delete' );
		$this->verifySelection();

		$recordIds = isset( $_REQUEST['id'] ) ? (array) $_REQUEST['id'] : array();
        $count         = 0;

        foreach ($recordIds as $recordId) {

            $result = ActivationResourceRepository::instance()->delete((array)$recordId);

            if ($result) {
                $count += $result;
            }
        }
		// Translators: %d is the number of activation records deleted.
		$message = sprintf( esc_html__( '%d activation record(s) permanently deleted.', 'license-manager-for-woocommerce' ), $count );

		// Set the admin notice
		AdminNotice::success( $message );

		// Redirect and exit
		wp_redirect(
			admin_url(
				sprintf( 'admin.php?page=%s', $this->slug )
			)
		);
	}

	/**
	 * Adds the order and product filters to the licenses list.
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( $which === 'top' ) {
			echo '<div class="alignleft actions">';
			$this->licenseDropdown();
			$this->sourceDropdown();
			submit_button( __( 'Filter', 'license-manager-for-woocommerce' ), '', 'filter-action', false );
			echo '</div>';
		}
	}

	/**
	 * Displays the order dropdown filter.
	 */
	public function licenseDropdown() {

		$selected = isset( $_REQUEST['license-id'] ) ? (int) $_REQUEST['license-id'] : '';
		?>
        <label for="filter-by-license-id" class="screen-reader-text">
            <span><?php esc_html_e( 'Filter by license', 'license-manager-for-woocommerce' ); ?></span>
        </label><select name="license-id" id="filter-by-license-id">
            <option></option>
			<?php if ( $selected ): ?>
                <option selected value="<?php echo (int) $selected; ?>"><?php echo sprintf( '#%d', esc_attr( $selected ) ); ?></option>
			<?php endif; ?>
        </select>
		<?php
	}

	/**
	 * Displays the order dropdown filter.
	 */
	public function sourceDropdown() {

		$selected = isset( $_REQUEST['license-source'] ) ? (int) $_REQUEST['license-source'] : - 1;
		?>
        <label for="filter-by-source" class="screen-reader-text">
            <span><?php esc_html_e( 'Filter by source', 'license-manager-for-woocommerce' ); ?></span>
        </label>

        <select name="license-source" id="filter-by-source">
            <option></option>
			<?php foreach ( ActivationProcessor::getAllSources() as $key => $name ): ?>
                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected, $key ); ?>><?php echo esc_attr( $name ); ?></option>
			<?php endforeach; ?>
        </select>
		<?php
	}

	/**
	 * Defines items in the bulk action dropdown.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {

		$actions = array();
		if ( $this->canActivate ) {
			$actions['activate'] = __( 'Activate', 'license-manager-for-woocommerce' );
		}
		if ( $this->canDeactivate ) {
			;
			$actions['deactivate'] = __( 'Deactivate', 'license-manager-for-woocommerce' );
		}
		if ( $this->canDelete ) {

			$actions['delete'] = __( 'Delete', 'license-manager-for-woocommerce' );
		}

		return $actions;
	}


	/**
	 * Initialization function.
	 *
	 * @throws Exception
	 */
	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();

		$this->processBulkActions();

		$perPage     = $this->get_items_per_page( 'activations_per_page', 10 );
		$currentPage = $this->get_pagenum();
		$totalItems  = $this->getRecordsCount();

		$this->set_pagination_args(
			array(
				'total_items' => $totalItems,
				'per_page'    => $perPage,
				'total_pages' => ceil( $totalItems / $perPage )
			)
		);

		$this->items = $this->getRecords( $perPage, $currentPage );
	}

	  /**
     * Checks if the given nonce is valid.
     *
     * @param string $nonceAction The nonce to check
     *
     * @throws Exception
     */
    private function verifyNonce($nonceAction)
    {

        if (
            !wp_verify_nonce($_REQUEST['_wpnonce'], $nonceAction) &&
            !wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'] )
        ) {
            AdminNotice::error(__('The nonce is invalid or has expired.', 'license-manager-for-woocommerce'));
            wp_redirect(
                admin_url(sprintf('admin.php?page=%s', AdminMenus::ACTIVATIONS_PAGE))
            );

            exit();
        }
    }

    /**
     * Makes sure that generators were selected for the bulk action.
     */
    private function verifySelection()
    {
        // No ID's were selected, show a warning and redirect
        if (!array_key_exists('id', $_REQUEST)) {
            $message = sprintf(esc_html__('No Activations were selected.', 'license-manager-for-woocommerce'));
            AdminNotice::warning($message);

            wp_redirect(
                admin_url(
                    sprintf('admin.php?page=%s', AdminMenus::ACTIVATIONS_PAGE)
                )
            );

            exit();
        }
    }

}
