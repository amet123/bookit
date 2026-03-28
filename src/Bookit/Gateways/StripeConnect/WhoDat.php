<?php

namespace Bookit\Gateways\StripeConnect;

use Bookit\Gateways\Contracts\Abstract_WhoDat;

/**
 * Class WhoDat. Handles connection to Stripe when the platform keys are needed.
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\StripeConnect
 */
class WhoDat extends Abstract_WhoDat {

	/**
	 * The API Path.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	public $api_endpoint = 'stripe';

	/**
	 * Requests WhoDat to refresh the oAuth tokens.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function refresh_token() {
		$refresh_token = bookit( Gateway::class )->get_current_refresh_token();

		$query_args = [
			'grant_type' => 'refresh_token',
			'refresh_token' => $refresh_token,
		];

		return $this->get( 'token', $query_args );
	}
}
