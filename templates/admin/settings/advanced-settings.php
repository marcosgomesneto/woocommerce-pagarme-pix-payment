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
    .d-flex{
        display: flex;
    }
    .items-center{
        align-items: center;
    }
    .mr-2{
        margin-right: 10px !important;
    }
</style>
<h2><?php echo esc_html($current_tab_name); ?></h2>
<table class="form-table">
    <tbody>
        <tr valign="top">
			<th scope="row" class="titledesc">
            <label for="<?php echo esc_html( $this->get_field_name('check_payment_interval') ); ?>">Intervalo de tempo para verificar pagamento em segundos</label>
			</th>
			<td class="forminp">
				<fieldset>
					<input 
                        class="input-text regular-input " 
                        type="number" 
                        name="<?php echo esc_html( $this->get_field_name('check_payment_interval') )?>" 
                        id="<?php echo esc_html( $this->get_field_name('check_payment_interval') )?>" 
                        value="<?php echo esc_html($this->check_payment_interval); ?>" 
                        placeholder="" 
                        required="required"/>
				</fieldset>
                <p class="description">O plugin faz requisições HTTP em um determinado intervalo de tempo para verificar se o pedido foi pago e mostrar a animação de concluído para o cliente sem ele precisar atualizar a pagina (isso só ocorre na pagina do QR Code para pagamento). Isso não influência na alteração do status para 'pago' do pedido, pois ela é instantânea. Isso só influência para o cliente.</p>
			</td>
		</tr>
        <tr valign="top">
			<th scope="row" class="titledesc">
            <label for="<?php echo esc_html( $this->get_field_name('expiration_days') ); ?>"><?php echo __( 'Dia e hora para expirar o Qr Code', 'wc-pagarme-pix-payment' );?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<div class="d-flex">
                        <input 
                            class="input-text regular-input mr-2" 
                            type="number" 
                            style="width: 200px; margin-bottom: 8px;"
                            name="<?php echo esc_html( $this->get_field_name('expiration_days') )?>" 
                            id="<?php echo esc_html( $this->get_field_name('expiration_days') )?>" 
                            value="<?php echo esc_html($this->expiration_days); ?>" 
                            placeholder="" 
                            required="required"/>
                            <div class="d-flex items-center"><strong>Dias</strong></div>
                    </div>
                    <div class="d-flex">
                        <input 
                            class="input-text regular-input mr-2" 
                            type="number" 
                            style="width: 200px;"
                            name="<?php echo esc_html( $this->get_field_name('expiration_hours') )?>" 
                            id="<?php echo esc_html( $this->get_field_name('expiration_hours') )?>" 
                            value="<?php echo esc_html($this->expiration_hours); ?>" 
                            placeholder="" 
                            required="required"/>
                            <div class="d-flex items-center"><strong>Horas</strong></div>
                    </div>
				</fieldset>
                <p class="description"><?php echo __( 'Em quantos dias ou horas que o Qr Code expirará, ex: para expirar em 2 horas, preencha 0 dias, e coloque 2 horas', 'wc-pagarme-pix-payment' ); ?></p>
			</td>
		</tr>
        <tr valign="top">
			<th scope="row" class="titledesc">
                <label for="<?php echo esc_html( $this->get_field_name('auto_cancel') ); ?>">Cancelar ao expirar</label>
			</th>
			<td class="forminp">
				<fieldset>
                    <label for="<?php echo esc_html( $this->get_field_name('auto_cancel') ); ?>">
					<input class="" type="checkbox" name="<?php echo esc_html( $this->get_field_name('auto_cancel') ); ?>" id="<?php echo esc_html( $this->get_field_name('auto_cancel') ); ?>" <?php echo $this->auto_cancel == 'yes' ? 'checked' : '' ; ?>>Cancelar pedidos automaticamente após expiração do QR Code</label>

				</fieldset>
                <p class="description">Quando o QR Code expirar, o pedido será cancelado automaticamente.</p>
			</td>
		</tr>
    </tbody>
</table>