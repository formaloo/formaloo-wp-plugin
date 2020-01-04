/**
 * Formaloo plugin Saving process
 */
jQuery( document ).ready( function () {

  jQuery( document ).on( 'submit', '#formaloo-admin-form', function ( e ) {

      e.preventDefault();

      // We inject some extra fields required for the security
      jQuery(this).append('<input type="hidden" name="action" value="store_admin_data" />');
      jQuery(this).append('<input type="hidden" name="security" value="'+ formaloo_exchanger._nonce +'" />');

      // We make our call
      jQuery.ajax( {
          url: formaloo_exchanger.ajax_url,
          type: 'post',
          data: jQuery(this).serialize(),
          success: function (response) { 
              location.reload();
          }
      } );

  } );


  jQuery( document ).on( 'submit', '#formaloo-customize-form', function ( e ) {

    e.preventDefault();

    // We inject some extra fields required for the security
    jQuery(this).append('<input type="hidden" name="action" value="get_formaloo_shortcode" />');
    jQuery(this).append('<input type="hidden" name="security" value="'+ formaloo_exchanger._nonce +'" />');

    // We make our call
    jQuery.ajax( {
        url: formaloo_exchanger.ajax_url,
        type: 'post',
        data: jQuery(this).serialize(),
        success: function (response) { 
            //window.open("?page=formaloo&formaloo-get-shortcode=go","_self");
            jQuery('.formaloo_clipboard_wrapper').removeClass('hidden');
            jQuery('#formaloo_shortcode_pre').html(response.data.output);
        }
    } );

} );

} );