<?php
/**
 * @var \WCPagarmePixPayment\Gateway\PagarmePixGateway $this
 */
defined( 'ABSPATH' ) || exit;
?>
<style>
	.mgn-editor {
		height: 200px;
	}

	.mgn-info {
		font-weight: normal;
		color: #646970;
	}
</style>
<h2><?php echo esc_html( $current_tab_name ); ?></h2>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_html( $this->get_field_name( 'email_instruction' ) ); ?>">Customizar o e-mail
					de pedido recebido para pagamento PIX</label>
				<p class="mgn-info">
					<?php echo __( '<code>[qr_code]</code> para inserir a imagem do QR Code<br><br><code>[text_code]</code> para inserir o código QR Code em texto<br><br><code>[link text="Clique aqui"]</code> para inserir o link do site que conterá o botão copiar<br><br><code>[expiration_date]</code> para inserir a data que expira o código', 'wc-pagarme-pix-payment' ); ?>
				</p>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span>Mensagem pix dentro do e-mail de pedido recebido</span>
					</legend>
					<?php
					wp_editor(
						wptexturize( $this->email_instruction ),
						"email_instruction",
						[ 
							'editor_class' => 'mgn-editor',
							'textarea_name' => esc_html( $this->get_field_name( 'email_instruction' ) )
						]
					);
					?>
				</fieldset>
			</td>
		</tr>
	</tbody>
</table>