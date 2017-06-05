<?php 
 /* star plugins
  * system of plugins V2
  * @autor Pp Galvan - LdrMx
  */
 //location in script
 $plugin_page = '';
 if ($wo['loggedin'] == true && !isset($_GET['link1'])) { $plugin_page = 'home'; } elseif (isset($_GET['link1'])) { $plugin_page = $_GET['link1']; }
 if ((!isset($_GET['link1']) && $wo['loggedin'] == false) || (isset($_GET['link1']) && $wo['loggedin'] == false && $plugin_page == 'home')) { $plugin_page = 'welcome'; }
 
 $wo['plugin_page'] = $plugin_page;
 $wo['HtmlBP'] = '';	

 /* plugin classes & function */ 
 require_once('class_plugins.php');
 require_once('functions-general.php');
 require_once('functions-bigpipe.php');

 /* system setting */
 $wo['system'] = array();
 $get_system = $sqlConnect->query("SHOW TABLES FROM `".$sql_db_name."` LIKE 'plugins_system'");  
 if($get_system->num_rows){
	 $get_options = $sqlConnect->query("SELECT * FROM `plugins_system`");
	 $wo['system'] = $get_options->fetch_assoc();
 }

 /* get all plugins */
 $all_plugins = array();  
 $get_plugins = $sqlConnect->query("SHOW TABLES FROM `".$sql_db_name."` LIKE 'plugins_ldrsoft'");  
 if($get_plugins->num_rows){ 
	 $ldrsoft = new LdrSoft();
	 $all_plugins = $ldrsoft->get_plugins_list("", false, false, " p_id DESC ", " p_active = '1' ");
 }

 /* load plugin actived */
 $wo['plugin_list'] = array();
 if($all_plugins['plugins']){ foreach($all_plugins['plugins'] as $plugin){  if( file_exists("plugin_{$plugin['p_type']}.php") ){  include "plugin_{$plugin['p_type']}.php";  }  } }

 /* link to admin area */
 $wo['plugin_list']['plugin_wo'][] = array('admin-plugins'=> 'admin_plugins' ); 
 $wo['plugin_list']['plugin_actived'] = $all_plugins['active'];
 
 $wo['plugin_list']['admin_menu'][] = array('Bonus' => 'admin_bonus');
 $wo['plugin_list']['admin_tab'][] = array('admin_bonus' => 'admin_bonus.phtml');
?>