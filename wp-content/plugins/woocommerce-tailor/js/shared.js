/**
 * Shared JS
 */
( function ( window ) {
	jQuery( function( $ ) {

		// ajax loading
		var $body = $( 'body' ),
			$ajax_loading = null;

		$body.ajaxStart( function() {
			$ajax_loading = $( '<div id="ajax-loading"></div>' ).appendTo( $body );
		} );

		$body.ajaxStop( clear_ajax_loading )
			.ajaxComplete( clear_ajax_loading )
			.ajaxError( clear_ajax_loading )
			.ajaxSuccess( clear_ajax_loading );

		function clear_ajax_loading() {
			if( $ajax_loading && $ajax_loading.remove )
				$ajax_loading.remove();
		}

	});

	window.call_func = function( cb ) {
		var func;

		if ( typeof cb === 'string' ) {
			func = ( typeof this[cb] === 'function' ) ? this[cb] : func = ( new Function( null, 'return ' + cb ) )();
		} else if ( Object.prototype.toString.call(cb) === '[object Array]' ) {
			func = ( typeof cb[0] === 'string' ) ? eval( cb[0] + "['" + cb[1] + "']" ) : func = cb[0][cb[1]];
		} else if ( typeof cb === 'function' ) {
			func = cb;
		}

		if ( typeof func !== 'function' ) {
			throw new Error( func + ' is not a valid function' );
		}

		var parameters = Array.prototype.slice.call( arguments, 1 );
		if ( typeof cb[0] === 'string' ) {
			return func.apply( eval( cb[0] ), parameters );
		} else if ( typeof cb[0] !== 'object' ) {
			return func.apply( null, parameters);
		} else {
			return func.apply( cb[0], parameters );
		}
	};

	window.round = function( value, precision, mode ) {
		//  discuss at: http://phpjs.org/functions/round/
		var m, f, isHalf, sgn; // helper variables
		// making sure precision is integer
		precision |= 0;
		m = Math.pow(10, precision);
		value *= m;
		// sign of the number
		sgn = (value > 0) | -(value < 0);
		isHalf = value % 1 === 0.5 * sgn;
		f = Math.floor(value);

		if (isHalf) {
			switch (mode) {
			case 'PHP_ROUND_HALF_DOWN':
				// rounds .5 toward zero
				value = f + (sgn < 0);
				break;
			case 'PHP_ROUND_HALF_EVEN':
				// rouds .5 towards the next even integer
				value = f + (f % 2 * sgn);
				break;
			case 'PHP_ROUND_HALF_ODD':
				// rounds .5 towards the next odd integer
				value = f + !(f % 2);
				break;
			default:
				// rounds .5 away from zero
				value = f + (sgn > 0);
			}
		}

		return (isHalf ? value : Math.round(value)) / m;
	};

	window.number_format = function( number, decimals, dec_point, thousands_sep ) {
		number = ( number + '').replace(/[^0-9+\-Ee.]/g, '' );
		var n = !isFinite( +number ) ? 0 : +number,
			prec = !isFinite( +decimals) ? 0 : Math.abs(decimals ),
			sep = ( typeof thousands_sep === 'undefined' ) ? ',' : thousands_sep,
			dec = ( typeof dec_point === 'undefined' ) ? '.' : dec_point,
			s = '',
			toFixedFix = function ( n, prec ) {
				var k = Math.pow( 10, prec );
				return '' + Math.round( n * k ) / k;
			};
			// Fix for IE parseFloat( 0.55).toFixed(0 ) = 0;
			s = ( prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.' );
			if ( s[0].length > 3 ) {
				s[0] = s[0].replace( /\B(?=(?:\d{3})+(?!\d))/g, sep );
			}
			if ( (s[1] || '').length < prec ) {
				s[1] = s[1] || '';
				s[1] += new Array( prec - s[1].length + 1).join('0' );
			}
			return s.join( dec );
	};
	
	// define removeByIndex method as part of the array  
	Array.prototype.removeByIndex = function( index ) {
		this.splice( index, 1 );
	};

	// attach the .compare method to Array's prototype to call it on any array
	Array.prototype.compare = function ( array ) {
		// if the other array is a falsy value, return
		if ( !array )
			return false;

		// compare lengths - can save a lot of time
		if ( this.length != array.length )
			return false;

		// convert into joined json and compare
		return array_jsonify( this ).join( ) == array_jsonify( array ).join( );
	};
	
	// convert array items to json
	window.array_jsonify = function( array ) {
		// convert into joined json
		if ( typeof Array.prototype.map == 'undefined' ) {
			// use JQuery Method
			return jQuery.map( array, json_encode );
		} else {
			// use native Method
			return array.map( json_encode );
		}
	};
	
	// php like array_diff
	window.array_diff = function ( arr1 ) {
		var retArr = [],
		argl = arguments.length,
		k1 = '',
		i = 1,
		k = '',
		arr = [];
		
		arr1keys: for ( k1 in arr1 ) {
			for ( i = 1; i < argl; i++ ) {
				arr = arguments[i];
				for ( k in arr ) {
					if ( json_encode( arr[k] ) === json_encode( arr1[k1] ) ) {
						// If it reaches here, it was found in at least one array, so try next value
						continue arr1keys;
					}
				}
				retArr[k1] = arr1[k1];
			}
		}
		
		return retArr;
	};

	// check if data isset
	window.isset = function () {
		var a = arguments,
		l = a.length,
		i = 0,
		undef;

		if ( l === 0 ) {
			throw new Error( 'Empty isset' );
		}

		while ( i !== l ) {
			if ( a[i] === undef || a[i] === null ) {
				return false;
			}
			i++;
		}
		return true;
	};

	// check if data is object
	window.is_object = function ( mixed_var ) {
		if ( Object.prototype.toString.call(mixed_var) === '[object Array]' ) {
			return false;
		}
		return mixed_var !== null && typeof mixed_var == 'object';
	};

	// php like
	window.ucwords = function ( str ) {
		return ( str + '').replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function ($1 ) {
			return $1.toUpperCase();
		});
	};

	// php like
	window.ucfirst = function ( str ) {
		str += '';
		var f = str.charAt( 0).toUpperCase( );
		return f + str.substr( 1 );
	};

	// php like
	window.trim = function ( str, charlist ) {
		var whitespace, l = 0, i = 0; 
		str += '';
		
		if ( !charlist ) {
			// default list
			whitespace = " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
		} else {
			// preg_quote custom list
			charlist += '';
			whitespace = charlist.replace( /([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1' );
		}

		l = str.length;
		for ( i = 0; i < l; i++ ) {
			if ( whitespace.indexOf(str.charAt(i)) === -1 ) {
				str = str.substring( i );
				break;
			}
		}

		l = str.length;
		for ( i = l - 1; i >= 0; i-- ) {
			if ( whitespace.indexOf(str.charAt(i)) === -1 ) {
				str = str.substring( 0, i + 1 );
				break;
			}
		}

		return whitespace.indexOf( str.charAt(0)) === -1 ? remove_extra_space( str  ) : '';
	};

	// get query string value
	window.get_query_value = function ( name, url ) {
		name = name.replace( /[\[]/, "\\\[").replace(/[\]]/, "\\\]" );
		var regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" ),
			results = typeof url == 'string' ? regex.exec( url.substr(url.indexOf('?'))) : regex.exec(location.search );
		return results == null ? "" : decodeURIComponent( results[1].replace(/\+/g, " ") );
	};
	
	// set query string value
	window.update_query_value = function( uri, key, value ) {
		var re = new RegExp( "([?|&])" + key + "=.*?(&|$)", "i" );
		separator = uri.indexOf( '?' ) !== -1 ? "&" : "?";
		if ( uri.match(re) ) {
			return uri.replace( re, '$1' + key + "=" + value + '$2' );
		}
		else {
			return uri + separator + key + "=" + value;
		}
	};

	// remove extra white spaces
	window.remove_extra_space = function ( str ) {
		return str.replace( /\s+/, ' ' );
	};

	// php preg_quote like
	window.preg_quote = function ( str ) {
		return ( str+'').replace(/([\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:])/g, "\\$1" );
	};

	// php like sprintf
	window.sprintf = function () {
		var regex = /%%|%(\d+\$)?([-+\'#0 ]*)(\*\d+\$|\*|\d+)?(\.(\*\d+\$|\*|\d+))?([scboxXuideEfFgG])/g;
		var a = arguments;
		var i = 0;
		var format = a[i++];
		
		// pad()
		var pad = function (str, len, chr, leftJustify) {
			if (!chr) {
				chr = ' ';
			}
			var padding = (str.length >= len) ? '' : new Array(1 + len - str.length >>> 0)
			.join(chr);
			return leftJustify ? str + padding : padding + str;
		};
		
		// justify()
		var justify = function (value, prefix, leftJustify, minWidth, zeroPad, customPadChar) {
			var diff = minWidth - value.length;
			if (diff > 0) {
				if (leftJustify || !zeroPad) {
					value = pad(value, minWidth, customPadChar, leftJustify);
				} else {
					value = value.slice(0, prefix.length) + pad('', diff, '0', true) + value.slice(prefix.length);
				}
			}
			return value;
		};
		
		// formatBaseX()
		var formatBaseX = function (value, base, prefix, leftJustify, minWidth, precision, zeroPad) {
			// Note: casts negative numbers to positive ones
			var number = value >>> 0;
			prefix = prefix && number && {
				'2': '0b',
				'8': '0',
				'16': '0x'
			}[base] || '';
			value = prefix + pad(number.toString(base), precision || 0, '0', false);
			return justify(value, prefix, leftJustify, minWidth, zeroPad);
		};
		
		// formatString()
		var formatString = function (value, leftJustify, minWidth, precision, zeroPad, customPadChar) {
			if (precision != null) {
				value = value.slice(0, precision);
			}
			return justify(value, '', leftJustify, minWidth, zeroPad, customPadChar);
		};
		
		// doFormat()
		var doFormat = function (substring, valueIndex, flags, minWidth, _, precision, type) {
			var number, prefix, method, textTransform, value;
			
			if (substring === '%%') {
				return '%';
			}
			
			// parse flags
			var leftJustify = false;
			var positivePrefix = '';
			var zeroPad = false;
			var prefixBaseX = false;
			var customPadChar = ' ';
			var flagsl = flags.length;
			for (var j = 0; flags && j < flagsl; j++) {
				switch (flags.charAt(j)) {
				case ' ':
					positivePrefix = ' ';
					break;
				case '+':
					positivePrefix = '+';
					break;
				case '-':
					leftJustify = true;
					break;
				case "'":
					customPadChar = flags.charAt(j + 1);
					break;
				case '0':
					zeroPad = true;
					customPadChar = '0';
					break;
				case '#':
					prefixBaseX = true;
					break;
				}
			}
			
			// parameters may be null, undefined, empty-string or real valued
			// we want to ignore null, undefined and empty-string values
			if (!minWidth) {
				minWidth = 0;
			} else if (minWidth === '*') {
				minWidth = +a[i++];
			} else if (minWidth.charAt(0) == '*') {
				minWidth = +a[minWidth.slice(1, -1)];
			} else {
				minWidth = +minWidth;
			}
			
			// Note: undocumented perl feature:
			if (minWidth < 0) {
				minWidth = -minWidth;
				leftJustify = true;
			}
			
			if (!isFinite(minWidth)) {
				throw new Error('sprintf: (minimum-)width must be finite');
			}
			
			if (!precision) {
				precision = 'fFeE'.indexOf(type) > -1 ? 6 : (type === 'd') ? 0 : undefined;
			} else if (precision === '*') {
				precision = +a[i++];
			} else if (precision.charAt(0) == '*') {
				precision = +a[precision.slice(1, -1)];
			} else {
				precision = +precision;
			}
			
			// grab value using valueIndex if required?
			value = valueIndex ? a[valueIndex.slice(0, -1)] : a[i++];
			
			switch (type) {
			case 's':
				return formatString(String(value), leftJustify, minWidth, precision, zeroPad, customPadChar);
			case 'c':
				return formatString(String.fromCharCode(+value), leftJustify, minWidth, precision, zeroPad);
			case 'b':
				return formatBaseX(value, 2, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
			case 'o':
				return formatBaseX(value, 8, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
			case 'x':
				return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
			case 'X':
				return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad)
				.toUpperCase();
			case 'u':
				return formatBaseX(value, 10, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
			case 'i':
			case 'd':
				number = +value || 0;
				// Plain Math.round doesn't just truncate
				number = Math.round(number - number % 1);
				prefix = number < 0 ? '-' : positivePrefix;
				value = prefix + pad(String(Math.abs(number)), precision, '0', false);
				return justify(value, prefix, leftJustify, minWidth, zeroPad);
			case 'e':
			case 'E':
			case 'f': // Should handle locales (as per setlocale)
			case 'F':
			case 'g':
			case 'G':
				number = +value;
				prefix = number < 0 ? '-' : positivePrefix;
				method = ['toExponential', 'toFixed', 'toPrecision']['efg'.indexOf(type.toLowerCase())];
				textTransform = ['toString', 'toUpperCase']['eEfFgG'.indexOf(type) % 2];
				value = prefix + Math.abs(number)[method](precision);
				return justify(value, prefix, leftJustify, minWidth, zeroPad)[textTransform]();
			default:
				return substring;
			}
		};
		
		return format.replace(regex, doFormat);
	};

	// php like json_encode
	window.json_encode = function ( mixed_val ) {
		var retVal, json = this.window.JSON;
		try {
			if ( typeof json === 'object' && typeof json.stringify === 'function' ) {
				retVal = json.stringify( mixed_val ); // Errors will not be caught here if our own equivalent to resource
				//  ( an instance of PHPJS_Resource ) is used
				if ( retVal === undefined ) {
					throw new SyntaxError( 'json_encode' );
				}
				return retVal;
			}
			
			var value = mixed_val;
			
			var quote = function ( string ) {
				var escapable = /[\\\"\u0000-\u001f\u007f-\u009f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
				var meta = { // table of character substitutions
						'\b': '\\b',
						'\t': '\\t',
						'\n': '\\n',
						'\f': '\\f',
						'\r': '\\r',
						'"': '\\"',
						'\\': '\\\\'
				};
				
				escapable.lastIndex = 0;
				return escapable.test( string) ? '"' + string.replace(escapable, function (a ) {
					var c = meta[a];
					return typeof c === 'string' ? c : '\\u' + ( '0000' + a.charCodeAt(0).toString(16)).slice(-4 );
				}) + '"' : '"' + string + '"';
			};
			
			var str = function ( key, holder ) {
				var gap = '';
				var indent = '    ';
				var i = 0; // The loop counter.
				var k = ''; // The member key.
				var v = ''; // The member value.
				var length = 0;
				var mind = gap;
				var partial = [];
				var value = holder[key];
				
				// If the value has a toJSON method, call it to obtain a replacement value.
				if ( value && typeof value === 'object' && typeof value.toJSON === 'function' ) {
					value = value.toJSON( key );
				}
				
				// What happens next depends on the value's type.
				switch ( typeof value ) {
				case 'string':
					return quote( value );
					
				case 'number':
					// JSON numbers must be finite. Encode non-finite numbers as null.
					return isFinite( value) ? String(value ) : 'null';
					
				case 'boolean':
				case 'null':
					// If the value is a boolean or null, convert it to a string. Note:
					// typeof null does not produce 'null'. The case is included here in
					// the remote chance that this gets fixed someday.
					return String( value );
					
				case 'object':
					// If the type is 'object', we might be dealing with an object or an array or
					// null.
					// Due to a specification blunder in ECMAScript, typeof null is 'object',
					// so watch out for that case.
					if ( !value ) {
						return 'null';
					}
					if ( (this.PHPJS_Resource && value instanceof this.PHPJS_Resource) || (window.PHPJS_Resource && value instanceof window.PHPJS_Resource) ) {
						throw new SyntaxError( 'json_encode' );
					}
					
					// Make an array to hold the partial results of stringifying this object value.
					gap += indent;
					partial = [];
					
					// Is the value an array?
					if ( Object.prototype.toString.apply(value) === '[object Array]' ) {
						// The value is an array. Stringify every element. Use null as a placeholder
						// for non-JSON values.
						length = value.length;
						for ( i = 0; i < length; i += 1 ) {
							partial[i] = str( i, value ) || 'null';
						}
						
						// Join all of the elements together, separated with commas, and wrap them in
						// brackets.
						v = partial.length === 0 ? '[]' : gap ? '[\n' + gap + partial.join( ',\n' + gap) + '\n' + mind + ']' : '[' + partial.join(',' ) + ']';
						gap = mind;
						return v;
					}
					
					// Iterate through all of the keys in the object.
					for ( k in value ) {
						if ( Object.hasOwnProperty.call(value, k) ) {
							v = str( k, value );
							if ( v ) {
								partial.push( quote(k) + (gap ? ': ' : ':') + v );
							}
						}
					}
					
					// Join all of the member texts together, separated with commas,
					// and wrap them in braces.
					v = partial.length === 0 ? '{}' : gap ? '{\n' + gap + partial.join( ',\n' + gap) + '\n' + mind + '}' : '{' + partial.join(',' ) + '}';
					gap = mind;
					return v;
				case 'undefined':
					// Fall-through
				case 'function':
					// Fall-through
				default:
					throw new SyntaxError( 'json_encode' );
				}
			};
			
			// Make a fake root object containing our value under the key of ''.
			// Return the result of stringifying the value.
			return str('', {
				'': value
			});
			
		} catch ( err ) { // Todo: ensure error handling above throws a SyntaxError in all cases where it could
			// ( i.e., when the JSON global is not available and there is an error )
			if ( !(err instanceof SyntaxError) ) {
				throw new Error( 'Unexpected error type in json_encode()' );
			}
			this.php_js = this.php_js || {};
			this.php_js.last_error_json = 4; // usable by json_last_error()
			return null;
		}
	};

	// debugging
	window.trace = function () {
		if( window.console && arguments.length ) {
			if ( arguments.length == 1 ) {
				console.log( arguments[0] );
			} else {
				console.log( arguments );
			}
		}
	};
} )( window );