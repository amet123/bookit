<?php

namespace Bookit\Gateways\Contracts;

/**
 * Merchant Interface
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\Contracts
 */
interface Merchant_Interface {

	/**
	 * Gets the account key.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_account_key();

	/**
	 * Save merchant data.
	 *
	 * @since 2.5.0
	 *
	 * @return boolean
	 */
	public function save();

	/**
	 * Transforms the Merchant data into an array.
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	public function to_array();

	/**
	 * Creates this object from an array.
	 *
	 * @since 2.5.0
	 *
	 * @param array   $data
	 * @param boolean $needs_save
	 *
	 * @return boolean
	 */
	public function from_array( array $data, $needs_save = true );

}