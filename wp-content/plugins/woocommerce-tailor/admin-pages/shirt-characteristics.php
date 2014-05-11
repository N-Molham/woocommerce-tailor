<?php
/**
 * Shirt's Characteristics
 * 
 * @since 1.0
 */

// Selected gender
$gender = filter_input( INPUT_GET, 'gender' );
if ( !$gender || !in_array( $gender, array( 'male', 'female' ) ) )
	$gender = 'male';

// get option
$shirt_characters = get_option( 'wc_tailor_shirt_chars' );
if ( false === $shirt_characters )
{
	// default value
	$shirt_characters = array( 'male' => array(), 'female' => array() );

	// set option
	add_option( 'wc_tailor_shirt_chars', $shirt_characters, '', 'no' );
}

// json data build
$json_data = array();
foreach ( $shirt_characters[$gender] as $index => $characters )
{
	$json_data[$index] = $characters;
}
//dump_data( $json_data );
?>

<h2><?php _e( 'Shirt\'s Characteristics', WCT_DOMAIN ); ?></h2>

<?php 
if ( isset( $_GET['message'] ) )
{
	switch ( $_GET['message'] )
	{
		case 'success':
			echo '<div class="updated"><p><strong>', __( 'Data saved.', WCT_DOMAIN ) ,'</strong></p></div>';
			break;
	}
}

?>

<h2 class="nav-tab-wrapper">
	<a href="<?php echo add_query_arg( array( 'gender' => 'male', 'message' => false ) ); ?>" class="nav-tab<?php echo 'male' == $gender ? ' nav-tab-active' : '' ?>"><?php _e( 'Men', WCT_DOMAIN ); ?></a>
	<a href="<?php echo add_query_arg( array( 'gender' => 'female', 'message' => false ) ); ?>" class="nav-tab<?php echo 'female' == $gender ? ' nav-tab-active' : '' ?>"><?php _e( 'Women', WCT_DOMAIN ); ?></a>
</h2>

<form action="" method="post" id="characteristics">

	<ul class="shirt-chars"
			data-values="<?php echo htmlentities( json_encode( $json_data ) ); ?>" 
			data-add-button-class="button" 
			data-confirm-remove="yes" 
			data-confirm-remove-message="<?php esc_attr_e( 'Are Your Sure ?', WCT_DOMAIN ); ?>" 
			data-empty-list-message="<?php echo esc_attr( '<p class="error">'. __( 'Not option added yet.' ) .'</p>' ); ?>">
		<li data-template="yes" class="list-item">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="option_{index}_label"><?php _e( 'Option Label', WCT_DOMAIN ); ?></label></th>
						<td><input name="chars[{index}][label]" id="option_{index}_label" type="text" class="large-text" value="{label}" /></td>
					</tr>
					<tr>
						<th scope="row"><label><?php _e( 'Option Values', WCT_DOMAIN ); ?></label></th>
						<td>
							<ol class="option-values"></ol>
							<input type="button" class="button new-option-value" 
												data-input-name="chars[{index}][values]" 
												data-remove-label="<?php esc_attr_e( 'Remove Value', WCT_DOMAIN ); ?>" 
												data-remove-confirm="<?php esc_attr_e( 'Are You Sure ?', WCT_DOMAIN ); ?>" 
												data-input-label="<?php esc_attr_e( 'Value Label', WCT_DOMAIN ); ?>" 
												data-input-price="<?php echo esc_attr( sprintf( __( 'Value Price (%s)', WCT_DOMAIN ), get_woocommerce_currency_symbol() ) ); ?>" 
												value="<?php esc_attr_e( 'Add New', WCT_DOMAIN ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="option_{index}_picture"><?php _e( 'Option Picture', WCT_DOMAIN ); ?></label></th>
						<td>
							<input name="chars[{index}][picture]" id="option_{index}_picture" type="text" class="regular-text code" value="{picture}" />
							<input type="button" class="button media-button" 
												data-target="option_{index}_picture" 
												data-frame-title="<?php esc_attr_e( 'Select option image', WCT_DOMAIN ); ?>" 
												value="<?php esc_attr_e( 'Media Library' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="option_{index}_desc"><?php _e( 'Option Description', WCT_DOMAIN ); ?></label></th>
						<td><textarea name="chars[{index}][desc]" id="option_{index}_desc" class="large-text">{desc}</textarea></td>
					</tr>
					<tr>
						<th scope="row">&nbsp;</th>
						<td><a href="#" class="button button-remove" data-remove="yes"><?php _e( 'Remove', WCT_DOMAIN ); ?></a></td>
					</tr>
				</tbody>
			</table>
			<hr /><hr />
		</li>
	</ul>

	<!-- Hidden fields -->
	<?php wp_nonce_field( 'wc_tailor_shirt_chars', 'nonce' ); ?>
	<input type="hidden" name="gender" id="gender" value="<?php echo $gender; ?>" />

	<p class="submit"><input type="submit" name="wct_shirt_save" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', WCT_DOMAIN ); ?>" /></p>

</form>