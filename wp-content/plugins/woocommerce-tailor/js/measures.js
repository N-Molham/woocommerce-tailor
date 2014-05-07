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
				$measures_inputs = $measures.find( '.inputs-holder' );

			// gender change
			$account_details.find( 'input[name=account_gender]' ).on( 'change wct-change', function( e ) {
				// save selected
				selected_gender = e.target.value;

				// 
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
				$other_input.val( round( converted_value, 2 ) );
			} );
		}
		// cm to inch = cm / 2.54
		// inch to cm = inch * 2.54
	} );
} )( window );