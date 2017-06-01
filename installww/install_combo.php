<?php
/**
 * Combo installer
 * Plugins for Wowonder
 * @author Pp Galvan - LdrMx
 */
$ready = 0;

// enviroment settings
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

/* the config file exist -> start the system */
if(!file_exists('../config.php')) {
    /* the config file doesn't exist -> start the installer */
    header('Location: ../');
} else { 
    // get system configurations
    require('../config.php');
    // enviroment settings
 }
  
// install
if(isset($_POST['submit'])) {
    
    // [1] connect to the db
    $sqlConnect = new mysqli($sql_db_host, $sql_db_user, $sql_db_pass, $sql_db_name);
    if(mysqli_connect_error()){ $ServerErrors[] = "Failed to connect to MySQL: " . mysqli_connect_error(); }
    $sqlConnect->set_charset('utf8');
    // [2] create tabs the database    
	
    //Variables of combo
    $p_name = "Plugin Combo";
    $p_version = "1.2.3";
    $p_type = "Combo";
    $p_desc = "This plugin gives to your users the ability of create more expresion in a post.";
    $p_icon = "combo";
    $p_db_charset = 'utf8';
    $p_db_collation = 'utf8_unicode_ci';

    $sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'plugins_ldrsoft'";
        $resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
  
        if(!$resource->num_rows){
		   $sql = "
		   CREATE TABLE IF NOT EXISTS `plugins_ldrsoft` (
  	       `p_id` int(9) NOT NULL AUTO_INCREMENT,
  	       `p_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  	       `p_version` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  	       `p_type` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  	       `p_desc` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  	       `p_icon` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  	       `p_order` int(3) NOT NULL DEFAULT '0',
  	       `p_active` tinyint(1) NOT NULL DEFAULT '1',
		   `p_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  	       PRIMARY KEY (`p_id`),
  	       UNIQUE KEY `p_type` (`p_type`)
	       ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
   	       ";  
  	       $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
  	    }

    //new key	
	$sql = "SHOW COLUMNS FROM `plugins_ldrsoft` LIKE 'p_key'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){
		$sql = " ALTER TABLE plugins_ldrsoft ADD COLUMN `p_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' ";  
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
		
    /* INSERT ROW INTO plugins_ldrsoft */
   $sql = "SELECT p_id FROM `".$sql_db_name."`.`plugins_ldrsoft` WHERE p_type='$p_type'";
   $resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
    // INSERT ROW INTO plugins
    $sql = "SELECT p_id FROM `".$sql_db_name."`.`plugins_ldrsoft` WHERE p_type='$p_type'";
    $resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
    if(!$resource->num_rows ){   
	   $sql = "INSERT INTO `".$sql_db_name."`.`plugins_ldrsoft`
      ( p_name, p_version, p_type, p_desc, p_icon, p_order, p_active )
      VALUES
      ( '$p_name',  '$p_version',  '$p_type',  '$p_desc',  '$p_icon', '1', '1')"; 
    $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
  }
  // ######### UPDATE PLUGIN VERSION IN plugins
  else
  {
    $sql = " UPDATE `".$sql_db_name."`.`plugins_ldrsoft` SET p_name='$p_name', p_version='$p_version', p_desc='$p_desc', p_icon='$p_icon' WHERE p_type='$p_type' ";
    $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
  }
  
      	  // [2] Create Plugins tabs
  $sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'plugins_system'";
  $resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
  
  if( !$resource->num_rows )
  {  $sql = "
		CREATE TABLE IF NOT EXISTS `plugins_system` (
		  `system_id` int(9) NOT NULL AUTO_INCREMENT,
		  `system_version` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		  PRIMARY KEY (`system_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8
   ";  
  $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
  }
  
  $sql = "SELECT system_id FROM `plugins_system` WHERE system_id='1'";
  $resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
  
  if( !$resource->num_rows )
  {   
  $sql = "INSERT INTO `plugins_system`( system_version ) VALUES ( '1.0')"; 
  $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
  }
  
	//INSTALL LANGUAGE
	require('../assets/plugins/languages/plugins_lang.php');
  
	$resource = $sqlConnect->query("SHOW COLUMNS FROM Wo_Langs");
	while ($fetched_data = $resource->fetch_assoc()) {
		$data[] = $fetched_data['Field'];
	}
	unset($data[0]);
	unset($data[1]);
		
  //install plugins
	foreach($wo['plugins'] as $k => $v){
	    $langz = "";
		$new_k = 'plugin_'.str_replace("-",'_',$k);
		$sql = "SELECT * FROM `Wo_Langs` WHERE lang_key='{$new_k}' LIMIT 1";
		$resource = $sqlConnect->query($sql);
		if(!$resource->num_rows ){
			$sqlConnect->query("INSERT INTO `Wo_Langs` (`lang_key`) VALUES ('{$new_k}')");
			$new_lang_id = $sqlConnect->insert_id;
			$javascript_lang_import_first = TRUE; 	
		    foreach($data as $key => $val){
			    if( !$javascript_lang_import_first ) $langz .= ", ";
				$langz .= "`{$val}`='{$v}'";
				$javascript_lang_import_first = FALSE;
			}			
			$sql_new = "UPDATE `Wo_Langs` SET {$langz} WHERE id='{$new_lang_id}' LIMIT 1";			
			$sqlConnect->query($sql_new);
		} /*else {
			$get_id = $resource->fetch_assoc();
			$javascript_lang_import_first = TRUE; 	
		    foreach($data as $key => $val){
			    if( !$javascript_lang_import_first ) $langz .= ", ";
				$langz .= "`{$val}`='{$v}'";
				$javascript_lang_import_first = FALSE;
			}			
			$sql_new = "UPDATE `Wo_Langs` SET {$langz} WHERE lang_key='{$new_k}' LIMIT 1";			
			$sqlConnect->query($sql_new);
			
			$db_update =  $sqlConnect->affected_rows;
			echo $sql_new;
			if($db_update == 1){
			   echo json_encode(array('result' => 1, 'message' => "Successfully info have been updated for:".$new_k, 'results'=> $db_update, 'success' => true));
			} else {
			   echo json_encode(array('result' => 0, 'message' => 'Cant update try more late! for:'.$new_k, 'results'=> $db_update, 'error' => true));
			}
		}*/
	}		
	//install combo
	foreach($wo['plugin_combo'] as $k => $v){
	 $langz = "";
	 $new_k = 'plugin_combo_'.str_replace("-",'_',$k);
	 $sql = "SELECT * FROM `Wo_Langs` WHERE lang_key='{$new_k}' LIMIT 1";
	 $resource = $sqlConnect->query($sql);
	 if(!$resource->num_rows ){
			$sqlConnect->query("INSERT INTO `Wo_Langs` (`lang_key`) VALUES ('{$new_k}')");
			$new_lang_id = $sqlConnect->insert_id;
			$javascript_lang_import_first = TRUE; 	
		    foreach($data as $key => $val){
			    if( !$javascript_lang_import_first ) $langz .= ", ";
				$langz .= "`{$val}`='{$v}'";
				$javascript_lang_import_first = FALSE;
			}			
			$sql_new = "UPDATE `Wo_Langs` SET {$langz} WHERE id='{$new_lang_id}' LIMIT 1";			
			$sqlConnect->query($sql_new);
	 } /*else {
			$get_id = $resource->fetch_assoc();
			$javascript_lang_import_first = TRUE; 	
		    foreach($data as $key => $val){
			    if( !$javascript_lang_import_first ) $langz .= ", ";
				$langz .= "`{$val}`='{$v}'";
				$javascript_lang_import_first = FALSE;
			}			
			$sql_new = "UPDATE `Wo_Langs` SET {$langz} WHERE lang_key='{$new_k}' LIMIT 1";			
			$sqlConnect->query($sql_new);
			
			$db_update =  $sqlConnect->affected_rows;
			echo $sql_new;
			if($db_update == 1){
			   echo json_encode(array('result' => 1, 'message' => "Successfully info have been updated for:".$new_k, 'results'=> $db_update, 'success' => true));
			} else {
			   echo json_encode(array('result' => 0, 'message' => 'Cant update try more late! for:'.$new_k, 'results'=> $db_update, 'error' => true));
			}
		}*/
	}

    foreach($wo['plugin_reactions'] as $k => $v){
		$langz = "";
		$sql = "SELECT * FROM `Wo_Langs` WHERE lang_key='{$k}' LIMIT 1";
		$resource = $sqlConnect->query($sql);
		
		if(!$resource->num_rows){
			$sqlConnect->query("INSERT INTO `Wo_Langs` (`lang_key`) VALUES ('{$k}')");
			$new_lang_id = $sqlConnect->insert_id;
			$javascript_lang_import_first = TRUE; 	
			foreach($data as $key => $val){
				if( !$javascript_lang_import_first ) $langz .= ", ";
				$langz .= "`{$val}`='{$v}'";
				$javascript_lang_import_first = FALSE;
			}			
			$sql_new = "UPDATE `Wo_Langs` SET {$langz} WHERE id='{$new_lang_id}' LIMIT 1";			
			$sqlConnect->query($sql_new);
		} /*else {
			$get_id = $resource->fetch_assoc();
			$javascript_lang_import_first = TRUE; 	
		    foreach($data as $key => $val){
			    if( !$javascript_lang_import_first ) $langz .= ", ";
				$langz .= "`{$val}`='{$v}'";
				$javascript_lang_import_first = FALSE;
			}			
			$sql_new = "UPDATE `Wo_Langs` SET {$langz} WHERE lang_key='{$new_k}' LIMIT 1";			
			$sqlConnect->query($sql_new);
			
			$db_update =  $sqlConnect->affected_rows;
			echo $sql_new;
			if($db_update == 1){
			   echo json_encode(array('result' => 1, 'message' => "Successfully info have been updated for:".$new_k, 'results'=> $db_update, 'success' => true));
			} else {
			   echo json_encode(array('result' => 0, 'message' => 'Cant update try more late! for:'.$new_k, 'results'=> $db_update, 'error' => true));
			}
		} */
	} 		
  //INSTALL LANGUAGE

      /* START REACTION */
	//$sqlConnect->query("TRUNCATE TABLE reactions"); 
	//SQL REACTIONS
	//INSTALL LANGUAGE
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'reactions'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");  
	if( !$resource->num_rows ){
	$sql = "CREATE TABLE `reactions` (
	`reaction_id` int(11) PRIMARY KEY auto_increment,
	`reaction_key` varchar(55) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`reaction_filetype` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`reaction_colour` varchar(12) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`reaction_lang` INT(11) NOT NULL DEFAULT '0',
	`reaction_status` INT(11) NOT NULL DEFAULT '1',
	`reaction_order` INT(11) NOT NULL DEFAULT '0',
	`reaction_date` INT(11) NOT NULL DEFAULT '0',
	`reaction_hits` INT(55) NOT NULL DEFAULT '0') ";  
	$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
  
	$sql = "SELECT * FROM `reactions` WHERE reaction_id='1' LIMIT 1";
	$resource = $sqlConnect->query($sql);
	if(!$resource->num_rows ){
	 $resource_1 = $sqlConnect->query("SELECT id FROM `Wo_Langs` WHERE lang_key='reaction_1' LIMIT 1");
	 if($resource_1->num_rows > 0){
		 $rl = $resource_1->fetch_assoc();
		 $sqlConnect->query("
		 INSERT INTO `reactions` (`reaction_id`, `reaction_key`, `reaction_filetype`, `reaction_colour`, `reaction_lang`, `reaction_status`, `reaction_order`, `reaction_date`, `reaction_hits`) VALUES
		(1, 'like', 'png', 'opt-active', ".$rl['id'].", 1, 1, 1485545712, 0);
		 ");
	 }
	}	
	
	$sql = "SELECT * FROM `reactions` WHERE reaction_id='2' LIMIT 1";
	$resource = $sqlConnect->query($sql);
	if(!$resource->num_rows ){
	 $resource_2 = $sqlConnect->query("SELECT id FROM `Wo_Langs` WHERE lang_key='reaction_2' LIMIT 1");
	 if($resource_2->num_rows > 0){
		 $rl = $resource_2->fetch_assoc();
		 $sqlConnect->query("
		 INSERT INTO `reactions` (`reaction_id`, `reaction_key`, `reaction_filetype`, `reaction_colour`, `reaction_lang`, `reaction_status`, `reaction_order`, `reaction_date`, `reaction_hits`) VALUES
		(2, 'love', 'png', 'red-opt', ".$rl['id'].", 1, 2, 1485546419, 0);
		 ");
	 }
	}	
	
	$sql = "SELECT * FROM `reactions` WHERE reaction_id='3' LIMIT 1";
	 $resource = $sqlConnect->query($sql);
	 if(!$resource->num_rows ){
		 $resource_3 = $sqlConnect->query("SELECT id FROM `Wo_Langs` WHERE lang_key='reaction_3' LIMIT 1");
		 if($resource_3->num_rows > 0){
			 $rl = $resource_3->fetch_assoc();
			 $sqlConnect->query("
			 INSERT INTO `reactions` (`reaction_id`, `reaction_key`, `reaction_filetype`, `reaction_colour`, `reaction_lang`, `reaction_status`, `reaction_order`, `reaction_date`, `reaction_hits`) VALUES
			(3, 'haha', 'png', 'yellow-opt', ".$rl['id'].", 1, 3, 1485546936, 0);
			 ");
		 }
	 }	
	 
	 $sql = "SELECT * FROM `reactions` WHERE reaction_id='4' LIMIT 1";
	 $resource = $sqlConnect->query($sql);
	 if(!$resource->num_rows ){
		 $resource_4 = $sqlConnect->query("SELECT id FROM `Wo_Langs` WHERE lang_key='reaction_4' LIMIT 1");
		 if($resource_4->num_rows > 0){
			 $rl = $resource_4->fetch_assoc();
			 $sqlConnect->query("
			 INSERT INTO `reactions` (`reaction_id`, `reaction_key`, `reaction_filetype`, `reaction_colour`, `reaction_lang`, `reaction_status`, `reaction_order`, `reaction_date`, `reaction_hits`) VALUES
			(4, 'wow', 'png', 'yellow-opt', ".$rl['id'].", 1, 4, 1485547922, 0);
			 ");
		 }
	 }	

	 $sql = "SELECT * FROM `reactions` WHERE reaction_id='5' LIMIT 1";
	 $resource = $sqlConnect->query($sql);
	 if(!$resource->num_rows ){
		 $resource_5 = $sqlConnect->query("SELECT id FROM `Wo_Langs` WHERE lang_key='reaction_5' LIMIT 1");
		 if($resource_5->num_rows > 0){
			 $rl = $resource_5->fetch_assoc();
			 $sqlConnect->query("
			 INSERT INTO `reactions` (`reaction_id`, `reaction_key`, `reaction_filetype`, `reaction_colour`, `reaction_lang`, `reaction_status`, `reaction_order`, `reaction_date`, `reaction_hits`) VALUES
			(5, 'sad', 'png', 'yellow-opt', ".$rl['id'].", 1, 5, 1485548523, 0);
			 ");
		 }
	 }	

	 $sql = "SELECT * FROM `reactions` WHERE reaction_id='6' LIMIT 1";
	 $resource = $sqlConnect->query($sql);
	 if(!$resource->num_rows ){
		 $resource_6 = $sqlConnect->query("SELECT id FROM `Wo_Langs` WHERE lang_key='reaction_6' LIMIT 1");
		 if($resource_6->num_rows > 0){
			 $rl = $resource_6->fetch_assoc();
			 $sqlConnect->query("
			 INSERT INTO `reactions` (`reaction_id`, `reaction_key`, `reaction_filetype`, `reaction_colour`, `reaction_lang`, `reaction_status`, `reaction_order`, `reaction_date`, `reaction_hits`) VALUES
			(6, 'angry', 'png', 'orange-opt', ".$rl['id'].", 1, 6, 1485549175, 0);
			 ");
		 }
	 }

	 $sql = "SELECT * FROM `reactions` WHERE reaction_id='7' LIMIT 1";
	 $resource = $sqlConnect->query($sql);
	 if(!$resource->num_rows ){
		 $resource_7 = $sqlConnect->query("SELECT id FROM `Wo_Langs` WHERE lang_key='reaction_7' LIMIT 1");
		 if($resource_7->num_rows > 0){
			 $rl = $resource_7->fetch_assoc();
			 $sqlConnect->query("
			 INSERT INTO `reactions` (`reaction_id`, `reaction_key`, `reaction_filetype`, `reaction_colour`, `reaction_lang`, `reaction_status`, `reaction_order`, `reaction_date`, `reaction_hits`) VALUES
			(7, 'dislike', 'png', 'opt-active', ".$rl['id'].", 1, 7, 1485554614, 0);
			 ");
		 }
	 }	
  
    //CREATING BONUS FOR ALL PLUGINS  
	$sql = "SHOW COLUMNS FROM `".$sql_db_name."`.`plugins_system` LIKE 'bonus_enable_home_left_column'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){
	$sql = " ALTER TABLE plugins_system ADD COLUMN `bonus_enable_home_left_column` INT NOT NULL DEFAULT 1 ";    
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	$sql = "SHOW COLUMNS FROM `".$sql_db_name."`.`plugins_system` LIKE 'bonus_enable_plublisher_new'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
	$sql = " ALTER TABLE plugins_system ADD COLUMN `bonus_enable_plublisher_new` INT NOT NULL DEFAULT 1 ";    
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
  
  
  	 //update language
	 $langz = ""; 
     $resource = $sqlConnect->query("SELECT * FROM `Wo_Langs` WHERE lang_key='plugin_combo_reaction_post' LIMIT 1");	 
     if($resource->num_rows>0){
	        $get_lang = $resource->fetch_assoc();
			$javascript_lang_import_first = TRUE; 	
		    foreach($data as $key => $val){
			    if( !$javascript_lang_import_first ){ $langz .= ", "; }
				$langz .= "`{$val}`='{$wo['plugin_combo']['reaction_post']}'";
				$javascript_lang_import_first = FALSE;
			}						
			$sqlConnect->query("UPDATE `Wo_Langs` SET {$langz} WHERE id='{$get_lang['id']}' LIMIT 1");
	 }
	$sql = "SHOW COLUMNS FROM `".$sql_db_name."`.`Wo_Activities` LIKE 'app_src'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE Wo_Activities ADD COLUMN `app_src` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	$sql = "SHOW COLUMNS FROM `".$sql_db_name."`.`Wo_Notifications` LIKE 'app_src'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE Wo_Notifications ADD COLUMN `app_src` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	$sqlConnect->query("UPDATE `Wo_Notifications` SET `app_src`=`text` WHERE type='reaction_post'"); 
	$sqlConnect->query("UPDATE `Wo_Notifications` SET `text`='' WHERE type='reaction_post'"); 
   /*tags*/
    $sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'posts_tags'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");  
	if( !$resource->num_rows ){
	$sql = "CREATE TABLE `posts_tags` (
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`post_id` INT(11) NOT NULL DEFAULT '0',
  	UNIQUE KEY `user_id_post_id` (`user_id`,`post_id`)
	) ";  
	$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
		
	$sql = "SHOW COLUMNS FROM ".$sql_db_name.".`Wo_Likes` LIKE 'reaction'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){
		$sql = " ALTER TABLE Wo_Likes ADD COLUMN `reaction` VARCHAR(50)  COLLATE utf8_unicode_ci NOT NULL DEFAULT 'like' ";
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 	
	$sql = "SHOW COLUMNS FROM `".$sql_db_name."`.`plugins_system` LIKE 'combo_enable_reaction'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){
		$sql = " ALTER TABLE plugins_system ADD COLUMN `combo_enable_reaction` INT(11) NOT NULL DEFAULT '1' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	$sql = "SHOW COLUMNS FROM `".$sql_db_name."`.`plugins_system` LIKE 'combo_enable_tag_action'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `combo_enable_tag_action` INT(11) NOT NULL DEFAULT '1' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
		$sql = "SHOW COLUMNS FROM `".$sql_db_name."`.`plugins_system` LIKE 'combo_enable_welcome'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){
		$sql = " ALTER TABLE plugins_system ADD COLUMN `combo_enable_welcome` INT(11) NOT NULL DEFAULT '1' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	$sql = "SHOW COLUMNS FROM `".$sql_db_name."`.`plugins_system` LIKE 'combo_enable_dragdrop'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `combo_enable_dragdrop` INT(11) NOT NULL DEFAULT '1' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
  /*end all*/
   $ready = 1;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge"> 
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
        <title>Plugins for Wowonder 1+ &rsaquo; Installer</title>      
		<link rel="stylesheet" href="../themes/wowonder/stylesheet/bootstrap.min.css">
    </head>
	<style>
		body{ font-size:12px; }
		.btn_bt{border:1px solid #CCC; background-color:#CCC; text-align:center; padding:5px;}
	</style>
    <body>
		<div class="container mt20">
			<div class="row">
				<div class="col-sm-12">
		
		
					<div class="panel panel-default">               
						<div class="panel-heading"> Welcome to <strong>Plugin Combo</strong> installation!</div>
						<div class="panel-body pg_1x">	
							<div class="col-sm-7"><img src="../assets/plugins/img/cover.jpg" width="100%"></div>
						
							<div class="col-sm-5">							
								<div class="panel panel-default">
									<div class="panel-heading"><b>Welcome to installation program</b></div>
									<div class="panel-body" style="text-align:justify;">
										Is easy! To get started, click the button below. If you have questions about the installation process or in general, get in touch with us.
									</div>	
								</div>											
								<div class="panel panel-default">
									<div class="panel-heading"><b>Installing Plugins:</b></div>
									<div class="panel-body" style="text-align:justify;">
									Before continuing, please be sure you have reviewed the installation instructions provided in <b>Documentation</b>.				
									</div>	
								</div>							
							</div>
						
							<form autocomplete="off" action="install_combo.php" method="post">                             
								<?php if($ready == 0){
								echo '<button name="submit" type="submit" class="btn btn-primary" style="width:100%;">Update / Install Now!!</button>';
								} else if($ready == 1){ 
								echo 'Plugin installed <a href="../index.php?link1=admin-plugins">CLICK HERE PLEASE</a>';
								header('Location: ../index.php?link1=admin-plugins');
								?>
									   <script type="text/javascript">
											var URL = '../index.php?link1=admin-plugins';
											var delay = 1000; //Your delay in milliseconds
											setTimeout(function(){ window.location = URL; }, delay);
									   </script>
								<?php } ?>
							</form>
						 
						</div>
					</div>
				
				</div>
			</div>
		</div>       
    </body>
</html>