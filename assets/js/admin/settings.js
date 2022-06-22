jQuery(function($){
  const prefix = 'woocommerce_wc_pagarme_pix_payment_geteway_';
  $(`#${prefix}api_version`).on('change', function(e) {
    if($(this).val() == 'v5'){
      $(`#${prefix}api_key`).closest('tr').hide();
      $(`#${prefix}api_key`).removeAttr('required');
      $(`#${prefix}encryption_key`).closest('tr').hide();
      $(`#${prefix}encryption_key`).removeAttr('required');
      
      $(`#${prefix}secret_key`).closest('tr').show();
    }

    if($(this).val() == 'v4'){
      $(`#${prefix}api_key`).closest('tr').show();
      $(`#${prefix}api_key`).attr('required', 'required');
      $(`#${prefix}encryption_key`).closest('tr').show();
      $(`#${prefix}encryption_key`).attr('required', 'required');

      $(`#${prefix}secret_key`).closest('tr').hide();
    }
  })
  $(`#${prefix}api_version`).trigger('change');
})