<?php
/**
 * Utils functions
 */

function woo_tailor_get_value( $key, $query_var = '', $session = false, $encoded = false, $html = false, $allowed = null )
{
	return Woo_Tailor_Utiles::get_value( $key, $query_var, $session, $encoded, $html, $allowed );
}

function woo_tailor_sanitize_digit( $target, $negative = false )
{
	return Woo_Tailor_Utiles::sanitize_digit( $target, $negative );
}

function woo_tailor_redirect( $target = '', $status = 302 )
{
	Woo_Tailor_Utiles::redirect( $target, $status );
}

function woo_tailor_get_page_by_slug( $page_slug, $output = OBJECT, $post_type = 'page' )
{
	return Woo_Tailor_Utiles::get_page_by_slug( $page_slug, $output, $post_type );
}

function woo_tailor_form_input( $field, $args, $echo = false )
{
	return Woo_Tailor_Utiles::form_input( $field, $args, $echo );
}

function woo_tailor_parse_input( $field, $args = array(), $query_var = false, $session = false, $encoded = false, $html = false, $allowed = null )
{
	return Woo_Tailor_Utiles::parse_input( $field, $args, $query_var, $session, $encoded, $html, $allowed );
}

class Woo_Tailor_Utiles
{
	static $text_domain = WOOT_DOMAIN;

	public static function wp_datetime_format( $datetime, $str_format = '%s %s' )
	{
		$datetime = strtotime( $datetime );
		if ( !$datetime )
			return false;

		return sprintf( $str_format, date( get_option( 'date_format' ), $datetime ), date( get_option( 'time_format' ), $datetime ) );
	}

	public static function pretty_json( $json ) 
	{
		$result      = '';
		$pos         = 0;
		$strLen      = strlen( $json );
		$indentStr   = '    ';
		$newLine     = "\n";
		$prevChar    = '';
		$outOfQuotes = true;

		for ( $i = 0; $i <= $strLen; $i++ ) 
		{
			// Grab the next character in the string.
			$char = substr( $json, $i, 1 );

			// Are we inside a quoted string?
			if ( $char == '"' && $prevChar != '\\' ) 
			{
				$outOfQuotes = !$outOfQuotes;

				// If this character is the end of an element,
				// output a new line and indent the next line.
			} 
			else if ( ( $char == '}' || $char == ']' ) && $outOfQuotes ) 
			{
				$result .= $newLine;
				$pos --;
				for ( $j = 0; $j < $pos; $j++ ) 
				{
					$result .= $indentStr;
				}
			}

			// Add the character to the result string.
			$result .= $char;

			// If the last character was the beginning of an element,
			// output a new line and indent the next line.
			if ( ( $char == ',' || $char == '{' || $char == '[' ) && $outOfQuotes ) 
			{
				$result .= $newLine;
				if ( $char == '{' || $char == '[' ) 
				{
					$pos ++;
				}
	
				for ( $j = 0; $j < $pos; $j++ ) 
				{
					$result .= $indentStr;
				}
			}
			$prevChar = $char;
		}
		return $result;
	}

	public static function array2csv( array &$array )
	{
		if ( count( $array) == 0  )
			return null;

		ob_start();
		$df = fopen( "php://output", 'w' );

		fputcsv( $df, array_keys( reset( $array ) ) );

		foreach ( $array as $row )
		{
			fputcsv( $df, $row );
		}

		fclose( $df );

		return ob_get_clean();
	}

	public static function download_header_params( $filename )
	{
		// disable caching
		$now = gmdate( 'D, d M Y H:i:s' );
		header( 'Expires: Tue, 03 Jul 2001 06:00:00 GMT' );
		header( 'Cache-Control: max_age=0, no-cache, must-revalidate, proxy-revalidate' );
		header( 'Last-Modified: '. $now .' GMT' );

		// force download
		header( 'Content-Type: application/force-download' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Type: application/download' );

		// disposition / encoding on response body
		header( 'Content-Disposition: attachment;filename='. $filename );
		header( 'Content-Transfer-Encoding: binary' );
	}

	/**
	 * Cache printed fragment
	 *
	 * @param string $fragment_key
	 * @param number $time
	 * @param function $callback
	 */
	public static function fragment_cache( $fragment_key, $time, $callback )
	{
		// key filter
		$fragment_key = apply_filters( 'fragment_cache_prefix', 'fragment_cache_' ) . $fragment_key;

		// output
		$output = get_transient( $fragment_key );

		// check if it was cached before or not
		if ( '' == $output || empty( $output ) )
		{
			// start recording
			ob_start();

			// run callback
			call_user_func( $callback );

			// get recorded output
			$output = ob_get_clean();

			// cache output
			set_transient( $fragment_key, $output, $time );
		}

		// echo output
		echo $output;
	}

	public static function map_image( $lat, $lng, $width, $height, $zoom = '12' )
	{
		return "http://maps.googleapis.com/maps/api/staticmap?center={$lat},{$lng}&zoom={$zoom}&markers=color=red|{$lat},{$lng}&size={$width}x{$height}&maptype=roadmap&sensor=false";
	}

	public static function map_enlarge( $lat, $lng )
	{
		return "https://maps.google.com/maps?q={$lat},{$lng}";
	}

	public static function parse_input( $field, $args = array(), $query_var = false, $session = false, $encoded = false, $html = false, $allowed = null  )
	{
		$value = self::get_value( $field, $query_var, $session, $encoded, $html, $allowed );

		$defaults = array (
				'data_type' => 'text',
				'required' => false,
				'min_length' => false,
				'max_length' => false,
				'sanitize_callback' => '',
				'validate_callback' => '',
				'min_number' => null,
		);
		$args = wp_parse_args( $args, $defaults );

		if ( function_exists( $args['sanitize_callback'] ) )
			$value = call_user_func( $args['sanitize_callback'], $value );

		switch( $args['data_type'] )
		{
			case 'int':
			case 'float':
			case 'number':
				$value = self::sanitize_digit( $value );
				if ( is_numeric( $value ) )
				{
					if ( 'int' == $args['data_type'] )
						$value = (int) $value;
					elseif ( 'float' == $args['data_type'] )
						$value = (float) $value;
				}
				else 
					$value = false;

				if ( !$value )
						self::form_error( $field, sprintf( __( '%s is not valid numeric value', self::$text_domain ), $args['label'] ) );

				if ( $args['min_number'] && $value < $args['min_number'] )
						self::form_error( $field, sprintf( __( '%s must be must be at least %s', self::$text_domain ), $args['label'], $args['min_number'] ) );
				break;

			case 'plain-text-aplha':
				$value = preg_replace( '/\d/', '', $value );
				break;

			case 'password':
				if ( isset( $args['match-base'] ) )
				{
					$match = self::get_value( $args['match-base'] );
					if( $match != $value )
						self::form_error( $field, __( 'Passwords does not match', self::$text_domain ) );
				}
				break;

			case 'email':
				$value = is_email( $value );
				if( !$value )
					self::form_error( $field, __( 'Invalid email address', self::$text_domain ) );
				break;

			case 'file':
				if( isset( $_FILES[$field] ) )
				{
					$file = $_FILES[$field];
					if( $file['size'] <= $args['file_size'] )
					{
						if( in_array($file['type'], $args['file_types']) )
						{
							$value = self::process_file_upload( $field );
							if( is_wp_error( $value ) )
							{
								self::form_error( $field, __( 'Error saving uploaded file, please try again later', self::$text_domain ) );
								$value = false;
							}
						}
						else
							self::form_error( $field, __( 'Invalid file type', self::$text_domain ) );
					}
					else
						self::form_error( $field, sprintf( __( '%s size is too big', self::$text_domain ), $args['label'] ) );
				}
				else
					$value = false;
				break;
		}

		// validation custom callback
		if ( '' != $args['validate_callback'] && function_exists( $args['validate_callback'] ) )
		{
			if ( !call_user_func( $args['validate_callback'], $value ) )
				self::form_error( $field, sprintf( $args['validate_error_msg'], $args['label'] ) );
		}

		if ( 'select' == $args['input'] )
		{
			// check source
			if ( !isset( $args['source'][$value] ) )
			{
				self::form_error( $field, sprintf( __( '%s is not a valid selection', self::$text_domain ), $args['label'] ) );
				$value = false;
			}
		}

		if ( $value !== false && $args['required'] && ( '' == $value || empty( $value ) ) )
			self::form_error( $field, sprintf( __( '%s required', self::$text_domain ), $args['label']) );

		if ( '' != $value || !empty( $value ) )
		{
			if( $args['min_length'] && $args['max_length'] && !self::is_str_length_between( $value, $args['min_length'], $args['max_length'] ) )
				self::form_error( $field, sprintf( __( '%s character length must be between %d and %d', self::$text_domain ), $args['label'], $args['min_length'], $args['max_length'] ) );
			elseif( $args['min_length'] && !$args['max_length'] && strlen( $value ) < $args['min_length'] )
				self::form_error( $field, sprintf( __( '%s character length must be at least %d', self::$text_domain ), $args['label'], $args['min_length'] ) );
			elseif( !$args['min_length'] && $args['max_length'] && strlen( $value ) > $args['max_length'] )
				self::form_error( $field, sprintf( __( '%s character length must be less than %d', self::$text_domain ), $args['label'], $args['max_length'] ) );
		}

		$_SESSION['request_data'][$field] = $value;
		return $value;
	}

	public static function form_parse_multi_image( $field )
	{
		if( isset($_POST[$field], $_POST[$field]['ids'], $_POST[$field]['urls']) && is_array($_POST[$field]['ids']) && $_POST[$field]['urls'] )
		{
			$images =& $_POST[$field];
			$ids_count = count( $images['ids'] );
			$urls_count = count( $images['urls'] );
			if( !$ids_count || !$urls_count || $ids_count != $urls_count )
				return;

			$final = array();
			for( $i = 0; $i < $ids_count; $i++ )
			{
				$final[] = array( 'id' => $images['ids'][$i], 'url' => $images['urls'][$i] );
			}
			return $final;
		}
		return false;
	}

	public static function form_multi_image_input( $field, $label, $id, $images )
	{
		echo '<label>', $label ,': <input type="button" class="add-image button" data-field="', $field ,'" data-target="', $id ,'" value="Add New" /></label>';
		echo '<div id="', $id ,'" class="sortable">';
		foreach ( $images as $image )
		{
			$thum = wp_get_attachment_thumb_url( $image['id'] );
			echo '<p class="image ui-state-default">';
			echo '<span style="display:block;" class="image-holder"><img height="', get_option( 'thumbnail_size_h' ) ,'" src="'. $thum .'" /></span>';
			echo '<input name="', $field ,'[ids][]" type="hidden" value="', $image['id'] ,'" class="image-id" />';
			echo '<input name="', $field ,'[urls][]" type="hidden" value="', $image['url'] ,'" class="image-url" />';
			echo '&nbsp;&nbsp;<input type="button" class="ml-image-remove button" value="Remove Image" /></p>';
		}
		echo '</div>';
	}

	public static function form_input( $field, $args, $echo = false )
	{
		$out = '';
		$defaults = array (
				'class' => 'input-text',
				'holder_class' => '',
				'dir' => 'ltr',
				'label_next' => null,
				'desc' => '',
				'value' => '',
				'placeholder' => '',
				'show_label' => true,
		);
		$args = apply_filters( 'form_input_args', wp_parse_args( $args, $defaults ) );

		if( $args['show_label'] )
			$out .= '<label for="'. $field .'">'. $args['label'] .' :</label>';

		if( $args['label_next'] )
			$out .= ' <span class="description" style="vertical-align:middle;">'. $args['label_next'] .'</span>';

		// placeholder same as label
		if ( 'label' == $args['placeholder'] )
			$args['placeholder'] = $args['label'];

		// field description
		$out .= '<p class="form-input form-input-'. $field .' '. $args['holder_class'] .'">';

		// characters escapes
		$field = esc_attr( $field );
		$args['placeholder'] = esc_attr( $args['placeholder'] );
		$args['class'] = esc_attr( $args['class'] );

		// field input element
		switch( $args['input'] )
		{
			case 'select':
				$args = wp_parse_args( $args, array ( 
						'source' => array(),
						'default_label' => '',
						'default_value' => '-1',
				) );

				$out .= '<select id="'. $field .'" name="'. $field .'">';
				$out .= '<option value="'. $args['default_value'] .'">'. $args['default_label'] .'</option>';

				foreach ( $args['source'] as $option_value => $option_label )
				{
					$out .= '<option value="'. esc_attr( $option_value ) .'"';
					$out .= ( $option_value == $args['value'] ? ' selected' : '' ) .'>';
					$out .= ( is_array( $option_label ) && isset( $option_label['label'] ) ? $option_label['label'] : $option_label );
					$out .= '</option>';
				}

				$out .= '</select>';
				break;

			case 'checkbox':
				if( $args['is_singular'] )
				{
					$out .= '<label><input type="checkbox" name="'. $field .'" value="'. $args['input_data']['value'] .'" ';
					if( $args['input_data']['value'] == $args['value'] )
						$out .= 'checked="checked" ';
					$out .= '/> '. $args['input_data']['label'] .'</label>';
				}
				else
				{
					foreach( $args['values'] as $checkbox_value => $checkbox_label )
					{
						$out .= '<label><input type="checkbox" name="'. $field .'[]" value="'. $checkbox_value .'" ';
						if( in_array($checkbox_value, $args['value']) )
							$out .= 'checked="checked" ';
						$out .= '/> '. $checkbox_label .'</label>&nbsp;&nbsp;&nbsp;&nbsp;';
					}
				}
				break;

			case 'radio':
				foreach( $args['values'] as $radio_value => $radio_label )
				{
					$out .= '<label><input type="radio" name="'. $field .'" value="'. esc_attr( $radio_value ) .'" ';
					if( $radio_value == $args['value'] )
						$out .= 'checked="checked" ';
					$out .= '/> '. $radio_label .'</label>&nbsp;&nbsp;&nbsp;&nbsp;';
				}
				break;

			case 'text':
			case 'password':
				$out .= '<input type="'. $args['input'] .'" placeholder="'. $args['placeholder'] .'" name="'. $field .'" id="'. $field .'" value="'. esc_attr( $args['value'] ) .'" class="'. $args['class'] .'" dir="'. $args['dir'] .'" /> ';
				break;

			case 'textarea':
				$args = wp_parse_args( $args, array (
						'rows' => '',
						'cols' => '-1',
				) );

				$out .= '<textarea placeholder="'. $args['placeholder'] .'" name="'. $field .'" id="'. $field .'" rows="'. $args['rows'] .'" cols="'. $args['cols'] .'" class="'. $args['class'] .'" dir="'. $args['dir'] .'">'. esc_attr( $args['value'] ) .'</textarea>';
				break;

			case 'button':
				$out .= '<input type="'. $args['data_type'] .'" name="'. $field .'" id="'. $field .'" value="'. esc_attr( $args['label'] ) .'" class="'. $args['class'] .'" /> ';
				break;

			case 'file':
				$out .= '<input type="file" name="'. $field .'" id="'. $field .'" /> ';
				break;

			case 'html':
				$out .= $args['html'];
				break;

			case 'image':
				if( $args['media_library'] )
				{
					$args = wp_parse_args( $args, array (
							'value' => array( 'id' => '', 'url' => '' ),
							'cols' => '-1',
					) );

					$not_empty = '' != $args['value']['url'];
					$out .= '<span style="display:block;" class="image-holder">';
					if( $not_empty )
						$out .= '<img src="'. $args['value']['url'] .'" />';

					$out .= '</span>';
					$out .= '<input type="button" class="ml-image button" value="Media Library" />';
					$out .= '<input name="'. $field .'[id]" type="hidden" value="'. $args['value']['id'] .'" class="image-id" />';
					$out .= '<input name="'. $field .'[url]" type="hidden" value="'. $args['value']['url'] .'" class="image-url" />';
					$out .= '&nbsp;&nbsp;<input type="button" class="ml-image-remove button" value="Remove Image" ';
					$out .= $not_empty ? '' : 'style="display: none;" ';
					$out .= '/>';
				}
				break;
		}

		if( '' != $args['desc'] )
			$out .= '<em class="description">'. $args['desc'] .'</em>';

		$out .= '</p>';

		if( $echo )
			echo $out;
		else
			return $out;
	}

	public static function is_array_assoc( $arr )
	{
		return array_keys( $arr) !== range(0, count($arr) - 1 );
	}

	public static function uniord( $u )
	{
		// i just copied this function fron the php.net comments, but it should work fine!
		$k = mb_convert_encoding( $u, 'UCS-2LE', 'UTF-8' );
		$k1 = ord( substr($k, 0, 1) );
		$k2 = ord( substr($k, 1, 1) );
		return $k2 * 256 + $k1;
	}

	public static function is_arabic( $str )
	{
		if( mb_detect_encoding($str) !== 'UTF-8' )
			$str = mb_convert_encoding( $str,mb_detect_encoding($str),'UTF-8' );

		preg_match_all( '/.|\n/u', $str, $matches );

		$chars = $matches[0];
		$arabic_count = 0;
		$latin_count = 0;
		$total_count = 0;

		foreach( $chars as $char )
		{
			$pos = self::uniord( $char );

			if( $pos >= 1536 && $pos <= 1791 )
				$arabic_count++;
			elseif( $pos > 123 && $pos < 123 )
			$latin_count++;

			$total_count++;
		}

		if( @($arabic_count/$total_count) > 0.6 )
			return true;

		return false;
	}

	public static function encode_email( $email )
	{
		$encodedmail = '';
		$len = strlen( $email );
		for ( $i = 0; $i < $len; $i++ )
		{
			$encodedmail .= "&#" . ord( $email[$i] ) . ';';
		}
		return $encodedmail;
	}

	public static function sanitize_digit( $target, $negative = false )
	{
		$pattern = '/( ?!\d)(?!\. )./';
		if( $negative )
			$pattern = '/( ?!^-)(?!\d)(?!\. )./';

		return preg_replace( $pattern, '', $target );
	}

	public static function clear_false_filter( $el )
	{
		return $el ? true : false;
	}

	public static function clear_empty_filter( $el )
	{
		return '' == $el || empty( $el ) || !strlen( $el ) ? false : true;
	}

	public static function sanitize_ml_file_data( $ml )
	{
		$ml = wp_parse_args( $ml, array('id' => 0, 'url' => '') );

		$ml['id'] = self::sanitize_digit( $ml['id'] );
		$ml['url'] = esc_url( $ml['url'] );

		return $ml;
	}

	public static function convert_datetime( $datetime, $timezone )
	{
		$timezone = explode( ':', $timezone );
		$convert_str = $timezone[0] . ' hours';
		if( count($timezone) == 2 )
			$convert_str .= ( strpos($timezone[0], '-') !== false ? ' -' : '' ) . $timezone[1] . ' minutes';

		if( strpos($datetime, '-') === false )
			return date( 'H:i:s', strtotime($convert_str, strtotime($datetime)) );

		return date( 'Y-m-d H:i:s', strtotime($convert_str, strtotime($datetime)) );
	}

	public static function convert_datetime_reverse( $datetime, $timezone )
	{
		$timezone = explode( ':', $timezone );
		$convert_str = -1 * $timezone[0] . ' hours';
		if( count($timezone) == 2 )
			$convert_str .= ( strpos($timezone[0], '+') !== false ? ' -' : ' +' ) . $timezone[1] . ' minutes';

		if( strpos($datetime, '-') === false )
			return date( 'H:i:s', strtotime($convert_str, strtotime($datetime)) );

		return date( 'Y-m-d H:i:s', strtotime($convert_str, strtotime($datetime)) );
	}

	public static function check_time_zone( $timezone )
	{
		return preg_match( '/^((\+|-)?\d{1,2}(:\d{1,2})?)$/', $timezone );
	}

	public static function sanitize_text_field( $str )
	{
		$filtered = wp_check_invalid_utf8( $str );

		if ( strpos( $filtered, '<') !== false  )
		{
			$filtered = wp_pre_kses_less_than( $filtered );
			// This will strip extra whitespace for us.
			$filtered = wp_strip_all_tags( $filtered, false );
		}
		else
		{
			$filtered = trim( preg_replace( '/[\t ]+/', ' ', $filtered)  );
		}

		$match = array();
		$found = false;
		while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match)  )
		{
			$filtered = str_replace( $match[0], '', $filtered );
			$found = true;
		}

		if ( $found )
		{
			// Strip out the whitespace that may now exist after removing the octets.
			$filtered = trim( preg_replace( '/ +/', ' ', $filtered)  );
		}

		return $filtered;
	}

	public static function array_insert( $array, $pos, $vals )
	{
		$array2 = array_splice( $array, $pos );
		if( self::is_array_assoc($vals) )
		{
			foreach ( $vals as $key => $value )
			{
				$array[$key] = $value;
			}
		}
		else
		{
			foreach ( $vals as $value )
			{
				$array[] = $value;
			}
		}
		$array = array_merge( $array, $array2 );

		return $array;
	}

	public static function change_datetime_hours( $datetime, $to = '24', $seconds = true )
	{
		$date = substr( $datetime, 0, 10 );
		$time = substr( $datetime, 11 );
		// convert
		$time = self::change_time_hours( $time );
		return $date . ' ' . $time;
	}

	public static function change_time_hours( $time, $to = '24', $seconds = true )
	{
		$strtime = strtotime( $time, current_time('timestamp') );
		// which time format
		if( '12' == $to )
			$format = 'h:i A';
		elseif ( '24' == $to && $seconds )
		$format = 'H:i:s';
		else
			$format = 'H:i';
		// convert
		return date( $format, strtotime($time) );
	}

	public static function current_time( $type, $format = 'Y-m-d H:i:s' )
	{
		switch ( $type )
		{
			case 'mysql':
				return gmdate( $format, ( time( ) + ( get_option( 'gmt_offset' ) * 3600 ) )  );
				break;
			case 'timestamp':
				return time( ) + ( get_option( 'gmt_offset' ) * 3600  );
				break;
		}
	}

	public static function sec_to_time( $seconds, $ampm = true )
	{
		if( $ampm )
			return date( 'h:i a', $seconds );
		else
			return date( 'H:i', $seconds );
	}

	public static function time_to_sec( $time )
	{
		if( strpos($time, 'am') !== false || strpos($time, 'pm') !== false )
		{
			// 12 am pm
			$sep = explode( ' ', $time );
			$ampm = $sep[1];
			$sep = explode( ':', $sep[0] );
			$hours = intval( $sep[0] );
			$minutes = intval( $sep[1] ) * 60;
			if( $ampm == 'pm' && $hours < 12 )
				$hours += 12;
			elseif ( $ampm == 'am' && $hours == 12 )
			$hours = 0;
			$hours = $hours * 60 * 60;
			return $hours + $minutes;
		}
		else
		{
			// 24
			$sep = explode( ':', $time );
			$hours = intval( $sep[0] ) * 60 * 60;
			$minutes = intval( $sep[1] ) * 60;
			return $hours + $minutes;
		}
		return false;
	}

	public static function redirect( $target = '', $status = 302 )
	{
		if ( '' == $target && isset( $_REQUEST['_wp_http_referer'] ) )
			$target = esc_url( $_REQUEST['_wp_http_referer'] );

		wp_redirect( $target, $status );
		die();
	}

	public static function process_file_upload( $file, $post_id = 0 )
	{
		// file errors already checked
		require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
		require_once( ABSPATH . 'wp-admin' . '/includes/file.php' );
		require_once( ABSPATH . 'wp-admin' . '/includes/media.php' );

		$attachment_id = media_handle_upload( $file, $post_id );
		return $attachment_id;
	}

	public static function get_ip_country()
	{
		$ip = self::user_ip();
		$get_country = wp_remote_get( 'http://www.webservicex.net/geoipservice.asmx/GetGeoIP?IPAddress=' . $ip );
		if( !is_wp_error($get_country) && $get_country['response']['code'] == 200 )
		{
			$response = self::parse_xml_json( $get_country['body'] );
			if( is_object($response) && isset($response->ReturnCode) && $response->ReturnCode == '1' && isset($response->CountryCode) )
				return $response->CountryCode == 'ZZZ' ? false : strtolower( $response->CountryCode );
		}
		return false;
	}

	public static function parse_xml_json( $xml )
	{
		$xml = str_replace( array("\n", "\r", "\t"), '', $xml );
		$xml = trim( str_replace('"', "'", $xml) );
		$simpleXml = simplexml_load_string( $xml );
		$json = json_decode( json_encode($simpleXml) );
		return $json;
	}

	public static function is_url( $url )
	{
		return preg_match( '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url );
	}

	public static function get_youtube_id( $youtube_url )
	{
		parse_str( parse_url($youtube_url, PHP_URL_QUERY), $youtube_vars );
		return isset( $youtube_vars['v'] ) ? $youtube_vars['v'] : false;
	}

	public static function get_youtube_sreenshot( $video_id, $big = true )
	{
		return 'http://img.youtube.com/vi/' . $video_id . '/' . ( $big ? '0' : '2' ) . '.jpg';
	}

	public static function qr_code( $data, $key, $size = 's', $img_tag = true, $EC_level = 'H', $margin = '0' )
	{
		$upload_dir = wp_upload_dir();
		$file_name = sanitize_file_name( 'qr_' . $key . '_' . $size ) . '.png';
		$file_path = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $file_name;
		$file_url = $upload_dir['baseurl'] . '/' . $file_name;

		switch( $size )
		{
			case 's':
				$size = '135';
				break;
			case 'm':
				$size = '162';
				break;
			case 'l':
				$size = '216';
				break;
		}

		if( file_exists($file_path) )
		{
			$img = $file_url;
		}
		else
		{
			$data = urlencode( $data );
			$img = 'http://chart.apis.google.com/chart';
			$img .= '?chs=' . $size . 'x' . $size;
			$img .= '&cht=qr';
			$img .= '&chld=' . $EC_level . '|' . $margin; // $EC_level = L | M | Q | H
			$img .= '&chl=' . $data;

			$image_data = wp_remote_get( $img );
			if( $image_data['response']['code'] == 200 )
			{
				$ifp = @ fopen( $file_path, 'wb' );
				if ( ! $ifp )
					return 'write';

				@fwrite( $ifp, $image_data['body'] );
				fclose( $ifp );
				clearstatcache();

				$stat = @stat( dirname($file_path) );
				$perms = $stat['mode'] & 0007777;
				$perms = $perms & 0000666;
				@chmod( $file_path, $perms );
				clearstatcache();
					
				$img = $file_url;
			}
		}

		if( $img_tag )
			$img = '<img src="' . $img . '" alt="QR code" width="'.$size.'" height="'.$size.'"/>';

		return $img;
	}

	public static function array_to_xml( $data, $root = 'places', $custom_node = true, $node_name = 'place' )
	{
		header ( "Content-Type:application/json" );
		return current_user_can( 'manage_options') && isset($_REQUEST['dump']) ? dump_data($data) : json_encode($data );

		header ( "Content-Type:text/xml" );
		$xml = '<?xml version="1.0" encoding="utf-8"?><' . $root . '>';
		foreach( $data as $index => $node )
		{
			if( $custom_node )
				$xml .= '<' . $node_name . ' index="' . $index . '">';
			else
				$xml .= '<' . $index . '>';

			$xml .= self::parse_xml_node( $node );

			if( $custom_node )
				$xml .= '</' . $node_name . '>';
			else
				$xml .= '</' . $index . '>';
		}
		$xml .= '</' . $root . '>';
		return $xml;
	}

	public static function parse_xml_node( $node )
	{
		$node = is_object( $node) ? get_object_vars($node ) : $node;
		$out = '';
		if( is_array($node) )
		{
			foreach ( $node as $key => $value )
			{
				if( is_object($value) || is_array($value) )
				{
					$value = self::parse_xml_node( $value );
					$key = 'item';
				}
					
				if( is_numeric($key) )
					$out .= '<item index="' . $key . '">' . $value . '</item>';
				else
					$out .= '<' . $key . '>' . $value . '</' . $key . '>';
			}
		}
		else
		{
			$out .= $node;
		}
		return $out;
	}

	public static function user_ip()
	{
		$list = array (
				'HTTP_CLIENT_IP',
				'HTTP_X_FORWARDED_FOR',
				'HTTP_X_FORWARDED',
				'HTTP_X_CLUSTER_CLIENT_IP',
				'HTTP_FORWARDED_FOR',
				'HTTP_FORWARDED',
				'REMOTE_ADDR',
		);

		foreach ( $list as $key )
		{
			if ( array_key_exists($key, $_SERVER) === true )
			{
				foreach ( explode(',', $_SERVER[$key]) as $ip )
				{
					if ( filter_var($ip, FILTER_VALIDATE_IP) !== false )
						return $ip;
				}
			}
		}
		return false;
	}

	public static function calc_rows_offset( $page_rows, $totalrows, $pagenum )
	{
		if( $totalrows < 1 )
			$totalrows = 1;

		if( !$pagenum )
			$pagenum = 1;

		$last = ceil( $totalrows/$page_rows );
		if ( $pagenum > $last )
			$pagenum = $last;

		return 'LIMIT ' . ( $pagenum - 1 ) * $page_rows . ',' . $page_rows;
	}

	public static function delete_file( $file )
	{
		if( file_exists($file) )
			unlink( $file );
	}

	public static function array_obj_diff( $array1, $array2 )
	{
		foreach ( $array1 as $key => $value )
		{
			$array1[$key] = serialize ( $value );
		}

		foreach ( $array2 as $key => $value )
		{
			$array2[$key] = serialize ( $value );
		}

		$array_diff = array_diff ( $array1, $array2 );

		foreach ( $array_diff as $key => $value )
		{
			$array_diff[$key] = unserialize ( $value );
		}

		return $array_diff;
	}

	public static function age( $birthday )
	{
		if( is_numeric($birthday) )
			$birthday = date( 'd-m-Y', $birthday );

		list( $day,$month,$year) = explode("-", $birthday );

		$year_diff = date( "Y" ) - $year;
		$month_diff = date( "m" ) - $month;
		$day_diff = date( "d" ) - $day;

		if ( $month_diff < 0 )
			$year_diff--;
		elseif ( ($month_diff==0) && ($day_diff < 0) )
		$year_diff--;

		return $year_diff;
	}

	public static function confirm_code( $user_id, $length = 6 )
	{
		$chars = explode( '  ', 'A  B  C  D  E  F  G  H  I  J  K  L  M  N  O  P  R  S  T  V  W  X  Y  Z' );
		$char = $chars[array_rand( $chars )];
		return $char . $user_id . strtoupper( self::rand_string(2) );
	}

	public static function time_diff( $first, $second )
	{
		$diff = abs( $first - $second );

		$r = array();
		$r['years'] = floor( $diff / (365*60*60*24) );
		$r['months'] = floor( ($diff - $r['years'] * 365*60*60*24) / (30*60*60*24) );
		$r['days'] = floor( ($diff - ($r['years'] * 365*60*60*24) - ($r['months'] * 30*60*60*24)) / (60*60*24) );

		return $r;
	}

	public static function content_lachooser( $content, $target_lang = '' )
	{
		if( empty($target_lang) ) $target_lang = $GLOBALS['lang'];

		preg_match( '/(.?)\[(' . $target_lang . ')\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)/s', $content, $matchs );

		if( count($matchs) < 1 )
		{
			$content = str_ireplace( '[en]', '', $content );
			$content = str_ireplace( '[/en]', '', $content );
			return $content;
		}
		$matchs[0] = str_ireplace( '>[' . $target_lang . ']', '', $matchs[0] );
		$matchs[0] = str_ireplace( '[/' . $target_lang . ']<', '', $matchs[0] );
		$matchs[0] = str_ireplace( '[' . $target_lang . ']', '', $matchs[0] );
		$matchs[0] = str_ireplace( '[/' . $target_lang . ']', '', $matchs[0] );
		return $matchs[0];
	}

	public static function is_ajax()
	{
		return ( defined( 'DOING_AJAX') || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')  );
	}

	public static function image_resize_dimensions( $orig_w, $orig_h, $dest_w, $dest_h, $crop = false )
	{

		if ( $orig_w <= 0 || $orig_h <= 0 )
			return false;
		// at least one of dest_w or dest_h must be specific
		if ( $dest_w <= 0 && $dest_h <= 0 )
			return false;

		if ( $crop ) {
			// crop the largest possible portion of the original image that we can size to $dest_w x $dest_h
			$aspect_ratio = $orig_w / $orig_h;
			$new_w = min( $dest_w, $orig_w );
			$new_h = min( $dest_h, $orig_h );

			if ( !$new_w ) {
				$new_w = intval( $new_h * $aspect_ratio );
			}

			if ( !$new_h ) {
				$new_h = intval( $new_w / $aspect_ratio );
			}

			$size_ratio = max( $new_w / $orig_w, $new_h / $orig_h );

			$crop_w = round( $new_w / $size_ratio );
			$crop_h = round( $new_h / $size_ratio );

			$s_x = floor( ( $orig_w - $crop_w) / 2  );
			$s_y = floor( ( $orig_h - $crop_h) / 2  );
		} else {
			// don't crop, just resize using $dest_w x $dest_h as a maximum bounding box
			$crop_w = $orig_w;
			$crop_h = $orig_h;

			$s_x = 0;
			$s_y = 0;

			list( $new_w, $new_h ) = wp_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
		}

		// the return array matches the parameters to imagecopyresampled()
		// int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
		return array( 0, 0, ( int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h  );
	}

	public static function image_resize( $file, $max_w, $max_h, $crop = false, $suffix = null, $dest_path = null, $jpeg_quality = 90 )
	{
		$image = wp_load_image( $file );
		if ( !is_resource( $image ) )
			return new WP_Error( 'error_loadiimage', $image, $file );

		$size = @getimagesize( $file );
		if ( !$size )
			return new WP_Error( 'invalid_image', __( 'Could not read image size', self::$text_domain ), $file );
		list( $orig_w, $orig_h, $orig_type ) = $size;

		$dims = image_resize_dimensions( $orig_w, $orig_h, $max_w, $max_h, $crop );
		if ( !$dims )
			return new WP_Error( 'error_gettidimensions', __( 'Could not calculate resized image dimensions')  );
		list( $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h ) = $dims;

		$newimage = wp_imagecreatetruecolor( $dst_w, $dst_h );

		imagecopyresampled( $newimage, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

		// convert from full colors to index colors, like original PNG.
		if ( IMAGETYPE_PNG == $orig_type && function_exists( 'imageistruecolor') && !imageistruecolor( $image )  )
			imagetruecolortopalette( $newimage, false, imagecolorstotal( $image ) );

		// we don't need the original in memory anymore
		imagedestroy( $image );

		$info = pathinfo( $file );
		$dir = $info['dirname'];
		$ext = $info['extension'];
		$name = wp_basename( $file, ".$ext" );

		if ( !is_null( $dest_path) and $_dest_path = realpath($dest_path)  )
			$dir = $_dest_path;
		$destfilename = "{$dir}/{$name}.{$ext}";
		if( !is_null($suffix) )
			$destfilename = "{$dir}/{$name}-{$suffix}.{$ext}";

		if ( IMAGETYPE_GIF == $orig_type ) {
			if ( !imagegif( $newimage, $destfilename ) )
				return new WP_Error( 'resize_path_invalid', __( 'Resize path invalid' ) );
		} elseif ( IMAGETYPE_PNG == $orig_type ) {
			if ( !imagepng( $newimage, $destfilename ) )
				return new WP_Error( 'resize_path_invalid', __( 'Resize path invalid' ) );
		} else {
			// all other formats are converted to jpg
			$destfilename = "{$dir}/{$name}.jpg";
			if( !is_null($suffix) )
				$destfilename = "{$dir}/{$name}-{$suffix}.{$ext}";

			if ( !imagejpeg( $newimage, $destfilename, apply_filters( 'jpeg_quality', $jpeg_quality, 'image_resize' ) ) )
				return new WP_Error( 'resize_path_invalid', __( 'Resize path invalid' ) );
		}

		imagedestroy( $newimage );

		// Set correct file permissions
		$stat = stat( dirname( $destfilename ));
		$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
		@ chmod( $destfilename, $perms );

		return $destfilename;
	}

	public static function clear_left_overs( $dirname, $self_delete = false )
	{
		if ( is_dir($dirname) )
			$dir_handle = opendir( $dirname );

		if ( !$dir_handle )
			return false;

		while( $file = readdir($dir_handle) )
		{
			if ( $file != "." && $file != ".." )
			{
				if ( !is_dir($dirname . "/" . $file) )
					@unlink( $dirname . "/" . $file );
				else
					self::clear_left_overs( $dirname . '/' . $file, true );
			}
		}

		closedir( $dir_handle );
		if ( $self_delete )
			@rmdir( $dirname );

		return true;
	}

	public static function rand_string( $length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890' )
	{
		$chars_length = ( strlen($chars) - 1 );
		$string = $chars{rand( 0, $chars_length )};
		for ( $i = 1; $i < $length; $i = strlen($string) )
		{
			$r = $chars{rand( 0, $chars_length )};
			if ( $r != $string{$i - 1} ) $string .= $r;
		}
		return $string;
	}

	public static function is_plain_text( $string )
	{
		if( preg_match('/^[A-Za-z0-9\'_,:\s\p{Arabic}]+$/u', $string) )
			return true;
		else
			return false;
	}

	public static function is_phone_number( $string )
	{
		if( preg_match('/^((\+\d{1,2})?\d{7,12})$/', $string) )
			return true;
		else
			return false;
	}

	public static function is_str_length_between( $string, $min, $max )
	{
		if( strlen($string) >= $min && strlen($string) <= $max )
			return true;
		else
			return false;
	}

	public static function is_valid_password( $pass, $pattern = '/^[a-zA-Z0-9_+]+$/' )
	{
		return preg_match( $pattern, $pass );
	}

	public static function is_number_between( $num, $min, $max )
	{
		if( is_numeric($max) )
		{
			if( $num >= $min && $num <= $max )
				return true;
		}
		else
		{
			if( $num >= $min )
				return true;
		}
		return false;
	}

	public static function form_error( $field, $message, &$holder = NULL )
	{
		if( !isset( $_SESSION['form_errors'] ) )
			$_SESSION['form_errors'] = array();

		$_SESSION['form_errors'][$field] = $message;

		if( !is_null($holder) )
		{
			if( is_array($holder) )
				unset( $holder[$field] );
			else
				unset( $holder->$field );
		}
	}

	public static function has_form_errors()
	{
		if( isset( $_SESSION['form_errors'] ) && count( $_SESSION['form_errors'] ) > 0 )
			return true;

		return false;
	}

	public static function clear_form_errors()
	{
		$_SESSION['form_errors'] = array();
	}

	public static function show_form_errors( $raw = false, $as_array = false )
	{
		if( isset( $_SESSION['form_errors'] ) && count( $_SESSION['form_errors'] ) > 0 )
		{
			if( $as_array )
				return $_SESSION['form_errors'];

			$out = "";
			foreach( $_SESSION['form_errors'] as $field => $message )
			{
				if( $raw )
					$out .= $message . "\n\r";
				else
					$out .= self::show_message( $message, 'error' );

				unset( $_SESSION['form_errors'][$field] );
			}
			return $out;
		}
		return false;
	}

	public static function get_message( &$messages, $from = 'get', $key = 'message' )
	{
		$values = array(
				'get' => &$_GET,
				'post' => &$_POST,
				'request' => &$_REQUEST,
		);

		if( !isset($values[$from]) )
			return false;

		if( isset($values[$from][$key], $messages[$values[$from][$key]]) )
			return $messages[$values[$from][$key]];

		return false;
	}

	public static function show_message( $message, $type = 'general' )
	{
		return '<div class="alert '. $type .'"><div class="msg">'. $message .'</div></div>';
	}

	public static function catch_request_data()
	{
		if( '' == session_id() )
			session_start();

		// post data saving
		foreach( $_REQUEST as $key => $val )
		{
			if( !isset($_SESSION['request_data']) )
				$_SESSION['request_data'] = array();

			$_SESSION['request_data'][$key] = $val;
		}
	}

	public static function clear_values()
	{
		unset( $_SESSION['request_data'] );
	}

	public static function get_value( $key, $query_var = '', $session = false, $encoded = false, $html = false, $allowed = null )
	{
		$value = '';
		if( '' != $query_var )
			$value = get_query_var( $query_var );
		elseif( isset($_REQUEST[$key]) )
			$value = $_REQUEST[$key];
		elseif( $session && isset($_SESSION['request_data']) && isset($_SESSION['request_data'][$key]) )
			$value = $_SESSION['request_data'][$key];

		if( '' == $value || empty($value) )
			return '';

		return self::clean_string( $value, $encoded, $html, $allowed );
	}

	public static function clean_string( $value, $encoded = false, $html = false, $allowed = null )
	{
		$value = trim( $value );
		if( $encoded )
			$value = urldecode( $value );

		if( $html )
		{
			$value = nl2br( $value );
			$value = self::remove_extra_space( $value );
			$value = force_balance_tags( $value );
			if( is_array($allowed) )
				$value = wp_kses( $value, $allowed );
			elseif( is_null($allowed) )
			$value = wp_kses_data( $value );
		}
		else
		{
			$value = sanitize_text_field( $value );
		}
		return str_ireplace( '\\', '', $value );
	}

	public static function remove_extra_space( $string )
	{
		$string = trim( $string );
		return preg_replace( '/\s\s+/', ' ', $string );
	}

	public static function is_odd( $num )
	{
		if ( $num % 2 )
			return true;
		else
			return false;
	}

	public static function get_page_by_slug( $page_slug, $output = OBJECT, $post_type = 'page' )
	{
		global $wpdb;

		$page = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type= %s", $page_slug, $post_type ) );
		if ( $page )
		{
			if ( 'id' == $output )
				return $page;

			return get_post( $page, $output );
		}

		return null;
	}

	public static function get_permalink_by_slug( $slug )
	{
		return get_permalink( self::get_page_by_slug( $slug, 'id' ) );
	}

	public static function substr_words( $paragraph, $num_words, $wrapper = 'font', $rest = ' ...' )
	{
		$org_string = $paragraph;
		$paragraph = explode( ' ', self::remove_extra_space($paragraph) );

		if( count($paragraph) <= $num_words )
			return $org_string;

		$paragraph = array_slice( $paragraph, 0, $num_words );
		$out = '<' . $wrapper . ' title="' . $org_string . '">' . implode ( ' ', $paragraph ) . $rest . '</' . $wrapper . '>';

		if( '' == $wrapper )
			$out = implode( ' ', $paragraph ) . $rest;

		return $out;
	}
}

if( !function_exists( 'dump_data_export' ) )
{
	function dump_data_export( $data, $type = false )
	{
		return '<pre>'. ( $type ? var_export( $data, true ) : print_r( $data, true ) ) .'</pre>';
	}
}

if( !function_exists( 'trace_data' ) )
{
	function trace_data()
	{
		$args = func_get_args();
		for ( $i = 0; $i < func_num_args(); $i++ )
		{
			dump_data( $args[$i] );
		}
	}
}

if( !function_exists( 'dump_data' ) )
{
	function dump_data( $data, $type = false )
	{
		// normal
		echo '<pre>';
		$type ? var_dump( $data ) : print_r( $data );
		echo '</pre>';
	}
}

if( !function_exists( 'multi_dump_data' ) )
{
	function multi_dump_data()
	{
		$args = func_get_args();
		foreach ( $args as $arg )
		{
			dump_data( $arg );
		}
	}
}