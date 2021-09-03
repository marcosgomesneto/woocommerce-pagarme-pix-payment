<?php
defined( 'ABSPATH' ) || exit;

printf('<p class="mgn-description">%s</p>', nl2br( wptexturize( $checkout_message ) )); 
?>