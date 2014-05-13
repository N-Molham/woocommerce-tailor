/**
 * Design Wizard
 */
( function( window ) {
	jQuery( function( $ ) {
		// chosen select element
		$( '.chosen_select' ).chosen();

		// repeatable item
		$( '.repeatable' ).repeatable_item();
	} );
} )( window );