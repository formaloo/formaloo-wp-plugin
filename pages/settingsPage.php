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

                        <h3 class="formaloo-heading">
                            <?php echo $this->getStatusIcon(!$not_ready); ?>
                            <?php _e('Welcome to Formaloo!', 'formaloo-form-builder'); ?>
                        </h3>

                        <?php if ($not_ready): ?>
                            <p>
                                <?php echo __('To get started, we\'ll need to access your Formaloo account with an', 'formaloo-form-builder') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/profile/" target="_blank">'. __('API Key & API Secret', 'formaloo-form-builder') .'</a>. '. __('Paste your Formaloo API Key & API Secret, and click', 'formaloo-form-builder') .' <strong>'. __('Connect', 'formaloo-form-builder') .'</strong> '. __('to continue', 'formaloo-form-builder') .'.'; ?>
                            </p>
                        <?php else: ?>
                            <?php echo __('You can access your', 'formaloo-form-builder') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/" target="_blank">'. __('Formaloo dashboard here', 'formaloo-form-builder') .'</a>.'; ?>  
                        <?php endif; ?>
                        <?php echo $this->getStatusDiv(!$not_ready); ?>

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
                                            value="<?php echo (isset($data['api_key'])) ? $data['api_key'] : ''; ?>"/>
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
                        </table>
                    </div>

                </form>

                <a href="<?php echo esc_url( $this->getSupportUrl() ); ?>" target="_blank"><?php _e( 'Need Support? Feel free to contact us', 'formaloo-form-builder' ); ?></a>
            </div>

            <?php

        }
    }