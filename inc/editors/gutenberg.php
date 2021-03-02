<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function formaloo_gutenberg_block_callback($attr) {
    
    if ($attr['show_form_selector']) {
        $formAddress = $attr['selected_form_address'];
    } else {
        $formAddress = substr(parse_url($attr['url'])['path'],1);
    }
    $apiUrl = FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT .'/v1/forms/form/'. $formAddress . '/show/';
    $formSlug = '';
    $data = get_option('formaloo_data', array());
    $api_token = $data['api_token'];
    $api_key = $data['api_key'];
    $api_secret = $data['api_secret'];

    $request = wp_remote_get( $apiUrl ,
    array( 'timeout' => 10,
            'headers' => array( 'x-api-key' => $api_key,
                                'Authorization'=> 'JWT ' . $api_token ) 
    ));

    if (wp_remote_retrieve_response_code($response) == 401 && !empty($api_secret) && !empty($api_key)) {
        // $url = FORMALOO_PROTOCOL. '://accounts.'. FORMALOO_ENDPOINT .'/v1/oauth2/authorization-token/';
        $url = 'https://staging.icas.formaloo.com/v1/oauth2/authorization-token/';
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
            update_option('formaloo_data', $data);
            $this->formaloo_gutenberg_block_callback($attr);
        }
    }

    if( is_wp_error( $request ) ) {
    return false;
    }

    $body = wp_remote_retrieve_body( $request );

    $result = json_decode( $body );

    if( ! empty( $result ) ) {
        $formSlug = $result->data->form->slug;
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
                '. wp_enqueue_script ( 'formaloo-form-js-script', FORMALOO_PROTOCOL . '://' . FORMALOO_ENDPOINT . '/istatic/js/main.js' );
    }

}