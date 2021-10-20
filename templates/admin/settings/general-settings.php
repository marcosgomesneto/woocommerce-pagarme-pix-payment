<?php
defined( 'ABSPATH' ) || exit;
?>
<h2><?php echo esc_html($current_tab_name); ?></h2>
<?php if( !$this->read_notice ) : ?>
	<div class="notice inline notice-warning notice-alt">
		<p>Lembre-se de ativar o PIX dentro da Dashboard da pagar.me ou solicitar para um atendente ativar para você.
			<button type="submit" name="pagarmeconfirm" value="1" class="button"><?php _e('Entendi', 'wc-pagarme-pix-payment'); ?></button>
		</p>
	</div>
<?php endif; ?>
<table class="form-table">
	<?php 
		$this->generate_settings_html(); 
	?>
</table>
