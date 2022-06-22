jQuery(function ( $ ) {
	'use strict';

  $('body').on("updated_checkout",function() {
    $('.payment_methods input[name=payment_method]').on('change', function ( e ) { 
      $('body').trigger( 'update_checkout' );
    });
  });
});