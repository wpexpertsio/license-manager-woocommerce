<?php


namespace LicenseManagerForWooCommerce\Controllers;

use LicenseManagerForWooCommerce\Models\Resources\License as LicenseResourceModel;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\Generator as GeneratorResourceRepository;
use LicenseManagerForWooCommerce\Setup;

use WP_Query;
use WP_User;
use WP_User_Query;

/**
 * Class Dropdowns
 *
 * @package LicenseManagerForWooCommerce\Controllers
 */
class Dropdowns {

	/**
	 * Dropdowns constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_lmfwc_dropdown_search', array( $this, 'dropdownSearch' ), 15 );
	}

	/**
	 * Formats post object
	 *
	 * @param \WP_Post $record
	 */
	private function formatPost( $record ) {
		return array(
			'id'   => $record->ID,
			'text' => sprintf( '#%d - %s', $record->ID, $record->post_title )
		);
	}

	/**
	 * The dropdown search
	 */
	public function dropdownSearch() {

		check_ajax_referer( 'lmfwc_dropdown_search', 'security' );
		if ( ! current_user_can( 'lmfwc_read_licenses' ) ) {
			wp_die();
		}
		$type    = isset( $_POST['type'] ) ? (string) sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '' ;
		$page    = 1;
		$limit   = 6;
		$results = array();
		$term    = isset( $_POST['term'] ) ? (string) sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
		$more    = true;
		$offset  = 0;
		$ids     = array();

		if ( ! $term ) {
			wp_die();
		}

		if ( array_key_exists( 'page', $_POST ) ) {
			$page = (int) $_POST['page'];
		}

		if ( $page > 1 ) {
			$offset = ( $page - 1 ) * $limit;
		}
		/**
		*	Filter lmfwc_dropdown_searchable_post_types
		*	
		*	@since 1.0
		**/
		$searchable_post_types = apply_filters( 'lmfwc_dropdown_searchable_post_types', array() );
		/**
		*	Filter lmfwc_dropdown_search_query_default_status
		*	
		*	@since 1.0
		**/
		$search_query_status   = apply_filters( 'lmfwc_dropdown_search_query_default_status', array( 'publish' ), $type );

		if ( is_numeric( $term ) ) {
			// Search for a specific license
			if ( 'license' === $type ) {

				$license = LicenseResourceRepository::instance()->find( (int) $term );

				// Product exists.
				if ( $license ) {
					$text      = sprintf(
						'#%s',
						$license->getId()
					);
					$results[] = array(
						'id'   => $license->getId(),
						'text' => $text
					);
				}
			} 
			//  elseif ( 'generator' === $type ) {
            //     $generator = GeneratorResourceRepository::instance()->find( (int) $term);

                

            //     // Product exists.
            //     if ( $generator ) {
            //         $text      = sprintf(
            //             '#%s',
            //             $generator->getId()
            //         );
            //         $results[] = array(
            //             'id'   => $generator->getId(),
            //             'text' => $text
            //         );
            //     }
            // }
			elseif( 'user' === $type ) {

				$users = new WP_User_Query(
					array(
						'search'         => '*' . esc_attr( $term ) . '*',
						'search_columns' => array(
							'user_id'
						),
					)
				);

				if ( $users->get_total() <= $limit ) {
					$more = false;
				}

				foreach ( $users->get_results() as $user ) {
					$results[] = array(
						'id'   => $user->ID,
						'text' => sprintf(
						/* translators: $1: user nicename, $2: user id, $3: user email */
							'%1$s (#%2$d - %3$s)',
							$user->user_nicename,
							$user->ID,
							$user->user_email
						)
					);
				}
			} elseif ( 'product' === $type ) {

				$products = [];
				$product  = wc_get_product( $term );
				if ( ! empty( $product ) ) {
					$products[] = $product;
					foreach ( $product->get_children() as $child ) {
						$products[] = wc_get_product( $child );
					}
				}
				foreach ( $products as $product ) {
					$results[] = $this->formatProduct( $product );
				}
				$more = false;

			} elseif ( ! empty( $searchable_post_types ) && in_array( $type, $searchable_post_types ) ) {
				global $wpdb;

				$search_query_status_in = array_map( function ( $item ) {
					return sprintf( "'%s'", esc_sql( $item ) );
				}, $search_query_status );

				$search_query_status_in = implode( ',', $search_query_status_in );

				$query   = $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_status IN (" . $search_query_status_in . ") AND post_type=%s AND ID LIKE %s LIMIT %d OFFSET %d", $type, '%' . $term . '%', $limit, $offset );
				$records = $wpdb->get_results( $query );
				$total   = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status IN (" . $search_query_status_in . ") AND post_type=%s AND ID LIKE %s", $type, '%' . $term . '%' ) );

				if ( $total <= $limit ) {
					$more = false;
				}

				if ( ! empty( $records ) ) {
					foreach ( $records as $record ) {
						$results[] = $this->formatPost( $record );
					}
				}

			} else {
				/**
				*	Filter lmfwc_dropdown_search_single
				*	
				*	@since 1.0
				**/
				$result = apply_filters( 'lmfwc_dropdown_search_single', [], $type, $term );
				if ( ! empty( $result ) ) {
					$results[] = $result;
				}
			}
		} else {
			$args = array(
				'type'     => $type,
				'limit'    => $limit,
				'offset'   => $offset,
				'customer' => $term,
			);

			// Search for licenses
			if ( 'license' === $type ) {
				$licenses = $this->searchLicenses( $term, $limit, $offset );

				if ( count( $licenses ) < $limit ) {
					$more = false;
				}

				foreach ( $licenses as $licenseId ) {
					
					$text      = sprintf(
						'#%s',
						$licenseId
					);
					$results[] = array(
						'id'   => $licenseId,
						'text' => $text
					);
				}
			}
			// elseif ( 'generator' === $type ) {

            //     $generator = GeneratorResourceRepository::instance()->find( (int) $term);
               
                

            //     // Product exists.
            //     if ( $generator ) {
            //         $text      = sprintf(
            //             '#%s',
            //             $generator->getId()
            //         );
            //         $results[] = array(
            //             'id'   => $generator->getId(),
            //             'text' => $text
            //         );
            //     }
            // }
			 elseif ( 'user' === $type  ) {
				$users = new WP_User_Query(
					array(
						'search'         => '*' . esc_attr( $term ) . '*',
						'search_columns' => array(
							'user_login',
							'user_nicename',
							'user_email',
							'user_url',
						),
					)
				);

				if ( $users->get_total() < $limit ) {
					$more = false;
				}

				foreach ( $users->get_results() as $user ) {
					$results[] = array(
						'id'   => $user->ID,
						'text' => sprintf( '%s (#%d - %s)', $user->user_nicename, $user->ID, $user->user_email )
					);
				}
			} else if ( 'product' === $type ) {

				$query = wc_get_products( [
					'page'   => $page,
					'limit'  => $limit,
					'search' => $term,
					'paginate' => true,
				] );


				foreach ( $query->products as $product ) {
					/* @var \WC_Product $product */
					$results[] = $this->formatProduct( $product );
					$children  = $product->get_children();
					if ( ! empty( $children ) ) {
						foreach ( $children as $child ) {
							$childProduct = wc_get_product( $child );
							if ( $childProduct ) {
								$results[] = $this->formatProduct( $childProduct );
							}
						}
					}
				}

				$more = $page < $query->max_num_pages;

			} else if ( ! empty( $searchable_post_types ) && in_array( $type, $searchable_post_types ) ) {

				$query = new WP_Query( array(
					'post_type'      => $type,
					's'              => esc_attr( $term ),
					'paged'          => $page,
					'posts_per_page' => $limit,
					'post_status'    => $search_query_status,
				) );

				if ( $query->found_posts <= $limit ) {
					$more = false;
				}

				foreach ( $query->posts as $_post ) {
					$results[] = $this->formatPost( $_post );
				}

			} else {
				/**
				*	Filter lmfwc_dropdown_search_multiple
				*	
				*	@since 1.0
				**/
				$result = apply_filters(
					'lmfwc_dropdown_search_multiple',
					array(
						'records' => array(),
						'more'    => $more
					),
					$type,
					$ids,
					$args
				);
				if ( ! empty( $result['records'] ) ) {
					$results = array_merge( $results, $result['records'] );
				}
				if ( isset( $result['more'] ) ) {
					$more = $result['more'];
				}
			}
		}

		wp_send_json(
			array(
				'page'       => $page,
				'results'    => $results,
				'pagination' => array(
					'more' => $more
				)
			)
		);
	}

	/**
	 * Searches the database for posts that match the given term.
	 *
	 * @param string $term The search term
	 * @param int $limit Maximum number of search results
	 * @param int $offset Search offset
	 *
	 * @return array
	 */
	private function searchLicenses($term, $limit, $offset) {
    global $wpdb;
    $tblLicenses = $wpdb->prefix . Setup::LICENSES_TABLE_NAME;

    $termHash = apply_filters('lmfwc_hash', license($term)) . '%'; // Assuming "license" is a function defined somewhere
    $termId = intval($term);

    $query = $wpdb->prepare(
        "SELECT DISTINCT licenses.id
        FROM $tblLicenses AS licenses
        WHERE 1=1 AND (licenses.hash LIKE %s OR licenses.id = %d)
        ORDER BY licenses.ID DESC
        LIMIT %d OFFSET %d",
        $termHash,
        $termId,
        $limit,
        $offset
    );

    return $wpdb->get_col($query);
}


	/**
	 * Format products
	 *
	 * @param \WC_Product $product
	 *
	 * @return array
	 */
	private function formatProduct( $product ) {

		$type = '';
		if ( $product->is_type( 'variable' ) ) {
			$type .= ' (' . __( 'Variable', 'license-manager-for-woocommerce' ) . ')';
		} else if ( $product->is_type( 'variation' ) ) {
			$type .= ' (' . __( 'Variation', 'license-manager-for-woocommerce' ) . ')';
		}

		if ($product->is_type( 'variation' )) {
			$id = $product->get_parent_id();
			$title = wp_strip_all_tags($product->get_formatted_name());
		} else {
			$title = $product->get_name();
			$id = $product->get_id();
		}


		return [
			'id'   => $product->get_id(),
			'text' => sprintf( '#%d - %s%s', $id, $title, $type )
		];
	}

	/**
	 * Handles search parameter for products
	 *
	 * @param $query
	 * @param $query_vars
	 *
	 * @return mixed
	 */
	public function handleSearchParameter( $query, $query_vars ) {
		if ( isset( $query_vars['search'] ) && ! empty( $query_vars['search'] ) ) {
			$query['s'] = esc_attr( $query_vars['search'] );
		}

		return $query;
	}
}
