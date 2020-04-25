<?php 

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