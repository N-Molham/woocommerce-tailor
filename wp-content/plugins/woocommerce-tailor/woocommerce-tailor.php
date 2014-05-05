<?php
/*
Plugin Name: WooCommerce Tailor Store plugin
Plugin URI: http://nabeel.molham.me/plugins/woocommerce-tailor/
Description: Adds tailor store features like measurements
Author: Nabeel Molham
Author URI: http://nabeel.molham.me/
Version: 1.0
Text Domain: woo-tailor
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/* @var $woocommerce_tailor Woo_Tailor */
global $woocommerce_tailor;

/**
 * Constants
 */
define( 'WOO_TAILOR_PLUGIN_FILE', __FILE__ );
define( 'WOO_TAILOR_DIR', plugin_dir_path( WOO_TAILOR_PLUGIN_FILE ) );
define( 'WOO_TAILOR_URL', plugin_dir_url( WOO_TAILOR_PLUGIN_FILE ) );
define( 'WOOT_DOMAIN', 'woo-tailor' );

/**
 * Includes
 */
require WOO_TAILOR_DIR .'includes/utils.php';

spl_autoload_register( 'woo_tailor_autoload' );
/**
 * Autoload class files on demand
 *
 * `Woo_Tailor_Installer` becomes => installer.php
 * `Woo_Tailor_Template_Report` becomes => template-report.php
 *
 * @param string $class requested class name
 * @return void
 */
function woo_tailor_autoload( $class_name )
{
	$prefix = 'Woo_Tailor';

	if ( stripos( $class_name, $prefix ) !== false )
	{
		$class_name = $prefix == $class_name ? $class_name : str_replace( array( $prefix .'_', '_' ), array( '', '-' ), $class_name );
		$file_path = WOO_TAILOR_DIR . 'includes/classes/' . strtolower( $class_name ) . '.php';

		// check class file, include it
		if ( file_exists( $file_path ) )
			require_once $file_path;
	}
}

// start the whole thing
$woocommerce_tailor = new Woo_Tailor();

// trigger plugin ready action
do_action( 'woocommerce_tailor_loaded' );

