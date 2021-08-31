<?php
defined( 'ABSPATH' ) || exit;
?>
<style>
    .mgn-editor{
        height: 200px;
    }
    .mgn-info{
        font-weight: normal;
        color: #646970;
    }
</style>
<h2><?php echo esc_html($current_tab_name); ?></h2>
<table class="form-table">
    <tbody>
        <tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_html( $this->get_field_name('checkout_message') ); ?>">Mensagem nas opções de pagamento </label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span>Mensagem nas opções de pagamento</span></legend>
					<textarea rows="3" cols="20" class="input-text wide-input " type="textarea" name="<?php echo esc_html( $this->get_field_name('checkout_message') ); ?>" id="<?php echo esc_html( $this->get_field_name('checkout_message') ); ?>" style="" placeholder="" required="required"><?php echo $this->checkout_message; ?></textarea>
					<p class="description">Quando é selecionado o PIX como forma de pagamento antes de finalizar a compra.</p>
				</fieldset>
			</td>
		</tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_html( $this->get_field_name('order_recived_message') ); ?>">
                    <?php echo esc_html( __('Mensagem na tela do QR Code', 'wc-pagarme-pix-payment') ); ?>
                </label>
                <p class="mgn-info">
                    <?php echo __('Essa mensagem aparece na tela do QR Code, depois que o cliente finaliza o pedido.', 'wc-pagarme-pix-payment'); ?></p>
            </th>
            <td class="forminp">
                <fieldset>
                    <?php 
                        wp_editor( 
                            $this->order_recived_message,
                            "order_recived_message", 
                            [
                                'editor_class'  => 'mgn-editor',
                                'textarea_name' => esc_html( $this->get_field_name('order_recived_message') )
                            ] 
                        ); 
                    ?>
                </fieldset>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="thank_you_message">
                    <?php echo __( 'Mensagem de agradecimento pelo pagamento', 'wc-pagarme-pix-payment' ); ?>
                </label>
                <p class="mgn-info">
                    <?php echo __( 'Essa mensagem aparece quando o pagamento PIX é confirmado.', 'wc-pagarme-pix-payment' ); ?>    
                </p>
            </th>
            <td class="forminp">
                <fieldset>
                    <?php 
                        wp_editor( 
                            $this->thank_you_message, 
                            esc_html( $this->get_field_name('thank_you_message') ), 
                            [
                                'editor_class'  => 'mgn-editor',
                                'textarea_name' => esc_html( $this->get_field_name('thank_you_message') )
                            ] 
                        ); 
                    ?>
                </fieldset>
            </td>
        </tr>
    </tbody>
</table>
