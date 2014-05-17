<?php
/**
 * AJAX handling base
 *
 * @package WooCommerce Tailor
 * @since 1.0
 */

/**
 * AJAX Debug response
*
* @param mixed $data
* @param boolean $type
* @return void
*/
function wct_ajax_debug( $data, $type = false )
{
	// return dump
	wct_ajax_error( 'debug', dump_data_export( $data, $type ) );
}

/**
 * AJAX Error response
 *
 * @param string $error_key
 * @param mixed $error_message
 * @param boolean $add_notice
 * @return void
 */
function wct_ajax_error( $error_key, $error_message, $add_notice = false )
{
	// error obj
	$error = array( 'key' => $error_key, 'message' => $error_message );

	// add woocommerce notice
	if ( $add_notice )
		wc_add_notice( $error_message, 'error' );

	// send response
	wct_ajax_response( $error, false );
}

/**
 * AJAX JSON Response
 *
 * @param mixed $data
 * @param boolean $status
 * @return void
 */
function wct_ajax_response( $data, $status = true )
{
	// set response header content type
	header( 'Content-Type:application/json' );

	// response body
	$response = array ( 'status' => $status );

	// response type
	if ( $status )
	{
		// success response
		$response['data'] = $data;
	}
	else
	{
		// failure/error response
		$response['error'] = $data;
	}

	// send response
	die( json_encode( $response ) );
}
