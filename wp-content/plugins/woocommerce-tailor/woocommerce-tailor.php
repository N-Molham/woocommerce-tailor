<?php
/*
Plugin Name: WooCommerce Tailor Store plugin
Plugin URI: http://nabeel.molham.me/plugins/woocommerce-tailor/
Description: Adds tailor store features like measurements
Author: Nabeel Molham
Author URI: http://nabeel.molham.me/
Version: 1.0
Text Domain: wc-tailor
License: GPL version 3 or later
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Constants
 */
define( 'WC_TAILOR_PLUGIN_FILE', __FILE__ );
define( 'WC_TAILOR_DIR', plugin_dir_path( WC_TAILOR_PLUGIN_FILE ) );
define( 'WC_TAILOR_URL', plugin_dir_url( WC_TAILOR_PLUGIN_FILE ) );
define( 'WCT_DOMAIN', 'wc-tailor' );

/**
 * Includes
 */
require WC_TAILOR_DIR .'includes/utils.php';

spl_autoload_register( 'wc_tailor_autoload' );
/**
 * Autoload class files on demand
 *
 * `WC_Tailor_Installer` becomes => installer.php
 * `WC_Tailor_Template_Report` becomes => template-report.php
 *
 * @param string $class requested class name
 * @return void
 */
function wc_tailor_autoload( $class_name )
{
	$prefix = 'WC_Tailor';

	if ( stripos( $class_name, $prefix ) !== false )
	{
		$class_name = $prefix == $class_name ? $class_name : str_replace( array( $prefix .'_', '_' ), array( '', '-' ), $class_name );
		$file_path = WC_TAILOR_DIR . 'includes/classes/' . strtolower( $class_name ) . '.php';

		// check class file, include it
		if ( file_exists( $file_path ) )
			require_once $file_path;
	}
}

/**
 * Get WooCommerce Tailor instance
 * 
 * @return WC_Tailor
 */
function WC_Tailor()
{
	return WC_Tailor::get_instance();
}

// start the whole thing
$GLOBALS['woocommerce_tailor'] = WC_Tailor();

// trigger plugin ready action
do_action( 'woocommerce_tailor_loaded' );

