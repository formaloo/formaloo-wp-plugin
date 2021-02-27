<?php

class Formaloo_Admin_Notice extends Formaloo_Main_Class {
    /**
     * Display custom admin notice
     */
    public static function showInvalidTokenAdminNotice() {
        $currentScreen = get_current_screen();
        $parent = new Formaloo_Main_Class();
        $forms = $parent->getForms();
        $currentGetFormsStatus = isset($forms['status'])? $forms['status'] : 401;
        if ($currentGetFormsStatus == 401 && $currentScreen->id == 'toplevel_page_formaloo') {
        ?>

            <div class="notice notice-error is-dismissible inline">
                <p><?php echo __('Invalid API Token or API Key! Please visit your', 'formaloo-form-builder') . ' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/" target="_blank">'. __('Formaloo dashboard here', 'formaloo-form-builder') .'</a>'. ' ' . __('to get a new one.', 'formaloo-form-builder'); ?></p>
            </div>

        <?php
        }
    }
}