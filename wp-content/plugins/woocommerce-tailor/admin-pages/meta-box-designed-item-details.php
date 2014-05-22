<?php
/**
 * Order Details: Designed Item information
 *
 * @since 1.0
 */

global $wpdb, $thepostid, $theorder, $woocommerce;

if ( ! is_object( $theorder ) )
	$theorder = new WC_Order( $thepostid );

$has_designed_item = false;

$order_items = $theorder->get_items();
foreach ( $order_items as $order_item_id => $order_item_data )
{
	// check for designed item only
	if ( !isset( $order_item_data['wct_designed_item'] ) || 'yes' !== $order_item_data['wct_designed_item'] || !isset( $order_item_data['wct_order_info'] ) )
		continue;

	// unserialized designed item data
	$item_data = maybe_unserialize( $order_item_data['wct_order_info'] );
	if ( !is_array( $item_data ) )
		continue;

	// default values
	$item_data = wp_parse_args( $item_data, array ( 
			'fabric' => 0,
			'gender' => '',
			'body_profile' => array(),
			'measures' => array(),
			'shirt-characters' => array(),
			'in_cart' => false,
			'cart_item_key' => '',
	) );

	// it has a designed item
	$has_designed_item = true;

	// needed data
	$body_profile_fields = WC_Tailor()->account_updates->get_account_details_by_section( 'body_profile' );

	// panel start
	echo '<div class="panel woocommerce_options_panel">';

	// fabric
	echo '<div class="options_group"><p class="form-field">';
	echo '<label>', __( 'Fabric', WCT_DOMAIN ) ,'</label>';
	$fabic_product = get_product( $item_data['fabric'] );
	printf( __( '<a href="%s" target="_blank">%s</a>', WCT_DOMAIN ), admin_url( 'post.php?post='. $fabic_product->id .'&action=edit' ), $fabic_product->get_title() );
	echo '</p></div>';

	// gender
	echo '<div class="options_group"><p class="form-field">';
	echo '<label>', __( 'Gender', WCT_DOMAIN ) ,'</label>';
	echo ucwords( $item_data['gender'] );
	echo '</p></div>';

	// body profile
	echo '<div class="options_group"><br/>';
	echo '<table class="wc_status_table widefat wct-focus-table">';
	echo '<thead><tr><th colspan="2">', __( 'Body Profile', WCT_DOMAIN ) ,'</th></tr></thead>';
	echo '<tbody>';
	foreach ( $body_profile_fields as $field_name => $field_args )
	{
		$option_key = str_replace( 'body_profile_', '', $field_name );
		if ( !isset( $item_data['body_profile'][$option_key] ) )
			continue;

		// option name
		echo '<tr><td>', preg_replace( '/<span.+/', '', $field_args['label'] ) ,'</td><td>';

		// option selected value
		if ( 'radio' === $field_args['input'] )
			echo $field_args['options'][ $item_data['body_profile'][$option_key] ]; 
		elseif ( 'text' === $field_args['input'] )
			echo number_format( $item_data['body_profile'][$option_key], 2 ), $field_args['description'];

		echo '</td></tr>';
	}
	echo '</tbody></table><br/></div>';

	// measurements
	echo '<div class="options_group"><br/>';
	echo '<table class="wc_status_table widefat wct-focus-table">';
	echo '<thead><tr><th colspan="3">', __( 'Measurements', WCT_DOMAIN ) ,'</th></tr></thead>';
	echo '<tbody>';
	foreach ( WC_Tailor()->account_updates->body_measurements as $measure_name => $measure_info )
	{
		if ( !isset( $item_data['measures'][$measure_name] ) )
			continue;

		// measure label
		echo '<tr><td>', $measure_info['label'] ,'</td>';
		echo '<td>', $item_data['measures'][$measure_name]['cm'], ' cm</td>';
		echo '<td>', $item_data['measures'][$measure_name]['inches'], ' inches</td>';

		echo '</tr>';
	}
	echo '</tbody></table><br/></div>';

	// shirt characteristics
	echo '<div class="options_group"><br/>';
	echo '<table class="wc_status_table widefat wct-focus-table">';
	echo '<thead><tr><th colspan="2">', __( 'Shirt\'s Characteristics', WCT_DOMAIN ) ,'</th></tr></thead>';
	echo '<tbody>';
	foreach ( $item_data['shirt-characters'] as $character )
	{
		echo '<tr><td>', $character['label'] ,'</td>';
		echo '<td>', $character['value_label'], '</td>';
		echo '</tr>';
	}
	echo '</tbody></table><br/></div>';

	// panel end
	echo '</div>';
}

if ( !$has_designed_item )
	_e( 'No Designed item in this order.', WCT_DOMAIN );








