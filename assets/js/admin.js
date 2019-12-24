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
              alert(response);
          }
      } );

  } );


  jQuery( document ).on( 'submit', '#formaloo-customize-form', function ( e ) {

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
            window.open("?page=formaloo&formaloo-get-shortcode=go","_self");
            let params = new URLSearchParams(location.search)
            params.delete("formaloo-get-shortcode")
            history.replaceState(null, "", "?" + params + location.hash)
        }
    } );

} );

} );