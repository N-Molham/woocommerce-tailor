/**
 * Body Profile
 */
( function( window ) {
	jQuery( function( $ ) {
		$account_details = $( '#account-details' );
		if ( $account_details.length ) {
			// gender value holder
			var selected_gender = '',
				$profile_inputs = $account_details.find( '.body-profile' );

			// gender change
			var $genders = $account_details.find( 'input[name=account_gender]' ).on( 'change profile-change', function( e ) {
				// save selected
				selected_gender = e.target.value;

				// show all
				$profile_inputs.show()
								// filter the unwanted fields to hide
								.filter( ':not(.gender-'+ selected_gender +')' ).hide();
			} );

			// trigger selected/first gender
			if ( $genders.filter( ':checked' ).length )
				$genders.filter( ':checked' ).trigger( 'profile-change' );
			else
				$genders.filter( ':first' ).trigger( 'profile-change' );

			// image fancybox ( lightbox )
			$account_details.find( 'a.bp-image' ).fancybox( {
				'overlayShow' : false,
				'transitionIn' : 'elastic',
				'transitionOut' : 'elastic'
			} );
		}
	} );
} )( window );