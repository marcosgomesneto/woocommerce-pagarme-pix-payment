jQuery(function ($) {
  "use strict";
  $(document.body).on("click", ".copy-qr-code", function () {
    /* Get the text field */
    var tempInput = document.createElement("input");
    var copyText = document.getElementById("pixQrCodeInput");
    tempInput.value = copyText.value;
    document.body.appendChild(tempInput);
    tempInput.select();
    tempInput.setSelectionRange(0, 99999); /* For mobile devices */
    document.execCommand("copy");
    document.body.removeChild(tempInput);

    $(".qrcode-copyed").show();
  });

  function checkPixPayment() {
    var interval = 5000;
    var reload = false;
    if (typeof window.wc_pagarme_pix_payment_geteway !== "undefined") {
      interval = window.wc_pagarme_pix_payment_geteway.checkInterval;
      reload = window.wc_pagarme_pix_payment_geteway.reload;
    }

    var checkInt = setInterval(function () {
      $.get(woocommerce_params.ajax_url, {
        action: "wc_pagarme_pix_payment_check",
        key: $("input[name=wc_pagarme_pix_order_key]").val(),
      }).done(function (data) {
        if (data.paid == true) {
          clearInterval(checkInt);
          $("#watingPixPaymentBox").fadeOut(function () {
            $("#successPixPaymentBox").fadeIn();
          });
          if (reload) window.location.reload();
          return;
        }
      });
    }, interval);
  }

  if (!$("#successPixPaymentBox").is(":visible")) checkPixPayment();
});
