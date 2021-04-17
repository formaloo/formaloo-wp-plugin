<?php
    class Formaloo_Settings_Page extends Formaloo_Main_Class {
        /**
         * Outputs the Admin Dashboard layout containing the form with all its options
         *
         * @return void
         */
        public function settingsPage() {

            $data = $this->getData();

            $not_ready = (empty($data['api_secret']) || empty($data['api_key']));

            /**
             * Check if WooCommerce is active
             **/
            if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                Formaloo_Activation_Class::syncHourly();
            }

            ?>

            <div class="wrap">

                <form id="formaloo-admin-form" class="postbox">

                    <div class="form-group inside">

                        <?php
                        /*
                        * --------------------------
                        * API Settings
                        * --------------------------
                        */
                        ?>

                        <div class="formaloo-api-settings-top-wrapper">
                            <img src="<?php echo FORMALOO_URL ?>assets/images/Formaloo_Logo.png" alt="formaloo-logo">
                            <h1 class="formaloo-heading">
                                <?php _e('Setup Formaloo', 'formaloo-form-builder'); ?>
                            </h1>
                        </div>

                        <p class="notice notice-error formaloo-settings-page-notice"></p> 

                        <h3 class="formaloo-heading">
                            <?php echo $this->getStatusIcon(!$not_ready); ?>
                            <?php _e('Welcome to Formaloo!', 'formaloo-form-builder'); ?>
                        </h3>

                        <?php if ($not_ready): ?>
                            <p>
                                <?php echo __('To get started, we\'ll need to access your Formaloo account with an API Key & Secret Key which you can', 'formaloo-form-builder') .' <a class="button button-primary" href="'. FORMALOO_PROTOCOL . '://cdp.' . FORMALOO_ENDPOINT .'/redirect/current-organization/integrations/wordpress" target="_blank">'. __('Get them here', 'formaloo-form-builder') .'</a>. '. '<br>' . __('Paste your Formaloo API Key & API Secret, and click', 'formaloo-form-builder') .' <strong>'. __('Connect', 'formaloo-form-builder') .'</strong> '. __('to continue', 'formaloo-form-builder') .'.'; ?>
                            </p>
                        <?php else: ?>
                            <?php echo __('You can access your', 'formaloo-form-builder') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/" target="_blank">'. __('Formaloo dashboard here', 'formaloo-form-builder') .'</a>.'; ?>  
                        <?php endif; ?>
                        <?php echo $this->getStatusDiv(!$not_ready); ?>

                        <table class="form-table formaloo-api-settings-table">
                            <tbody class="formaloo-api-settings-keys-body">
                                <tr>
                                    <td scope="row">
                                        <label><?php _e( 'API Key', 'formaloo-form-builder' ); ?></label>
                                    </td>
                                    <td>
                                        <textarea 
                                            name="formaloo_api_key"
                                            form="formaloo-admin-form"
                                            id="formaloo_api_key"
                                            class="regular-text"
                                            rows="5"
                                        ><?php echo (isset($data['api_key'])) ? $data['api_key'] : ''; ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td scope="row">
                                        <label><?php _e( 'API Secret', 'formaloo-form-builder' ); ?></label>
                                    </td>
                                    <td>
                                        <textarea 
                                            name="formaloo_api_secret"
                                            form="formaloo-admin-form"
                                            id="formaloo_api_secret"
                                            class="regular-text"
                                            rows="6"
                                        ><?php echo (isset($data['api_secret'])) ? $data['api_secret'] : ''; ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input name="formaloo_api_token"
                                            id="formaloo_api_token"
                                            type="hidden"/>
                                    </td>
                                </tr>
                                <tr id="formaloo-settings-submit-row">
                                    <td>
                                        <button class="button button-primary formaloo-admin-save formaloo-button-black" type="submit">
                                            <?php ($not_ready) ? _e( 'Connect', 'formaloo-form-builder' ) : _e( 'Save', 'formaloo-form-builder' ) ?>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <?php if (!$not_ready && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )): ?>
                                <tbody class="formaloo-cdp-sync-wrapper">
                                    <tr>
                                        <td>
                                            <h3>
                                                <?php _e( 'WooCommerce + Formaloo CDP Sync Status', 'formaloo-form-builder' ); ?>
                                            </h3>
                                            <p>
                                                <?php echo __( 'We\'ll sync your customers and orders list hourly with your Formaloo CDP account which you can see in your', 'formaloo-form-builder') . ' ' . '<a href="'. FORMALOO_PROTOCOL . '://' . 'cdp.' . FORMALOO_ENDPOINT .'/" target="_blank">'. __('Formaloo CDP dashboard here', 'formaloo-form-builder') .'</a>.' . ' ' . __( 'Please refresh the page to see new changes.', 'formaloo-form-builder'); ?>
                                            </p>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong>
                                                <?php _e( 'Customers Import Status', 'formaloo-form-builder' ); ?>
                                            </strong>
                                        </td>
                                        <td id="formaloo-customers-import-status"></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong>
                                                <?php _e( 'Orders Import Status', 'formaloo-form-builder' ); ?>
                                            </strong>
                                        </td>
                                        <td id="formaloo-orders-import-status"></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                                <tbody id="formaloo-show-sync-errors-log"></tbody>
                            <?php endif; ?>
                        </table>
                    </div>

                </form>

                <a href="<?php echo esc_url( $this->getSupportUrl() ); ?>" target="_blank"><?php _e( 'Need Support? Feel free to contact us', 'formaloo-form-builder' ); ?></a>
            </div>

            <script>
                jQuery(document).ready(function($){

                    $('.formaloo-settings-page-notice').hide();

                    $('#formaloo-admin-form').on('submit', function(e){
                        e.preventDefault();

                        $('.formaloo-settings-page-notice').hide();

                        // We inject some extra fields required for the security
                        $('#formaloo-settings-submit-row').append('<td><span class="spinner is-active"></span></td>');
                        $(this).append('<input type="hidden" name="action" value="store_admin_data" />');
                        $(this).append('<input type="hidden" name="security" value="' + formaloo_exchanger._nonce + '" />');
                        $(this).append('<input type="hidden" name="formaloo_api_token_save_date" value="' + '<?php echo time(); ?>' + '" />');

                        const apiKey = document.getElementById('formaloo_api_key').value;
                        const secretKey = document.getElementById('formaloo_api_secret').value
                        const url = formaloo_exchanger.protocol + '://accounts.' + formaloo_exchanger.endpoint_url + '/v1/oauth2/authorization-token/';
                        // const url = 'https://staging.icas.formaloo.com' + '/v1/oauth2/authorization-token/';
                        $.ajax({
                            url: url,
                            type: 'POST',
                            dataType: 'json',
                            headers: {
                                'x-api-key': apiKey,
                                'Authorization': 'Basic ' + secretKey
                            },
                            contentType: 'application/x-www-form-urlencoded',
                            data: {'grant_type': 'client_credentials'},
                            success: function (result) {

                                document.getElementById('formaloo_api_token').value = result['authorization_token'];

                                $.ajax({
                                    url: formaloo_exchanger.ajax_url,
                                    type: 'post',
                                    data: $('#formaloo-admin-form').serialize(),
                                    success: function(response) {
                                        setTimeout(function() {
                                            $('.spinner').removeClass('is-active');
                                            // window.location.href = "?page=formaloo";
                                            location.reload();
                                            }, 1000);
                                    }
                                });
                            },
                            error: function (error) {
                                $.ajax({
                                    url: formaloo_exchanger.ajax_url,
                                    type: 'post',
                                    data: $('#formaloo-admin-form').serialize(),
                                    success: function(response) {
                                        setTimeout(function() {
                                            $('.spinner').removeClass('is-active');
                                            // window.location.href = "?page=formaloo";
                                            location.reload();
                                            }, 1000);
                                    }
                                });
                            }
                        });

                    });
                    
                    <?php if (!$not_ready && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )): ?>

                        <?php if (isset($data['last_customers_batch_import_slug'])): ?>
                            checkBatchImportStatus(true, "<?php echo isset($data['last_customers_batch_import_slug']) ? $data['last_customers_batch_import_slug'] : ''; ?>");
                        <?php else: ?>
                            batchImportStatusHandler(true, 'queued');
                        <?php endif; ?>

                        <?php if (isset($data['last_orders_batch_import_slug'])): ?>
                            checkBatchImportStatus(false, "<?php echo isset($data['last_orders_batch_import_slug']) ? $data['last_orders_batch_import_slug'] : ''; ?>");
                        <?php else: ?>
                            batchImportStatusHandler(false, 'queued');
                        <?php endif; ?>
                    
                    <?php endif; ?>

                    function checkBatchImportStatus(checkingCustomersImport, slug){

                        var endpointParam = checkingCustomersImport ? "customers" : "activities";
                        var resultParseItem = checkingCustomersImport ? "customer_batch" : "activity_batch";

                        $.ajax({
                            url: "<?php echo esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1.0/' ); ?>"+endpointParam+"/batch/"+slug+"/",
                            type: 'GET',
                            dataType: 'json',
                            headers: {
                                'x-api-key': '<?php echo isset($data['api_key']) ? $data['api_key'] : ''; ?>',
                                'Authorization': '<?php echo 'JWT ' . (isset($data['api_token']) ? $data['api_token'] : ''); ?>'
                            },
                            contentType: 'application/json; charset=utf-8',
                            success: function (result) {
                                batchImportStatusHandler(checkingCustomersImport, result['data'][resultParseItem]['status']);

                                if (!$.isEmptyObject(result['data'][resultParseItem]['error_log'])) {
                                    showSyncErrorsLog(checkingCustomersImport, result['data'][resultParseItem]['error_log']);
                                }
                            },
                            error: function (error) {
                                var errorText = error['responseJSON']['errors']['general_errors'][0];
                                // showGeneralErrors(errorText);
                                batchImportStatusHandler(checkingCustomersImport, 'queued');
                            }
                        });
                    }

                    function batchImportStatusHandler(checkingCustomersImport, status) {
                        var dashicon = '';
                        var divId = checkingCustomersImport ? "formaloo-customers-import-status" : "formaloo-orders-import-status";
                        var syncDate = checkingCustomersImport ? '<?php echo isset($data['last_customers_sync_date']) ? $data['last_customers_sync_date'] : date('Y-m-d H:i:s'); ?>' : '<?php echo isset($data['last_orders_sync_date']) ? $data['last_orders_sync_date'] : date('Y-m-d H:i:s'); ?>';

                        switch(status) {
                            case 'new':
                                dashicon = '<span class="dashicons dashicons-marker" style="color: yellow;"></span>';
                            case 'queued':
                            case 'in_progress':
                                dashicon = '<span class="dashicons dashicons-clock"></span>';
                                break;
                            case 'imported':
                                dashicon = '<span class="dashicons dashicons-yes-alt" style="color: yellowgreen;"></span>';
                                break;
                            case 'failed':
                                dashicon = '<span class="dashicons dashicons-no" style="color: crimson;"></span>';
                                break;
                            default:
                                dashicon = '<span class="dashicons dashicons-clock"></span>';
                        }

                        <?php
                            if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                                $orders = wc_get_orders( array('numberposts' => -1) );
                                $orders_total_count = count($orders);

                                $users_query = new WP_User_Query(
                                    array(
                                        'fields'  => array( 'user_registered' ),
                                        'role'    => 'customer',
                                    )
                                );
                                $customers = $users_query->get_results();
                                $customers_total_count = count( $customers );
                            }
                        ?>

                        var count = '';

                        if (status == 'imported') {
                            if (checkingCustomersImport) {
                                count = "<?php echo isset($customers_total_count) ? $customers_total_count : 0; ?> customers "; 
                            } else {
                                count = "<?php echo isset($orders_total_count) ? $orders_total_count : 0; ?> orders "; 
                            }
                        }

                        document.getElementById(divId).innerHTML = dashicon + ' ' + titleCase(status) + ' ' + count + getTimeAgo(syncDate);
                    }

                    function getTimeAgo(date) {

                        const MINUTE = 60,
                            HOUR = MINUTE * 60,
                            DAY = HOUR * 24,
                            WEEK = DAY * 7,
                            MONTH = DAY * 30,
                            YEAR = DAY * 365

                        const secondsAgo = Math.round((+new Date('<?php echo date('Y-m-d H:i:s'); ?>') - new Date(date)) / 1000)
                        let divisor = null
                        let unit = null

                        if (secondsAgo < MINUTE) {
                            if (secondsAgo == 0) {
                                return "just now"
                            } else {
                                return secondsAgo + " seconds ago"
                            }
                        } else if (secondsAgo < HOUR) {
                            [divisor, unit] = [MINUTE, 'minute']
                        } else if (secondsAgo < DAY) {
                            [divisor, unit] = [HOUR, 'hour']
                        } else if (secondsAgo < WEEK) {
                            [divisor, unit] = [DAY, 'day']
                        } else if (secondsAgo < MONTH) {
                            [divisor, unit] = [WEEK, 'week']
                        } else if (secondsAgo < YEAR) {
                            [divisor, unit] = [MONTH, 'month']
                        } else if (secondsAgo > YEAR) {
                            [divisor, unit] = [YEAR, 'year']
                        }

                        const count = Math.floor(secondsAgo / divisor)
                        return  `${count} ${unit}${(count > 1)?'s':''} ago`
                    }

                    function showSyncErrorsLog(checkingCustomersImport, errors) {

                        var tableIdColTitle = checkingCustomersImport ? "<?php _e('Customer #', 'formaloo-form-builder'); ?>" : "<?php _e('Order #', 'formaloo-form-builder'); ?>";

                        var col = [tableIdColTitle];
                        for (var customer in errors) {
                            for (var field in errors[customer]) {
                                if (col.indexOf(field) === -1) {
                                    col.push(field);
                                }
                            }
                        }

                        var table = document.createElement("table");


                        var tr = table.insertRow(-1);

                        for (var i = 0; i < col.length; i++) {
                            var th = document.createElement("th");
                            th.innerHTML = col[i];
                            tr.appendChild(th);
                        }

                        for (var customer in errors) {
                            tr = table.insertRow(-1);
                            var tabCell = tr.insertCell(-1);
                            tabCell.innerHTML = customer;
                            for (var field in errors[customer]) {
                                var tabCell = tr.insertCell(-1);
                                tabCell.innerHTML = errors[customer][field][0];
                            }
                        }

                        var divContainer = document.getElementById("formaloo-show-sync-errors-log");
                        divContainer.innerHTML = "";
                        divContainer.appendChild(table);

                    }

                    function titleCase(s) { 
                        return s.replace(/([a-z])([A-Z])/g, function (allMatches, firstMatch, secondMatch) {
                                            return firstMatch + " " + secondMatch;
                                    })
                                    .toLowerCase()
                                    .replace(/([ -_]|^)(.)/g, function (allMatches, firstMatch, secondMatch) {
                                            return (firstMatch ? " " : "") + secondMatch.toUpperCase();
                                        }
                                    ); 
                    }

                    function showGeneralErrors(errorText) {
                        $('.formaloo-settings-page-notice').show();
                        $('.formaloo-settings-page-notice').text(errorText);
                    }

                });


            </script>

            <?php

        }
    }