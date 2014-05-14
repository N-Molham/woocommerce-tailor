<?php
/**
 * WP-Admin Meta Boxes
 * 
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Tailor_Meta_Boxes
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		// meta box actions
		add_action( 'save_post', array( &$this, 'save_post_meta_box' ) );

		// Setup meta boxes
		add_action( 'add_meta_boxes', array( &$this, 'setup_meta_boxes' ) );
	}

	/**
	 * 
	 * 
	 * @param int $post_id
	 * @return void
	 */
	public function save_post_meta_box( $post_id )
	{
		// skip quick save
		if ( !isset( $_POST['action'] ) || 'editpost' !== $_POST['action'] )
			return;

		switch ( get_post_type( $post_id ) )
		{
			case 'product':
				// get filters
				if ( (float) phpversion() >= 5.4 )
					$design_wizard_filters = WC_Tailor()->get_design_wizard_settings()['filters'];
				else
					$design_wizard_filters = WC_Tailor()->get_design_wizard_settings( true )->filters;

				// sanitize values
				$selected_values = isset( $_POST['wc_tailor_filters'] ) ? (array) $_POST['wc_tailor_filters'] : array();
				$selected_values = WC_Tailor_Utiles::array_map_recursive( $selected_values, 'intval' );

				// meta keys
				$meta_keys = WC_Tailor()->wizard_filters_meta_keys();

				foreach ( $design_wizard_filters as $filter_name => $filter_data )
				{
					// clean old values
					delete_post_meta( $post_id, $meta_keys[$filter_name] );

					// check filter
					if ( !isset( $selected_values[$filter_name] ) )
						continue;

					// add values
					foreach ( $selected_values[$filter_name] as $value )
					{
						// check value
						if ( !isset( $filter_data['options'][$value] ) )
							continue;

						// add value to meta
						add_post_meta( $post_id, $meta_keys[$filter_name], $value );
					}
				}
				break;
		}
	}

	/**
	 * Meta boxes setup
	 * 
	 * @param string $post_type
	 * @hook add_meta_boxes
	 * @return void
	 */
	public function setup_meta_boxes( $post_type = '' )
	{
		switch ( $post_type )
		{
			// product meta boxes
			case 'product':
				// enqueues
				wp_enqueue_style( 'chosen-styles' );
				wp_enqueue_style( 'wct-admin-style' );
				wp_enqueue_script( 'chosen' );

				// design wizard filters relation
				add_meta_box( 'wc_tailor_wizard_relation', __( 'Product Filter Options', WCT_DOMAIN ), array( &$this, 'meta_box_layout' ), $post_type, 'normal', 'high' );
				break;
		}
	}

	/**
	 * Load meta box layout file
	 * 
	 * @param WP_Post $post
	 * @param array $meta_box
	 * @return void
	 */
	public function meta_box_layout( $post, $meta_box )
	{
		// build page path
		$page_path = WC_TAILOR_DIR .'admin-pages/meta-box-'. str_replace( array( 'wc_tailor_', '_' ), array( '', '-' ), $meta_box['id'] ) .'.php';

		// check page existence
		if ( file_exists( $page_path ) )
		{
			do_action( 'woocommerce_tailor_meta_box_before', $page_path, $meta_box['id'] );

			include apply_filters( 'woocommerce_tailor_meta_box_path', $page_path, $meta_box['id'] );

			do_action( 'woocommerce_tailor_meta_box_after', $page_path, $meta_box['id'] );
		}
		else
			echo '<div class="error"><p><strong>'. __( 'Meta box not found.', WCT_DOMAIN ) .'</strong></p></div>';
	}
}






