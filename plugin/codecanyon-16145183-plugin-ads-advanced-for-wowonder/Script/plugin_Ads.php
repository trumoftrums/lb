<?php
/** 
 * Plugin Ads
 * Plugin for Wowonder
 * @author Pp Galvan  - LdrMx
 */
 
   /*class of plugin*/
   include "assets/plugins/class-ads.php";
   $ads_ = new Ads();
 
   /*gets*/  
   $ad_id = ( isset($_POST['ad_id']) ? $_POST['ad_id'] : ( isset($_GET['ad_id']) ? $_GET['ad_id'] : 0 ) );
   $plan_id = ( isset($_POST['plan_id']) ? $_POST['plan_id'] : ( isset($_GET['plan_id']) ? $_GET['plan_id'] : 0 ) );
   $view_ads = ( isset($_POST['view']) ? $_POST['view'] : ( isset($_GET['view']) ? $_GET['view'] : '' ) );
   $task = ( isset($_POST['task']) ? $_POST['task'] : ( isset($_GET['task']) ? $_GET['task'] : '' ) );
   $v = ( isset($_POST['v']) ? $_POST['v'] : ( isset($_GET['v']) ? $_GET['v'] : '' ) );
   $by = ( isset($_POST['by']) ? $_POST['by'] : ( isset($_GET['by']) ? $_GET['by'] : 'all' ) );
   $sub_view = ( isset($_POST['sub_view']) ? $_POST['sub_view'] : ( isset($_GET['sub_view']) ? $_GET['sub_view'] : '' ) );

   /* head*/
   $wo['plugin_list']['header'][] = 'header_ads';
   $wo['plugin_list']['header_css'][] = 'ads';
  
   /*content list*/
   if($wo['loggedin'] == true) { 
   
      $wo['plugin_list']['plugin_wo'][] = array('ads' => 'ads', 'ads-create' => 'ads_create', 'ads-edit'=> 'ads_edit', 'ads-activate'=> 'ads_activate', 'ads-payment-history'=> 'ads_payment_history'); 
      //js
      $wo['plugin_list']['footer_js'][] = 'ads';
	  // menu home
      $wo['plugin_list']['plugin_menu'][] = array('name' => 'ads', 'url' => '', 'link' => 'ads-create', 'icon'=> 'fa-bullhorn', 'title' => $wo['lang']['plugin_ads_create_ads'] );
      // header
      $wo['plugin_list']['plugin_head_menu_down'][] = array('name' => 'ads', 'url' => '', 'link' => 'ads', 'icon'=> 'fa-globe', 'title' => $wo['lang']['plugin_ads_ads_manager'] );
      $wo['plugin_list']['plugin_head_menu_down'][] = array('name' => 'ads', 'url' => '', 'link' => 'ads-create', 'icon'=> 'fa-bullhorn', 'title' => $wo['lang']['plugin_ads_create_ads'] );
      //menu post
      $wo['plugin_list']['post_menu'][] = array('name' => 'ads', 'url' =>'', 'link' => 'ads-create&type=post&post_id=', 'icon' => 'fa-bullhorn', 'title'=> $wo['lang']['plugin_ads_boost_this_post']);
	  
	  /*ADMIN*/
   if($plugin_page == "admin-plugins"){	 
	  $wo['plugin_list']['admin_menu'][] = array(
		  'Clients'=>'payment_clients', 
		  'Payment Settings'=>'payment_setting', 
		  'View Campaigns' => 'campaigns_view', 
		  'Campaigns Reports' => 'campaigns_reports', 
		  'Manage Packages'=>'campaigns_manage', 
		  'Campaigns Settings'=>'campaign_setting'
	  );
      $wo['plugin_list']['admin_tab'][] = array(
		  'payment_clients'=>'admin_payment_clients.phtml', 
		  'payment_setting'=>'ads_payment_setting.phtml', 
		  'campaigns_view' => 'admin_campaigns_view.phtml', 
		  'campaigns_reports' => 'admin_campaigns_reports.phtml', 
		  'campaigns_manage'=>'admin_campaigns_manage.phtml', 
		  'campaign_setting'=>'ads_position_setting.phtml'
	  );
      //js
	  $wo['plugin_list']['footer_js'][] = 'ads_admin';       
   } 
      	  
   }//log

   /*tab right*/
   if(in_array($plugin_page, array('index', 'messages', 'page', 'timeline', 'group', 'event','post', 'home'))){ 
      $wo['plugin_list']['tab_rigth'][] = 'plugin_tab_right_ads';
   }
   
  
   /*profile content*/
   if(in_array($plugin_page, array('timeline', 'page', 'group', 'event'))){ 
      $wo['plugin_list']['profile_tab_panel'][] = array('name'=>'ads', 'tab' => 'plugin_profile_tab_panel_ads');
   }
 ?>