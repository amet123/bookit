<?php

namespace Bookit\Gateways\StripeConnect;

use Bookit\Gateways\Contracts\Abstract_Signup;

/**
 * Class Signup.
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\StripeConnect
 */
class Signup extends Abstract_Signup {

	/**
	 * @inheritDoc
	 */
	public static $signup_data_meta_key = 'bookit_stripe_connnect_signup_data';

	/**
	 * The return path the user will be redirected to after signing up or disconnecting.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	public $signup_return_path = '/bookit/v1/commerce/stripe/return';

	/**
	 * An instance of the Whodat handler.
	 *
	 * @since 2.5.0
	 *
	 * @var WhoDat
	 */
	protected $whodat;

	/**
	 * An instance of the Gateway handler.
	 *
	 * @since 2.5.0
	 *
	 * @var Gateway
	 */
	protected $gateway;

	/**
	 * An instance of the Merchant handler.
	 *
	 * @since 2.5.0
	 *
	 * @var Merchant
	 */
	protected $merchant;

	/**
	 * Signup constructor.
	 *
	 * @since 2.5.0
	 *
	 * @param WhoDat   $whodat   An instance of the Whodat handler.
	 * @param Gateway  $gateway  An instance of the Gateway handler.
	 * @param Merchant $merchant An instance of the Merchant handler.
	 */
	public function __construct( WhoDat $whodat, Gateway $gateway, Merchant $merchant ) {
		$this->whodat   = $whodat;
		$this->gateway  = $gateway;
		$this->merchant = $merchant;
	}

	/**
	 * Generates a stripe connection URL from WhoDat.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function generate_signup_url() {

		return $this->whodat->get_api_url( 'connect', [
				'token'      => $this->get_client_id(),
				'return_url' => $this->whodat->get_api_url( 'connected' ),
			] );

	}

	/**
	 * Generates a stripe disconnection URL from WhoDat
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function generate_disconnect_url() {

		return $this->whodat->get_api_url( 'disconnect', [
				'stripe_user_id' => $this->merchant->get_client_id(),
				'return_url'     => rest_url( $this->signup_return_path ),
			] );
	}

	/**
	 * Get a unique tracking ID to identify this client on stripe.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_client_id() {
		return $this->gateway->generate_unique_tracking_id();
	}

	/**
	 * Determines if the signup was successful.
	 *
	 * @since 2.5.0
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function is_success( $data ) {

		return ! empty( $data->stripe_user_id ) && ! empty( $data->live->access_token ) && ! empty( $data->sandbox->access_token );
	}
}