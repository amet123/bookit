<?php

namespace Bookit\Gateways\Contracts;

use Bookit\Vendor\StellarWP\Arrays\Arr;

/**
 * Abstract Gateway Contract.
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\Contracts
 */
abstract class Abstract_Gateway implements Gateway_Interface {

	/**
	 * The Gateway key.
	 *
	 * @since 2.5.0
	 */
	protected static $key;

	/**
	 * Supported currencies.
	 *
	 * @since 2.5.0
	 *
	 * @var string[]
	 */
	protected static $supported_currencies = [];

	/**
	 * The option name prefix that configured whether a gateway is enabled.
	 * It is followed by the gateway 'key'
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	public static $option_enabled_prefix = '_bookit_gateway_enabled_';

	/**
	 * @inheritDoc
	 */
	public static function get_key() {
		return static::$key;
	}

	/**
	 * @inheritDoc
	 */
	public function register_gateway( array $gateways ) {
		$gateways[ static::get_key() ] = $this;

		return $gateways;
	}

	/**
	 * @inheritDoc
	 */
	public static function should_show() {
		return true;
	}

	/**
	 * Generates a Tracking ID for this website.
	 *
	 * The Tracking ID is a site-specific identifier that links the client and platform accounts in the Payment Gateway
	 * without exposing sensitive data. By default, the identifier generated is a URL in the format:
	 *
	 * {SITE_URL}?v={GATEWAY_VERSION}-{RANDOM_6_CHAR_HASH}
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function generate_unique_tracking_id() {
		$id = wp_generate_password( 6, false, false );;
		$url_frags = wp_parse_url( home_url() );
		$url       = Arr::get( $url_frags, 'host' ) . Arr::get( $url_frags, 'path' );
		$url       = add_query_arg( [
			'v' => static::VERSION . '-' . $id,
		], $url );

		// Always limit it to 127 chars.
		return substr( $url, 0, 127 );
	}

	/**
	 * Get URL for the display logo.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_logo_url(): string {
		return '';
	}

	/**
	 * Get text to use a subtitle when listing gateways.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_subtitle(): string {
		return '';
	}

	/**
	 * Returns the enabled option key.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public static function get_enabled_option_key(): string {
		return static::$option_enabled_prefix . self::get_key();
	}

	/**
	 * Returns if gateway is enabled.
	 *
	 * @since 2.5.0
	 *
	 * @return boolean
	 */
	public static function is_enabled(): bool {
		if ( ! static::should_show() ) {
			return false;
		}

		return bookit_is_truthy( get_option( static::get_enabled_option_key() ) );
	}

	/**
	 * Disable the gateway toggle.
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public static function disable() {
		if ( ! static::is_enabled() ) {
			return true;
		}

		return delete_option( static::get_enabled_option_key() );
	}

	/**
	 * Get supported currencies.
	 *
	 * @since 2.5.0
	 *
	 * @return string[]
	 */
	public static function get_supported_currencies() {
		/**
		 * Filter to modify supported currencies for this gateway.
		 *
		 * @since 2.5.0
		 *
		 * @param string[] $supported_currencies Array of three-letter, supported currency codes.
		 */
		return apply_filters( 'bookit_gateway_supported_currencies_' . static::$key, static::$supported_currencies );
	}

	/**
	 * Is currency supported.
	 *
	 * @since 2.5.0
	 *
	 * @param string $currency_code Currency code.
	 *
	 * @return bool
	 */
	public static function is_currency_supported( $currency_code ) {
		if ( empty( $currency_code ) ) {
			return false;
		}

		$supported_currencies = static::get_supported_currencies();

		// If supported currencies aren't set, assume it's supported.
		if ( empty( $supported_currencies ) ) {
			return true;
		}

		return in_array( $currency_code, $supported_currencies, true );
	}
}
