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

<form action="" method="post">

	<p class="form-row form-row-first">
		<label for="account_first_name"><?php _e( 'First name', BRETHEON_CHILD_DOMAIN ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="account_first_name" id="account_first_name" value="<?php esc_attr_e( $user->first_name ); ?>" />
	</p>
	<p class="form-row form-row-last">
		<label for="account_last_name"><?php _e( 'Last name', BRETHEON_CHILD_DOMAIN ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="account_last_name" id="account_last_name" value="<?php esc_attr_e( $user->last_name ); ?>" />
	</p>
	<p class="form-row form-row-wide">
		<label for="account_email"><?php _e( 'Email address', BRETHEON_CHILD_DOMAIN ); ?> <span class="required">*</span></label>
		<input type="email" class="input-text" name="account_email" id="account_email" value="<?php esc_attr_e( $user->user_email ); ?>" />
	</p>
	<p class="form-row form-row-first">
		<label for="password_1"><?php _e( 'Password (leave blank to leave unchanged)', BRETHEON_CHILD_DOMAIN ); ?></label>
		<input type="password" class="input-text" name="password_1" id="password_1" />
	</p>
	<p class="form-row form-row-last">
		<label for="password_2"><?php _e( 'Confirm new password', BRETHEON_CHILD_DOMAIN ); ?></label>
		<input type="password" class="input-text" name="password_2" id="password_2" />
	</p>

	<?php
	// additional account fields 
	do_action( 'woocommerce_tailor_account_fields', $user );
	?>

	<div class="clear"></div>

	<p><input type="submit" class="button" name="save_account_details" value="<?php _e( 'Save changes', BRETHEON_CHILD_DOMAIN ); ?>" /></p>

	<?php wp_nonce_field( 'save_account_details' ); ?>
	<input type="hidden" name="action" value="save_account_details" />
</form>

