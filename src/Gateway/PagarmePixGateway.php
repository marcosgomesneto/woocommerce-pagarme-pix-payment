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

		// Set the API.
		$this->api = new PagarmeApi( $this );

		// Active logs.
		if ( 'yes' === $this->debug ) {
			$this->log = new WC_Logger();
		}

		if( isset( $_POST['pagarmeconfirm'] ) 
			&& isset( $_GET['page'] ) 
			&& $_GET['page'] == 'wc-settings'
			&& isset( $_GET['section'] )
			&& $_GET['section'] == 'wc_pagarme_pix_payment_geteway'
		){
			$update_settings = get_option($this->get_option_key(), []);
			$update_settings['read_notice'] = true;
			$this->read_notice = true;
			update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $update_settings ), 'yes' );
		}

		// Actions.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		add_action( 'woocommerce_order_details_before_order_table', array( $this, 'order_view_page' ) );
		add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		add_action( 'woocommerce_api_' . $this->id, array( $this, 'ipn_handler' ) );
		add_action( 'woocommerce_init', array( $this, 'init' ) );
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
				$title 				= filter_input( INPUT_POST, $this->get_field_name('title'), FILTER_SANITIZE_STRING );
				$api_key 			= filter_input( INPUT_POST, $this->get_field_name('api_key'), FILTER_SANITIZE_STRING );
				$api_version 		= filter_input( INPUT_POST, $this->get_field_name('api_version'), FILTER_SANITIZE_STRING );
				$encryption_key		= filter_input( INPUT_POST, $this->get_field_name('encryption_key'), FILTER_SANITIZE_STRING );
				$debug				= filter_input( INPUT_POST, $this->get_field_name('debug'), FILTER_SANITIZE_STRING );
				$after_paid_status	= filter_input( INPUT_POST, $this->get_field_name('after_paid_status'), FILTER_SANITIZE_STRING );

				if( empty($api_key) || empty($encryption_key) || empty($title) || empty($api_version) ){
					WC_Admin_Settings::add_error( __('É preciso preencher a todos os campos', \WC_PAGARME_PIX_PAYMENT_DIR_NAME) ); 
					return;
				}
				
				$update_settings['api_key'] 		= $api_key;
				$update_settings['api_version'] 	= $api_version;
				$update_settings['title'] 			= $title;
				$update_settings['encryption_key'] 	= $encryption_key;
				$update_settings['debug'] 			= isset($debug) ? 'yes' : 'no';
				$update_settings['after_paid_status'] = $after_paid_status;
			break;
			case 'customize':
				$checkout_message 		= filter_input( INPUT_POST, $this->get_field_name('checkout_message') ); //Liberar HTML
				$order_recived_message 	= filter_input( INPUT_POST, $this->get_field_name('order_recived_message') );
				$thank_you_message 		= filter_input( INPUT_POST, $this->get_field_name('thank_you_message') );
				$pix_icon_color 		= filter_input( INPUT_POST, $this->get_field_name('pix_icon_color'), FILTER_SANITIZE_STRING );

				if( empty($checkout_message) || empty($order_recived_message) || empty($thank_you_message) ){
					//WC_Admin_Settings::add_error( __('É preciso preencher a todos os campos', \WC_PAGARME_PIX_PAYMENT_DIR_NAME) ); 
				}
				
				$update_settings['checkout_message'] 		= $checkout_message;
				$update_settings['order_recived_message'] 	= $order_recived_message;
				$update_settings['thank_you_message'] 		= $thank_you_message;		
				$update_settings['pix_icon_color'] 			= $pix_icon_color;
				$update_settings['pix_icon'] 				= 'data:image/svg+xml;base64, ' . base64_encode( preg_replace('/#32BCAD/i', $pix_icon_color, '<svg viewBox="0 0 47.999999 47.999999" version="1.1" width="48" height="48" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg"><path d="m 37.212736,36.519836 a 6.8957697,6.8957697 0 0 1 -4.906519,-2.025174 l -7.087361,-7.09185 a 1.3471224,1.3471224 0 0 0 -1.862022,0 l -7.11131,7.111308 a 6.8987632,6.8987632 0 0 1 -4.906518,2.031162 H 9.9514702 l 8.9808148,8.980816 a 7.1846526,7.1846526 0 0 0 10.149819,0 l 8.998777,-9.000275 z" fill="#32BCAD"/><path d="m 11.340503,11.457373 a 6.8972665,6.8972665 0 0 1 4.906518,2.03116 l 7.11131,7.112807 a 1.318683,1.318683 0 0 0 1.862022,0 l 7.085864,-7.085865 a 6.8852919,6.8852919 0 0 1 4.906519,-2.032657 h 0.853176 L 29.067136,2.4840405 a 7.1756718,7.1756718 0 0 0 -10.149819,0 L 9.9514702,11.457373 Z" fill="#32BCAD"/><path d="M 45.509513,18.927915 40.071628,13.49003 a 1.0477618,1.0477618 0 0 1 -0.386174,0.07783 h -2.472718 a 4.8825701,4.8825701 0 0 0 -3.43217,1.421959 l -7.085862,7.081373 a 3.4037292,3.4037292 0 0 1 -4.809227,0 l -7.112806,-7.10831 A 4.8825701,4.8825701 0 0 0 11.340503,13.539424 H 8.3049864 a 1.0657234,1.0657234 0 0 1 -0.3652196,-0.07334 l -5.4723103,5.461833 a 7.1846526,7.1846526 0 0 0 0,10.149818 l 5.4603358,5.460331 a 1.0253097,1.0253097 0 0 1 0.3652196,-0.07335 h 3.0474911 a 4.884067,4.884067 0 0 0 3.432168,-1.423458 l 7.111309,-7.11131 c 1.285754,-1.284256 3.526467,-1.284256 4.810724,0 l 7.085862,7.084367 a 4.8825701,4.8825701 0 0 0 3.43217,1.421962 h 2.472718 a 1.0327938,1.0327938 0 0 1 0.386174,0.07783 l 5.437885,-5.437885 a 7.1756718,7.1756718 0 0 0 0,-10.149818" fill="#32BCAD"/></svg>'));

			break;
			case 'email':
				$email_instruction 	= filter_input( INPUT_POST, $this->get_field_name('email_instruction') ); //Allow HTML
				$email_instruction	= preg_replace('#<script(.*?)>(.*?)</script>#is', '', $email_instruction);
				$email_instruction  = preg_replace('/”/', '"', $email_instruction );
				$update_settings['email_instruction'] 		= $email_instruction;
			break;
			case 'advanced':
				$check_payment_interval = filter_input( INPUT_POST, $this->get_field_name('check_payment_interval'), FILTER_SANITIZE_NUMBER_INT );
				$auto_cancel			= filter_input( INPUT_POST, $this->get_field_name('auto_cancel'), FILTER_SANITIZE_STRING );
				$apply_discount			= filter_input( INPUT_POST, $this->get_field_name('apply_discount'), FILTER_SANITIZE_STRING );
				$apply_discount_amount	= filter_input( INPUT_POST, $this->get_field_name('apply_discount_amount'), FILTER_UNSAFE_RAW );
				$apply_discount_type	= filter_input( INPUT_POST, $this->get_field_name('apply_discount_type'), FILTER_UNSAFE_RAW );
				$expiration_days		= filter_input( INPUT_POST, $this->get_field_name('expiration_days'), FILTER_VALIDATE_INT );
				$expiration_hours		= filter_input( INPUT_POST, $this->get_field_name('expiration_hours'), FILTER_VALIDATE_INT );

				if( $expiration_hours < 0 ){
					WC_Admin_Settings::add_error( __('Horas que expiram não pode ser menor que 0', \WC_PAGARME_PIX_PAYMENT_DIR_NAME) ); 
					return;
				}

				if( $expiration_hours >= 24 ){
					WC_Admin_Settings::add_error( __('Horas que expiram não pode ser maior ou igual a 24 horas, se deseja mais que 24 horas, coloque no campo dias', \WC_PAGARME_PIX_PAYMENT_DIR_NAME) ); 
					return;
				}

				if( $expiration_hours <= 0 && $expiration_days <= 0 ){
					WC_Admin_Settings::add_error( __('Coloque ao menos um campo de hora ou dias maior que 0', \WC_PAGARME_PIX_PAYMENT_DIR_NAME) ); 
					return;
				}

				if( $expiration_days < 0 ){
					WC_Admin_Settings::add_error( __('Dias de expiração não pode ser menor que zero', \WC_PAGARME_PIX_PAYMENT_DIR_NAME) ); 
					return;
				}

				if( $expiration_days === '' ){
					WC_Admin_Settings::add_error( __('É preciso preencher o compo de dias da expiração', \WC_PAGARME_PIX_PAYMENT_DIR_NAME) ); 
					return;
				}

				if( $check_payment_interval <= 4 ){
					WC_Admin_Settings::add_error( __('O intervalo não pode ser menor que 5 segundos para evitar sobrecarga.', \WC_PAGARME_PIX_PAYMENT_DIR_NAME) ); 
					return;
				}

				if( isset($apply_discount) && ( empty($apply_discount_amount) || empty($apply_discount_type) ) ){
					WC_Admin_Settings::add_error( __('Ao ativar o desconto você precisa preencher os campos.', \WC_PAGARME_PIX_PAYMENT_DIR_NAME) ); 
					return;
				}

				if( isset($apply_discount) && $apply_discount_amount == '0' ){
					WC_Admin_Settings::add_error( __('O desconto não pode ser 0.', \WC_PAGARME_PIX_PAYMENT_DIR_NAME) ); 
					return;
				}

				if( isset($apply_discount) && !preg_match('/^[0-9]+([\,][0-9]{1,2})$/i', $apply_discount_amount)){
					WC_Admin_Settings::add_error( __('O desconto só poder ter números inteiros ou então separado por "," (vírgula) com até 2 casas decimais: ex: 10 ou 5,80', \WC_PAGARME_PIX_PAYMENT_DIR_NAME) ); 
					return;
				}

				$apply_discount_amount = preg_replace('/,/i', '.', $apply_discount_amount);


				$update_settings['check_payment_interval'] 	= $check_payment_interval;
				$update_settings['auto_cancel'] 			= isset($auto_cancel) ? 'yes' : 'no';
				$update_settings['apply_discount'] 			= isset($apply_discount) ? 'yes' : 'no';
				$update_settings['apply_discount_amount'] 	= $apply_discount_amount;
				$update_settings['apply_discount_type'] 	= $apply_discount_type;
				$update_settings['expiration_days'] 		= $expiration_days;
				$update_settings['expiration_hours'] 		= $expiration_hours;
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
			'title' => array(
				'title'             => __( 'Titulo', 'wc-pagarme-pix-payment' ),
				'type'              => 'text',
				'description'       => __( 'Esse titulo irá aparecer na opção de pagamento para o cliente', 'wc-pagarme-pix-payment' ),
				'default'           => '',
				'custom_attributes' => array(
					'required' => 'required',
				),
			),
			'api_version' => array(
				'title'             => __( 'Pagar.me Versão API', 'wc-pagarme-pix-payment' ),
				'type'              => 'select',
				'description'       => __( 'Insira a versão da API da pagar.me que você está usando', 'wc-pagarme-pix-payment' ),
				'default'           => 'v4',
				'options'           => array('v4' => 'v4 (01/09/2019)'),
				'custom_attributes' => array(
					'required' => 'required',
				),
			),
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
			'after_paid_status' => array(
				'title'             => __( 'Após pagamento mudar status para:', 'wc-pagarme-pix-payment' ),
				'type'              => 'select',
				'description'       => __( 'Defina o status que o pedido ficará após o pagamento ser confirmado.', 'wc-pagarme-pix-payment' ),
				'default'           => '',
				'options'			=> wc_get_order_statuses(),
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
		$this->api_version        		= $this->get_option( 'api_version' );
		$this->api_key        			= $this->get_option( 'api_key' );
		$this->encryption_key 			= $this->get_option( 'encryption_key' );
		$this->checkout_message 		= $this->get_option( 'checkout_message', "Ao finalizar a compra, iremos gerar o código Pix para pagamento.\r\n\r\nNosso sistema detecta automaticamente o pagamento sem precisar enviar comprovantes." );
		$this->order_recived_message 	= $this->get_option( 'order_recived_message', '<h4 style="text-align: center;">Faça o pagamento para finalizar!</h4><p style="text-align: center;">Escaneie o código QR ou copie o código abaixo para fazer o PIX.<br>O sistema vai detectar automáticamente quando fizer a transferência.</p><p style="text-align: center;"><strong>Podemos demorar até 5 minutos para detectarmos o pagamento.</strong></p><p style="text-align: center;">[copy_button]</p><p style="text-align: center;">[qr_code]</p>' );
		$this->thank_you_message 		= $this->get_option( 'thank_you_message', '<p style="text-align: center;">Sua transferência PIX foi confirmada!<br>O seu pedido já está sendo separado e logo será enviado para seu endereço.</p>' );
		$this->email_instruction		= $this->get_option( 'email_instruction', '<h4 style="text-align: center;">Faça o pagamento para finalizar a compra</h4><p style="text-align: center;">Escaneie o código abaixo</p><p style="text-align: center;">[qr_code]</p><h4 style="text-align: center;">ou</h4><p style="text-align: center;">[link text="Clique aqui"] para ver o código ou copiar</p>');
		$this->pix_icon_color 			= $this->get_option( 'pix_icon_color', '#32BCAD' );
		$this->icon						= apply_filters( 'woocommerce_gateway_icon', $this->get_option( 'pix_icon', 'data:image/svg+xml;base64, PHN2ZyB2aWV3Qm94PSIwIDAgNDcuOTk5OTk5IDQ3Ljk5OTk5OSIgdmVyc2lvbj0iMS4xIiB3aWR0aD0iNDgiIGhlaWdodD0iNDgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0ibSAzNy4yMTI3MzYsMzYuNTE5ODM2IGEgNi44OTU3Njk3LDYuODk1NzY5NyAwIDAgMSAtNC45MDY1MTksLTIuMDI1MTc0IGwgLTcuMDg3MzYxLC03LjA5MTg1IGEgMS4zNDcxMjI0LDEuMzQ3MTIyNCAwIDAgMCAtMS44NjIwMjIsMCBsIC03LjExMTMxLDcuMTExMzA4IGEgNi44OTg3NjMyLDYuODk4NzYzMiAwIDAgMSAtNC45MDY1MTgsMi4wMzExNjIgSCA5Ljk1MTQ3MDIgbCA4Ljk4MDgxNDgsOC45ODA4MTYgYSA3LjE4NDY1MjYsNy4xODQ2NTI2IDAgMCAwIDEwLjE0OTgxOSwwIGwgOC45OTg3NzcsLTkuMDAwMjc1IHoiIGZpbGw9IiMzMkJDQUQiLz48cGF0aCBkPSJtIDExLjM0MDUwMywxMS40NTczNzMgYSA2Ljg5NzI2NjUsNi44OTcyNjY1IDAgMCAxIDQuOTA2NTE4LDIuMDMxMTYgbCA3LjExMTMxLDcuMTEyODA3IGEgMS4zMTg2ODMsMS4zMTg2ODMgMCAwIDAgMS44NjIwMjIsMCBsIDcuMDg1ODY0LC03LjA4NTg2NSBhIDYuODg1MjkxOSw2Ljg4NTI5MTkgMCAwIDEgNC45MDY1MTksLTIuMDMyNjU3IGggMC44NTMxNzYgTCAyOS4wNjcxMzYsMi40ODQwNDA1IGEgNy4xNzU2NzE4LDcuMTc1NjcxOCAwIDAgMCAtMTAuMTQ5ODE5LDAgTCA5Ljk1MTQ3MDIsMTEuNDU3MzczIFoiIGZpbGw9IiMzMkJDQUQiLz48cGF0aCBkPSJNIDQ1LjUwOTUxMywxOC45Mjc5MTUgNDAuMDcxNjI4LDEzLjQ5MDAzIGEgMS4wNDc3NjE4LDEuMDQ3NzYxOCAwIDAgMSAtMC4zODYxNzQsMC4wNzc4MyBoIC0yLjQ3MjcxOCBhIDQuODgyNTcwMSw0Ljg4MjU3MDEgMCAwIDAgLTMuNDMyMTcsMS40MjE5NTkgbCAtNy4wODU4NjIsNy4wODEzNzMgYSAzLjQwMzcyOTIsMy40MDM3MjkyIDAgMCAxIC00LjgwOTIyNywwIGwgLTcuMTEyODA2LC03LjEwODMxIEEgNC44ODI1NzAxLDQuODgyNTcwMSAwIDAgMCAxMS4zNDA1MDMsMTMuNTM5NDI0IEggOC4zMDQ5ODY0IGEgMS4wNjU3MjM0LDEuMDY1NzIzNCAwIDAgMSAtMC4zNjUyMTk2LC0wLjA3MzM0IGwgLTUuNDcyMzEwMyw1LjQ2MTgzMyBhIDcuMTg0NjUyNiw3LjE4NDY1MjYgMCAwIDAgMCwxMC4xNDk4MTggbCA1LjQ2MDMzNTgsNS40NjAzMzEgYSAxLjAyNTMwOTcsMS4wMjUzMDk3IDAgMCAxIDAuMzY1MjE5NiwtMC4wNzMzNSBoIDMuMDQ3NDkxMSBhIDQuODg0MDY3LDQuODg0MDY3IDAgMCAwIDMuNDMyMTY4LC0xLjQyMzQ1OCBsIDcuMTExMzA5LC03LjExMTMxIGMgMS4yODU3NTQsLTEuMjg0MjU2IDMuNTI2NDY3LC0xLjI4NDI1NiA0LjgxMDcyNCwwIGwgNy4wODU4NjIsNy4wODQzNjcgYSA0Ljg4MjU3MDEsNC44ODI1NzAxIDAgMCAwIDMuNDMyMTcsMS40MjE5NjIgaCAyLjQ3MjcxOCBhIDEuMDMyNzkzOCwxLjAzMjc5MzggMCAwIDEgMC4zODYxNzQsMC4wNzc4MyBsIDUuNDM3ODg1LC01LjQzNzg4NSBhIDcuMTc1NjcxOCw3LjE3NTY3MTggMCAwIDAgMCwtMTAuMTQ5ODE4IiBmaWxsPSIjMzJCQ0FEIi8+PC9zdmc+' ), 'wc_pagarme_pix_payment' );
		$this->expiration_days 			= (int) $this->get_option( 'expiration_days', 15 );
		$this->expiration_hours 		= (int) $this->get_option( 'expiration_hours', 0 );
		$this->after_paid_status		= $this->get_option( 'after_paid_status', 'wc-processing' );
		$this->read_notice				= $this->get_option( 'read_notice', false );
		$this->check_payment_interval	= $this->get_option( 'check_payment_interval', '5' );
		$this->auto_cancel				= $this->get_option( 'auto_cancel', 'no' );
		$this->apply_discount			= $this->get_option( 'apply_discount', 'no' );
		$this->apply_discount_type		= $this->get_option( 'apply_discount_type', 'fixed' );
		$this->apply_discount_amount	= $this->get_option( 'apply_discount_amount', '0' );
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
			'email' => __( 'E-mail', 'wc-pagarme-pix-payment' ),
			'advanced' => __( 'Avançado', 'wc-pagarme-pix-payment' ),
			'donate' => __( 'Doação', 'wc-pagarme-pix-payment' )
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
		$expiration_date = $order->get_meta('_wc_pagarme_pix_payment_expiration_date');
		$qr_code_image = $order->get_meta('_wc_pagarme_pix_payment_qr_code_image');

		wc_get_template(
			'html-woocommerce-thank-you-page.php',
			[
				'qr_code' => $qr_code,
				'thank_you_message' => $this->thank_you_message,
				'order_recived_message' => $this->order_recived_message,
				'order' => $order,
				'qr_code_image' => $qr_code_image,
				'order_key' => $order->get_order_key(),
				'expiration_date' => $expiration_date
			],
			WC()->template_path().\WC_PAGARME_PIX_PAYMENT_DIR_NAME . '/',
			WC_PAGARME_PIX_PAYMENT_PLUGIN_PATH . 'templates/'
		);
	}

	/**
	 * Pix QR Code in Order View
	 * 
	 * @since 1.1.0
	 * @return void
	*/
	public function order_view_page( $order )
	{
		if( $order->get_status() == 'on-hold' &&
			$order->get_payment_method() == $this->id &&
			is_wc_endpoint_url( 'view-order' ) )
		{ 
			do_action( 'woocommerce_thankyou_'.$order->get_payment_method(), $order->get_id() ); 
		}
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
		if ( ! in_array( $order->get_status(), array( 'on-hold' ), true ) || 
			$this->id !== $order->payment_method ) {
			return;
		}

		$email_type = $plain_text ? 'plain' : 'html';

		$qr_code = $order->get_meta('_wc_pagarme_pix_payment_qr_code');
		$expiration_date = $order->get_meta('_wc_pagarme_pix_payment_expiration_date');
		$qr_code_image = $order->get_meta('_wc_pagarme_pix_payment_qr_code_image');

		wc_get_template(
			'email-new-order-instructions.php',
			[
				'qr_code' => $qr_code,
				'qr_code_image' => $qr_code_image,
				'email_instruction' => $this->email_instruction,
				'order_id' => $order->get_id(),
				'order_key' => $order->get_order_key(),
				'expiration_date' => $expiration_date,
				'order_url' => $order->get_checkout_order_received_url()
			],
			WC()->template_path() . \WC_PAGARME_PIX_PAYMENT_DIR_NAME.'/',
			WC_PAGARME_PIX_PAYMENT_PLUGIN_PATH . 'templates/emails/'
		);
	}

	/**
	 * Is Debug function
	 */
	public function is_debug() {
		return 'yes' === $this->debug ? true : false;
	}

	/**
	 * IPN handler.
	 */
	public function ipn_handler() {
		$this->api->ipn_handler();
	}
}