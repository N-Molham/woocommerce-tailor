<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/* Add custom functions below */

/**
 * Constants
 */
define( 'BRETHEON_CHILD_DOMAIN', 'bretheon-child' );

add_action( 'after_setup_theme', 'bretheon_child_theme_setup' );
/**
 * Child Theme Setup
 * 
 * @return void
 */
function bretheon_child_theme_setup() 
{
	// load language files
	load_child_theme_textdomain( BRETHEON_CHILD_DOMAIN, get_stylesheet_directory() . '/languages' );
}
