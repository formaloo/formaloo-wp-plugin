<?php
    class Formaloo_Templates_Page extends Formaloo_Main_Class {

        /**
         * Outputs the Formaloo Templates
         *
         * @return void
         */
        public function templatesPage() {

            $data = $this->getData();

            $not_ready = (empty($data['api_token']) || empty($data['api_key']));

            ?>

            <div class="wrap">

                <form id="formaloo-templates-admin-form"></form>

                <form id="formaloo-templates-form" class="postbox">

                    <div class="form-group inside">

                        <?php
                        /*
                        * --------------------------
                        * Templates Page
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

                        <div id="formaloo-guest-template-login" style="display:none;">
                            <div class="form-group inside">
                                <?php if ($not_ready): ?>
                                <p id="formaloo-guest-template-login-descr">
                                    <?php echo __('To get started, we\'ll need to access your Formaloo account with an API Key & Secret Key which you can', 'formaloo-form-builder') .' <a class="button button-primary" href="'. FORMALOO_PROTOCOL . '://cdp.' . FORMALOO_ENDPOINT .'/redirect/current-organization/integrations/wordpress" target="_blank">'. __('Get them here', 'formaloo-form-builder') .'</a>. '. '<br>' . __('Paste your Formaloo API Key & API Secret, and click', 'formaloo-form-builder') .' <strong>'. __('Connect', 'formaloo-form-builder') .'</strong> '. __('to continue', 'formaloo-form-builder') .'.'; ?>
                                </p>
                                <?php endif; ?>
                                <?php echo $this->getStatusDiv(!$not_ready); ?>
                            </div>

                            <div class="form-group inside">
                                <table class="form-table formaloo-api-settings-table">
                                    <tbody>
                                        <tr>
                                            <td scope="row">
                                                <label><?php _e( 'API Key', 'formaloo-form-builder' ); ?></label>
                                            </td>
                                            <td>
                                                <input name="formaloo_api_key"
                                                    id="formaloo_api_key"
                                                    class="regular-text"
                                                    type="text"
                                                    form="formaloo-templates-admin-form"
                                                    value="<?php echo (isset($data['api_key'])) ? $data['api_key'] : ''; ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td scope="row">
                                                <label><?php _e( 'API Secret', 'formaloo-form-builder' ); ?></label>
                                            </td>
                                            <td>
                                                <input name="formaloo_api_secret"
                                                    id="formaloo_api_secret"
                                                    class="regular-text"
                                                    type="text"
                                                    form="formaloo-templates-admin-form"
                                                    value="<?php echo (isset($data['api_secret'])) ? $data['api_secret'] : ''; ?>"/>
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
                                                <input class="button button-primary formaloo-admin-save formaloo-button-black" type="submit" form="formaloo-templates-admin-form" value="<?php ($not_ready) ? _e( 'Connect', 'formaloo-form-builder' ) : _e( 'Save', 'formaloo-form-builder' )?>">
                                                </input>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="formaloo-api-settings-top-wrapper">
                            <img src="<?php echo FORMALOO_URL ?>assets/images/Formaloo_Logo.png" alt="formaloo-logo">
                            <h1 class="formaloo-heading">
                                <?php _e('Templates', 'formaloo-form-builder'); ?>
                            </h1>
                        </div>

                        <p class="notice notice-error formaloo-templates-notice"></p> 

                        <input type="hidden" id="formaloo_templates_form_slug" name="formaloo_templates_form_slug" value="">

                        <div class="formaloo-templates-toolbar-wrapper">
                            <div class="alignleft actions">
                                <select id="formaloo-templates-category-selector-top">
                                    <option value="-1"><?php _e('All Categories', 'formaloo-form-builder'); ?></option>
                                </select>
                                <input type="submit" id="formaloo-templates-category-submit" class="button action" value="<?php _e('Apply', 'formaloo-form-builder'); ?>">
                            </div>
                            <p class="search-box">
                                <input type="search" id="formaloo-templates-search-text" placeholder="<?php _e('Search a template..', 'formaloo-form-builder'); ?>">
                                <input type="submit" id="formaloo-templates-search-submit" class="button" value="<?php _e('Search', 'formaloo-form-builder'); ?>">
                            </p>
                        </div>

                        <div class="formaloo-templates-grid-container"></div>
                        
                        <div id="formaloo-tempaltes-pagination-wrapper">
                            <div id="formaloo-templates-prev-page" class="button button-primary">
                            <?php _e('Prev', 'formaloo-form-builder'); ?>
                            </div>
                            <p id="formaloo-templates-current-page-number">
                                -
                            </p>
                            <div id="formaloo-templates-next-page"  class="button button-primary">
                            <?php _e('Next', 'formaloo-form-builder'); ?>
                            </div>
                        </div>
                        
                    </div>

                </form>

                <a href="<?php echo esc_url( $this->getSupportUrl() ); ?>" target="_blank"><?php _e( 'Need Support? Feel free to contact us', 'formaloo-form-builder' ); ?></a>             
            </div>

            <script>
                jQuery(document).ready(function($){

                    <?php 
                        $data = $this->getData();
                    ?>

                    $('.formaloo-templates-notice').hide();
                    loadTemplates();
                    loadCategories();
                    var searchText = '';
                    var selectedCategory = '';
                    
                    function loadTemplates(url = '', searchText = '', category = '') {

                        showLoadingGif();

                        var finalUrl = ''

                        if (url == '') {
                            finalUrl = '<?php echo esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1/forms/templates/list/?page=1' ); ?>';
                        } else {
                            finalUrl = url
                        }

                        if (searchText != '') {
                            var searchUrl = new URL(finalUrl);
                            searchUrl.searchParams.append('search', searchText);
                            finalUrl = searchUrl.toString();
                        }

                        if (category != '') {
                            var categorizedUrl = new URL(finalUrl);
                            categorizedUrl.searchParams.append('category', category);
                            finalUrl = categorizedUrl.toString();
                        }
    
                        $.ajax({
                            url: finalUrl,
                            type: 'GET',
                            dataType: 'json',
                            headers: {
                                'x-api-key': '<?php echo isset($data['api_key']) ? $data['api_key'] : ''; ?>'
                            },
                            contentType: 'application/json; charset=utf-8',
                            success: function (result) {

                                $( '.formaloo-templates-grid-container' ).empty();
                                
                                if(result['data']['forms'].length == 0) {
                                    $( '.formaloo-templates-grid-container' ).append( $( '<div></div><div class="formaloo-templates-not-found-wrapper"><img src="<?php echo FORMALOO_URL ?>assets/images/feedback_widget.png" alt="Template not found" ><?php _e('Template not found', 'formaloo-form-builder'); ?></div><div></div>' ) );
                                } else {
                                    $.each(result['data']['forms'], function(i, form) {
                                        $( '.formaloo-templates-grid-container' ).append( $( '<div class="formaloo-templates-grid-item"><div class="formaloo-templates-grid-item-inner"><img src="' + form['logo'] + '" alt="' + form['title'] + '"><div class="formaloo-template-title">' + form['title'] + '</div><div class="formaloo-templates-hover-div"><a href="<?php echo FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT ?>/' + form['address'] + '?TB_iframe=true&width=100vw&height=100vh" title="<?php _e('Preview the template', 'formaloo-form-builder'); ?>" target="_blank" class="button button-secondary formaloo-preview-template-link thickbox"><?php _e('Preview', 'formaloo-form-builder') ?></a><a href="#" class="button button-primary" data-form-slug="' + form['slug'] + '" onclick="copyTemplate(event, this)"><?php _e('Use', 'formaloo-form-builder'); ?></a></div></div></div>' ) );
                                    });

                                    jQuery( 'body' ).on( 'thickbox:iframe:loaded', function ( event ) {
                                        resizeIframe();
                                    });

                                    handlePagination(result['data']['current_page'], result['data']['previous'], result['data']['next']);
                                    handleHover();
                                }

                                hideLoadingGif();

                            },
                            error: function (error) {
                                var errorText = error['responseJSON']['errors']['general_errors'][0];
                                showGeneralErrors(errorText);
                                hideLoadingGif();
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

                    function loadCategories() {

                        $.ajax({
                            url: '<?php echo esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v2/forms/templates/categories/' ); ?>',
                            type: 'GET',
                            dataType: 'json',
                            contentType: 'application/json; charset=utf-8',
                            success: function (result) {
                                $.each(result['data']['categories'], function(i, category) {
                                    $( '#formaloo-templates-category-selector-top' ).append( $( '<option value="'+ category['slug'] +'">'+ category['title'] +'</option>' ) );
                                });
                            },
                            error: function (error) {
                                var errorText = error['responseJSON']['errors']['general_errors'][0];
                                showGeneralErrors(errorText);
                            }
                        });
                    }

                    $('#formaloo-templates-search-submit').click(function(e) {
                        e.preventDefault();
                        var val = $('#formaloo-templates-search-text').val();
                        searchText = val;
                        loadTemplates('', searchText, selectedCategory);
                    });

                    $('#formaloo-templates-category-submit').click(function(e) {
                        e.preventDefault();
                        var val = $( "#formaloo-templates-category-selector-top option:selected" ).val();
                        if (val == '-1') {
                            selectedCategory = '';
                        } else {
                            selectedCategory = val;
                        }
                        loadTemplates('', searchText, selectedCategory);
                    });

                    function handleHover() {
                        $(".formaloo-templates-grid-item").on({
                            mouseenter: function () {
                                $(this).find('div.formaloo-templates-grid-item-inner').find('div.formaloo-templates-hover-div').css('visibility','visible');
                            },
                            mouseleave: function () {
                                $(this).find('div.formaloo-templates-grid-item-inner').find('div.formaloo-templates-hover-div').css('visibility','hidden');
                            }
                        });
                    }

                    function handlePagination(currentPage, prev, next) {
                        $( "#formaloo-templates-prev-page").unbind( "click" );
                        $( "#formaloo-templates-next-page").unbind( "click" );
                        if (prev == null){
                            $('#formaloo-templates-prev-page').addClass("formaloo-disabled-next-prev-button");
                        } else {
                            $('#formaloo-templates-prev-page').removeClass("formaloo-disabled-next-prev-button");
                            $( '#formaloo-templates-prev-page' ).click(function() {
                                loadTemplates(prev, searchText, selectedCategory);
                            });
                        }
                        if (next == null){
                            $('#formaloo-templates-next-page').addClass("formaloo-disabled-next-prev-button");
                        } else {
                            $('#formaloo-templates-next-page').removeClass("formaloo-disabled-next-prev-button");
                            $( '#formaloo-templates-next-page' ).click(function() {
                                loadTemplates(next, searchText, selectedCategory);
                            });
                        }
                        $('#formaloo-templates-current-page-number').text(currentPage);
                    }

                });

                function showLoadingGif() {
                    jQuery('.formaloo-loading-gif-wrapper').show();
                }

                function hideLoadingGif() {
                    jQuery('.formaloo-loading-gif-wrapper').hide();
                }

                function showGeneralErrors(errorText) {
                    jQuery('.formaloo-templates-notice').show();
                    jQuery('.formaloo-templates-notice').text(errorText);
                }

                function copyTemplate(e, form) {

                    e.preventDefault();

                    var formSlug = form.dataset.formSlug;

                    <?php 
                        $data = $this->getData();
                        $not_ready = (empty($data['api_token']) || empty($data['api_key']));

                        if ($not_ready):
                    ?>
                    
                    tb_show("<?php _e( 'To use this template, please login first', 'formaloo-form-builder' ); ?>","#TB_inline?width=100vw&height=100vh&inlineId=formaloo-guest-template-login",null);


                    jQuery(document).on('submit', '#formaloo-templates-admin-form', function(e) {

                        e.preventDefault();

                        // We inject some extra fields required for the security
                        jQuery('#formaloo-settings-submit-row').append('<td><span class="spinner is-active"></span></td>');
                        jQuery(this).append('<input type="hidden" form="formaloo-templates-admin-form" name="action" value="store_admin_data" />');
                        jQuery(this).append('<input type="hidden" form="formaloo-templates-admin-form" name="security" value="' + formaloo_exchanger._nonce + '" />');

                        const apiKey = document.getElementById('formaloo_api_key').value;
                        const secretKey = document.getElementById('formaloo_api_secret').value
                        const url = formaloo_exchanger.protocol + '://accounts.' + formaloo_exchanger.endpoint_url + '/v1/oauth2/authorization-token/';

                        jQuery.ajax({
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
                                jQuery.ajax({
                                    url: formaloo_exchanger.ajax_url,
                                    type: 'post',
                                    data: jQuery('#formaloo-templates-admin-form').serialize(),
                                    success: function(response) {
                                        setTimeout(function() {
                                            jQuery('.spinner').removeClass('is-active');
                                            window.location.href = "?page=formaloo";
                                            }, 1000);
                                    }
                                });
                            },
                            error: function (error) {
                                setTimeout(function() {
                                    jQuery('.spinner').removeClass('is-active');
                                    window.location.href = "?page=formaloo";
                                }, 1000);
                            }
                        });

                    });

                    <?php else: ?>

                    jQuery.ajax({
                        url: "<?php echo esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1/forms/form/copy/' ); ?>",
                        type: 'POST',
                        headers: {
                            'x-api-key': '<?php echo isset($data['api_key']) ? $data['api_key'] : ''; ?>',
                            'Authorization': '<?php echo 'JWT ' . (isset($data['api_token']) ? $data['api_token'] : ''); ?>'
                        },
                        data: { 'copied_form' : formSlug },
                        success: function (result) {
                            showLoadingGif();
                            window.location.href = "?page=formaloo&created_from_template=true";
                        },
                        error: function (error) {
                            var errorText = error['responseJSON']['errors']['general_errors'][0];
                            showGeneralErrors(errorText);
                            hideLoadingGif();
                        }
                    });

                    <?php endif; ?>

                }

            </script>

            <?php

        }
    }