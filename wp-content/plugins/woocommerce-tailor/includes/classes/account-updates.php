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
	var $account_details_sections;

	/**
	 * Account details fields
	 *
	 * @var array
	 */
	var $account_details_fields;

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
	 * Body Profile
	 * 
	 * @var array
	 */
	var $body_profile;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// images directory
		$image_dir = WC_TAILOR_URL .'images/';

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
						'description' => '',
						'enqueues' => array ( 
								'js' => array( 'wct-body-profile-js' ),
								'css' => array( 'jquery-fancybox-css' )
						),
				),
		) );

		// body profile field picture template
		$body_profile_pic_template = '<span class="field-picture">'. __( '&nbsp;( <a href="%s" title="%s" target="_blank" class="bp-image fancybox">picture</a> )', WCT_DOMAIN ) .'</span>';

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
						'data_type' => 'key',
						'options' => array ( 
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
						'required' => false,
						'section' => 'measurements',
						'after' => '<div class="clear"></div>',
				),
				'body_profile_chest' => array ( 
						'label' => __( 'Chest', WCT_DOMAIN ) . sprintf( $body_profile_pic_template, esc_attr( $image_dir .'body-profile/chest.gif' ), __( 'Chest', WCT_DOMAIN ) ),
						'meta_key' => array( 'body_profile', 'chest' ),
						'input' => 'radio',
						'input_class' => 'input-radio',
						'wrapper_class' => 'form-row form-row-wide gender-male body-profile',
						'data_type' => 'key',
						'options' => array ( 
								'muscular' => __( 'Muscular', WCT_DOMAIN ),
								'large' => __( 'Large', WCT_DOMAIN ),
								'average' => __( 'Average', WCT_DOMAIN ),
						),
						'required' => false,
						'section' => 'body_profile',
						'gender' => array( 'male' ),
				),
				'body_profile_bshape' => array ( 
						'label' => __( 'Body Shape', WCT_DOMAIN ) . sprintf( $body_profile_pic_template, esc_attr( $image_dir .'body-profile/bodyshape_m.gif' ), __( 'Body Shape', WCT_DOMAIN ) ),
						'meta_key' => array( 'body_profile', 'body_shape' ),
						'input' => 'radio',
						'input_class' => 'input-radio',
						'wrapper_class' => 'form-row form-row-wide gender-male body-profile',
						'data_type' => 'key',
						'options' => array ( 
								'average' => __( 'Average', WCT_DOMAIN ),
								'flat' => __( 'Flat', WCT_DOMAIN ),
								'large' => __( 'Large', WCT_DOMAIN ),
						),
						'required' => false,
						'section' => 'body_profile',
						'gender' => array( 'male' ),
				),
				'body_profile_shoulders' => array ( 
						'label' => __( 'Shoulders', WCT_DOMAIN ) . sprintf( $body_profile_pic_template, esc_attr( $image_dir .'body-profile/shoulders.gif' ), __( 'Shoulders', WCT_DOMAIN ) ),
						'meta_key' => array( 'body_profile', 'shoulders' ),
						'input' => 'radio',
						'input_class' => 'input-radio',
						'wrapper_class' => 'form-row form-row-wide gender-male gender-female body-profile',
						'data_type' => 'key',
						'options' => array ( 
								'square' => __( 'Square', WCT_DOMAIN ),
								'sloping' => __( 'Sloping', WCT_DOMAIN ),
								'average' => __( 'Average', WCT_DOMAIN ),
						),
						'required' => false,
						'section' => 'body_profile',
						'gender' => array( 'male', 'female' ),
				),
				'body_profile_figure' => array ( 
						'label' => __( 'Figure', WCT_DOMAIN ) . sprintf( $body_profile_pic_template, esc_attr( $image_dir .'body-profile/figure.gif' ), __( 'Figure', WCT_DOMAIN ) ),
						'meta_key' => array( 'body_profile', 'figure' ),
						'input' => 'radio',
						'input_class' => 'input-radio',
						'wrapper_class' => 'form-row form-row-wide gender-female body-profile',
						'data_type' => 'key',
						'options' => array ( 
								'apple' => __( 'Apple', WCT_DOMAIN ),
								'pear' => __( 'Pear', WCT_DOMAIN ),
								'hourglass' => __( 'Hour Glass', WCT_DOMAIN ),
						),
						'required' => false,
						'section' => 'body_profile',
						'gender' => array( 'female' ),
				),
				'body_profile_bshapef' => array (
						'label' => __( 'Body Shape', WCT_DOMAIN ) . sprintf( $body_profile_pic_template, esc_attr( $image_dir .'body-profile/bodyshape_f.gif' ), __( 'Body Shape ( Female )', WCT_DOMAIN ) ),
						'meta_key' => array( 'body_profile', 'body_shapef' ),
						'input' => 'radio',
						'input_class' => 'input-radio',
						'wrapper_class' => 'form-row form-row-wide gender-female body-profile',
						'data_type' => 'key',
						'options' => array (
								'slim' => __( 'Slim', WCT_DOMAIN ),
								'fullermidriff' => __( 'Fuller Midriff', WCT_DOMAIN ),
								'fullerbottom' => __( 'Fuller Bottom', WCT_DOMAIN ),
						),
						'required' => false,
						'section' => 'body_profile',
						'gender' => array( 'female' ),
				),
				'body_profile_height' => array (
						'label' => __( 'Height', WCT_DOMAIN ),
						'meta_key' => array( 'body_profile', 'height' ),
						'input' => 'text',
						'input_class' => 'input-text input-small',
						'wrapper_class' => 'form-row column one-fourth',
						'data_type' => 'float',
						'required' => true,
						'section' => 'body_profile',
						'description' => '&nbsp;cm',
						'gender' => array( 'male', 'female' ),
				),
				'body_profile_weight' => array (
						'label' => __( 'Weight', WCT_DOMAIN ),
						'meta_key' => array( 'body_profile', 'weight' ),
						'input' => 'text',
						'input_class' => 'input-text input-small',
						'wrapper_class' => 'form-row column one-fourth',
						'data_type' => 'float',
						'required' => true,
						'section' => 'body_profile',
						'description' => '&nbsp;kg',
						'gender' => array( 'male', 'female' ),
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
				'input_name' => '',
				'wrapper_attrs' => '',
				'wrapper_tag' => 'p',
				'wrapper_class' => 'form-row form-row-wide',
				'data_type' => 'text',
				'required' => false,
				'options' => array(),
				'option_class' => 'column one-fourth',
				'value' => '',
				'after' => '',
				'description' => '',
		) );

		// register js & css enqueues
		wp_register_script( 'wct-measures-js', WC_TAILOR_URL .'js/measures.js', array( 'wct-shared-js' ), false, true );

		// save new order measures
		add_action( 'woocommerce_checkout_update_order_meta', array( &$this, 'save_new_order_meaures' ) );
	}

	/**
	 * Save newly created order measures to account details profile 
	 * 
	 * @param integer $order_id
	 * @return void
	 */
	public function save_new_order_meaures( $order_id )
	{
		$orders_info = WC_Tailor_Design_Wizard::get_orders_data();
		if ( empty( $orders_info ) )
			return;

		$last_item_key = end( array_keys( $orders_info ) );
		if ( !$last_item_key || !isset( $orders_info[ $last_item_key ] ) )
			return;

		$order = new WC_Order( $order_id );
		$customer_id = $order->customer_user;

		// update measurements
		update_user_meta( $customer_id, 'measurements', $orders_info[ $last_item_key ]['measures'] );

		// update body profile
		update_user_meta( $customer_id, 'body_profile', $orders_info[ $last_item_key ]['body_profile'] );
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

			// parse value
			$value = $this->parse_field_value( $field_name, $field_args );
			if ( is_wp_error( $value ) )
			{
				$errors->add( $value->get_error_code(), $value->get_error_message() );
				continue;
			}

			// meta key
			$meta_key = $field_args['meta_key'];
			if ( is_array( $meta_key ) && count( $meta_key ) == 2 )
			{
				// array value
				$meta_value = (array) get_user_meta( $user->ID, $meta_key[0], true );
				$meta_value[$meta_key[1]] = $value;

				// meta info
				$meta_key = $meta_key[0];
				$value = $meta_value;
			}

			// update values
			update_user_meta( $user->ID, $meta_key, apply_filters( 'woocommerce_tailor_account_save_field_value', $value, $field_name, $field_args ) );
		}
	}

	/**
	 * Parse field value
	 * 
	 * @param string $field_name
	 * @param array $field_args
	 * @param mixed $field_value
	 * @return mixed|WP_Error
	 */
	public function parse_field_value( $field_name, $field_args, $field_value = null )
	{
		// sanitizing value
		$value = is_null( $field_value ) ? wc_clean( filter_input( INPUT_POST, $field_name ) ) : wc_clean( $field_value );

		// data type sanitizing
		switch ( $field_args['data_type'] )
		{
			case 'int':
			case 'integer':
				$value = intval( $value );
				break;

			case 'float':
				$value = floatval( $value );
				break;

			case 'email':
				$value = is_email( sanitize_email( $value ) );
				break;

			case 'key':
				$value = sanitize_key( $value );
				break;
		}

		$is_empty = empty( $value );

		// check required
		if ( $is_empty && $field_args['required'] )
			return new WP_Error( $field_name .'_required', sprintf( __( '%s is required', WCT_DOMAIN ), $field_args['label'] ) );

		// data input validation
		switch( $field_args['input'] )
		{
			case 'radio':
				if ( !$is_empty && !isset( $field_args['options'][$value] ) )
				{
					// clear value
					$value = '';

					// return error
					return new WP_Error( $field_name .'_invalid', sprintf( __( '%s is not valid', WCT_DOMAIN ), $field_args['label'] ) );
				}
				break;
		}

		// specific field handling
		switch ( $field_name )
		{
			case 'measures_inputs':
				if ( !isset( $_REQUEST[$field_name] ) || !is_array( $_REQUEST[$field_name] ) )
					return new WP_Error( $field_name .'_required', sprintf( __( '%s is required', WCT_DOMAIN ), $field_args['label'] ) );

				// check keys
				$value = $_REQUEST[$field_name];
				if ( array_keys( $value ) !== array_keys( $this->body_measurements ) )
					return new WP_Error( $field_name .'_invalid', sprintf( __( '%s is not valid', WCT_DOMAIN ), $field_args['label'] ) );

				// parse values
				$value = array_map( array( &$this, 'parse_meausre' ), $_REQUEST[$field_name] );
				break;
		}

		// return filtered value
		return apply_filters( 'woocommerce_tailor_account_parse_field_value', $value, $field_name, $field_args );
	}

	/**
	 * Parse body measure values
	 * 
	 * @param mixed $measure
	 * @return array
	 */
	public function parse_meausre( $measure )
	{
		// default values
		$measure  = wp_parse_args( $measure, array( 'cm' => 0, 'inches' => 0 ) );

		// needed values only
		return array ( 
				'cm' => floatval( $measure['cm'] ), 
				'inches' => floatval( $measure['inches'] ) 
		);
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

				// enqueues
				$enqueues = isset( $this->account_details_sections[$field_section]['enqueues'] ) ? (array) $this->account_details_sections[$field_section]['enqueues'] : array();
				if ( ! empty( $enqueues ) )
				{
					// scripts
					if ( isset( $enqueues['js'] ) )
					{
						array_walk( $enqueues['js'], function( $handle ) {
							wp_enqueue_script( $handle );
						} );
					}

					// styles
					if ( isset( $enqueues['css'] ) )
					{
						array_walk( $enqueues['css'], function( $handle ) {
							wp_enqueue_style( $handle );
						} );
					}
				}
			}

			// field input layout
			$output .= $this->render_field_output( $field_name, $field_args, $this->get_field_value( $field_name, $field_args, $user ), $user );
		}

		if ( $echo )
			echo apply_filters( 'woocommerce_tailor_edit_account_fields', $output, $user );
		else
			return apply_filters( 'woocommerce_tailor_edit_account_fields', $output, $user );
	}

	/**
	 * Get account detail field value
	 * 
	 * @param string $field_name
	 * @param array $field_args
	 * @param WP_User $user
	 * @return mixed
	 */
	public function get_field_value( $field_name, $field_args, &$user = null )
	{
		// if no user passed, get the current logged in user
		if ( !$user )
			$user = wp_get_current_user();

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
		return apply_filters( 'woocommerce_tailor_account_field_value', $field_value, $field_name, $field_args );;
	}

	/**
	 * Render field layout
	 * 
	 * @param string $field_name
	 * @param array $field_args
	 * @param mixed $field_value
	 * @param WP_User $user
	 * @return string
	 */
	public function render_field_output( $field_name, $field_args, $field_value = null, &$user = null )
	{
		// get field value if not set
		if ( is_null( $field_value ) )
			$field_value = $this->get_field_value( $field_name, $field_args, $user );

		// parse args
		$field_args = apply_filters( 'woocommerce_tailor_account_field_args', wp_parse_args( $field_args, $this->field_defaults ), $field_name );

		// input name
		$input_name = empty( $field_args['input_name'] ) ? $field_name : $field_args['input_name'];

		// wrapper
		$output = '<'. $field_args['wrapper_tag'] .' class="'. $field_args['wrapper_class'] .'" '. $field_args['wrapper_attrs'] .'>';

		// label
		$output .= 'none' == $field_args['label'] ? '' : '<label for="'. $field_name .'">'. $field_args['label'] . ( $field_args['required'] ? ' <span class="required">*</span>' : '' ) .'</label>';

		// input layout
		switch ( $field_args['input'] )
		{
			case 'text':
			case 'email':
			case 'password':
				$output .= '<input type="'. $field_args['input'] .'" class="'. $field_args['input_class'] .'" name="'. $input_name .'" id="'. $field_name .'" value="'. esc_attr( $field_value ) .'" />';
				break;

			case 'radio':
				// loop values
				foreach ( $field_args['options'] as $option_value => $option_label )
				{
					$output .= '<label class="'. $field_args['option_class'] .'"><input type="radio" class="'. $field_args['input_class'] .'" name="'. $input_name .'" value="'. $option_value .'"'. checked( $field_value, $option_value, false ) .'/> '. $option_label .'</label>';
				}
				break;

			case 'html':
				switch ( $field_name )
				{
					case 'measures_inputs':
						if ( !is_array( $field_value ) )
							$field_value = array();

						// measure image
						$output .= '<div class="column two-third">';
						$output .= '<div class="loading"></div>';
						$output .= '<img src="" data-default="'. WC_TAILOR_URL .'images/measurements/default.jpg" alt="" class="measure-img" /></div>';

						// inputs
						$output .= '<div class="column one-third">';

						// measurements loop
						foreach ( $this->body_measurements as $meausre_name => $meausre_args )
						{
							if ( !isset( $field_value[$meausre_name] ) )
								$field_value[$meausre_name] = array();

							$field_value[$meausre_name] = wp_parse_args( $field_value[$meausre_name], array( 'cm' => 0, 'inches' => 0 ) );

							// data attributes
							$output .= '<p class="inputs-holder" data-key="'. $meausre_name .'" data-gender="'. $meausre_args['gender'] .'" data-instructions="'. esc_attr( $meausre_args['instructions'] ) .'">';

							// label
							$output .= '<label>'. $meausre_args['label'] .'</label>&nbsp;&nbsp;';

							// inputs
							$output .= '<input type="text" class="input-text input-cm" name="'. $input_name .'['. $meausre_name .'][cm]" value="'. floatval( $field_value[$meausre_name]['cm'] ) .'" /> cm &nbsp;';
							$output .= '<input type="text" class="input-text input-inches" name="'. $input_name .'['. $meausre_name .'][inches]" value="'. floatval( $field_value[$meausre_name]['inches'] ) .'" /> inc.';
						}

						// inputs end
						$output .= '</div>';

						// instructions
						$output .= '<div class="clear"></div><div class="instructions">';
						$output .= '<h3>'. __( 'Instructions', WCT_DOMAIN ) .'</h3>';
						$output .= '<p class="content-holder"></p></div>';

						// enqueues
						wp_enqueue_script( 'wct-measures-js' );

						// js localize
						wp_localize_script( 'wct-measures-js', 'wct_measures', array ( 
								'measure_url' => WC_TAILOR_URL .'images/measurements/',
						) );
						break;
				}
				break;
		}

		// input + description
		$output .= '<span class="description">'. $field_args['description'] .'</span>';

		// wrapper end + after field
		$output .= '</'. $field_args['wrapper_tag'] .'>'. $field_args['after'];

		// input layout filter
		return apply_filters( 'woocommerce_tailor_account_field_input', $output, $field_name, $field_value, $field_args );
	}

	/**
	 * Get account details by section
	 * 
	 * @return array
	 */
	public function get_account_details_by_section( $section )
	{
		return array_filter( $this->account_details_fields, function( $field ) use ( $section ) {
			// get only fields in body profile section
			return $section === $field['section'];
		} );
	}
}

