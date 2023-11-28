<?php
/**
 * Some of the code written, maintained by Darko Gjorgjijoski
 */
use LicenseManagerForWooCommerce\Lists\ActivationsList;

defined( 'ABSPATH' ) || exit;

/**
 * @var Activations $activations
 */
?>

<h1 class="wp-heading-inline"><?php esc_html_e( 'Activations', 'license-manager-for-woocommerce' ); ?></h1>

<hr class="wp-header-end">

<form method="post">
	<?php
	$activations->prepare_items();
	$activations->views();
	$activations->search_box(__( 'Search activations', 'license-manager-for-woocommerce' ), 'license_key');
	$activations->display();
	?>
</form>
