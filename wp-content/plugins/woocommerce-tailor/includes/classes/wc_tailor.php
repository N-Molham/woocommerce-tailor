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

		// init
		add_action( 'init', array( &$this, 'init' ) );

		// plugin activation hook
		register_activation_hook( WC_TAILOR_PLUGIN_FILE, array( &$this, 'plugin_activation' ) );
	}

	/**
	 * init
	 * 
	 * @return void
	 */
	public function init()
	{
		// admin pages instance
		$this->admin_pages = new WC_Tailor_Admin_Pages();

		// account updates instance
		$this->account_updates = new WC_Tailor_Account_Updates();

		// templates override
		add_filter( 'woocommerce_locate_template', array( $this, 'template_override' ), 10, 3 );

		// register js & css enqueues
		wp_register_style( 'wct-style', WC_TAILOR_URL .'css/style.css' );
		wp_register_script( 'wct-shared-js', WC_TAILOR_URL .'js/shared.js', array( 'jquery' ), false, true );
	}

	/**
	 * Override templates to load custom ones
	 * 
	 * @param string $template
	 * @param string $template_name
	 * @param string $template_path
	 * @return string
	 */
	public function template_override( $original_template, $template_name, $template_path )
	{
		// possible template location
		$new_template = WC_TAILOR_DIR .'templates/'. $template_name;

		// check template existence
		if ( file_exists( $new_template ) )
			return apply_filters( 'woocommerce_tailor_template_override', $new_template, $original_template, $template_name );

		// return original template location
		return $original_template;
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




