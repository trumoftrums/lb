<?php
/**
* Ads installer
* Plugins for Wowonder
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
	$p_name = "Ads Advanced";
	$p_version = "1.7.3";
	$p_type = "Ads";
	$p_desc = "This plugin gives to your users the ability uploads ads in your social network.";
	$p_icon = "ads";
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
	
	
	// INSERT ROW INTO plugins
	$sql = "SELECT p_id FROM `plugins_ldrsoft` WHERE p_type='$p_type'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if(!$resource->num_rows){ 
		$sql = "INSERT INTO `plugins_ldrsoft`
		( p_name, p_version, p_type, p_desc, p_icon, p_order, p_active )
		VALUES
		( '$p_name',  '$p_version',  '$p_type',  '$p_desc',  '$p_icon', '10', '1')"; 
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} else { // UPDATE plugins
		$sql = " UPDATE `plugins_ldrsoft` SET p_name='$p_name', p_version='$p_version', p_desc='$p_desc', p_icon='$p_icon' WHERE p_type='$p_type' ";
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	// [2] Create setting
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'plugins_system'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if(!$resource->num_rows){
		$sql = "
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
	if( !$resource->num_rows ){   
		$sql = "INSERT INTO `plugins_system` ( system_version ) VALUES ( '1.0')"; 
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

	//install ads 
	foreach($wo['plugin_ads'] as $k => $v){
	    $langz = "";
		$new_k = 'plugin_ads_'.str_replace("-",'_',$k);
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
	
	// CREATE ads_plans
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'ads_plans'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `ads_plans` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`price` float NOT NULL DEFAULT '0',
		`money_type` varchar(55) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'USD',
		`quantity` int(11)  NOT NULL DEFAULT '0',
		`enable` int(11) NOT NULL DEFAULT '1',
		`created` int(11) NOT NULL DEFAULT '0',
		`updated` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	$sql = "SELECT id FROM ads_plans WHERE id = 1 LIMIT 1";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");	
	if( !$resource->num_rows ){
		$sql="
		INSERT INTO `ads_plans` (`id`, `name`, `type`, `description`, `price`, `quantity`, `created`, `updated`) VALUES
		(1, 'Basic Impression', 'impression', 'Basic plan impression', 1.5, 1000, '".time()."', '".time()."'),
		(2, 'Basic Clicks', 'clicks', 'Basic plan clicks', 1, 100, '".time()."', '".time()."');
		";
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	// CREATE ads_ldrsoft
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'ads_ldrsoft'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){  
	$sql = "
		CREATE TABLE IF NOT EXISTS `ads_ldrsoft` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`campaign_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`user_id` int(11) NOT NULL,
		`page_id` int(11) NOT NULL,
		`post_id` int(11) NOT NULL,
		`title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`display_link` text COLLATE utf8_unicode_ci NOT NULL,
		`type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`type_content` longtext COLLATE utf8_unicode_ci NOT NULL,
		`plan_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
		`plan_id` int(11) NOT NULL,
		`quantity` int(11) NOT NULL,
		`target_location` longtext COLLATE utf8_unicode_ci NOT NULL,
		`target_gender` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'all',
		`impression_stats` int(11) NOT NULL,
		`clicks_stats` int(11) NOT NULL,
		`clicks` int(11) NOT NULL DEFAULT '0',
		`views` int(11) NOT NULL DEFAULT '0',
		`images` longtext COLLATE utf8_unicode_ci NOT NULL,
		`paid` int(11) NOT NULL,
		`status` int(11) NOT NULL DEFAULT '0',  
		`limit_views` int(11) NOT NULL DEFAULT '0',
		`limit_clicks` int(11) NOT NULL DEFAULT '0',
		`limit_ctr` int(11) NOT NULL DEFAULT '0',
		`position` varchar(55) COLLATE utf8_unicode_ci NOT NULL,
		`ad_public` int(11) NOT NULL DEFAULT '0',
		`ad_html` longtext COLLATE utf8_unicode_ci NOT NULL,
		`created` int(11) NOT NULL DEFAULT '0',
		`updated` int(11) NOT NULL DEFAULT '0',
		`date_start` int(11) NOT NULL DEFAULT '0',
		`date_end` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	// ADD COLUMNS plugins_system
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'ads_enable_sandbox'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `ads_enable_sandbox` INT NOT NULL DEFAULT 1 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'ads_no_external_images'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `ads_no_external_images` INT NOT NULL DEFAULT 5 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'ads_no_right_column'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `ads_no_right_column` INT NOT NULL DEFAULT 3 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'enable_ads_on_post'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `enable_ads_on_post` INT NOT NULL DEFAULT 1 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'enable_website_ads_on_post'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `enable_website_ads_on_post` INT NOT NULL DEFAULT 1 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'ads_enable_secure'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `ads_enable_secure` INT NOT NULL DEFAULT 0 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'ads_enable_paypal'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `ads_enable_paypal` INT NOT NULL DEFAULT 1 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}  
	
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'corporate_paypal_email'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `corporate_paypal_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}  
	
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'paypal_email_notification'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `paypal_email_notification` varchar(255) COLLATE utf8_unicode_ci NOT NULL ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}  
	
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'ads_enable_stripe'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `ads_enable_stripe` INT NOT NULL DEFAULT 1 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'stripe_secret_key'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `stripe_secret_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'stripe_publishable_key'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `stripe_publishable_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}  

	/*voguepay*/
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'ads_enable_voguepay'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `ads_enable_voguepay` INT NOT NULL DEFAULT 1 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}   

	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'voguePayMerchantId'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `voguePayMerchantId` varchar(255) COLLATE utf8_unicode_ci NOT NULL ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	/*voguepay*/
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'pagelet_ego'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `pagelet_ego` int(11) NOT NULL DEFAULT '1' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	//ads left
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'enable_ads_left_column'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `enable_ads_left_column` INT NOT NULL DEFAULT 1 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'ads_no_left_column'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `ads_no_left_column` INT NOT NULL DEFAULT 1 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}      
	
	/*PAYPAL SAVE DATA*/
	// CREATE ads_entities
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'ads_purchased'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
				
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `ads_purchased` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`user_id` int(11) NOT NULL DEFAULT '0',
		`payment_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Paypal',
		`item_name` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`item_number` int(11) NOT NULL DEFAULT '0',
		`payment_status` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`payment_amount` float NOT NULL DEFAULT '0',
		`payment_currency` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`quantity` int(11) NOT NULL DEFAULT '0',
		`txn_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`receiver_email` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`payer_email` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`custom` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`created` int(11) NOT NULL DEFAULT '0',
		`updated` int(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`),
		UNIQUE KEY `txn_id` (`txn_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	$sql = "SHOW COLUMNS FROM `ads_plans` LIKE 'money_type'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_plans ADD COLUMN `money_type` varchar(55) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'USD' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	
	/*NEW UPDATE*/
	$sql = "SHOW COLUMNS FROM `ads_ldrsoft` LIKE 'created'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_ldrsoft ADD COLUMN `created` int(11) NOT NULL DEFAULT '0' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	$sql = "SHOW COLUMNS FROM `ads_ldrsoft` LIKE 'updated'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_ldrsoft ADD COLUMN `updated` int(11) NOT NULL DEFAULT '0' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	$sql = "SHOW COLUMNS FROM `ads_plans` LIKE 'created'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_plans ADD COLUMN `created` int(11) NOT NULL DEFAULT '0' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	$sql = "SHOW COLUMNS FROM `ads_plans` LIKE 'updated'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_plans ADD COLUMN `updated` int(11) NOT NULL DEFAULT '0' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	/*153*/
	$sql = "SHOW COLUMNS FROM `ads_ldrsoft` LIKE 'date_start'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_ldrsoft ADD COLUMN `date_start` int(11) NOT NULL DEFAULT '0' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	$sql = "SHOW COLUMNS FROM `ads_ldrsoft` LIKE 'date_end'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_ldrsoft ADD COLUMN `date_end` int(11) NOT NULL DEFAULT '0' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	/*154*/
	$sql = "SHOW COLUMNS FROM `ads_ldrsoft` LIKE 'limit_views'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_ldrsoft ADD COLUMN `limit_views` int(11) NOT NULL DEFAULT '0' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	$sql = "SHOW COLUMNS FROM `ads_ldrsoft` LIKE 'limit_clicks'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_ldrsoft ADD COLUMN `limit_clicks` int(11) NOT NULL DEFAULT '0' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}

	$sql = "SHOW COLUMNS FROM `ads_ldrsoft` LIKE 'limit_ctr'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_ldrsoft ADD COLUMN `limit_ctr` int(11) NOT NULL DEFAULT '0' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 

	$sql = "SHOW COLUMNS FROM `ads_ldrsoft` LIKE 'position'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_ldrsoft ADD COLUMN `position` varchar(55) COLLATE utf8_unicode_ci NOT NULL ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 

	$sql = "SHOW COLUMNS FROM `ads_ldrsoft` LIKE 'ad_public'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_ldrsoft ADD COLUMN `ad_public` int(11) NOT NULL DEFAULT '0' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 

	$sql = "SHOW COLUMNS FROM `ads_ldrsoft` LIKE 'ad_html'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_ldrsoft ADD COLUMN `ad_html` longtext COLLATE utf8_unicode_ci NOT NULL ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'enable_ads_admin'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `enable_ads_admin` INT NOT NULL DEFAULT 1 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	// ads rigth place
	$sql = "SHOW COLUMNS FROM `plugins_system` LIKE 'enable_ad_rigth_place'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `enable_ad_rigth_place` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'timeline,page,group,event' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	
	$sql = "SHOW COLUMNS FROM `ads_plans` LIKE 'enable'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE ads_plans ADD COLUMN `enable` INT NOT NULL DEFAULT '1' ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}   
	
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'ads_reports'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	
	if(!$resource->num_rows){
	   $sql = "
	   CREATE TABLE IF NOT EXISTS `ads_reports` (
	   `report_id` int(9) NOT NULL AUTO_INCREMENT,
	   `report_type` int(3) NOT NULL DEFAULT '0',
	   `report_desc` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
	   `report_ad_id` int(11) NOT NULL DEFAULT '0',
	   `report_user_id` int(11) NOT NULL DEFAULT '0',
	   `report_time` int(11) NOT NULL DEFAULT '0',
	   `report_view` int(3) NOT NULL DEFAULT '0',
	   PRIMARY KEY (`report_id`)
	   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
	   ";  
	   $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
		
	$ready = 1;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge"> 
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
        <title>Plugins for Wowonder &rsaquo; Installer</title>      
		<link rel="stylesheet" href="../themes/wowonder/stylesheet/bootstrap.min.css">
    </head>
    <body>   
		<div class="container mt20">
			<div class="row">
				<div class="col-sm-12">
		
		
					<div class="panel panel-default"> 
						<div class="panel-heading">Welcome to <strong>Plugin Ads Advanced</strong> installation!.</div>
						
						<div class="panel-body pg_1x">	
						
							<div class="col-sm-7">
								<img src="../assets/plugins/img/install_ads_logo.jpg" width="100%">							
								<div class="panel panel-default">
									<div class="panel-heading"><b>File Permissions</b></div>
									<div class="panel-body" style="text-align:justify;">../upload/ads/</div>	
								</div>							
							</div>						
							<div class="col-sm-5">							
								<div class="panel panel-default">
									<div class="panel-heading"><b>Welcome to installation program</b></div>
									<div class="panel-body" style="text-align:justify;">
										Is easy! To get started, click the button below. If you have questions about the installation process or in general, get in touch with us. <a href="https://codecanyon.net/user/ldrmx/">Codecanyon user: LdrMx</a>
									</div>	
								</div>								
								<div class="panel panel-default">
									<div class="panel-heading"><b>Installing Ads Advanced Plugin:</b></div>
									<div class="panel-body" style="text-align:justify;">
										Before continuing, please be sure you have reviewed the installation instructions provided in <b>Documentation</b>.				
									</div>	
								</div>							
							</div>									
							<form id="ldrmxform" class="" autocomplete="off" action="install_ads.php" method="post">              
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