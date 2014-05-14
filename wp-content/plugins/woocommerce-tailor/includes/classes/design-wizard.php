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
	 * Constructor
	 */
	public function __construct()
	{
		
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






