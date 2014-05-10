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
$shirt_chars = get_option( 'wc_tailor_shirt_chars' );
if ( false === $shirt_chars )
{
	// default value
	$shirt_chars = array( 'male' => array(), 'female' => array() );

	// set option
	add_option( 'wc_tailor_shirt_chars', $shirt_chars, '', 'no' );
}

?>

<h2><?php _e( 'Shirt\'s Characteristics', WCT_DOMAIN ); ?></h2>

<h2 class="nav-tab-wrapper">
	<a href="<?php echo add_query_arg( 'gender', 'male' ); ?>" class="nav-tab<?php echo 'male' == $gender ? ' nav-tab-active' : '' ?>"><?php _e( 'Men', WCT_DOMAIN ); ?></a>
	<a href="<?php echo add_query_arg( 'gender', 'female' ); ?>" class="nav-tab<?php echo 'female' == $gender ? ' nav-tab-active' : '' ?>"><?php _e( 'Women', WCT_DOMAIN ); ?></a>
</h2>

<form action="" method="post" id="characteristics">

	<ul class="repeatable"
			data-add-button-class="button" 
			data-confirm-remove="yes" 
			data-confirm-remove-message="<?php esc_attr_e( 'Are Your Sure ?', WCT_DOMAIN ); ?>" 
			data-empty-list-message="<?php echo esc_attr( '<p class="error">'. __( 'Not option added yet.' ) .'</p>' ); ?>">
		<li data-template="yes" class="list-item">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="option_{index}_label"><?php _e( 'Option Label', WCT_DOMAIN ); ?></label></th>
						<td><input name="options[{index}][label]" id="option_{index}_label" type="text" class="regular-text" value="{label}" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="option_{index}_price"><?php printf( __( 'Option Price (%s)', WCT_DOMAIN ), get_woocommerce_currency_symbol() ); ?></label></th>
						<td><input name="options[{index}][price]" id="option_{index}_price" min="0" step="0.5" type="number" class="small-text" value="{price}" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="option_{index}_picture"><?php _e( 'Option Picture', WCT_DOMAIN ); ?></label></th>
						<td>
							<input name="options[{index}][picture]" id="option_{index}_picture" type="text" class="regular-text code" value="{picture}" />
							<input data-target="option_{index}_picture" type="button" class="button media-button" data-frame-title="<?php esc_attr_e( 'Select option image', WCT_DOMAIN ); ?>" value="<?php esc_attr_e( 'Media Library' ); ?>" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="option_{index}_desc"><?php _e( 'Option Description', WCT_DOMAIN ); ?></label></th>
						<td><textarea name="options[{index}][desc]" id="option_{index}_desc" class="large-text code">{description}</textarea></td>
					</tr>
					<tr>
						<th scope="row">&nbsp;</th>
						<td><a href="#" class="button button-remove" data-remove="yes"><?php _e( 'Remove', WCT_DOMAIN ); ?></a></td>
					</tr>
				</tbody>
			</table>
			<hr />
		</li>
	</ul>

</form>