<?php
/**
 * Create a new table class that will extend the WP_List_Table
 */
class Formaloo_Forms_List_Table extends WP_List_Table {

    private $formData = array();

    public function setFormData($formData) { 
        $this->formData = $formData; 
    }
    public function getFormData() { 
        return $this->formData; 
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
        // $currentPage = $this->get_pagenum();
        $totalItems = $formData['data']['count']; //count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        // $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

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
            'excel'         => __('Download Results', 'formaloo'),
            'more'          => __('More Options', 'formaloo')
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
                'title'        => '<a href="?page=formaloo-results-page&results_slug='. $form['slug'] .'"><strong class="formaloo-table-title">'. $form['title'] .'</strong></a>',
                'active'       => ($form['active']) ? '<span class="dashicons dashicons-yes success-message"></span>' : '<span class="dashicons dashicons-no-alt error-message"></span>',
                'submitCount'  => $form['submit_count'],
                'slug'         => $form['slug'],
                'address'      => $form['address'],
                'excel'        => '<button class="button formaloo-get-excel-link" data-form-slug="'. $form['slug'] .'"> <span class="dashicons dashicons-download"></span> '. __('Download', 'formaloo') .' </button>',
                'more'         => '<div class="formaloo-column-more-wrapper"><a href="'. FORMALOO_PROTOCOL .'://'. FORMALOO_ENDPOINT .'/dashboard/my-forms/'. $form['slug'] .'/edit" target="_blank" class="button formaloo-edit-link"><span class="dashicons dashicons-edit"></span></a> <a href="'. FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT . '/dashboard/my-forms/' . $form['slug'] . '/share" target="_blank" class="button formaloo-edit-link"><span class="dashicons dashicons-share"></span></a></div>',
                'type'         => $form['form_type'],
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
            case 'more':
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
        // Set defaults
        $columns = ['title','active','submitCount','excel', 'more'];
        $orders = ['asc', 'desc'];

        $orderBy = $columns[0];
        $order = $orders[0];

        if (isset( $_GET['orderby'] )) {
            $orderBy = sanitize_text_field( $_GET['orderby'] );
        }

        if (isset( $_GET['order'] )) {
            $order = sanitize_text_field( $_GET['order'] );
        }

        // If orderby exists, use this as the sort column
        if (!in_array( $orderBy, $columns )) {
            $orderBy = $columns[0];
        }

        // If order exists use this as the order
        if (!in_array( $order, $orders )) {
            $order = $orders[0];
        }

        $result = strnatcmp( $a[sanitize_text_field( $_GET['orderby'] )], $b[sanitize_text_field( $_GET['orderby'] )] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }

    function column_title($item) {
        $modalTitle = __('Set-up Form Settings', 'formaloo');
        
        $actions = array(
                  'view'      => sprintf('<a href="%s://%s/%s" target="_blank">'. __('View','formaloo') .'</a>',FORMALOO_PROTOCOL,FORMALOO_ENDPOINT,$item['address'])
              );

        if ($item['type'] != 'nps') {
            $actions['shortcode'] = '<a href="#TB_inline?&width=100vw&height=100vh&inlineId=form-show-options" class="thickbox" title="'. $modalTitle .'" onclick = "getRowInfo(\''. $item['slug'] .'\',\''. $item['address'] .'\')">'. __('Get Shortcode', 'formaloo') .'</a>';
            $actions['edit'] = '<a href="#TB_inline?&width=100vw&height=100vh&inlineId=form-show-edit" title="'. __('Edit Form','formaloo') .'" class="thickbox" onclick = "showEditFormWith(\''. FORMALOO_PROTOCOL .'\', \''. FORMALOO_ENDPOINT .'\', \''. $item['slug'] .'\')">'. __('Edit', 'formaloo') .'</a>';
        } else {
            $actions['edit'] = '<a href="?page=formaloo-feedback-widget-page&widget_slug='. $item['slug'] .'" target="_blank">'. __('Edit', 'formaloo') .'</a>';
            $actions['use_widget'] = '<a href="?page=formaloo-feedback-widget-page&widget_slug='. $item['slug'] .'" target="_blank">'. __('Use Widget', 'formaloo') .'</a>';

        }
      
        return sprintf('%1$s %2$s', $item['title'], $this->row_actions($actions) );
    }

    
}