<?php
/**
 * The REST Endpoint Interface.
 *
 * @since 2.5.0
 *
 * @package Bookit\REST\V1\Interfaces
 */

namespace Bookit\REST\V1\Interfaces;

/**
 * REST_Endpoint_Interface
 *
 * @since 2.5.0
 *
 * @package Bookit\REST\V1\Interfaces
 */
interface REST_Endpoint_Interface {

	/**
	 * Gets the Endpoint path for this route.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_endpoint_path();

	/**
	 * Get the endpoint id.
	 *
	 * @since 2.5.0
	 *
	 * @return string The endpoint details id with prefix and endpoint combined.
	 */
	public function get_id();
}
