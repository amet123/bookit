<?php

namespace Bookit\Gateways\Contracts;

/**
 * Gateway Interface
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\Contracts
 */
interface Gateway_Interface {

	/**
	 * Get's the key for this Commerce Gateway.
	 *
	 * @since 2.5.0
	 *
	 * @return string What is the Key used.
	 */
	public static function get_key();

	/**
	 * Get the label for this Commerce Gateway.
	 *
	 * @since 2.5.0
	 *
	 * @return string What label we are using for this gateway.
	 */
	public static function get_label();

	/**
	 * Determine whether the gateway should be shown as an available gateway.
	 *
	 * @since 2.5.0
	 *
	 * @return bool Whether the gateway should be shown as an available gateway.
	 */
	public static function should_show();

	/**
	 * Register the gateway for Bookit Payment.
	 *
	 * @since 2.5.0
	 *
	 * @param array $gateways The list of registered Bookit Payment gateways.
	 *
	 * @return Abstract_Gateway[] The list of registered Bookit Payment gateways.
	 */
	public function register_gateway( array $gateways );

	/**
	 * Get all the admin notices.
	 *
	 * @since 2.5.0.
	 *
	 * @return array
	 */
	public function get_admin_notices();
}