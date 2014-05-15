<?php
/**
 * Design Wizard Options
 * 
 * @since 1.0
 */

$settings = WC_Tailor()->get_design_wizard_settings();
$filters_labels = WC_Tailor()->wizard_filters_labels();
?>

<h2><?php _e( 'Design Wizard Options', WCT_DOMAIN ); ?></h2>
<p></p>
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

	<h3><?php _e( 'Products View', WCT_DOMAIN ); ?></h3>
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
						echo ( $categories[$i]->term_id == $settings['category'] ? ' selected' : '' );

						// category name
						echo '>', $categories[$i]->name ,'</option>';
					}
					?></select>
					<span class="description"><?php _e( 'The materials', WCT_DOMAIN ); ?></span>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wizard_columns"><?php _e( 'Number of Columns', WCT_DOMAIN ); ?></label></th>
				<td>
					<input type="number" step="1" min="1" name="wizard[columns]" id="wizard_columns" class="small-text" value="<?php echo (int) $settings['columns']; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="wizard_per_page"><?php _e( 'Number of Products Per Page', WCT_DOMAIN ); ?></label></th>
				<td>
					<input type="number" step="1" min="1" name="wizard[per_page]" id="wizard_per_page" class="small-text" value="<?php echo (int) $settings['per_page']; ?>" />
				</td>
			</tr>
		</tbody>
	</table>

	<h3><?php _e( 'Products Filters', WCT_DOMAIN ); ?></h3>
	<table class="form-table wc-filters">
		<tbody>
			<?php
			// comparison list
			$compares = array (
					'=' => __( 'Equal to', WCT_DOMAIN ),
					'!=' => __( 'Not Equal to', WCT_DOMAIN ),
					'>' => __( 'Greater Than', WCT_DOMAIN ),
					'>=' => __( 'Greater Than or Equal', WCT_DOMAIN ),
					'<' => __( 'Less Than', WCT_DOMAIN ),
					'<=' => __( 'Less Than or Equal', WCT_DOMAIN ),
			);

			foreach ( WC_Tailor_Design_Wizard::get_filters_defaults() as $filter_name => $filter_default_data )
			{
				// inputs prefix
				$input_id_prefix = 'wizard_filters_'. $filter_name .'_';
				$input_name_prefix = 'wizard[filters]['. $filter_name .']';
				$filter_data = isset( $settings['filters'][$filter_name] ) ? $settings['filters'][$filter_name] : $filter_default_data;

				// filter label
				echo '<tr><th scope="row"><label>', $filters_labels[$filter_name] ,'</label></th><td>';

				// comparison label
				echo '<label for="', $input_id_prefix ,'compare">', __( 'Comparison', WCT_DOMAIN ) ,' : </label>';

				// comparison options
				echo '<select name="', $input_name_prefix ,'[compare]" id="', $input_id_prefix ,'compare">';
				foreach ( $compares as $compare_key => $compare_label )
				{
					echo '<option value="', $compare_key ,'"';
					echo htmlentities( $compare_key ) === $filter_data['compare'] ? ' selected' : '';
					echo '>', $compare_label ,'</option>';
				}
				echo '</select>';

				// options label
				echo '<br/><label for="', $input_id_prefix ,'options">', __( 'Options', WCT_DOMAIN ) ,' : </label>';

				// options values
				echo '<div class="options-list">';
					echo '<ol class="repeatable" data-confirm-remove-message="', esc_attr__( 'Are Your Sure ?', WCT_DOMAIN ) ,'" ';
					echo 'data-add-button-class="button" data-confirm-remove="yes" data-empty-list-message="no" data-values="', htmlentities( json_encode( $filter_data['options'] ) ) ,'">';
						echo '<li data-template="yes" class="list-item">';
						echo '<input type="text" name="', $input_name_prefix ,'[options][{index}]" class="regular-text" value="{value}" placeholder="', esc_attr__( 'Option Name', WCT_DOMAIN ) ,'" />';
						echo '&nbsp;&nbsp;<a href="#" class="button button-remove" data-remove="yes">', __( 'Remove', WCT_DOMAIN ) ,'</a></li>';
					echo '</ol>';
				echo '</div>';

				// label saving
				echo '<input type="hidden" name="name" id="name" value="value" />';

				// end row
				echo '</td></tr>';
			}
			?>
		</tbody>
	</table>

	<!-- nonces -->
	<?php wp_nonce_field( 'wc_tailor_admin_design_wizard', 'nonce' ); ?>

	<p class="submit"><input type="submit" name="wct_wizard_save" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', WCT_DOMAIN ); ?>" /></p>

</form>