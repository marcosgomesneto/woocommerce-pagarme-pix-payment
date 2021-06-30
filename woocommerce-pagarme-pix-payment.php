<?php
/**
 * WooCommerce Pagarme Pix Payment
 *
 * @link              https://github.com/marcosgomesneto/woocommerce-pagarme-pix-payment
 * @since             1.1.0
 * @package           WC_Pagarme_Pix_Payment
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Pagarme Pix Payment
 * Plugin URI:        https://github.com/marcosgomesneto/woocommerce-pagarme-pix-payment
 * Description:       Receba pagamentos via PIX no WooCommerce com a Pagar-me de forma automática sem precisar de comprovantes de pagamento.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Marcos Gomes Neto
 * Author URI:        https://github.com/marcosgomesneto
 * Text Domain:       wc-pagarme-pix-payment
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined( 'ABSPATH' ) || exit;

//Define globals
define( 'WC_PAGARME_PIX_PAYMENT_PLUGIN_NAME', 'wc-pagarme-pix-payment' );
define( 'WC_PAGARME_PIX_PAYMENT_PLUGIN_VERSION', '1.1.10' );
define( 'WC_PAGARME_PIX_PAYMENT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WC_PAGARME_PIX_PAYMENT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

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