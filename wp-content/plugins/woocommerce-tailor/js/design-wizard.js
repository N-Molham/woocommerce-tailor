/**
 * Design Wizard
 */
( function( window ) {
	jQuery( function( $ ) {
		// wizard init
		var $viewport = $( 'body, html' ),
			$wizard = $( '#wct-design-wizard' ).steps( {
			headerTag: 'h3',
			bodyTag: 'div.wizard-step',
			enableKeyNavigation: false,
			enablePagination: true,
			labels: wct_design_wizard.wizard_labels,
			// before changing to next step
			onStepChanging: function( e, current_step, nex_step ) {
				switch( current_step ) {
					// step one
					case 0:
						// not product selected
						var $error = $products_step.find( '.error-no-fabric' ).addClass( 'hidden' );
						if ( $products_wrapper.find( 'input[type=radio]:checked' ).length != 1 ) {
							$error.removeClass( 'hidden' );
							return false;
						}
						break;

					// step two
					case 1:
						var $error = $shirt_characters_step.find( '.error-characters' ).addClass( 'hidden' ),
							show_error = false;

						// check visible options
						$characters_options.filter( ':visible' ).each( function() {
							// checked ones
							if( $( this ).find( 'input:checked' ).length != 1 ) {
								show_error = true;
							}
						} );

						if ( show_error ) {
							$error.removeClass( 'hidden' );
							return false;
						}
						break;
				}

				// continue
				return true;
			}
		} );

		// step one
		// products step
		var $products_step = $wizard.find( '.wct-products' ),
			// loading overlay
			$loading = $products_step.find( '.loading' ),
			// filters container
			$product_filters = $products_step.find( '.product-filters' ),
			// filters buttons
			$product_filter_buttons = $product_filters.find( '.button' ),
			// filter run button
			$product_filter_run_button = $product_filter_buttons.filter( '.filter-button' ),
			// filters select elements
			$product_filter_options = $product_filters.find( '.filter-options' ),
			// products list wrapper
			$products_wrapper = $products_step.find( '.products-wrapper' ),
			// current selected product button
			$selected_product_button = null;

		// products filtering options
		$product_filter_options.on( 'change', function( e ) {
			// show filters buttons
			$product_filter_buttons.removeClass( 'invisible' );

			// update filter button URL link
			$product_filter_run_button.attr( 'href', update_query_value( $product_filter_run_button.attr( 'href' ), e.currentTarget.name, e.currentTarget.value ) )
				// trigger click
				.trigger( 'wct-click' );
		} );

		// products filtering button
		$product_filter_buttons.on( 'click wct-click', function( e ) {
			e.preventDefault();

			// load products
			load_products_page( e.currentTarget.href );

			// if clear button
			if ( $( this ).hasClass( 'clear-button' ) ) {
				// hide buttons
				$product_filter_buttons.addClass( 'invisible' );

				// reset run button
				$product_filter_run_button.attr( 'href', this.href );

				// reset filter options
				$product_filter_options.val( 'none' );
			}
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

		// load next products page
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
				show_title: true,
				horizontal_padding: 20,
				opacity: 0.8,
				deeplinking: false
			} );
		};
		lightbox_setup();

		// step two
		var $shirt_characters_step = $wizard.find( '.wc-shirt-characters' ),
			$user_gender = $shirt_characters_step.find( '.user-gender' ),
			$selected_gender = $user_gender.filter( ':checked' );
			$characters_options = $shirt_characters_step.find( '.character-option' );

		// gender change
		$user_gender.on( 'change wct-change', function( e ) {
			// hide all characters
			$characters_options.css( 'display', 'none' )
				// show only selected gender related
				.filter( '.gender-'+ e.currentTarget.value ).css( 'display', 'block' );
		} );

		// default characters visible ?
		if ( $selected_gender.length ) {
			// trigger pre-selected gender
			$selected_gender.trigger( 'wct-change' );
		} else {
			// trigger first option by default
			$user_gender.filter( ':first' ).attr( 'checked', true ).trigger( 'wct-change' );
		}

	} );
} )( window );