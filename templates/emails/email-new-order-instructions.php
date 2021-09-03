<?php
defined( 'ABSPATH' ) || exit;

use chillerlan\QRCode\QRCode;

ob_start();
?>
    <img src="<?php echo (new QRCode)->render( esc_html($qr_code) ); ?>" />
<?php
$qr_code_html = ob_get_clean();

$email_instruction = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $email_instruction);

if( preg_match('/\[qr_code\]/i', $email_instruction) ){
    $email_instruction = preg_replace('/\[qr_code\]/i', $qr_code_html, $email_instruction, 1);
}

if( preg_match('/\[text_code\]/i', $email_instruction) ){
    $email_instruction = preg_replace('/\[text_code\]/i', $qr_code, $email_instruction, 1);
}

echo $email_instruction;

?>
