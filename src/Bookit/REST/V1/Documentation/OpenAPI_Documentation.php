<?php
/**
 * The ESM OpenAPI Documentation Endpoint.
 *
 * @since 2.5.0
 *
 * @package Bookit\REST\V1\Documentation
 */

namespace Bookit\REST\V1\Documentation;

use Bookit\REST\V1\Traits\REST_Namespace as ESM_REST_Namespace;

// phpcs:disable StellarWP.Classes.ValidClassName.NotSnakeCase

/**
 * Class OpenAPI_Documentation
 *
 * @since 2.5.0
 *
 * @package Bookit\REST\V1\Documentation
 */
class OpenAPI_Documentation extends Abstract_OpenAPI_Documentation {
	// phpcs:enable StellarWP.Classes.ValidClassName.NotSnakeCase

	use ESM_REST_Namespace;

	/**
	 * @inerhitDoc
	 */
	protected function get_api_info() {
		return [
			'title'       => __( 'Bookit REST API', 'bookit' ),
			'description' => __( 'Bookit REST API allows direct connections to different views.', 'bookit' ),
			'version'     => $this->rest_api_version,
		];
	}
}
