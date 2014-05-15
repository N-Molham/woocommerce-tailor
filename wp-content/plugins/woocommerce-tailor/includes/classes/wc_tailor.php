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
	 * Admin Meta boxes
	 *
	 * @var WC_Tailor_Meta_Boxes
	 */
	protected $meta_boxes;

	/**
	 * Account Updates
	 *
	 * @var WC_Tailor_Account_Updates
	 */
	protected $account_updates;

	/**
	 * Design Wizard
	 *
	 * @var WC_Tailor_Design_Wizard
	 */
	protected $design_wizard;

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

		// override product price
		// add_action( 'woocommerce_before_calculate_totals', array( &$this, 'product_price_override_test' ) );
	}

	/**
	 * Product price override to add options selected
	 * 
	 * @param WC_Cart $cart
	 */
	public function product_price_override_test( $cart )
	{
		foreach ( $cart->cart_contents as $cart_item_key => $cart_item )
		{
			// set price
			$cart_item['data']->price = 20;
		}
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

		// meta box instance
		$this->meta_boxes = new WC_Tailor_Meta_Boxes();

		// account updates instance
		$this->account_updates = new WC_Tailor_Account_Updates();

		// design wizard instance
		$this->design_wizard = new WC_Tailor_Design_Wizard();

		// woocommerce templates override
		add_filter( 'woocommerce_locate_template', array( $this, 'wc_template_override' ), 10, 3 );

		/**
		 * Styles
		 */
		// front-end
		wp_register_style( 'wct-style', WC_TAILOR_URL .'css/style.css' );

		// jQuery Chosen plugin
		wp_register_style( 'chosen-styles', WC()->plugin_url() . '/assets/css/chosen.css' );

		// wp-admin
		wp_register_style( 'wct-admin-style', WC_TAILOR_URL .'css/admin.css', array( 'chosen-styles' ) );

		// fancybox css
		wp_register_style( 'jquery-fancybox-css', WC_TAILOR_URL .'js/fancybox/jquery.fancybox-1.3.4.css' );

		/**
		 * JavaScript
		 */
		// shared js utils
		wp_register_script( 'wct-shared-js', WC_TAILOR_URL .'js/shared.js', array( 'jquery' ), false, true );

		// fancybox js
		wp_register_script( 'jquery-fancybox', WC_TAILOR_URL .'js/fancybox/jquery.fancybox-1.3.4.pack.js', array( 'wct-shared-js' ), false, true );

		// repeatable item js
		wp_register_script( 'wct-repeatable', WC_TAILOR_URL .'js/jquery.repeatable.item.js', array( 'wct-shared-js' ), false, true );

		// body profile js
		wp_register_script( 'wct-body-profile-js', WC_TAILOR_URL .'js/body-profile.js', array( 'jquery-fancybox' ), false, true );

		// wizard steps
		wp_register_script( 'jquery-steps', WC_TAILOR_URL .'js/jquery.steps.min.js', array( 'wct-shared-js' ), false, true );

		// front-end global enqueues
		add_action( 'template_redirect', array( &$this, 'frontend_global_enqueues' ) );
	}

	/**
	 * Front-end Global enqueues
	 * 
	 * @return void
	 */
	public function frontend_global_enqueues()
	{
		/**
		 * Styles
		 */
		wp_enqueue_style( 'wct-style' );
	}

	/**
	 * Override woocommerce templates
	 * 
	 * @param string $template
	 * @param string $template_name
	 * @param string $template_path
	 * @return string
	 */
	public function wc_template_override( $original_template, $template_name, $template_path )
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
	 * Get shirt characteristics settings
	 * 
	 * @return array
	 * 
	 * @uses WC_Tailor_Design_Wizard::get_shirt_charaters
	 */
	public function get_shirt_charaters_settings()
	{
		return $this->design_wizard->get_shirt_charaters();
	}

	/**
	 * Get design wizard settings
	 * 
	 * @param boolean $return_object
	 * @return array|stdClass
	 * 
	 * @uses WC_Tailor_Design_Wizard::get_settings
	 */
	public function get_design_wizard_settings( $return_object = false )
	{
		return $this->design_wizard->get_settings( $return_object );
	}

	/**
	 * Get design wizard filters labels
	 * 
	 * @return array
	 */
	public function wizard_filters_labels()
	{
		return $this->design_wizard->get_filters_labels();
	}

	/**
	 * Get design wizard filters meta keys
	 * 
	 * @return array
	 */
	public function wizard_filters_meta_keys()
	{
		return $this->design_wizard->get_filters_meta_keys();
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




