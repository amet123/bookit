<?php

namespace Bookit\Gateways\StripeConnect;

use Bookit\Admin\Settings as Plugin_Settings;
use Bookit\Gateways\Contracts\Abstract_Gateway;
use Bookit\Gateways\StripeConnect\REST\Return_Endpoint;

/**
 * Class Gateway
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\StripeConnect
 */
class Gateway extends Abstract_Gateway {

	/**
	 * @inheritDoc
	 */
	protected static $key = 'stripe';

	/**
	 * @inheritDoc
	 */
	protected static $supported_currencies = [
		'USD', 'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD',
		'BDT', 'BGN', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BWP', 'BYN', 'BZD', 'CAD', 'CDF',
		'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ETB',
		'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK',
		'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'ISK', 'JMD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KRW',
		'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT',
		'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR',
		'NZD', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF',
		'SAR', 'SBD', 'SCR', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'STD', 'SZL', 'THB', 'TJS',
		'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'UYU', 'UZS', 'VND', 'VUV', 'WST', 'XAF',
		'XCD', 'XOF', 'XPF', 'YER', 'ZAR', 'ZMW',
	];

	/**
	 * Stripe tracking ID version.
	 *
	 * This shouldn't be updated unless we are modifying something on the Stripe user level.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * An instance of the Plugin_Settings handler.
	 *
	 * @since 2.5.0
	 *
	 * @var Plugin_Settings
	 */
	protected $plugin_settings;

	/**
	 * Gateway constructor.
	 *
	 * @since 2.5.0
	 *
	 * @param Plugin_Settings $plugin_settings An instance of the Plugin_Settings handler.
	 */
	public function __construct( Plugin_Settings $plugin_settings ) {
		$this->plugin_settings = $plugin_settings;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_label() {
		return __( 'Stripe Connect', 'bookit' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_admin_notices() {
		$notices = [
			[
				'slug'    => 'bookit-stripe-signup-error',
				'content' => __( "Stripe wasn't able to complete your connection request. Try again.", 'bookit' ),
				'type'    => 'error',
			],
			[
				'slug'    => 'bookit-stripe-token-error',
				'content' => __( 'Stripe signup was successful but the authentication tokens could not be retrieved. Try refreshing the tokens.', 'bookit' ),
				'type'    => 'error',
			],
			[
				'slug'    => 'bookit-stripe-disconnect-error',
				'content' => __( 'Disconnecting from Stripe failed. Please try again.', 'bookit' ),
				'type'    => 'error',
			],
			[
				'slug' => 'bookit-stripe-currency-mismatch',
				'type' => 'notice',
				'dismiss' => true,
			],
			[
				'slug'    => 'bookit-stripe-country-denied',
				'content' => __( 'Due to Regulatory Issues between Stripe and the country listed in your Stripe account, the Bookit Payments cannot accept connections from accounts in your country. Please use a Stripe account from a different country or purchase Bookit Pro to continue.', 'bookit' ),
				'type'    => 'error',
			],
			[
				'slug'    => 'bookit-stripe-account-disconnected',
				'content' => sprintf(
					// Translators: %1$s is the opening <a> tag for the Payments Tab page link. %2$s is the closing <a> tag.
					__( 'Your stripe account was disconnected from the Stripe dashboard. If you believe this is an error, you can re-connect in the %1$sPayments Tab of the Settings Page%2$s.', 'bookit' ),
					'<a href="' . $this->plugin_settings->get_url( [], 'payments' ) . '">',
					'</a>' ),
				'type'    => 'error',
				'dismiss' => true,
			],
		];

		return $notices;
	}

	/**
	 * @inheritDoc
	 */
	public function get_logo_url(): string {
		return BOOKIT_URL . 'assets/images/stripe/stripe-logo.png';
	}

	/**
	 * @inheritDoc
	 */
	public function get_subtitle(): string {
		return __( 'Enable credit card payments, Afterpay, AliPay, Giropay, Klarna and more.', 'bookit' );
	}

	/**
	 * @inheritDoc
	 */
	public function generate_unique_tracking_id() {
		return bookit( Return_Endpoint::class )->get_route_url();
	}
}
