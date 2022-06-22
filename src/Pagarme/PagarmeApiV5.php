<?php

namespace WCPagarmePixPayment\Pagarme;

use chillerlan\QRCode\QRCode;
class PagarmeApiV5 extends PagarmeApi
{
  protected $api_url = 'https://api.pagar.me/core/v5/';

  protected $endpoint = 'orders/';

  public function __construct( $gateway = null ) {
		$this->gateway = $gateway;
    
    $this->headers = [
      'Authorization' => 'Basic ' . base64_encode("{$gateway->secret_key}:"),
      'Content-Type' => 'application/json',
      'Accept' => 'application/json'
    ];
	}

  /**
   * Generate the transaction data.
   *
   * @param  WC_Order $order  Order data.
   *
   * @return array            Transaction data.
   */
  public function generate_transaction_data($order)
  {
    // Set the request data.
    $data = array(
      'metadata' => [
        'order_number' => $order->get_order_number(),
      ],
      'items' => [
        [
          'amount' => $order->get_total() * 100,
          'description' => 'WCPagarmePixPayment',
          'quantity' => 1
        ]
      ],
      'customer' => [
        'name' => trim($order->billing_first_name . ' ' . $order->billing_last_name),
        'email' => $order->billing_email,
        'type' => 'individual',
        'document' => 'null',
      ],
      'payments' => [
        [
          'payment_method' => 'pix',
          'pix' => [
            'expires_at' => date('Y-m-d H:i:s', strtotime('+' . $this->gateway->expiration_days . ' days ' . $this->gateway->expiration_hours . ' hours', current_time('timestamp'))),
          ]
        ]
      ]
    );

    // Phone.
    if (!empty($order->billing_phone)) {
      $phone = $this->only_numbers($order->billing_phone);

      $data['customer']['phones']['mobile_phone'] = array(
        'country_code' => '55',
        'area_code' => substr($phone, 0, 2),
        'number'    => substr($phone, 2),
      );
    }

    // Set the document number.
    if (class_exists('Extra_Checkout_Fields_For_Brazil')) {
      $wcbcf_settings = get_option('wcbcf_settings');
      $person_type    = (string) $wcbcf_settings['person_type'];
      if ('0' !== $person_type) {
        if (('1' === $person_type && '1' === $order->billing_persontype) || '2' === $person_type) {
          $data['customer']['document'] = $this->only_numbers($order->billing_cpf);
          $data['customer']['type'] = 'individual';
          $data['customer']['document_type'] = 'CPF';
        }

        if (('1' === $person_type && '2' === $order->billing_persontype) || '3' === $person_type) {
          $data['customer']['name'] = $order->billing_company;
          $data['customer']['type'] = 'company';
          $data['customer']['document'] = $this->only_numbers($order->billing_cnpj);
          $data['customer']['document_type'] = 'CNPJ';
        }
      }
    } else {
      if (!empty($order->billing_cpf)) {
        $data['customer']['document'] = $this->only_numbers($order->billing_cpf);
      }
      if (!empty($order->billing_cnpj)) {
        $data['customer']['name'] = $order->billing_company;
        $data['customer']['document'] = $this->only_numbers($order->billing_cnpj);
      }
    }

    // Set the customer gender.
    if (!empty($order->billing_sex)) {
      $data['customer']['gender'] = strtoupper(substr($order->billing_sex, 0, 1)) == 'M' ? 'male' : 'female';
    }

    // Set the customer birthdate.
    if (!empty($order->billing_birthdate)) {
      $birthdate = explode('/', $order->billing_birthdate);

      $data['customer']['birthdate'] = $birthdate[1] . '-' . $birthdate[0] . '-' . $birthdate[2];
    }

    // Add filter for Third Party plugins.
    return apply_filters('wc_pagarme_pix_payment_transaction_data', $data, $order);
  }

  public function process_regular_payment($order_id)
  {
    $order = wc_get_order($order_id);

    if ($this->gateway->is_debug()) {
      $this->gateway->log->add($this->gateway->id, 'API PagarmePix: Init process payment');
    }

    $data        = $this->generate_transaction_data($order);
    $transaction = $this->do_transaction($order, json_encode($data));

    if (isset($transaction['errors'])) {
      foreach ($transaction['errors'] as $error) {
        wc_add_notice($error['message'], 'error');
      }

      return array(
        'result' => 'fail',
      );
    } else {

      if (extension_loaded('mbstring') && version_compare(phpversion(), "7.4", ">=")) {
        $upload = wp_upload_dir();
        $upload_folder = sprintf('%s/%s/qr-codes/', $upload['basedir'], \WC_PAGARME_PIX_PAYMENT_DIR_NAME);
        $upload_url = sprintf('%s/%s/qr-codes/', $upload['baseurl'], \WC_PAGARME_PIX_PAYMENT_DIR_NAME);

        if (!file_exists($upload_folder)) {
          wp_mkdir_p($upload_folder);
        }

        $qrcode_file_name = date('Ymd', strtotime(current_time('mysql'))) . $transaction['id'] . '.png';
        (new QRCode)->render($transaction['charges'][0]['last_transaction']['qr_code'], $upload_folder . $qrcode_file_name);

        update_post_meta($order_id, '_wc_pagarme_pix_payment_qr_code_image', $upload_url . $qrcode_file_name);
      } else {
        update_post_meta($order_id, '_wc_pagarme_pix_payment_qr_code_image', sprintf("%s", $transaction['charges'][0]['last_transaction']['qr_code_url'] ));
      }

      update_post_meta($order_id, '_wc_pagarme_pix_payment_qr_code', $transaction['charges'][0]['last_transaction']['qr_code']);
      update_post_meta($order_id, '_wc_pagarme_pix_payment_expiration_date', date('Y-m-d H:i:s', strtotime('+' . $this->gateway->expiration_days . ' days ' . $this->gateway->expiration_hours . ' hours', current_time('timestamp'))));
      update_post_meta($order_id, '_wc_pagarme_pix_payment_expiration_days', $this->gateway->expiration_days);
      update_post_meta($order_id, '_wc_pagarme_pix_payment_transaction_id', $transaction['id']);
      update_post_meta($order_id, '_wc_pagarme_pix_payment_paid', 'no');

      $this->process_order_status($order, $transaction['status']);

      // Empty the cart.
      WC()->cart->empty_cart();

      // Redirect to thanks page.
      return array(
        'result'   => 'success',
        'redirect' => $this->gateway->get_return_url($order),
      );
    }
  }

  public function check_fingerprint( $ipn_response ) {
		if ( isset( $ipn_response['id'] ) ) {
			return true;
		}

		return false;
	}

  public function process_successful_ipn( $posted ) {
		global $wpdb;
		$posted   = wp_unslash( $posted );

		if ( $this->gateway->is_debug() ) {
			$this->gateway->log->add( $this->gateway->id, 'Sucesso: ID = ' . $posted['id'] );
			$this->gateway->log->add( $this->gateway->id, 'Sucesso: OrderID = ' . $posted['data']['order']['id'] );	
		}
    
		$order_id = absint( $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wc_pagarme_pix_payment_transaction_id' AND meta_value = '%s'", $posted['data']['order']['id'] ) ) );
		$order    = wc_get_order( $order_id );
		$status   = sanitize_text_field( $posted['data']['status'] );

		if ( $order && $order->get_id() === $order_id && $posted['data']['payment_method'] == 'pix' ) {
     
			if ( $this->gateway->is_debug() ) {
				$this->gateway->log->add( $this->gateway->id, print_r($posted, true) );
			}
			$this->process_order_status( $order, $status );
		}
	}

  public function ipn_handler() {
		@ob_clean();

    $post = file_get_contents('php://input');

		if ( $this->gateway->is_debug() ) {
			$this->gateway->log->add( $this->gateway->id, 'Retornou um POSTBACK' );
      $this->gateway->log->add( $this->gateway->id, 'Response' . print_r($post, true) );
		}

		$ipn_response = ! empty( $post ) ? json_decode($post, true) : false;

		if ( $ipn_response && $this->check_fingerprint( $ipn_response ) ) {
			header( 'HTTP/1.1 200 OK' );

			$this->process_successful_ipn( $ipn_response );

      wp_send_json(array(
        'success' => true,
      ), 200);
			exit;
		} else {
      
			wp_die( esc_html__( 'Pagar.me PIX Request Failure', 'wc-pagarme-pix-payment' ), '', array( 'response' => 401 ) );
		}
	}
}
