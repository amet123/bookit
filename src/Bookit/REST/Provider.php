<?php
/**
 * Provider for REST Functionality.
 *
 * @since 2.5.0
 *
 * @package Bookit\REST
 */

namespace Bookit\REST;

use Bookit\Contracts\Service_Provider;
use Bookit\REST\V1\Documentation\OpenAPI_Documentation;

/**
 * Class Provider
 *
 * Provides the functionality for REST API.
 *
 * @since 2.5.0
 *
 * @package Bookit\REST
 */
class Provider extends Service_Provider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 2.5.0
	 */
	public function register() {
		// Register the SP on the container.
		$this->container->singleton( 'bookit.rests.provider', $this );
		$this->container->singleton( OpenAPI_Documentation::class, OpenAPI_Documentation::class );

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Adds required actions for REST API.
	 *
	 * @since 2.5.0
	 */
	protected function add_actions() {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Registers the REST API endpoints for ESM.
	 *
	 * @since 2.5.0
	 */
	public function register_endpoints() {
		$this->container->make( OpenAPI_Documentation::class )->register();
	}

	/**
	 * Adds required filters for REST API.
	 *
	 * @since 2.5.0
	 */
	protected function add_filters() {}
}
