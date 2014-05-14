/**
 * Design Wizard
 */
( function( window ) {
	jQuery( function( $ ) {
		// wizard init
		$wizard = $( '#wct-design-wizard' ).steps( {
			headerTag: 'h3',
			bodyTag: 'div.wizard-step',
			enableKeyNavigation: false,
			enablePagination: true,
			labels: wct_design_wizard.labels
		} );

		
		// lightbox
		function lightbox_setup() {
			$wizard.find( 'a.lightbox').prettyPhoto( {
				hook: 'data-rel',
				social_tools: false,
				theme: 'pp_woocommerce',
				show_title: false,
				horizontal_padding: 20,
				opacity: 0.8,
				deeplinking: false
			} );
		};
		lightbox_setup();
	} );
} )( window );