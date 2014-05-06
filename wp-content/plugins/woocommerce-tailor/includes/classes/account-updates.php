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
	 * Account details fields
	 *
	 * @var array
	 */
	protected $account_details;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// set fields info
		$this->account_details = array ( 
				'account_phone' => array ( 
						'label' => __( 'Telephone Number', WCT_DOMAIN ),
						'meta_key' => 'phone',
						'input' => 'text',
						'input_class' => 'input-text',
						'wrapper_class' => 'form-row form-row-first',
						'data_type' => 'text',
						'required' => true,
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
				),
		);

		// apply filter
		$this->account_details = apply_filters( 'woocommerce_tailor_account_details', $this->account_details );

		// Redirect new customer to edit account page
		add_filter( 'woocommerce_registration_redirect', 'wc_customer_edit_account_url' );

		// save account updates
		add_action( 'user_profile_update_errors', array( &$this, 'save_account_details_errors' ), 10, 3 );

		// redirect after account update success to same form
		add_action( 'woocommerce_save_account_details', function() {
			wp_safe_redirect( wc_customer_edit_account_url() );
			exit;
		});

		// additional fields render
		add_action( 'woocommerce_tailor_account_fields', array( &$this, 'render_account_details' ) );
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
		foreach ( $this->account_details as $field_name => $field_args )
		{
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

		// field arguments default values
		$defaults = apply_filters( 'woocommerce_tailor_fields_defaults', array ( 
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

		// output holder
		$output = '';

		// loop fields
		foreach ( $this->account_details as $field_name => $field_args )
		{
			// get value
			$field_args['value'] = get_user_meta( $user->ID, $field_args['meta_key'], true );

			// parse args
			$field_args = apply_filters( 'woocommerce_tailor_account_field_args', wp_parse_args( $field_args, $defaults ), $field_name, $user );

			// wrapper
			$output .= '<p class="'. $field_args['wrapper_class'] .'">';

			// label
			$output .= '<label for="'. $field_name .'">'. $field_args['label'] . ( $field_args['required'] ? ' <span class="required">*</span>' : '' ) .'</label>';

			// input
			switch ( $field_args['input'] )
			{
				case 'text':
					$output .= '<input type="text" class="'. $field_args['input_class'] .'" name="'. $field_name .'" id="'. $field_name .'" value="'. esc_attr( $field_args['value'] ) .'" />';
					break;

				case 'radio':
					// loop values
					foreach ( $field_args['values'] as $option_value => $option_label )
					{
						$output .= '<label><input type="radio" class="'. $field_args['input_class'] .'" name="'. $field_name .'" value="'. $option_value .'"'. checked( $field_args['value'], $option_value, false ) .'/> '. $option_label .'</label>';
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
		return $this->account_details;
	}
}











