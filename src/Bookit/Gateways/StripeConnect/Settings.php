<?php

namespace Bookit\Gateways\StripeConnect;

use Bookit\Classes\Vendor\Payments;
use Bookit\Gateways\Contracts\Abstract_Settings;

/**
 * The Stripe specific settings.
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\StripeConnect
 */
class Settings extends Abstract_Settings {

	/**
	 * DB identifier for the Payment Element selection
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	const PAYMENT_ELEMENT_SLUG = 'payment';

	/**
	 * DB identifier for the Card Element selection
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	const CARD_ELEMENT_SLUG = 'card';

	/**
	 * DB identifier for the Card Element Compact Layout
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	const COMPACT_CARD_ELEMENT_SLUG = 'compact';

	/**
	 * DB identifier for the default methods set for the Payment Element
	 *
	 * @since 2.5.0
	 *
	 * @var array
	 */
	const DEFAULT_PAYMENT_ELEMENT_METHODS = [ 'card' ];

	/**
	 * Connection details fetched from the Stripe API on page-load
	 *
	 * @since 2.5.0
	 *
	 * @var array
	 */
	public $connection_status;

	/**
	 * @inheritDoc
	 */
	public static $option_sandbox = 'bookit-stripe-sandbox';

	/**
	 * Option name for the stripe checkout element field
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	public static $option_checkout_element = 'bookit-stripe-checkout-element';

	/**
	 * Option name for the card element credit card fields to use
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	public static $option_checkout_element_card_fields = 'bookit-stripe-checkout-element-card-fields';

	/**
	 * Option name for the payment element payment methods allowed
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	public static $option_checkout_element_payment_methods = 'bookit-stripe-checkout-element-payment-methods';

	/**
	 * Instance of the Signup handler.
	 *
	 * @since 2.5.0
	 *
	 * @var Signup
	 */
	public $signup;

	/**
	 * Instance of the Merchant handler.
	 *
	 * @since 2.5.0
	 *
	 * @var Signup
	 */
	public $merchant;

	/**
	 * Constructor
	 */
	public function __construct( Signup $signup, Merchant $merchant ) {
		$this->signup   = $signup;
		$this->merchant = $merchant;
		$this->set_connection_status();
	}

	/**
	 * Set the internal parameter w/ account details received from the Stripe API
	 *
	 * @since 2.5.0
	 */
	public function set_connection_status() {
		$this->connection_status = $this->merchant->check_account_status();
	}

	/**
	 * Setup basic defaults once a new account is onboarded.
	 *
	 * @since 2.5.0
	 */
	public function setup_account_defaults() {
		if ( empty( $this->connection_status ) ) {
			$this->set_connection_status();
		}

		update_option( Merchant::$merchant_default_currency_option_key, $this->connection_status['default_currency'] );

		if ( empty( get_option( static::$option_checkout_element ) ) ) {
			update_option( static::$option_checkout_element, static::PAYMENT_ELEMENT_SLUG );
		}

		if ( empty( get_option( static::$option_checkout_element_card_fields ) ) ) {
			update_option( static::$option_checkout_element_card_fields, static::COMPACT_CARD_ELEMENT_SLUG );
		}

		if ( empty( get_option( static::$option_checkout_element_payment_methods ) ) ) {
			update_option( static::$option_checkout_element_payment_methods, static::DEFAULT_PAYMENT_ELEMENT_METHODS );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_connection_settings() {
		return [
			'merchant_status' => $this->connection_status,
			'signup'          => bookit( Signup::class ),
			'merchant'        => bookit( Merchant::class ),
			'fee_is_applied'  => apply_filters( 'bookit_stripe_fee_is_applied_notice', true ),
		];
	}

	/**
	 * Filter the BookIt settings.
	 *
	 * @since 2.5.0
	 *
	 * @param array<string|mixed> $settings The array of settings.
	 */
	public function add_settings( $settings ) {
		$connection                                           = $this->get_connection_settings();
		$settings['payments']['stripeConnect']['publish_key'] = $connection['merchant']->get_publishable_key();

		return $settings;
	}

	/**
	 * Update template variables with Stripe Connect data.
	 *
	 * @since 2.5.0
	 *
	 * @param array $template_vars The template variables array.
	 *
	 * @return array The updated template variables array.
	 */
	public function update_template_vars( $template_vars ) {
		$connection       = $this->get_connection_settings();
		$site_currency    = get_option_by_path( 'bookit_settings.currency' );
		$currency_aliases = array_column( Payments::get_currency_list(), 'alias', 'value' );
		$current_alias    = $currency_aliases[ strtoupper( $site_currency ) ] ?? null;

		$template_vars['gateways']['stripeConnect'] = [
			'id'                   => $this->merchant->get_client_id(),
			'authorize_link'       => $this->get_authorize_link(),
			'disconnect_link'      => $this->get_disconnect_link(),
			'configuring_link'     => 'https://evnt.is/1axw',
			'troubleshooting_link' => 'https://evnt.is/1axw',
			'currency'             => $current_alias,
			'currency_code'        => strtoupper( $site_currency ),
			'stripe_currency_code' => strtoupper( $connection['merchant_status']['default_currency'] ?? '' ),
			'payment_methods'      => $connection['merchant_status']['capabilities'] ?? [],
		];

		return $template_vars;
	}

	/**
	 * Get the Stripe Connect authorization link.
	 *
	 * @since 2.5.0
	 *
	 * @return string The Stripe Connect authorization link.
	 */
	protected function get_authorize_link() {
		return $this->signup->generate_signup_url();
	}

	/**
	 * Get the Stripe Connect authorization link.
	 *
	 * @since 2.5.0
	 *
	 * @return string The Stripe Connect authorization link.
	 */
	protected function get_disconnect_link() {
		return $this->signup->generate_disconnect_url();
	}

	/**
	 * Updates the StripeConnect test mode option.
	 *
	 * @since 2.5.0
	 *
	 * @param array $data The options data containing StripeConnect settings.
	 */
	public function update_stripe_connect_test_mode( array $data ) {
		if ( ! isset( $data['payments']['stripeConnect']['test_mode'] ) ) {
			return;
		}

		$test_mode     = (bool) $data['payments']['stripeConnect']['test_mode'];
		$current_value = get_option( static::$option_sandbox, null );

		if ( $current_value === $test_mode ) {
			return;
		}

		update_option( static::$option_sandbox, $test_mode );
	}
}
