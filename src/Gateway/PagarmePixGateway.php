<?php
namespace WCPagarmePixPayment\Gateway;

use WC_Payment_Gateway;
use WC_Logger;
use WCPagarmePixPayment\Pagarme\PagarmeApi;
use WC_Admin_Settings;

/**
 * Pix GeteWay class
 */
class PagarmePixGateway extends WC_Payment_Gateway {

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
        global $current_section;

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
	 * Update admin options
	 * 
	 * @since 1.1.0
	 * @return void
	 */
	public function process_admin_options(){
		$current_tab = $this->get_current_tab();
		$update_settings = get_option($this->get_option_key(), []);

		if( !is_array($update_settings) )
			$update_settings = [];

		switch( $current_tab ){
			case 'general':
				$api_key 		= filter_input( INPUT_POST, $this->get_field_name('api_key'), FILTER_SANITIZE_STRING );
				$encryption_key	= filter_input( INPUT_POST, $this->get_field_name('encryption_key'), FILTER_SANITIZE_STRING );
				$debug			= filter_input( INPUT_POST, $this->get_field_name('debug'), FILTER_SANITIZE_STRING );

				if( empty($api_key) || empty($encryption_key) ){
					WC_Admin_Settings::add_error( __('É preciso preencher a todos os campos', \WC_PAGARME_PIX_PAYMENT_DIR_NAME) ); 
				}
				
				$update_settings['api_key'] 		= $api_key;
				$update_settings['encryption_key'] 	= $encryption_key;
				$update_settings['debug'] 			= isset($debug) ? 'yes' : 'no';
			break;
			case 'customize':
				$checkout_message 		= filter_input( INPUT_POST, $this->get_field_name('checkout_message'), FILTER_SANITIZE_STRING );
				$order_recived_message 	= filter_input( INPUT_POST, $this->get_field_name('order_recived_message') );
				$thank_you_message 		= filter_input( INPUT_POST, $this->get_field_name('thank_you_message') );

				if( empty($checkout_message) || empty($order_recived_message) || empty($thank_you_message) ){
					//WC_Admin_Settings::add_error( __('É preciso preencher a todos os campos', \WC_PAGARME_PIX_PAYMENT_DIR_NAME) ); 
				}
				
				$update_settings['checkout_message'] 		= $checkout_message;
				$update_settings['order_recived_message'] 	= $order_recived_message;
				$update_settings['thank_you_message'] 		= $thank_you_message;		
			break;
		}
		
		return update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $update_settings ), 'yes' );
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
		$this->title          			= $this->get_option( 'title', 'Pix Instantâneo' );
		$this->description    			= $this->get_option( 'description' );
		$this->debug          			= $this->get_option( 'debug' );
		$this->async          			= $this->get_option( 'async' );
		$this->api_key        			= $this->get_option( 'api_key' );
		$this->encryption_key 			= $this->get_option( 'encryption_key' );
		$this->checkout_message 		= $this->get_option( 'checkout_message', "Ao finalizar a compra, iremos gerar o código Pix para pagamento.\r\n\r\nNosso sistema detecta automaticamente o pagamento sem precisar enviar comprovantes." );
		$this->order_recived_message 	= $this->get_option( 'order_recived_message', '<h4 style="text-align: center;">Faça o pagamento para finalizar!</h4><p style="text-align: center;">Escaneie o código QR ou copie o código abaixo para fazer o PIX.<br>O sistema vai detectar automáticamente quando fizer a transferência.</p><p style="text-align: center;"><strong>Podemos demorar até 5 minutos para detectarmos o pagamento.</strong></p>' );
		$this->thank_you_message 		= $this->get_option( 'thank_you_message', '<p style="text-align: center;">Sua transferência PIX foi confirmada!<br>O seu pedido já está sendo separado e logo será enviado para seu endereço.</p>' );
	}

	/**
	 * Get name of fields
	 * 
	 * @since 1.1.0
	 * @return string
	 */
	protected function get_field_name ( string $field = '' )
	{ 
		return 'woocommerce_' . $this->id . '_' . $field;	
	}

	/**
	 * Get current tab
	 * 
	 * @since 1.1.0
	 * @return string
	 */
	protected function get_current_tab()
	{ 
		$current_tab = filter_input( INPUT_GET, 'mgn_tab', FILTER_SANITIZE_STRING );
		$current_tab = isset( $current_tab ) ? $current_tab : 'general';

		return $current_tab;
	}

	/**
	 * Get current tab name
	 * 
	 * @since 1.1.0
	 * @return string
	 */
	protected function get_current_tab_name()
	{ 
		$tabs = $this->get_tabs();
		
		return $tabs[$this->get_current_tab()];
	}

	/**
	 * Tabs
	 * 
	 * @since 1.1.0
	 * @return string
	 */
	protected function get_tabs()
	{ 
		return [
			'general' => __( 'Geral', 'wc-pagarme-pix-payment' ),
			'customize' => __( 'Customizar', 'wc-pagarme-pix-payment' ),
		];
	}

	/**
	 * Admin page settings.
	 */
	public function admin_options() {
		$baseUrl  = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $this->id );
		
		$current_tab = $this->get_current_tab();
		$current_tab_name = $this->get_current_tab_name();		

		$tab_template = \WC_PAGARME_PIX_PAYMENT_PLUGIN_PATH . 'templates/admin/settings/' . $current_tab . '-settings.php';
		
		require_once(\WC_PAGARME_PIX_PAYMENT_PLUGIN_PATH . 'templates/admin/settings/header-settings.php');

		if( file_exists($tab_template) )
			require_once($tab_template);
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
				'qr_code' => $qr_code,
				'thank_you_message' => $this->thank_you_message,
				'order_recived_message' => $this->order_recived_message
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