jQuery(function ( $ ) {
	'use strict';

    $( document.body ).on( 'click', '.copy-qr-code', function () {
        /* Get the text field */
        var tempInput = document.createElement("input");
        var copyText = document.getElementById("pixQrCodeInput");
        tempInput.value = copyText.value;
        document.body.appendChild(tempInput);
        tempInput.select();
        tempInput.setSelectionRange(0, 99999); /* For mobile devices */
        document.execCommand("copy");
        document.body.removeChild(tempInput);

        $('.qrcode-copyed').show();
    });

    function checkPixPayment() {
        var checkInt = setInterval(function () {
            $.get( woocommerce_params.ajax_url, {
                'action': 'wc_pagarme_pix_payment_check',
                'key': $('input[name=wc_pagarme_pix_order_key]').val()
            } ).done( function(data) {
                if( data.paid == true ){
                    clearInterval(checkInt);
                    $('#watingPixPaymentBox').fadeOut( function() {
                        $('#successPixPaymentBox').fadeIn();
                    });
                    return;
                }
            });
        }, 5000);
    }   
	
    if( !$('#successPixPaymentBox').is(':visible') )
    checkPixPayment();
});