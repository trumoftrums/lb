<?php
/**
 * ads create
 * Plugin for Wowonder
 * @author Pepe Galvan  - LdrMx
 */

   $wo['plugin_page'] = 'ads_edit';
   if (Wo_IsLogged() === false) { header("Location: " . Wo_SeoLink('index.php?tab1=welcome')); exit(); }

   $type = ( isset($_POST['type']) ? $_POST['type'] : ( isset($_GET['type']) ? $_GET['type'] : 'website' ) );
   $post_id = ( isset($_POST['post_id']) ? $_POST['post_id'] : ( isset($_GET['post_id']) ? $_GET['post_id'] : 0 ) );
   $ad_id = ( isset($_POST['ad_id']) ? $_POST['ad_id'] : ( isset($_GET['ad_id']) ? $_GET['ad_id'] : 0 ) );

        // valid inputs
        $valid['view'] = array('', 'website', 'page', 'post');
        if(!in_array($type, $valid['view'])) { 
            $response['message'] = 'This windows no exist';
	         header("Content-type: application/json");
             echo json_encode($response);
             exit(); 
         }
		
$wo['description'] = $wo['config']['siteDesc'];
$wo['keywords']    = $wo['config']['siteKeywords'];
$wo['page']        = 'ads activate';
$wo['title']       = 'Edit Campaign | ' . $wo['config']['siteTitle'];
$wo['content']     = Wo_LoadPage('plugins/ads_edit'); 
?>