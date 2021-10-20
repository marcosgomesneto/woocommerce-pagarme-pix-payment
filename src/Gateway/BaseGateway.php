<?php
namespace WCPagarmePixPayment\Gateway;

use Exception;
//use WCPagarmePixPayment\WP\Debug;
use WCPagarmePixPayment\WP\Helper as WP;
use RuntimeException;

defined( 'ABSPATH' ) || exit;

/**
 * Load gateway if woocommerce is available.
 *
 * @since      1.1.0 
 */
class BaseGateway 
{
	/**
	 * Add all actions and filters to configure woocommerce
	 * gateways.
	 * 
	 * @since 1.2.0
	 * @return void
	 */
	public static function init () 
	{
		//global $Muscleboss;
		//$Muscleboss->showErrors();
		$base = new self();

		$base->expire_payment_scheduled();

		WP::add_filter('woocommerce_payment_gateways', $base, 'add_gateway');
		WP::add_filter('plugin_action_links_'.\WC_PAGARME_PIX_PAYMENT_BASE_NAME, $base, 'plugin_action_links');
		WP::add_action('wp_ajax_wc_pagarme_pix_payment_check', $base, 'check_pix_payment');
		WP::add_action('wp_ajax_nopriv_wc_pagarme_pix_payment_check', $base, 'check_pix_payment');
		WP::add_action('wp_loaded', $base, 'wp_loaded');	
	}

	public function wp_loaded()
	{
		WP::add_action('wc_pagarme_pix_payment_schedule', $this, 'check_expired_codes');
	}

	/**
	 * Check payment ajax request
	 *
	 * @return void
	 */
	public function check_pix_payment(){
		$order_id = wc_get_order_id_by_order_key($_GET['key']);
		$order = wc_get_order( $order_id );

		if( $order ){
			$paid = get_post_meta($order_id, '_wc_pagarme_pix_payment_paid', 'no') === 'yes' ? true : false;
			wp_send_json(['paid' => $paid]);
			die();
		}

		wp_die( esc_html__( 'Order not exists', 'wc-pagarme-pix-payment' ), '', array( 'response' => 401 ) );
	}

	/**
	 * Check qrcode payment expiration date
	 *
	 * @return void
	 */
	public function expire_payment_scheduled()
	{
		if ( ! wp_next_scheduled( 'wc_pagarme_pix_payment_schedule' ) ) {
			wp_schedule_event( time(), 'hourly', 'wc_pagarme_pix_payment_schedule' );
		}
	}

	/**
	 * Check expired qr codes
	 *
	 * @return void
	 */
	public function check_expired_codes()
	{
		$plugin_options = maybe_unserialize( get_option('woocommerce_wc_pagarme_pix_payment_geteway_settings', false) );

		if( !( $plugin_options && isset( $plugin_options['auto_cancel'] ) && $plugin_options['auto_cancel'] == 'yes' ) )
			return;

		$pix_orders = wc_get_orders(array(
			'limit'=>-1,
			'type'=> 'shop_order',
			'status'=> array( 'on-hold' ),
			'payment_method' => 'wc_pagarme_pix_payment_geteway'
			)
		);
		foreach($pix_orders as $order){
			$expiration_date = $order->get_meta('_wc_pagarme_pix_payment_expiration_date');
			$date_format = 'Y-m-d H:i:s';
			
			if( preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $expiration_date) ){
				$date_format = 'Y-m-d';
			}elseif( !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $expiration_date) ){
				continue;
			}
			$expiration_date = \DateTime::createFromFormat($date_format, $expiration_date);
			$current_date = \DateTime::createFromFormat($date_format, date($date_format, strtotime(current_time('mysql'))));
			if( $current_date >= $expiration_date ){
				$order->update_status('cancelled', 'PIX Pagarme: QR Code expirado, cancelamento automático do pedido.');
			}
		}
	}

	/**
	 * Add gateways to Woocommerce.
	 * 
	 * @since 1.1.0
	 * @return void
	 */
	public function add_gateway ( array $gateways )
	{
		array_push( $gateways, PagarmePixGateway::class );
		return $gateways;
	}

	/**
	 * Add links to plugin settings page.
	 * 
	 * @since 1.1.0
	 * @return void
	 */
	public function plugin_action_links ( $links )
	{
		$pluginLinks = array();

		$baseUrl = esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_pagarme_pix_payment_geteway' ) );

		$pluginLinks[] = sprintf('<a href="%s">%s</a>', $baseUrl, __('Configurações', \WC_PAGARME_PIX_PAYMENT_PLUGIN_NAME));
		$pluginLinks[] = sprintf('<a href="%s" target="_blank">%s</a>', 'https://wordpress.org/support/plugin/wc-pagarme-pix-payment/', __('Suporte', \WC_PAGARME_PIX_PAYMENT_PLUGIN_NAME));
		$pluginLinks[] = sprintf('<a href="%s" target="_blank">%s</a>', 'https://wordpress.org/plugins/wc-pagarme-pix-payment/#reviews', __('Avalie o Plugin	', \WC_PAGARME_PIX_PAYMENT_PLUGIN_NAME));

		return array_merge( $pluginLinks, $links );
	}
}