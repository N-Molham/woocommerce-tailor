/**
 * Measurements
 */
( function( window ) {
	jQuery( function( $ ) {
		$account_details = $( '#account-details' );
		if ( $account_details.length ) {
			// gender value holder
			var selected_gender = '',
				$measures = $account_details.find( '.measures-inputs' ),
				$measures_inputs = $measures.find( '.inputs-holder' ),
				$img_loading = $measures.find( '.loading' ),
				last_measure_key = '',
				$instructions_img = $measures.find( '.measure-img' ),
				$instructions = $measures.find( '.instructions' );

			// gender change
			$account_details.find( 'input[name=account_gender]' ).on( 'change wct-change', function( e ) {
				// save selected
				selected_gender = e.target.value;

				// show all
				$measures_inputs.show()
								// filter the unwanted fields to hide
								.filter( ':not([data-gender~="'+ selected_gender +'"])' ).hide();

				// reset instructions
				$instructions_img.attr( 'src', $instructions_img.data( 'default' ) );
				$instructions.css( 'display', 'none' );
			} ).filter( ':checked' ).trigger( 'wct-change' );

			// unit converting
			$measures_inputs.find( '.input-text' ).on( 'keyup focusout', function() {
				var $this = $( this ),
					$other_input, converted_value;

				if ( $this.is( '.input-cm' ) ) {
					// convert into inch
					$other_input = $this.parent().find( '.input-inches' );
					converted_value = parseFloat( $this.val() ) / 2.54;
				} else {
					// convert into cm
					$other_input = $this.parent().find( '.input-cm' );
					converted_value = parseFloat( $this.val() ) * 2.54;
				}

				// update other input
				$other_input.val( round( isNaN( converted_value ) ? 0 : converted_value, 2 ) );
			} ).on( 'focusin', function( e ) {
				var $this = $( this ).parent(),
					measure_key = $this.data( 'key' );

				// not the same key
				if ( last_measure_key !== measure_key ) {
					// instructions image
					$img_loading.css( 'display', 'block' );
					$instructions_img.attr( 'src', wct_measures.measure_url + selected_gender + '_' + measure_key + '.jpg' ).on( 'load', function() {
						$img_loading.css( 'display', 'none' );
					} );

					// instructions text content
					$instructions.css( 'display', 'block' ).find( '.content-holder' ).text( $this.data( 'instructions' ) );

					// catch last one
					last_measure_key = measure_key;
				}
			} );
		}
	} );
} )( window );