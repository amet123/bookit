<?php

namespace Bookit\Gateways\Contracts;

/**
 * Abstract Signup Contract
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\Contracts
 */
abstract class Abstract_Signup implements Signup_Interface {

	/**
	 * Holds the transient key used to store hash passed to PayPal.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	public static $signup_hash_meta_key;

	/**
	 * Holds the transient key used to link PayPal to this site.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	public static $signup_data_meta_key;

	/**
	 * @inheritDoc
	 */
	public function get_transient_data() {
		return get_transient( static::$signup_data_meta_key );
	}

	/**
	 * @inheritDoc
	 */
	public function update_transient_data( $value ) {
		return set_transient( static::$signup_data_meta_key, $value, DAY_IN_SECONDS );
	}

	/**
	 * @inheritDoc
	 */
	public function delete_transient_data() {
		return delete_transient( static::$signup_data_meta_key );
	}
}