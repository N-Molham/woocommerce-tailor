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

class Woo_Tailor_Admin_Pages
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
		$this->pages_hooks['parent'] = add_menu_page( __( 'WooCommerce Tailor', WOOT_DOMAIN ), __( 'WooCommerce Tailor', WOOT_DOMAIN ), 'manage_options', 'woo_tailor_menu', array( &$this, 'load_page_layout' ), 'dashicons-universal-access' );

		// options page
		$this->pages_hooks['options'] = add_submenu_page( 'woo_tailor_menu', __( 'Fabrics Options', WOOT_DOMAIN ), __( 'Fabrics Options', WOOT_DOMAIN ), 'manage_options', 'woo_tailor_menu', array( &$this, 'load_page_layout' ) );
	}

	/**
	 * Load page layout file
	 * 
	 * @return void
	 */
	public function load_page_layout()
	{
		dump_data( get_object_vars( get_current_screen() ) );
	}
}






