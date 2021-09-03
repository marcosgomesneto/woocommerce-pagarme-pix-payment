<?php
namespace WCPagarmePixPayment;

use WCPagarmePixPayment\WP\Helper as WP;
use WCPagarmePixPayment\Gateway\BaseGateway;

//Prevent direct file call
defined( 'ABSPATH' ) || exit;

/**
 * WC Pagarme Pix Payment
 *
 * @package WCPagarmePixPayment
 * @since   1.0.0
 * @version 1.0.0
 */
class Core {
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.0.0
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
		//WP::show_errors();
		$this->pluginUrl  = \WC_PAGARME_PIX_PAYMENT_PLUGIN_URL;
		$this->pluginPath = \WC_PAGARME_PIX_PAYMENT_PLUGIN_PATH;
		$this->assetsUrl  = $this->pluginUrl . '/assets';

		$this->pluginName    = \WC_PAGARME_PIX_PAYMENT_PLUGIN_NAME;
		$this->pluginVersion = \WC_PAGARME_PIX_PAYMENT_PLUGIN_VERSION;

		WP::add_action('plugins_loaded', $this, 'after_load' );
    }

	/**
	 * Plugin loaded method.
	 * 
	 * @since 1.1.0
	 * @return void
	 */
	public function after_load ()
	{
		if ( !class_exists('WC_Payment_Gateway') )
		{
			// Cannot start plugin
			return;
		}

		// Startup gateway
		BaseGateway::init();

		// Metabox init
		//Metabox::init();

		// Display all notices...
		//WP::add_action('admin_notices', WP::class, 'display_notices' );

		WP::add_action('wp_enqueue_scripts', $this, 'enqueue_scripts');
	}

	public function enqueue_scripts() {
		if( ( is_wc_endpoint_url('order-received') && is_checkout() ) || is_wc_endpoint_url( 'view-order' ) )	{
			wp_enqueue_script( \WC_PAGARME_PIX_PAYMENT_PLUGIN_NAME, \WC_PAGARME_PIX_PAYMENT_PLUGIN_URL.'assets/js/public/checkout.js', array( 'jquery' ), \WC_PAGARME_PIX_PAYMENT_PLUGIN_VERSION );
		}
	}
}