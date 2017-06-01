<?php
/**
* Share installer
* @author Pepe Galvan - LdrMx
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
}

// install
if(isset($_POST['submit'])) {

	// [1] connect to the db
	$sqlConnect = new mysqli($sql_db_host, $sql_db_user, $sql_db_pass, $sql_db_name);
	if(mysqli_connect_error()){ $ServerErrors[] = "Failed to connect to MySQL: " . mysqli_connect_error(); }
	$sqlConnect->set_charset('utf8');
	
	// [2] create tabs the database    
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'plugins_ldrsoft'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");	
	if( !$resource->num_rows ){  
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
	
	//Variables of share
	$p_name = "Share Post Plugin";
	$p_version = "1.2.3";
	$p_type = "Share";
	$p_desc = "This plugin gives to your users the ability share a post, getting information of post.";
	$p_icon = "share";
	$p_db_charset = 'utf8';
	$p_db_collation = 'utf8_unicode_ci';
	
	/* INSERT ROW INTO plugins_ldrsoft */
	$sql = "SELECT p_id FROM `".$sql_db_name."`.`plugins_ldrsoft` WHERE p_type='$p_type'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	
	// INSERT ROW INTO plugins
	$sql = "SELECT p_id FROM `".$sql_db_name."`.`plugins_ldrsoft` WHERE p_type='$p_type'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){  
		$sql = "INSERT INTO `".$sql_db_name."`.`plugins_ldrsoft`
		( p_name, p_version, p_type, p_desc, p_icon, p_order, p_active )
		VALUES
		( '$p_name',  '$p_version',  '$p_type',  '$p_desc',  '$p_icon', '1', '1')"; 
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}else{ 
		//UPDATE plugins
		$sql = " UPDATE `".$sql_db_name."`.`plugins_ldrsoft` SET p_name='$p_name', p_version='$p_version', p_desc='$p_desc', p_icon='$p_icon' WHERE p_type='$p_type' ";
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	// [3] Create config
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'plugins_system'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");	
	if( !$resource->num_rows ){ 
		$sql = "
		CREATE TABLE IF NOT EXISTS `plugins_system` (
		`system_id` int(9) NOT NULL AUTO_INCREMENT,
		`system_version` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		PRIMARY KEY (`system_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	$sql = "SELECT system_id FROM `".$sql_db_name."`.`plugins_system` WHERE system_id='1'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	
	if( !$resource->num_rows ){
	$sql = "INSERT INTO `".$sql_db_name."`.`plugins_system` ( system_version ) VALUES ( '1.0')"; 
	$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	//INSTALL LANGUAGE
	require('../assets/plugins/languages/plugins_lang.php');
	//get all languages
	$resource = $sqlConnect->query("SHOW COLUMNS FROM Wo_Langs");
	while ($fetched_data = $resource->fetch_assoc()) {
		$data[] = $fetched_data['Field'];
	}
	unset($data[0]);
	unset($data[1]);

	//install plugins lang 
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

	foreach($wo['plugin_share'] as $k => $v){
		$langz = "";
		$new_k = 'plugin_share_'.str_replace("-",'_',$k);
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
	//INSTALL LANGUAGE
	
	//CREATING BONUS FOR ALL PLUGINS  
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'bonus_enable_home_left_column'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){
		$sql = " ALTER TABLE plugins_system ADD COLUMN `bonus_enable_home_left_column` INT NOT NULL DEFAULT 1 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'bonus_enable_plublisher_new'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){
		$sql = " ALTER TABLE plugins_system ADD COLUMN `bonus_enable_plublisher_new` INT NOT NULL DEFAULT 1 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	/*share settins*/
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'share_post_advanced'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `share_post_advanced` INT(2) NOT NULL DEFAULT 1 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	$sql = "SHOW COLUMNS FROM `Wo_Posts` LIKE 'shares'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){
		$sql = " ALTER TABLE Wo_Posts ADD COLUMN `shares` INT NOT NULL DEFAULT 0 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	$sql = "SHOW COLUMNS FROM `Wo_Posts` LIKE 'origin_id'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){
		$sql = " ALTER TABLE Wo_Posts ADD COLUMN `origin_id` INT NOT NULL DEFAULT 0 ";    
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
        <title>Plugins for Sngine v2+ &rsaquo; Installer</title>      
		<link rel="stylesheet" href="../themes/wowonder/stylesheet/bootstrap.min.css">
		<link rel="stylesheet" href="../assets/plugins/stylesheet/leader.css">
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
						<div class="panel-heading"> Welcome to <strong>Share Post Plugin</strong> installation!</div>
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
									
							<form autocomplete="off" action="install_share.php" method="post">                             
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