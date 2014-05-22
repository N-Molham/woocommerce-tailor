<?php
/**
 * Design Wizard
 * 
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Tailor_Design_Wizard
{
	/**
	 * Wizard Shortcode key name
	 * 
	 * @var string
	 */
	const SHORTCODE = 'woocommerce_tailor_design_wizard';

	/**
	 * Wizard input fields names prefix
	 * 
	 * @var string
	 */
	const INPUTS_PREFIX = 'wct_wizard';

	/**
	 * AJAX action name
	 * 
	 * @var string
	 */
	const AJAX_HANDLER_ACTION = 'wct_add_to_cart';

	/**
	 * AJAX action nonce key
	 * 
	 * @var string
	 */
	const AJAX_HANDLER_NONCE = 'wct_wizard_add_to_cart';

	/**
	 * Session order data storage key
	 * 
	 * @var string
	 */
	const SESSION_ORDER_KEY = 'wct_order_info';

	/**
	 * Wizard settings
	 * 
	 * @var array
	 */
	protected $settings;

	/**
	 * Designed item order information
	 * 
	 * @var array
	 */
	protected static $_order_info;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// remove cross sell from cart
		remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );

		// enqueues
		add_action( 'template_redirect', array( &$this, 'enqueues' ) );

		// register wizard shortcode
		add_shortcode( self::SHORTCODE, array( &$this, 'layout_render' ) );

		// specific meta query override
		add_filter( 'woocommerce_tailor_design_wizard_meta_query', array( &$this, 'meta_query_filter_max_price' ), 10, 3 );

		// specific filter option label override
		add_filter( 'woocommerce_tailor_design_wizard_option_label', array( &$this, 'filter_options_label_max_price_label' ), 10, 3 );

		// validate wizard values action
		add_filter( 'woocommerce_tailor_design_wizard_validate', array( &$this, 'validate_field_values' ) );

		// remove designed item from cart
		add_action( 'woocommerce_before_cart_item_quantity_zero', array( &$this, 'remove_item_from_cart' ) );

		// AJAX action handler
		add_action( 'wp_ajax_'. self::AJAX_HANDLER_ACTION, array( &$this, 'add_to_cart_handler' ) );
		add_action( 'wp_ajax_nopriv_'. self::AJAX_HANDLER_ACTION, array( &$this, 'add_to_cart_handler' ) );

		// cart additional fees
		add_action( 'woocommerce_cart_calculate_fees', array( &$this, 'cart_additional_fees' ) );

		// override cart product data
		add_filter( 'woocommerce_cart_item_product', array( &$this, 'override_cart_item_data' ), 10, 3 );

		// override cart thumbnail
		add_filter( 'woocommerce_cart_item_thumbnail', array( &$this, 'woocommerce_cart_item_thumbnail' ), 10, 3 );

		// save order data
		add_action( 'woocommerce_add_order_item_meta', array( &$this, 'save_order_data_meta' ), 10, 3 );

		// override order details item data
		add_filter( 'woocommerce_order_item_product', array( &$this, 'override_order_item_data' ), 10, 2 );

		// override order details item name
		add_filter( 'woocommerce_order_item_name', array( &$this, 'override_order_item_name' ), 10, 2 );
	}

	/**
	 * Save order data in order meta
	 * 
	 * @param integer $item_id
	 * @param mixed $values
	 * @param string $cart_item_key
	 * @return void
	 */
	public function save_order_data_meta( $item_id, $values, $cart_item_key )
	{
		$order_info = self::get_order_data();
		if ( $cart_item_key === $order_info['cart_item_key'] )
		{
			// mark as designed item
			wc_add_order_item_meta( $item_id, '_wct_designed_item', 'yes' );

			// save in meta
			wc_add_order_item_meta( $item_id, '_wct_order_info', $order_info );

			// clear order data from session
			WC()->session->__unset( self::SESSION_ORDER_KEY );
		}
	}

	/**
	 * Override order item name
	 * 
	 * @param string $item_name
	 * @param array $item
	 * @return string
	 */
	public function override_order_item_name( $item_name, $item )
	{
		if ( isset( $item['item_meta'] ) && isset( $item['item_meta']['_wct_designed_item'] ) && 'yes' === $item['item_meta']['_wct_designed_item'][0] ) 
		{
			// remove link
			return sprintf( __( 'Designed Item\'s Fabric : <strong>%s</strong>', WCT_DOMAIN ), $item_name );
		}

		return $item_name;
	}

	/**
	 * Override order item data
	 * 
	 * @param WC_Product $product
	 * @param array $item
	 * @return WC_Product
	 */
	public function override_order_item_data( $product, $item )
	{
		if ( isset( $item['item_meta'] ) && isset( $item['item_meta']['_wct_designed_item'] ) && 'yes' === $item['item_meta']['_wct_designed_item'][0] ) 
		{
			// remove link
			$product->visibility = 'hidden';
		}

		return $product;
	}

	/**
	 * Override cart item data
	 * 
	 * @param WC_Product $product
	 * @param array $item
	 * @param string $cart_item_key
	 * @return WC_Product
	 */
	public function override_cart_item_data( $product, $item, $cart_item_key = false )
	{
		$order_info = self::get_order_data();
		if ( 
			$cart_item_key === $order_info['cart_item_key'] || 
			( isset( $item['item_meta'] ) && isset( $item['item_meta']['_wct_designed_item'] ) && 'yes' === $item['item_meta']['_wct_designed_item'][0] ) 
		)
		{
			// remove link
			$product->visibility = 'hidden';

			// change title
			$product->post->post_title = sprintf( __( 'Designed Item\'s Fabric : <strong>%s</strong>', WCT_DOMAIN ), $product->get_title() );
		}

		return $product;
	}

	/**
	 * Override cart item data
	 * 
	 * @param string $thumbnail
	 * @param array $cart_item
	 * @param string $cart_item_key
	 * @return string
	 */
	public function woocommerce_cart_item_thumbnail( $thumbnail, $cart_item, $cart_item_key )
	{
		$order_info = self::get_order_data();
		if ( $cart_item_key === $order_info['cart_item_key'] )
		{
			// add fancybox lightbox to full size fabric image
			return '<a href="'. esc_attr( wp_get_attachment_url( get_post_thumbnail_id( $cart_item['data']->id ) ) ) .'" title="'. esc_attr( $cart_item['data']->post_title ) .'" class="fancybox">'. $thumbnail .'</a>';
		}

		// return original
		return $thumbnail;
	}

	/**
	 * Remove designed item from cart
	 * 
	 * @param string $cart_item_key
	 * @return void
	 */
	public function remove_item_from_cart( $cart_item_key )
	{
		// skip empty cart
		if ( !WC()->cart->cart_contents_count )
			return;

		// order info
		$order_info = self::get_order_data();

		// skip non related orders
		if ( $cart_item_key !== $order_info['cart_item_key'] )
			return;

		// clear session
		WC()->session->__unset( self::SESSION_ORDER_KEY );
	}

	/**
	 * Add additional fees to cart
	 * 
	 * @param WC_Cart $cart
	 * @return void
	 */
	public function cart_additional_fees( $cart )
	{
		// skip empty cart
		if ( !$cart->cart_contents_count )
			return;

		// order info
		$order_info = self::get_order_data();

		// skip non related orders
		if ( !isset( $cart->cart_contents[ $order_info['cart_item_key'] ] ) )
			return;

		$cart_item = $cart->cart_contents[ $order_info['cart_item_key'] ];
		$fee_data = null;

		// additional fees
		foreach ( $order_info['shirt-characters'] as $character_index => $selected_character )
		{
			$fee_data = array ( 
					'name' => $selected_character['label'] .' : '. $selected_character['value_label'],
					'amount' => $cart_item['quantity'] * $selected_character['value_price'],
					'taxable' => false,
					'tax_class' => '',
			);

			$fee_data = apply_filters( 'woocommerce_tailor_design_wizard_cart_add_data', $fee_data, $selected_character, $cart_item );
			if ( false === $fee_data )
				continue;

			$cart->add_fee( $fee_data['name'], $fee_data['amount'], $fee_data['taxable'], $fee_data['tax_class'] );
		}
	}

	/**
	 * AJAX action handler
	 * 
	 * @return void
	 */
	public function add_to_cart_handler()
	{
		check_ajax_referer( self::AJAX_HANDLER_NONCE, 'nonce' );

		$no_errors = isset( $_REQUEST[ self::INPUTS_PREFIX ] );

		// check values
		if( $no_errors )
		{
			// sanitize values
			$wizard_values = WC_Tailor_Utiles::array_map_recursive( $_REQUEST[ self::INPUTS_PREFIX ], 'wc_clean' );

			// default values
			$wizard_values = wp_parse_args( $wizard_values, array ( 
					'fabric' => 0,
					'gender' => '',
					'body_profile' => array(),
					'shirt-characters' => array( 'male' => array(), 'female' => array() ),
			) );

			// validate values
			$wizard_values = apply_filters( 'woocommerce_tailor_design_wizard_validate', $wizard_values );

			// check validation errors
			$no_errors = wc_notice_count( 'error' ) > 0;
		}
		else
			wc_add_notice( __( 'There are missing fields, please try again.', WCT_DOMAIN ), 'error' );

		if ( !$no_errors )
		{
			// clear previous order if there are 
			self::clear_previous_order();

			// add fabric to cart
			$wizard_values['in_cart'] = WC()->cart->add_to_cart( $wizard_values['fabric'], 1, '', '', $wizard_values );
		}
		else
		{
			// buffer error notices
			ob_start();
			wc_print_notices();
			wct_ajax_error( 'error', ob_get_clean() );
		}

		// related cart content only
		$cart_content = array_filter( WC()->cart->cart_contents, function ( $cart_item ) use ( $wizard_values ) {
			return $cart_item['product_id'] == $wizard_values['fabric'];
		} );

		// save cart item key
		$wizard_values['cart_item_key'] = current( array_keys( $cart_content ) );

		// save order information
		self::set_order_data( $wizard_values );

		// add success message
		wc_add_notice( __( 'Your order added successfully to the cart', WCT_DOMAIN ) );

		// response with cart page url
		wct_ajax_response( WC()->cart->get_cart_url() );
	}

	/**
	 * Validate wizard field values
	 * 
	 * @param array $wizard
	 * @return void
	 */
	public function validate_field_values( $wizard_values )
	{
		// test invalid value
		// $wizard_values['gender'] = 'asdasdas';

		$reuiqred_field_template = __( '%s is required', WCT_DOMAIN );
		$invalid_value_template = __( 'Invalid %s selection.', WCT_DOMAIN );

		// check product ( fabric )
		$wizard_values['fabric'] = (int) $wizard_values['fabric'];
		$fabric = get_product( $wizard_values['fabric'] );

		// is it available to purchase
		if ( !$fabric || false === $fabric->is_purchasable() )
		{
			wc_add_notice( __( 'Sorry, this product cannot be purchased.', WCT_DOMAIN ), 'error' );
			return false;
		}

		// check gender
		if ( !in_array( $wizard_values['gender'], array( 'male', 'female' ) ) )
		{
			wc_add_notice( sprintf( $invalid_value_template, __( 'Gender', WCT_DOMAIN ) ), 'error' );
			return false;
		}

		// check body profile values
		$body_profile_fields = WC_Tailor()->account_updates->get_account_details_by_section( 'body_profile' );
		foreach ( $body_profile_fields as $field_name => $field_args )
		{
			$input_name = str_replace( 'body_profile_', '', $field_name );
			$field_args['label'] = preg_replace( '/<span.+/', '', $field_args['label'] );

			// gender related
			if ( !in_array( $wizard_values['gender'], $field_args['gender'] ) )
			{
				if ( isset( $wizard_values['body_profile'][$input_name] ) )
					unset( $wizard_values['body_profile'][$input_name] );

				continue;
			}

			// is value exists
			if ( !isset( $wizard_values['body_profile'][$input_name] ) )
			{
				wc_add_notice( sprintf( $reuiqred_field_template, $field_args['label'] ), 'error' );
				continue;
			}

			// parse value
			$value = WC_Tailor()->account_updates->parse_field_value( $field_name, $field_args, $wizard_values['body_profile'][$input_name] );
			if ( is_wp_error( $value ) )
			{
				wc_add_notice( $value->get_error_message(), 'error' );
				continue;
			}
		}

		// check shirt characters related to selected gender
		if ( !isset( $wizard_values['shirt-characters'][ $wizard_values['gender'] ] ) )
		{
			wc_add_notice( __( 'Shirt\'s Characteristics options are not selected yet.', WCT_DOMAIN ), 'error' );
			return false;
		}

		// selected characters
		$wizard_values['shirt-characters'] = $wizard_values['shirt-characters'][ $wizard_values['gender'] ];

		// registered ones
		$shirt_characters = $this->get_shirt_charaters();

		foreach ( $shirt_characters[ $wizard_values['gender'] ] as $character_index => $character_info )
		{
			$field_name = 'character-'. $character_index;

			// check existence
			if ( !isset( $wizard_values['shirt-characters'][$field_name] ) )
			{
				wc_add_notice( sprintf( $reuiqred_field_template, $character_info['label'] ), 'error' );
				continue;
			}

			// check value
			$field_value = (int) $wizard_values['shirt-characters'][$field_name];
			if ( !isset( $character_info['values']['price'][$field_value] ) || !isset( $character_info['values']['label'][$field_value] ) )
			{
				wc_add_notice( sprintf( $invalid_value_template, $character_info['label'] ), 'error' );
				continue;
			}

			$wizard_values['shirt-characters'][$field_name] = array (
					'label' => $character_info['label'],
					'value_index' => $field_value,
					'value_label' => $character_info['values']['label'][$field_value],
					'value_price' => (float) $character_info['values']['price'][$field_value],
			);
		}

		return $wizard_values;
	}

	/**
	 * Wizard Layout render
	 * 
	 * @return string
	 */
	public function layout_render()
	{
		global $wpdb;

		$settings = $this->get_settings();
		$page_url = get_permalink();

		// filters
		$filter_labels = $this->get_filters_labels();
		$filter_meta_keys = $this->get_filters_meta_keys();
		$selected_filters = array();
		$has_filters = false;
		$filters_layout = '';

		// products query args
		$query_args = array ( 
				'post_type' => 'product',
				'post_status' => 'publish',
				'ignore_sticky_posts' => 1,
				'posts_per_page' => (int) $settings['per_page'],
				'paged' => get_query_var( 'paged', 1 ),
				'meta_query' => array(
						array (
								'key' => '_visibility',
								'value' => array( 'catalog', 'visible' ),
								'compare' => 'IN'
						)
				),
				'tax_query' => array (
						array (
								'taxonomy' => 'product_cat',
								'terms' => $settings['category'],
								'field' => 'id',
						)
				)
		);

		// filters loop
		foreach ( $settings['filters'] as $filter_name => $filter_data )
		{
			$query_string = 'filter_'. $filter_name;

			// selected value
			$selected_filters[$query_string] = isset( $_GET[$query_string] ) ? wc_clean( $_GET[$query_string] ) : 'none';

			// check value
			if ( 'none' != $selected_filters[$query_string] && isset( $filter_data['options'][ $selected_filters[$query_string] ] ) )
			{
				// add meta query parameters
				$query_args['meta_query'][] = apply_filters( 'woocommerce_tailor_design_wizard_meta_query', array ( 
						'key' => $filter_meta_keys[$filter_name],
						'value' => $selected_filters[$query_string],
						'compare' => html_entity_decode( $filter_data['compare'] ),
				), $filter_name, $filter_data );

				$has_filters = true;
			}

			// filter layout
			$filters_layout .= '<label class="filter">'. $filter_labels[$filter_name] .'&nbsp;&nbsp;';
			$filters_layout .= '<select name="filter_'. $filter_name .'" class="filter-options">';

			// default
			$filters_layout .= '<option value="none">'. __( 'Any', WCT_DOMAIN ) .'</option>';

			foreach ( $filter_data['options'] as $option_index => $option_label )
			{
				// option value
				$filters_layout .= '<option value="'. $option_index .'"';

				// selected value
				$filters_layout .= $option_index == $selected_filters[$query_string] ? ' selected' : '';

				// option label
				$filters_layout .= '>'. apply_filters( 'woocommerce_tailor_design_wizard_option_label', $option_label, $filter_name, $filter_data ) .'</option>';
			}

			// filter layout end
			$filters_layout .= '</select></label>';
		}

		// wc wrapper
		$out = '<div class="woocommerce">';

		// wizard form
		$out .= '<form action="" method="post" id="wizard-form">';

		$out .= '<div id="wct-ajax-errors"></div>';

		// wrapper start
		$out .= '<div id="wct-design-wizard">';

		// loading
		$out .= '<div class="loading"><div class="loader">'. __( 'Loading', WCT_DOMAIN ) .'</div></div>';

		/*********************{{ Step One : Fabric selection }}*********************/

		// step one
		$out .= '<h3>'. __( 'Choose your favorite fabric', WCT_DOMAIN ) .'</h3>';

		// step one content start
		$out .= '<div class="wizard-step wct-products">';

		// before products
		$out .= apply_filters( 'woocommerce_tailor_design_wizard_products_before', '' );

		// product filters
		$out .= '<div class="product-filters">';

		// filters options
		$out .= $filters_layout;

		// filter run button
		$out .= '<a href="'. $page_url .'" class="button filter-button'. ( $has_filters ? '' : ' invisible' ) .'">'. __( 'Filter', WCT_DOMAIN ) .'</a>';

		// filter clean button
		$out .= '&nbsp;&nbsp;&nbsp;<a href="'. $page_url .'" class="button clear-button'. ( $has_filters ? '' : ' invisible' ) .'">'. __( 'CLear', WCT_DOMAIN ) .'</a>';

		// product filters end
		$out .= '</div>';

		// not product selected error
		$out .= '<div class="wizard-errors"><p class="woocommerce-error error-no-fabric hidden">'. __( 'Please select a fabric first.', WCT_DOMAIN ) .'</p></div>';

		// product wrappers
		$out .= '<div class="products-wrapper">';

		// product list
		$out .= '<div class="woocommerce columns-'. $settings['columns'] .'">';

		// products query
		$query = new WP_Query( apply_filters( 'woocommerce_tailor_design_wizard_products_args', $query_args ) );
		// dump_data( $wpdb->last_query );

		// product class wrapper
		$products = array_map( 'get_product', $query->posts ); 

		if ( $query->post_count )
		{
			$out .= '<ul class="products">';
			// products loop
			/* @var $product WC_Product */
			for ( $i = 0; $i < $query->post_count; $i++ )
			{
				$product = &$products[$i];

				// first/last item class
				$classes = array();
				$loop = $i + 1;

				if ( 0 == ( $loop - 1 ) % $settings['columns'] || 1 == $settings['columns'] )
					$classes[] = 'first';

				if ( 0 == $loop % $settings['columns'] )
					$classes[] = 'last';

				// product item start
				$out .= '<li class="'. join( ' ', get_post_class( $classes, $product->id ) ) .'">';

				// product title
				$out .= '<h3>'. apply_filters( 'the_title', $product->post->post_title, $product->id ) .'</h3>';

				// image link
				$out .= '<a href="'. esc_attr( wp_get_attachment_url( get_post_thumbnail_id( $product->id ) ) ) .'" title="'. esc_attr( $product->post->post_title ) .'" class="lightbox" data-rel="prettyPhoto">';
				if ( has_post_thumbnail( $product->id ) )
					$out .= get_the_post_thumbnail( $product->id, 'shop_catalog' );
				elseif ( wc_placeholder_img_src() )
					$out .= wc_placeholder_img( 'shop_catalog' );
				$out .= '</a>';

				// product short description
				$out .= '<div class="excerpt">'. apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $product->post->post_excerpt ) ) .'</div>';

				// price
				$out .= '<span class="price">'. $product->get_price_html() .'</span>';

				// select button
				$out .= '<a href="#" rel="nofollow" class="button select-button">'. __( 'Select', WCT_DOMAIN ) .'</a>';
				$out .= '<input type="radio" name="'. self::INPUTS_PREFIX .'[fabric]" class="button" value="'. $product->id .'" />';

				// product item end
				$out .= '</li>';
			}
			$out .= '</ul>';
		}
		else
		{
			// not match found message
			$out .= '<div class="woocommerce-info">'. __( 'No Match Found', WCT_DOMAIN ) .'</div>';
		}

		// base url with filters
		$big = 999999999;
		$paging_base_url = add_query_arg( $selected_filters, get_pagenum_link( $big ) );
		$paging_base_url = str_replace( $big, '%#%', $paging_base_url );

		// paging list
		$out .= '<nav class="woocommerce-pagination">'. paginate_links( array ( 
				'base' => $paging_base_url,
				'format' => '&paged=%#%',
				'current' => max( 1, get_query_var( 'paged' ) ),
				'total' => $query->max_num_pages,
				'prev_text' => '&larr;',
				'next_text' => '&rarr;',
				'type' => 'list',
				'end_size' => 3,
				'mid_size' => 3
		) ) .'</nav>';

		// step one content end
		$out .= '</div>'; // .columns
		$out .= '</div>'; // .products-wrapper

		// before products
		$out .= apply_filters( 'woocommerce_tailor_design_wizard_products_after', '' );

		$out .= '</div>'; // .wct-products .wizard-step

		/*********************{{ Step Two : Shirt's Characteristics options }}*********************/

		// current logged-in user
		$user = wp_get_current_user();
		$user_gender = $user->exists() ? $user->gender : '';
		if ( '' == $user_gender || empty( $user_gender ) )
			$user_gender = 'male';

		// step two
		$out .= '<h3>'. __( 'Select your shirt\'s characteristics', WCT_DOMAIN ) .'</h3>';
		$out .= '<div class="wizard-step wc-shirt-characters">';

		// before
		$out .= apply_filters( 'woocommerce_tailor_design_wizard_shirt_chars_before', '' );

		// missing selections errors
		$out .= '<div class="wizard-errors"><p class="woocommerce-error error-characters hidden">'. __( 'There are missing options.', WCT_DOMAIN ) .'</p></div>';

		// selecting gender
		$out .= '<div class="input-field"><label class="input-label">'. __( 'Gender', WCT_DOMAIN ) .'</label>';
		$out .= '<p class="input-options">';
		$out .= '<label class="input-option"><input type="radio" name="'. self::INPUTS_PREFIX .'[gender]" value="male" class="user-gender"'. ( 'male' == $user_gender ? ' checked="checked"' : '' ) .' /> '. __( 'Male', WCT_DOMAIN ) .'</label>';
		$out .= '<label class="input-option"><input type="radio" name="'. self::INPUTS_PREFIX .'[gender]" value="female" class="user-gender"'. ( 'female' == $user_gender ? ' checked="checked"' : '' ) .' /> '. __( 'Female', WCT_DOMAIN ) .'</label>';
		$out .= '</p></div><hr/>';

		// loop characters
		$shit_characters = $this->get_shirt_charaters();
		foreach ( $shit_characters as $gender => $gender_characters )
		{
			foreach ( $gender_characters as $charcter_index => $character_data )
			{
				// label
				$out .= '<div class="input-field character-option gender-'. $gender .'">';
				$out .= '<label class="input-label">'. $character_data['label'];

				// picture & description
				$href = "javascript:jQuery.prettyPhoto.open( '{$character_data['picture']}', '{$character_data['label']}', '". esc_sql( $character_data['desc'] ) ."' );";
				$out .= '<br/>( <a href="'. $href .'">'. __( 'Picture', WCT_DOMAIN ) .'</a> )</label>';

				$out .= '<p class="input-options">';
				foreach ( $character_data['values']['label'] as $value_index => $value_label )
				{
					$out .= '<label class="input-option"><input type="radio" name="'. self::INPUTS_PREFIX .'[shirt-characters]['. $gender .'][character-'. $charcter_index .']" value="'. $value_index .'" />'. $value_label .'</label>';
				}
				$out .= '</p></div>';
			}
		}

		// after
		$out .= apply_filters( 'woocommerce_tailor_design_wizard_shirt_chars_after', '' );

		$out .= '</div>'; // .wizard-step.wc-shirt-characters

		/*********************{{ Step Three : Body Profile }}*********************/

		// step three
		$out .= '<h3>'. __( 'Measure Up', WCT_DOMAIN ) .'</h3>';
		$out .= '<div class="wizard-step body-profile-step">';

		// before
		$out .= apply_filters( 'woocommerce_tailor_design_wizard_body_profile_before', '' );

		// missing selections errors
		$out .= '<div class="wizard-errors"><p class="woocommerce-error error-body-profile hidden">'. __( 'There are missing options.', WCT_DOMAIN ) .'</p></div>';

		// body profile fields
		$body_profile_fields = WC_Tailor()->account_updates->get_account_details_by_section( 'body_profile' );

		// fields render
		foreach ( $body_profile_fields as $field_name => $field_args )
		{
			// change inputs names
			$field_args['input_name'] = str_replace( 'body_profile_', self::INPUTS_PREFIX .'[body_profile][', $field_name ) .']';

			// field layout
			$out .= WC_Tailor()->account_updates->render_field_output( $field_name, $field_args, null, $user );
		}

		// after
		$out .= apply_filters( 'woocommerce_tailor_design_wizard_body_profile_after', '' );

		$out .= '</div>'; // .body-profile

		// wizard wrapper end
		$out .= '</div>';

		// hidden inputs
		$out .= '<input type="hidden" name="action" value="'. self::AJAX_HANDLER_ACTION .'" />';
		$out .= wp_nonce_field( self::AJAX_HANDLER_NONCE, 'nonce', false, false );

		// wizard form end
		$out .= '</form></div>';

		// return filtered layout
		return apply_filters( 'woocommerce_tailor_design_wizard_layout', $out );
	}

	/**
	 * Override max price filter value with price number
	 * 
	 * @param array $meta_query
	 * @param string $filter_name
	 * @param array $filter_data
	 * @return array
	 */
	public function meta_query_filter_max_price( $meta_query, $filter_name, $filter_data )
	{
		if ( 'max_price' == $filter_name ) 
		{
			// set value
			$meta_query['value'] = (float) $filter_data['options'][ (int) $meta_query['value'] ];

			// set data type
			$meta_query['type'] = 'numeric';
		}

		return $meta_query;
	}

	/**
	 * Override max price filter select element option label
	 * 
	 * @param string $meta_query
	 * @param string $filter_name
	 * @param array $filter_data
	 * @return string
	 */
	public function filter_options_label_max_price_label( $label, $filter_name, $filter_data )
	{
		if ( 'max_price' == $filter_name ) 
		{
			// price format
			$label = wc_price( $label );
		}

		return $label;
	}

	/**
	 * Wizard enqueues
	 * 
	 * @return void
	 */
	public function enqueues()
	{
		if ( strstr( get_post()->post_content, self::SHORTCODE ) )
		{
			$wc_assets_path = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';

			// prettyPhoto style
			wp_enqueue_style( 'woocommerce_prettyPhoto_css', $wc_assets_path . 'css/prettyPhoto.css' );

			// prettyPhoto plugin
			wp_enqueue_script( 'prettyPhoto', $wc_assets_path . 'js/prettyPhoto/jquery.prettyPhoto.min.js', array( 'jquery' ), '3.1.5', true );

			// wizard script
			wp_enqueue_script( 'wc-design-wizard', WC_TAILOR_URL .'js/design-wizard.js', array( 'jquery-steps' ), false, true );

			// wizard localize
			wp_localize_script( 'wc-design-wizard', 'wct_design_wizard', apply_filters( 'woocommerce_tailor_design_localize', array ( 
					'ajax_url' => admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
					'product_labels' => array (
							'selected' => __( 'Selected', WCT_DOMAIN ),
							'select' => __( 'Select', WCT_DOMAIN ),
					),
					'wizard_labels' => array (
							'cancel' => __( 'Cancel', WCT_DOMAIN ),
							'current' => __( 'current step:', WCT_DOMAIN ),
							'pagination' => __( 'Pagination', WCT_DOMAIN ),
							'finish' => __( 'Finish', WCT_DOMAIN ),
							'next' => __( 'Next', WCT_DOMAIN ),
							'previous' => __( 'Previous', WCT_DOMAIN ),
							'loading' => __( 'Loading ...', WCT_DOMAIN ),
					),
			) ) );
		}
	}

	/**
	 * Get design wizard settings
	 *
	 * @param boolean $return_object
	 * @return array|stdClass
	 */
	public function get_settings( $return_object = false )
	{
		// defaults
		$defaults = array (
				'category' => 0,
				'columns' => 3,
				'per_page' => 6,
				'filters' => self::get_filters_defaults(),
		);

		// check cached first
		if ( is_null( $this->settings ) )
		{
			// get option
			$this->settings = get_option( 'wc_tailor_design_wizard' );
			if ( false === $this->settings )
			{
				// default value
				$this->settings = $defaults;

				// set option
				add_option( 'wc_tailor_design_wizard', $this->settings, '', 'no' );
			}
		}

		// filtered
		$this->settings = apply_filters( 'woocommerce_tailor_design_wizard_settings', wp_parse_args( $this->settings, $defaults ) );
		return $return_object ? (object) $this->settings : $this->settings;
	}

	/**
	 * Get filters defaults
	 * 
	 * @return array
	 */
	public static function get_filters_defaults()
	{
		return array ( 
				'color' => array ( 
						'compare' => '=',
						'options' => array (
								__( 'Black', WCT_DOMAIN ),
								__( 'Blue', WCT_DOMAIN ),
								__( 'Purple', WCT_DOMAIN ),
								__( 'Grey', WCT_DOMAIN ),
								__( 'Light Blue', WCT_DOMAIN ),
								__( 'Navy', WCT_DOMAIN ),
								__( 'Orange', WCT_DOMAIN ),
								__( 'Pink', WCT_DOMAIN ),
								__( 'Red', WCT_DOMAIN ),
								__( 'White', WCT_DOMAIN ),
								__( 'Yellow', WCT_DOMAIN ),
								__( 'Green', WCT_DOMAIN ),
								__( 'Brown', WCT_DOMAIN ),
								__( 'Dark Purple', WCT_DOMAIN ),
								__( 'Aqua', WCT_DOMAIN ),
						),
				),
				'pattern' => array (
						'compare' => '=',
						'options' => array (
								__( 'Check', WCT_DOMAIN ),
								__( 'Plain', WCT_DOMAIN ),
								__( 'Stripe', WCT_DOMAIN ),
						),
				),
				'max_price' => array (
						'compare' => '<=',
						'options' => array (
								'10',
								'20',
								'30',
								'40',
						),
				),
		);
	}

	/**
	 * Clear previous designed order if there are one
	 * 
	 * @return void
	 */
	public static function clear_previous_order()
	{
		$order_info = self::get_order_data();

		if ( !empty( $order_info['cart_item_key'] ) )
		{
			// remove from cart
			WC()->cart->set_quantity( $order_info['cart_item_key'], 0 );

			// destroy session data
			// WC()->session->__unset( self::SESSION_ORDER_KEY );
		}
	}

	/**
	 * Get order ( wizard ) data from session
	 * 
	 * @param boolean $force_session
	 * @return array
	 */
	public static function get_order_data( $force_session = false )
	{
		if ( $force_session || is_null( self::$_order_info ) )
		{
			// get from session
			self::$_order_info = WC()->session->get( self::SESSION_ORDER_KEY, array ( 
					'fabric' => 0,
					'gender' => '',
					'body_profile' => array(),
					'shirt-characters' => array(),
					'in_cart' => false,
					'cart_item_key' => '',
			) );
		}

		// return filters one
		return apply_filters( 'woocommerce_tailor_design_wizard_session_data', self::$_order_info );
	}

	/**
	 * Set order ( wizard ) data from session
	 * 
	 * @param array $order_data
	 * @return void
	 */
	public static function set_order_data( $order_data )
	{
		// set instence variable
		self::$_order_info = $order_data;

		// set session
		WC()->session->set( self::SESSION_ORDER_KEY, $order_data );
	}

	/**
	 * Get filters labels
	 *
	 * @return array
	 */
	public function get_filters_labels()
	{
		return apply_filters( 'woocommerce_tailor_design_wizard_filters_labels', array (
				'color' => __( 'Color', WCT_DOMAIN ),
				'pattern' => __( 'Pattern', WCT_DOMAIN ),
				'max_price' => __( 'Maximum Price', WCT_DOMAIN ),
		) );
	}

	/**
	 * Get filters meta keys
	 *
	 * @return array
	 */
	public function get_filters_meta_keys()
	{
		return apply_filters( 'woocommerce_tailor_design_wizard_filters_meta_keys', array (
				'color' => 'wc_filter_color',
				'pattern' => 'wc_filter_pattern',
				'max_price' => '_price',
		) );
	}

	/**
	 * Get shirt characteristics settings
	 *
	 * @return array
	 */
	public function get_shirt_charaters()
	{
		$defaults = array (
				'male' => array(),
				'female' => array()
		);

		// get option
		$shirt_characters = get_option( 'wc_tailor_shirt_chars' );
		if ( false === $shirt_characters )
		{
			// default value
			$shirt_characters = $defaults;

			// set option
			add_option( 'wc_tailor_shirt_chars', $shirt_characters, '', 'no' );
		}

		return apply_filters( 'woocommerce_tailor_shirt_characteristics', wp_parse_args( $shirt_characters, $defaults ) );
	}
}






