<?php

namespace Bookit\Gateways\Contracts;

/**
 * Signup Interface.
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\Contracts
 */
interface Signup_Interface {
	
	/**
	 * Gets the saved hash for a given user, empty when non-existent.
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	public function get_transient_data();

	/**
	 * Saves the URL in a transient for later use.
	 *
	 * @since 2.5.0
	 *
	 * @param string $value URL for signup.
	 *
	 * @return bool
	 */
	public function update_transient_data( $value );

	/**
	 * Delete url transient from the DB.
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public function delete_transient_data();
}