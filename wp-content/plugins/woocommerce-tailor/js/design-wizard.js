/**
 * Design Wizard
 */
( function( window ) {
	jQuery( function( $ ) {
		// wizard init
		var $wizard = $( '#wct-design-wizard' ).steps( {
			headerTag: 'h3',
			bodyTag: 'div.wizard-step',
			enableKeyNavigation: false,
			enablePagination: true,
			labels: wct_design_wizard.wizard_labels
		} );

		var $loading = $wizard.find( '.wct-products .loading' ),
			$product_filters = $wizard.find( '.product-filters' ),
			$product_filter_button = $wizard.find( '.filter-button' ),
			$products_wrapper = $wizard.find( '.products-wrapper' ),
			$viewport = $( 'body, html' ),
			$selected_product_button = null;

		// products filtering options
		$product_filters.find( '.filter-options' ).on( 'change', function( e ){
			// update filter button URL link
			$product_filter_button.attr( 'href', update_query_value( $product_filter_button.attr( 'href' ), e.currentTarget.name, e.currentTarget.value ) )
			// trigger click
			.trigger( 'wct-click' );
		} );

		// products filtering button
		$product_filter_button.on( 'click wct-click', function( e ) {
			e.preventDefault();

			// load products
			load_products_page( e.currentTarget.href );
		} );

		// selecting product
		$products_wrapper.on( 'click', '.products .select-button', function( e ) {
			e.preventDefault();

			// clear selected product
			if ( $selected_product_button )
				$selected_product_button.text( wct_design_wizard.product_labels.select ).removeClass( 'added' ).next().attr( 'checked', false );

			// mark as checked
			$selected_product_button = $( this ).addClass( 'added' ).text( wct_design_wizard.product_labels.selected );
			$selected_product_button.next().attr( 'checked', true );
		} );

		// products paging
		$products_wrapper.on( 'click', '.woocommerce-pagination a.page-numbers', function( e ) {
			e.preventDefault();

			// load products
			load_products_page( e.currentTarget.href );
		} );

		function load_products_page( url ) {
			// show loading
			$loading.css( 'display', 'block' );

			// get new page
			$.get( url, function( response, status, request ) {
				// hide loading
				$loading.css( 'display', 'none' );

				// check response status first
				if ( status == 'success' && request.status == 200 ) {
					var $new_content = $( response ).find( '#wct-design-wizard .wct-products .products-wrapper' );
					if ( $new_content.length ) {
						// fill in new content
						$products_wrapper.html( $new_content.html() );
						lightbox_setup();

						// scroll
						$viewport.animate( {
							scrollTop: $wizard.offset().top - 20
						}, 500 );
					}
				}
			} );
		}

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