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
    .mgn-flex{
      display: flex;
      height: 35px;
      align-items: center;
    }
    .colpick.colpick_full{
        z-index: 999;
    }
</style>
<h2><?php echo esc_html($current_tab_name); ?></h2>
<table class="form-table">
    <tbody>
        <tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_html( $this->get_field_name('checkout_message') ); ?>">Icone </label>
                <p class="mgn-info">
                    <?php echo __('Icone que aparece na tela de opções de pagamento em finalizar compra', 'wc-pagarme-pix-payment'); ?>
                </p>
			</th>
			<td class="forminp">
				<fieldset>
                <svg
                    viewBox="0 0 47.999999 47.999999"
                    id="imagePixSvg"
                    version="1.1"
                    width="<?php echo esc_html($this->pix_icon_size);?>"
                    height="<?php echo esc_html($this->pix_icon_size);?>"
                    xmlns="http://www.w3.org/2000/svg"
                    xmlns:svg="http://www.w3.org/2000/svg">
                <path
                    d="m 37.212736,36.519836 a 6.8957697,6.8957697 0 0 1 -4.906519,-2.025174 l -7.087361,-7.09185 a 1.3471224,1.3471224 0 0 0 -1.862022,0 l -7.11131,7.111308 a 6.8987632,6.8987632 0 0 1 -4.906518,2.031162 H 9.9514702 l 8.9808148,8.980816 a 7.1846526,7.1846526 0 0 0 10.149819,0 l 8.998777,-9.000275 z"
                    fill="<?php echo esc_html($this->pix_icon_color);?>"
                    class="pppix-c1" />
                <path
                    d="m 11.340503,11.457373 a 6.8972665,6.8972665 0 0 1 4.906518,2.03116 l 7.11131,7.112807 a 1.318683,1.318683 0 0 0 1.862022,0 l 7.085864,-7.085865 a 6.8852919,6.8852919 0 0 1 4.906519,-2.032657 h 0.853176 L 29.067136,2.4840405 a 7.1756718,7.1756718 0 0 0 -10.149819,0 L 9.9514702,11.457373 Z"
                    fill="<?php echo esc_html($this->pix_icon_color);?>"
                    class="pppix-c1" />
                <path
                    d="M 45.509513,18.927915 40.071628,13.49003 a 1.0477618,1.0477618 0 0 1 -0.386174,0.07783 h -2.472718 a 4.8825701,4.8825701 0 0 0 -3.43217,1.421959 l -7.085862,7.081373 a 3.4037292,3.4037292 0 0 1 -4.809227,0 l -7.112806,-7.10831 A 4.8825701,4.8825701 0 0 0 11.340503,13.539424 H 8.3049864 a 1.0657234,1.0657234 0 0 1 -0.3652196,-0.07334 l -5.4723103,5.461833 a 7.1846526,7.1846526 0 0 0 0,10.149818 l 5.4603358,5.460331 a 1.0253097,1.0253097 0 0 1 0.3652196,-0.07335 h 3.0474911 a 4.884067,4.884067 0 0 0 3.432168,-1.423458 l 7.111309,-7.11131 c 1.285754,-1.284256 3.526467,-1.284256 4.810724,0 l 7.085862,7.084367 a 4.8825701,4.8825701 0 0 0 3.43217,1.421962 h 2.472718 a 1.0327938,1.0327938 0 0 1 0.386174,0.07783 l 5.437885,-5.437885 a 7.1756718,7.1756718 0 0 0 0,-10.149818"
                    fill="<?php echo esc_html($this->pix_icon_color);?>"
                    class="pppix-c1" />
                </svg>
                <div class="mgn-flex">
                    <input name="<?php echo esc_html( $this->get_field_name('pix_icon_size') ); ?>" id="rangeicon" type="range" min="4" max="128" value="<?php echo esc_html($this->pix_icon_size);?>">
                    <span id="rangeiconsize" style="margin-left: 10px;"><?php echo esc_html($this->pix_icon_size);?>px</span>
                </div>
                <div>
                    <button id="colorpicker" class="button">Mudar a cor</button>
                    <button id="colordefault" class="button">Cor padrão</button>
                </div>
                <input type="hidden" name="<?php echo esc_html( $this->get_field_name('pix_icon_color') ); ?>" value="<?php echo esc_html($this->pix_icon_color);?>">
                <script>
                    jQuery('#rangeicon').on('input',  function(value){
                      const rangeValue = jQuery(this).val();
                      jQuery('#imagePixSvg').width(rangeValue);
                      jQuery('#imagePixSvg').height(rangeValue);
                      jQuery('#rangeiconsize').html(rangeValue + 'px');
                    });
                    jQuery('#colorpicker').colpick({onChange: function(c1,c2){
                        jQuery(".pppix-c1").css('fill', '#' + c2);
                        jQuery("input[name=<?php echo esc_html( $this->get_field_name('pix_icon_color') ); ?>]").val("#" + c2);
                    }});
                    jQuery('#colordefault').on('click', function(e){
                        e.preventDefault();
                        jQuery(".pppix-c1").css('fill', '#32bcad');
                        jQuery("input[name=<?php echo esc_html( $this->get_field_name('pix_icon_color') ); ?>]").val('#32bcad');
                    });
                </script>
				</fieldset>
			</td>
		</tr>
        <tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_html( $this->get_field_name('checkout_message') ); ?>">Mensagem nas opções de pagamento </label>
                <p class="mgn-info">
                    <?php echo __('Quando é selecionado o PIX como forma de pagamento antes de finalizar a compra.', 'wc-pagarme-pix-payment'); ?>
                </p>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span>Mensagem nas opções de pagamento</span></legend>
                    <?php 
                        wp_editor( 
                            $this->checkout_message,
                            "checkout_message", 
                            [
                                'editor_class'  => 'mgn-editor',
                                'textarea_name' => esc_html( $this->get_field_name('checkout_message') )
                            ] 
                        ); 
                    ?>
				</fieldset>
			</td>
		</tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_html( $this->get_field_name('order_recived_message') ); ?>">
                    <?php echo esc_html( __('Mensagem na tela do QR Code', 'wc-pagarme-pix-payment') ); ?>
                </label>
                <p class="mgn-info">
                    <?php echo __('Essa mensagem aparece na tela do QR Code, depois que o cliente finaliza o pedido.<br><br><code>[qr_code]</code> para definir o local do código QR.<br><code>[copy_button]</code> para definir o local do botão para copiar o código.<br><code>[text_code]</code> para definir o local do código pix em texto corrido<br><code>[expiration_date]</code> para inserir a data que o código expira', 'wc-pagarme-pix-payment'); ?>
                </p>
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
