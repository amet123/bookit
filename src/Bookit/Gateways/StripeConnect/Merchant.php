<?php

namespace Bookit\Gateways\StripeConnect;

use Bookit\Gateways\Contracts\Abstract_Merchant;
use Bookit\Vendor\StellarWP\Arrays\Arr;
use Bookit\Classes\Vendor\Payments;
use Bookit\Classes\Database\Payments as PaymentDb;
use Bookit\Classes\Admin\SettingsController;

/**
 * Class Merchant
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\StripeConnect
 */
class Merchant extends Abstract_Merchant {

	/**
	 * List of countries that are unauthorized to work with the Bookit Provider for regulatory reasons.
	 *
	 * @var array
	 */
	const UNAUTHORIZED_COUNTRIES = [
		'BR',
		'IN',
		'MX',
	];

	/**
	 * Option key to save the information regarding merchant status.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	public static $merchant_unauthorized_option_key = 'bookit-merchant-stripe-merchant-unauthorized';

	/**
	 * Option key to save the information regarding merchant authorization.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	public static $merchant_deauthorized_option_key = 'bookit-merchant-stripe-merchant-deauthorized';

	/**
	 * Option key to save the information regarding merchant default currency.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	public static $merchant_default_currency_option_key = 'bookit-merchant-stripe-merchant-currency';

	/**
	 * Stripe API URL for payment intents.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	private $url = 'https://api.stripe.com/v1/payment_intents/';

	/**
	 * Determines if Merchant is active. For Stripe this is the same as being connected.
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public function is_active( $recheck = false ) {
		return $this->is_connected( $recheck );
	}

	/**
	 * Determines if the Merchant is connected.
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public function is_connected( $recheck = false ) {
		$client_data = $this->to_array();

		if ( empty( $client_data['client_id'] )
			 || empty( $client_data['client_secret'] )
			 || empty( $client_data['publishable_key'] )
		) {
			return false;
		}

		if ( $recheck ) {
			$status = $this->check_account_status( $client_data );

			if ( false === $status['connected'] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns the options key for the account in the merchant mode.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_account_key() {
		$gateway_key = Gateway::get_key();

		return "bookit_{$gateway_key}_account";
	}

	/**
	 * Returns the data retrieved from the signup process.
	 *
	 * Uses normal WP options to be saved, instead of the normal update_option.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_signup_data_key() {
		$gateway_key = Gateway::get_key();

		return "bookit_{$gateway_key}_signup_data";
	}

	/**
	 * Returns the stripe client secret stored for server-side transactions.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_client_secret() {
		$keys = get_option( $this->get_signup_data_key() );

		if ( empty( $keys[ $this->get_mode() ]->access_token ) ) {
			return '';
		}

		return $keys[ $this->get_mode() ]->access_token;
	}

	/**
	 * Fetch the Publishable key for the user.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_publishable_key() {
		$keys = get_option( $this->get_signup_data_key() );

		if ( empty( $keys[ $this->get_mode() ]->publishable_key ) ) {
			return '';
		}

		return $keys[ $this->get_mode() ]->publishable_key;
	}

	/**
	 * Returns the stripe client id stored for server-side transactions.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_client_id() {
		$keys = get_option( $this->get_signup_data_key() );

		if ( empty( $keys['stripe_user_id'] ) ) {
			return '';
		}

		return $keys['stripe_user_id'];
	}

	/**
	 * Return array of merchant details.
	 *
	 * @since 2.5.0
	 *
	 * @return array
	 */
	public function to_array() {
		return [
			'client_id'       => $this->get_client_id(),
			'client_secret'   => $this->get_client_secret(),
			'publishable_key' => $this->get_client_id(),
		];
	}

	/**
	 * Saves signup data from the redirect into permanent option.
	 *
	 * @since 2.5.0
	 *
	 * @param array $signup_data
	 *
	 * @return bool
	 */
	public function save_signup_data( array $signup_data ) {
		unset( $signup_data['whodat'] );
		unset( $signup_data['state'] );

		return update_option( $this->get_signup_data_key(), $signup_data );
	}

	/**
	 * Query the Stripe API to gather information about the current connected account.
	 *
	 * @since 2.5.0
	 *
	 * @param array $client_data Connection data from the database.
	 *
	 * @return array
	 */
	public function check_account_status( $client_data = [] ) {

		if ( empty( $client_data ) ) {
			$client_data = $this->to_array();
		}

		$return = [
			'connected'       => false,
			'charges_enabled' => false,
			'errors'          => [],
			'capabilities'    => [],
		];

		if ( empty( $client_data['client_id'] )
			 || empty( $client_data['client_secret'] )
			 || empty( $client_data['publishable_key'] )
		) {
			return $return;
		}

		$url = sprintf( '/accounts/%s', urlencode( $client_data['client_id'] ) );

		$response = Requests::get( $url, [], [] );

		if ( ! empty( $response['object'] ) && 'account' === $response['object'] ) {
			$return['connected']            = true;
			$return['charges_enabled']      = bookit_is_truthy( Arr::get( $response, 'charges_enabled', false ) );
			$return['country']              = Arr::get( $response, 'country', false );
			$return['default_currency']     = Arr::get( $response, 'default_currency', false );
			$return['capabilities']         = Arr::get( $response, 'capabilities', false );
			$return['statement_descriptor'] = Arr::get( $response, 'statement_descriptor', false );

			if ( empty( $return['statement_descriptor'] ) && ! empty( $response['settings']['payments']['statement_descriptor'] ) ) {
				$return['statement_descriptor'] = $response['settings']['payments']['statement_descriptor'];
			}

			if ( ! empty( $response['requirements']['errors'] ) ) {
				$return['errors']['requirements'] = $response['requirements']['errors'];
			}

			if ( ! empty( $response['future_requirements']['errors'] ) ) {
				$return['errors']['future_requirements'] = $response['future_requirements']['errors'];
			}
		}

		if ( ! empty( $response['type'] ) && in_array( $response['type'], [
				'api_error',
				'card_error',
				'idempotency_error',
				'invalid_request_error',
			], true ) ) {

			$return['request_error'] = $response;
		}

		return $return;
	}

	/**
	 * Empty the signup data option and void the connection.
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public function delete_signup_data() {
		return update_option( $this->get_signup_data_key(), [] );
	}

	/**
	 * Validate if this Merchant is allowed to connect to the Bookit Provider.
	 *
	 * @since 2.5.0
	 *
	 * @return string 'valid' if the account is permitted, or a string with the notice slug if not.
	 */
	public function validate_account_is_permitted() {
		$status = bookit( Settings::class )->connection_status;
		if ( empty( $status ) ) {
			bookit( Settings::class )->set_connection_status();
			$status = bookit( Settings::class )->connection_status;
		}

		//@TODO, update this as it checked for ET+ using PUE.
		$is_licensed = true;

		if ( $is_licensed ) {
			return 'valid';
		}

		if ( $this->country_is_unauthorized( $status ) ) {
			return 'bookit-stripe-country-denied';
		}

		return 'valid';
	}

	/**
	 * Determine if a stripe account is listed in an unauthorized country.
	 *
	 * @since 2.5.0
	 *
	 * @param array $status The connection status array.
	 *
	 * @return bool
	 */
	public function country_is_unauthorized( $status ) {
		return in_array( $status['country'], static::UNAUTHORIZED_COUNTRIES, true );
	}

	/**
	 * Check if merchant is set as unauthorized.
	 *
	 * Unauthorized accounts are accounts that cannot be authorized to connect, usually due to regulatory reasons.
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public function is_merchant_unauthorized() {
		return get_option( static::$merchant_unauthorized_option_key, false );
	}

	/**
	 * Set merchant as unauthorized.
	 *
	 * @since 2.5.0
	 *
	 * @param string $validation_key Refusal reason, must be the same as the notice slug for the corresponding error.
	 */
	public function set_merchant_unauthorized( $validation_key ) {
		update_option( static::$merchant_unauthorized_option_key, $validation_key );
	}

	/**
	 * Unset merchant as unauthorized.
	 *
	 * @since 2.5.0
	 */
	public function unset_merchant_unauthorized() {
		delete_option( static::$merchant_unauthorized_option_key );
	}

	/**
	 * Check if merchant is set as de-authorized.
	 *
	 * De-authorized accounts are accounts that were previously connected and whose connection has been revoked in the
	 * Stripe Dashboard. These accounts can be re-connected with the proper credentials.
	 *
	 * @since 2.5.0
	 *
	 * @return bool
	 */
	public function is_merchant_deauthorized() {
		return get_option( static::$merchant_deauthorized_option_key, false );
	}

	/**
	 * Set merchant as de-authorized.
	 *
	 * @since 2.5.0
	 *
	 * @param string $validation_key De-authorization reason, must be the same as the notice slug for the corresponding error.
	 */
	public function set_merchant_deauthorized( $validation_key ) {
		update_option( static::$merchant_deauthorized_option_key, $validation_key );
	}

	/**
	 * Unset merchant as de-authorized.
	 *
	 * @since 2.5.0
	 */
	public function unset_merchant_deauthorized() {
		delete_option( static::$merchant_deauthorized_option_key );
	}

	/**
	 * Get the merchant default currency.
	 *
	 * @since 2.5.0
	 *
	 * @return string
	 */
	public function get_merchant_currency() {
		return get_option( static::$merchant_default_currency_option_key );
	}

	/**
	 * Updates an existing merchant account.
	 *
	 * @since 2.5.0
	 *
	 * @param array $data Array of data to be passed directly to the body of the update request.
	 *
	 * @return array|\WP_Error|null
	 */
	public function update( $data ) {
		$query_args = [];
		$args       = [
			'body' => $data,
		];

		$url = sprintf( '/accounts/%s', urlencode( $this->get_client_id() ) );

		return Requests::post( $url, $query_args, $args );
	}

	/**
	 * Check Stripe Payment
	 *
	 * @since 2.5.0
	 *
	 * @param string $token   Stripe payment token.
	 * @param float  $total   Total amount to be charged.
	 * @param int    $invoice Invoice ID.
	 */
	public function check_payment( $token, $total, $invoice ) {
		if ( empty( $token ) || empty( $total ) || empty( $invoice ) ) {
			return;
		}

		$request = wp_remote_get( esc_url( $this->url . $token ), [ 'headers' => $this->get_request_header() ] );
		$request = wp_remote_retrieve_body( $request );
		$request = json_decode( $request, true );

		// Check if paid
		$currency = get_option_by_path( 'bookit_settings.currency' ) ?: SettingsController::$default_currency;
		$amount   = $this->get_amount( $total, $currency );

		if ( empty( $request['id'] ) ) {
			return;
		}

		$data = [
			'transaction' => $request['id'],
			'notes'       => serialize( $request ),
			'updated_at'  => wp_date( 'Y-m-d H:i:s' ),
		];

		if ( ! empty( $request['status'] ) && ! empty( $request['amount'] ) && 'succeeded' === $request['status'] && $request['amount'] == $amount ) {
			$data['status']  = PaymentDb::$completeStatus;
			$data['paid_at'] = wp_date( 'Y-m-d H:i:s' );

			PaymentDb::update( $data, [ 'appointment_id' => $invoice ] );

			do_action( 'bookit_payment_complete', $invoice );
		} else {
			$data['status'] = PaymentDb::$rejectedStatus;
			PaymentDb::update( $data, [ 'appointment_id' => $invoice ] );
		}
	}

	/**
	 * Get Payment Request Header
	 *
	 * @since 2.5.0
	 *
	 * @return array Request headers.
	 */
	public function get_request_header() {
/*		$settings = get_option( 'bookit_settings' );
		$payments = $settings['payments'];

		if ( empty( $payments['stripe'] ) || empty( $payments['stripe']['enabled'] ) || empty( $payments['stripe']['secret_key'] ) ) {
			die;
		}*/

		return [ 'Authorization' => 'Bearer ' . $this->get_client_secret() ];
	}

	/**
	 * Create Payment Method Ajax Action
	 *
	 * @since 2.5.0
	 *
	 * @return array Ajax response.
	 */
	public function intent_payment() {
		check_ajax_referer( 'bookit_book_appointment', 'nonce' );

		if ( empty( $_POST['total'] ) || ( empty( $_POST['payment_method_id'] ) && empty( $_POST['payment_intent_id'] ) ) ) {
			return wp_send_json_error( [ 'message' => __( 'Error occurred during Payment request!', 'bookit'  ) ] );
		}

		if ( ! empty( $_POST['payment_intent_id'] ) ) {
			// Confirm the PaymentIntent to finalize payment after handling a required action
			$retrieve = wp_remote_get( esc_url( $this->url . $_POST['payment_intent_id'] ), [ 'headers' => $this->get_request_header() ] );
			$retrieve = wp_remote_retrieve_body( $retrieve );
			$retrieve = json_decode( $retrieve, true );
			$request  = wp_remote_post( esc_url( $this->url . $retrieve['id'] . '/confirm' ), [ 'headers' => $this->get_request_header() ] );
		} elseif ( ! empty( $_POST['payment_method_id'] ) ) {
			// Create new PaymentIntent with a PaymentMethod ID from the client.
			$currency = get_option_by_path( 'bookit_settings.currency' ) ?: SettingsController::$default_currency;
			$amount   = $this->get_amount( $_POST['total'], $currency );

			$args    = [
				'amount'              => $amount,
				'currency'            => $currency,
				'payment_method'      => $_POST['payment_method_id'],
				'confirmation_method' => 'manual',
				'confirm'             => 'true',
			];
			$request = wp_remote_post( rtrim( $this->url, '/' ), [
				'headers' => $this->get_request_header(),
				'body'    => $args,
			] );
		}

		$request = wp_remote_retrieve_body( $request );
		$request = json_decode( $request, true );

		return $this->make_response( $request );
	}

	/**
	 * Generate and Return Response
	 *
	 * @since 2.5.0
	 *
	 * @param array $request The response from Stripe API.
	 *
	 * @return array Ajax response.
	 */
	public function make_response( $request ) {
		if ( ! empty( $request['error'] ) ) {
			return wp_send_json_error( [ 'message' => $request['error']['message'] ] );
		}

		switch ( $request['status'] ) {
			case 'requires_action':
			case 'requires_source_action':
				// Card requires authentication
				return wp_send_json_success( [
					'requires_action'   => true,
					'payment_intent_id' => $request['id'],
					'client_secret'     => $request['client_secret'],
				] );
			case 'requires_payment_method':
			case 'requires_source':
				// Card was not properly authenticated, suggest a new payment method
				return wp_send_json_error( [ 'message' => __( 'Your card was denied, please provide a new payment method!', 'bookit'  ) ] );
			case 'succeeded':
				// Payment is complete, authentication not required
				return wp_send_json_success( [ 'client_secret' => $request['client_secret'] ] );
		}
	}

	/**
	 * Get Amount for Payment
	 *
	 * @since 2.5.0
	 *
	 * @param float  $total    Total amount.
	 * @param string $currency Currency code.
	 *
	 * @return int Amount in the smallest currency unit.
	 */
	private function get_amount( $total, string $currency ) {
		$currency_key = array_search( strtoupper( $currency ), array_column( Payments::get_currency_list(), 'value' ) );

		$amount = intval( $total * 100 );
		if ( Payments::get_currency_list()[ $currency_key ]['is_zero_decimal'] ) {
			$amount = $amount / 100;
		}

		return (int) $amount;
	}
}