<?php
/**
 * Pix GeteWay class
 */
class PagarmePixGateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
        
		$this->id                   = 'wc_pagarme_pix_payment_geteway';
		$this->icon                 = false;
		$this->has_fields           = true;
		$this->method_title         = 'Pix';
		$this->method_description   = 'Pagamento via PIX processados pela Pagarme.';
		$this->supports = array('products');

		// Method with all the options fields
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		//Main settings
		$this->setup_settings();

		//$this->icon = apply_filters( 'woocommerce_gateway_icon', WC_PIGGLY_PIX_PLUGIN_URL.'assets/'.$this->select_icon.'.png' );

		// Active logs.
		if ( 'yes' === $this->debug ) {
			$this->log = new WC_Logger();
		}

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 3 );
		//add_action( 'woocommerce_api_wc_pagarme_banking_ticket_gateway', array( $this, 'ipn_handler' ) );
	}

	/**
	 * Setup all form fields.
	 * 
	 * @since 1.1.0
	 * @return void
	 */
	public function init_form_fields() { 
		return; 
	}

	/**
	 * Set settings function
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setup_settings() {
		// Define user set variables.
		$this->title          = $this->get_option( 'title' );
		$this->description    = $this->get_option( 'description' );
		$this->debug          = $this->get_option( 'debug' );
		$this->async          = $this->get_option( 'async' );
	}

	/**
	 * Admin page.
	 */
	public function admin_options() {
		include dirname( __FILE__ ) . '/admin/views/html-admin-page.php';
	}

	/**
	 * Payment fields.
	 */
	public function payment_fields() {
		if ( $description = $this->get_description() ) {
			echo wp_kses_post( wpautop( wptexturize( $description ) ) );
		}
		wc_get_template(
			'debito-automatico.php',
			array(),
			'woocommerce/pagarme/',
			WOODEBAUTO_TEMPLATES_PATH
		);
	}

	/**
	 * Process the payment.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return array Redirect data.
	 */
	public function process_payment( $order_id ) {

		//$url = "http://siacc02.intermedvida.com.br/wp-json/mgn-seg/v2/segurados/create";
		$url = "http://mgntech.com.br/wp-json/mgn-seg/v2/segurados/create";

		$fields = [
			'nome' => $_POST['billing_first_name'] . ' ' . $_POST['billing_last_name'],
			'cpf' => $_POST['billing_cpf'],
			'nasc' => $_POST['billing_birthdate'],
			'sexo' => $_POST['billing_sex'],
			'celular' => $_POST['billing_cellphone'],
			'email' => $_POST['billing_email'],
			'cep' => $_POST['billing_postcode'],
			'endereco' => $_POST['billing_address_1'],
			'numero' => $_POST['billing_number'],
			'complemento' => $_POST['billing_address_2'],
			'bairro' => $_POST['billing_neighborhood'],
			'cidade' => $_POST['billing_city'],
			'estado' => $_POST['billing_state'],
			'conta' => $_POST['conta'],
			'agencia' => $_POST['agencia'],
			'banco' => $_POST['banco'],
			'produto' => 'plus',
			'operacao' => $_POST['tipo_conta']
		];
		
		$fields_string = http_build_query($fields);

		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_handle, CURLOPT_POST, true);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $fields_string);
		$result = curl_exec($curl_handle);
		curl_close($curl_handle);

		$result = json_decode($result);
		
		if( $result->status == 'OK' ){
			$order = wc_get_order( $order_id );

			foreach( $fields as $key => $value ){
				//$order->update_meta_data( '_wc_da_' . $key, $value );
				update_post_meta( $order_id, '_wc_da_' . $key, $value );
			}

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order )
			);
		}
		else{
			wc_add_notice( $result->message, 'error' );
			return array(
				'result' => 'fail',
			);
		}


	}

	/**
	 * Thank You page message.
	 *
	 * @param int $order_id Order ID.
	 */
	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		$banco = $order->get_meta('_wc_da_banco');
		wc_get_template(
			'debito-automatico-payment.php',
			array(
				'banco' => $banco
			),
			'woocommerce/pagarme/',
			WOODEBAUTO_TEMPLATES_PATH
		);
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param  object $order         Order object.
	 * @param  bool   $sent_to_admin Send to admin.
	 * @param  bool   $plain_text    Plain text or HTML.
	 *
	 * @return string                Payment instructions.
	 */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $sent_to_admin || ! in_array( $order->get_status(), array( 'processing', 'on-hold' ), true ) || $this->id !== $order->payment_method ) {
			return;
		}

		$email_type = $plain_text ? 'plain' : 'html';

		wc_get_template(
			'banking-ticket/emails/' . $email_type . '-instructions.php',
			array(
				'url' => $data['boleto_url'],
			),
			'woocommerce/pagarme/',
			WC_Pagarme::get_templates_path()
		);

	}

	/**
	 * IPN handler.
	 */
	public function ipn_handler() {
		$this->api->ipn_handler();
	}
}