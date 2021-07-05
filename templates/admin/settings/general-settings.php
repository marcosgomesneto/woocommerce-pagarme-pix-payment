<?php
defined( 'ABSPATH' ) || exit;
?>
<h1>Pix via Pagarme</h1>

<?php echo wp_kses_post( wpautop( $this->method_description ) ); ?>

<table class="form-table">
	<?php $this->generate_settings_html(); ?>
</table>
