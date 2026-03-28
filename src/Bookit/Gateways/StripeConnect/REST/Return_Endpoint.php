<?php

namespace Bookit\Gateways\StripeConnect\REST;

use Bookit\Classes\Admin\SettingsController;
use Bookit\REST\V1\Endpoints\Abstract_REST_Endpoint;
use Bookit\REST\V1\Documentation\OpenAPI_Documentation;


use Bookit\Gateways\StripeConnect\Gateway;
use Bookit\Gateways\StripeConnect\Merchant;
use Bookit\Gateways\StripeConnect\Settings;
use Bookit\Gateways\StripeConnect\Signup;
use Bookit\Admin\Settings as Plugin_Settings;

use WP_REST_Server;
use WP_REST_Request;

/**
 * Class Return Endpoint.
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\StripeConnect\REST
 */
class Return_Endpoint extends Abstract_REST_Endpoint {

	/**
	 * The REST API endpoint path.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	protected $path = '/commerce/stripe/return';

	/**
	 * An instance of the Plugin_Settings handler.
	 *
	 * @since 2.5.0
	 *
	 * @var Plugin_Settings
	 */
	protected $plugin_settings;

	/**
	 * Return Endpoint constructor.
	 *
	 * @since 2.5.0
	 *
	 * @param OpenAPI_Documentation $documentation   An instance of the ESM OpenAPI_Documentation handler.
	 * @param Plugin_Settings       $plugin_settings An instance of the Plugin_Settings handler.
	 */
	public function __construct( OpenAPI_Documentation $documentation, Plugin_Settings $plugin_settings ) {
		$this->plugin_settings = $plugin_settings;
		parent::__construct( $documentation );
	}

	/**
	 * Register the actual endpoint on WP Rest API.
	 *
	 * @since 2.5.0
	 */
	public function register() {
		register_rest_route( $this->get_bookit_route_namespace(), $this->get_endpoint_path(), [
			'methods'             => WP_REST_Server::READABLE,
			'args'                => $this->create_order_args(),
			'callback'            => [ $this, 'handle_stripe_return' ],
			'permission_callback' => [ $this, 'check_permission' ],
		] );

		$this->documentation->register_documentation_provider( $this->get_endpoint_path(), $this );
	}

	/**
	 * Check if the current user has permission to connect Stripe accounts.
	 *
	 * @since 2.5.1
	 *
	 * @return bool True if user has permission, false otherwise.
	 */
	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Arguments used for the endpoint.
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	public function create_order_args() {
		return [];
	}

	/**
	 * Handles the request that creates an order with Bookit Payment and the Stripe gateway.
	 *
	 * @since 2.5.0
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	public function handle_stripe_return( WP_REST_Request $request ) {
		$stripe_obj   = bookit_get_request_var( 'stripe' );
		$disconnected = bookit_get_request_var( 'stripe_disconnected' );

		if ( ! empty( $stripe_obj ) ) {
			$response = $this->decode_payload( $stripe_obj );

			if ( ! empty( $response->{'tc-stripe-error'} ) ) {
				$this->handle_connection_error( $response );
			}

			if ( ! empty( $response->stripe_disconnected ) && $response->stripe_disconnected ) {
				$this->handle_connection_terminated();
			}

			$this->handle_connection_established( $response );
		}

		if ( ! empty( $disconnected ) ) {
			$this->handle_connection_terminated();
		}
	}

	/**
	 * Decode the payload received from WhoDat.
	 *
	 * @since 2.5.0
	 *
	 * @param string $payload json payload.
	 *
	 * @return object
	 */
	public function decode_payload( $payload ) {

		if ( empty( $payload ) ) {
			return;
		}

		return json_decode( base64_decode( $payload ) );
	}

	/**
	 * Handle successful account connections.
	 *
	 * @since 2.5.0
	 *
	 * @param object $payload data returned from WhoDat.
	 */
	public function handle_connection_established( $payload ) {

		bookit( Merchant::class )->save_signup_data( (array) $payload );
		bookit( Settings::class )->setup_account_defaults();

		$validate = bookit( Merchant::class )->validate_account_is_permitted();

		if ( 'valid' !== $validate ) {
			bookit( Merchant::class )->set_merchant_unauthorized( $validate );
			$disconnect_url = bookit( Signup::class )->generate_disconnect_url();

			bookit( Merchant::class )->delete_signup_data();

			// Allow WhoDat domain for redirect
			add_filter( 'allowed_redirect_hosts', function( $hosts ) {
				$hosts[] = 'whodat.theeventscalendar.com';
				return $hosts;
			} );

			wp_safe_redirect( $disconnect_url );
			exit();
		}

		// Enable the Stripe Connect payment gateway in the settings.
		$settings = SettingsController::get_settings();
		if ( ! empty( $settings ) ) {
			$settings['payments']['stripeConnect']['enabled'] = true;
			SettingsController::save_settings( $settings );
		}

		bookit( Merchant::class )->unset_merchant_unauthorized();
		$url = $this->plugin_settings->get_url( [], 'payments' );

		wp_safe_redirect( $url );
		exit();
	}

	/**
	 * Handle unsuccessful account connections.
	 *
	 * @since 2.5.0
	 *
	 * @param object $payload data returned from WhoDat.
	 */
	public function handle_connection_error( $payload ) {
		$url = $this->plugin_settings->get_url( [
			'tc-stripe-error' => $payload->{'tc-stripe-error'},
		], 'payments' );

		wp_safe_redirect( $url );
		exit();
	}

	/**
	 * Handle account disconnections.
	 *
	 * @since 2.5.0
	 */
	public function handle_connection_terminated( $reason = [] ) {
		bookit( Merchant::class )->delete_signup_data();
		Gateway::disable();

		$query_args = [
			'stripe_disconnected' => 1,
		];

		$url_args = array_merge( $query_args, $reason );

		$url = $this->plugin_settings->get_url( $url_args, 'payment' );

		wp_safe_redirect( $url );
		exit();
	}

	// phpcs:disable StellarWP.Classes.ValidClassName.NotSnakeCase
	// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	/**
	 * @inheritDoc
	 */
	public function get_documentation() {
		$POST_defaults = [
			'in'      => 'formData',
			'default' => '',
			'type'    => 'string',
		];
		$post_args     = array_merge( $this->READ_args() );

		return [
			'post' => [
				'consumes'   => [ 'application/x-www-form-urlencoded' ],
				'parameters' => $this->args_to_openapi_schema( $post_args, $POST_defaults ),
				'responses'  => [
					'201' => [
						'description' => esc_html_x( 'Returns successful checking of the new attendee queue.', 'Description for the Sponsors REST endpoint on a successful return.', 'bookit' ),
						'schema'      => [
							'$ref' => '#/definitions/ESM',
						],
					],
					'400' => [
						'description' => esc_html_x( 'A required parameter is missing or an input parameter is in the wrong format', 'Description for the Sponsors REST endpoint missing a required parameter.', 'bookit' ),
					],
				],
			],
		];
	}
	// phpcs:enable StellarWP.Classes.ValidClassName.NotSnakeCase
	// phpcs:enable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase

	/**
	 * @inheritDoc
	 */
	public function READ_args() {
		return [
			'posts_per_page'     => [
				'required'          => false,
				'sanitize_callback' => [ $this, 'sanitize_callback' ],
				'default'           => 100,
				'description'       => esc_html_x( 'The number of sponsors to return.', 'Description for the posts_per_page argument in the Sponsors REST endpoint.', 'bookit' ),
				'type'              => 'integer',
			],
			'excerpt_length'     => [
				'required'          => false,
				'sanitize_callback' => [ $this, 'sanitize_callback' ],
				'description'       => esc_html_x( 'The length of the sponsor excerpt.', 'Description for the excerpt_length argument in the Sponsors REST endpoint.', 'bookit' ),
				'type'              => 'integer',
			],
			'include_unassigned' => [
				'required'          => false,
				'sanitize_callback' => [ $this, 'sanitize_callback' ],
				'description'       => esc_html_x( 'Whether to include unassigned sponsors.', 'Description for the include_unassigned argument in the Sponsors REST endpoint.', 'bookit' ),
				'type'              => 'boolean',
			],
		];
	}
}
