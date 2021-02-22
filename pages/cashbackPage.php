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
                                <?php _e('Cashback Calculator for WooCommerce', 'formaloo-form-builder'); ?>
                            </h1>
                        </div>

                        <p class="notice notice-error formaloo-cashback-notice formaloo-cashback-error-notice"></p> 
                        <p class="notice notice-success formaloo-cashback-notice formaloo-cashback-success-notice is-dismissible"></p> 

                        <div class="formaloo-cashback-top-info">
                            <img src="<?php echo FORMALOO_URL ?>assets/images/woo-commerce-cashback.png" alt="formaloo-logo">
                            <h3 class="formaloo-heading">
                                <?php _e('Do you want to prepare cashback for your loyal customers?', 'formaloo-form-builder'); ?>
                            </h3>
                            <p>
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin gravida nisl ligula. Mauris rhoncus vitae orci ut ornare. Aenean interdum lacus sit amet dolor pellentesque malesuada sit amet ut dui. Nulla facilisi. Nullam in leo ac est efficitur pulvinar.
                            </p>
                        </div>

                        <?php if ($not_ready): ?>
                            <p>
                                <?php echo __('To get started, we\'ll need to access your Formaloo account with an', 'formaloo-form-builder') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/profile/" target="_blank">'. __('API Key & API Token', 'formaloo-form-builder') .'</a>. '. __('Paste your Formaloo API Key & API Token, and click', 'formaloo-form-builder') .' <strong>'. __('Connect', 'formaloo-form-builder') .'</strong> '. __('to continue', 'formaloo-form-builder') .'.'; ?>
                            </p>
                        <?php else: ?>
                            <?php // echo __('You can access your', 'formaloo-form-builder') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/" target="_blank">'. __('Formaloo dashboard here', 'formaloo-form-builder') .'</a>.'; ?>  
                        <?php endif; ?>
                        <?php echo $this->getStatusDiv(!$not_ready); ?>

                        <!-- <input type="hidden" id="formaloo_feedback_widget_form_slug" name="formaloo_feedback_widget_form_slug" value="">
                        <input type="hidden" id="formaloo_feedback_widget_form_address" name="formaloo_feedback_widget_form_address" value="">
                        <input type="hidden" id="formaloo_feedback_widget_nps_field_slug" name="formaloo_feedback_widget_nps_field_slug" value="">
                        <input type="hidden" id="formaloo_feedback_widget_text_field_slug" name="formaloo_feedback_widget_text_field_slug" value=""> -->

                        <hr>

                        <table class="formaloo-cashback-table form-table">
                            <tbody id="formaloo-woocommerce-not-connected">
                                <tr>
                                    <td>
                                        <p>
                                            <?php _e( 'Connect Your WooCommerce Account:', 'formaloo-form-builder' ); ?>
                                        </p> 
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="submit" name="submit" id="submit" class="button formaloo-woocommerce-connect-button" value="<?php _e( 'Connect', 'formaloo-form-builder' ); ?>">
                                    </td>
                                </tr>
                            </tbody>
                            <tbody id="formaloo-woocommerce-connected">
                                <tr>
                                    <td>
                                        <h4>
                                            <?php _e( 'Choose Your Cashback Range:', 'formaloo-form-builder' ); ?>
                                        </h4>
                                    </td>
                                </tr>
                                <tr>
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
                                    <!--
                                    <td>
                                        <p class="submit">
                                            <a class="button"><?php //_e( 'Sync', 'formaloo-form-builder' ); ?></a>
                                        </p>
                                    </td>
                                    -->
                                    <td>
                                        <a href="#" target="_blank"><?php _e( 'How we calculate cashback?', 'formaloo-form-builder' ); ?></a>
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>

                        </table>
                        
                    </div>

                </form>

                <a href="<?php echo esc_url( $this->getSupportUrl() ); ?>" target="_blank"><?php _e( 'Need Support? Feel free to contact us', 'formaloo-form-builder' ); ?></a>
                                
            </div>

            <script src="<?php echo FORMALOO_URL ?>assets/js/handleTokenExpiration.js"></script>
            
            <script>
                jQuery(document).ready(function($){

                    $('.formaloo-cashback-success-notice').hide();
                    $('.formaloo-cashback-error-notice').hide();
                    $('#formaloo-woocommerce-not-connected').hide();
                    hideLoadingGif();

                    $('#formaloo-cashback-form').on('submit', function(e){
                        e.preventDefault();
                        showLoadingGif();
                        syncCustomers();
                        syncOrders();
                    });

                    function syncCustomers() {

                        // MARK: remove active business
                        $.ajax({
                            url: '',
                            type: 'POST',
                            dataType: 'json',
                            headers: {
                                'x-api-key': '<?php echo $data['api_key']; ?>',
                                'Authorization': '<?php echo 'Token ' . $data['api_token']; ?>'
                            },
                            contentType: 'application/json; charset=utf-8',
                            data: JSON.stringify(customersJson),
                            success: function (result) {
                                if (result['status'] == 201) {
                                    showSuccessMessage('Customers have been imported successfully.');
                                }
                                hideLoadingGif();
                            },
                            error: function (error) {
                                console.log(error);
                                disableCashbackTable();
                                var errorText = error['responseJSON']['errors']['general_errors'][0];
                                showGeneralErrors(errorText);
                                hideLoadingGif();
                            }
                        });

                    }

                    function syncOrders() {
                        
                        orders.forEach(function(entry) {
                            // MARK: remove active business
                            $.ajax({
                                url: '',
                                type: 'POST',
                                dataType: 'json',
                                contentType: 'application/json; charset=utf-8',
                                data: JSON.stringify(entry),
                                success: function (result) {
                                    if (result['status'] == 201) {
                                        showSuccessMessage('Orders have been imported successfully.');
                                    }
                                },
                                error: function (error) {
                                    if (error['status'] != 401) {
                                        disableCashbackTable();
                                        var errorText = error['responseJSON']['errors']['general_errors'][0];
                                        showGeneralErrors(errorText);
                                    } else {
                                        handleTokenExpiration(error);
                                    }
                                }
                            });
                        });

                    }

                    function disableCashbackTable() {
                        $(".formaloo-cashback-settings-table").addClass("formaloo-cashback-disabled-table");
                        $(".formaloo-cashback-settings-table :input").attr("disabled", true);
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