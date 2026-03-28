<?php

namespace Bookit\Gateways\Contracts;

/**
 * Abstract Settings
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\Contracts
 */
abstract class Abstract_Settings {

	/**
	 * The option key for the gateway-specific sandbox.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	public static $option_sandbox;

	/**
	 * Get the connection settings in the admin.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	abstract function get_connection_settings();

	/**
	 * Check if this gateway is currently in test mode.
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public function is_gateway_test_mode() {
		return bookit_is_truthy( get_option( static::$option_sandbox ) );
	}
}
