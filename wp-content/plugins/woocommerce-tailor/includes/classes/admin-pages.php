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
		add_menu_page( __( 'WooCommerce Tailor', WCT_DOMAIN ), __( 'WooCommerce Tailor', WCT_DOMAIN ), 'manage_options', 'wc_tailor_shirt_characteristics', array( &$this, 'load_page_layout' ), 'dashicons-universal-access' );

		// shirt characteristics page
		$this->pages_hooks['shirt_chars'] = add_submenu_page( 'wc_tailor_shirt_characteristics', __( 'Shirt\'s Characteristics', WCT_DOMAIN ), __( 'Shirt\'s Characteristics', WCT_DOMAIN ), 'manage_options', 'wc_tailor_shirt_characteristics', array( &$this, 'load_page_layout' ) );

		// design wizard options
		$this->pages_hooks['design_wizard'] = add_submenu_page( 'wc_tailor_shirt_characteristics', __( 'Design Wizard', WCT_DOMAIN ), __( 'Design Wizard', WCT_DOMAIN ), 'manage_options', 'wc_tailor_design_wizard', array( &$this, 'load_page_layout' ) );

		foreach ( $this->pages_hooks as $page_name => $page_hook )
		{
			// global enqueue styles
			add_action( 'admin_print_styles-'.$page_hook, array( &$this, 'enqueue_scripts_styles' ) );

			// page actions
			if ( method_exists( $this, 'page_action_'. $page_name ) )
				add_action( 'load-'. $page_hook, array( &$this, 'page_action_'. $page_name ) );
		}
	}

	/**
	 * Design wizard options action handler
	 * 
	 * @return void
	 */
	public function page_action_design_wizard()
	{
		if ( isset( $_POST['wct_wizard_save'], $_POST['wizard'] ) && check_admin_referer( 'wc_tailor_admin_design_wizard', 'nonce' ) )
		{
			// sanitize data
			$wizard = WC_Tailor_Utiles::array_map_recursive( (array) $_POST['wizard'], 'wc_clean' );

			// old values
			$old_wizard = WC_Tailor()->get_design_wizard_settings();

			// update option
			update_option( 'wc_tailor_design_wizard', apply_filters( 'woocommerce_tailor_update_design_wizard', $wizard, $old_wizard ) );

			// redirect
			wc_tailor_redirect( add_query_arg( 'message', 'success' ) );
		}
	}

	/**
	 * Shirt characteristics page action handler
	 * 
	 * @return void
	 */
	public function page_action_shirt_chars()
	{
		if ( isset( $_POST['wct_shirt_save'], $_POST['chars'] ) && check_admin_referer( 'wc_tailor_admin_shirt_chars', 'nonce' ) )
		{
			$target_gender = filter_input( INPUT_POST, 'gender' );
			if ( in_array( $target_gender, array( 'male', 'female' ) ) )
			{
				// get option
				$shirt_characters = get_option( 'wc_tailor_shirt_chars', array( 'male' => array(), 'female' => array() ) );

				// clean values
				$new_characters = WC_Tailor_Utiles::array_map_recursive( $_POST['chars'], 'wc_tailor_clean_string_basic' );

				// set new values
				$shirt_characters[$target_gender] = apply_filters( 'woocommerce_tailor_update_shirt_characteristics', $new_characters, $target_gender, $shirt_characters[$target_gender] );

				// update option
				update_option( 'wc_tailor_shirt_chars', $shirt_characters );

				// redirect
				wc_tailor_redirect( add_query_arg( 'message', 'success' ) );
			}
		}
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
		wp_enqueue_style( 'wct-admin-style', WC_TAILOR_URL .'css/admin.css', array( 'chosen-styles' ) );

		/**
		 * Scripts
		 */
		switch ( $_GET['page'] )
		{
			case 'wc_tailor_design_wizard':
				wp_enqueue_script( 'wct-design-wizard', WC_TAILOR_URL .'js/admin-wizard.js', array( 'chosen', 'wct-repeatable' ), false, true );
				break;

			case 'wc_tailor_shirt_characteristics':
				wp_enqueue_media();
				wp_enqueue_script( 'wct-shirt-chars', WC_TAILOR_URL .'js/admin-shirts.js', array( 'wct-repeatable' ), false, true );
				break;
		}
	}
}






