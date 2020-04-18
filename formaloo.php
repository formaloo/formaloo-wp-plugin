<?php
/**
 * Plugin Name:       Formaloo Form Builder
 * Description:       Easily embed Formaloo forms into your blog or WP pages.
 * Version:           1.0.0
 * Author:            Formaloo team
 * Author URI:        https://formaloo.net/
 * Text Domain:       formaloo
 * Domain Path: /languages
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
if(!defined('FORMALOO_ENDPOINT')) {
    if (get_locale() == 'fa_IR') {
        define('FORMALOO_ENDPOINT', 'formaloo.com');
    } else {
        define('FORMALOO_ENDPOINT', 'formaloo.net');
    }
}
	
if(!defined('FORMALOO_PROTOCOL'))
    define('FORMALOO_PROTOCOL', 'https');
if(!defined('FORMALOO_X_API_KEY')) {
    if (get_locale() == 'fa_IR') {
        define('FORMALOO_X_API_KEY', 'd90bf3d862962b9123f1cbc8b2e72f19adacf46d');
    } else {
        define('FORMALOO_X_API_KEY', '7a377c51b8aa0e87e93c1b6bb951166784c98351');
    }
}

require_once plugin_dir_path( __FILE__ ) . '/blocks/formaloo-block.php';


function formaloo_gutenberg_block_callback($attr) {
    
    $formAddress = substr(parse_url($attr['url'])['path'],1);
    $apiUrl = FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT .'/v1/forms/form/'. $formAddress . '/show/';
    $formSlug = '';
    $data = get_option('formaloo_data', array());
    $private_key = $data['private_key'];
 
    $request = wp_remote_get( $apiUrl ,
     array( 'timeout' => 10,
     'headers' => array( 'x-api-key' => FORMALOO_X_API_KEY,
                     'Authorization'=> 'Token ' . $private_key ) 
    ));
 
    if( is_wp_error( $request ) ) {
     return false; // Bail early
    }
 
     $body = wp_remote_retrieve_body( $request );
 
     $data = json_decode( $body );
 
     if( ! empty( $data ) ) {
         $formSlug = $data->data->form->slug;
     }
 
     switch ($attr['show_type']) {
         case 'link':
             return '<a href="' . FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/'. $formAddress .'" target="_blank"> '. $attr['link_title'] .' </a>';
         case 'iframe':
             return '<iframe src="' . FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/'. $formAddress .'" class="custom-formaloo-iframe-style" frameborder="0" marginheight="0" marginwidth="0">Loading...</iframe><style>.custom-formaloo-iframe-style {display:block; width:100%; height:100vh;}</style>';
         case 'script':
             if ($attr['show_title'] == 0) {
                 $show_title =  '#main-form .formz-form-title { display: none; }';
             } 
             if ($attr['show_descr'] == 0) {
                 $show_desc =  '#main-form .formz-form-desc { display: none; }';
             }
             if ($attr['show_logo'] == 0) {
                 $show_logo =  '#main-form .formz-main-logo { display: none; }';
             }
             return '
                 <style>'. $show_title . $show_desc . $show_logo .'</style>
                 <div id="formz-wrapper" data-formz-slug="'. $formSlug .'"></div>
                 <script src="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT . '/istatic/js/main.js" type="text/javascript" async></script>
             ';
     }
 
}

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

        load_plugin_textdomain( 'formaloo', false, 'formaloo/languages/' );

        add_action('admin_menu',                array($this,'addAdminMenu'));
        add_action('wp_ajax_store_admin_data',  array($this,'storeAdminData'));
        add_action('wp_ajax_get_formaloo_shortcode',  array($this,'getFormalooShortcode'));
        add_action('admin_enqueue_scripts',     array($this,'addAdminScripts'));

        add_shortcode('formaloo', array($this, 'formaloo_show_form_shortcode'));

        add_filter( 'submenu_file', array($this, 'formaloo_wp_admin_submenu_filter'));
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
                    <script src="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT . '/istatic/js/main.js" type="text/javascript" async></script>
                ';
        }

    }

    /**
     * Display the list table page
     *
     * @return Void
     */
    public function list_table_page() {
        $data = $this->getData();
        $formListTable = new Forms_List_Table();
        $formData = $this->getForms($data['private_key'], $formListTable->get_pagenum());
        $formListTable->setFormData($formData);
        $formListTable->setPrivateKey($data['private_key']);
        $formListTable->prepare_items();
        $formListTable->display();
    }

    public function results_table_page($slug) {
        $results = array();
        $data = $this->getData();
        $private_key = $data['private_key'];
        $resultListTable = new Results_List_Table();
        
        $api_url = FORMALOO_PROTOCOL. '://api.'. FORMALOO_ENDPOINT .'/v1/forms/form/'. $slug .'/submits/?page='. $resultListTable->get_pagenum();
  
        $response = wp_remote_get( $api_url ,
        array( 'timeout' => 10,
       'headers' => array( 'x-api-key' => FORMALOO_X_API_KEY,
                          'Authorization'=> 'Token ' . $private_key ) 
        ));
  
        if (is_array($response) && !is_wp_error($response)) {
          $results = json_decode($response['body'], true);
        }

        $resultListTable->setFormData($results);
        $resultListTable->setPrivateKey($private_key);
        $resultListTable->prepare_items();
        ?>
        <?php $resultListTable->display(); ?>
        <?php
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

        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');

	    wp_enqueue_style('formaloo-admin', FORMALOO_URL. 'assets/css/admin.css', false, 1.0);
        wp_enqueue_script('formaloo-admin', FORMALOO_URL. 'assets/js/admin.js', array(), 1.0);

        if (get_locale() == 'fa_IR') {
            wp_enqueue_style('formaloo-admin-rtl', FORMALOO_URL. 'assets/css/rtl.css', false, 1.0);
        }

        $data = $this->getData();

		$admin_options = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
            '_nonce'   => wp_create_nonce( $this->_nonce ),
            'private_key' => $data['private_key'],
            'x_api_key' => FORMALOO_X_API_KEY,
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
            // 'dashicons-forms'
        );
        
        add_submenu_page(
            'formaloo',
            __( 'Settings', 'formaloo' ),
            __( 'Settings', 'formaloo' ),
            'manage_options',
            'formaloo-settings-page',
            array($this, 'adminLayout')
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
     * @param $private_key string
     *
     *
     * @return array
	 */
	private function getForms($private_key, $pageNum = 1) {
        
        $data = array();
        
        $api_url = FORMALOO_PROTOCOL. '://api.'. FORMALOO_ENDPOINT .'/v1/forms/list/?page='. $pageNum;

        $response = wp_remote_get( $api_url ,
        array( 'timeout' => 10,
       'headers' => array( 'x-api-key' => FORMALOO_X_API_KEY,
                          'Authorization'=> 'Token ' . $private_key ) 
        ));

        // print('Token ' . $private_key);
        // print("\n");
        // print_r($response);

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
	    $api_response = $this->getForms($data['private_key']);
	    $not_ready = (empty($data['private_key']) || empty($api_response) || isset($api_response['error']));

	    ?>

		<div class="wrap">

            <!-- <h1><?php // _e('Formaloo', 'formaloo'); ?></h1> -->

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
                <div class="formaloo_clipboard_wrapper hidden">
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
                <script src="<?php echo FORMALOO_URL ?>/assets/js/clipboard.min.js"></script>
                <script>
                
                    new ClipboardJS('.formaloo_clipboard_btn');

                    function getRowInfo($slug, $address) {
                        jQuery('.formaloo_clipboard_wrapper').addClass('hidden');
                        jQuery(".form-table").append('<input name="formaloo_form_slug" id="formaloo_form_slug" type="hidden" value="' + $slug + '" />');
                        jQuery(".form-table").append('<input name="formaloo_form_address" id="formaloo_form_address" type="hidden" value="' + $address + '" />');
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
                        jQuery('.formaloo_clipboard_wrapper').addClass('hidden');
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
                            <?php echo __('If so you can find your API key from your','formaloo') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/profile/" target="_blank">'. __('profile page,','formaloo') .'</a> '. __('and enter it on the','formaloo') .' <a href="?page=formaloo-settings-page">'. __('settings page','formaloo') .'</a>.'; ?>
                        </p>
                    <?php else: ?>
                        <?php echo $this->getStatusIcon(!$not_ready); ?>
                        <?php 
                            $formaloo_first_name = $api_response['data']['forms'][0]['owner']['first_name'];
                            $formaloo_user_name = empty($formaloo_first_name) ? __('User','formaloo') : $formaloo_first_name;
                            echo __('Hello Dear','formaloo'). ' ' . $formaloo_user_name .'! '. __('You can edit or view your forms right here or you can access', 'formaloo') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/" target="_blank">'. __('your full dashboard here','formaloo') .'</a>.'; 
                        ?>
                    <?php endif; ?>
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
                            <h3 class="formaloo-heading">
                                <span class="dashicons dashicons-feedback"></span>
                                <?php _e('Your forms', 'formaloo'); ?>
                            </h3>
                            <div class="formaloo-create-new-form">
                                <a href="#TB_inline?&width=100vh&height=100vw&inlineId=form-show-create-form" title="<?php _e('Create a form', 'formaloo'); ?>" target="_blank" class="button formaloo-create-new-form-link thickbox" onclick = "showCreateForm()"><span class="dashicons dashicons-plus"></span> <?php _e('Create a new form', 'formaloo') ?></a>
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
	    $not_ready = (empty($data['private_key']));

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

	            <?php if (!empty($data['private_key']) && !empty($data['slug_for_results'])): ?>
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
                            <?php _e('Your Form Results', 'formaloo'); ?>
                        </h3>
                        <?php $this->results_table_page($_GET['results_slug']); ?>
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
	public function adminLayout() {

        $data = $this->getData();

	    $not_ready = (empty($data['private_key']));

	    ?>

		<div class="wrap">

            <!-- <h1><?php // _e('Formaloo', 'formaloo'); ?></h1> -->

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
                            <?php echo __('To get started, we\'ll need to access your Formaloo account with an','formaloo') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/profile/" target="_blank">'. __('API Key','formaloo') .'</a>. '. __('Paste your Formaloo API Key, and click','formaloo') .' <strong>'. __('Connect','formaloo') .'</strong> '. __('to continue','formaloo') .'.'; ?>
                            <!-- <br> -->
                            <?php //_e('Once the key set and saved, if you do not see any option, please reload the page. Thank you, you rock 🤘', 'formaloo'); ?>
                        </p>
                    <?php else: ?>
                        <?php echo __('You can access your','formaloo') .' <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/dashboard/" target="_blank">'. __('Formaloo dashboard here','formaloo') .'</a>.'; ?>  
                        <br>
                        <br>
                    <?php endif; ?>
                    <?php echo $this->getStatusDiv(!$not_ready); ?>

                    <table class="form-table formaloo-api-settings-table">
                        <tbody>
                            <tr>
                                <td scope="row">
                                    <label><?php _e( 'API Private Key', 'formaloo' ); ?></label>
                                </td>
                                <td>
                                    <input name="formaloo_private_key"
                                           id="formaloo_private_key"
                                           class="regular-text"
                                           type="text"
                                           value="<?php echo (isset($data['private_key'])) ? $data['private_key'] : ''; ?>"/>
                                </td>
                                <td>
                                    <button class="button button-primary formaloo-admin-save formaloo-button-black" type="submit">
                                        <?php ($not_ready) ? _e( 'Connect', 'formaloo' ) : _e( 'Save', 'formaloo' ) ?>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!--
                    <table class="form-table formaloo-api-settings-table">
                        <tbody>
                            <tr>
                                <td scope="row">
                                    <label><?php _e( 'Change the Plugin Language', 'formaloo' ); ?></label>
                                </td>
                                <td>
                                    <select name="formaloo_language_options"
                                            id="formaloo_language_options">
                                        <option value="en_language" selected>
                                            <?php _e( 'English', 'formaloo' ); ?>
                                        </option>
                                        <option value="fa_language">
                                            <?php _e( 'Farsi', 'formaloo' ); ?>
                                        </option>
                                    </select>
                                </td>
                                <td>
                                    <button class="button button-primary formaloo-admin-save formaloo-button-black" type="submit">
                                       <?php _e('Change','formaloo') ?>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    -->
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
new Formaloo();

function formaloo_settings_link($links) { 
    $settings_link = '<a href="admin.php?page=formaloo-settings-page">Settings</a>'; 
    array_unshift($links, $settings_link); 
    return $links; 
}
$plugin = plugin_basename(__FILE__); 
add_filter('plugin_action_links_'. $plugin, 'formaloo_settings_link' );
