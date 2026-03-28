<?php
/**
 * The Integration Abstract OpenAPI Documentation Endpoint.
 *
 * @since 2.5.0
 *
 * @package Bookit\REST\V1\Documentation
 */

namespace Bookit\REST\V1\Documentation;

use Bookit\REST\V1\Endpoints\READ_Endpoint_Interface;
use Bookit\REST\V1\Traits\REST_Namespace as ESM_REST_Namespace;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

// phpcs:disable StellarWP.Classes.ValidClassName.NotSnakeCase

/**
 * Class OpenAPI_Documentation
 *
 * @since 2.5.0
 *
 * @package Bookit\REST\V1\Documentation
 */
abstract class Abstract_OpenAPI_Documentation implements READ_Endpoint_Interface, Provider_Interface, Builder_Interface {
	// phpcs:enable StellarWP.Classes.ValidClassName.NotSnakeCase

	use ESM_REST_Namespace;

	/**
	 * Open API Version.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	protected $open_api_version = '3.0.0';

	/**
	 * Integration REST API Version.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	protected $rest_api_version = '1.0.0';

	/**
	 * REST Documentation Definition Providers.
	 *
	 * @since 2.5.0
	 *
	 * @var Provider_Interface[]
	 */
	protected $documentation_providers = [];

	/**
	 * REST Definition Definition Providers.
	 *
	 * @since 2.5.0
	 *
	 * @var Provider_Interface[]
	 */
	protected $definition_providers = [];

	/**
	 * Register the actual endpoint on WP Rest API.
	 *
	 * @since 2.5.0
	 */
	public function register() {
		register_rest_route(
			$this->get_bookit_route_namespace(),
			'/doc',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get' ],
				'permission_callback' => '__return_true',
			]
		);
		$this->register_documentation_provider( '/doc', $this );
	}

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @since 2.5.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$data = $this->get_documentation();

		return new WP_REST_Response( $data );
	}

	/**
	 * Returns an array in the format used by OpenAPI 3.0.
	 *
	 * @since 2.5.0
	 *
	 * While the structure must conform to that used by v3.0 of OpenAPI the structure can be that of a full document
	 * or that of a document part.
	 * The intelligence lies in the "gatherer" of informations rather than in the single "providers" implementing this
	 * interface.
	 *
	 * @link  https://www.openapis.org/
	 *
	 * @return array<string|mixed> An array description of a OpenAPI supported component.
	 */
	public function get_documentation() {
		$url = $this->get_url();

		$documentation = [
			'openapi'    => $this->open_api_version,
			'info'       => $this->get_api_info(),
			'servers'    => [
				[
					'url' => $url,
				],
			],
			'paths'      => $this->get_paths(),
			'components' => [ 'schemas' => $this->get_definitions() ],
		];

		/**
		 * Filters the OpenAPI documentation generated for the TEC REST API.
		 *
		 * @since 2.5.0
		 *
		 * @param array<string|mixed>   $documentation An associative PHP array in the format supported by OpenAPI.
		 * @param OpenAPI_Documentation $this          This documentation endpoint instance.
		 *
		 * @link  https://www.openapis.org/
		 */
		$documentation = apply_filters( 'bookit_rest_openapi_documentation', $documentation, $this );

		return $documentation;
	}

	/**
	 * Get REST API Info
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	abstract protected function get_api_info();

	/**
	 * Get REST API Path
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	protected function get_paths() {
		$paths = [];
		foreach ( $this->documentation_providers as $path => $endpoint ) {
			if ( $endpoint !== $this ) {
				/** @var Provider_Interface $endpoint */
				$documentation = $endpoint->get_documentation();
			} else {
				$documentation = $this->get_own_documentation();
			}
			$paths[ $path ] = $documentation;
		}

		return $paths;
	}

	/**
	 * Registers a documentation provider for a path.
	 *
	 * @since 2.5.0
	 *
	 * @param string             $path     The path to register the provider for.
	 * @param Provider_Interface $endpoint The endpoint provider.
	 */
	public function register_documentation_provider( $path, Provider_Interface $endpoint ) {
		$this->documentation_providers[ $path ] = $endpoint;
	}

	/**
	 * Get REST API Documentation
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	protected function get_own_documentation() {
		return [
			'get' => [
				'responses' => [
					'200' => [
						'description' => __( 'Returns the documentation for Bookit REST API in OpenAPI consumable format.', 'bookit' ),
					],
				],
			],
		];
	}

	/**
	 * Get REST API Definitions
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	protected function get_definitions() {
		$definitions = [];
		/** @var Provider_Interface $provider */
		foreach ( $this->definition_providers as $type => $provider ) {
			$definitions[ $type ] = $provider->get_documentation();
		}

		return $definitions;
	}

	/**
	 * Get REST API Registered Documentation Providers
	 *
	 * @since 2.5.0
	 *
	 * @return Provider_Interface[]
	 */
	public function get_registered_documentation_providers() {
		return $this->documentation_providers;
	}

	/**
	 * Registers a documentation provider for a definition.
	 *
	 * @since 2.5.0
	 *
	 * @param string             $type     The type of the definition.
	 * @param Provider_Interface $provider The provider for the definition.
	 */
	public function register_definition_provider( $type, Provider_Interface $provider ) {
		$this->definition_providers[ $type ] = $provider;
	}

	/**
	 * Get Documentation Provider Interface
	 *
	 * @since 2.5.0
	 *
	 * @return Provider_Interface[]
	 */
	public function get_registered_definition_providers() {
		return $this->definition_providers;
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	public function READ_args() {
		return [];
	}
}
