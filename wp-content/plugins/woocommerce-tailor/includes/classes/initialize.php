<?php
/**
 * Initialize Class
 * 
 * @since 1.0
 */

class Woo_Tailor_Initialize
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

		// admin pages setup
		add_action( 'admin_menu', array( &$this->admin_pages, 'setup_admin_pages' ) );
	}
}