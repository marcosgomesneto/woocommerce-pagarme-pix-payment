<?php

/**
 * Pix Automático com Pagarme para WooCommerce
 *
 * @link              https://github.com/marcosgomesneto/woocommerce-pagarme-pix-payment
 * @since             1.1.0
 * @package           WC_Pagarme_Pix_Payment
 *
 * @wordpress-plugin
 * Plugin Name:       		Pix Automático com Pagarme para WooCommerce
 * Plugin URI:        		https://github.com/marcosgomesneto/woocommerce-pagarme-pix-payment
 * Description:       		Receba pagamentos via PIX no WooCommerce com a Pagar-me de forma automática sem precisar de comprovantes de pagamento.
 * Version:           		2.1.4
 * Requires at least: 		5.2
 * Requires PHP:      		7.0
 * WC requires at least:	3.0
 * WC tested up to:      	8.8.3
 * Author:            		Marcos Gomes Neto
 * Author URI:        		https://github.com/marcosgomesneto
 * Text Domain:       		wc-pagarme-pix-payment
 * License:           		GPLv2 or later
 * License URI:       		http://www.gnu.org/licenses/gpl-2.0.txt
 */
defined( 'ABSPATH' ) || exit;

//Define globals
define( 'WC_PAGARME_PIX_PAYMENT_PLUGIN_NAME', 'wc-pagarme-pix-payment' );
define( 'WC_PAGARME_PIX_PAYMENT_PLUGIN_VERSION', '2.1.4' );
define( 'WC_PAGARME_PIX_PAYMENT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WC_PAGARME_PIX_PAYMENT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_PAGARME_PIX_PAYMENT_BASE_NAME', plugin_basename( __FILE__ ) );
define( 'WC_PAGARME_PIX_PAYMENT_DIR_NAME', dirname( plugin_basename( __FILE__ ) ) );
define( 'WC_PAGARME_PIX_PAYMENT_FILE_NAME', __FILE__ );

function deactivate_wc_pagarme_pix_payment() {
	$timestamp = wp_next_scheduled( 'wc_pagarme_pix_payment_schedule' );
	wp_unschedule_event( $timestamp, 'wc_pagarme_pix_payment_schedule' );
}
register_deactivation_hook( __FILE__, 'deactivate_wc_pagarme_pix_payment' );

require WC_PAGARME_PIX_PAYMENT_PLUGIN_PATH . 'vendor/autoload.php';

/**
 * Global function-holder. Works similar to a singleton's instance().
 *
 * @since 1.0.0
 *
 * @return WCPagarmePixPayment\Core
 */
function wc_pagarme_pix_payment() {
	/**
	 * @var \WCPagarmePixPayment\Core
	 */
	static $core;

	if ( ! isset( $core ) ) {
		$core = new \WCPagarmePixPayment\Core();
	}

	return $core;
}

wc_pagarme_pix_payment();
