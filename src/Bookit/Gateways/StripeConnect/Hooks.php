<?php

namespace Bookit\Gateways\StripeConnect;

use Bookit\Contracts\Service_Provider;

/**
 * Class Hooks
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\StripeConnect
 */
class Hooks extends Service_Provider {

	/**
	 * @inheritDoc
	 */
	public function register() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Adds the actions required by each Stripe component.
	 *
	 * @since 2.5.0
	 */
	protected function add_actions() {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
		add_action( 'bookit_before_update_setting', [ $this, 'update_stripe_connect_test_mode' ] );

		add_action( 'wp_ajax_bookit_stripeConnect_intent_payment', [ $this, 'intent_payment' ] );
		add_action( 'wp_ajax_nopriv_bookit_stripeConnect_intent_payment', [ $this, 'intent_payment' ] );
	}

	/**
	 * Register the Endpoints from Stripe.
	 *
	 * @since 2.5.0
	 */
	public function register_endpoints() {
		$this->container->make( REST::class )->register_endpoints();
	}

	/**
	 * Create Payment Method Ajax Action
	 *
	 * @since 2.5.0
	 *
	 * @return array Ajax response.
	 */
	public function intent_payment() {
		$this->container->make( Merchant::class )->intent_payment();
	}

	/**
	 * Updates the StripeConnect test mode option.
	 *
	 * @since 2.5.0
	 *
	 * @param array $data The options data containing StripeConnect settings.
	 */
	public function update_stripe_connect_test_mode( array $data ) {
		$this->container->make( Settings::class )->update_stripe_connect_test_mode( $data );
	}

	/**
	 * Adds the filters required by each Stripe component.
	 *
	 * @since 2.5.0
	 */
	protected function add_filters() {
		add_filter( 'bookit_settings', [ $this, 'add_settings' ] );
		add_filter( 'bookit_settings_template_vars', [ $this, 'update_template_vars' ] );
		add_filter( 'bookit_settings_template_vars', [ $this, 'add_compatibility_payment_settings' ], 20 );
	}

	/**
	 * Filter the BookIt settings.
	 *
	 * @since 2.5.0
	 *
	 * @param array<string|mixed> $settings The array of settings.
	 */
	public function add_settings( $settings ) {
		return $this->container->make( Settings::class )->add_settings( $settings );
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
		return $this->container->make( Settings::class )->update_template_vars( $template_vars );
	}

	/**
	 * Adds compatibility payment settings to the template variables.
	 *
	 * This method checks the version of BOOKIT_PRO, verifies compatibility, and ensures that payment settings
	 * have the formatted name. If the 'stripeConnect' payment is missing, it will be added.
	 *
	 * @since 2.5.0
	 *
	 * @param array<string|mixed> $template_vars The template variables.
	 *
	 * @return array<string|mixed> Updated template variables with compatibility payment settings.
	 */
	public function add_compatibility_payment_settings( $template_vars ) {
		return $this->container->make( Compatibility::class )->add_compatibility_payment_settings( $template_vars );
	}
}
