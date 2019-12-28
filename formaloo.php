<?php
/**
 * Plugin Name:       Foramloo
 * Description:       Easily embed Formaloo forms into your blog or WP pages.
 * Version:           1.0.0
 * Author:            Idearun team
 * Author URI:        https://idearun.co/
 * Text Domain:       formaloo
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */


/*
 * Plugin constants
 */
if(!defined('FORMALOO_PLUGIN_VERSION'))
	define('FORMALOO_PLUGIN_VERSION', '1.0.0');
if(!defined('FORMALOO_URL'))
	define('FORMALOO_URL', plugin_dir_url( __FILE__ ));
if(!defined('FORMALOO_PATH'))
	define('FORMALOO_PATH', plugin_dir_path( __FILE__ ));
if(!defined('FORMALOO_ENDPOINT'))
	define('FORMALOO_ENDPOINT', 'formz.aasoo.ir');
if(!defined('FORMALOO_PROTOCOL'))
    define('FORMALOO_PROTOCOL', 'http');
if(!defined('FORMALOO_X_API_KEY'))
    define('FORMALOO_X_API_KEY', 'f0a5ce1ecc1fea87a57f06a52a8e12c48cb16d34');

/*
 * Main class
 */
/**
 * Class Formaloo
 *
 * This class creates the option page and add the web app script
 */
class Formaloo {

	/**
	 * The security nonce
	 *
	 * @var string
	 */
	private $_nonce = 'formaloo_admin';

	/**
	 * The option name
	 *
	 * @var string
	 */
	private $option_name = 'formaloo_data';

	/**
	 * Formaloo constructor.
     *
     * The main plugin actions registered for WordPress
	 */
	public function __construct() {
        // Admin page calls
        // add_action('wp_footer',                 array($this,'addFooterCode'));

        add_action('admin_menu',                array($this,'addAdminMenu'));
        add_action('wp_ajax_store_admin_data',  array($this,'storeAdminData'));
        add_action('wp_ajax_get_formaloo_shortcode',  array($this,'getFormalooShortcode'));
        add_action('admin_enqueue_scripts',     array($this,'addAdminScripts'));

        add_shortcode('formaloo', array($this, 'formaloo_show_form_shortcode'));

    }

    public function formaloo_show_form_shortcode($atts) {
        // extract the attributes into variables
        extract(shortcode_atts(array(
            'slug'              => 'slug',
            'address'           => 'address',
            'type'              => 'link',
            'link_title'        => 'Show Form',
            'show_title'        => 'yes',
            'show_descr'        => 'yes',
            'show_logo'         => 'yes'
        ), $atts));

        switch ($atts['type']) {
            case 'link':
                return '<a href="' . FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/'. $atts['address'] .'" target="_blank"> '. $atts['link_title'] .' </a>';
            case 'iframe':
                return '<iframe src="' . FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/'. $atts['address'] .'" class="custom-formaloo-iframe-style" frameborder="0" marginheight="0" marginwidth="0">Loading...</iframe><style>.custom-formaloo-iframe-style {display:block; width:100%; height:100vh;}</style>';
            case 'script':
                if ($atts['show_title'] == 'no') {
                    $show_title =  '#main-form .formz-form-title { display: none; }';
                } 
                if ($atts['show_descr'] == 'no') {
                    $show_desc =  '#main-form .formz-form-desc { display: none; }';
                }
                if ($atts['show_logo'] == 'no') {
                    $show_logo =  '#main-form .formz-main-logo { display: none; }';
                }
                return '
                    <style>'. $show_title . $show_desc . $show_logo .'</style>
                    <div id="formz-wrapper" data-formz-slug="'. $atts['slug'] .'"></div>
                    <script src="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT . '/istatic/js/main.js" type="text/javascript" async></script>
                ';
        }

    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page($formData) {
        $data = $this->getData();
        $formListTable = new Forms_List_Table();
        $formListTable->setFormData($formData);
        $formListTable->setPrivateKey($data['private_key']);
        $formListTable->prepare_items();
        // $formListTable->search_box('search', 'search_id');
        ?>
        <?php $formListTable->display(); ?>
        <?php 
            if ( isset($_GET['download_excel']) ) {
                $this->downloadExcel( $_GET['download_excel'] );
                remove_query_arg( 'download_excel' );
            }
        ?>
        <?php
    }

    /**
     * Download the results excel file
     *
     */
    private function downloadExcel($slug){
        $data = $this->getData();
        echo '<script>
            window.open("'. $this->getExcelLink($slug ,$data['private_key']) .'","_self");
            let params = new URLSearchParams(location.search)
            params.delete("download_excel")
            history.replaceState(null, "", "?" + params + location.hash)
        </script>';
    }

    /**
     * Get the results excel file URL
     *
     * @return String
     */
    private function getExcelLink($slug, $private_key) {
        
        $data = '';
        $api_url = FORMALOO_PROTOCOL. '://api.'. FORMALOO_ENDPOINT .'/v1/forms/form/' . $slug . '/excel/';

        $response = wp_remote_get( $api_url ,
        array( 'timeout' => 10,
       'headers' => array( 'x-api-key' => FORMALOO_X_API_KEY,
                          'Authorization'=> 'Token ' . $private_key ) 
        ));

	    if (is_array($response) && !is_wp_error($response)) {
		    $data = json_decode($response['body'], true);
        }

	    return $data['data']['form']['excel'];
    }

	/**
	 * Returns the saved options data as an array
     *
     * @return array
	 */
	private function getData() {
	    return get_option($this->option_name, array());
    }

	/**
	 * Callback for the Ajax request
	 *
	 * Print the shortcode
     *
     * @return void
	 */
    public function getFormalooShortcode() {

		if (wp_verify_nonce($_POST['security'], $this->_nonce ) === false)
			die('Invalid Request! Reload your page please.');

        $fields = [];

		foreach ($_POST as $field=>$value) {

		    if (substr($field, 0, 9) !== "formaloo_")
				continue;

		    // We remove the formaloo_ prefix to clean things up
            $field = substr($field, 9);
            $fields[$field] = esc_attr__($value);
        }
        
        $form_add = (isset($fields['form_address'])) ? $fields['form_address'] : 'try again';
        $form_slug = (isset($fields['form_slug'])) ? $fields['form_slug'] : 'try again';
        $form_type = (isset($fields['show_type'])) ? $fields['show_type'] : 'link';
        
        $output = '[formaloo address="'. $form_add . '" slug="'. $form_slug . '" type="'. $form_type .'"';

        switch ($fields['show_type']) {
        case 'link':
            if (isset($fields['link_title']) && !empty($fields['link_title'])):
                $output = $output . ' link_title="'. $fields['link_title']. '"';
            endif;
        break;
        case 'iframe': break;
        case 'script':
            $show_title = (isset($fields['show_title']) && !empty($fields['show_title'])) ? $fields['show_title'] : 'no';
            $output = $output . ' show_title="'. $show_title .'"';

            $show_desc = (isset($fields['show_descr']) && !empty($fields['show_descr'])) ? $fields['show_descr'] : 'no';
            $output = $output . ' show_descr="'. $show_desc .'"';

            $show_logo = (isset($fields['show_logo']) && !empty($fields['show_logo'])) ? $fields['show_logo'] : 'no';
            $output = $output . ' show_logo="'. $show_logo .'"';
        break;
        }
        
        $output = $output . ']';

        wp_send_json_success(['output'=>$output]);

	}

	/**
	 * Callback for the Ajax request
	 *
	 * Updates the options data
     *
     * @return void
	 */
	public function storeAdminData() {

		if (wp_verify_nonce($_POST['security'], $this->_nonce ) === false)
			die('Invalid Request! Reload your page please.');

        $data = $this->getData();

		foreach ($_POST as $field=>$value) {

		    if (substr($field, 0, 9) !== "formaloo_")
				continue;

		    if (empty($value))
		        unset($data[$field]);

		    // We remove the formaloo_ prefix to clean things up
            $field = substr($field, 9);

			$data[$field] = esc_attr__($value);

        }

        update_option($this->option_name, $data);

		die();

	}

	/**
	 * Adds Admin Scripts for the Ajax call
	 */
	public function addAdminScripts() {

        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');

	    wp_enqueue_style('formaloo-admin', FORMALOO_URL. 'assets/css/admin.css', false, 1.0);
        wp_enqueue_script('formaloo-admin', FORMALOO_URL. 'assets/js/admin.js', array(), 1.0);

		$admin_options = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'_nonce'   => wp_create_nonce( $this->_nonce ),
		);

		wp_localize_script('formaloo-admin', 'formaloo_exchanger', $admin_options);

	}

	/**
	 * Adds the Formaloo label to the WordPress Admin Sidebar Menu
	 */
	public function addAdminMenu()
    {
		add_menu_page(
			__( 'Formaloo', 'formaloo' ),
			__( 'Formaloo', 'formaloo' ),
			'manage_options',
			'formaloo',
			array($this, 'adminLayout'),
			'dashicons-feedback'
		);
	}

	/**
	 * Make an API call to the Formaloo API and returns the response
     *
     * @param $private_key string
     *
     *
     * @return array
	 */
	private function getForms($private_key) {
        
        $data = array();
        
        $api_url = FORMALOO_PROTOCOL. '://api.'. FORMALOO_ENDPOINT .'/v1/forms/list/';

        $response = wp_remote_get( $api_url ,
        array( 'timeout' => 10,
       'headers' => array( 'x-api-key' => FORMALOO_X_API_KEY,
                          'Authorization'=> 'Token ' . $private_key ) 
        ));

	    if (is_array($response) && !is_wp_error($response)) {
		    $data = json_decode($response['body'], true);
        }
        
	    return $data;

    }

	/**
     * Get a Dashicon for a given status
     *
	 * @param $valid boolean
     *
     * @return string
	 */
    private function getStatusIcon($valid) {

        return ($valid) ? '<span class="dashicons dashicons-yes success-message"></span>' : '<span class="dashicons dashicons-no-alt error-message"></span>';

    }

	/**
	 * Outputs the Admin Dashboard layout containing the form with all its options
     *
     * @return void
	 */
	public function adminLayout() {

        $data = $this->getData();

	    $api_response = $this->getForms($data['private_key']);
	    $not_ready = (empty($data['private_key']) || empty($api_response) || isset($api_response['error']));
        
        $FORMALOO_WEBSITE = "formaloo.com";

	    ?>

		<div class="wrap">

            <h1><?php _e('Formaloo Settings', 'formaloo'); ?></h1>
            
            <div id="form-show-options" style="display:none;">
                <form id="formaloo-customize-form">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <td scope="row">
                                <label><?php _e( 'How to show', 'formaloo' ); ?></label>
                            </td>
                            <td>
                                <select name="formaloo_show_type"
                                        id="formaloo_show_type">
                                    <option value="link" <?php echo (!isset($data['show_type']) || (isset($data['show_type']) && $data['show_type'] === 'link')) ? 'selected' : ''; ?>>
                                        <?php _e( 'Link', 'formaloo' ); ?>
                                    </option>
                                    <option value="iframe" <?php echo (isset($data['show_type']) && $data['show_type'] === 'iframe') ? 'selected' : ''; ?>>
                                        <?php _e( 'iFrame', 'formaloo' ); ?>
                                    </option>
                                    <option value="script" <?php echo (isset($data['show_type']) && $data['show_type'] === 'script') ? 'selected' : ''; ?>>
                                        <?php _e( 'Script', 'formaloo' ); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <tr id="link_title_row">
                            <td scope="row">
                                <label>
                                    <?php _e( 'Link title', 'formaloo' ); ?>
                                    <br>
                                    <small><?php _e( '(Choose a title for the form link)', 'formaloo' ); ?></small>
                                </label>
                            </td>
                            <td>
                                <input name="formaloo_link_title"
                                        id="formaloo_link_title"
                                        type="text"
                                        class="regular-text"
                                        value="<?php echo (isset($data['link_title'])) ? esc_attr__($data['link_title']) : ''; ?>"/>
                            </td>
                        </tr>
                        <tr id="show_logo_row">
                            <td scope="row">
                                <label>
                                    <?php _e( 'Show logo', 'formaloo' ); ?>
                                    <br>
                                    <small><?php _e( '(Show the logo in the embedded form?)', 'formaloo' ); ?></small>
                                </label>
                            </td>
                            <td>
                            <input type="radio" name="formaloo_show_logo" id="formaloo_show_logo" value="yes" /> <label for = "formaloo_show_logo">Yes</label> <br>
                            <input type="radio" name="formaloo_show_logo" id="formaloo_show_logo" value="no" /> <label for = "formaloo_show_logo">No</label> 
                            </td>
                        </tr>
                        <tr id="show_title_row">
                            <td scope="row">
                                <label>
                                    <?php _e( 'Show title', 'formaloo' ); ?>
                                    <br>
                                    <small><?php _e( '(Show the title in the embedded form?)', 'formaloo' ); ?></small>
                                </label>
                            </td>
                            <td>
                            <input type="radio" name="formaloo_show_title" id="formaloo_show_title" value="yes" /> <label for = "formaloo_show_title">Yes</label> <br>
                            <input type="radio" name="formaloo_show_title" id="formaloo_show_title" value="no" <?php // checked( 'no' == $data['show_title'] ); ?> /> <label for = "formaloo_show_title">No</label> 
                            </td>
                        </tr>
                        <tr id="show_descr_row">
                            <td scope="row">
                                <label>
                                    <?php _e( 'Show description', 'formaloo' ); ?>
                                    <br>
                                    <small><?php _e( '(Show the description in the embedded form?)', 'formaloo' ); ?></small>
                                </label>
                            </td>
                            <td>
                            <input type="radio" name="formaloo_show_descr" id="formaloo_show_descr" value="yes" /> <label for = "formaloo_show_descr">Yes</label> <br>
                            <input type="radio" name="formaloo_show_descr" id="formaloo_show_descr" value="no" /> <label for = "formaloo_show_descr">No</label> 
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="formaloo_clipboard_wrapper hidden">
                    <pre id="formaloo_shortcode_pre"></pre>
                    <button class="button button-primary formaloo_clipboard_btn" data-clipboard-target="#formaloo_shortcode_pre">
                        <img src="<?php echo FORMALOO_URL ?>/assets/images/clippy.svg" width="13" alt="Copy to clipboard">
                    </button>  
                </div>
                <?php if (!$not_ready): ?> 
                    <button class="button button-primary formaloo-admin-save my-10" type="submit">
                        <?php _e( 'Get shortcode', 'formaloo' ); ?>
                    </button>
                <?php endif; ?>
                </form>
                <script src="<?php echo FORMALOO_URL ?>/assets/js/clipboard.min.js"></script>
                <script>
                
                    new ClipboardJS('.formaloo_clipboard_btn');

                    function getRowInfo($slug, $address) {
                        jQuery('.formaloo_clipboard_wrapper').addClass('hidden');
                        jQuery(".form-table").append('<input name="formaloo_form_slug" id="formaloo_form_slug" type="hidden" value="' + $slug + '" />');
                        jQuery(".form-table").append('<input name="formaloo_form_address" id="formaloo_form_address" type="hidden" value="' + $address + '" />');
                    }

                    jQuery("#formaloo_show_type").change(function() {
                        if (jQuery(this).val() == "link") {
                            toggleRows(link = true);
                        } else if (jQuery(this).val() == "script") {
                            toggleRows(title = true, logo = true, descr = true);
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

                <div class="form-group inside">

	                <?php
	                /*
					 * --------------------------
					 * API Settings
					 * --------------------------
					 */
	                ?>

                    <h3>
		                <?php echo $this->getStatusIcon(!$not_ready); ?>
		                <?php _e('Formaloo API Settings', 'formaloo'); ?>
                    </h3>

	                <?php if ($not_ready): ?>
                        <p>
                            <?php _e('Make sure you have a Formaloo account first, it\'s free! 👍', 'formaloo'); ?>
                            <?php _e('You can <a href="https://' . $FORMALOO_WEBSITE . '/" target="_blank">create an account here</a>.', 'formaloo'); ?>
                            <br>
                            <?php _e('If so you can find your api keys from your <a href="https://' . $FORMALOO_WEBSITE . '/dashboard/profile/" target="_blank">profile page</a>.', 'formaloo'); ?>
                            <br>
                            <br>
	                        <?php _e('Once the keys set and saved, if you do not see any option, please reload the page. Thank you, you rock 🤘', 'formaloo'); ?>
                        </p>
                    <?php else: ?>
		                <?php _e('Access your <a href="https://' . $FORMALOO_WEBSITE . '/dashboard/" target="_blank">Formaloo dashboard here</a>.', 'formaloo'); ?>
                    <?php endif; ?>

                    <table class="form-table">
                        <tbody>
                            <tr>
                                <td scope="row">
                                    <label><?php _e( 'Private key', 'formaloo' ); ?></label>
                                </td>
                                <td>
                                    <p>
                                    </p>
                                    <input name="formaloo_private_key"
                                           id="formaloo_private_key"
                                           class="regular-text"
                                           type="text"
                                           value="<?php echo (isset($data['private_key'])) ? $data['private_key'] : ''; ?>"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>


	            <?php if (!empty($data['private_key']) /*&& !empty($data['public_key'])*/): ?>

                    <?php
                    // if we don't even have a response from the API
                    if (empty($api_response)) : ?>
                        <p class="notice notice-error">
                            <?php _e( 'An error happened on the WordPress side. Make sure your server allows remote calls.', 'formaloo' ); ?>
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
                            <h3>
                                <span class="dashicons dashicons-forms"></span>
                                <?php _e('Your forms', 'formaloo'); ?>
                            </h3>
                            <?php $this->list_table_page($api_response); ?>
                        </div>
                        <hr>

                    <?php endif; ?>

                <?php endif; ?>

                <div class="inside">
                    <button class="button button-primary formaloo-admin-save" type="submit">
                        <?php _e( 'Save', 'formaloo' ); ?>
                    </button>
                </div>

            </form>

		</div>

		<?php

    }

	/**
     * Add the web app code to the page's footer
     *
     * This contains the widget markup used by the web app and the widget API call on the frontend
     * We use the options saved from the admin page
     *
     * @param $force boolean
     *
     * @return void
     */
	public function addFooterCode($force = false, $targetClass) {

        echo '<style> #main-form .'. $targetClass .' { display: none; } </style>';
        
    }

}

require('showActivationNotice.php');

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

require('listTable.php');

/*
 * Starts our plugin class, easy!
 */
new Formaloo();

