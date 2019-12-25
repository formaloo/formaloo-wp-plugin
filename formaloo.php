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

		add_action('admin_menu',                array($this,'addAdminMenu'));
		add_action('wp_ajax_store_admin_data',  array($this,'storeAdminData'));
        add_action('admin_enqueue_scripts',     array($this,'addAdminScripts'));

        add_shortcode('formaloo', array($this, 'formaloo_show_form_shortcode'));

    }

    public function formaloo_show_form_shortcode($atts) {
        // extract the attributes into variables
        extract(shortcode_atts(array(
            'slug'              => 'slug',
            'address'           => 'address',
            'type'              => 'link',
            'show_title'        => false,
            'link_title'        => 'Show Form'
        ), $atts));
        
        if ($atts['show_title']) {
            $this->addFooterCode(true);
        }

        switch ($atts['type']) {
            case 'link':
                return '<a href="' . FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/'. $atts['address'] .'" target="_blank"> '. $atts['link_title'] .' </a>';
            case 'iframe':
                return '<iframe src="' . FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT .'/'. $atts['address'] .'" class="custom-formaloo-iframe-style" frameborder="0" marginheight="0" marginwidth="0">Loading...</iframe><style>.custom-formaloo-iframe-style {display:block; width:100%; height:100vh;}</style>';
            case 'script':
                return '
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

        // wp_redirect('http://www.domain.nl/email-acties/');

        // echo __('Saved!', 'formaloo');
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
	    $is_requesting_shortcode = (isset($_GET['formaloo-get-shortcode']) && $_GET['formaloo-get-shortcode'] === 'go');
        
        $FORMALOO_WEBSITE = "formaloo.com";

	    ?>

		<div class="wrap">

            <h1><?php _e('Formaloo Settings', 'formaloo'); ?></h1>

			<?php if ($is_requesting_shortcode): ?>
				<?php // $this->addFooterCode(true); ?>
                <p class="notice notice-success p-10 is-dismissible">
                    <?php // print_r($data); ?>
					<strong><?php _e( 'Here is your Shortocde:', 'formaloo' ); ?></strong>
                    [formaloo 
                    address="<?php echo (isset($data['widget_form_address'])) ? $data['widget_form_address'] : 'try again'; ?>" 
                    slug="<?php echo (isset($data['widget_form_slug'])) ? $data['widget_form_slug'] : 'try again'; ?>" 
                    type="<?php echo (isset($data['widget_show_type'])) ? $data['widget_show_type'] : 'link'; ?>" 
                    <?php 
                    switch ($data['widget_show_type']) {
                    case 'link':
                        if (isset($data['widget_link_title']) && !empty($data['widget_link_title'])):
                            echo 'link_title="'. $data['widget_link_title']. '"';
                        endif;
                    break;
                    case 'iframe': break;
                    case 'script':
                        echo 'show_title="'. (isset($data['widget_show_title'])) ? $data['widget_show_title'] : false .'"';
                    break;
                    }
                    ?>
                    ]
                </p>
                <script>
                    let params = new URLSearchParams(location.search)
                    params.delete("formaloo-get-shortcode")
                    history.replaceState(null, "", "?" + params + location.hash)
                </script>
			<?php endif; ?>

            <div id="form-show-options" style="display:none;">
                <h3>
                    <?php _e('Set-up Form Settings', 'formaloo'); ?>
                </h3>
                <form id="formaloo-customize-form">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <td scope="row">
                                <label><?php _e( 'How to show', 'formaloo' ); ?></label>
                            </td>
                            <td>
                                <select name="formaloo_widget_show_type"
                                        id="formaloo_widget_show_type">
                                    <option value="link" <?php echo (!isset($data['widget_show_type']) || (isset($data['widget_show_type']) && $data['widget_show_type'] === 'link')) ? 'selected' : ''; ?>>
                                        <?php _e( 'Link', 'formaloo' ); ?>
                                    </option>
                                    <option value="iframe" <?php echo (isset($data['widget_show_type']) && $data['widget_show_type'] === 'iframe') ? 'selected' : ''; ?>>
                                        <?php _e( 'iFrame', 'formaloo' ); ?>
                                    </option>
                                    <option value="script" <?php echo (isset($data['widget_show_type']) && $data['widget_show_type'] === 'script') ? 'selected' : ''; ?>>
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
                                <input name="formaloo_widget_link_title"
                                        id="formaloo_widget_link_title"
                                        type="text"
                                        class="regular-text"
                                        value="<?php echo (isset($data['widget_link_title'])) ? esc_attr__($data['widget_link_title']) : ''; ?>"/>
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
                                <input name="formaloo_widget_show_title"
                                        id="formaloo_widget_show_title"
                                        type="checkbox"
                                        <?php echo (isset($data['widget_show_title']) && $data['widget_show_title']) ? 'checked' : ''; ?>/>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <?php if (!$not_ready): ?> 
                    <button class="button button-primary my-10" id="formaloo-admin-save" type="submit">
                        <?php _e( 'Get shortcode', 'formaloo' ); ?>
                    </button>
                <?php endif; ?>
                </form>
                <script>

                    function getRowInfo($slug, $address) {
                        console.log($slug);
                        console.log($address);
                        jQuery(".form-table").append('<input name="formaloo_widget_form_slug" id="formaloo_widget_form_slug" type="hidden" value="' + $slug + '" />');
                        jQuery(".form-table").append('<input name="formaloo_widget_form_address" id="formaloo_widget_form_address" type="hidden" value="' + $address + '" />');
                    }
                    jQuery("#formaloo_widget_show_type").change(function() {
                        if (jQuery(this).val() == "link") {
                            jQuery('#link_title_row').show();
                            jQuery('#show_title_row').hide();
                        } else if (jQuery(this).val() == "script") {
                            jQuery('#show_title_row').show();
                            jQuery('#link_title_row').hide();
                        } else {
                            jQuery('#show_title_row').hide();
                            jQuery('#link_title_row').hide();
                        }
                    });
                    jQuery("#formaloo_widget_show_type").trigger("change");
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
                    <button class="button button-primary" id="formaloo-admin-save" type="submit">
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
	public function addFooterCode($force = false) {

        ?>

        <style>
            #main-form .formz-form-title {
                display: none;
            }
        </style>

        <?php

    }

}

/* Register activation hook. */
register_activation_hook( __FILE__, 'formaloo_admin_notice_activation_hook' );

/**
 * Runs only when the plugin is activated.
 * @since 0.1.0
 */
function formaloo_admin_notice_activation_hook() {

    /* Create transient data */
    set_transient( 'formaloo-admin-notice-activation', true, 5 );
}

/* Add admin notice */
add_action( 'admin_notices', 'formaloo_admin_notice_activation_notice' );

/**
 * Admin Notice on Activation.
 * @since 0.1.0
 */
function formaloo_admin_notice_activation_notice(){

    /* Check transient, if available display notice */
    if( get_transient( 'formaloo-admin-notice-activation' ) ){
        ?>
        <div class="updated notice is-dismissible">
            <p>Thank you for using this plugin! <strong>Please Activate your plugin</strong>.</p>
        </div>
        <?php
        /* Delete transient, only display this notice once. */
        delete_transient( 'formaloo-admin-notice-activation' );
    }
}


// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Forms_List_Table extends WP_List_Table {

    private $formData = array();

    public function setFormData($formData) { 
        $this->formData = $formData; 
    }
    public function getFormData() { 
        return $this->formData; 
    }

    private $privateKey = '';

    public function setPrivateKey($privateKey) { 
        $this->privateKey = $privateKey; 
    }
    public function getPrivateKey() { 
        return $this->privateKey; 
    }

    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $formData = $this->getFormData();

        $perPage = $formData['data']['page_size'];
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            // 'cb'            => '<input type="checkbox" />',
            'title'         => __('Title', 'formaloo'),
            'active'        => __('Active', 'formaloo'),
            'submitCount'   => __('Submit Count', 'formaloo'),
            'excel'         => __('Download Results', 'formaloo')
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('title' => array('title', false), 'submitCount' => array('submitCount', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data() {
        $tableData = array();
        $data = $this->getFormData();
        $index = 0;

        foreach($data['data']['forms'] as $form) {
            $tableData[] = array(
                'ID'           => $index,
                'title'        => '<a href="#TB_inline?&width=600&height=250&inlineId=form-show-options" class="thickbox" onclick = "getRowInfo(\''. $form['slug'] .'\',\''. $form['address'] .'\')"><strong class="formaloo-table-title">'. $form['title'] .'</strong></a>',
                'active'       => ($form['active']) ? '<span class="dashicons dashicons-yes success-message"></span>' : '<span class="dashicons dashicons-no-alt error-message"></span>',
                'submitCount'  => $form['submit_count'],
                'slug'         => $form['slug'],
                'address'      => $form['address'],
                'excel'        => '<a href="'. add_query_arg('download_excel',$form['slug']) .'" class="formaloo-download-btn"><span class="dashicons dashicons-download"></span> Download</a>'
            );
            $index++;
        }

        return $tableData;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            // case 'cb':
            case 'title':
            case 'active':
            case 'submitCount':
            case 'excel':
                return $item[ $column_name ];

            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b ) {

        esc_url( remove_query_arg( 'download_excel' ) );

        // Set defaults
        $orderby = 'title';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }

        $result = strnatcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }

    function column_title($item) {
        $actions = array(
                  'view'      => sprintf('<a href="%s://%s/%s" target="_blank">View Form</a>',FORMALOO_PROTOCOL,FORMALOO_ENDPOINT,$item['address']),
                  'edit'      => sprintf('<a href="%s://%s/dashboard/my-forms/%s/edit" target="_blank">Edit Form</a>',FORMALOO_PROTOCOL,FORMALOO_ENDPOINT,$item['slug']),
                  // 'delete'    => sprintf('<a href="?page=%s&action=%s&book=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
              );
      
        return sprintf('%1$s %2$s', $item['title'], $this->row_actions($actions) );
    }

    
}

/*
 * Starts our plugin class, easy!
 */
new Formaloo();

