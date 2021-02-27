<?php
function formaloo_settings_link($links) { 
    $settings_link = '<a href="admin.php?page=formaloo-settings-page">Settings</a>'; 
    array_unshift($links, $settings_link); 
    return $links; 
}
$plugin = plugin_basename(__FILE__); 
add_filter('plugin_action_links_'. $plugin, 'formaloo_settings_link' );