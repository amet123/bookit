<?php

namespace Bookit\Gateways\StripeConnect;

/**
 * The Stripe Connect Compatibility settings.
 *
 * @since   2.5.0
 *
 * @package Bookit\Gateways\StripeConnect
 */
class Compatibility {

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
		if ( ! defined( 'BOOKIT_PRO_VERSION' ) ) {
			return $template_vars;
		}

		$is_compatible_version = version_compare( BOOKIT_PRO_VERSION, '2.0.4', '>' );
		if ( $is_compatible_version ) {
			return $template_vars;
		}

		$p_index = $this->get_pro_addon_index( $template_vars['addons'] );
		if ( $p_index === false || empty( $template_vars['addons'][ $p_index ]['data']['settings']['payments'] ) ) {
			return $template_vars;
		}

		if ( ! empty( $template_vars['addons'][ $p_index ]['data']['settings']['payments']['stripeConnect'] ) ) {
			return $template_vars;
		}

		$template_vars['addons'][ $p_index ]['data']['settings']['payments'][] = [
			'name'     => 'stripeConnect',
			'settings' => [],
		];

		$payments                                                            = $template_vars['addons'][ $p_index ]['data']['settings']['payments'];
		$template_vars['addons'][ $p_index ]['data']['settings']['payments'] = $this->update_payments_with_formatted_name( $payments );

		return $template_vars;
	}

	/**
	 * Get index of the 'addons' array where the 'name' key equals 'pro'.
	 *
	 * @since 2.5.0
	 *
	 * @param array $addons The array of addons.
	 *
	 * @return int|bool The index if found, false otherwise.
	 */
	protected function get_pro_addon_index( array $addons ) {
		foreach ( $addons as $index => $addon ) {
			if ( isset( $addon['name'] ) && $addon['name'] === 'pro' ) {
				return $index;
			}
		}

		return false;
	}

	/**
	 * Update payments to add formatted_name if it is missing.
	 *
	 * @since 2.5.0
	 *
	 * @param array $payments Array of payment settings.
	 *
	 * @return array Updated array of payment settings.
	 */
	protected function update_payments_with_formatted_name( array $payments ): array {
		$available_payments = $this->getAvailablePayments();
		foreach ( $payments as &$payment ) {
			foreach ( $available_payments as $available_payment ) {
				if ( $payment['name'] === $available_payment['name'] && empty( $payment['formatted_name'] ) ) {
					$payment['formatted_name'] = $available_payment['formatted_name'];
					break;
				}
			}
		}

		return $payments;
	}

	/**
	 * Get available payments.
	 *
	 * @since 2.5.0
	 *
	 * @return array<int, array<string|mixed>> Array of available payment methods.
	 */
	protected function getAvailablePayments(): array {
		return [
			[
				'name'           => 'stripeConnect',
				'formatted_name' => esc_html_x( 'Stripe Connect', 'Stripe Connect payment singular name.', 'bookit' ),
			],
			[
				'name'           => 'paypal',
				'formatted_name' => esc_html_x( 'PayPal - Legacy', 'PayPal Legacy payment singular name.', 'bookit' ),
			],
			[
				'name'           => 'stripe',
				'formatted_name' => esc_html_x( 'Stripe - Legacy', 'Stripe Legacy payment singular name.', 'bookit' ),
			],
			[
				'name'           => 'woocommerce',
				'formatted_name' => esc_html_x( 'WooCommerce', 'WooCommerce payment singular name.', 'bookit' ),
			],
		];
	}
}
