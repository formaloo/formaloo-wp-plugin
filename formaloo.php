<?php
/**
 * Plugin Name:       Formaloo Form Builder
 * Description:       Easily embed Formaloo forms into your blog or WP pages.
 * Version:           1.5.0.1
 * Author:            Formaloo team
 * Author URI:        https://formaloo.net/
 * Text Domain:       formaloo
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */


/*
 * Plugin constants
 */
if(!defined('FORMALOO_PLUGIN_VERSION'))
	define('FORMALOO_PLUGIN_VERSION', '1.5.0.1');
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

require_once('gutenberg.php');

add_action('plugins_loaded', array('Formaloo_Main_Class', 'loadTextDomain'));

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
        // add_action('wp_footer',                 array($this,'addFooterCode'));

        add_action('admin_menu',                array($this,'addAdminMenu'));
        add_action('wp_ajax_store_admin_data',  array($this,'storeAdminData'));
        add_action('wp_ajax_get_formaloo_shortcode',  array($this,'getFormalooShortcode'));
        add_action('admin_enqueue_scripts',     array($this,'addAdminScripts'));

        add_shortcode('formaloo', array($this, 'formaloo_show_form_shortcode'));

        add_filter( 'submenu_file', array($this, 'formaloo_wp_admin_submenu_filter'));

        add_action('admin_notices', array($this, 'formaloo_invalid_token_admin_notice'));

    }

    public static function loadTextDomain() {
        load_plugin_textdomain('formaloo', false, dirname(plugin_basename(__FILE__ )) . '/languages/');
    }

    public function formaloo_show_form_shortcode($atts) {
        // extract the attributes into variables
        extract(shortcode_atts(array(
            'slug'              => 'slug',
            'address'           => 'address',
            'type'              => 'link',
            'link_title'        => __('Show Form', 'formaloo'),
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

	/**
	 * Adds Admin Scripts for the Ajax call
	 */
	public function addAdminScripts() {

        wp_enqueue_style( 'wp-color-picker' );

        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');

        wp_enqueue_script( 'clipboard');
        wp_add_inline_script( 'clipboard', 'new ClipboardJS(".formaloo_clipboard_btn");' );
        wp_add_inline_script( 'clipboard', 'new ClipboardJS(".formaloo_widget_clipboard_btn");' );     

	    wp_enqueue_style('formaloo-admin', FORMALOO_URL. 'assets/css/admin.css', false, 1.0);
        wp_enqueue_script('formaloo-admin', FORMALOO_URL. 'assets/js/admin.js', array('wp-color-picker'), 1.0);

        if (get_locale() == 'fa_IR') {
            wp_enqueue_style('formaloo-admin-rtl', FORMALOO_URL. 'assets/css/rtl.css', false, 1.0);
        }

        $data = $this->getData();

		$admin_options = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
            '_nonce'   => wp_create_nonce( $this->_nonce ),
            'api_token' => $data['api_token'],
            'api_key' => $data['api_key'],
            'protocol' => FORMALOO_PROTOCOL,
            'endpoint_url' => FORMALOO_ENDPOINT
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
			__( 'Formaloo', 'formaloo' ),
			__( 'Formaloo', 'formaloo' ),
			'manage_options',
			'formaloo',
			array($this, 'formsListPage'),
            'data:image/svg+xml;base64,' . $formalooIconBase64
        );

        add_submenu_page(
            'formaloo',
            __( 'Feedback Widget', 'formaloo' ),
            __( 'Feedback Widget', 'formaloo' ),
            'manage_options',
            'formaloo-feedback-widget-page',
            array($this, 'feedbackWidgetPage')
        );
        
        add_submenu_page(
            'formaloo',
            __( 'Settings', 'formaloo' ),
            __( 'Settings', 'formaloo' ),
            'manage_options',
            'formaloo-settings-page',
            array($this, 'settingsPage')
        );

        add_submenu_page(
            'formaloo',
            __( 'Results', 'formaloo' ),
            '',
            'manage_options',
            'formaloo-results-page',
            array($this, 'formResultsPage')
        );

        $submenu['formaloo'][0][0] = __( 'My Forms', 'formaloo' );

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
	private function getForms($api_key, $api_token, $pageNum = 1) {
        
        $data = array();
        
        $api_url = FORMALOO_PROTOCOL. '://api.'. FORMALOO_ENDPOINT .'/v1/forms/list/?page='. $pageNum;

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

    // display custom admin notice
    function formaloo_invalid_token_admin_notice() { 
        $currentScreen = get_current_screen();
        $data = $this->getData();
        $forms = $this->getForms($data['api_key'], $data['api_token']);
        $currentGetFormsStatus = isset($forms['status'])? $forms['status'] : 401;
        if ($currentGetFormsStatus == 401 && $currentScreen->id == 'toplevel_page_formaloo') {
        ?>

            <div class="notice notice-error is-dismissible inline">
                <p><?php echo __('Invalid API Token or API Key! Please visit your','formaloo') . ' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/" target="_blank">'. __('Formaloo dashboard here','formaloo') .'</a>'. ' ' . __('to get a new one.','formaloo'); ?></p>
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
    private function getStatusIcon($valid) {

        return ($valid) ? '<span class="dashicons dashicons-yes success-message"></span>' : '<span class="dashicons dashicons-no-alt error-message"></span>';

    }

    /**
     * Show/hide a div for a given status
     *
	 * @param $valid boolean
     *
     * @return string
	 */
    private function getStatusDiv($valid) {

        return ($valid) ? '' : '<div class="inside formaloo-sign-up-wrapper"> <p><strong>'. __('Don\'t have a Formaloo account?', 'formaloo') .'</strong></p> <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/account/signUp/" target="_blank">'. __('Try one for Free!', 'formaloo') .'</a> </div>';

    }

    /**
	 * Outputs the Admin Dashboard layout containing the form with all its options
     *
     * @return void
	 */
	public function formsListPage() {

        $data = $this->getData();
        $api_response = $this->getForms($data['api_key'], $data['api_token']);
	    $not_ready = (empty($data['api_token']) || empty($data['api_key']) || empty($api_response) || isset($api_response['error']) || $api_response['status'] != 200);

	    ?>

		<div class="wrap">

            <div id="form-show-edit" style="display:none;">
            </div>

            <div id="form-show-create-form" style="display:none;">

            </div>
            
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
                                    <option value="link" <?php echo (isset($data['show_type']) && $data['show_type'] === 'link') ? 'selected' : ''; ?>>
                                        <?php _e( 'Link', 'formaloo' ); ?>
                                    </option>
                                    <option value="iframe" <?php echo (isset($data['show_type']) && $data['show_type'] === 'iframe') ? 'selected' : ''; ?>>
                                        <?php _e( 'iFrame', 'formaloo' ); ?>
                                    </option>
                                    <option value="script" <?php echo (!isset($data['show_type']) || (isset($data['show_type']) && $data['show_type'] === 'script')) ? 'selected' : ''; ?>>
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
                                        value="<?php echo (isset($data['link_title'])) ? esc_attr__($data['link_title']) : __('Show Form','formaloo'); ?>"/>
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
                            <input type="radio" name="formaloo_show_logo" id="formaloo_show_logo_yes" value="yes" checked /> <label for = "formaloo_show_logo"><?php _e('Yes','formaloo'); ?></label> <br>
                            <input type="radio" name="formaloo_show_logo" id="formaloo_show_logo_no" value="no" /> <label for = "formaloo_show_logo"><?php _e('No','formaloo'); ?></label> 
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
                            <input type="radio" name="formaloo_show_title" id="formaloo_show_title_yes" value="yes" checked /> <label for = "formaloo_show_title"><?php _e('Yes','formaloo'); ?></label> <br>
                            <input type="radio" name="formaloo_show_title" id="formaloo_show_title_no" value="no" <?php // checked( 'no' == $data['show_title'] ); ?> /> <label for = "formaloo_show_title"><?php _e('No','formaloo'); ?></label> 
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
                            <input type="radio" name="formaloo_show_descr" id="formaloo_show_descr_yes" value="yes" checked /> <label for = "formaloo_show_descr"><?php _e('Yes','formaloo'); ?></label> <br>
                            <input type="radio" name="formaloo_show_descr" id="formaloo_show_descr_no" value="no" /> <label for = "formaloo_show_descr"><?php _e('No','formaloo'); ?></label> 
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="formaloo_clipboard_wrapper formaloo_hidden">
                    <input id="formaloo_shortcode_pre" type="text" class="regular-text" placeholder="<?php _e('Shortcode will appear here','formaloo'); ?>">
                    <button class="button button-primary formaloo_clipboard_btn" data-clipboard-target="#formaloo_shortcode_pre">
                        <img src="<?php echo FORMALOO_URL ?>/assets/images/clippy.svg" width="13" alt="Copy to clipboard">
                    </button>  
                </div>
                <p>
                <?php _e('Copy the shortcode above then go to your post/page editor. If it is Gutenberg Editor, add a Shortcode block and paste the shortcode. If it is Classic Editor, choose the Text tab (instead of Visual tab) tab and paste the shortcode wherever you desire.' ,'formaloo') ?>
                <a href="https://en.support.wordpress.com/shortcodes/" target="_blank"> <?php _e( 'More Info', 'formaloo' ); ?> </a>
                </p>
                <?php if (!$not_ready): ?>
                    <div class="formaloo-shortcode-post-row">
                        <button class="button button-primary formaloo-admin-save my-10" type="submit">
                            <?php _e( 'Get shortcode', 'formaloo' ); ?>
                        </button>
                    </div>
                <?php endif; ?>
                </form>             
                <script>

                    function getRowInfo($slug, $address) {
                        jQuery('.formaloo_clipboard_wrapper').addClass('formaloo_hidden');
                        jQuery(".form-table").append('<input name="formaloo_form_slug" id="formaloo_form_slug" type="hidden" value="' + $slug + '" />');
                        jQuery(".form-table").append('<input name="formaloo_form_address" id="formaloo_form_address" type="hidden" value="' + $address + '" />');
                        jQuery('.formaloo-shortcode-post-row').find('a').remove();
                        jQuery(".formaloo-shortcode-post-row").append('<a href="<?php echo FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT ?>/dashboard/my-forms/' + $slug + '/share" target="_blank"><?php _e( 'Additional Settings', 'formaloo' ); ?></a>');
                    }

                    function showEditFormWith($protocol, $url, $slug) {
                        jQuery("#form-show-edit").append('<iframe id="edit-form-iframe" width="100%" height="100%" src="'+ $protocol +'://'+ $url +'/dashboard/my-forms/'+ $slug +'/edit" frameborder="0" onload="resizeIframe();">');
                    }

                    function showCreateForm($protocol, $url, $slug) {
                        jQuery("#form-show-create-form").append('<iframe id="edit-form-iframe" width="100%" height="100%" src="<?php echo FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT ?>/formMaker/newForm/" frameborder="0" onload="resizeIframe();">');
                    }

                    function resizeIframe() {
                        var TB_WIDTH = jQuery(document).width();
                        jQuery("#TB_window").animate({
                            width: TB_WIDTH + 'px',
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

                <div class="form-group inside">
                    <div class="formaloo-api-settings-top-wrapper">
                        <img src="<?php echo FORMALOO_URL ?>assets/images/Formaloo_Logo.png" alt="formaloo-logo">
                        <h1 class="formaloo-heading">
                            <?php _e('My Forms', 'formaloo'); ?>
                        </h1>
                    </div>
                </div>

                <div class="form-group inside">
                    
                    <?php if ($not_ready): ?>
                        <p>
                            <?php echo $this->getStatusIcon(!$not_ready) . __("Make sure you have a Formaloo account first, it's free! 👍","formaloo"); ?>
                            <?php echo __('You can','formaloo') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/" target="_blank">'. __('create an account here','formaloo') .'</a>.'; ?>
                            <br>
                            <?php echo __('If so you can find your API Key & API Token from your','formaloo') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/profile/" target="_blank">'. __('profile page,','formaloo') .'</a> '. __('and enter it on the','formaloo') .' <a href="?page=formaloo-settings-page">'. __('settings page','formaloo') .'</a>.'; ?>
                        </p>
                    <?php else: ?>
                        <?php echo $this->getStatusIcon(!$not_ready); ?>
                        <?php 
                            $formaloo_first_name = $this->get_user_profile_name();
                            $formaloo_user_name = empty($formaloo_first_name) ? __('User','formaloo') : $formaloo_first_name;
                            echo __('Hello Dear','formaloo'). ' ' . $formaloo_user_name .'! '. __('You can edit or view your forms right here or you can access', 'formaloo') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/" target="_blank">'. __('your full dashboard here','formaloo') .'</a>.'; 
                        ?>
                    <?php endif; ?>
                </div>

	            <?php if (!empty($data['api_token']) /*&& !empty($data['public_key'])*/): ?>

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
                            <h3 class="formaloo-heading">
                                <span class="dashicons dashicons-feedback"></span>
                                <?php _e('Your Forms', 'formaloo'); ?>
                            </h3>
                            <div class="formaloo-create-form-wrapper">
                                <div class="formaloo-create-form">
                                    <a href="#TB_inline?&width=100vw&height=100vh&inlineId=form-show-create-form" title="<?php _e('Create a form', 'formaloo'); ?>" target="_blank" class="button button-primary formaloo-create-new-form-link thickbox" onclick = "showCreateForm()"><span class="dashicons dashicons-plus"></span> <?php _e('Create a new form', 'formaloo') ?></a>
                                </div>
                                <div class="formaloo-create-form">
                                    <a href="<?php echo admin_url( "admin.php?page=formaloo-feedback-widget-page" ) ?>" class="button button-secondary formaloo-create-new-form-link"><span class="dashicons dashicons-star-half"></span> <?php _e('Create a feedback widget', 'formaloo'); ?></a>
                                </div>
                            </div>
                            <?php $this->list_table_page(); ?>
                        </div>

                    <?php endif; ?>

                <?php endif; ?>

            </form>
		</div>

		<?php

    }

    public function formResultsPage() {
        // $data = $this->getData();
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
                            <?php _e( 'An error happened on the WordPress side.', 'formaloo' ); ?>
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
                            <a href="<?php echo admin_url( "admin.php?page=formaloo" ) ?>"><?php _e('My Forms', 'formaloo'); ?></a> / 
                            <?php _e('Your Form Results', 'formaloo'); ?>
                        </h3>
                        <?php $this->results_table_page(esc_attr($_GET['results_slug'])); ?>
                    </div>

                <?php endif; ?>

            </form>
		</div>

		<?php
    }

	/**
	 * Outputs the Admin Dashboard layout containing the form with all its options
     *
     * @return void
	 */
	public function settingsPage() {

        $data = $this->getData();

	    $not_ready = (empty($data['api_token']) || empty($data['api_key']));

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
                            <?php _e('Setup Formaloo', 'formaloo'); ?>
                        </h1>
                    </div>

                    <h3 class="formaloo-heading">
		                <?php echo $this->getStatusIcon(!$not_ready); ?>
		                <?php _e('Welcome to Formaloo!', 'formaloo'); ?>
                    </h3>

	                <?php if ($not_ready): ?>
                        <p>
                            <?php echo __('To get started, we\'ll need to access your Formaloo account with an','formaloo') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/profile/" target="_blank">'. __('API Key & API Token','formaloo') .'</a>. '. __('Paste your Formaloo API Key & API Token, and click','formaloo') .' <strong>'. __('Connect','formaloo') .'</strong> '. __('to continue','formaloo') .'.'; ?>
                        </p>
                    <?php else: ?>
                        <?php echo __('You can access your','formaloo') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/" target="_blank">'. __('Formaloo dashboard here','formaloo') .'</a>.'; ?>  
                    <?php endif; ?>
                    <?php echo $this->getStatusDiv(!$not_ready); ?>

                    <table class="form-table formaloo-api-settings-table">
                        <tbody>
                            <tr>
                                <td scope="row">
                                    <label><?php _e( 'API Key', 'formaloo' ); ?></label>
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
                                    <label><?php _e( 'API Token', 'formaloo' ); ?></label>
                                </td>
                                <td>
                                    <input name="formaloo_api_token"
                                           id="formaloo_api_token"
                                           class="regular-text"
                                           type="text"
                                           value="<?php echo (isset($data['api_token'])) ? $data['api_token'] : ''; ?>"/>
                                </td>
                            </tr>
                            <tr id="formaloo-settings-submit-row">
                                <td>
                                    <button class="button button-primary formaloo-admin-save formaloo-button-black" type="submit">
                                        <?php ($not_ready) ? _e( 'Connect', 'formaloo' ) : _e( 'Save', 'formaloo' ) ?>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <a href="<?php echo esc_url( FORMALOO_PROTOCOL . '://web.' . FORMALOO_ENDPOINT . '/contact/' ); ?>" target="_blank"><?php _e( 'Need Support? Feel free to contact us', 'formaloo' ); ?></a>
                </div>

            </form>

		</div>

		<?php

    }

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
                    
                    <div class="formaloo-feedback-widget-loading-gif-wrapper">
                        <img src="<?php echo FORMALOO_URL ?>assets/images/loading.gif" alt="feedback-widget-loading-gif">
                    </div>

                    <div id="formaloo-feedback-widget-show-options" style="display:none;">
                        <table class="form-table formaloo-feedback-widget-show-options-table">
                            <tbody>
                                <tr>
                                    <td scope="row">
                                        <label><strong><?php _e( 'As a Link', 'formaloo' ); ?></strong></label><br>
                                        <small><?php _e( 'Share this URL with others to view the form directly', 'formaloo' ); ?></small>
                                    </td>
                                    <td>
                                        <a href="" target="_blank" id="formaloo-feedback-widget"></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td scope="row">
                                        <label><strong><?php _e( 'As a Script Tag', 'formaloo' ); ?></strong></label><br>
                                        <small><?php _e( 'Using this method, the form will become a part of your website\'s markup. To use this, put the code snippet in your website footer:', 'formaloo' ); ?></small><br><br>
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
                            <?php _e('Feedback Widget Creator', 'formaloo'); ?>
                        </h1>
                    </div>

                    <p class="notice notice-error formaloo-feedback-widget-notice"></p> 

                    <div class="formaloo-feedback-wdiget-top-info">
                        <img src="<?php echo FORMALOO_URL ?>assets/images/feedback_widget.png" alt="feedback-widget-design">
                        <h3 class="formaloo-heading">
		                    <?php _e('Create a feedback widget for your website in less than 1 minute:', 'formaloo'); ?>
                        </h3>
                    </div>

	                <?php if ($not_ready): ?>
                        <p>
                            <?php echo __('To get started, we\'ll need to access your Formaloo account with an','formaloo') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/profile/" target="_blank">'. __('API Key & API Token','formaloo') .'</a>. '. __('Paste your Formaloo API Key & API Token, and click','formaloo') .' <strong>'. __('Connect','formaloo') .'</strong> '. __('to continue','formaloo') .'.'; ?>
                        </p>
                    <?php else: ?>
                        <?php // echo __('You can access your','formaloo') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/" target="_blank">'. __('Formaloo dashboard here','formaloo') .'</a>.'; ?>  
                    <?php endif; ?>
                    <?php echo $this->getStatusDiv(!$not_ready); ?>

                    <input type="hidden" id="formaloo_feedback_widget_form_slug" name="formaloo_feedback_widget_form_slug" value="">
                    <input type="hidden" id="formaloo_feedback_widget_form_address" name="formaloo_feedback_widget_form_address" value="">
                    <input type="hidden" id="formaloo_feedback_widget_nps_field_slug" name="formaloo_feedback_widget_nps_field_slug" value="">
                    <input type="hidden" id="formaloo_feedback_widget_text_field_slug" name="formaloo_feedback_widget_text_field_slug" value="">

                    <table class="form-table formaloo-feedback-widget-settings-table">
                        <tbody>
                            <tr>
                                <td scope="row">
                                    <label><strong><?php _e( 'Button Text', 'formaloo' ); ?></strong></label>
                                </td>
                                <td>
                                    <input name="formaloo_feedback_widget_button_text"
                                           id="formaloo_feedback_widget_button_text"
                                           class="regular-text"
                                           type="text"
                                           value=""
                                           placeholder="<?php _e( 'Button Text', 'formaloo' ); ?>"/>
                                </td>
                            </tr>
                            <tr id="formaloo_feedback_widget_position_row">
                                <td scope="row">
                                    <label>
                                        <strong>
                                            <?php _e( 'Widget Position on Screen', 'formaloo' ); ?>
                                        </strong>
                                    </label>
                                </td>
                                <td>
                                <fieldset>
                                    <input type="radio" name="formaloo_feedback_widget_position" id="formaloo_feedback_widget_position_left" value="left" checked /> <label for = "formaloo_feedback_widget_position"><?php _e('Left','formaloo'); ?></label><br>
                                    <input type="radio" name="formaloo_feedback_widget_position" id="formaloo_feedback_widget_position_right" value="right" /> <label for = "formaloo_feedback_widget_position"><?php _e('Right','formaloo'); ?></label><br>
                                    <input type="radio" name="formaloo_feedback_widget_position" id="formaloo_feedback_widget_position_bottom_left" value="bottom_left" /> <label for = "formaloo_feedback_widget_position"><?php _e('Bottom Left','formaloo'); ?></label><br>
                                    <input type="radio" name="formaloo_feedback_widget_position" id="formaloo_feedback_widget_position_bottom_right" value="bottom_right" /> <label for = "formaloo_feedback_widget_position"><?php _e('Bottom Right','formaloo'); ?></label> 
                                </fieldset>
                                </td>
                            </tr>
                            <tr>
                                <td scope="row">
                                    <label><strong><?php _e( 'NPS Choices Icon', 'formaloo' ); ?></strong></label>
                                </td>
                                <td>
                                <fieldset class="formaloo_feedback_widget_choice_wrapper">
                                    <div class="formaloo_feedback_widget_choice_icon formaloo_feedback_widget_choice_selected" id="heart">
                                        <img src="<?php echo FORMALOO_URL ?>assets/images/widget_icons/fillHeart.png" alt="Heart Icon">
                                    </div>
                                    <div class="formaloo_feedback_widget_choice_icon" id="star">
                                        <img src="<?php echo FORMALOO_URL ?>assets/images/widget_icons/fillStar.svg" alt="Star Icon">
                                    </div>
                                    <div class="formaloo_feedback_widget_choice_icon" id="funny_face">
                                        <img src="<?php echo FORMALOO_URL ?>assets/images/widget_icons/FSmile.svg" alt="Funny Face Icon">
                                    </div>
                                    <div class="formaloo_feedback_widget_choice_icon" id="monster">
                                        <img src="<?php echo FORMALOO_URL ?>assets/images/widget_icons/MSmile.svg" alt="Monster Icon">
                                    </div>
                                    <div class="formaloo_feedback_widget_choice_icon" id="flat_face">
                                        <img src="<?php echo FORMALOO_URL ?>assets/images/widget_icons/FLLove.svg" alt="Flat Face Icon">
                                    </div>
                                    <div class="formaloo_feedback_widget_choice_icon" id="outlined">
                                        <img src="<?php echo FORMALOO_URL ?>assets/images/widget_icons/OSmile.svg" alt="Outlined Icon">
                                    </div>
                                </fieldset>
                                </td>
                            </tr>
                            <tr>
                                <td scope="row">
                                    <label><strong><?php _e( 'Question Title', 'formaloo' ); ?></strong></label>
                                </td>
                                <td>
                                    <input name="formaloo_feedback_widget_question_text_title"
                                           id="formaloo_feedback_widget_question_text_title"
                                           class="regular-text"
                                           type="text"
                                           value=""
                                           placeholder="<?php _e( 'Question Title', 'formaloo' ); ?>"/>
                                </td>
                            </tr>
                            <tr>
                                <td scope="row">
                                    <label><strong><?php _e( 'TextBox Placeholder', 'formaloo' ); ?></strong></label>
                                </td>
                                <td>
                                    <input name="formaloo_feedback_widget_textbox_placeholder"
                                           id="formaloo_feedback_widget_textbox_placeholder"
                                           class="regular-text"
                                           type="text"
                                           value=""
                                           placeholder="<?php _e( 'TextBox Placeholder', 'formaloo' ); ?>"/>
                                </td>
                            </tr>
                            <tr>
                                <td scope="row">
                                    <label><strong><?php _e( 'Submit Button Text', 'formaloo' ); ?></strong></label>
                                </td>
                                <td>
                                    <input name="formaloo_feedback_widget_submit_button_text"
                                           id="formaloo_feedback_widget_submit_button_text"
                                           class="regular-text"
                                           type="text"
                                           value=""
                                           placeholder="<?php _e( 'Submit Button Text', 'formaloo' ); ?>"/>
                                </td>
                            </tr>
                            <tr>
                                <td scope="row">
                                    <label><strong><?php _e( 'Button Color', 'formaloo' ); ?></strong></label>
                                </td>
                                <td>
                                    <input type="text" value="#F95E2F" class="formaloo_feedback_widget_button_color" data-default-color="#F95E2F" />
                                </td>
                            </tr>
                            <tr>
                                <td scope="row">
                                    <label><strong><?php _e( 'Success Message After Submit', 'formaloo' ); ?></strong></label>
                                </td>
                                <td>
                                    <input name="formaloo_feedback_widget_success_message_after_submit"
                                           id="formaloo_feedback_widget_success_message_after_submit"
                                           class="regular-text"
                                           type="text"
                                           value=""
                                           placeholder="<?php _e( 'Success Message After Submit', 'formaloo' ); ?>"/>
                                </td>
                            </tr>
                            <tr id="formaloo-feedback-widget-submit-row">
                                <td>
                                    <p class="submit">
                                        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes', 'formaloo' ); ?>">
                                    </p>    
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <a href="<?php echo esc_url( FORMALOO_PROTOCOL . '://web.' . FORMALOO_ENDPOINT . '/contact/' ); ?>" target="_blank"><?php _e( 'Need Support? Feel free to contact us', 'formaloo' ); ?></a>
                </div>

            </form>
                            
		</div>
        
        <script>
            jQuery(document).ready(function($){

                $('.formaloo-feedback-widget-notice').hide();

                $('.formaloo_feedback_widget_choice_icon').on("click",function(){
                    $(".formaloo_feedback_widget_choice_selected").removeClass("formaloo_feedback_widget_choice_selected");
                    $(this).addClass("formaloo_feedback_widget_choice_selected");
                    jQuery("#formaloo_feedback_widget_choice_icon_type").val($(this).find("div").attr("data-value"));
                });

                <?php 
                    $data = $this->getData();

                    $widgetId = esc_attr($_GET['widget_slug']);
                    $widgetSlugToEdit = '';
                    if ($widgetId != '') {
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
                    $.ajax({
                        url: "<?php echo esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1/forms/templates/list' ); ?>",
                        type: 'GET',
                        dataType: 'json',
                        headers: {
                            'x-api-key': '<?php echo $data['api_key']; ?>',
                            'Authorization': '<?php echo 'Token ' . $data['api_token']; ?>'
                        },
                        contentType: 'application/json; charset=utf-8',
                        success: function (result) {
                            $.each(result['data']['forms'], function(i, form) {
                                if (form['form_type'] == 'nps') {
                                    copyTemplate(form['slug']);
                                }
                            });
                        },
                        error: function (error) {
                            disableFeedbackWidgetTable();
                            var errorText = error['responseJSON']['errors']['general_errors'][0];
                            showGeneralErrors(errorText);
                            $('.formaloo-feedback-widget-loading-gif-wrapper').hide();
                        }
                    });
                }

                function copyTemplate(slug) {
                    $.ajax({
                        url: "<?php echo esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1/forms/form/copy/' ); ?>",
                        type: 'POST',
                        headers: {
                            'x-api-key': '<?php echo $data['api_key']; ?>',
                            'Authorization': '<?php echo 'Token ' . $data['api_token']; ?>'
                        },
                        data: { 'copied_form' : slug },
                        success: function (result) {
                            setupFormSettings(result['data']['form']);
                        },
                        error: function (error) {
                            disableFeedbackWidgetTable();
                            var errorText = error['responseJSON']['errors']['general_errors'][0];
                            showGeneralErrors(errorText);
                            $('.formaloo-feedback-widget-loading-gif-wrapper').hide();
                        }
                    });
                }

                function loadExistingWidget(widgetSlug){
                    $.ajax({
                        url: "<?php echo esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v2/forms/form/' ); ?>"+widgetSlug+"/",
                        type: 'GET',
                        dataType: 'json',
                        headers: {
                            'x-api-key': '<?php echo $data['api_key']; ?>',
                            'Authorization': '<?php echo 'Token ' . $data['api_token']; ?>'
                        },
                        contentType: 'application/json; charset=utf-8',
                        success: function (result) {
                            setupFormSettings(result['data']['form']);
                        },
                        error: function (error) {
                            disableFeedbackWidgetTable();
                            var errorText = error['responseJSON']['errors']['general_errors'][0];
                            showGeneralErrors(errorText);
                            $('.formaloo-feedback-widget-loading-gif-wrapper').hide();
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
                        if (field['type'] == 'long_text') {
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

                    $('.formaloo-feedback-widget-loading-gif-wrapper').hide();
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
                            tb_show("<?php _e( 'How to use your Feedback Widget', 'formaloo' ); ?>","#TB_inline?width=100vw&height=100vh&inlineId=formaloo-feedback-widget-show-options",null);
                            jQuery('.spinner').removeClass('is-active');
                            }).catch(function(err) {
                                console.log(err);
                                jQuery('.spinner').removeClass('is-active');
                            })
                        }).catch(function(err) {
                            console.log(err);
                            jQuery('.spinner').removeClass('is-active');
                        })
                        
                    }).catch(function(err) {
                        console.log(err);
                        jQuery('.spinner').removeClass('is-active');
                    })

                    //     this.submit();

                });

                function editField(urlString, params) {
                    return new Promise(function(resolve, reject) {
                        $.ajax({
                            url: urlString,
                            type: 'PATCH',
                            headers: {
                                'x-api-key': '<?php echo $data['api_key']; ?>',
                                'Authorization': '<?php echo 'Token ' . $data['api_token']; ?>'
                            },
                            data: params,
                            success: function (result) {
                                resolve(result);
                            },
                            error: function (error) {
                                reject(error)
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

            });
        </script>

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

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

require_once('listTable.php');
require_once('resultsTable.php');

/* Register activation hook. */
register_activation_hook( __FILE__, 'formaloo_admin_notice_activation_hook' );
require_once('showActivationNotice.php');

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
