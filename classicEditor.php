<?php

/**
 * Functionality related to the admin TinyMCE editor.
 *
 * @since 1.0.0
 */
class Formaloo_Admin_Editor {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'media_buttons', array( $this, 'media_button' ), 15 );
	}

	/**
	 * Allow easy shortcode insertion via a custom media button.
	 *
	 * @since 1.0.0
	 *
	 * @param string $editor_id
	 */
	public function media_button( $editor_id ) {

    $data = get_option('formaloo_data', array());
		if ( !$data['api_key'] || !$data['api_token'] ) {
			return;
		}

		// Provide the ability to conditionally disable the button, so it can be
		// disabled for custom fields or front-end use such as bbPress. We default
		// to only showing within the admin panel.
		if ( ! apply_filters( 'formaloo_display_media_button', is_admin(), $editor_id ) ) {
			return;
		}

    // Setup the icon - currently using a dashicon.
    $icon = '<span class="wp-media-buttons-icon formaloo-menu-icon" style="font-size:16px;margin-top:-2px;"><?xml version="1.0" encoding="utf-8"?> <!-- Generator: Adobe Illustrator 24.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  --> <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 14 14" style="enable-background:new 0 0 14 14;" xml:space="preserve"> <style type="text/css"> .st0{fill:#F95C30;} </style> <path class="st0" d="M10.5,1H3.6C2.2,1,1,2.1,1,3.5v6.9C1,11.8,2.1,13,3.5,13h6.9c1.4,0,2.6-1.1,2.6-2.5V3.6C13,2.2,11.9,1,10.5,1z M5.3,9.7c0,0.3-0.3,0.5-0.5,0.5H4.3c-0.3,0-0.5-0.3-0.5-0.5V9.2c0-0.3,0.3-0.5,0.5-0.5h0.5c0.3,0,0.5,0.3,0.5,0.5V9.7z M7.9,7.3 c0,0.3-0.3,0.5-0.5,0.5H4.2c-0.3,0-0.5-0.3-0.5-0.5V6.7c0.1-0.3,0.3-0.5,0.5-0.5h3.2c0.3,0,0.5,0.3,0.5,0.5L7.9,7.3z M10.3,4.9 c0,0.3-0.3,0.5-0.5,0.5L4.3,5.3c-0.3,0-0.5-0.2-0.5-0.5V4.2c0-0.3,0.3-0.5,0.5-0.5h5.6c0.3,0,0.5,0.3,0.5,0.5V4.9z"/> </svg></span>';

		printf(
			'<a href="#TB_inline?&width=100vw&height=100vh&inlineId=formaloo-form-shortcode" class="button thickbox" data-editor="%s" title="%s">%s %s</a>',
			esc_attr( $editor_id ),
			esc_attr__( 'Add Form', 'formaloo-form-builder' ),
			$icon,
			__( 'Add Form', 'formaloo-form-builder' )
		);

		add_action( 'admin_footer', array( $this, 'shortcode_modal' ) );
	}

	/**
	 * Modal window for inserting the form shortcode into TinyMCE.
	 *
	 * Thickbox is old and busted so we don't use that. Creating a custom view in
	 * Backbone would make me pull my hair out. So instead we offer a small clean
	 * modal that is based off of the WordPress insert link modal.
	 *
	 * @since 1.0.0
	 */
	public function shortcode_modal() {
		?>
            <div id="formaloo-form-shortcode" style="display:none;">
              <form id="formaloo-customize-form">
                <table class="form-table">
                      <tbody>
                        <tr>
                            <td scope="row">
                                <label><?php _e( 'Select a form', 'formaloo-form-builder' ); ?></label>
                            </td>
                            <td>
                                <select name="formaloo_forms_list"
                                        id="formaloo_forms_list">
                                    <option value="-">
                                        -
                                    </option>
                                </select>
                            </td>
                        </tr>
                      </tbody>
                      <tbody id="formaloo-form-shortcode-options">
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
                <!-- <p>
                <?php // _e('Copy the shortcode above then go to your post/page editor. If it is Gutenberg Editor, add a Shortcode block and paste the shortcode. If it is Classic Editor, choose the Text tab (instead of Visual tab) tab and paste the shortcode wherever you desire.' , 'formaloo-form-builder') ?>
                <a href="https://en.support.wordpress.com/shortcodes/" target="_blank"> <?php // _e( 'More Info', 'formaloo-form-builder' ); ?> </a>
                </p> -->
                <?php if (!$not_ready): ?>
                    <div class="formaloo-shortcode-post-row">
                        <button class="button button-primary formaloo-admin-save my-10" id="formaloo-form-shortcode-insert-button" type="button">
                            <?php _e( 'Insert shortcode', 'formaloo-form-builder' ); ?>
                        </button>
                    </div>
                <?php endif; ?>
                </form>  
                <script>


                    jQuery("#formaloo-form-shortcode-options").hide();
                    jQuery(".formaloo-shortcode-post-row").hide();

                    formaloo_exchanger.forms_list['data']['forms'].forEach((form) => {
                        jQuery('#formaloo_forms_list').append('<option data-form-slug="' + form['slug'] + '" data-form-address="' + form['address'] + '" value="' + form['title'] + '">' + form['title'] + '</option>');
                    });

                    jQuery("select#formaloo_forms_list").change(function () {
                      if (jQuery('#formaloo_forms_list').find(":selected").val() != "-") {
                        var slug = jQuery('#formaloo_forms_list').find(":selected").data("form-slug");
                        var address = jQuery('#formaloo_forms_list').find(":selected").data("form-address");
                        jQuery("#formaloo-form-shortcode-options").show();
                        jQuery(".formaloo-shortcode-post-row").show();
                        jQuery(".formaloo_clipboard_wrapper").show();
                        getRowInfo(slug,address);
                      } else {
                        jQuery("#formaloo-form-shortcode-options").hide();
                        jQuery(".formaloo-shortcode-post-row").hide();
                        jQuery(".formaloo_clipboard_wrapper").hide();
                      }
                    });

                    jQuery( "#formaloo-form-shortcode-insert-button" ).click(function() {
                      var win = window.dialogArguments || opener || parent || top;
                      win.send_to_editor( document.getElementById( 'formaloo_shortcode_pre' ).value );
                    });

                    function getRowInfo($slug, $address) {
                        jQuery('.formaloo_clipboard_wrapper').addClass('formaloo_hidden');
                        jQuery(".form-table").append('<input name="formaloo_form_slug" id="formaloo_form_slug" type="hidden" value="' + $slug + '" />');
                        jQuery(".form-table").append('<input name="formaloo_form_address" id="formaloo_form_address" type="hidden" value="' + $address + '" />');
                        jQuery('.formaloo-shortcode-post-row').find('a').remove();
                        jQuery(".formaloo-shortcode-post-row").append('<a href="<?php echo FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT ?>/dashboard/my-forms/' + $slug + '/share" target="_blank"><?php _e( 'Additional Settings', 'formaloo-form-builder' ); ?></a>');
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
                 <style>
                    #TB_window {
                      top: 50% !important;
                      height: auto !important;
                    }
                 </style>
            </div>       
		<?php
	}

}

new Formaloo_Admin_Editor;
