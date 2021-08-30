<?php
namespace WCPagarmePixPayment\Gateway;

use WC_Payment_Gateway;
use WC_Logger;
use WCPagarmePixPayment\Pagarme\PagarmeApi;

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

		//$this->icon = apply_filters( 'woocommerce_gateway_icon', WC_PAGARME_PIX_PAYMENT_PLUGIN_NAME.'assets/'.$this->select_icon.'.png' );

		// Set the API.
		$this->api = new PagarmeApi( $this );

		// Active logs.
		if ( 'yes' === $this->debug ) {
			$this->log = new WC_Logger();
		}

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		//add_action( 'woocommerce_email_after_order_table', array( $this, 'email_instructions' ), 10, 3 );
		add_action( 'woocommerce_api_' . $this->id, array( $this, 'ipn_handler' ) );
	}

	/**
	 * Setup settings form fields.
	 * 
	 * @since 1.1.0
	 * @return void
	 */
	public function init_form_fields() { 
		$this->form_fields = array(
			'api_key' => array(
				'title'             => __( 'Pagar.me API Key', 'wc-pagarme-pix-payment' ),
				'type'              => 'text',
				'description'       => sprintf( __( 'Insira a Pagar.me API Key. Caso você não saiba você pode obter em %s.', 'wc-pagarme-pix-payment' ), '<a href="https://dashboard.pagar.me/">' . __( 'Pagar.me Dashboard > My Account page', 'wc-pagarme-pix-payment' ) . '</a>' ),
				'default'           => '',
				'custom_attributes' => array(
					'required' => 'required',
				),
			),
			'encryption_key' => array(
				'title'             => __( 'Pagar.me Encryption Key', 'wc-pagarme-pix-payment' ),
				'type'              => 'text',
				'description'       => sprintf( __( 'Insira a Pagar.me Encryption key. Caso você não saiba você pode obter em %s.', 'wc-pagarme-pix-payment' ), '<a href="https://dashboard.pagar.me/">' . __( 'Pagar.me Dashboard > My Account page', 'wc-pagarme-pix-payment' ) . '</a>' ),
				'default'           => '',
				'custom_attributes' => array(
					'required' => 'required',
				),
			),
			'checkout_message' => array(
				'title'             => __( 'Mensagem nas opções de pagamento', 'wc-pagarme-pix-payment' ),
				'type'              => 'textarea',
				'description'       => sprintf( __( 'Quando é selecionado o PIX como forma de pagamento antes de finalizar a compra.', 'wc-pagarme-pix-payment' ) ),
				'default'           => "Ao finalizar a compra, iremos gerar o código Pix para pagamento.\r\n\r\nNosso sistema detecta automaticamente o pagamento sem precisar enviar comprovantes.",
				'custom_attributes' => array(
					'required' => 'required',
				),
			),
			'order_recived_message' => array(
				'title'             => __( 'Mensagem na tela do QR Code', 'wc-pagarme-pix-payment' ),
				'type'              => 'textarea',
				'description'       => sprintf( __( 'Essa mensagem aparece após concluir a compra, na tela para pagamento PIX.', 'wc-pagarme-pix-payment' ) ),
				'default'           => "Escaneie o código QR ou copie o código abaixo para fazer o PIX.\r\nO sistema vai detectar automáticamente quando fizer a transferência.",
				'custom_attributes' => array(
					'required' => 'required',
				),
			),
			'thank_you_message' => array(
				'title'             => __( 'Mensagem de agradecimento pelo pagamento', 'wc-pagarme-pix-payment' ),
				'type'              => 'textarea',
				'description'       => sprintf( __( 'Essa mensagem aparece quando o pagamento PIX é confirmado automáticamente.', 'wc-pagarme-pix-payment' ) ),
				'default'           => "Sua transferência PIX foi confirmada!\r\nO seu pedido já está sendo separado e logo será enviado para seu endereço.",
				'custom_attributes' => array(
					'required' => 'required',
				),
			),
			'debug' => array(
				'title'       => __( 'Debug Log', 'wc-pagarme-pix-payment' ),
				'type'        => 'checkbox',
				'label'       => __( 'Ativar logs', 'wc-pagarme-pix-payment' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Veja os logs do plugin e mensagens de depuração em %s', 'wc-pagarme-pix-payment' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'wc-pagarme-pix-payment' ) . '</a>' ),
			)
		);
	}

	/**
	 * Set settings function
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setup_settings() {
		// Define user set variables.
		$this->title          = $this->get_option( 'title', 'Pix Instantâneo' );
		$this->description    = $this->get_option( 'description' );
		$this->debug          = $this->get_option( 'debug' );
		$this->async          = $this->get_option( 'async' );
		$this->api_key        = $this->get_option( 'api_key' );
		$this->encryption_key = $this->get_option( 'encryption_key' );
		$this->checkout_message = $this->get_option( 'checkout_message' );
	}

	/**
	 * Admin page.
	 */
	public function admin_options() {
		include WC_PAGARME_PIX_PAYMENT_PLUGIN_PATH . '/templates/admin/settings/general-settings.php';
	}

	/**
	 * Payment fields.
	 */
	public function payment_fields() {
		if ( $description = $this->get_description() ) {
			echo wp_kses_post( wpautop( wptexturize( $description ) ) );
		}

		wc_get_template(
			'html-woocommerce-instructions.php',
			[
				'description' => $this->get_description(),
				'checkout_message' => $this->checkout_message
			],
			WC()->template_path().\WC_PAGARME_PIX_PAYMENT_DIR_NAME.'/',
			WC_PAGARME_PIX_PAYMENT_PLUGIN_PATH.'templates/'
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
		/*global $woocommerce;

		// Load order
		$order = new WC_Order( $order_id );
		
		// Mark as on-hold (we're awaiting the payment)
		$order->update_status( 
			str_replace('wc-', '', $this->order_status), 
			__( 'Aguardando pagamento via Pix', \WC_PAGARME_PIX_PAYMENT_PLUGIN_NAME ) 
		);
 
		// Remove cart
		$woocommerce->cart->empty_cart();

		Debug::info(sprintf('Pagamento realizado via Pix para o pedido %s.', $order_id));

		do_action('wpgly_pix_after_process_payment', $order->get_id(), $order);		
		// Return thank-you redirect
		return array(
			'result' 	=> 'success',
			'redirect'	=> $this->get_return_url( $order )
		);*/
		return $this->api->process_regular_payment( $order_id );
	}

	/**
	 * Thank You page message.
	 *
	 * @param int $order_id Order ID.
	 */
	public function thankyou_page( $order_id ) {
		$order = wc_get_order( $order_id );
		$qr_code = $order->get_meta('_wc_pagarme_pix_payment_qr_code');

		wc_get_template(
			'html-woocommerce-thank-you-page.php',
			[
				'qr_code' => $qr_code
			],
			WC()->template_path().\WC_PAGARME_PIX_PAYMENT_DIR_NAME . '/',
			WC_PAGARME_PIX_PAYMENT_PLUGIN_PATH . 'templates/'
		);
	}

	public function is_debug() {
		return 'yes' === $this->debug ? true : false;
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