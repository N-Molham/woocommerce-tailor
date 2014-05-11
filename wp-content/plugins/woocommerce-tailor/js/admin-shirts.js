/**
 * Shirt Charactristics
 */
( function( window ) {
	jQuery( function( $ ) {
		// repeatable item
		var $options = $( '.shirt-chars' ),
			file_frame;

		// new option added
		$options.on( 'repeatable-new-item', function( e, $new_item, index, data ) {
			var $option_values = $new_item.find( '.option-values' ),
				$add_value_button = $new_item.find( '.new-option-value' );
			
			// option value layout
			var value_layout = '<li class="option-value">';
			value_layout += '<p><label class="value-label">'+ $add_value_button.data( 'input-label' ) +'</label> <input name="'+ $add_value_button.data( 'input-name' ) +'[label][{value_index}]" type="text" class="regular-text" value="{value_label}" /></p>';
			value_layout += '<p><label class="value-label">'+ $add_value_button.data( 'input-price' ) +'</label> <input name="'+ $add_value_button.data( 'input-name' ) +'[price][{value_index}]" type="number" class="small-text code" value="{value_price}" />';
			value_layout += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" class="button button-remove value-remove" data-remove-confirm="'+ $add_value_button.data( 'remove-confirm' ) +'">'+ $add_value_button.data( 'remove-label' ) +'</a>';
			value_layout += '</p><hr/></li>';

			// new option values
			$add_value_button.on( 'click wct-click', function( e, value_data ) {
				e.preventDefault();

				// default values
				value_data = $.extend( { 
					index: '', 
					label: '', 
					price: 0.0 
				}, value_data );

				// add to option values list
				$option_values.append(
						// fill index
						value_layout.replace( /{value_index}/g, value_data.index )
						// fill label
						.replace( /{value_label}/g, value_data.label )
						// fill price
						.replace( /{value_price}/g, value_data.price )
				);
			} );

			if ( typeof data === 'undefined' )
				return false;

			// check predefined values
			if( typeof data.values.label === 'object' ) {
				// loop through labels
				$.each( data.values.label, function( value_index, value_label ) {
					// check price if set first
					if( typeof data.values.price[value_index] !== 'undefined' ) {
						// trigger click with value data
						$add_value_button.trigger( 'wct-click', { 
							index: value_index, 
							label: value_label, 
							price: data.values.price[value_index] 
						} );
					}
				} );
			}

			// clear quote escaping
			$new_item.find( 'textarea' ).val( data.desc.replace( /\\'/g, "'" ) );
		} ).repeatable_item();

		// option value remove
		$options.on( 'click', '.option-values .value-remove', function( e ) {
			e.preventDefault();
			if ( confirm( $( this ).data( 'remove-confirm' ) ) ) {
				$( this ).parent().parent().remove();
			}
		} );

		// media librery buttons
		$options.on( 'click', '.media-button', function( e ) {
			e.preventDefault();
			var $this = $( this );

			// close frame it open
			if ( typeof file_frame != 'undefined' ) {
				file_frame.close();
			}

			// create and open new file frame
			file_frame = wp.media( {
				title: $this.data( 'frame-title' ),
				library: { type: 'image' }
			} );

			// callback for selected image
			file_frame.on( 'select', function() {
				var selection = file_frame.state().get( 'selection' );
				var selected = selection.first().toJSON().url;
				$this.parent().find( '.code' ).val( selected );
			} );

			// open file frame
			file_frame.open();
		} );
	} );
} )( window );