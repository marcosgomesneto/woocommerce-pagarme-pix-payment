<?php

/**
 * @var \WC_Order $order
 */

defined('ABSPATH') || exit;

if ($order) {
    $paid = $order->get_meta('_wc_pagarme_pix_payment_paid') === 'yes' ? true : false;
}

ob_start();
?>

<button class="button copy-qr-code"><i class="fa fa-copy fa-lg pr-3"></i>Clique aqui para copiar o código</button>
<p class="text-success qrcode-copyed" style="text-align: center; display: none; margin-top: 15px;">Código copiado com
    sucesso!<br>Vá até o aplicativo do seu banco e cole o código.</p>

<?php
$copy_button_html = ob_get_clean();

ob_start();
?>
<div id="qr-code-container" style="text-align: center;">
    <div id="qr-code-loader" style="display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 16px;">
        <svg id="spinner" style="margin-bottom: 10px;" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2.25V4.75M12 18V22M5.75 12H2.25M21.25 12H19.75M18.4571 18.4571L17.75 17.75M18.6642 5.41579L17.25 6.83M4.92157 19.0784L7.75 16.25M5.12868 5.20868L7.25 7.33" stroke="#1E2328" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        Carregando QR Code...
    </div>
    <img id="qr-code-image" src="" alt="QR Code" style="display:none;" />
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const qrCodeImage = document.getElementById("qr-code-image");
        const loader = document.getElementById("qr-code-loader");
        const qrCodeUrl = "<?php echo esc_js($qr_code_image); ?>";
        const maxAttempts = 15;
        let attemptCount = 0;

        function checkImage(url, callback) {
            const img = new Image();
            img.onload = () => {
                console.log("QR Code image loaded successfully.");
                callback(true);
            };
            img.onerror = () => {
                console.log("Failed to load QR Code image.");
                callback(false);
            };
            img.src = url;
        }

        function loadQrCode() {
            if (attemptCount >= maxAttempts) {
                console.log("Max attempts reached. QR Code image could not be loaded.");
                loader.innerText = "Falha ao carregar o QR Code. Por favor, tente novamente mais tarde.";
                return;
            }

            console.log("Attempting to load QR Code image... Attempt #" + (attemptCount + 1));
            checkImage(qrCodeUrl, function(isAvailable) {
                if (isAvailable) {
                    qrCodeImage.src = qrCodeUrl;
                    qrCodeImage.style.display = "block";
                    loader.style.display = "none";
                    console.log("QR Code image displayed.");
                } else {
                    console.log("QR Code image not available, retrying in 3 seconds...");
                    attemptCount++;
                    setTimeout(loadQrCode, 3000); // Tenta novamente após 3 segundos
                }
            });
        }

        loadQrCode();
    });
</script>
<?php
$qr_code_html = ob_get_clean();

?>
<style>
    .qrcode-copyed {
        box-shadow: 2px 2px 3px #e1e1e1;
        border-radius: 5px;
        width: 320px;
        border: 1px solid #dadada;
        margin: 0 auto;
        padding: 10px;
    }

    .text-center {
        text-align: center;
    }

    @-webkit-keyframes scaleAnimation {
        0% {
            opacity: 0;
            transform: scale(1.5);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes scaleAnimation {
        0% {
            opacity: 0;
            transform: scale(1.5);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    @-webkit-keyframes drawCircle {
        0% {
            stroke-dashoffset: 151px;
        }

        100% {
            stroke-dashoffset: 0;
        }
    }

    @keyframes drawCircle {
        0% {
            stroke-dashoffset: 151px;
        }

        100% {
            stroke-dashoffset: 0;
        }
    }

    @-webkit-keyframes drawCheck {
        0% {
            stroke-dashoffset: 36px;
        }

        100% {
            stroke-dashoffset: 0;
        }
    }

    @keyframes drawCheck {
        0% {
            stroke-dashoffset: 36px;
        }

        100% {
            stroke-dashoffset: 0;
        }
    }

    @-webkit-keyframes fadeOut {
        0% {
            opacity: 1;
        }

        100% {
            opacity: 0;
        }
    }

    @keyframes fadeOut {
        0% {
            opacity: 1;
        }

        100% {
            opacity: 0;
        }
    }

    @-webkit-keyframes fadeIn {
        0% {
            opacity: 0;
        }

        100% {
            opacity: 1;
        }
    }

    @keyframes fadeIn {
        0% {
            opacity: 0;
        }

        100% {
            opacity: 1;
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    #successAnimationCircle {
        stroke-dasharray: 151px 151px;
        stroke: #007bff;
    }

    #successAnimationCheck {
        stroke-dasharray: 36px 36px;
        stroke: #007bff;
    }

    #successAnimationResult {
        fill: #007bff;
        opacity: 0;
    }

    #successAnimation.animated {
        -webkit-animation: 1s ease-out 0s 1 both scaleAnimation;
        animation: 1s ease-out 0s 1 both scaleAnimation;
    }

    #successAnimation.animated #successAnimationCircle {
        -webkit-animation: 1s cubic-bezier(0.77, 0, 0.175, 1) 0s 1 both drawCircle, 0.3s linear 0.9s 1 both fadeOut;
        animation: 1s cubic-bezier(0.77, 0, 0.175, 1) 0s 1 both drawCircle, 0.3s linear 0.9s 1 both fadeOut;
    }

    #successAnimation.animated #successAnimationCheck {
        -webkit-animation: 1s cubic-bezier(0.77, 0, 0.175, 1) 0s 1 both drawCheck, 0.3s linear 0.9s 1 both fadeOut;
        animation: 1s cubic-bezier(0.77, 0, 0.175, 1) 0s 1 both drawCheck, 0.3s linear 0.9s 1 both fadeOut;
    }

    #successAnimation.animated #successAnimationResult {
        -webkit-animation: 0.3s linear 0.9s both fadeIn;
        animation: 0.3s linear 0.9s both fadeIn;
    }

    #spinner {
        -webkit-animation: spin 1s linear infinite;
        animation: spin 1s linear infinite;
    }
</style>
<div class="text-center">
    <div id="successPixPaymentBox" style="display: <?php echo $paid ? 'block' : 'none'; ?>;">
        <h4>Obrigado pelo pagamento!</h4>
        <svg id="successAnimation" class="animated" xmlns="http://www.w3.org/2000/svg" width="180" height="180"
            viewBox="0 0 70 70">
            <path id="successAnimationResult" fill="#D8D8D8"
                d="M35,60 C21.1928813,60 10,48.8071187 10,35 C10,21.1928813 21.1928813,10 35,10 C48.8071187,10 60,21.1928813 60,35 C60,48.8071187 48.8071187,60 35,60 Z M23.6332378,33.2260427 L22.3667622,34.7739573 L34.1433655,44.40936 L47.776114,27.6305926 L46.223886,26.3694074 L33.8566345,41.59064 L23.6332378,33.2260427 Z" />
            <circle id="successAnimationCircle" cx="35" cy="35" r="24" stroke="#979797" stroke-width="2"
                stroke-linecap="round" fill="transparent" />
            <polyline id="successAnimationCheck" stroke="#979797" stroke-width="2" points="23 34 34 43 47 27"
                fill="transparent" />
        </svg>
        <?php echo nl2br($thank_you_message); ?>
    </div>
    <div id="watingPixPaymentBox" style="display: <?php echo $paid ? 'none' : 'block'; ?>;">
        <?php
        if (preg_match('/\[copy_button\]/i', $order_recived_message)) {
            $order_recived_message = preg_replace('/\[copy_button\]/i', $copy_button_html, $order_recived_message, 1);
        } else {
            $order_recived_message .= sprintf('<p>%s</p>', $copy_button_html);
        }

        if (preg_match('/\[qr_code\]/i', $order_recived_message)) {
            $order_recived_message = preg_replace('/\[qr_code\]/i', $qr_code_html, $order_recived_message, 1);
        } else {
            $order_recived_message .= sprintf('<p>%s</p>', $qr_code_html);
        }

        if (preg_match('/\[text_code\]/i', $order_recived_message)) {
            $order_recived_message = preg_replace('/\[text_code\]/i', $qr_code, $order_recived_message, 1);
        }

        if (preg_match('/\[expiration_date\]/i', $order_recived_message)) {
            $order_recived_message = preg_replace('/\[expiration_date\]/i', date('d/m/Y H:i:s', strtotime($expiration_date)), $order_recived_message, 1);
        }

        echo $order_recived_message;
        ?>

        <div><input type="hidden" value="<?php echo esc_html($qr_code); ?>" id="pixQrCodeInput"></div>
        <input type="hidden" name="wc_pagarme_pix_order_key"
            value="<?php echo esc_html(sanitize_text_field($order_key)); ?>" />
    </div>
</div>