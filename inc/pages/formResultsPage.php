<?php
    class Formaloo_Form_Results_Page extends Formaloo_Main_Class {
        /**
         * Outputs the submits of the selected form
         *
         * @return void
         */
        public function formResultsPage() {
                $data = $this->getData();
                $not_ready = (empty($data['api_token']) || empty($data['api_key']));

                ?>

                <div class="wrap">

                    <div id="form-show-specific-result" style="display:none;">
                    </div>

                    <script>
                        function showFormResultWith($protocol, $url, $formSlug, $resultSlug) {
                            var urlBase = $protocol +'://'+ $url +'/dashboard/my-forms/'+ $formSlug +'/submit-details/'+ $resultSlug;
                            jQuery("#form-show-specific-result").append('<iframe id="show-result-iframe" width="100%" height="100%" src="'+ urlBase +'" frameborder="0" onload="resizeIframe();">');
                            var cacheParamValue = (new Date()).getTime();
                            var url = urlBase + "?cache=" + cacheParamValue;
                            reloadFrame(document.getElementById('show-result-iframe'), url);
                        }

                        function reloadFrame(iframe, src) {
                            iframe.src = src;
                        }

                        function resizeIframe() {
                            var TB_WIDTH = jQuery(document).width();
                            jQuery("#TB_window").animate({
                                width: TB_WIDTH + 'px',
                            });
                        }
                    </script>

                    <form id="formaloo-admin-form" class="postbox">

                        <?php if (!empty($data['api_token']) && !empty($data['slug_for_results'])): ?>
                            <p class="notice notice-error">
                                    <?php _e( 'An error happened on the WordPress side.', 'formaloo-form-builder' ); ?>
                            </p>

                        <?php
                        // If the results were returned
                        else: ?>

                            <?php
                            /*
                                * --------------------------
                                * Show List of Results
                                * --------------------------
                                */
                            ?>
                            <div class="form-group inside results-table">
                                <h3 class="formaloo-heading">
                                    <span class="dashicons dashicons-text-page"></span>
                                    <a href="<?php echo admin_url( "admin.php?page=formaloo" ) ?>"><?php _e('My Forms', 'formaloo-form-builder'); ?></a> / 
                                    <?php _e('Your Form Results', 'formaloo-form-builder'); ?>
                                </h3>
                                <?php 
                                    $resultsPageClass = new Formaloo_Results_Page();
                                    $resultsPageClass->resultsTablePage(esc_attr($_GET['results_slug'])); 
                                ?>
                            </div>

                        <?php endif; ?>

                    </form>
                </div>

                <?php
        }
    }