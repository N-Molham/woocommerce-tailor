<?php
/**
 * Admin Pages
 * 
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Tailor_Admin_Pages
{
	/**
	 * Pages hooks
	 * 
	 * @var array
	 */
	var $pages_hooks;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// hook list
		$this->pages_hooks = array();

		// Setup admin pages
		add_action( 'admin_menu', array( &$this, 'setup_admin_pages' ) );
	}

	/**
	 * Admin pages setup
	 * 
	 * @hook admin_menu
	 * @return void
	 */
	public function setup_admin_pages()
	{
		// parent menu
		$this->pages_hooks['parent'] = add_menu_page( __( 'WooCommerce Tailor', WCT_DOMAIN ), __( 'WooCommerce Tailor', WCT_DOMAIN ), 'manage_options', 'wc_tailor_shirt_characteristics', array( &$this, 'load_page_layout' ), 'dashicons-universal-access' );

		// options page
		$this->pages_hooks['shirt_chars'] = add_submenu_page( 'wc_tailor_shirt_characteristics', __( 'Shirt\'s Characteristics', WCT_DOMAIN ), __( 'Shirt\'s Characteristics', WCT_DOMAIN ), 'manage_options', 'wc_tailor_shirt_characteristics', array( &$this, 'load_page_layout' ) );

		// enqueue scripts & styles
		array_map( function( $hook ) {
			// enqueue styles
			add_action( 'admin_print_styles-'.$hook, array( &$this, 'enqueue_scripts_styles' ) );
		}, $this->pages_hooks );
	}

	/**
	 * Load page layout file
	 * 
	 * @return void
	 */
	public function load_page_layout()
	{
		// page wrapper
		echo '<div class="wrap">';

		$page_found = isset( $_REQUEST['page'] );

		// target page
		if ( $page_found )
		{
			// target page name
			$page_name = sanitize_key( $_REQUEST['page'] );

			// build page path
			$page_path = WC_TAILOR_DIR .'admin-pages/'. str_replace( array( 'wc_tailor_', '_' ), array( '', '-' ), $page_name ) .'.php';
			$page_found = file_exists( $page_path );

			// check page existence
			if ( $page_found )
			{
				do_action( 'woocommerce_tailor_admin_page_before', $page_path, $page_name );

				include apply_filters( 'woocommerce_tailor_admin_page_path', $page_path, $page_name );

				do_action( 'woocommerce_tailor_admin_page_after', $page_path, $page_name );
			}
		}

		// error
		if ( !$page_found )
			echo '<div class="error"><p><strong>'. __( 'Target page not found.', WCT_DOMAIN ) .'</strong></p></div>';

		// page wrapper end
		echo '</div>';
	}

	/**
	 * Enqueue admin scripts and styles
	 * 
	 * @return void
	 */
	public function enqueue_scripts_styles()
	{
		/**
		 * Styles
		 */
		// admin pages style
		wp_enqueue_style( 'wct-admin-style', WC_TAILOR_URL .'css/admin.css' );

		/**
		 * Scripts
		 */
		wp_enqueue_media();
		wp_enqueue_script( 'wct-repeatable', WC_TAILOR_URL .'js/jquery.repeatable.item.min.js', array( 'wct-shared-js', 'jquery-ui-sortable' ), false, true );
		wp_enqueue_script( 'wct-shirt-chars', WC_TAILOR_URL .'js/admin-shirts.js', array( 'wct-shared-js' ), false, true );
	}
}






