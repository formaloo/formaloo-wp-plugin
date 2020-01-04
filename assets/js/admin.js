/**
 * Formaloo plugin Saving process
 */
jQuery(document).ready(function() {

    jQuery(document).on('submit', '#formaloo-admin-form', function(e) {

        e.preventDefault();

        // We inject some extra fields required for the security
        jQuery(this).append('<input type="hidden" name="action" value="store_admin_data" />');
        jQuery(this).append('<input type="hidden" name="security" value="' + formaloo_exchanger._nonce + '" />');

        // We make our call
        jQuery.ajax({
            url: formaloo_exchanger.ajax_url,
            type: 'post',
            data: jQuery(this).serialize(),
            success: function(response) {
                location.reload();
            }
        });

    });


    jQuery(document).on('submit', '#formaloo-customize-form', function(e) {

        e.preventDefault();

        // We inject some extra fields required for the security
        jQuery(this).append('<input type="hidden" name="action" value="get_formaloo_shortcode" />');
        jQuery(this).append('<input type="hidden" name="security" value="' + formaloo_exchanger._nonce + '" />');

        // We make our call
        jQuery.ajax({
            url: formaloo_exchanger.ajax_url,
            type: 'post',
            data: jQuery(this).serialize(),
            success: function(response) {
                jQuery('.formaloo_clipboard_wrapper').removeClass('hidden');
                jQuery('#formaloo_shortcode_pre').html(response.data.output);
            }
        });

    });

    jQuery('.formaloo-get-excel-link').click(function(e) {

        e.preventDefault();

        // We inject some extra fields required for the security
        jQuery(this).append('<input type="hidden" name="security" value="' + formaloo_exchanger._nonce + '" />');
        console.log();

        jQuery.ajax({
            url: formaloo_exchanger.protocol + '://api.'+ formaloo_exchanger.endpoint_url +'/v1/forms/form/' + jQuery(this).data('form-slug') + '/excel/',
            type: 'get',
            headers: {
                'x-api-key': formaloo_exchanger.x_api_key,
                'Authorization': 'Token ' + formaloo_exchanger.private_key
            },
            success: function(data) {
                window.open(data['data']['form']['excel'], "_self");
            },
            error: function(error) {
                console.log(error);
            }
        });

    });

});