<?php
/**
 * Edit account form
 *
 * @package 	Bretheon-Child/WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<?php wc_print_notices(); ?>

<form action="" method="post" id="account-details">

	<?php
	// account details sections & fields
	do_action( 'woocommerce_tailor_account_fields', $user );
	?>

	<div class="clear"></div>

	<p><input type="submit" class="button" name="save_account_details" value="<?php _e( 'Save changes', WCT_DOMAIN ); ?>" /></p>

	<?php wp_nonce_field( 'save_account_details' ); ?>
	<input type="hidden" name="action" value="save_account_details" />
</form>

