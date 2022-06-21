<?php
namespace WCPagarmePixPayment\Pagarme;

use chillerlan\QRCode\QRCode;

abstract class PagarmeApi {

	/**
	 * API URL.
	 *
	 * @var string
	 */
	protected $api_url;

	/**
	 * Gateway class.
	 *
	 * @var WC_Payment_Gateway
	 */
	protected $gateway;

	/**
	 * Endpoint url.
	 *
	 * @var string
	 */
  protected $endpoint;

  /**
	 * Request Header Parameters.
	 *
	 * @var string
	 */
  protected $headers = array();

	/**
	 * Get API URL.
	 *
	 * @return string
	 */
	public function get_api_url() {
		return $this->api_url;
	}

	/**
	 * Constructor.
	 *
	 * @param WC_Payment_Gateway $gateway Gateway instance.
	 */
	public function __construct( $gateway = null ) {
		$this->gateway = $gateway;
	}

	/**
	 * Do requests in the Pagar.me API.
	 *
	 * @param  string $endpoint API Endpoint.
	 * @param  string $method   Request method.
	 * @param  array  $data     Request data.
	 * @param  array  $headers  Request headers.
	 *
	 * @return array            Request response.
	 */
	protected function do_request( $endpoint, $method = 'POST', $data = array(), $headers = array() ) {
		$params = array(
			'method'  => $method,
			'timeout' => 60,
		);

		if ( ! empty( $data ) ) {
			$params['body'] = $data;
		}

		// Pagar.me user-agent and api version.
		$x_pagarme_useragent = 'wc-pagarme-pix-payment/' . WC_PAGARME_PIX_PAYMENT_PLUGIN_VERSION;

		if ( defined( 'WC_VERSION' ) ) {
			$x_pagarme_useragent .= ' woocommerce/' . WC_VERSION;
		}

		$x_pagarme_useragent .= ' wordpress/' . get_bloginfo( 'version' );
		$x_pagarme_useragent .= ' php/' . phpversion();

		$params['headers'] = [
			'User-Agent' => $x_pagarme_useragent,
			'X-PagarMe-User-Agent' => $x_pagarme_useragent,
		];

    $params['headers'] = array_merge( $params['headers'], $this->headers, $headers );

		return wp_safe_remote_post( $this->get_api_url() . $endpoint, $params );
	}

	/**
	 * Do the transaction.
	 *
	 * @param  WC_Order $order Order data.
	 * @param  array    $args  Transaction args.
	 * @param  string   $token Checkout token.
	 *
	 * @return array           Response data.
	 */
	public function do_transaction( $order, $args, $token = '' ) {
		if ( 'yes' === $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Doing a transaction for order ' . $order->get_order_number() . '...' );
		}

		$response = $this->do_request( $this->endpoint, 'POST', $args );

		if ( is_wp_error( $response ) ) {
			if ( 'yes' === $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'WP_Error in doing the transaction: ' . $response->get_error_message() );
			}

			return array();
		} else {
			$data = json_decode( $response['body'], true );

			if ( isset( $data['errors'] ) ) {
				if ( 'yes' === $this->gateway->debug ) {
					$this->gateway->log->add( $this->gateway->id, 'Failed to make the transaction: ' . print_r( $response, true ) );
				}

				return $data;
			}

			if ( 'yes' === $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'Transaction completed successfully! The transaction response is: ' . print_r( $data, true ) );
			}

			return $data;
		}
	}

	/**
	 * Generate the transaction data.
	 *
	 * @param  WC_Order $order  Order data.
	 *
	 * @return array            Transaction data.
	 */
	abstract public function generate_transaction_data( $order );

	/**
	 * Process regular payment.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return array Redirect data.
	 */
	abstract public function process_regular_payment( $order_id );

	/**
	 * Check if Pagar.me response is validity.
	 *
	 * @param  array $ipn_response IPN response data.
	 *
	 * @return bool
	 */
	public function check_fingerprint( $ipn_response ) {
		if ( isset( $ipn_response['id'] ) && isset( $ipn_response['current_status'] ) && isset( $ipn_response['fingerprint'] ) ) {
			$fingerprint = sha1( $ipn_response['id'] . '#' . $this->gateway->api_key );

			if ( $fingerprint === $ipn_response['fingerprint'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * IPN handler.
	 */
	public function ipn_handler() {
		@ob_clean();

		if ( $this->gateway->is_debug() ) {
			$this->gateway->log->add( $this->gateway->id, 'Retornou um POSTBACK' );
		}

		$ipn_response = ! empty( $_POST ) ? $_POST : false;

		if ( $ipn_response && $this->check_fingerprint( $ipn_response ) ) {
			header( 'HTTP/1.1 200 OK' );

			$this->process_successful_ipn( $ipn_response );
			exit;
		} else {
			wp_die( esc_html__( 'Pagar.me PIX Request Failure', 'wc-pagarme-pix-payment' ), '', array( 'response' => 401 ) );
		}
	}

	/**
	 * Process successeful IPN requests.
	 *
	 * @param array $posted Posted data.
	 */
	public function process_successful_ipn( $posted ) {
		global $wpdb;
		$posted   = wp_unslash( $posted );

		if ( $this->gateway->is_debug() ) {
			$this->gateway->log->add( $this->gateway->id, 'Sucesso: ID = ' . $posted['id'] );
			
		}

		
		$order_id = absint( $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wc_pagarme_pix_payment_transaction_id' AND meta_value = %d", $posted['id'] ) ) );
		$order    = wc_get_order( $order_id );
		$status   = sanitize_text_field( $posted['current_status'] );

		if ( $order && $order->id === $order_id && $posted['transaction']['payment_method'] == 'pix' ) {
			if ( $this->gateway->is_debug() ) {
				$this->gateway->log->add( $this->gateway->id, print_r($posted, true) );
			}
			$this->process_order_status( $order, $status );
		}
	}

	/**
	 * Process the order status.
	 *
	 * @param WC_Order $order  Order data.
	 * @param string   $status Transaction status.
	 */
	public function process_order_status( $order, $status ) {
		if ( $this->gateway->is_debug() ) {
			$this->gateway->log->add( $this->gateway->id, 'PIX: Payment status for order ' . $order->get_order_number() . ' is now: ' . $status );
		}

		switch ( $status ) {
			case 'waiting_payment' :
				$order->update_status( 'on-hold', __( 'Aguardando pagamento via PIX.', 'wc-pagarme-pix-payment' ) );
				break;
			case 'paid' :
				if ( ! in_array( $order->get_status(), array( 'processing', 'completed' ), true ) ) {
					$order->add_order_note( __( 'Pagar.me PIX: Transação paga.', 'wc-pagarme-pix-payment' ) );
				}

				update_post_meta( $order->get_id(), '_wc_pagarme_pix_payment_paid', 'yes' );
				
				// Changing the order for processing and reduces the stock.
				$order->payment_complete();
				
				$after_paid_status = $this->gateway->after_paid_status;

				if( $after_paid_status != 'wc-processing' ){
					$statuses = wc_get_order_statuses();
					$order->update_status( $after_paid_status, __( sprintf('Pagar.me PIX: Pedido alterado para %s.', $statuses[$after_paid_status]) ));
				}
				break;
			default :
				break;
		}
	}

	/**
	 * Only numbers.
	 *
	 * @param  string|int $string String to convert.
	 *
	 * @return string|int
	 */
	protected function only_numbers( $string ) {
		return preg_replace( '([^0-9])', '', $string );
	}
}