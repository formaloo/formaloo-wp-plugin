<?php
/**
 * Create a new table class that will extend the WP_List_Table
 */
class Results_List_Table extends WP_List_Table {

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
    public function prepare_items() {
      
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
    public function get_columns() {

      $columns = array();
      $data = $this->getFormData();
      $top_fields = $data['data']['top_fields'];
      $noOfTopFields = (count($top_fields) > 3) ? 3 : count($top_fields);

      for ($i=0; $i < count($top_fields); $i++ ) {
        if(($i+1) <= $noOfTopFields) { 
            $columns[$top_fields[$i]['slug']] = $top_fields[$i]['title'];
        }
      }
      
      $columns['date_created'] = __('Date Created', 'formaloo');
      $columns['full_results'] = __('Show Full Results', 'formaloo');

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
        return array();
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data() {
        $tableData = array();
        $data = $this->getFormData();
        $top_fields = $data['data']['top_fields'];
        $noOfTopFields = (count($top_fields) > 3) ? 3 : count($top_fields);

        foreach($data['data']['rows'] as $key=>$row) {
          $date = date_create($row['created_at']);
          $rendered_data = $row['rendered_data'];
          $i = 0;
          
          $tableData[$key] = array(
              'ID'           => $key,
              'date_created' => date_format($date,"Y/m/d H:i:s"),
              'full_results' => '<a href="#TB_inline?&width=100vh&height=100vw&inlineId=form-show-specific-result" class="thickbox button formaloo-show-result-button" title="Show Result" onclick = "showFormResultWith(\''. FORMALOO_PROTOCOL .'\', \''. FORMALOO_ENDPOINT .'\', \''. $row['form'] .'\' , \''. $row['slug'] .'\')"><span class="dashicons dashicons-visibility"></span> Full Result</a>'
          );

          foreach ($rendered_data as $k => $v) {
            if(($i+1) <= $noOfTopFields) { 
              $tableData[$key][$rendered_data[$k]['slug']] = $rendered_data[$k]['value'];
            }
            $i++;
          }
          
        }

        // foreach($data['data']['rows'] as $row) {
        //   $rendered_data = $row['rendered_data'];
        //   $i = 0;
        //   foreach ($rendered_data as $k => $v) {
        //     if(($i+1)<=3) { 
        //       array_push($tableRowData, $rendered_data[$k]['value']);
        //     }
        //     $i++;
        //   }
        //   $date = date_create($row['created_at']);
        //   $tableRowData[] = date_format($date,"Y/m/d H:i:s");
        //   $tableRowData[] = '<button class="button formaloo-get-excel-link"> <span class="dashicons dashicons-visibility"></span>' . __(' Full Result', 'formaloo') . '</button>';
        //   array_push($tableData, $tableRowData);
        //   $index++;
        // }

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
    public function column_default( $item, $column_name) {

        $columns = array();
        $data = $this->getFormData();
        $top_fields = $data['data']['top_fields'];
        $noOfTopFields = (count($top_fields) > 3) ? 3 : count($top_fields);

        for ($i=0; $i < count($top_fields); $i++ ) {
          if(($i+1) <= $noOfTopFields) { 
              $columns[] = $top_fields[$i]['slug'];
          }
        }

        $columns[] = 'date_created';
        $columns[] = 'full_results';

        foreach ($columns as $keys => $values) {
            if ($values == $column_name) {
                return empty($item[ $column_name ]) ? '-' : $item[ $column_name ];
            }
        }
    }
    
}