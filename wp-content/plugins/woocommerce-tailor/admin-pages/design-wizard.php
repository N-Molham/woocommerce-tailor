<?php
/**
 * Design Wizard Options
 * 
 * @since 1.0
 */

$desing_wizard = WC_Tailor()->get_design_wizard_settings();

?>

<h2><?php _e( 'Design Wizard Options', WCT_DOMAIN ); ?></h2>

<?php 
if ( isset( $_GET['message'] ) )
{
	switch ( $_GET['message'] )
	{
		case 'success':
			echo '<div class="updated"><p><strong>', __( 'Settings saved.', WCT_DOMAIN ) ,'</strong></p></div>';
			break;
	}
}

?>

<form action="" method="post" id="desgin-wizard">

	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="wizard_category"><?php _e( 'Products Category', WCT_DOMAIN ); ?></label></th>
				<td>
					<select name="wizard[category]" id="wizard_category" class="chosen_select"><?php
					// category list 
					$categories = get_terms( 'product_cat', array ( 
							'hide_empty' => false,
							'number' => 0,
					) );

					for ( $i = 0, $len = count( $categories ); $i < $len; $i++ )
					{
						// category id
						echo '<option value="', $categories[$i]->term_id ,'"';

						// is selected
						echo ( $categories[$i]->term_id == $desing_wizard['category'] ? ' selected' : '' );

						// category name
						echo '>', $categories[$i]->name ,'</option>';
					}
					?></select>
					<span class="description"><?php _e( 'The materials', WCT_DOMAIN ); ?></span>
				</td>
			</tr>
		</tbody>
	</table>

	<!-- nonces -->
	<?php wp_nonce_field( 'wc_tailor_admin_design_wizard', 'nonce' ); ?>

	<p class="submit"><input type="submit" name="wct_wizard_save" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', WCT_DOMAIN ); ?>" /></p>

</form>

<script>
( function( window ) {
	jQuery( function( $ ) {
		$( '.chosen_select' ).chosen();
	} );
} )( window );
</script>