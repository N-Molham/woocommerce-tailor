/**
 * Shirt Charactristics
 */
( function( window ) {
	jQuery( function( $ ) {
		// repeatable item
		var $repeatable = $( '.repeatable' ),
			file_frame;

		if ( $repeatable.length )
			$repeatable.repeatable_item();

		// media librery buttons
		$repeatable.on( 'click', '.media-button', function( e ) {
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