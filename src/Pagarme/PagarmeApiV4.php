<?php
namespace WCPagarmePixPayment\Pagarme;

use chillerlan\QRCode\QRCode;
/**
 * Pagarme API Integration class
 */
class PagarmeApiV4 extends PagarmeApi {
  protected $api_url = 'https://api.pagar.me/1/';
}