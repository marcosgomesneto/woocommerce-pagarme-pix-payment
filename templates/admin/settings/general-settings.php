<?php
defined( 'ABSPATH' ) || exit;
?>
<h2><?php echo esc_html($current_tab_name); ?></h2>
<table class="form-table">
	<?php 
		$this->generate_settings_html(); 
	?>
</table>
