<?php

namespace Bookit\Classes\Admin;

use Bookit\Classes\Base\User;
use Bookit\Classes\Nonces;
use Bookit\Classes\Template;
use Bookit\Classes\Translations;
use Bookit\Helpers\FreemiusHelper;
use DateTimeZone;

class DashboardController {

	/**
	 *  Pro Addon Slug.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	protected static $proAddon = 'pro';

	protected static $googleCalendarAddon = 'google-calendar';
	protected static $user                = array();

	public static function bookitUser() {
		return User::getUserData();
	}

	/**
	 * Enqueue Admin Styles & Scripts
	 */
	public static function enqueue_styles_scripts() {
		wp_enqueue_style( 'bookit-dashboard-css', BOOKIT_URL . 'assets/dist/dashboard/css/app.css', array(), BOOKIT_VERSION );
		wp_enqueue_script( 'bookit-dashboard-js', BOOKIT_URL . 'assets/dist/dashboard/js/app.js', array(), BOOKIT_VERSION );

		$translations = array_merge( Translations::get_admin_translations(), Translations::get_addon_translations(), Translations::get_addons_page_translations() );

		$ajax_data = [
			'services_url' => admin_url( 'admin.php?page=bookit-services' ),
			'calendar_url' => admin_url( 'admin.php?page=bookit' ),
			'site_url'     => get_bloginfo( 'url' ),
			'plugin_url'   => BOOKIT_URL,
			'ajax_url'     => admin_url( 'admin-ajax.php' ),
			'translations' => $translations,
			'nonces'       => Nonces::get_admin_nonces(),
			'bookit_user'  => self::bookitUser(),
			'pro_disabled' => bookit_pro_features_disabled(), //todo remove
			'has_feedback' => self::has_feedback(),
			'language'     => substr( get_bloginfo( 'language' ), 0, 2 ),
			'timezones'    => self::get_timezones(),
		];

		wp_localize_script( 'bookit-dashboard-js', 'bookit_window', $ajax_data );
	}

	/**
	 * Display Rendered Template
	 * @return bool|string
	 */
	public static function render_addons() {
		wp_enqueue_style( 'bookit-pricing-css', BOOKIT_URL . 'assets/dist/dashboard/css/addons.css', array(), BOOKIT_VERSION );

		$data['translations']  = Translations::get_addons_page_translations();
		$data['freemius_info'] = FreemiusHelper::get_freemius_info();
		$data['descriptions']  = array(
			'bookit-pro'              => __( 'Let your customers select WooCommerce platform and pay for meetings with ease. Merge your Bookit calendar and Google Calendar with just one click. Now easy to book and schedule appointments.', 'bookit' ),
		);
		return Template::load_template( 'dashboard/bookit-addons', $data, true );
	}

	/**
	 * Check if Feedback already added
	 * @return bool
	 */
	public static function has_feedback() {
		return get_option( 'bookit_feedback_added', false );
	}

	/**
	 * Add Feedback
	 */
	public static function add_feedback() {
		check_ajax_referer( 'bookit_add_feedback', 'nonce' );
		update_option( 'bookit_feedback_added', true );
	}

	/**
	 * Get Timezones.
	 *
	 * @since 2.5.0
	 *
	 * @return array<string> An array of timezones.
	 */
	public static function get_timezones() {
	    $timezones = [];
	    $timezone_identifiers = DateTimeZone::listIdentifiers();

	    foreach ($timezone_identifiers as $timezone) {
	        if (strpos($timezone, '/') !== false) {
	            $timezones[] = $timezone;
	        }
	    }

	    return $timezones;
	}
}
