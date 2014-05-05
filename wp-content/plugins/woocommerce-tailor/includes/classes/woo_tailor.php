<?php
/**
 * WooCommrece Tailor Class
 * 
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_Tailor
{
	/**
	 * Admin pages class
	 *
	 * @var Woo_Tailor_Admin_Pages
	 */
	protected $admin_pages;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// admin pages instance
		$this->admin_pages = new Woo_Tailor_Admin_Pages();

		// plugin activation hook
		register_activation_hook( WOO_TAILOR_PLUGIN_FILE, array( &$this, 'plugin_activation' ) );

		// Setip admin pages
		add_action( 'admin_menu', array( &$this->admin_pages, 'setup_admin_pages' ) );

		// Redirect new customer to edit account page
		add_filter( 'woocommerce_registration_redirect', 'wc_customer_edit_account_url' );
	}

	/**
	 * Plugin Activation Hook
	 * 
	 * @return void
	 */
	public function plugin_activation()
	{
		// check WooCommerce plugin is active/installed
		if ( !class_exists( 'WooCommerce' ) )
		{
			// deactivate plugin
			deactivate_plugins( basename( WOO_TAILOR_PLUGIN_FILE ), true );

			// error message
			wp_die( __( 'WooCommerce Plugin must be installed and activated first.', WOOT_DOMAIN ), 
					__( 'Required Plugin', WOOT_DOMAIN ), 
					array( 'back_link' => __( 'Back', WOOT_DOMAIN ) ) );
		}
	}
}