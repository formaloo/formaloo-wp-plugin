/**
 * Formaloo plugin Saving process
 */
jQuery(document).ready(function() {

    jQuery('#formaloo-customize-form :input').change(function() {
        handleShortcodeFormChange(jQuery(this).parents('#formaloo-customize-form'));
     });

    jQuery(document).on('submit', '#formaloo-customize-form', function(e) {

        e.preventDefault();

        handleShortcodeFormChange(this);
    });

    function handleShortcodeFormChange(toForm) {
        // We inject some extra fields required for the security
        jQuery(toForm).append('<input type="hidden" name="action" value="get_formaloo_shortcode" />');
        jQuery(toForm).append('<input type="hidden" name="security" value="' + formaloo_exchanger._nonce + '" />');

        jQuery('.formaloo-shortcode-post-row-inner').append('<span class="spinner is-active"></span>');

        jQuery.ajax({
            url: formaloo_exchanger.ajax_url,
            type: 'post',
            data: jQuery(toForm).serialize(),
            success: function(response) {
                jQuery('.formaloo_clipboard_wrapper').removeClass('formaloo-hidden');
                jQuery('input#formaloo_shortcode_pre').val(response.data.output);
                jQuery('.spinner').remove();
            },
            error: function(error) {
                jQuery('.spinner').remove();
            }
        });
    }

    jQuery('.formaloo-get-excel-link').click(function(e) {

        e.preventDefault();

        // We inject some extra fields required for the security
        jQuery(this).append('<input type="hidden" name="security" value="' + formaloo_exchanger._nonce + '" />');
        jQuery(this).parent().append('<span class="spinner is-active"></span>');

        jQuery.ajax({
            url: formaloo_exchanger.protocol + '://api.' + formaloo_exchanger.endpoint_url +'/v2/forms/form/' + jQuery(this).data('form-slug') + '/excel/',
            type: 'post',
            headers: {
                'x-api-key': formaloo_exchanger.api_key,
                'Authorization': 'JWT ' + formaloo_exchanger.api_token
            },
            success: function(result) {
                if (result['data']['form']['async_export']) {
                    window.scrollTo({top: 0, behavior: 'smooth'});
                    jQuery('.formaloo-excel-export-notice').remove();
                    jQuery('#my-forms-header').append("<div class='notice notice-info is-dismissible formaloo-excel-export-notice'> <p>" + formaloo_exchanger.async_excel_export_message + "</p> </div>");
                } else {
                    window.open(result['data']['form']['excel_file'], "_self");
                }
                jQuery('.spinner').remove();
            },
            error: function(error) {
                jQuery('.spinner').remove();
            }
        });

    });

});