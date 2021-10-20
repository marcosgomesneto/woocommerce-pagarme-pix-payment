<?php
defined( 'ABSPATH' ) || exit;

ob_start();
?>
    <img src="<?php echo $qr_code_image; ?>" />
<?php
$qr_code_html = ob_get_clean();

$email_instruction = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $email_instruction);

if( preg_match('/\[qr_code\]/i', $email_instruction) ){
    $email_instruction = preg_replace('/\[qr_code\]/i', $qr_code_html, $email_instruction, 1);
}

if( preg_match('/\[(link)\s{0,}(text=[\"\”](.+)[\"\”])?\s{0,}\]/i', $email_instruction, $matches) ){
    $checkout_order_url = wc_get_checkout_url() . 'order-received/' . $order_id . '/?key=' . $order_key ;
    $email_instruction = preg_replace('/\[link.+\]/i', '<a href="' . $checkout_order_url . '">' . ( isset( $matches[3] ) ? $matches[3] : 'Clique aqui' ) . '</a>', $email_instruction, 1);
}

if( preg_match('/\[text_code\]/i', $email_instruction) ){
    $email_instruction = preg_replace('/\[text_code\]/i', $qr_code, $email_instruction, 1);
}

if( preg_match('/\[expiration_date\]/i', $email_instruction) ){
    $email_instruction = preg_replace('/\[expiration_date\]/i', date('d/m/Y H:i:s', strtotime($expiration_date) ), $email_instruction);
}

echo $email_instruction;

?>
