<?php
/** 
 * Plugin Combo
 * Plugin for Wowonder
 * @author Pepe Galvan  - LdrMx
 */
 
 
/*class of plugin*/
include "assets/plugins/functions_combo.php";

/* REACTION */
if(!empty($wo['system']['combo_enable_reaction']) && $wo['system']['combo_enable_reaction'] == 1){
	$wo['plugin_list']['header_css'][] = 'reaction';
	if($wo['loggedin'] == true){ $wo['plugin_list']['footer_js'][] = 'reaction'; }
}  
/* REACTION */
 
if($wo['loggedin'] == true) { 
	//reaction header
	$wo['plugin_list']['header'][] = 'header_combo';
	
	//ADMIN
	if($plugin_page == "admin-plugins"){
		// menu admin   
		$wo['plugin_list']['admin_menu'][] = array('Reaction Setting' => 'reaction_setting', 'Combo Setting' => 'combo_setting');
		$wo['plugin_list']['admin_tab'][] = array('reaction_setting' => 'admin_reaction_setting.phtml','combo_setting' => 'admin_combo_setting.phtml');
	}

	/* COMBO TAGS FRIENDS */
	if(!empty($wo['system']['combo_enable_tag_action'])  && $wo['system']['combo_enable_tag_action'] == 1){
		$wo['plugin_list']['footer_js'][] = 'tag_friend';
		$wo['plugin_list']['publiser_footer'][] = array('name' => 'tag_friend', 'id' => 'tag_friend', 'icon'=> 'fa-user', 'text' => 'people' ); 
		$wo['plugin_list']['plugin_wall_tabs'][] = array('name' => 'tag_friend', 'id' => 'tag_friend', 'icon'=> 'fa-users', 'tab_input' => 'create', 'text' => 'Tag Friends',
'tab_content' => '<div class="publisher-meta publisher_tag_friend js_autocomplete"><i class="fa fa-users fa-fw"></i><ul class="tags"></ul><input type="text" placeholder="Who are you with?" class="who_are_you_with" autocomplete="off"></div>' );
	}
	/* COMBO TAGS FRIENDS */

	/* DRAG & DROP */
	if(!empty($wo['system']['combo_enable_dragdrop']) && $wo['system']['combo_enable_dragdrop'] == 1){
		$wo['plugin_list']['footer_js'][] = 'drag_drop';
	}
	/* DRAG & DROP */

}	  
?>