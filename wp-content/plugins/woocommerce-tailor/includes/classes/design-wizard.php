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
	 * Wizard settings
	 * 
	 * @var array
	 */
	protected static $settings;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// enqueues
		add_action( 'template_redirect', array( &$this, 'enqueues' ) );

		// register wizard shortcode
		add_shortcode( self::SHORTCODE, array( &$this, 'layout_render' ) );

		// specific meta query override
		add_filter( 'woocommerce_tailor_design_wizard_meta_query', array( &$this, 'meta_query_filter_max_price' ), 10, 3 );

		// specific filter option label override
		add_filter( 'woocommerce_tailor_design_wizard_option_label', array( &$this, 'filter_options_label_max_price_label' ), 10, 3 );
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

		// wrapper start
		$out = '<div id="wct-design-wizard">';

		// step one
		$out .= '<h3>'. __( 'Choose your favorite fabric', WCT_DOMAIN ) .'</h3>';

		// step one content start
		$out .= '<div class="wizard-step wct-products">';

		// loading
		$out .= '<div class="loading"><div class="loader">'. __( 'Loading', WCT_DOMAIN ) .'</div></div>';

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
		$out .= '<div class="woocommerce"><p class="woocommerce-error error-no-fabric hidden">'. __( 'Please select a fabric first.', WCT_DOMAIN ) .'</p></div>';

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
	
				// image link
				$out .= '<a href="'. esc_attr( wp_get_attachment_url( get_post_thumbnail_id( $product->id ) ) ) .'" title="'. esc_attr( $product->post->post_title ) .'" class="lightbox" data-rel="prettyPhoto">';
				if ( has_post_thumbnail( $product->id ) )
					$out .= get_the_post_thumbnail( $product->id, 'shop_catalog' );
				elseif ( wc_placeholder_img_src() )
					$out .= wc_placeholder_img( 'shop_catalog' );
				$out .= '</a>';
	
				// product title
				$out .= '<h3>'. apply_filters( 'the_title', $product->post->post_title, $product->id ) .'</h3>';
	
				// product short description
				$out .= '<p class="excerpt">'. apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $product->post->post_excerpt ) ) .'</p>';
	
				// price
				$out .= '<span class="price">'. $product->get_price_html() .'</span>';
	
				// select button
				$out .= '<a href="#" rel="nofollow" class="button select-button">'. __( 'Select', WCT_DOMAIN ) .'</a>';
				$out .= '<input type="radio" name="wct_wizard[fabric]" class="button" value="'. $product->id .'" />';
	
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
		$out .= '</div>'; // .wct-products

		// step two
		$out .= '<h3>'. __( 'Select your shirt\'s characteristics', WCT_DOMAIN ) .'</h3>';
		$out .= '<div class="wizard-step">step to</div>';

		// step three
		$out .= '<h3>'. __( 'Measure Up', WCT_DOMAIN ) .'</h3>';
		$out .= '<div class="wizard-step">step three</div>';

		// wrapper end
		$out .= '</div>';

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
		if ( is_null( self::$settings ) )
		{
			// get option
			self::$settings = get_option( 'wc_tailor_design_wizard' );
			if ( false === self::$settings )
			{
				// default value
				self::$settings = $defaults;

				// set option
				add_option( 'wc_tailor_design_wizard', self::$settings, '', 'no' );
			}
		}

		// filtered
		self::$settings = apply_filters( 'woocommerce_tailor_design_wizard_settings', wp_parse_args( self::$settings, $defaults ) );
		return $return_object ? (object) self::$settings : self::$settings;
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
}






