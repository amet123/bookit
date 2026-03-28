<?php

namespace Bookit\Classes\Payments;

use Bookit\Classes\Database\Payments as PaymentDb;

class PayPal {

	public $url;
	public $currency_code;
	public $email;
	public $return_url;
	public $invoice;
	public $amount;
	public $item_name;
	public $item_number;
	public $user_email;

	/**
	 * PayPal constructor.
	 *
	 * @param int $amount
	 * @param int $invoice
	 * @param string $item_name
	 * @param string $item_number
	 * @param string $user_email
	 * @param string $return_url
	 */
	public function __construct( $amount = 10, $invoice = 0, $item_name = '', $item_number = '', $user_email = '', $return_url = '' ) {
		$settings = get_option( 'bookit_settings' );
		$payments = $settings['payments'];

		if ( ! empty( $payments['paypal'] ) && ! empty( $payments['paypal']['enabled'] ) ) {
			$paypal = $payments['paypal'];

			$this->url           = ( 'live' == $paypal['mode'] ) ? 'www.paypal.com' : 'www.sandbox.paypal.com';
			$this->currency_code = $settings['currency'];
			$this->email         = $paypal['email'];
			$this->invoice       = $invoice;
			$this->amount        = $amount;
			$this->item_name     = $item_name;
			$this->item_number   = $item_number;
			$this->user_email    = $user_email;
			$this->return_url    = apply_filters( 'bookit_paypal_return_url', empty( $return_url ) ? home_url() : $return_url );
		}
	}

	/**
	 * Generate Payment URL
	 * @return string
	 */
	public function generate_payment_url() {
		$get_params = array(
			'cmd'           => '_xclick',
			'business'      => $this->email,
			'no_shipping'   => 1,
			'no_note'       => 1,
			'currency_code' => strtoupper( $this->currency_code ),
			'bn'            => 'PP%2dBuyNowBF',
			'charset'       => 'UTF%2d8',
			'item_name'     => $this->item_name,
			'item_number'   => $this->item_number,
			'invoice'       => $this->invoice,
			'return'        => $this->return_url,
			'email'         => $this->user_email,
			'rm'            => 2,
			'amount'        => $this->amount,
			'notify_url'    => get_home_url() . '/?stm_bookit_check_ipn=1',
		);

		$url = 'https://' . $this->url . '/cgi-bin/webscr?' . http_build_query( $get_params );

		return $url;
	}

	/**
	 * Check IPN Response
	 * @param $ipn_response
	 */
	public function check_payment( $ipn_response ) {
		$paypal_adress  = 'https://' . $this->url . '/cgi-bin/webscr';
		$validate_ipn   = array( 'cmd' => '_notify-validate' );
		$validate_ipn   += stripslashes_deep( $ipn_response );

		$params = array(
			'body'        => $validate_ipn,
			'sslverify'   => false,
			'timeout'     => 60,
			'httpversion' => '1.1',
			'compress'    => false,
			'decompress'  => false,
			'user-agent'  => 'paypal-ipn/',
		);

		$response = wp_safe_remote_post( $paypal_adress, $params );

		$data = array(
			'transaction' => $ipn_response['txn_id'],
			'notes'       => serialize( $ipn_response ),
			'updated_at'  => wp_date( 'Y-m-d H:i:s' ),
		);

		if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr( $response['body'], 'VERIFIED' ) ) {
			$data['status']  = PaymentDb::$completeStatus;
			$data['paid_at'] = wp_date( 'Y-m-d H:i:s' );

			PaymentDb::update( $data, array( 'appointment_id' => $ipn_response['invoice'] ) );

			do_action( 'bookit_payment_complete', $ipn_response['invoice'] );
		} else {
			$data['status'] = PaymentDb::$rejectedStatus;
			PaymentDb::update( $data, array( 'appointment_id' => $this->invoice ) );
		}

		header( 'HTTP/1.1 200 OK' );
		exit;
	}
}

