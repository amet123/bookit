<?php

namespace Bookit\Gateways\Contracts;

use Bookit\Gateways\Traits\Has_Mode;

/**
 * Abstract Merchant Contract.
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\Contracts
 */
abstract class Abstract_Merchant implements Merchant_Interface {

	use Has_Mode;

	/**
	 * @inheritDoc
	 */
	public static $option_sandbox = 'bookit-stripe-sandbox';

	/**
	 * Make Merchant object from array.
	 *
	 * @since 2.5.0
	 *
	 * @param array   $data       Which values need to .
	 * @param boolean $needs_save Determines if the proprieties saved need to save to the DB.
	 *
	 * @return boolean
	 */
	public function from_array( array $data, $needs_save = true ) {
		if ( ! $this->validate( $data ) ) {
			return false;
		}

		$this->setup_properties( $data, $needs_save );

		return true;
	}

	/**
	 * Gets the value stored for the Client ID.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_client_id() {
		return $this->client_id;
	}

	/**
	 * Save merchant details.
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public function save() {
		if ( false === $this->needs_save() ) {
			return false;
		}

		$saved = update_option( $this->get_account_key(), $this->to_array() );

		// If we were able to save, we reset the needs save.
		if ( $saved ) {
			$this->needs_save = false;
		}

		return $saved;
	}
}