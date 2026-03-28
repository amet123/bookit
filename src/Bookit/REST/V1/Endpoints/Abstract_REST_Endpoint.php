<?php
/**
 * The ESM Abstract Endpoint.
 *
 * @since 2.5.0
 *
 * @package Bookit\REST\V1\Endpoints
 */

namespace Bookit\REST\V1\Endpoints;

use Bookit\REST\V1\Documentation\OpenAPI_Documentation;
use Bookit\REST\V1\Documentation\Provider_Interface;
use Bookit\REST\V1\Traits\REST_Namespace as Bookit_REST_Namespace;
use Bookit\Vendor\StellarWP\Arrays\Arr;

/**
 * Abstract REST Endpoint.
 *
 * @since 2.5.0
 *
 * @package Bookit\REST\V1\Endpoints
 */
abstract class Abstract_REST_Endpoint implements Provider_Interface {

	use Bookit_REST_Namespace;

	/**
	 * Supported Query Vars.
	 *
	 * @var array<string>
	 */
	protected $supported_query_vars = [];

	/**
	 * An instance of the OpenAPI_Documentation handler.
	 *
	 * @since 2.5.0
	 *
	 * @var \Bookit\REST\V1\Documentation\OpenAPI_Documentation
	 */
	protected $documentation;

	/**
	 * Abstract_REST_Endpoint constructor.
	 *
	 * @since 2.5.0
	 *
	 * @param OpenAPI_Documentation $documentation An instance of the OpenAPI_Documentation handler.
	 */
	public function __construct( OpenAPI_Documentation $documentation ) {
		$this->documentation = $documentation;
	}

	/**
	 * Register the actual endpoint on WP Rest API.
	 *
	 * @since 2.5.0
	 */
	abstract public function register();

	/**
	 * Returns an array in the format used by OpenAPI 3.0.
	 *
	 * While the structure must conform to that used by v3.0 of OpenAPI the structure can be that of a full document
	 * or that of a document part.
	 * The intelligence lies in the "gatherer" of informations rather than in the single "providers" implementing this
	 * interface.
	 *
	 * @link  https://www.openapis.org/
	 *
	 * @since 2.5.0
	 *
	 * @return array<string|mixed> An array description of a OpenAPI supported component.
	 */
	abstract public function get_documentation();

	/**
	 * Provides the content of the `args` array to register the endpoint support for GET requests.
	 *
	 * @since 2.5.0
	 *
	 * @return array<string|mixed> An array of read 'args'.
	 */
	abstract public function READ_args();

	/**
	 * Gets the Endpoint path for this route.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_endpoint_path() {
		return $this->path;
	}

	/**
	 * Converts an array of arguments suitable for the WP REST API to the OpenAPI format.
	 *
	 * @since 2.5.0
	 *
	 * @param array<string|mixed> $args     An array of arguments to openapiize.
	 * @param array<string|mixed> $defaults A default array of arguments.
	 *
	 * @return array<string|mixed> The converted arguments.
	 */
	public function args_to_openapi_schema( array $args = [], array $defaults = [] ) {
		if ( empty( $args ) ) {
			return $args;
		}

		$no_description = _x( 'No description provided', 'Default description for integration endpoint.', 'bookit' );
		$defaults       = array_merge(
			[
				'in'          => 'body',
				'schema'      => [
					'type' => 'string',
				],
				'description' => $no_description,
				'required'    => false,
				'items'       => [
					'type' => 'integer',
				],
			],
			$defaults
		);


		$openapiized = [];
		foreach ( $args as $name => $info ) {
			if ( isset( $info['openapi_type'] ) ) {
				$type = $info['openapi_type'];
			} else {
				$type = $info['type'] ?? false;
			}

			$type = $this->convert_type( $type );

			$read = [
				'name'        => $name,
				'in'          => $info['in'] ?? false,
				'description' => $info['description'] ?? false,
				'schema'      => [
					'type' => $type,
				],
				'required'    => $info['required'] ?? false,
			];

			if ( isset( $info['items'] ) ) {
				$read['schema']['items'] = $info['items'];
			}

			if ( isset( $info['collectionFormat'] ) && $info['collectionFormat'] === 'csv' ) {
				$read['style']   = 'form';
				$read['explode'] = false;
			}

			if ( isset( $info['openapi_type'] ) ) {
				$read['schema']['type'] = $info['openapi_type'];
			}

			// Copy in case we need to mutate default values for this field in args.
			$defaults_copy = $defaults;
			unset( $defaults_copy['default'] );
			unset( $defaults_copy['items'] );
			unset( $defaults_copy['type'] );

			$openapiized[] = array_merge( $defaults_copy, array_filter( $read ) );
		}

		return $openapiized;
	}

	/**
	 * Converts REST format type argument to the corresponding openapis.org definition.
	 *
	 * @since 2.5.0
	 *
	 * @param string $type A type to convert to OpenAPI.
	 *
	 * @return string|array<string> The converted type, maintaining structure if it's an array.
	 */
	protected function convert_type( $type ) {
		$rest_to_openapi_type_map = [
			'int'  => 'integer',
			'bool' => 'boolean',
		];

		// Check if type is scalar and directly map it.
		if ( is_scalar( $type ) ) {
			return Arr::get( $rest_to_openapi_type_map, $type, $type );
		}

		// If type is an array, recursively convert its elements.
		if ( is_array( $type ) ) {
			foreach ( $type as $key => $value ) {
				$type[ $key ] = $this->convert_type( $value );
			}

			return $type;
		}

		// Return the type unmodified if it's neither scalar nor array.
		return $type;
	}

	/**
	 * Parses the arguments populated parsing the request filling out with the defaults.
	 *
	 * @since 2.5.0
	 *
	 * @param array<string|mixed> $args     The arguments to parse.
	 * @param array<string|mixed> $defaults The default arguments.
	 *
	 * @return array<string|mixed> The parsed arguments.
	 */
	protected function parse_args( array $args, array $defaults ) {
		foreach ( $this->supported_query_vars as $request_key => $query_var ) {
			if ( isset( $defaults[ $request_key ] ) ) {
				$defaults[ $query_var ] = $defaults[ $request_key ];
			}
		}

		$args = wp_parse_args( array_filter( $args, [ $this, 'is_not_null' ] ), $defaults );

		return $args;
	}

	/**
	 * Whether a value is null or not.
	 *
	 * @since 2.5.0
	 *
	 * @param mixed $value The value to check.
	 *
	 * @return bool Whether the value is not null.
	 */
	public function is_not_null( $value ) {
		return null !== $value;
	}

	/**
	 * Sanitize a request argument based on details registered to the route.
	 *
	 * @since 2.5.0
	 *
	 * @param mixed $value Value of the 'filter' argument.
	 *
	 * @return string|array<string|string> A text field sanitized string or array.
	 */
	public function sanitize_callback( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'sanitize_text_field', $value );
		}

		return sanitize_text_field( $value );
	}

	/**
	 * @inheritDoc
	 */
	public function get_route_url() {
		$namespace = $this->get_bookit_route_namespace();

		return rest_url( '/' . $namespace . $this->get_endpoint_path(), 'https' );
	}

	/**
	 * Gets the Return URL pointing to this on boarding route.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_return_url( $hash = null ) {
		$arguments = [
			'hash' => $hash,
		];

		return add_query_arg( $arguments, $this->get_route_url() );
	}
}
