<?php
/**
 * @var \WCPagarmePixPayment\Gateway\PagarmePixGateway $this
 */
defined( 'ABSPATH' ) || exit;
?>
<style>

</style>
<h2><?php echo esc_html( $current_tab_name ); ?></h2>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label>Sinta-se à vontade de fazer uma doação via PIX pela chave aleatória</label>
			</th>
			<td class="forminp">
				<img width="300"
					src="<?php echo esc_html( \WC_PAGARME_PIX_PAYMENT_PLUGIN_URL ); ?>/assets/images/donate.svg" />
				<p>Chave aleatória: <strong>58a2463e-0e6b-4b00-aa7d-c62c6c4b712a</strong></p>
				<p>Agradeço a quem possa ajudar com qualquer valor.</p>
				<p><?php echo sprintf( 'Gostou bastante? Então <a href="%s" target="_blank">%s</a>', 'https://wordpress.org/plugins/wc-pagarme-pix-payment/#reviews', __( 'Avalie o Plugin	', \WC_PAGARME_PIX_PAYMENT_PLUGIN_NAME ) ); ?>
				</p>
				<p>Contato: <a target="_blank" href="mailto:gomes.php@gmail.com">gomes.php@gmail.com</a></p>
			</td>
		</tr>
	</tbody>
</table>