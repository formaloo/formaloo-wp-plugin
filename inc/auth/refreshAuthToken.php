<?php

class TokenAuthenticator extends Formaloo_Main_Class {

    public function __construct() {

        $data = get_option('formaloo_data', array());

        $api_key = isset($data['api_key']) ? $data['api_key'] : '';
        $api_secret = isset($data['api_secret']) ? $data['api_secret'] : '';

        $timeA = isset($data['api_token_save_date']) ? $data['api_token_save_date'] : time();
        $timeB = time();

        $fiveMinutes = 60 * 5;

        if (($timeA+$fiveMinutes) <= $timeB) {
            $refreshed_api_token = $this->getNewAuthToken($api_key, $api_secret);
            if ($refreshed_api_token != '') {
                 file_put_contents(__DIR__.'/my_loggg.txt', ' // ' . date('m/d/Y H:i:s', time()) . ' // ');
                 $data['api_token'] = $refreshed_api_token;
                 $data['api_token_save_date'] = time();
                 update_option('formaloo_data', $data);
            }
        }

    }

    function getNewAuthToken($api_key, $api_secret) {
        $url = FORMALOO_PROTOCOL.'://accounts.'.FORMALOO_ENDPOINT.'/v1/oauth2/authorization-token/';

        $renewAuthTokenResponse = wp_remote_post($url,
                                                array('body' => array('grant_type' => 'client_credentials'),
                                                'headers' => array('x-api-key' => $api_key,
                                                                'Authorization'=>'Basic ' . $api_secret))
                                                );

        if (!is_wp_error($renewAuthTokenResponse)) {
            $renewAuthTokenResult = json_decode($renewAuthTokenResponse['body'], true);
            return $renewAuthTokenResult['authorization_token'];
        } else {
            return '';
        }
        
    }

}

