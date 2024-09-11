<?php

namespace WCPagarmePixPayment\Pagarme;

use chillerlan\QRCode\QRCode;

/**
 * Pagarme API Integration class
 */
class PagarmeApiV4 extends PagarmeApi
{

	protected $api_url = 'https://api.pagar.me/1/';

	protected $endpoint = 'transactions';

	public function __construct($gateway = null)
	{
		$this->gateway = $gateway;

		$this->headers = [
			'X-PagarMe-Version' => '2017-07-17',
		];
	}

	/**
	 * Generate the transaction data.
	 *
	 * @param  WC_Order $order  Order data.
	 *
	 * @return array|null Transaction data.
	 */
	public function generate_transaction_data($order)
	{
		// Set the request data.
		$data = array(
			'api_key' => $this->gateway->api_key,
			'payment_method' => 'pix',
			'pix_expiration_date' => date('Y-m-d H:i:s', strtotime('+' . $this->gateway->expiration_days . ' days ' . $this->gateway->expiration_hours . ' hours', current_time('timestamp'))),
			'amount' => round($order->get_total() * 100),
			'postback_url' => WC()->api_request_url($this->gateway->id),
			'customer' => array(
				'name' => trim($order->billing_first_name . ' ' . $order->billing_last_name),
				'email' => $order->billing_email,
			),
			'metadata' => array(
				'order_number' => $order->get_order_number(),
			),
		);

		// Phone.
		if (! empty($order->billing_phone)) {
			$phone = $this->only_numbers($order->billing_phone);

			$data['customer']['phone'] = array(
				'ddd' => substr($phone, 0, 2),
				'number' => substr($phone, 2),
			);
		}

		if ($order->billing_cpf == null && $order->billing_cnpj == null) {
			wc_add_notice('É obrigatório preencher o campo CPF para pagamento em PIX.', 'error');
			return null;
		}

		// Set the document number.
		if (class_exists('Extra_Checkout_Fields_For_Brazil')) {
			$wcbcf_settings = get_option('wcbcf_settings');
			$person_type = (string) $wcbcf_settings['person_type'];
			if ('0' !== $person_type) {
				if (('1' === $person_type && '1' === $order->billing_persontype) || '2' === $person_type) {
					if (! $this->cpfValidator($order->billing_cpf)) {
						wc_add_notice('CPF Inválido.', 'error');
						return null;
					}
					$data['customer']['document_number'] = $this->only_numbers($order->billing_cpf);
				}

				if (('1' === $person_type && '2' === $order->billing_persontype) || '3' === $person_type) {
					$data['customer']['name'] = $order->billing_company;
					$data['customer']['document_number'] = $this->only_numbers($order->billing_cnpj);
				}
			}
		} else {
			if (! empty($order->billing_cpf)) {
				if (! $this->cpfValidator($order->billing_cpf)) {
					wc_add_notice('CPF Inválido.', 'error');
					return null;
				}
				$data['customer']['document_number'] = $this->only_numbers($order->billing_cpf);
			}
			if (! empty($order->billing_cnpj)) {
				$data['customer']['name'] = $order->billing_company;
				$data['customer']['document_number'] = $this->only_numbers($order->billing_cnpj);
			}
		}

		// Set the customer gender.
		if (! empty($order->billing_sex)) {
			$data['customer']['sex'] = strtoupper(substr($order->billing_sex, 0, 1));
		}

		// Set the customer birthdate.
		if (! empty($order->billing_birthdate)) {
			$birthdate = explode('/', $order->billing_birthdate);

			$data['customer']['born_at'] = $birthdate[1] . '-' . $birthdate[0] . '-' . $birthdate[2];
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

		$data = $this->generate_transaction_data($order);

		if ($this->gateway->is_debug()) {
			$this->gateway->log->add($this->gateway->id, 'API PagarmePix: Send pagarme data:' . print_r($data, true));
		}


		$transaction = $this->do_transaction($order, $data);

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

				if (! file_exists($upload_folder)) {
					wp_mkdir_p($upload_folder);
				}

				$qrcode_file_name = date('Ymd', strtotime(current_time('mysql'))) . $transaction['id'] . '.png';
				(new QRCode)->render($transaction['pix_qr_code'], $upload_folder . $qrcode_file_name);

				$order->update_meta_data('_wc_pagarme_pix_payment_qr_code_image', $upload_url . $qrcode_file_name);
			} else {
				$order->update_meta_data('_wc_pagarme_pix_payment_qr_code_image', sprintf("https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=%s&choe=UTF-8", urlencode($transaction['pix_qr_code'])));
			}

			$order->update_meta_data('_wc_pagarme_pix_payment_qr_code', $transaction['pix_qr_code']);
			$order->update_meta_data('_wc_pagarme_pix_payment_expiration_date', date('Y-m-d H:i:s', strtotime('+' . $this->gateway->expiration_days . ' days ' . $this->gateway->expiration_hours . ' hours', current_time('timestamp'))));
			$order->update_meta_data('_wc_pagarme_pix_payment_expiration_days', $this->gateway->expiration_days);
			$order->update_meta_data('_wc_pagarme_pix_payment_transaction_id', $transaction['id']);
			$order->update_meta_data('_wc_pagarme_pix_payment_paid', 'no');

			$order->save();

			$this->process_order_status($order, $transaction['status']);

			// Empty the cart.
			if (method_exists(WC()->cart, 'empty_cart')) {
				WC()->cart->empty_cart();
			}

			// Redirect to thanks page.
			return array(
				'result' => 'success',
				'redirect' => $this->gateway->get_return_url($order),
			);
		}
	}

	public function validate($payload, $signature)
	{
		$parts = explode('=', $signature, 2);

		if (count($parts) != 2) {
			return false;
		}

		$apiKey = $this->gateway->api_key;

		return hash_hmac($parts[0], $payload, $apiKey) === $parts[1];
	}

	public function check_fingerprint($ipn_response)
	{
		if (isset($_SERVER['HTTP_X_HUB_SIGNATURE']) && isset($ipn_response['id']) && isset($ipn_response['current_status'])) {
			$postbackPayload = file_get_contents('php://input');
			$signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];

			if ($this->validate($postbackPayload, $signature)) {
				return true;
			}
		}

		return false;
	}

	public function process_successful_ipn($posted)
	{
		$posted = wp_unslash($posted);

		if ($this->gateway->is_debug()) {
			$this->gateway->log->add($this->gateway->id, 'Sucesso: ID = ' . $posted['id']);
		}

		$args = array(
			'limit' => 1,
			'meta_key' => '_wc_pagarme_pix_payment_transaction_id',
			'meta_value' => $posted['id'],
			'meta_compare' => '=',
		);

		$orders = wc_get_orders($args);

		if (empty($orders)) {
			$this->gateway->log->add($this->gateway->id, 'ERRO: Nenhum internalId = ' . $posted['internalId']);
			return;
		}

		$order = reset($orders);
		$status = sanitize_text_field($posted['current_status']);

		if ($order && $posted['transaction']['payment_method'] == 'pix') {
			if ($this->gateway->is_debug()) {
				$this->gateway->log->add($this->gateway->id, print_r($posted, true));
			}
			$this->process_order_status($order, $status);
		}
	}

	public function ipn_handler()
	{
		@ob_clean();

		if ($this->gateway->is_debug()) {
			$this->gateway->log->add($this->gateway->id, 'Retornou um POSTBACK');

			$this->gateway->log->add($this->gateway->id, 'Response' . print_r($_POST, true));
		}

		$ipn_response = ! empty($_POST) ? $_POST : false;

		if ($ipn_response) {
			header('HTTP/1.1 200 OK');

			$this->process_successful_ipn($ipn_response);
			exit;
		} else {
			wp_die(esc_html__('Pagar.me PIX Request Failure', 'wc-pagarme-pix-payment'), '', array('response' => 401));
		}
	}
}
