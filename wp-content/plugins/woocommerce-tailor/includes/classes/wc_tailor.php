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

class WC_Tailor
{
	/**
	 * @var WooCommerce Tailor The single instance of the class
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Admin pages class
	 *
	 * @var WC_Tailor_Admin_Pages
	 */
	protected $admin_pages;

	/**
	 * Account Updates
	 *
	 * @var WC_Tailor_Account_Updates
	 */
	protected $account_updates;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// load languages
		add_action( 'plugins_loaded', array( &$this, 'load_languages' ) );

		// admin pages instance
		$this->admin_pages = new WC_Tailor_Admin_Pages();

		// account updates instance
		$this->account_updates = new WC_Tailor_Account_Updates();

		// plugin activation hook
		register_activation_hook( WC_TAILOR_PLUGIN_FILE, array( &$this, 'plugin_activation' ) );
	}

	/**
	 * Load locale language
	 * 
	 * @return void
	 */
	public function load_languages()
	{
		load_plugin_textdomain( WCT_DOMAIN, false, dirname( plugin_basename( WC_TAILOR_PLUGIN_FILE ) ) . '/languages/' );
	}

	/**
	 * Get account updates instance
	 * 
	 * @return WC_Tailor_Account_Updates
	 */
	public function get_account_updates()
	{
		return $this->account_updates;
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
			deactivate_plugins( basename( WC_TAILOR_PLUGIN_FILE ), true );

			// error message
			wp_die( __( 'WooCommerce Plugin must be installed and activated first.', WOOT_DOMAIN ), 
					__( 'Required Plugin', WOOT_DOMAIN ), 
					array( 'back_link' => __( 'Back', WOOT_DOMAIN ) ) );
		}
	}

	/**
	 * Main WooCommerce Tailor Instance
	 *
	 * Ensures only one instance is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @see WC_Tailor()
	 * @return WC_Tailor - Main instance
	 */
	public static function get_instance() 
	{
		// create instance if not set
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();

		return self::$_instance;
	}
}




