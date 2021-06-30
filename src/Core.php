<?php

namespace WCPagarmePixPayment;

//Prevent direct file call
defined( 'ABSPATH' ) || exit;

/**
 * WC Pagarme Pix Payment
 *
 * @package WCPagarmePixPayment
 * @since   1.1.0
 * @version 1.1.0
 */
class Core {
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.2.0
	 * @var string $pluginName
	 */
	public $pluginName;

	/**
	 * The current version of the plugin.
	 *
	 * @since 1.1.0
	 * @var string $pluginVersion
	 */
	public $pluginVersion;

	/**
	 * Path to plugin directory.
	 * 
	 * @since 1.1.0
	 * @var string $pluginPath Without trailing slash.
	 */
	public $pluginPath;

	/**
	 * URL to plugin directory.
	 * 
	 * @since 1.1.0
	 * @var string $pluginUrl Without trailing slash.
	 */
	public $pluginUrl;

	/**
	 * URL to plugin assets directory.
	 * 
	 * @since 1.1.0
	 * @var string $assetsUrl Without trailing slash.
	 */
	public $assetsUrl;

	/**
	 * Plugin settings.
	 * 
	 * @since 1.1.0
	 * @var array
	 */
	protected $settings;

	/**
	 * Startup plugin.
	 * 
	 * @since 1.1.0
	 * @return void
	 */

    /**
     * Initialize the plugin public actions.
     */
    public function __construct() {
		$this->pluginUrl  = \WC_PAGARME_PIX_PAYMENT_PLUGIN_URL;
		$this->pluginPath = \WC_PAGARME_PIX_PAYMENT_PLUGIN_PATH;
		$this->assetsUrl  = $this->pluginUrl . '/assets';

		$this->pluginName    = \WC_PAGARME_PIX_PAYMENT_PLUGIN_NAME;
		$this->pluginVersion = \WC_PAGARME_PIX_PAYMENT_PLUGIN_VERSION;
    }

	/**
	 * Create submenu to pix receipts at Woocommerce menu.
	 * 
	 * @since 1.1.0
	 * @return string
	 */
	public function create_menu ()
	{
		add_submenu_page(
			'woocommerce',
			__('Comprovantes Pix - Pix por Piggly', \WC_PIGGLY_PIX_PLUGIN_NAME),
			__('Comprovantes Pix', \WC_PIGGLY_PIX_PLUGIN_NAME),
			'manage_woocommerce',
			\WC_PIGGLY_PIX_PLUGIN_NAME,
			[$this, 'page']
		);
	}
}