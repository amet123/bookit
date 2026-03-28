<?php

namespace Bookit\Gateways\StripeConnect;

Use Bookit\Contracts\Service_Provider;

/**
 * Class REST
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\StripeConnect
 */
class REST extends Service_Provider {

	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->singleton( REST\Return_Endpoint::class );
	}

	/**
	 * Register the endpoints for handling webhooks.
	 *
	 * @since 2.5.0
	 */
	public function register_endpoints() {
		$this->container->make( REST\Return_Endpoint::class )->register();
	}
}
