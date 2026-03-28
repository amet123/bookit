<?php

namespace Bookit\Gateways\Traits;

/**
 * Trait Has_Mode.
 *
 * @since 2.5.0
 *
 * @package Bookit\Gateways\Traits
 */
trait Has_Mode {

	/**
	 * The current working mode: live or sandbox.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	protected $mode;

	/**
	 * Valid modes.
	 *
	 * @since 2.5.0
	 *
	 * @var array
	 */
	protected $valid_modes = [
		'sandbox', // Default.
		'live',
	];

	/**
	 * Sets the mode for the Merchant for handling operations.
	 *
	 * @since 2.5.0
	 *
	 * @param string $mode
	 *
	 * @return $this
	 */
	public function set_mode( $mode ) {
		if ( ! in_array( $mode, $this->valid_modes, true ) ) {
			$mode = reset( $this->valid_modes );
		}

		$this->mode = $mode;

		return $this;
	}

	/**
	 * Gets the mode for Merchant for handling operations.
	 *
	 * @since 2.5.0
	 *
	 * @return string Which mode we are using the Merchant.
	 */
	public function get_mode() {
		if ( null === $this->mode ) {
			$this->set_mode( bookit_is_sandbox_mode( static::$option_sandbox ) ? 'sandbox' : 'live' );
		}

		return $this->mode;
	}

	/**
	 * Determines if we are using sandbox mode.
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public function is_sandbox() {
		return 'sandbox' === $this->get_mode();
	}
}
