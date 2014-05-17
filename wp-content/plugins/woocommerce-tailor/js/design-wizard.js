/**
 * Design Wizard
 */
( function( window ) {
	jQuery( function( $ ) {

		// inits
		var wizard_has_error = false,
			// wizard global error holder
			$wizard_ajax_errors = $( '#wct-ajax-errors' );

		// wizard events
		var wizard_events = {
				// before changing to next step
				before_next_step: function( e, current_step, next_step ) { 
					// skip going back
					if ( !isNaN( next_step ) && current_step > next_step )
						return true;

					// witch step
					switch( current_step ) {
					// step one
					case 0:
						wizard_has_error = false;
						$wizard_error = $( '.wizard-errors .error-no-fabric' ).addClass( 'hidden' );

						// check if there is a product selected
						if ( $products_wrapper.find( 'input[type=radio]:checked' ).length != 1 ) {
							$wizard_error.removeClass( 'hidden' );
							return false;
						}
						break;

					// step two
					case 1:
					// step three
					case 2:
						// which error message
						if ( current_step == 1 ) {
							// step two
							$wizard_error = $( '.wizard-errors .error-characters' );
						} else {
							// step three
							$wizard_error = $( '.wizard-errors .error-body-profile' );
						}

						$wizard_error.addClass( 'hidden' );
						wizard_has_error = false;

						// check visible options
						$gender_related_fields.filter( ':visible' ).each( function() {
							// checked ones
							if( $( this ).find( 'input:checked' ).length != 1 ) {
								wizard_has_error = true;
							}
						} );

						// step three text fields
						if ( current_step == 2 ) {
							$( '.body-profile-step .input-text' ).each( function( index, element ) {
								element.value = trim( element.value );
								if ( !element.value.length || isNaN( element.value ) ) {
									wizard_has_error = true;
								}
							} );
						}

						if ( wizard_has_error ) {
							$wizard_error.removeClass( 'hidden' );
							return false;
						}
						break;
					}

					// continue
					return true;
				},
				// wizard finished
				on_finish: function ( e, current_index ) {
					// loading overlay view
					$loading.css( 'display', 'block' );

					// clear previous errors
					$wizard_ajax_errors.empty();

					$.post( wct_design_wizard.ajax_url, $( '#wizard-form' ).serialize(), function( result, status, response ) {
						// hide loading overlay
						$loading.css( 'display', 'none' );

						if ( result.status ) {
							// redirect to target
							location.href = result.data;
						} else {
							// show errors if there are
							$wizard_ajax_errors.html( result.error.message );
						}
					}, 'json' );
				}
		};

		// wizard start
		var $wizard = $( '#wct-design-wizard' ).steps( {
			//startIndex: 2, // test only
			headerTag: 'h3',
			bodyTag: 'div.wizard-step',
			enableKeyNavigation: false,
			enablePagination: true,
			enableAllSteps: true,
			labels: wct_design_wizard.wizard_labels,
			// before changing to next step
			onStepChanging: wizard_events.before_next_step,
			// before finishing the last step
			onFinishing: wizard_events.before_next_step,
			// wizard finished
			onFinished: wizard_events.on_finish
		} );

		// step one
		// products step
		var $products_step = $wizard.find( '.wct-products' ),
			// loading overlay
			$loading = $wizard.find( '.loading' ),
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
						animate_scroll_to( $wizard, 20 );
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

		// step two & three
		var $shirt_characters_step = $wizard.find( '.wc-shirt-characters' ),
			// user gender field
			$user_gender = $shirt_characters_step.find( '.user-gender' ),
			// already selected gender option
			$preselected_gender = $user_gender.filter( ':checked' ),
			// currently selected gender key name
			selected_gender = '',
			// user gender related fields that change based on gender selected option
			$gender_related_fields = $wizard.find( '.wc-shirt-characters .character-option, .body-profile-step .form-row[class*=gender]' );

		// gender change
		$user_gender.on( 'change wct-change', function( e ) {
			selected_gender = e.currentTarget.value;
			// hide all characters
			$gender_related_fields.css( 'display', 'none' )
				// show only selected gender related
				.filter( '.gender-'+ selected_gender ).css( 'display', 'block' );
		} );

		// default characters visible ?
		if ( $preselected_gender.length ) {
			// trigger pre-selected gender
			selected_gender = $preselected_gender.trigger( 'wct-change' ).val();
		} else {
			// trigger first option by default
			selected_gender = $user_gender.filter( ':first' ).attr( 'checked', true ).trigger( 'wct-change' ).val();
		}

	} );
} )( window );