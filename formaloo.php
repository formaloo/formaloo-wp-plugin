<?php
/**
 * Plugin Name:       Formaloo Form Builder
 * Description:       Easily embed Formaloo forms into your blog or WP pages.
 * Version:           2.0.0.0
 * Author:            Formaloo team
 * Author URI:        https://en.formaloo.com/
 * Text Domain:       formaloo-form-builder
 * 
 * WC requires at least: 3.5.0
 * WC tested up to: 5.0.0
 *
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/*
 * Plugin constants
 */
if(!defined('FORMALOO_PLUGIN_VERSION'))
	define('FORMALOO_PLUGIN_VERSION', '2.0.0.0');
if(!defined('FORMALOO_URL'))
	define('FORMALOO_URL', plugin_dir_url( __FILE__ ));
if(!defined('FORMALOO_PATH'))
	define('FORMALOO_PATH', plugin_dir_path( __FILE__ ));
if(!defined('FORMALOO_ENDPOINT')) {
    if (get_locale() == 'fa_IR') {
        define('FORMALOO_ENDPOINT', 'formaloo.com');
    } else {
        define('FORMALOO_ENDPOINT', 'formaloo.net');
    }
}
	
if(!defined('FORMALOO_PROTOCOL'))
    define('FORMALOO_PROTOCOL', 'https');

require_once plugin_dir_path( __FILE__ ) . '/blocks/formaloo-block.php';

require_once('editors/gutenberg.php');
require_once('editors/classicEditor.php');

require_once('pages/formsListPage.php');
require_once('pages/formResultsPage.php');
require_once('pages/templatesPage.php');
require_once('pages/feedbackWidgetPage.php');
require_once('pages/cashbackPage.php');
require_once('pages/settingsPage.php');

require_once('woocommerce/customers.php');
require_once('woocommerce/orders.php');

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

require_once('tables/listTable.php');
require_once('tables/resultsTable.php');

/* Register activation hook. */
register_activation_hook( __FILE__, 'formaloo_admin_notice_activation_hook' );
require_once('notices/activationNotice.php');

require_once('notices/pluginReview.php');

/*
 * Main class
 */
/**
 * Class Formaloo_Main_Class
 *
 * This class creates the option page and add the web app script
 */
class Formaloo_Main_Class {

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
        add_action('admin_menu',                array($this,'addAdminMenu'));
        add_action('wp_ajax_store_admin_data',  array($this,'storeAdminData'));
        add_action('wp_ajax_get_formaloo_shortcode',  array($this,'getFormalooShortcode'));
        add_action('admin_enqueue_scripts',     array($this,'addAdminScripts'));

        add_action('wp_print_scripts', array($this,'formalooClipboadPrintScripts'));


        add_shortcode('formaloo', array($this, 'formaloo_show_form_shortcode'));

        add_filter( 'submenu_file', array($this, 'formaloo_wp_admin_submenu_filter'));

        add_action('admin_notices', array($this, 'formaloo_invalid_token_admin_notice'));

    }
    
	/**
	 * Returns the support url
     *
     * @return array
	 */
	protected function getSupportUrl() {
        if (get_locale() == 'fa_IR') {
            return FORMALOO_PROTOCOL . '://formaloo.com/contact-us/';
        } else {
            return FORMALOO_PROTOCOL . '://en.formaloo.com/contact/';
        }
    }


    public function formaloo_show_form_shortcode($atts) {
        // extract the attributes into variables
        extract(shortcode_atts(array(
            'slug'              => 'slug',
            'address'           => 'address',
            'type'              => 'link',
            'link_title'        => __('Show Form', 'formaloo-form-builder'),
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
                } else {
                    $show_title = '';
                }
                if ($atts['show_descr'] == 'no') {
                    $show_desc =  '#main-form .formz-form-desc { display: none; }';
                } else {
                    $show_desc = '';
                }
                if ($atts['show_logo'] == 'no') {
                    $show_logo =  '#main-form .formz-main-logo { display: none; }';
                } else {
                    $show_logo = '';
                }
                return '
                    <style>'. $show_title . $show_desc . $show_logo .'</style>
                    <div id="formz-wrapper" data-formz-slug="'. $atts['slug'] .'"></div>
                    '. wp_enqueue_script ( 'formaloo-form-js-script', FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT . '/istatic/js/main.js' );
        }

    }

    // hooks your functions into the correct filters
    function formaloo_add_mce_button() {
        // check user permissions
        if ( !current_user_can( 'edit_posts' ) &&  !current_user_can( 'edit_pages' ) ) {
                return;
        }
        // check if WYSIWYG is enabled
        if ( 'true' == get_user_option( 'rich_editing' ) ) {
            add_filter( 'mce_external_plugins', array($this,'formaloo_add_tinymce_plugin') );
            add_filter( 'mce_buttons', array($this,'formaloo_register_mce_button') );
        }
    }

    // register new button in the editor
    function formaloo_register_mce_button( $buttons ) {
        array_push( $buttons, 'formaloo_mce_button' );
        return $buttons;
    }

    // declare a script for the new button
    // the script will insert the shortcode on the click event
    function formaloo_add_tinymce_plugin( $plugin_array ) {
        $plugin_array['formaloo_mce_button'] = FORMALOO_URL . 'assets/js/formaloo-mce-button.js';
        return $plugin_array;
    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page() {
        $data = $this->getData();
        $formListTable = new Formaloo_Forms_List_Table();
        $formData = $this->getForms($data['api_key'], $data['api_token'], $formListTable->get_pagenum());
        $formListTable->setFormData($formData);
        $formListTable->setPrivateKey($data['api_token']);
        $formListTable->prepare_items();
        $formListTable->display();
    }

    public function results_table_page($slug) {
        $results = array();
        $data = $this->getData();
        $api_token = $data['api_token'];
        $api_key = $data['api_key'];
        $resultListTable = new Formaloo_Results_List_Table();
        
        $api_url = FORMALOO_PROTOCOL. '://api.'. FORMALOO_ENDPOINT .'/v1/forms/form/'. $slug .'/submits/?page='. $resultListTable->get_pagenum();
  
        $response = wp_remote_get( $api_url ,
        array( 'timeout' => 10,
       'headers' => array( 'x-api-key' => $api_key,
                          'Authorization'=> 'Token ' . $api_token ) 
        ));
  
        if (is_array($response) && !is_wp_error($response)) {
          $results = json_decode($response['body'], true);
        }

        $resultListTable->setFormData($results);
        $resultListTable->setPrivateKey($api_token);
        $resultListTable->prepare_items();
        ?>
        <?php $resultListTable->display(); ?>
        <?php
    }

    public function get_user_profile_name() {
        $result = array();
        $data = $this->getData();
        $api_token = $data['api_token'];
        $api_key = $data['api_key'];
        
        $api_url = FORMALOO_PROTOCOL. '://api.'. FORMALOO_ENDPOINT .'/v2/profiles/profile/me/';
  
        $response = wp_remote_get( $api_url ,
        array( 'timeout' => 10,
       'headers' => array( 'x-api-key' => $api_key,
                          'Authorization'=> 'Token ' . $api_token ) 
        ));
  
        if (is_array($response) && !is_wp_error($response)) {
          $result = json_decode($response['body'], true);
        }

        return $result['data']['profile']['first_name'];
    }

	/**
	 * Returns the saved options data as an array
     *
     * @return array
	 */
	protected function getData() {
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
            $show_title = (isset($fields['show_title']) && !empty($fields['show_title'])) ? $fields['show_title'] : 'yes';
            $output = $output . ' show_title="'. $show_title .'"';

            $show_desc = (isset($fields['show_descr']) && !empty($fields['show_descr'])) ? $fields['show_descr'] : 'yes';
            $output = $output . ' show_descr="'. $show_desc .'"';

            $show_logo = (isset($fields['show_logo']) && !empty($fields['show_logo'])) ? $fields['show_logo'] : 'yes';
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

    // inline scripts WP >= 4.5
    function formalooClipboardInlineScript() {
        
        $wp_version = get_bloginfo('version');
        
        if (version_compare($wp_version, '4.5', '>=')) {
            
            wp_add_inline_script( 'clipboard', 'new ClipboardJS(".formaloo_clipboard_btn");' );
            wp_add_inline_script( 'clipboard', 'new ClipboardJS(".formaloo_widget_clipboard_btn");' );     
            
        }
        
    }

    // inline scripts WP < 4.5
    function formalooClipboadPrintScripts() { 
        
        $wp_version = get_bloginfo('version');
        
        if (version_compare($wp_version, '4.5', '<')) {
            
            ?>
            
            <script>
                new ClipboardJS(".formaloo_clipboard_btn");
                new ClipboardJS(".formaloo_widget_clipboard_btn");
            </script>
            
            <?php
            
        }
        
    }

	/**
	 * Adds Admin Scripts for the Ajax call
	 */
	public function addAdminScripts() {

        wp_enqueue_style( 'wp-color-picker' );

        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');

        wp_enqueue_script( 'clipboard');
        
        $this->formalooClipboardInlineScript();

	    wp_enqueue_style('formaloo-admin', FORMALOO_URL. 'assets/css/admin.css', false, FORMALOO_PLUGIN_VERSION);
        wp_enqueue_script('formaloo-admin', FORMALOO_URL. 'assets/js/admin.js', array('wp-color-picker'), FORMALOO_PLUGIN_VERSION);

        if (get_locale() == 'fa_IR') {
            wp_enqueue_style('formaloo-admin-rtl', FORMALOO_URL. 'assets/css/rtl.css', false, FORMALOO_PLUGIN_VERSION);
        }

        $data = $this->getData();

		$admin_options = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
            '_nonce'   => wp_create_nonce( $this->_nonce ),
            'api_token' => $data['api_token'],
            'api_key' => $data['api_key'],
            'protocol' => FORMALOO_PROTOCOL,
            'endpoint_url' => FORMALOO_ENDPOINT,
            'forms_list' => $this->getForms($data['api_key'], $data['api_token']),
            'async_excel_export_message' => __('Your excel file will be ready in a couple of minutes. Please refresh this page to see the result.', 'formaloo-form-builder')
        );
        
        wp_localize_script('formaloo-admin', 'formaloo_exchanger', $admin_options);

	}

	/**
	 * Adds the Formaloo label to the WordPress Admin Sidebar Menu
	 */
	public function addAdminMenu() {
        global $submenu;
        $formalooIconBase64 = 'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDIzLjEuMSwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IgoJIHZpZXdCb3g9IjAgMCAyMCAyMCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMjAgMjA7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4KPHBhdGggZD0iTTE0LjgsM0w2LjcsM2MtMS42LDAtMywxLjMtMywyLjlsMCw4LjFjMCwxLjYsMS4zLDMsMi45LDNsOC4xLDBjMS42LDAsMy0xLjMsMy0yLjlsMC04LjFDMTcuNyw0LjQsMTYuNCwzLDE0LjgsM3oKCSBNOC43LDEzLjJjMCwwLjQtMC4zLDAuNi0wLjYsMC42bC0wLjYsMGMtMC40LDAtMC42LTAuMy0wLjYtMC42bDAtMC42YzAtMC40LDAuMy0wLjYsMC42LTAuNmwwLjYsMGMwLjQsMCwwLjYsMC4zLDAuNiwwLjZMOC43LDEzLjIKCXogTTExLjcsMTAuNGMwLDAuMy0wLjMsMC42LTAuNiwwLjZsLTMuNywwYy0wLjMsMC0wLjYtMC4zLTAuNi0wLjZsMC0wLjhDNi45LDkuMyw3LjEsOSw3LjQsOWwzLjcsMGMwLjMsMCwwLjYsMC4zLDAuNiwwLjYKCUwxMS43LDEwLjR6IE0xNC42LDcuNWMwLDAuMy0wLjMsMC42LTAuNiwwLjZMNy41LDhDNy4xLDgsNi45LDcuOCw2LjksNy40bDAtMC43YzAtMC4zLDAuMy0wLjYsMC42LTAuNmw2LjUsMGMwLjMsMCwwLjYsMC4zLDAuNiwwLjYKCUwxNC42LDcuNXoiLz4KPC9zdmc+Cg==';

		add_menu_page(
			__( 'Formaloo', 'formaloo-form-builder' ),
			__( 'Formaloo', 'formaloo-form-builder' ),
			'manage_options',
			'formaloo',
			array(new Formaloo_Forms_List_Page(), 'formsListPage'),
            'data:image/svg+xml;base64,' . $formalooIconBase64
        );

        add_submenu_page(
            'formaloo',
            __( 'Templates', 'formaloo-form-builder' ),
            __( 'Templates', 'formaloo-form-builder' ),
            'manage_options',
            'formaloo-templates-page',
            array(new Formaloo_Templates_Page(), 'templatesPage')
        );

        add_submenu_page(
            'formaloo',
            __( 'Feedback Widget', 'formaloo-form-builder' ),
            __( 'Feedback Widget', 'formaloo-form-builder' ),
            'manage_options',
            'formaloo-feedback-widget-page',
            array(new Formaloo_Feedback_Widget_Page(), 'feedbackWidgetPage')
        );

        /**
         * Check if WooCommerce is active
         **/
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            add_submenu_page(
                'formaloo',
                __( 'Cashback for WooCommerce (New)', 'formaloo-form-builder' ),
                __( 'Cashback for WooCommerce (New)', 'formaloo-form-builder' ),
                'manage_options',
                'formaloo-cashback-page',
                array(new Formaloo_Cashback_Page(), 'cashbackPage')
            );
        }
        
        add_submenu_page(
            'formaloo',
            __( 'Settings', 'formaloo-form-builder' ),
            __( 'Settings', 'formaloo-form-builder' ),
            'manage_options',
            'formaloo-settings-page',
            array(new Formaloo_Settings_Page(), 'settingsPage')
        );

        add_submenu_page(
            'formaloo',
            __( 'Results', 'formaloo-form-builder' ),
            '',
            'manage_options',
            'formaloo-results-page',
            array(new Formaloo_Form_Results_Page(), 'formResultsPage')
        );

        $submenu['formaloo'][0][0] = __( 'My Forms', 'formaloo-form-builder' );

    }

    function formaloo_wp_admin_submenu_filter( $submenu_file ) {

        global $plugin_page;
    
        $hidden_submenus = array(
            'formaloo-results-page' => true,
        );
    
        // Select another submenu item to highlight (optional).
        if ( $plugin_page && isset( $hidden_submenus[ $plugin_page ] ) ) {
            $submenu_file = 'formaloo-results-page';
        }
    
        // Hide the submenu.
        foreach ( $hidden_submenus as $submenu => $unused ) {
            remove_submenu_page( 'formaloo', $submenu );
        }
    
        return $submenu_file;
    }

	/**
	 * Make an API call to the Formaloo API and returns the response
     *
     * @param $api_token string
     *
     *
     * @return array
	 */
	protected function getForms($api_key, $api_token, $pageNum = 1) {
        
        $data = array();
        
        $api_url = FORMALOO_PROTOCOL. '://api.'. FORMALOO_ENDPOINT .'/v2/forms/list/?page='. $pageNum;

        $response = wp_remote_get( $api_url ,
        array( 'timeout' => 10,
       'headers' => array( 'x-api-key' => $api_key,
                          'Authorization'=> 'Token ' . $api_token ) 
        ));

	    if (is_array($response) && !is_wp_error($response)) {
            $data = json_decode($response['body'], true);
        }
        
	    return $data;

    }

    /**
	 * Display custom admin notice
	 */
    function formaloo_invalid_token_admin_notice() { 
        $currentScreen = get_current_screen();
        $data = $this->getData();
        $forms = $this->getForms($data['api_key'], $data['api_token']);
        $currentGetFormsStatus = isset($forms['status'])? $forms['status'] : 401;
        if ($currentGetFormsStatus == 401 && $currentScreen->id == 'toplevel_page_formaloo') {
        ?>

            <div class="notice notice-error is-dismissible inline">
                <p><?php echo __('Invalid API Token or API Key! Please visit your', 'formaloo-form-builder') . ' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/" target="_blank">'. __('Formaloo dashboard here', 'formaloo-form-builder') .'</a>'. ' ' . __('to get a new one.', 'formaloo-form-builder'); ?></p>
            </div>
	
        <?php 
        }
    }

	/**
     * Get a Dashicon for a given status
     *
	 * @param $valid boolean
     *
     * @return string
	 */
    protected function getStatusIcon($valid) {

        return ($valid) ? '<span class="dashicons dashicons-yes success-message"></span>' : '<span class="dashicons dashicons-no-alt error-message"></span>';

    }

    /**
     * Show/hide a div for a given status
     *
	 * @param $valid boolean
     *
     * @return string
	 */
    protected function getStatusDiv($valid) {

        return ($valid) ? '' : '<div class="inside formaloo-sign-up-wrapper"> <p><strong>'. __('Don\'t have a Formaloo account?', 'formaloo-form-builder') .'</strong></p> <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/account/signUp/" target="_blank">'. __('Try one for Free!', 'formaloo-form-builder') .'</a> </div>';

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

new Formaloo_Plugin_Review( array(
	'slug'        => 'formaloo-form-builder',  // The plugin slug
	'name'        => 'Form Builder by Formaloo', // The plugin name
	'time_limit'  => WEEK_IN_SECONDS,     // The time limit at which notice is shown
) );

/*
 * Starts our plugin class, easy!
 */
new Formaloo_Main_Class();

function formaloo_settings_link($links) { 
    $settings_link = '<a href="admin.php?page=formaloo-settings-page">Settings</a>'; 
    array_unshift($links, $settings_link); 
    return $links; 
}
$plugin = plugin_basename(__FILE__); 
add_filter('plugin_action_links_'. $plugin, 'formaloo_settings_link' );