/**
 * Cart
 */
( function( window ) {
	jQuery( function( $ ) {
		// cart fees
		var $fees = $( '#cart-fees' ).find( '.design-fee' );

		// fees highlight
		$( '#cart-table' ).find( '.button-fees' ).on( 'click', function( e ) {
			// scroll to target
			animate_scroll_to ( 
				// remove pre-highlighted 
				$fees.removeClass( 'fee-highlight' )
					// highlight only related fees
					.filter( '.product-'+ e.currentTarget.dataset.product ).addClass( 'fee-highlight' )
			);
		} );
	} );
} )( window );