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
	 * User class properties ( built-in )
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
	 * Body Measurements
	 * 
	 * @var array
	 */
	var $body_measurements;

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
				'measurements' => array ( 
						'title' => __( 'Measurements', WCT_DOMAIN ),
						'description' => '',
				),
				'body_profile' => array ( 
						'title' => __( 'Body Profile', WCT_DOMAIN ),
						'description' => __( 'Place the tape measure around your bicep over the largest part of your upper arm. To ensure a comfortable fit take the bicep measurement with one finger inside the tape measure.', WCT_DOMAIN ),
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
						'after' => '<div class="clear"></div>',
				),
				'measures_inputs' => array ( 
						'label' => 'none',
						'meta_key' => 'measurements',
						'input' => 'html',
						'input_class' => 'input-text input-small',
						'wrapper_tag' => 'div',
						'wrapper_class' => 'form-row measures-inputs',
						'data_type' => 'float',
						'required' => true,
						'section' => 'measurements',
						'after' => '<div class="clear"></div>',
				),
				'body_profile_inputs' => array ( 
						'label' => 'none',
						'meta_key' => 'body_profile',
						'input' => 'html',
						'input_class' => 'input-text input-small',
						'wrapper_tag' => 'div',
						'wrapper_class' => 'form-row body-profile-inputs',
						'data_type' => 'float',
						'required' => true,
						'section' => 'body_profile',
				),
		) );

		// set list
		$this->body_measurements = apply_filters( 'woocommerce_tailor_account_body_measurements', array (
				'chest' => array ( 
						'label' => 'Chest',
						'instructions' => __( 'Place the tape measure around the body over the largest part of the chest as shown in the diagram. To ensure the right fit take the chest measurement with one finger inside the tape measure.', WCT_DOMAIN ),
						'gender' => 'male female',
				),
				'waist' => array ( 
						'label' => 'Waist',
						'instructions' => __( 'Place the tape measure around the body over the smallest part of the waist area as shown in the diagram. To ensure a comfortable fit, take the measurement with one finger inside the tape measure.', WCT_DOMAIN ),
						'gender' => 'male female',
				),
				'hips' => array ( 
						'label' => 'Hips',
						'instructions' => __( 'Place the tape measure around the body at the point where you would like the shirt length to finish. To ensure a more comfortable fit, take the hip measurement with one finger inside the tape measure. Remember to use this as the reference point when entering your shirt length.', WCT_DOMAIN ),
						'gender' => 'male female',
				),
				'length' => array ( 
						'label' => 'Length',
						'instructions' => __( 'Place the tape measure at the top of the shirt level with the collar seam, and then measure the length of the shirt. This measurement will determine how long your shirt is, so simply select the required length. If you intend on wearing your shirts tucked in, we recommend the shirt length measurement be taken at point level with the base of the trouser crotch.', WCT_DOMAIN ),
						'gender' => 'male female',
				),
				'shoulders' => array ( 
						'label' => 'Shoulders',
						'instructions' => __( 'Place the tape measure across the top of the shoulders and measure from one shoulder edge to the other, ensuring you take the curved contour over the top of the shoulders as seen in the diagram.', WCT_DOMAIN ),
						'gender' => 'male female',
				),
				'sleeve' => array ( 
						'label' => 'Sleeve',
						'instructions' => __( 'Place the tape measure at the top of the sleeve level with the edge of the shoulder, and then measure the length of the sleeve. This measurement will determine where the sleeve will come to on the hand, so simply select your preferred length. We recommend the sleeve length be at a point approximately level with the top of the V between the thumb and forefinger.', WCT_DOMAIN ),
						'gender' => 'male female',
				),
				'bicep' => array ( 
						'label' => 'Bicep',
						'instructions' => __( 'Place the tape measure around your bicep over the largest part of your upper arm. To ensure a comfortable fit take the bicep measurement with one finger inside the tape measure.', WCT_DOMAIN ),
						'gender' => 'male female',
				),
				'wrist' => array ( 
						'label' => 'Wrist',
						'instructions' => __( 'Place the tape measure around the entire wrist. To ensure the right fit, take the wrist measurement with one finger inside the tape measure.', WCT_DOMAIN ),
						'gender' => 'male female',
				),
				'neck' => array ( 
						'label' => 'Neck',
						'instructions' => __( 'Place the tape measure around the entire neck. To ensure a comfortable fit take the neck measurement with one finger inside the tape measure, ensuring the tape is at the base of the neck where the neck and shoulders meet as seen in the diagram.', WCT_DOMAIN ),
						'gender' => 'male female',
				),
				'frontarmhole' => array ( 
						'label' => 'Front Arm to Arm',
						'instructions' => __( 'Place the tape measure at the crease in the top of the arm where the arm and chest meet. Measure the length across the chest to the equal and opposite point on the body. There is no need to place the tape in the armpit, just on the crease where the relevant body parts meet.', WCT_DOMAIN ),
						'gender' => 'male female',
				),
				'backarmhole' => array ( 
						'label' => 'Back Arm to Arm',
						'instructions' => __( 'Place the tape measure at the crease in the top of the arm where the arm and back meet. Measure the length across the back to the equal and opposite point on the body. There is no need to place the tape in the armpit, just on the crease where the relevant body parts meet.', WCT_DOMAIN ),
						'gender' => 'male female',
				),
				'forearm' => array ( 
						'label' => 'Forearm Circumference',
						'instructions' => __( 'Place the tape measure around the entire forearm and take the forearm measurement. To ensure the best fit, take the measurement with one finger inside the tape measure.', WCT_DOMAIN ),
						'gender' => 'female',
				),
				'bustpoint' => array ( 
						'label' => 'Bust Point',
						'instructions' => __( 'Place the tape measure at the top of the shirt level with the collar seam, then measure the distance down to the bust point as shown above. If possible ensure you are wearing a bra that you would normally wear under your shirts.', WCT_DOMAIN ),
						'gender' => 'female',
				),
				'busttobust' => array ( 
						'label' => 'Bust Point to Bust Point',
						'instructions' => __( 'Place the tape measure on the left bust point and measure across to the equal and opposite bust point as shown.', WCT_DOMAIN ),
						'gender' => 'female',
				),
				'frontwaist' => array ( 
						'label' => 'Front Waistline',
						'instructions' => __( 'Place the tape measure at the top of the shirt level with the collar seam, then measure the distance over the bust point down to the high waistline as shown. Please ensure you follow the diagram as this line is higher than the actual waist itself.', WCT_DOMAIN ),
						'gender' => 'female',
				),
				'backwaist' => array ( 
						'label' => 'Back Waistline',
						'instructions' => __( 'Place the tape measure at the top of the shirt level with the collar seam, then measure the distance down the back to the high waistline as shown. Please ensure you follow the diagram as this line is higher than the actual waist itself.', WCT_DOMAIN ),
						'gender' => 'female',
				),
		) );

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
				'wrapper_tag' => 'p',
				'wrapper_class' => 'form-row form-row-wide',
				'data_type' => 'text',
				'required' => false,
				'values' => array(),
				'value' => '',
				'after' => '',
		) );

		// register js & css enqueues
		wp_register_script( 'wct-measures-js', WC_TAILOR_URL .'js/measures.js', array( 'wct-shared-js' ), false, true );
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
			// parse args
			$field_args = apply_filters( 'woocommerce_tailor_account_field_args', wp_parse_args( $field_args, $this->field_defaults ), $field_name, $user );

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
			$meta_key = $field_args['meta_key'];
			if ( in_array( $meta_key, $this->user_class_props ) && isset( $user->$meta_key ) )
			{
				// from class property
				$field_value = $user->$meta_key;
			}
			else
			{
				// from meta
				if ( is_array( $meta_key ) && count( $meta_key ) == 2 )
				{
					// array value
					$meta_value = (array) get_user_meta( $user->ID, $meta_key[0], true );
					$field_value = isset( $meta_value[$meta_key[1]] ) ? $meta_value[$meta_key[1]] : '';
				}
				else
				{
					// non array
					$field_value = empty( $meta_key ) ? '' : get_user_meta( $user->ID, $meta_key, true );
				}
			}

			// value filter
			$field_value = apply_filters( 'woocommerce_tailor_account_field_value', $field_value, $field_name, $field_args );

			// wrapper
			$output .= '<'. $field_args['wrapper_tag'] .' class="'. $field_args['wrapper_class'] .'">';

			// label
			$output .= 'none' == $field_args['label'] ? '' : '<label for="'. $field_name .'">'. $field_args['label'] . ( $field_args['required'] ? ' <span class="required">*</span>' : '' ) .'</label>';

			// input layout
			$input_layout = '';
			switch ( $field_args['input'] )
			{
				case 'text':
				case 'email':
				case 'password':
					$input_layout .= '<input type="'. $field_args['input'] .'" class="'. $field_args['input_class'] .'" name="'. $field_name .'" id="'. $field_name .'" value="'. esc_attr( $field_value ) .'" />';
					break;

				case 'radio':
					// loop values
					foreach ( $field_args['values'] as $option_value => $option_label )
					{
						$input_layout .= '<label><input type="radio" class="'. $field_args['input_class'] .'" name="'. $field_name .'" value="'. $option_value .'"'. checked( $field_value, $option_value, false ) .'/> '. $option_label .'</label>';
					}
					break;

				case 'html':
					switch ( $field_name )
					{
						case 'measures_inputs':
							if ( !is_array( $field_value ) )
								$field_value = array();

							// measure image
							$input_layout .= '<div class="column two-third">';
							$input_layout .= '<div class="loading"></div>';
							$input_layout .= '<img src="" data-default="'. WC_TAILOR_URL .'images/measurements/default.jpg" alt="" class="measure-img" /></div>';

							// inputs
							$input_layout .= '<div class="column one-third">';

							// measurements loop
							foreach ( $this->body_measurements as $meausre_name => $meausre_args )
							{
								$field_value[$meausre_name] = wp_parse_args( $field_value, array( 'cm' => 0, 'inches' => 0 ) );

								// data attributes
								$input_layout .= '<p class="inputs-holder" data-key="'. $meausre_name .'" data-gender="'. $meausre_args['gender'] .'" data-instructions="'. esc_attr( $meausre_args['instructions'] ) .'">';

								// label
								$input_layout .= '<label>'. $meausre_args['label'] .'</label>&nbsp;&nbsp;';

								// inputs
								$input_layout .= '<input type="text" class="input-text input-cm" name="'. $field_name .'['. $meausre_name .'][cm]" value="'. floatval( $field_value[$meausre_name]['cm'] ) .'" /> cm &nbsp;';
								$input_layout .= '<input type="text" class="input-text input-inches" name="'. $field_name .'['. $meausre_name .'][inches]" value="'. floatval( $field_value[$meausre_name]['inches'] ) .'" /> inc.';
							}

							// inputs end
							$input_layout .= '</div>';

							// instructions
							$input_layout .= '<div class="clear"></div><div class="instructions">';
							$input_layout .= '<h3>'. __( 'Instructions', WCT_DOMAIN ) .'</h3>';
							$input_layout .= '<p class="content-holder"></p></div>';

							// enqueues
							wp_enqueue_style( 'wct-style' );
							wp_enqueue_script( 'wct-measures-js' );

							// js localize
							wp_localize_script( 'wct-measures-js', 'wct_measures', array ( 
									'measure_url' => WC_TAILOR_URL .'images/measurements/',
							) );
							break;
					}
					break;
			}

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

			// input layout filter
			$input_layout = apply_filters( 'woocommerce_tailor_account_field_input', $input_layout, $field_name, $field_value, $field_args );

			// input + wrapper end + after field
			$output .= $input_layout .'</'. $field_args['wrapper_tag'] .'>'. $field_args['after'];
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











