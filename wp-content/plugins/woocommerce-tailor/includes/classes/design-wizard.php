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
	 * Constructor
	 */
	public function __construct()
	{
		// enqueues
		add_action( 'template_redirect', array( &$this, 'enqueues' ) );

		// register wizard shortcode
		add_shortcode( self::SHORTCODE, array( &$this, 'layout_render' ) );
	}

	/**
	 * Wizard Layout render
	 * 
	 * @return string
	 */
	public function layout_render()
	{
		$settings = $this->get_settings();

		// wrapper start
		$out = '<div id="wct-design-wizard">';

		// step one
		$out .= '<h3>'. __( 'Choose your favorite fabric', WCT_DOMAIN ) .'</h3>';

		// step one content start
		$out .= '<div class="wizard-step wct-products"><div class="woocommerce columns-'. $settings['columns'] .'"><ul class="products">';

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

		// products query
		$query = new WP_Query( $query_args );

		// product class wrapper
		$products = array_map( 'get_product', $query->posts ); 

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
			$out .= '<h3>'. get_the_title( $product->id ) .'</h3>';

			// price
			$out .= '<span class="price">'. $product->get_price_html() .'</span>';

			// select button
			$out .= '<a href="#" rel="nofollow" data-product="'. $product->id .'" class="button">'. __( 'Select', WCT_DOMAIN ) .'</a>';

			// product item end
			$out .= '</li>';
		}
		$out .= '</ul>';

		// step one content end
		$out .= '</div></div>';

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
					'labels' => array (
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
				'filters' => array (
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
				),
		);

		// get option
		$design_wizard = get_option( 'wc_tailor_design_wizard' );
		if ( false === $design_wizard )
		{
			// default value
			$design_wizard = $defaults;

			// set option
			add_option( 'wc_tailor_design_wizard', $design_wizard, '', 'no' );
		}

		// filtered
		$design_wizard = apply_filters( 'woocommerce_tailor_design_wizard_settings', wp_parse_args( $design_wizard, $defaults ) );
		return $return_object ? (object) $design_wizard : $design_wizard;
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
		) );
	}
}






