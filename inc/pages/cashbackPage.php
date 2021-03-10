<?php
    class Formaloo_Cashback_Page extends Formaloo_Main_Class {
        /**
         * Outputs the Cashback Calculator layout containing the form with all its options
         *
         * @return void
         */
        public function cashbackPage() {

            $data = $this->getData();

            $not_ready = (empty($data['api_token']) || empty($data['api_key']));

            Formaloo_Activation_Class::syncHourly();

            ?>

            <div class="wrap">

                <form id="formaloo-cashback-form" class="postbox">

                    <div class="form-group inside">

                        <?php
                        /*
                        * --------------------------
                        * Cashback Calculator Settings
                        * --------------------------
                        */
                        ?>
                        
                        <div class="formaloo-loading-gif-wrapper">
                            <div class="formaloo-loader-wrapper">
                                <div class="formaloo-borders formaloo-first"></div>
                                <div class="formaloo-borders formaloo-middle"></div>
                                <div class="formaloo-borders formaloo-last"></div>
                            </div>
                        </div>

                        <div class="formaloo-api-settings-top-wrapper">
                            <img src="<?php echo FORMALOO_URL ?>assets/images/Formaloo_Logo.png" alt="formaloo-logo">
                            <h1 class="formaloo-heading">
                                <?php _e('Cashback Calculator for WooCommerce (Coming Soon)', 'formaloo-form-builder'); ?>
                            </h1>
                        </div>

                        <p class="notice notice-error formaloo-cashback-notice formaloo-cashback-error-notice"></p> 
                        <p class="notice notice-success formaloo-cashback-notice formaloo-cashback-success-notice is-dismissible"></p> 

                        <div class="formaloo-cashback-top-info">
                            <img src="<?php echo FORMALOO_URL ?>assets/images/woo-commerce-cashback.png" alt="formaloo-logo">
                            <h3 class="formaloo-heading">
                                <?php _e('Do you want to prepare cashback for your loyal customers?', 'formaloo-form-builder'); ?>
                            </h3>
                            
                        </div>

                        <!-- <hr> -->

                        <?php if ($not_ready): ?>
                            <p><?php echo __('You didn\'t activate the plugin.', 'formaloo-form-builder') ?> <a href="<?php echo admin_url( "admin.php?page=formaloo-settings-page" ) ?>"><strong><?php echo __('Get Started by visiting the Settings Page', 'formaloo-form-builder') ?></strong></a>.</p>
                        <?php else: ?>
                           <!-- <p>
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin gravida nisl ligula. Mauris rhoncus vitae orci ut ornare. Aenean interdum lacus sit amet dolor pellentesque malesuada sit amet ut dui. Nulla facilisi. Nullam in leo ac est efficitur pulvinar.
                            </p> -->
                        <?php endif; ?>

                        <!-- <input type="hidden" id="formaloo_feedback_widget_form_slug" name="formaloo_feedback_widget_form_slug" value="">
                        <input type="hidden" id="formaloo_feedback_widget_form_address" name="formaloo_feedback_widget_form_address" value="">
                        <input type="hidden" id="formaloo_feedback_widget_nps_field_slug" name="formaloo_feedback_widget_nps_field_slug" value="">
                        <input type="hidden" id="formaloo_feedback_widget_text_field_slug" name="formaloo_feedback_widget_text_field_slug" value=""> -->

                        <table class="formaloo-cashback-table form-table">
                            <tbody id="formaloo-woocommerce-connected">
                                <tr>
                                    <td>
                                        <h4>
                                            <?php _e( 'Choose Your Cashback Range:', 'formaloo-form-builder' ); ?>
                                        </h4>
                                    </td>
                                </tr>
                                <tr id="formaloo-cashback-range-tr">
                                    <td>
                                        <label><strong>$</strong></label>
                                    </td>
                                    <td>
                                        <input name="formaloo_cashback_low_range_percentage"
                                            id="formaloo_cashback_low_range_percentage"
                                            class="small"
                                            type="number"
                                            value=""
                                            placeholder="0"/>
                                    </td>
                                    <td>
                                        <label><strong><?php _e( 'to', 'formaloo-form-builder' ); ?></strong></label>
                                    </td>
                                    <td>
                                        <label><strong>$</strong></label>
                                    </td>
                                    <td>
                                        <input name="formaloo_cashback_high_range_percentage"
                                            id="formaloo_cashback_high_range_percentage"
                                            class="small"
                                            type="number"
                                            value=""
                                            placeholder="0"/>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <p class="formaloo-cashback-error-text"></p>
                                        <p class="formaloo-caption-text">
                                            <?php _e( 'I want to give my customers between $x to $y cashback at most.', 'formaloo-form-builder' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr id="formaloo-cashback-submit-row">
                                    <td>
                                        <p class="submit">
                                            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Calculate', 'formaloo-form-builder' ); ?>">
                                        </p>    
                                    </td>
                                    <!-- <td>
                                        <a href="#" target="_blank"><?php _e( 'How we calculate cashback?', 'formaloo-form-builder' ); ?></a>
                                    </td> -->
                                    <td></td>
                                </tr>
                                </tbody>
                                <?php if (!$not_ready): ?>
                                <tbody class="formaloo-cdp-sync-wrapper">
                                <tr>
                                    <td>
                                        <hr><br>
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

            <script src="<?php echo FORMALOO_URL ?>assets/js/handleTokenExpiration.js"></script>
            
            <script>
                jQuery(document).ready(function($){

                    $('.formaloo-cashback-error-text').hide();
                    $('.formaloo-cashback-success-notice').hide();
                    $('.formaloo-cashback-error-notice').hide();
                    $('#formaloo-woocommerce-not-connected').hide();
                    hideLoadingGif();

                    // disableCashbackTable();

                    $('#formaloo-cashback-form').on('submit', function(e){
                        e.preventDefault();
                        showLoadingGif();
                        $('.formaloo-cashback-error-text').hide();
                        const lowRangeValue = $('#formaloo_cashback_low_range_percentage').val();
                        const highRangeValue = $('#formaloo_cashback_high_range_percentage').val();
                        
                        if (lowRangeValue < 0 || highRangeValue < 0) {
                            $('.formaloo-cashback-error-text').show();
                            $('.formaloo-cashback-error-text').text("<?php _e('Please don\'t enter any negative value.', 'formaloo-form-builder'); ?>");
                        } else if (lowRangeValue == 0 || highRangeValue == 0) {
                            $('.formaloo-cashback-error-text').show();
                            $('.formaloo-cashback-error-text').text("<?php _e('Please enter values higher than 0.', 'formaloo-form-builder'); ?>");
                        } else if (lowRangeValue > highRangeValue) {
                            $('.formaloo-cashback-error-text').show();
                            $('.formaloo-cashback-error-text').text("<?php _e('Low range value shouldn\'t be bigger than high range value.', 'formaloo-form-builder'); ?>");
                        } else {
                            // Success!
                        }

                        hideLoadingGif();
                    });

                    <?php if (!$not_ready): ?>

                    checkBatchImportStatus(true, "<?php echo isset($data['last_customers_batch_import_slug']) ? $data['last_customers_batch_import_slug'] : ''; ?>");
                    checkBatchImportStatus(false, "<?php echo isset($data['last_orders_batch_import_slug']) ? $data['last_orders_batch_import_slug'] : ''; ?>");

                    <?php else: ?>

                        disableCashbackTable();

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
                                batchImportStatusHandler(checkingCustomersImport, 'queued');
                            }
                        });
                    }

                    function batchImportStatusHandler(checkingCustomersImport, status) {
                        var dashicon = '';
                        var divId = checkingCustomersImport ? "formaloo-customers-import-status" : "formaloo-orders-import-status";
                        var syncDate = checkingCustomersImport ? '<?php echo isset($data['last_customers_sync_date']) ? $data['last_customers_sync_date'] : date('Y-m-d H:i:s'); ?>' : '<?php echo isset($data['last_orders_sync_date']) ? $data['last_orders_sync_date'] : date('Y-m-d H:i:s'); ?>'

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

                        document.getElementById(divId).innerHTML = dashicon + ' ' + titleCase(status) + ' ' + getTimeAgo(syncDate);
                    }

                    function getTimeAgo(date) {

                        const MINUTE = 60,
                            HOUR = MINUTE * 60,
                            DAY = HOUR * 24,
                            WEEK = DAY * 7,
                            MONTH = DAY * 30,
                            YEAR = DAY * 365

                        const secondsAgo = Math.round((+new Date() - new Date(date)) / 1000)
                        let divisor = null
                        let unit = null

                        if (secondsAgo < MINUTE) {
                            return secondsAgo + " seconds ago"
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

                    function disableCashbackTable() {
                        $(".formaloo-cashback-table").addClass("formaloo-cashback-disabled-table");
                        $(".formaloo-cashback-table :input").attr("disabled", true);
                        $(".formaloo-cdp-sync-wrapper").addClass("formaloo-hidden");
                    }

                    function showSuccessMessage(successText) {
                        $('.formaloo-cashback-success-notice').show();
                        $('.formaloo-cashback-success-notice').text(successText);
                    }

                    function showGeneralErrors(errorText) {
                        $('.formaloo-cashback-error-notice').show();
                        $('.formaloo-cashback-error-notice').text(errorText);
                    }

                    function showLoadingGif() {
                        $('.formaloo-loading-gif-wrapper').show();
                    }

                    function hideLoadingGif() {
                        $('.formaloo-loading-gif-wrapper').hide();
                    }

                });
            </script>

            <?php

        }
    }