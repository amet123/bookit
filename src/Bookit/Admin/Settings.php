<?php

namespace Bookit\Admin;

/**
 * Class Settings
 *
 * @since   2.5.0
 *
 * @package Bookit\Admin
 */
class Settings {

	/**
	 * Current page ID (or false if not registered with this controller).
	 *
	 * @since 2.5.0
	 *
	 * @var string|null
	 */
	private $current_page = null;

	/**
	 * Bookit settings page slug.
	 *
	 * @var string
	 */
	public static $settings_page_id = 'bookit-settings';

	/**
	 * Gets the URL to the Bookit settings page with optional query arguments and anchor links.
	 *
	 * @since 2.5.0
	 *
	 * @param array  $args   Query arguments to add to the URL.
	 * @param string $anchor Anchor link to append to the URL (e.g., 'payments').
	 *
	 * @return string The URL to the Bookit settings page with query arguments and anchor link.
	 */
	public function get_url( array $args = [], $anchor = '' ) {
		$defaults = [
			'page' => static::$settings_page_id,
		];

		// Allow the link to be "changed" on the fly.
		$args = wp_parse_args( $args, $defaults );

		$wp_url = is_network_admin() ? network_admin_url( 'settings.php' ) : admin_url( 'admin.php' );

		// Keep the resulting URL args clean.
		$url = add_query_arg( $args, $wp_url );

		// Add the anchor link if provided.
		if ( ! empty( $anchor ) ) {
			$url .= '#' . ltrim( $anchor, '#' );
		}

		/**
		 * Filters the URL to the Bookit settings page.
		 *
		 * @since 2.5.0
		 *
		 * @param string $url The URL to the Bookit settings page.
		 */
		return apply_filters( 'bookit_settings_url', $url );
	}

	/**
	 * Get the current page.
	 *
	 * @since 2.5.0
	 *
	 * @return string|boolean Current page or false if not registered with this controller.
	 */
	public function get_current_page() {
		if ( is_null( $this->current_page ) ) {
			$this->determine_current_page();
		}

		return $this->current_page;
	}

	/**
	 * Determine the current page.
	 *
	 * @since 2.5.0
	 *
	 * @return string|boolean Current page or false if not registered with this controller.
	 */
	public function determine_current_page() {
		$current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( is_null( $current_screen ) ) {
			$this->current_page = bookit_get_request_var( 'page' );

			return $this->current_page;
		}

		$this->current_page = $current_screen->id;

		return $this->current_page;
	}
}
