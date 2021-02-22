function handleTokenExpiration(error) {
    if (error['status'] == 401) {
        const url = 'https://staging.icas.formaloo.com/v1/oauth2/authorization-token/';
        // formaloo_exchanger.protocol + '://accounts.' + formaloo_exchanger.endpoint_url + '/v1/oauth2/authorization-token/'
        jQuery.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            headers: {
                'x-api-key': formaloo_exchanger.api_key,
                'Authorization': 'Basic ' + formaloo_exchanger.api_secret
            },
            contentType: 'application/x-www-form-urlencoded',
            data: {'grant_type': 'client_credentials'},
            success: function (result) {
                const auth_token = result['authorization_token'];

                jQuery.ajax({
                    url: formaloo_exchanger.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'update_auth_token',
                        newAuthToken : auth_token
                    },
                    success: function (msg) {
                        console.log(msg);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                        
                    },
                    error: function (error) {
                        console.log(error);
                    }
              });

            },
            error: function (error) {
                console.log(error);
            }
        });
    }
}