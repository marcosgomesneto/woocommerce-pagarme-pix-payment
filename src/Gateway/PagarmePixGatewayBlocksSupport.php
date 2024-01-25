<?php
namespace WCPagarmePixPayment\Gateway;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Pagarme Pix Payments Blocks integration
 *
 * @since 1.0.3
 */
final class PagarmePixGatewayBlocksSupport extends AbstractPaymentMethodType {

	/**
	 * The gateway instance.
	 *
	 * @var PagarmePixGateway
	 */
	private $gateway;

	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'wc_pagarme_pix_payment_geteway';

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_wc_pagarme_pix_payment_geteway_settings', [] );
		$this->gateway = PagarmePixGateway::getInstance();

	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return $this->gateway->is_available();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$script_path = 'assets/js/block/frontend/payment-block.js';
		$script_asset_path = \WC_PAGARME_PIX_PAYMENT_PLUGIN_PATH . 'assets/js/block/frontend/payment-block.asset.php';
		$script_asset = file_exists( $script_asset_path )
			? require( $script_asset_path )
			: array(
				'dependencies' => array(),
				'version' => '1.2.0'
			);
		$script_url = \WC_PAGARME_PIX_PAYMENT_PLUGIN_URL . $script_path;

		wp_register_script(
			'wc-pagarme-pix-payment-blocks',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		return [ 'wc-pagarme-pix-payment-blocks' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return [ 
			'title' => $this->get_setting( 'title' ),
			'description' => esc_html( $this->get_setting( 'checkout_message' ) ),
			'supports' => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] )
		];
	}
}
