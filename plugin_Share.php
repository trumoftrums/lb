<?php
/** 
 * Plugin Share
 * Plugin for Wowonder
 * @author Pepe Galvan  - LdrMx
 */
  
 /*start menus and tabs array list*/
 if($wo['loggedin'] == true) {
    $wo['plugin_list']['footer_js'][] = 'share_post'; 
    /* ADMIN AREA IN NEXT VERSION
    $wo['plugin_list']['admin_menu'][] = array('Share setting' => 'share_setting');
    $wo['plugin_list']['admin_tab'][] = array('share_setting' => 'p_admin_share_setting.phtml');
    */
 }
?>