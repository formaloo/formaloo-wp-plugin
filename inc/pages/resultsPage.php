<?php
class Formaloo_Results_Page extends Formaloo_Main_Class {
    
    public function resultsTablePage($slug) {
        $results = array();
        $data = $this->getData();
        $api_token = $data['api_token'];
        $api_key = $data['api_key'];
        $api_secret = $data['api_secret'];
        $resultListTable = new Formaloo_Results_List_Table();
        
        $api_url = FORMALOO_PROTOCOL. '://api.'. FORMALOO_ENDPOINT .'/v1/forms/form/'. $slug .'/submits/?page='. $resultListTable->get_pagenum();
  
        $response = wp_remote_get( $api_url ,
            array( 'timeout' => 10,
                    'headers' => array( 'x-api-key' => $api_key,
                                        'Authorization'=> 'JWT ' . $api_token ) 
        ));

        if (wp_remote_retrieve_response_code($response) == 401 && !empty($api_secret) && !empty($api_key)) {
            $url = FORMALOO_PROTOCOL. '://accounts.'. FORMALOO_ENDPOINT .'/v1/oauth2/authorization-token/';
            // $url = 'https://staging.icas.formaloo.com/v1/oauth2/authorization-token/';
            $renewAuthTokenResponse = wp_remote_post( $url, array(
                'body'    => array(
                    'grant_type'   => 'client_credentials'
                ),
                'headers' => array( 'x-api-key' => $api_key,
                                    'Authorization'=> 'Basic ' . $api_secret ) 
            ) );
            if (!is_wp_error($renewAuthTokenResponse)) {
                $renewAuthTokenResult = json_decode($renewAuthTokenResponse['body'], true);
                $data['api_token'] = $renewAuthTokenResult['authorization_token'];
                update_option($this->option_name, $data);
                $this->resultsTablePage($slug);
            }
        }
  
        if (is_array($response) && !is_wp_error($response)) {
          $results = json_decode($response['body'], true);
        }

        $resultListTable->setFormData($results);
        $resultListTable->setPrivateKey($api_token);
        $resultListTable->prepare_items();
        
        $resultListTable->display();
    }
}