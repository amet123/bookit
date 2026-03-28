<?php

namespace Bookit\Gateways\StripeConnect;

use Bookit\Contracts\Service_Provider;

/**
 * Class Provider
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\StripeConnect
 */
class Provider extends Service_Provider {

	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->container->singleton( REST::class );

		$this->register_hooks();
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for this Service Provider.
	 *
	 * @since 2.5.0
	 */
	protected function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container
		$this->container->singleton( Hooks::class, $hooks );
	}
}
