<?php
/**
 * Bookit Helpers
 */

use Bookit\Vendor\StellarWP\Arrays\Arr;

/**
 * Generate Price
 * @param $price
 * @return string
 */
function bookit_price( $price ) {
	$settings           = \Bookit\Classes\Admin\SettingsController::get_settings();
	$formatted_price    = number_format($price, $settings['decimals_number'], $settings['decimals_separator'], $settings['thousands_separator']);

	if ( $settings['currency_position'] == 'left' ) {
		$formatted_price = $settings['currency_symbol'] . $formatted_price;
	} else {
		$formatted_price .= $settings['currency_symbol'];
	}

	return $formatted_price;
}

/**
 * Translated Date Time
 * @param $format
 * @param $timestamp
 * @return mixed
 */
function bookit_datetime_i18n( $format, $timestamp ) {
	$timezone_str   = get_option('timezone_string') ?: 'UTC';
	$timezone       = new \DateTimeZone($timezone_str);

	// The date in the local timezone.
	$date           = new \DateTime(null, $timezone);
	$date->setTimestamp($timestamp);
	$date_string    = $date->format('Y-m-d H:i:s');

	// Pretend the local date is UTC to get the timestamp
	// to pass to date_i18n().
	$utc_timezone   = new \DateTimeZone('UTC');
	$utc_date       = new \DateTime($date_string, $utc_timezone);
	$timestamp      = $utc_date->getTimestamp();

	return date_i18n($format, $timestamp, true);
}

/**
 * Generate List
 * @param $data
 * @param $key
 * @param $value
 * @param $elementor
 * @return array
 */
function bookit_data_to_list( $data, $key, $value, $elementor = false ) {
	if ( $elementor ) {
		$list = [ '' => esc_html__('Keep empty', 'bookit') ];
	} else {
		$list = [ esc_html__('Keep empty', 'bookit') => '' ];
	}

	if ( count($data) > 0 ) {
		foreach ( $data as $item ) {
			$list[$item[$key]] = $item[$value];
		}
	} else {
		if ( $elementor ) {
			$list = [ '' => esc_html__('Nothing found', 'bookit') ];
		} else {
			$list = [ esc_html__('Nothing found', 'bookit') => '' ];
		}
	}

	return $list;
}
/**
 * Convert PHP To Moment JS Date Format
 * @param $format
 * @return string
 */
function bookit_php_to_moment( $format ) {
	$replacements = [
		'd' => 'DD',
		'D' => 'ddd',
		'j' => 'D',
		'l' => 'dddd',
		'N' => 'E',
		'S' => 'o',
		'w' => 'e',
		'z' => 'DDD',
		'W' => 'W',
		'F' => 'MMMM',
		'm' => 'MM',
		'M' => 'MMM',
		'n' => 'M',
		't' => '', // no equivalent
		'L' => '', // no equivalent
		'o' => 'YYYY',
		'Y' => 'YYYY',
		'y' => 'YY',
		'a' => 'a',
		'A' => 'A',
		'B' => '', // no equivalent
		'g' => 'h',
		'G' => 'H',
		'h' => 'hh',
		'H' => 'HH',
		'i' => 'mm',
		's' => 'ss',
		'u' => 'SSS',
		'e' => 'zz', // deprecated since version 1.6.0 of moment.js
		'I' => '', // no equivalent
		'O' => '', // no equivalent
		'P' => '', // no equivalent
		'T' => '', // no equivalent
		'Z' => '', // no equivalent
		'c' => '', // no equivalent
		'r' => '', // no equivalent
		'U' => 'X',
	];
	$momentFormat = strtr($format, $replacements);
	return $momentFormat;
}

/**
 * Check if Pro plugin is active
 * @return bool
 */
function bookit_pro_active() {//todo remove
	return defined("BOOKIT_PRO_VERSION");
}

/**
 * Disable Pro Features
 * @return bool
 */
function bookit_pro_features_disabled() {//todo remove
	return bookit_pro_active() ? 'false' : 'true';
}

/**
 * Function to get inside array option value
 * key separated by .
 * ex-le: 'bookit_settings.first_key_of_option_bookit_settings.second_key_of_option_bookit_settings
 */

function get_option_by_path( $path ) {

	$keys = explode( '.', $path );
	$option = get_option( $keys[0] );

	if ( ! $option || ! is_array( $option ) ) {
		return false;
	}
	array_shift($keys);

	foreach ( $keys as $key ) {
		if ( is_array( $option )  && array_key_exists( $key, $option ) ) {
			$option = $option[ $key ];
		}else{
			return false;
		}
	}
	return $option;
}

/**
 * @param string $path | dot-delimited nested key values  'data.settings.etc'
 * @param array $array
 *
 * @return false|mixed
 */
function get_deep_array_value_by_path( $path, $array ) {
	$keys = explode( '.', $path );

	foreach ( $keys as $key ) {
		if ( is_array( $array )  && array_key_exists( $key, $array ) ) {
			$array = $array[ $key ];
		}else{
			return false;
		}
	}

	return $array;
}

/**
 * Encrypts a value using AES-256-CBC encryption and returns a hexadecimal-encoded string.
 *
 * @param string $value The value to be encrypted.
 *
 * @return string The encrypted and hexadecimal-encoded string.
 */
function bookit_crypt( $value ) {
	$vector   = "1234567890123412";
	$data     = openssl_encrypt( $value, 'aes-256-cbc', BOOKIT_FILE, OPENSSL_RAW_DATA, $vector );
	$hex_data = bin2hex( $data );

	return $hex_data;
}

/**
 * Decrypts a hexadecimal-encoded string that was encrypted using AES-256-CBC.
 *
 * @param string $value The hexadecimal-encoded string to be decrypted.
 *
 * @return string The decrypted value.
 */
function bookit_decrypt( $value ) {
	$vector   = "1234567890123412";
	$hex_data = $value;
	$data     = hex2bin( $hex_data );

	return openssl_decrypt( $data, 'aes-256-cbc', BOOKIT_FILE, OPENSSL_RAW_DATA, $vector );
}

/** check is date string by format */
function isDateByFormat($date, $format = 'Y-m-d H:i:s') {
	$dateVar = DateTime::createFromFormat($format, $date);
	return $dateVar && $dateVar->format($format) == $date;
}

if ( ! function_exists( 'bookit_get_request_var' ) ) {
	/**
	 * Tests to see if the requested variable is set either as a post field or as a URL
	 * param and returns the value if so.
	 *
	 * Post data takes priority over fields passed in the URL query. If the field is not
	 * set then $default (null unless a different value is specified) will be returned.
	 *
	 * The variable being tested for can be an array if you wish to find a nested value.
	 *
	 * @since 2.5.0
	 *
	 * @see   Arr::get()
	 *
	 * @param string|array $var
	 * @param mixed        $default
	 *
	 * @return mixed
	 */
	function bookit_get_request_var( $var, $default = null ) {
		$requests = [];

		// Prevent a slew of warnings every time we call this.
		if ( isset( $_REQUEST ) ) {
			$requests[] = (array) $_REQUEST;
		}

		if ( isset( $_GET ) ) {
			$requests[] = (array) $_GET;
		}

		if ( isset( $_POST ) ) {
			$requests[] = (array) $_POST;
		}

		if ( empty( $requests ) ) {
			return $default;
		}

		$unsafe = Arr::get_in_any( $requests, $var, $default );
		return bookit_sanitize_deep( $unsafe );
	}
}

if ( ! function_exists( 'bookit_sanitize_deep' ) ) {

	/**
	 * Sanitizes a value according to its type.
	 *
	 * The function will recursively sanitize array values.
	 *
	 * @since 4.9.20
	 *
	 * @param mixed $value The value, or values, to sanitize.
	 *
	 * @return mixed|null Either the sanitized version of the value, or `null` if the value is not a string, number or
	 *                    array.
	 */
	function bookit_sanitize_deep( &$value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}
		if ( is_string( $value ) ) {
			$value = bookit_sanitize_string( $value );
			return $value;
		}
		if ( is_int( $value ) ) {
			$value = filter_var( $value, FILTER_VALIDATE_INT );
			return $value;
		}
		if ( is_float( $value ) ) {
			$value = filter_var( $value, FILTER_VALIDATE_FLOAT );
			return $value;
		}
		if ( is_array( $value ) ) {
			array_walk( $value, 'bookit_sanitize_deep' );
			return $value;
		}

		return null;
	}
}

if ( ! function_exists( 'bookit_is_truthy' ) ) {
	/**
	 * Determines if the provided value should be regarded as 'true'.
	 *
	 * @param mixed $var
	 *
	 * @return bool
	 */
	function bookit_is_truthy( $var ) {
		if ( is_bool( $var ) ) {
			return $var;
		}

		/**
		 * Provides an opportunity to modify strings that will be
		 * deemed to evaluate to true.
		 *
		 * @param array $truthy_strings
		 */
		$truthy_strings = (array) apply_filters( 'bookit_is_truthy_strings', [
			'1',
			'enable',
			'enabled',
			'on',
			'y',
			'yes',
			'true',
		] );

		// Makes sure we are dealing with lowercase for testing
		if ( is_string( $var ) ) {
			$var = strtolower( $var );
		}

		// If $var is a string, it is only true if it is contained in the above array
		if ( in_array( $var, $truthy_strings, true ) ) {
			return true;
		}

		// All other strings will be treated as false
		if ( is_string( $var ) ) {
			return false;
		}

		// For other types (ints, floats etc) cast to bool
		return (bool) $var;
	}
}

/**
 * Determine whether Bookit Payment is in sandbox mode.
 *
 * @since 2.5.0
 *
 * @param string $option_name The option name to check.
 *
 * @return bool Whether Bookit Payment is in test mode.
 */
function bookit_is_sandbox_mode( $option_name ) {
	$sandbox_mode = bookit_is_truthy( get_option( $option_name ) );

	/**
	 * Filter whether we should use sandbox mode.
	 *
	 * @since 2.5.0
	 *
	 * @param boolean $sandbox_mode should be available or not.
	 */
	return apply_filters( 'bookit_is_sandbox_mode', $sandbox_mode );
}

/**
 * Sanitizes string values.
 *
 * @since 2.5.0
 *
 * @param string $string The string being sanitized.
 *
 * @return string $string The sanitized version of the string.
 */
function bookit_sanitize_string( $string ) {
	// Replace HTML tags and entities with their plain text equivalents
	$string = htmlspecialchars_decode( $string, ENT_QUOTES );

	// Remove any remaining HTML tags
	$string = strip_tags( $string );

	return $string;
}