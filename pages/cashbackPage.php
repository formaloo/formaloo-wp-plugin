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
            
            <script>
                jQuery(document).ready(function($){

                    $('.formaloo-cashback-success-notice').hide();
                    $('.formaloo-cashback-error-notice').hide();
                    $('#formaloo-woocommerce-not-connected').hide();
                    hideLoadingGif();

                    // var activeBusiness = '';

                    // $.ajax({
                    //     url: "https://api.staging.formaloo.com/v1.0/businesses/",
                    //     type: 'GET',
                    //     dataType: 'json',
                    //     headers: {
                    //         'x-api-key': '9cabf77d58dd1a716dd5f9513db04c73a7ff76c9',
                    //         'authorization': 'JWT eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6Im15YXBwLTAwMSJ9.eyJ0b2tlbiI6ImF1dGgiLCJzaWQiOiIyYzgxOTFiZS1hNzQzLTQ3ZTMtODg0MC01MGE1YmE2NzA0ZTgiLCJ1aWQiOjM0LCJlbWFpbCI6InRhZGVoMTlAZ21haWwuY29tIiwiZmlyc3RfbmFtZSI6IlRhZGVoIiwibGFzdF9uYW1lIjoiQWxleGFuaSIsInBob25lX251bWJlciI6IjA5MzU1MzEyOTUyIiwidXNlcm5hbWUiOiJ0YWRlaDE5QGdtYWlsLmNvbSIsInZlcmlmaWVkX2VtYWlsIjpmYWxzZSwidmVyaWZpZWRfcGhvbmUiOnRydWUsImxhc3RfdXBkYXRlIjoiMjAyMC0wNS0xMlQxODo0NzowNi45MzNaIiwiZ3JvdXBzIjoiIiwiaXNzIjoiaWNhcyIsImF1ZCI6WyJpY2FzIiwiY3JtIiwiZm9ybXoiLCJpbnZvaWNlIiwicHJvamVjdGFudCIsImFjdGlvbnMiXSwiZXhwIjoxNjE2MDU1NjU5LCJpYXQiOjE2MTM0NjM2NTl9.04ZrpgDCEBGfp0No_A5gqk83XlZ3AQqZtYEz1ORtHwZDWYc-FjEggmDB5L3RUh576MHBDouHxU7_MWk48CtK_QPHuv5kucauaD0iLLZYVQubaqCagwYqkdHuZ5uIVAAK8e0FsG9SX6xa5At_rBOw2u95Hd6Cm2MVVwtAl6feiJ02YPXQZkv4_Ms7eAIoGQSGy2LrwF6Sh9KLpFfpy6Pz_2nj5nEAV9iCtNgklmq-DIpP-kFEggTcu_vUXisIhKV1XxTEsxfyTsjPD_YLsINSyvK3lRVb9w_jkMWxSVohJ5HOxHHNKSrAtok-4ruPYnyl7zt7fwWoD9IYEmY7WdnmAw'
                    //     },
                    //     contentType: 'application/json; charset=utf-8',
                    //     success: function (result) {
                    //         activeBusiness = result['data']['businesss'][0]['slug'];
                    //     },
                    //     error: function (error) {
                    //         disableCashbackTable();
                    //         var errorText = error['responseJSON']['errors']['general_errors'][0];
                    //         showGeneralErrors(errorText);
                    //         hideLoadingGif();
                    //     }
                    // });

                    $('#formaloo-cashback-form').on('submit', function(e){
                        e.preventDefault();
                        showLoadingGif();
                        syncCustomers();
                        syncOrders();
                    });

                    function syncCustomers() {
                        <?php
                            $wc_customers = new Formaloo_WC_Customers();
                            $customers = $wc_customers->get_customers();
                            $customers_json = json_encode($customers, JSON_PRETTY_PRINT);
                            echo "var customersJson = {$customers_json};";
                        ?>

                        // MARK: remove active business
                        $.ajax({
                            url: "https://api.staging.formaloo.com/v1.0/customers/batch/?active_business=bcmqr9Qb",
                            type: 'POST',
                            dataType: 'json',
                            headers: {
                                'x-api-key': '9cabf77d58dd1a716dd5f9513db04c73a7ff76c9',
                                'authorization': 'JWT eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6Im15YXBwLTAwMSJ9.eyJ0b2tlbiI6ImF1dGgiLCJzaWQiOiIyYzgxOTFiZS1hNzQzLTQ3ZTMtODg0MC01MGE1YmE2NzA0ZTgiLCJ1aWQiOjM0LCJlbWFpbCI6InRhZGVoMTlAZ21haWwuY29tIiwiZmlyc3RfbmFtZSI6IlRhZGVoIiwibGFzdF9uYW1lIjoiQWxleGFuaSIsInBob25lX251bWJlciI6IjA5MzU1MzEyOTUyIiwidXNlcm5hbWUiOiJ0YWRlaDE5QGdtYWlsLmNvbSIsInZlcmlmaWVkX2VtYWlsIjpmYWxzZSwidmVyaWZpZWRfcGhvbmUiOnRydWUsImxhc3RfdXBkYXRlIjoiMjAyMC0wNS0xMlQxODo0NzowNi45MzNaIiwiZ3JvdXBzIjoiIiwiaXNzIjoiaWNhcyIsImF1ZCI6WyJpY2FzIiwiY3JtIiwiZm9ybXoiLCJpbnZvaWNlIiwicHJvamVjdGFudCIsImFjdGlvbnMiXSwiZXhwIjoxNjE2MDU1NjU5LCJpYXQiOjE2MTM0NjM2NTl9.04ZrpgDCEBGfp0No_A5gqk83XlZ3AQqZtYEz1ORtHwZDWYc-FjEggmDB5L3RUh576MHBDouHxU7_MWk48CtK_QPHuv5kucauaD0iLLZYVQubaqCagwYqkdHuZ5uIVAAK8e0FsG9SX6xa5At_rBOw2u95Hd6Cm2MVVwtAl6feiJ02YPXQZkv4_Ms7eAIoGQSGy2LrwF6Sh9KLpFfpy6Pz_2nj5nEAV9iCtNgklmq-DIpP-kFEggTcu_vUXisIhKV1XxTEsxfyTsjPD_YLsINSyvK3lRVb9w_jkMWxSVohJ5HOxHHNKSrAtok-4ruPYnyl7zt7fwWoD9IYEmY7WdnmAw'
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
                                disableCashbackTable();
                                var errorText = error['responseJSON']['errors']['general_errors'][0];
                                showGeneralErrors(errorText);
                                hideLoadingGif();
                            }
                        });

                    }

                    function syncOrders() {
                        
                        <?php
                            $orders = wc_get_orders( array('numberposts' => -1) );

                            $orders_arr = array();

                            foreach( $orders as $order ){
                                
                                $user_id = $order->get_user_id();
                                $customer = new WC_Customer( $user_id );
                                $user_email   = $customer->get_email();

                                $orders_arr[] = array(
                                    'action' => 'order',
                                    'customer' => array(
                                        'email' => $user_email
                                    ),
                                    'activity_data' => array(
                                        'order_status' => $order->get_status(), 
                                        'order_total' => $order->get_total(),
                                        'order_total_discount' => $order->get_total_discount(),
                                        'order_shipping_method' => $order->get_shipping_method(),
                                        'order_currency' => $order->get_currency()
                                    )
                                );                                
                            }

                            $orders_json = json_encode($orders_arr);

                            echo "var orders = {$orders_json};";
                        ?>

                        orders.forEach(function(entry) {
                            console.log(JSON.stringify(entry));
                            // MARK: remove active business
                            $.ajax({
                                url: "https://api.staging.formaloo.com/v1.0/activities?active_business=bcmqr9Qb",
                                type: 'POST',
                                dataType: 'json',
                                headers: {
                                    'x-api-key': '9cabf77d58dd1a716dd5f9513db04c73a7ff76c9',
                                    'authorization': 'JWT eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImtpZCI6Im15YXBwLTAwMSJ9.eyJ0b2tlbiI6ImF1dGgiLCJzaWQiOiIyYzgxOTFiZS1hNzQzLTQ3ZTMtODg0MC01MGE1YmE2NzA0ZTgiLCJ1aWQiOjM0LCJlbWFpbCI6InRhZGVoMTlAZ21haWwuY29tIiwiZmlyc3RfbmFtZSI6IlRhZGVoIiwibGFzdF9uYW1lIjoiQWxleGFuaSIsInBob25lX251bWJlciI6IjA5MzU1MzEyOTUyIiwidXNlcm5hbWUiOiJ0YWRlaDE5QGdtYWlsLmNvbSIsInZlcmlmaWVkX2VtYWlsIjpmYWxzZSwidmVyaWZpZWRfcGhvbmUiOnRydWUsImxhc3RfdXBkYXRlIjoiMjAyMC0wNS0xMlQxODo0NzowNi45MzNaIiwiZ3JvdXBzIjoiIiwiaXNzIjoiaWNhcyIsImF1ZCI6WyJpY2FzIiwiY3JtIiwiZm9ybXoiLCJpbnZvaWNlIiwicHJvamVjdGFudCIsImFjdGlvbnMiXSwiZXhwIjoxNjE2MDU1NjU5LCJpYXQiOjE2MTM0NjM2NTl9.04ZrpgDCEBGfp0No_A5gqk83XlZ3AQqZtYEz1ORtHwZDWYc-FjEggmDB5L3RUh576MHBDouHxU7_MWk48CtK_QPHuv5kucauaD0iLLZYVQubaqCagwYqkdHuZ5uIVAAK8e0FsG9SX6xa5At_rBOw2u95Hd6Cm2MVVwtAl6feiJ02YPXQZkv4_Ms7eAIoGQSGy2LrwF6Sh9KLpFfpy6Pz_2nj5nEAV9iCtNgklmq-DIpP-kFEggTcu_vUXisIhKV1XxTEsxfyTsjPD_YLsINSyvK3lRVb9w_jkMWxSVohJ5HOxHHNKSrAtok-4ruPYnyl7zt7fwWoD9IYEmY7WdnmAw'
                                },
                                contentType: 'application/json; charset=utf-8',
                                data: JSON.stringify(entry),
                                success: function (result) {
                                    console.log(result);
                                    if (result['status'] == 201) {
                                        // showSuccessMessage('Customers have been imported successfully.');
                                    }
                                },
                                error: function (error) {
                                    console.log(error);
                                    disableCashbackTable();
                                    var errorText = error['responseJSON']['errors']['general_errors'][0];
                                    showGeneralErrors(errorText);
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