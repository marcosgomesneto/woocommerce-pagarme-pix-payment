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

		WP::add_filter('woocommerce_payment_gateways', $base, 'add_gateway');
		WP::add_filter('plugin_action_links_'.\WC_PAGARME_PIX_PAYMENT_BASE_NAME, $base, 'plugin_action_links');
		WP::add_action('wp_ajax_wc_pagarme_pix_payment_check', $base, 'check_pix_payment');
		WP::add_action('wp_ajax_nopriv_wc_pagarme_pix_payment_check', $base, 'check_pix_payment');
	}

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