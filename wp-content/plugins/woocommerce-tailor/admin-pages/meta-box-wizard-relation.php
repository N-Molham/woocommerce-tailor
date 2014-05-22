<?php
/**
 * Design Wizard Options
 *
 * @since 1.0
 */

// get filters
if ( (float) phpversion() >= 5.4 )
	$design_wizard_filters = WC_Tailor()->get_design_wizard_settings()['filters'];
else
	$design_wizard_filters = WC_Tailor()->get_design_wizard_settings( true )->filters;

$filters_labels = WC_Tailor()->wizard_filters_labels();
$filters_meta_keys = WC_Tailor()->wizard_filters_meta_keys();

// panel start
echo '<div class="panel woocommerce_options_panel">';

// filters loop
foreach ( $design_wizard_filters as $filter_name => $filter_data )
{
	// skip price filter
	if ( 'max_price' == $filter_name )
		continue;

	// input value
	$value = (array) get_post_meta( $post->ID, $filters_meta_keys[$filter_name] );

	// input layout
	echo '<div class="options_group"><p class="form-field">';
	echo '<label for="wc_tailor_', $filter_name ,'">', $filters_labels[$filter_name] ,'</label>';
	echo '<select name="wc_tailor_filters[', $filter_name ,'][]" id="wc_tailor_', $filter_name ,'" multiple class="chosen">';
	foreach ( $filter_data['options'] as $option_index => $option_name )
	{
		// option index (id)
		echo '<option value="', $option_index ,'"';

		// is selected
		echo ( in_array( $option_index, $value ) ? ' selected' : '' );

		// option name
		echo '>', $option_name ,'</option>';
	}
	echo '</select></p></div>';
}

// panel end
echo '</div>';

?>
<script>
( function( window ) {
	jQuery( function( $ ) {
		$( '.chosen' ).chosen();
	} );
} )( window );
</script>