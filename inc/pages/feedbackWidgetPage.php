<?php
    class Formaloo_Feedback_Widget_Page extends Formaloo_Main_Class {
        /**
         * Outputs the Feedback Widget Creation layout containing the form with all its options
         *
         * @return void
         */
        public function feedbackWidgetPage() {

            $data = $this->getData();

            $not_ready = (empty($data['api_token']) || empty($data['api_key']));

            ?>

            <div class="wrap">

                <form id="formaloo-feedback-widget-form" class="postbox">

                    <div class="form-group inside">

                        <?php
                        /*
                        * --------------------------
                        * Feedback Widget Settings
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

                        <div id="form-show-edit" style="display:none;"></div>
                        <div id="formaloo-feedback-widget-show-options" style="display:none;">

                        <table class="form-table formaloo-feedback-widget-show-options-table">
                            <tbody>
                                <tr>
                                    <td scope="row">
                                        <label><strong><?php _e( 'As a Link', 'formaloo-form-builder' ); ?></strong></label><br>
                                        <small><?php _e( 'Share this URL with others to view the form directly', 'formaloo-form-builder' ); ?></small>
                                    </td>
                                    <td>
                                        <a href="" target="_blank" id="formaloo-feedback-widget"></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td scope="row">
                                        <label><strong><?php _e( 'As a Script Tag', 'formaloo-form-builder' ); ?></strong></label><br>
                                        <small><?php _e( 'Using this method, the form will become a part of your website\'s markup. To use this, put the code snippet in your website footer:', 'formaloo-form-builder' ); ?></small><br><br>
                                        <a href="<?php echo FORMALOO_URL ?>assets/images/feedback_widget_helper.png" target="_blank"> <img src="<?php echo FORMALOO_URL ?>assets/images/feedback_widget_helper.png" id="formaloo-where-to-put-feedback-widget" alt="Feedback Widget Helper" /></a>
                                    </td>
                                    <td>
                                    <div class="formaloo_clipboard_wrapper">
                                        <textarea id="formaloo-feedback-widget-script-textarea"></textarea>
                                        <button class="button button-primary formaloo_clipboard_btn" data-clipboard-target="#formaloo-feedback-widget-script-textarea">
                                            <img src="<?php echo FORMALOO_URL ?>/assets/images/clippy.svg" width="13" alt="Copy to clipboard">
                                        </button>  
                                    </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>

                        <div class="formaloo-api-settings-top-wrapper">
                            <img src="<?php echo FORMALOO_URL ?>assets/images/Formaloo_Logo.png" alt="formaloo-logo">
                            <h1 class="formaloo-heading">
                                <?php _e('Feedback Widget Creator', 'formaloo-form-builder'); ?>
                            </h1>
                        </div>

                        <p class="notice notice-error formaloo-feedback-widget-notice"></p> 

                        <div class="formaloo-feedback-wdiget-top-info">
                            <img src="<?php echo FORMALOO_URL ?>assets/images/feedback_widget.png" alt="feedback-widget-design">
                            <h3 class="formaloo-heading">
                                <?php _e('Create a feedback widget for your website in less than 1 minute:', 'formaloo-form-builder'); ?>
                            </h3>
                        </div>

                        <?php if ($not_ready): ?>
                            <p><?php echo __('You didn\'t activate the plugin.', 'formaloo-form-builder') ?> <a href="<?php echo admin_url( "admin.php?page=formaloo-settings-page" ) ?>"><strong><?php echo __('Get Started by visiting the Settings Page', 'formaloo-form-builder') ?></strong></a>.</p>
                        <?php else: ?>
                            <?php // echo __('You can access your', 'formaloo-form-builder') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/" target="_blank">'. __('Formaloo dashboard here', 'formaloo-form-builder') .'</a>.'; ?>  
                        <?php endif; ?>

                        <input type="hidden" id="formaloo_feedback_widget_form_slug" name="formaloo_feedback_widget_form_slug" value="">
                        <input type="hidden" id="formaloo_feedback_widget_form_address" name="formaloo_feedback_widget_form_address" value="">
                        <input type="hidden" id="formaloo_feedback_widget_theme_config" name="formaloo_feedback_widget_theme_config" value="">

                        <table class="form-table formaloo-feedback-widget-settings-table">
                            <tbody id="formaloo_feedback_widget_templates_tbody">
                                <tr>
                                    <td scope="row">
                                        <label><strong><?php _e( 'Choose the NPS Template:', 'formaloo-form-builder' ); ?></strong></label>
                                    </td>
                                    <td>
                                        <fieldset class="formaloo_feedback_widget_templates_wrapper">
                                        </fieldset>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody id="formaloo_feedback_widget_settings_tbody">
                                <tr>
                                    <td scope="row">
                                        <label><strong><?php _e( 'Button Text', 'formaloo-form-builder' ); ?></strong></label>
                                    </td>
                                    <td>
                                        <input name="formaloo_feedback_widget_button_text"
                                            id="formaloo_feedback_widget_button_text"
                                            class="regular-text"
                                            type="text"
                                            value=""
                                            placeholder="<?php _e( 'Button Text', 'formaloo-form-builder' ); ?>"/>
                                    </td>
                                </tr>
                                <tr id="formaloo_feedback_widget_type_row">
                                    <td scope="row">
                                        <label>
                                            <strong>
                                                <?php _e( 'Widget Type', 'formaloo-form-builder' ); ?>
                                            </strong>
                                        </label>
                                    </td>
                                    <td>
                                    <fieldset id="formaloo_feedback_widget_type_fieldset">
                                        <input type="radio" name="formaloo_feedback_widget_type" id="formaloo_feedback_widget_type_drawer" value="drawer" checked /> <label for = "formaloo_feedback_widget_type"><?php _e('Drawer', 'formaloo-form-builder'); ?></label><br>
                                        <input type="radio" name="formaloo_feedback_widget_type" id="formaloo_feedback_widget_type_side-widget" value="side-widget" /> <label for = "formaloo_feedback_widget_type"><?php _e('Side Widget', 'formaloo-form-builder'); ?></label><br>
                                        <input type="radio" name="formaloo_feedback_widget_type" id="formaloo_feedback_widget_type_single-step" value="single-step" /> <label for = "formaloo_feedback_widget_type"><?php _e('Single Step', 'formaloo-form-builder'); ?></label><br>
                                    </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <td scope="row">
                                        <label><strong><?php _e( 'Show Only‌ ‌Once‌ ‌Per‌ ‌User', 'formaloo-form-builder' ); ?></strong></label>
                                    </td>
                                    <td>
                                        <input name="formaloo_feedback_widget_once_per_user"
                                            id="formaloo_feedback_widget_once_per_user"
                                            class=""
                                            type="checkbox"
                                            value="once_per_user"
                                            placeholder="<?php _e( 'Show Only‌ ‌Once‌ ‌Per‌ ‌User', 'formaloo-form-builder' ); ?>"/>
                                    </td>
                                </tr>
                                <tr id="formaloo_feedback_widget_position_row">
                                    <td scope="row">
                                        <label>
                                            <strong>
                                                <?php _e( 'Widget Position on Screen', 'formaloo-form-builder' ); ?>
                                            </strong>
                                        </label>
                                    </td>
                                    <td>
                                    <fieldset>
                                        <input type="radio" name="formaloo_feedback_widget_position" id="formaloo_feedback_widget_position_left" value="left" checked /> <label for = "formaloo_feedback_widget_position"><?php _e('Left', 'formaloo-form-builder'); ?></label><br>
                                        <input type="radio" name="formaloo_feedback_widget_position" id="formaloo_feedback_widget_position_right" value="right" /> <label for = "formaloo_feedback_widget_position"><?php _e('Right', 'formaloo-form-builder'); ?></label><br>
                                        <input type="radio" name="formaloo_feedback_widget_position" id="formaloo_feedback_widget_position_left-bottom" value="left-bottom" /> <label for = "formaloo_feedback_widget_position"><?php _e('Bottom Left', 'formaloo-form-builder'); ?></label><br>
                                        <input type="radio" name="formaloo_feedback_widget_position" id="formaloo_feedback_widget_position_right-bottom" value="right-bottom" /> <label for = "formaloo_feedback_widget_position"><?php _e('Bottom Right', 'formaloo-form-builder'); ?></label> 
                                    </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <td scope="row">
                                        <label><strong><?php _e( 'Submit Button Text', 'formaloo-form-builder' ); ?></strong></label>
                                    </td>
                                    <td>
                                        <input name="formaloo_feedback_widget_submit_button_text"
                                            id="formaloo_feedback_widget_submit_button_text"
                                            class="regular-text"
                                            type="text"
                                            value=""
                                            placeholder="<?php _e( 'Submit Button Text', 'formaloo-form-builder' ); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td scope="row">
                                        <label><strong><?php _e( 'Button Color', 'formaloo-form-builder' ); ?></strong></label>
                                    </td>
                                    <td>
                                        <input type="text" value="#F95E2F" class="formaloo_feedback_widget_button_color" data-default-color="#F95E2F" />
                                    </td>
                                </tr>
                                <tr>
                                    <td scope="row">
                                        <label><strong><?php _e( 'Success Message After Submit', 'formaloo-form-builder' ); ?></strong></label>
                                    </td>
                                    <td>
                                        <input name="formaloo_feedback_widget_success_message_after_submit"
                                            id="formaloo_feedback_widget_success_message_after_submit"
                                            class="regular-text"
                                            type="text"
                                            value=""
                                            placeholder="<?php _e( 'Success Message After Submit', 'formaloo-form-builder' ); ?>"/>
                                    </td>
                                </tr>
                                <tr id="formaloo-feedback-widget-submit-row">
                                    <td>
                                        <p class="submit">
                                            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'formaloo-form-builder' ); ?>">
                                        </p>    
                                    </td>
                                    <td>
                                        <span class="spinner"></span>
                                    </td>
                                    <td id="formaloo-feedback-widget-edit-fields-btn-wrapper">
                                        <a href="#TB_inline?width=100vw&height=100vh&inlineId=form-show-edit" title="<?php __('Edit Fields', 'formaloo-form-builder') ?>" class="thickbox" onclick="showEditFormWith();"><?php _e( 'Edit Fields', 'formaloo-form-builder' ); ?></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                    </div>

                </form>

                <a href="<?php echo esc_url( $this->getSupportUrl() ); ?>" target="_blank"><?php _e( 'Need Support? Feel free to contact us', 'formaloo-form-builder' ); ?></a>
                                
            </div>
            
            <script>
                function showEditFormWith() {
                    var formSlug = jQuery('#formaloo_feedback_widget_form_slug').val();
                    jQuery("#form-show-edit").append('<iframe id="edit-form-iframe" width="100%" height="100%" src="<?php echo esc_url( FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT . '/dashboard/my-forms/' ); ?>'+formSlug+'/edit/" frameborder="0" onload="resizeIframe();">');
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

                jQuery(document).ready(function($){

                    $('.formaloo-feedback-widget-notice').hide();

                    $('.formaloo_feedback_widget_templates_wrapper').on('click', '.formaloo_feedback_widget_template', function(){
                        $(".formaloo_feedback_widget_template_selected").removeClass("formaloo_feedback_widget_template_selected");
                        $(this).addClass("formaloo_feedback_widget_template_selected");
                        copyTemplate($(this).attr("id"));
                    });

                    toggleFeedbackWidgetPositionRow($('#formaloo_feedback_widget_type_fieldset input[type="radio"]').val());

                    $('#formaloo_feedback_widget_type_fieldset input[type="radio"]').on('change', function() {
                        toggleFeedbackWidgetPositionRow($(this).val());   
                    });

                    function toggleFeedbackWidgetPositionRow(widgetType) {
                        if (widgetType == 'drawer') {
                            $('#formaloo_feedback_widget_position_row').hide();
                        } else {
                            $('#formaloo_feedback_widget_position_row').show();
                        }
                    }

                    <?php 
                        $data = $this->getData();
                        
                        $widgetSlugToEdit = '';
                        if (!empty($_GET['widget_slug'])) {
                            $widgetId = esc_attr($_GET['widget_slug']);
                            $widgetSlugToEdit = $widgetId;
                        }
                    ?>

                    var widgetSlugToEdit = "<?php echo $widgetSlugToEdit; ?>";

                    if (widgetSlugToEdit.length > 0) {
                        $('#formaloo_feedback_widget_templates_tbody').hide();
                        loadExistingWidget(widgetSlugToEdit);
                    } else {
                        $('#formaloo_feedback_widget_templates_tbody').show();
                        loadTemplates();
                    }
                    
                    function loadTemplates() {
                        
                        var templateIndex = 0

                        $.ajax({
                            url: "<?php echo esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1/forms/templates/list/?pagination=0' ); ?>",
                            type: 'GET',
                            dataType: 'json',
                            contentType: 'application/json; charset=utf-8',
                            success: function (result) {
                                $.each(result['data']['forms'], function(i, form) {
                                    if (form['form_type'] == 'nps') {
                                        if (templateIndex == 0) {
                                            copyTemplate(form['slug']);   
                                        }    
                                        $(".formaloo_feedback_widget_templates_wrapper").append('<div class="formaloo_feedback_widget_template '+ ((templateIndex == 0) ? ' formaloo_feedback_widget_template_selected' : '') +'" id="'+ form['slug'] +'"><img src="' +  ((form['logo'] == null) ? "<?php echo FORMALOO_URL ?>assets/images/logo_placeholder.png" : form['logo']) + '" alt="' + form['title'] + '"><b>'+form['title']+'</b></div>');
                                        templateIndex += 1;
                                    }
                                });
                                templateIndex = 0;
                            },
                            error: function (error) {
                                disableFeedbackWidgetTable();
                                var errorText = error['responseJSON']['errors']['general_errors'][0];
                                showGeneralErrors(errorText);
                                hideLoadingGif();
                            }
                        });
                    }

                    function copyTemplate(slug) {
                        $.ajax({
                            url: "<?php echo esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1/forms/form/copy/' ); ?>",
                            type: 'POST',
                            headers: {
                                'x-api-key': '<?php echo isset($data['api_key']) ? $data['api_key'] : ''; ?>',
                                'Authorization': '<?php echo 'JWT ' . (isset($data['api_token']) ? $data['api_token'] : ''); ?>'
                            },
                            data: { 'copied_form' : slug },
                            success: function (result) {
                                setupFormSettings(result['data']['form']);
                            },
                            error: function (error) {
                                disableFeedbackWidgetTable();
                                var errorText = error['responseJSON']['errors']['general_errors'][0];
                                showGeneralErrors(errorText);
                                hideLoadingGif();
                            }
                        });
                    }

                    function loadExistingWidget(widgetSlug){
                        $.ajax({
                            url: "<?php echo esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v2/forms/form/' ); ?>"+widgetSlug+"/",
                            type: 'GET',
                            dataType: 'json',
                            headers: {
                                'x-api-key': '<?php echo isset($data['api_key']) ? $data['api_key'] : ''; ?>',
                                'Authorization': '<?php echo 'JWT ' . (isset($data['api_token']) ? $data['api_token'] : ''); ?>'
                            },
                            contentType: 'application/json; charset=utf-8',
                            success: function (result) {
                                setupFormSettings(result['data']['form']);
                            },
                            error: function (error) {
                                disableFeedbackWidgetTable();
                                var errorText = error['responseJSON']['errors']['general_errors'][0];
                                showGeneralErrors(errorText);
                                hideLoadingGif();                                
                            }
                        });
                    }

                    function setupFormSettings(form) {

                        // Setup Slug Inputs
                        $('#formaloo_feedback_widget_form_slug').val(form['slug']);
                        $('#formaloo_feedback_widget_form_address').val(form['address']);

                        $('#formaloo_feedback_widget_button_text').val(form['title']);
                        $('#formaloo_feedback_widget_submit_button_text').val(form['button_text']);
                        $('#formaloo_feedback_widget_success_message_after_submit').val(form['success_message']);

                        var themeConfig = form['theme_config'];

                        $('#formaloo_feedback_widget_theme_config').val(JSON.stringify(themeConfig));

                        if (themeConfig != null) {
                            var widgetSettings = themeConfig['widget_settings'];

                            $('#formaloo_feedback_widget_type_' + widgetSettings['type']).attr('checked', 'checked');
                            toggleFeedbackWidgetPositionRow(widgetSettings['type']);
                            $('#formaloo_feedback_widget_once_per_user').prop('checked', widgetSettings['once_per_user']);
                            if (widgetSettings['type'] != 'drawer') {
                                $('#formaloo_feedback_widget_position_' + widgetSettings['position']).attr('checked', 'checked');
                            }
                        }

                        var buttonColors = JSON.parse(form['button_color']);
                        var r = buttonColors['r'];
                        var g = buttonColors['g'];
                        var b = buttonColors['b'];
                        var buttonColor = rgbToHex(r, g, b);

                        $('.formaloo_feedback_widget_button_color').val(buttonColor);
                        $('.formaloo_feedback_widget_button_color').attr('data-default-color', buttonColor);  

                        $('.formaloo_feedback_widget_button_color').wpColorPicker();

                        hideLoadingGif();
                    }

                    $('#formaloo-feedback-widget-form').on('submit', function(e){
                        e.preventDefault();

                        $('.formaloo-feedback-widget-notice').hide();

                        jQuery('.spinner').addClass('is-active');

                        var formSlug = $('#formaloo_feedback_widget_form_slug').val();
                        var formAddress = $('#formaloo_feedback_widget_form_address').val();
                        var themeConfig = $('#formaloo_feedback_widget_theme_config').val();
                        var buttonText = $('#formaloo_feedback_widget_button_text').val();
                        var submitButtonText = $('#formaloo_feedback_widget_submit_button_text').val();
                        var buttonColor = $('.formaloo_feedback_widget_button_color').val();
                        var successMessage = $('#formaloo_feedback_widget_success_message_after_submit').val();

                        try {
                            hexToRgbA(buttonColor);
                        } catch(err) {
                            showGeneralErrors(err.message);
                            jQuery('.spinner').removeClass('is-active');
                        }

                        var formParams = { "slug": formSlug, "title" : buttonText, "button_color" : hexToRgbA(buttonColor), "form_type" : "nps", "success_message": successMessage, "button_text": submitButtonText};
                        var editFormUrl = "<?php echo FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v2/forms/form/'; ?>"+formSlug+"/";
                        var formUrl =  "<?php echo FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT; ?>" + "/" + formAddress;
                        $('#formaloo-feedback-widget').attr("href",formUrl);
                        $('#formaloo-feedback-widget').text(formUrl);

                        var newThemeConfig = JSON.parse($('#formaloo_feedback_widget_theme_config').val());
                        var oncePerUser = $("#formaloo_feedback_widget_once_per_user").is( ":checked" );
                        var widgetType = $("input[name='formaloo_feedback_widget_type']:checked").val();
                        var widgetPosition = $("input[name='formaloo_feedback_widget_position']:checked").val();
                        var widgetDirection = ('<?php echo get_locale(); ?>' == 'fa_IR') ? 'rtl' : 'ltr';
                        var widgetBaseUrl = 'https://staging.widget.formaloo.com';
                        newThemeConfig['widget_settings']['once_per_user'] = oncePerUser;
                        newThemeConfig['widget_settings']['type'] = widgetType;
                        newThemeConfig['widget_settings']['position'] = widgetPosition;
                        
                        formParams['theme_config'] = JSON.stringify(newThemeConfig);

                        var scriptText = `<!-- Formaloo Widget --><div data-widget-form="formaloo-widget" data-prop-slug="${formSlug}" data-prop-type="${widgetType}" dir="${widgetDirection}"><script type="text/props">{"position": "${widgetPosition}","once_per_user": ${oncePerUser}}<\/script></div><script async src="${widgetBaseUrl}/bundle.js"><\/script><!-- End Formaloo Widget -->`;

                        $('#formaloo-feedback-widget-script-textarea').val(scriptText);

                        editForm(editFormUrl, formParams).then(function(data) {
                            tb_show("<?php _e( 'How to use your Feedback Widget', 'formaloo-form-builder' ); ?>","#TB_inline?width=100vw&height=100vh&inlineId=formaloo-feedback-widget-show-options",null);
                            jQuery('.spinner').removeClass('is-active');
                        }).catch(function(err) {
                            jQuery('.spinner').removeClass('is-active');
                        })

                    });

                    function editForm(urlString, params) {
                        return new Promise(function(resolve, reject) {
                            $.ajax({
                                url: urlString,
                                type: 'PATCH',
                                headers: {
                                    'x-api-key': '<?php echo isset($data['api_key']) ? $data['api_key'] : ''; ?>',
                                    'Authorization': '<?php echo 'JWT ' . (isset($data['api_token']) ? $data['api_token'] : ''); ?>'
                                },
                                data: params,
                                success: function (result) {
                                    resolve(result);
                                },
                                error: function (error) {
                                    reject(error);
                                }
                            });
                        });
                    }

                    function hexToRgbA(hex){
                        var c;
                        if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){
                            c= hex.substring(1).split('');
                            if(c.length== 3){
                                c= [c[0], c[0], c[1], c[1], c[2], c[2]];
                            }
                            c= '0x'+c.join('');
                            return '{"r":'+String((c>>16)&255)+',"g":'+String((c>>8)&255)+',"b":'+String(c&255)+',"a":1}';
                        }
                        throw new Error('Bad Hex');
                    }

                    function componentToHex(c) {
                        var hex = c.toString(16);
                        return hex.length == 1 ? "0" + hex : hex;
                    }

                    function rgbToHex(r, g, b) {
                        return "#" + componentToHex(r) + componentToHex(g) + componentToHex(b);
                    }

                    function disableFeedbackWidgetTable() {
                        $(".formaloo-feedback-widget-settings-table").addClass("formaloo-feedback-widget-disabled-table");
                        $(".formaloo-feedback-widget-settings-table :input").attr("disabled", true);
                    }

                    function showGeneralErrors(errorText) {
                        $('.formaloo-feedback-widget-notice').show();
                        $('.formaloo-feedback-widget-notice').text(errorText);
                    }

                    function hideLoadingGif() {
                        $('.formaloo-loading-gif-wrapper').hide();
                    }

                });
            </script>

            <?php

        }
    }