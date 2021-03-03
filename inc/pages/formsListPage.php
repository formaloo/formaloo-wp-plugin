<?php
    class Formaloo_Forms_List_Page extends Formaloo_Main_Class {

        public function getUserProfileName() {
            $result = array();
            $data = $this->getData();
            $api_token = $data['api_token'];
            $api_key = $data['api_key'];
            $api_secret = $data['api_secret'];
            
            $api_url = FORMALOO_PROTOCOL. '://api.'. FORMALOO_ENDPOINT .'/v2/profiles/profile/me/';
      
            $response = wp_remote_get( $api_url ,
                array( 'timeout' => 10,
                        'headers' => array( 'x-api-key' => $api_key,
                                            'Authorization'=> 'JWT ' . $api_token ) 
            ));
    
            if (wp_remote_retrieve_response_code($response) == 401 && !empty($api_secret) && !empty($api_key)) {
                $url = FORMALOO_PROTOCOL. '://accounts.'. FORMALOO_ENDPOINT .'/v1/oauth2/authorization-token/';
                // $url = 'https://staging.icas.formaloo.com/v1/oauth2/authorization-token/';
                $renewAuthTokenResponse = wp_remote_post( $url, array(
                    'body'    => array(
                        'grant_type'   => 'client_credentials'
                    ),
                    'headers' => array( 'x-api-key' => $api_key,
                                        'Authorization'=> 'Basic ' . $api_secret ) 
                ) );
                if (!is_wp_error($renewAuthTokenResponse)) {
                    $renewAuthTokenResult = json_decode($renewAuthTokenResponse['body'], true);
                    $data['api_token'] = $renewAuthTokenResult['authorization_token'];
                    update_option($this->option_name, $data);
                    $this->getUserProfileName();
                }
            }
      
            if (is_array($response) && !is_wp_error($response)) {
              $result = json_decode($response['body'], true);
            }
    
            return $result['data']['profile']['first_name'];
        }

        /**
         * Outputs the Admin Dashboard layout containing the form with all its options
         *
         * @return void
         */
        public function formsListPage() {

            $data = $this->getData();
            $api_response = $this->getForms();
            $not_ready = (empty($data['api_token']) || empty($data['api_key']) || empty($api_response) || isset($api_response['error']) || $api_response['status'] != 200);

            ?>

            <div class="wrap">

                <div id="form-show-edit" style="display:none;">
                </div>

                <div id="form-show-options" style="display:none;">
                    <form id="formaloo-customize-form">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <td scope="row">
                                    <label><?php _e( 'How to show', 'formaloo-form-builder' ); ?></label>
                                </td>
                                <td>
                                    <select name="formaloo_show_type"
                                            id="formaloo_show_type">
                                        <option value="link" <?php echo (isset($data['show_type']) && $data['show_type'] === 'link') ? 'selected' : ''; ?>>
                                            <?php _e( 'Link', 'formaloo-form-builder' ); ?>
                                        </option>
                                        <option value="iframe" <?php echo (isset($data['show_type']) && $data['show_type'] === 'iframe') ? 'selected' : ''; ?>>
                                            <?php _e( 'iFrame', 'formaloo-form-builder' ); ?>
                                        </option>
                                        <option value="script" <?php echo (!isset($data['show_type']) || (isset($data['show_type']) && $data['show_type'] === 'script')) ? 'selected' : ''; ?>>
                                            <?php _e( 'Script', 'formaloo-form-builder' ); ?>
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            <tr id="link_title_row">
                                <td scope="row">
                                    <label>
                                        <?php _e( 'Link title', 'formaloo-form-builder' ); ?>
                                        <br>
                                        <small><?php _e( '(Choose a title for the form link)', 'formaloo-form-builder' ); ?></small>
                                    </label>
                                </td>
                                <td>
                                    <input name="formaloo_link_title"
                                            id="formaloo_link_title"
                                            type="text"
                                            class="regular-text"
                                            value="<?php echo (isset($data['link_title'])) ? esc_attr__($data['link_title']) : __('Show Form', 'formaloo-form-builder'); ?>"/>
                                </td>
                            </tr>
                            <tr id="show_logo_row">
                                <td scope="row">
                                    <label>
                                        <?php _e( 'Show logo', 'formaloo-form-builder' ); ?>
                                        <br>
                                        <small><?php _e( '(Show the logo in the embedded form?)', 'formaloo-form-builder' ); ?></small>
                                    </label>
                                </td>
                                <td>
                                <input type="radio" name="formaloo_show_logo" id="formaloo_show_logo_yes" value="yes" checked /> <label for = "formaloo_show_logo"><?php _e('Yes', 'formaloo-form-builder'); ?></label> <br>
                                <input type="radio" name="formaloo_show_logo" id="formaloo_show_logo_no" value="no" /> <label for = "formaloo_show_logo"><?php _e('No', 'formaloo-form-builder'); ?></label> 
                                </td>
                            </tr>
                            <tr id="show_title_row">
                                <td scope="row">
                                    <label>
                                        <?php _e( 'Show title', 'formaloo-form-builder' ); ?>
                                        <br>
                                        <small><?php _e( '(Show the title in the embedded form?)', 'formaloo-form-builder' ); ?></small>
                                    </label>
                                </td>
                                <td>
                                <input type="radio" name="formaloo_show_title" id="formaloo_show_title_yes" value="yes" checked /> <label for = "formaloo_show_title"><?php _e('Yes', 'formaloo-form-builder'); ?></label> <br>
                                <input type="radio" name="formaloo_show_title" id="formaloo_show_title_no" value="no" <?php // checked( 'no' == $data['show_title'] ); ?> /> <label for = "formaloo_show_title"><?php _e('No', 'formaloo-form-builder'); ?></label> 
                                </td>
                            </tr>
                            <tr id="show_descr_row">
                                <td scope="row">
                                    <label>
                                        <?php _e( 'Show description', 'formaloo-form-builder' ); ?>
                                        <br>
                                        <small><?php _e( '(Show the description in the embedded form?)', 'formaloo-form-builder' ); ?></small>
                                    </label>
                                </td>
                                <td>
                                <input type="radio" name="formaloo_show_descr" id="formaloo_show_descr_yes" value="yes" checked /> <label for = "formaloo_show_descr"><?php _e('Yes', 'formaloo-form-builder'); ?></label> <br>
                                <input type="radio" name="formaloo_show_descr" id="formaloo_show_descr_no" value="no" /> <label for = "formaloo_show_descr"><?php _e('No', 'formaloo-form-builder'); ?></label> 
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="formaloo_clipboard_wrapper formaloo_hidden">
                        <input id="formaloo_shortcode_pre" type="text" class="regular-text" placeholder="<?php _e('Shortcode will appear here', 'formaloo-form-builder'); ?>">
                        <button class="button button-primary formaloo_clipboard_btn" data-clipboard-target="#formaloo_shortcode_pre">
                            <img src="<?php echo FORMALOO_URL ?>/assets/images/clippy.svg" width="13" alt="Copy to clipboard">
                        </button>  
                    </div>
                    <p>
                    <?php _e('Copy the shortcode above then go to your post/page editor. If it is Gutenberg Editor, add a Shortcode block and paste the shortcode. If it is Classic Editor, choose the Text tab (instead of Visual tab) tab and paste the shortcode wherever you desire.' , 'formaloo-form-builder') ?>
                    <a href="https://en.support.wordpress.com/shortcodes/" target="_blank"> <?php _e( 'More Info', 'formaloo-form-builder' ); ?> </a>
                    </p>
                    <?php if (!$not_ready): ?>
                        <div class="formaloo-shortcode-post-row">
                            <button class="button button-primary formaloo-admin-save my-10" type="submit">
                                <?php _e( 'Get shortcode', 'formaloo-form-builder' ); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                    </form>          
                    
                    <script src="<?php echo FORMALOO_URL ?>assets/js/handleTokenExpiration.js"></script>

                    <script>
                        function getRowInfo($slug, $address) {
                            jQuery('.formaloo_clipboard_wrapper').addClass('formaloo_hidden');
                            jQuery(".form-table").append('<input name="formaloo_form_slug" id="formaloo_form_slug" type="hidden" value="' + $slug + '" />');
                            jQuery(".form-table").append('<input name="formaloo_form_address" id="formaloo_form_address" type="hidden" value="' + $address + '" />');
                            jQuery('.formaloo-shortcode-post-row').find('a').remove();
                            jQuery(".formaloo-shortcode-post-row").append('<a href="<?php echo FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT ?>/dashboard/my-forms/' + $slug + '/share" target="_blank"><?php _e( 'Additional Settings', 'formaloo-form-builder' ); ?></a>');
                        }

                        function showEditFormWith($protocol, $url, $slug) {
                            jQuery("#form-show-edit").append('<iframe id="edit-form-iframe" width="100%" height="100%" src="'+ $protocol +'://'+ $url +'/dashboard/my-forms/'+ $slug +'/edit/" frameborder="0" onload="resizeIframe();">');
                        }

                        function createNewForm(e) {
                            e.preventDefault();
                            jQuery.ajax({
                                url: "<?php echo esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1/forms/form/' ); ?>",
                                type: 'POST',
                                headers: {
                                    'x-api-key': '<?php echo $data['api_key']; ?>',
                                    'Authorization': '<?php echo 'JWT ' . $data['api_token']; ?>'
                                },
                                data: { 'active' : true, 'show_title' : true },
                                success: function (result) {
                                    var formSlug = result['data']['form']['slug'];
                                    tb_show('<?php _e('Create a form', 'formaloo-form-builder'); ?>', '<?php echo FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT ?>/dashboard/my-forms/' + formSlug + '/edit/&TB_iframe=true&width=100vw&height=100vh');

                                    jQuery( 'body' ).on( 'thickbox:iframe:loaded', function ( event ) {
                                        resizeIframe();
                                    });
                                    
                                    jQuery( 'body' ).on( 'thickbox:removed', function ( event ) {
                                        location.reload();
                                    });
                                },
                                error: function (error) {
                                    // MARK: Handle create new form error
                                }
                            });
                        }

                        function resizeIframe() {
                            var TB_WIDTH = jQuery(document).width();
                            jQuery("#TB_window").animate({
                                width: TB_WIDTH + 'px',
                                height: '100vh'
                            });
                            jQuery("iframe").animate({
                                width: '100%',
                                height: '100vh'
                            });
                        }

                        jQuery("#formaloo_show_type").change(function() {
                            jQuery('.formaloo_clipboard_wrapper').addClass('formaloo_hidden');
                            if (jQuery(this).val() == "link") {
                                toggleRows(link = true);
                            } else if (jQuery(this).val() == "script") {
                                toggleRows(link = false, title = true, logo = true, descr = true);
                            } else {
                                toggleRows();
                            }
                        });

                        function toggleRows(link = false, title = false, logo = false, descr = false) {
                            link ? jQuery('#link_title_row').show() : jQuery('#link_title_row').hide();
                            title ? jQuery('#show_title_row').show() : jQuery('#show_title_row').hide();
                            logo ? jQuery('#show_logo_row').show() : jQuery('#show_logo_row').hide();
                            descr ? jQuery('#show_descr_row').show() : jQuery('#show_descr_row').hide();
                        }

                        jQuery("#formaloo_show_type").trigger("change");
                    </script>
                </div>

                <form id="formaloo-admin-form" class="postbox">

                    <div class="form-group inside" id="my-forms-header">
                        <div class="formaloo-api-settings-top-wrapper">
                            <img src="<?php echo FORMALOO_URL ?>assets/images/Formaloo_Logo.png" alt="formaloo-logo">
                            <h1 class="formaloo-heading">
                                <?php _e('My Forms', 'formaloo-form-builder'); ?>
                            </h1>
                        </div>
                    </div>

                    <div class="form-group inside">
                        
                        <?php if ($not_ready): ?>
                            <p>
                                <?php echo $this->getStatusIcon(!$not_ready) . __("Make sure you have a Formaloo account first, it's free! 👍","formaloo-form-builder"); ?>
                                <?php echo __('You can', 'formaloo-form-builder') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/" target="_blank">'. __('create an account here', 'formaloo-form-builder') .'</a>.'; ?>
                                <br>
                                <?php echo __('If so you can find your API Key & API Token from your', 'formaloo-form-builder') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/profile/" target="_blank">'. __('profile page,', 'formaloo-form-builder') .'</a> '. __('and enter it on the', 'formaloo-form-builder') .' <a href="?page=formaloo-settings-page">'. __('settings page', 'formaloo-form-builder') .'</a>.'; ?>
                            </p>
                        <?php else: ?>
                            <?php echo $this->getStatusIcon(!$not_ready); ?>
                            <?php 
                                $formaloo_first_name = $this->getUserProfileName();
                                $formaloo_user_name = empty($formaloo_first_name) ? __('User', 'formaloo-form-builder') : $formaloo_first_name;
                                echo __('Hello Dear', 'formaloo-form-builder'). ' ' . $formaloo_user_name .'! '. __('You can edit or view your forms right here or you can access', 'formaloo-form-builder') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/" target="_blank">'. __('your full dashboard here', 'formaloo-form-builder') .'</a>.'; 
                            ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($data['api_token']) && !empty($data['api_key']) /*&& !empty($data['public_key'])*/): ?>

                        <?php
                        // if we don't even have a response from the API
                        if (empty($api_response)) : ?>
                            <p class="notice notice-error">
                                <?php _e( 'An error happened on the WordPress side. Make sure your server allows remote calls.', 'formaloo-form-builder' ); ?>
                            </p>

                        <?php
                        // If we have an error returned by the API
                        elseif (isset($api_response['error'])): ?>

                            <p class="notice notice-error">
                                <?php echo $api_response['error']; ?>
                            </p>

                        <?php
                        // If the Forms were returned
                        else: ?>

                            <?php
                            /*
                            * --------------------------
                            * Show List of Forms
                            * --------------------------
                            */
                            ?>

                            <div class="form-group inside">
                                <h3 class="formaloo-heading">
                                    <span class="dashicons dashicons-feedback"></span>
                                    <?php _e('Your Forms', 'formaloo-form-builder'); ?>
                                </h3>
                                <div class="formaloo-create-form-wrapper">
                                    <div class="formaloo-create-form">
                                        <a href="#" class="button button-primary formaloo-create-new-form-link" onclick="createNewForm(event);"><span class="dashicons dashicons-plus"></span> <?php _e('Create a Blank Form', 'formaloo-form-builder') ?></a>
                                    </div>
                                    <div class="formaloo-create-form">
                                        <a href="<?php echo admin_url( "admin.php?page=formaloo-templates-page" ) ?>" class="button button-primary formaloo-create-new-form-link"><span class="dashicons dashicons-welcome-add-page"></span> <?php _e('Select a Template', 'formaloo-form-builder') ?></a>
                                    </div>
                                    <div class="formaloo-create-form">
                                        <a href="<?php echo admin_url( "admin.php?page=formaloo-feedback-widget-page" ) ?>" class="button button-secondary formaloo-create-new-form-link"><span class="dashicons dashicons-star-half"></span> <?php _e('Create a feedback widget', 'formaloo-form-builder'); ?></a>
                                    </div>
                                </div>
                                <?php $this->list_table_page(); ?>
                            </div>

                            <script>
                                jQuery(document).ready(function($){

                                    var created_from_template = false;
                                    <?php
                                        if (!empty($_GET['created_from_template'])):
                                            if (esc_attr($_GET['created_from_template']) == true):
                                     ?>
                                        created_from_template = true;
                                    <?php 
                                            endif;
                                        endif;
                                    ?>

                                    if (created_from_template == true) {
                                        blink($('table').find('tbody').find('tr:first'), 3, 300, '#FFE7E1');
                                        removeURLParameters(['created_from_template']);
                                    }

                                    function removeURLParameters(removeParams) {
                                        const deleteRegex = new RegExp(removeParams.join('=|') + '=')

                                        const params = location.search.slice(1).split('&')
                                        let search = []
                                        for (let i = 0; i < params.length; i++) if (deleteRegex.test(params[i]) === false) search.push(params[i])

                                        window.history.replaceState({}, document.title, location.pathname + (search.length ? '?' + search.join('&') : '') + location.hash)
                                    }

                                    function blink(target, count, blinkspeed, bc) {
                                        let promises=[];
                                        const b=target.css('background-color');
                                        target.css('background-color', bc||b);
                                        for (i=1; i<count; i++) {
                                            const blink = target.fadeTo(blinkspeed||100, .3).fadeTo(blinkspeed||100, 1.0);
                                            promises.push(blink);
                                        }
                                        // wait for all the blinking to finish before changing the background color back
                                        $.when.apply(null, promises).done(function() {
                                            target.css('background-color', bc);
                                        });
                                        promises=undefined;
                                    }
                                });
                            </script>

                        <?php endif; ?>

                    <?php endif; ?>

                </form>

                <a href="<?php echo esc_url( $this->getSupportUrl() ); ?>" target="_blank"><?php _e( 'Need Support? Feel free to contact us', 'formaloo-form-builder' ); ?></a>
            </div>

            <?php

        }
    }