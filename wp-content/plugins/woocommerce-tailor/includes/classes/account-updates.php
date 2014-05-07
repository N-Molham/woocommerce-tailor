<?php
/**
 * Account Updates
 * 
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Tailor_Account_Updates
{
	/**
	 * Account details sections
	 *
	 * @var array
	 */
	protected $account_details_sections;

	/**
	 * Account details fields
	 *
	 * @var array
	 */
	protected $account_details_fields;

	/**
	 * List of fields to be handled by WooCommerce by default
	 * 
	 * @var array
	 */
	var $handled_fields;

	/**
	 * User class properties ( buit-in )
	 * 
	 * @var array
	 */
	var $user_class_props;

	/**
	 * Input field default arguments
	 * 
	 * @var array
	 */
	var $field_defaults;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// set sections
		$this->account_details_sections = apply_filters( 'woocommerce_tailor_account_details_sections', array (
				'personal_info' => array ( 
						'title' => __( 'Your Personal Details', WCT_DOMAIN ),
						'description' => __( 'Our website makes ordering easy for you. We remember your measurements for next time you login. This is handy when you simply can\'t live without a few more finely crafted My Bespoke Tailor custom shirts.', WCT_DOMAIN ),
				),
		) );

		// set fields info
		$this->account_details_fields = apply_filters( 'woocommerce_tailor_account_details_fields', array ( 
				'account_first_name' => array ( 
						'label' => __( 'First name', WCT_DOMAIN ),
						'meta_key' => 'first_name',
						'input' => 'text',
						'input_class' => 'input-text',
						'wrapper_class' => 'form-row form-row-first',
						'data_type' => 'text',
						'required' => true,
						'section' => 'personal_info',
				),
				'account_last_name' => array ( 
						'label' => __( 'Last name', WCT_DOMAIN ),
						'meta_key' => 'last_name',
						'input' => 'text',
						'input_class' => 'input-text',
						'wrapper_class' => 'form-row form-row-last',
						'data_type' => 'text',
						'required' => true,
						'section' => 'personal_info',
				),
				'account_email' => array ( 
						'label' => __( 'Email address', WCT_DOMAIN ),
						'meta_key' => 'user_email',
						'input' => 'email',
						'input_class' => 'input-text',
						'wrapper_class' => 'form-row form-row-wide',
						'data_type' => 'email',
						'required' => true,
						'section' => 'personal_info',
				),
				'password_1' => array ( 
						'label' => __( 'Password (leave blank to leave unchanged)', WCT_DOMAIN ),
						'meta_key' => '',
						'input' => 'password',
						'input_class' => 'input-text',
						'wrapper_class' => 'form-row form-row-first',
						'data_type' => 'text',
						'required' => false,
						'section' => 'personal_info',
				),
				'password_2' => array ( 
						'label' => __( 'Confirm new password', WCT_DOMAIN ),
						'meta_key' => '',
						'input' => 'password',
						'input_class' => 'input-text',
						'wrapper_class' => 'form-row form-row-last',
						'data_type' => 'text',
						'required' => false,
						'section' => 'personal_info',
				),
				'account_phone' => array ( 
						'label' => __( 'Telephone Number', WCT_DOMAIN ),
						'meta_key' => 'phone',
						'input' => 'text',
						'input_class' => 'input-text',
						'wrapper_class' => 'form-row form-row-first',
						'data_type' => 'text',
						'required' => true,
						'section' => 'personal_info',
				),
				'account_gender' => array ( 
						'label' => __( 'Gender', WCT_DOMAIN ),
						'meta_key' => 'gender',
						'input' => 'radio',
						'input_class' => 'input-radio',
						'wrapper_class' => 'form-row form-row-last',
						'data_type' => 'text',
						'values' => array ( 
								'male' => __( 'Male', WCT_DOMAIN ), 
								'female' => __( 'Female', WCT_DOMAIN ),
						 ),
						'required' => true,
						'section' => 'personal_info',
				),
		) );
		/*$measurements		= array( cm inches
				"Chest",
				"Waist",
				"Hips",
				"Length",
				"Shoulders",
				"Sleeve",
				"Bicep",
				"Wrist",
				"Neck",
				"Front Arm to Arm",
				"Back Arm to Arm", // male
				"Forearm Circumference", // female
				"Bust Point",
				"Bust Point to Bust Point",
				"Front Waistline",
				"Back Waistline"
		);*/
		/*
		Body Profile
		male
			Chest 		> Muscular		Large				Average
 			Body Shape 	> Average		Flat				Large
 			Shoulders 	> Square		Sloping				Average
 		Female
 			Shoulders 	> Square		Sloping				Average
 			Figure 		> Apple			Pear				Hour Glass
 			Body Shape 	> Slim			Fuller Midriff		Fuller Bottom
 		both
 			Height cm   Weight kg
		 */

		// Redirect new customer to edit account page
		add_filter( 'woocommerce_registration_redirect', 'wc_customer_edit_account_url' );

		// save account updates
		add_action( 'user_profile_update_errors', array( &$this, 'save_account_details_errors' ), 10, 3 );

		// redirect after account update success to same form
		add_action( 'woocommerce_save_account_details', function() {
			wp_safe_redirect( wc_customer_edit_account_url() );
			exit;
		} );

		// additional fields render
		add_action( 'woocommerce_tailor_account_fields', array( &$this, 'render_account_details' ) );

		// handled fields
		$this->handled_fields = array ( 
				'account_first_name',
				'account_last_name',
				'account_email',
				'password_1',
				'password_2',
		);

		// WP_User properties
		$this->user_class_props = apply_filters( 'woocommerce_tailor_user_class_properties', array( 'first_name', 'last_name', 'user_email' ) );

		// field arguments default values
		$this->field_defaults = apply_filters( 'woocommerce_tailor_fields_defaults', array (
				'label' => '',
				'meta_key' => '',
				'input' => 'text',
				'input_class' => 'input-text',
				'wrapper_class' => 'form-row form-row-wide',
				'data_type' => 'text',
				'required' => false,
				'values' => array(),
				'value' => '',
		) );
	}

	/**
	 * Save user account details errors
	 *
	 * @param WP_Error $errors
	 * @param boolean $update
	 * @param stdClass $user
	 * @return void
	 */
	public function save_account_details_errors( $errors, $update, $user )
	{
		// loop fields
		foreach ( $this->account_details_fields as $field_name => $field_args )
		{
			// skip already handled fields
			if ( in_array( $field_name, $this->handled_fields ) )
				continue;

			// sanitizing value
			$value = wc_clean( filter_input( INPUT_POST, $field_name ) );

			// check required
			if ( empty( $value ) && $field_args['required'] )
			{
				$errors->add( $field_name .'_required', sprintf( __( '%s is required', WCT_DOMAIN ), $field_args['label'] ) );
				continue;
			}

			// data validation
			switch( $field_args['input'] )
			{
				case 'radio':
					if ( !isset( $field_args['values'][$value] ) )
					{
						$errors->add( $field_name .'_invalid', sprintf( __( '%s is not valid', WCT_DOMAIN ), $field_args['label'] ) );
						continue;
					}
					break;
			}

			// update values
			update_user_meta( $user->ID, $field_args['meta_key'], apply_filters( 'woocommerce_tailor_account_field_value', $value ) );
		}
	}

	/**
	 * Render additional account fields
	 * 
	 * @param WP_User $user
	 * @param boolean $echo
	 * @return void|string return HTML layout string if $echo is true
	 */
	public function render_account_details( $user = null, $echo = true )
	{
		// if no user passed, get the current logged in user
		if ( !$user )
			$user = wp_get_current_user();

		// output holder
		$output = '';
		$handled_sections = array();

		// fields loop
		foreach ( $this->account_details_fields as $field_name => $field_args )
		{
			// section handler
			$field_section = $field_args['section'];
			if ( !in_array( $field_section, $handled_sections ) && isset( $this->account_details_sections[$field_section] ) )
			{
				// section title
				$output .= '<h3>'. $this->account_details_sections[$field_section]['title'] .'</h3>';

				// section descriptions
				$output .= '<p>'. $this->account_details_sections[$field_section]['description'] .'</p>';

				// set as handled
				$handled_sections[] = $field_section;
			}

			// field value
			if ( in_array( $field_args['meta_key'], $this->user_class_props ) && isset( $user->$field_args['meta_key'] ) )
			{
				// from class property
				$value = $user->$field_args['meta_key'];
			}
			else
			{
				// from meta
				$value = empty( $field_args['meta_key'] ) ? '' : get_user_meta( $user->ID, $field_args['meta_key'], true );
			}

			// parse args
			$field_args = apply_filters( 'woocommerce_tailor_account_field_args', wp_parse_args( $field_args, $this->field_defaults ), $field_name, $user );

			// wrapper
			$output .= '<p class="'. $field_args['wrapper_class'] .'">';

			// label
			$output .= '<label for="'. $field_name .'">'. $field_args['label'] . ( $field_args['required'] ? ' <span class="required">*</span>' : '' ) .'</label>';

			// input
			switch ( $field_args['input'] )
			{
				case 'text':
				case 'email':
				case 'password':
					$output .= '<input type="'. $field_args['input'] .'" class="'. $field_args['input_class'] .'" name="'. $field_name .'" id="'. $field_name .'" value="'. esc_attr( $value ) .'" />';
					break;

				case 'radio':
					// loop values
					foreach ( $field_args['values'] as $option_value => $option_label )
					{
						$output .= '<label><input type="radio" class="'. $field_args['input_class'] .'" name="'. $field_name .'" value="'. $option_value .'"'. checked( $value, $option_value, false ) .'/> '. $option_label .'</label>';
					}
					break;
			}

			// wrapper end
			$output .= '</p>';
		}

		if ( $echo )
			echo apply_filters( 'woocommerce_tailor_edit_account_fields', $output, $user );
		else
			return apply_filters( 'woocommerce_tailor_edit_account_fields', $output, $user );
	}

	/**
	 * Get account details fields
	 * 
	 * @return multitype:
	 */
	public function get_account_details()
	{
		return $this->account_details_fields;
	}
}











