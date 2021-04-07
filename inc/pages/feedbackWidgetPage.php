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
                        
                        <!-- <div class="formaloo-loading-gif-wrapper">
                            <div class="formaloo-loader-wrapper">
                                <div class="formaloo-borders formaloo-first"></div>
                                <div class="formaloo-borders formaloo-middle"></div>
                                <div class="formaloo-borders formaloo-last"></div>
                            </div>
                        </div> -->

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
                        <input type="hidden" id="formaloo_feedback_widget_nps_field_slug" name="formaloo_feedback_widget_nps_field_slug" value="">
                        <input type="hidden" id="formaloo_feedback_widget_text_field_slug" name="formaloo_feedback_widget_text_field_slug" value="">

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
                                        <input type="radio" name="formaloo_feedback_widget_type" id="formaloo_feedback_widget_type_" value="drawer" checked /> <label for = "formaloo_feedback_widget_type"><?php _e('Drawer', 'formaloo-form-builder'); ?></label><br>
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
                                        <input type="radio" name="formaloo_feedback_widget_position" id="formaloo_feedback_widget_position_bottom_left" value="bottom_left" /> <label for = "formaloo_feedback_widget_position"><?php _e('Bottom Left', 'formaloo-form-builder'); ?></label><br>
                                        <input type="radio" name="formaloo_feedback_widget_position" id="formaloo_feedback_widget_position_bottom_right" value="bottom_right" /> <label for = "formaloo_feedback_widget_position"><?php _e('Bottom Right', 'formaloo-form-builder'); ?></label> 
                                    </fieldset>
                                    </td>
                                </tr>
                                <!--<tr>
                                    <td scope="row">
                                        <label><strong><?php //_e( 'NPS Choices Icon', 'formaloo-form-builder' ); ?></strong></label>
                                    </td>
                                    <td>
                                    <fieldset class="formaloo_feedback_widget_choice_wrapper">
                                        <div class="formaloo_feedback_widget_choice_icon formaloo_feedback_widget_choice_selected" id="heart">
                                            <img src="<?php //echo FORMALOO_URL ?>assets/images/widget_icons/fillHeart.png" alt="Heart Icon">
                                        </div>
                                        <div class="formaloo_feedback_widget_choice_icon" id="star">
                                            <img src="<?php //echo FORMALOO_URL ?>assets/images/widget_icons/fillStar.svg" alt="Star Icon">
                                        </div>
                                        <div class="formaloo_feedback_widget_choice_icon" id="funny_face">
                                            <img src="<?php //echo FORMALOO_URL ?>assets/images/widget_icons/FSmile.svg" alt="Funny Face Icon">
                                        </div>
                                        <div class="formaloo_feedback_widget_choice_icon" id="monster">
                                            <img src="<?php //echo FORMALOO_URL ?>assets/images/widget_icons/MSmile.svg" alt="Monster Icon">
                                        </div>
                                        <div class="formaloo_feedback_widget_choice_icon" id="flat_face">
                                            <img src="<?php //echo FORMALOO_URL ?>assets/images/widget_icons/FLLove.svg" alt="Flat Face Icon">
                                        </div>
                                        <div class="formaloo_feedback_widget_choice_icon" id="outlined">
                                            <img src="<?php //echo FORMALOO_URL ?>assets/images/widget_icons/OSmile.svg" alt="Outlined Icon">
                                        </div>
                                    </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <td scope="row">
                                        <label><strong><?php //_e( 'Question Title', 'formaloo-form-builder' ); ?></strong></label>
                                    </td>
                                    <td>
                                        <input name="formaloo_feedback_widget_question_text_title"
                                            id="formaloo_feedback_widget_question_text_title"
                                            class="regular-text"
                                            type="text"
                                            value=""
                                            placeholder="<?php //_e( 'Question Title', 'formaloo-form-builder' ); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td scope="row">
                                        <label><strong><?php //_e( 'TextBox Placeholder', 'formaloo-form-builder' ); ?></strong></label>
                                    </td>
                                    <td>
                                        <input name="formaloo_feedback_widget_textbox_placeholder"
                                            id="formaloo_feedback_widget_textbox_placeholder"
                                            class="regular-text"
                                            type="text"
                                            value=""
                                            placeholder="<?php //_e( 'TextBox Placeholder', 'formaloo-form-builder' ); ?>"/>
                                    </td>
                                </tr>
                                -->
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
                                </tr>
                            </tbody>
                        </table>
                        
                    </div>

                </form>

                <a href="<?php echo esc_url( $this->getSupportUrl() ); ?>" target="_blank"><?php _e( 'Need Support? Feel free to contact us', 'formaloo-form-builder' ); ?></a>
                                
            </div>
            
            <script>
                jQuery(document).ready(function($){

                    $('.formaloo-feedback-widget-notice').hide();

                    // $('.formaloo_feedback_widget_choice_icon').on("click",function(){
                    //     $(".formaloo_feedback_widget_choice_selected").removeClass("formaloo_feedback_widget_choice_selected");
                    //     $(this).addClass("formaloo_feedback_widget_choice_selected");
                    //     jQuery("#formaloo_feedback_widget_choice_icon_type").val($(this).find("div").attr("data-value"));
                    // });

                    $('.formaloo_feedback_widget_templates_wrapper').on('click', '.formaloo_feedback_widget_template', function(){
                        $(".formaloo_feedback_widget_template_selected").removeClass("formaloo_feedback_widget_template_selected");
                        $(this).addClass("formaloo_feedback_widget_template_selected");
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
                        loadExistingWidget(widgetSlugToEdit);
                    } else {
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
                                        console.log(templateIndex);
                                        if (templateIndex == 0) {
                                            // copyTemplate(form['slug']);   
                                        }    
                                        console.log(form);                              
                                        $(".formaloo_feedback_widget_templates_wrapper").append('<div class="formaloo_feedback_widget_template '+ ((templateIndex == 0) ? ' formaloo_feedback_widget_template_selected' : '') +'" id="formaloo_feedback_widget_'+ form['slug'] +'"><img src="' +  ((form['logo'] == null) ? "<?php echo FORMALOO_URL ?>assets/images/logo_placeholder.png" : form['logo']) + '" alt="' + form['title'] + '"><b>'+form['title']+'</b></div>');
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
                        
                        $.each(form['fields_list'], function(i, field) {
                            if (field['type'] == 'long_text' || field['type'] == 'short_text') {
                                $('#formaloo_feedback_widget_text_field_slug').val(field['slug']);
                                $('#formaloo_feedback_widget_textbox_placeholder').val(field['title']);
                            } else {
                                $('#formaloo_feedback_widget_nps_field_slug').val(field['slug']);
                                $('#formaloo_feedback_widget_question_text_title').val(field['title']);
                                $(".formaloo_feedback_widget_choice_selected").removeClass("formaloo_feedback_widget_choice_selected");
                                $('#' + field['thumbnail_type']).addClass("formaloo_feedback_widget_choice_selected");
                            }
                        });
                        
                        var formConfig = form['config'];

                        if (formConfig != null) {
                            $('#formaloo_feedback_widget_position_' + formConfig).attr('checked', 'checked');                    }

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

                        jQuery('#formaloo-feedback-widget-submit-row').append('<td><span class="spinner is-active"></span></td>');

                        var formSlug = $('#formaloo_feedback_widget_form_slug').val();
                        var formAddress = $('#formaloo_feedback_widget_form_address').val();
                        var npsFieldSlug = $('#formaloo_feedback_widget_nps_field_slug').val();
                        var textFieldSlug = $('#formaloo_feedback_widget_text_field_slug').val();
                        var buttonText = $('#formaloo_feedback_widget_button_text').val();
                        var buttonPosition = $('#formaloo_feedback_widget_position').val();
                        var selectedIcon = $(".formaloo_feedback_widget_choice_selected").attr('id');
                        var selectedPosition = "";
                        var textBoxPlaceHolder = $('#formaloo_feedback_widget_textbox_placeholder').val();
                        var questionTitle = $('#formaloo_feedback_widget_question_text_title').val();
                        var submitButtonText = $('#formaloo_feedback_widget_submit_button_text').val();
                        var buttonColor = $('.formaloo_feedback_widget_button_color').val();
                        var successMessage = $('#formaloo_feedback_widget_success_message_after_submit').val();

                        $.each($("input[type='radio']").filter(":checked"), function () {
                            selectedPosition = $(this).val();
                        });


                        try {
                            hexToRgbA(buttonColor);
                        }
                        catch(err) {
                            showGeneralErrors(err.message);
                            jQuery('.spinner').removeClass('is-active');
                        }

                        var npsFieldParams = { "form": formSlug, "slug" : npsFieldSlug, "title" : questionTitle, "thumbnail_type": selectedIcon};
                        var textFielParams = { "form": formSlug, "slug" : textFieldSlug, "title" : textBoxPlaceHolder};
                        var formParams = { "slug": formSlug, "title" : buttonText, "button_color" : hexToRgbA(buttonColor), "config" : selectedPosition, "form_type" : "nps", "success_message": successMessage, "button_text": submitButtonText};

                        var editTextFieldUrl = "<?php echo FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v2/fields/field/'; ?>"+textFieldSlug+"/";
                        var editNpsFieldUrl = "<?php echo FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v2/fields/field/'; ?>"+npsFieldSlug+"/";
                        var editFormUrl = "<?php echo FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v2/forms/form/'; ?>"+formSlug+"/";
                        var formUrl =  "<?php echo FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT; ?>" + "/" + formAddress;
                        $('#formaloo-feedback-widget').attr("href",formUrl);
                        $('#formaloo-feedback-widget').text(formUrl);
                        var scriptText = '<div id="formz-wrapper" data-formz-slug="' + formSlug + '"></div>' + '<script src="' + '<?php echo FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT . '/istatic/js/main.js'; ?>' + '\"><\/script>';
                        $('#formaloo-feedback-widget-script-textarea').val(scriptText);
                        
                        editField(editTextFieldUrl, textFielParams).then(function(data) {
                            editField(editNpsFieldUrl, npsFieldParams).then(function(data) {
                                editField(editFormUrl, formParams).then(function(data) {
                                tb_show("<?php _e( 'How to use your Feedback Widget', 'formaloo-form-builder' ); ?>","#TB_inline?width=100vw&height=100vh&inlineId=formaloo-feedback-widget-show-options",null);
                                jQuery('.spinner').removeClass('is-active');
                                }).catch(function(err) {
                                    jQuery('.spinner').removeClass('is-active');
                                })
                            }).catch(function(err) {
                                jQuery('.spinner').removeClass('is-active');
                            })
                            
                        }).catch(function(err) {
                            jQuery('.spinner').removeClass('is-active');
                        })
                    });

                    function editField(urlString, params) {
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