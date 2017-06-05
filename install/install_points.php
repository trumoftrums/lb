<?php
/**
* Points installer
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
	$p_name = "Activities Points Advanced";
	$p_version = "1.0.3";
	$p_type = "Points";
	$p_desc = "Points allows your users to benefit from activity on your site, earn points on specific actions.";
	$p_icon = "points";
	$p_db_charset = 'utf8';
	$p_db_collation = 'utf8_unicode_ci';
	
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
	
	// INSERT  plugins
	$sql = "SELECT p_id FROM `plugins_ldrsoft` WHERE p_type='$p_type'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	
	if( !$resource->num_rows ){   
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
	
	//install plugins lang point
	foreach($wo['plugin_point'] as $k => $v){
	    $langz = "";
		$new_k = 'plugin_point_'.str_replace("-",'_',$k);
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
	
	// CREATE actionpoints
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'actionpoints'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");	
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `actionpoints` (
		`action_id` int(11) NOT NULL auto_increment,
		`action_type` varchar(50) NOT NULL,
		`action_enabled` tinyint(1) NOT NULL default '1',
		`action_name` varchar(255) NOT NULL,
		`action_points` int(11) NOT NULL,
		`action_pointsmax` int(11) NOT NULL default '0',
		`action_rolloverperiod` int(11) NOT NULL default '0',
		`action_requiredplugin` varchar(100) default NULL,
		`action_group` tinyint(4) NOT NULL default '0',
		PRIMARY KEY  (`action_id`),
		KEY `action_type` (`action_type`)
		) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	//$sqlConnect->query("TRUNCATE TABLE actionpoints");
	
	# CUSTOM ACTIONS
	$sql = "SELECT action_id FROM actionpoints WHERE action_id = '106' LIMIT 1";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	
	if( !$resource->num_rows ){
		$sql="
		INSERT INTO `actionpoints` (`action_id`, `action_type`, `action_enabled`, `action_name`, `action_points`, `action_pointsmax`, `action_rolloverperiod`, `action_requiredplugin`, `action_group`) VALUES
		(1, 'transferpoints', 1, 'Transfer Points', 1, 0, 0, NULL, 0),
		(2, 'givepoints', 1, 'Give Points', 1, 0, 0, NULL, 0),
		(101, 'invite', 1, 'Invite Friends (for each invited friend)', 200, 0, 0, NULL, 101),
		(102, 'refer', 1, 'Refer friends (actual signup)', 80, 0, 0, NULL, 101),
		(103, 'signup', 1, 'Signup Bonus', 20, 20, 86400, NULL, 101),
		(104, 'addfriend', 1, 'Add a friend', 1, 10, 86400, NULL, 100),
		(105, 'editphoto', 1, 'Upload profile photo', 100, 100, 86400, NULL, 100),
		(106, 'editprofile', 1, 'Edit / Update profile', 100, 100, 86400, NULL, 100),
		(107, 'editcover', 1, 'Update Cover', 100, 100, 86400, NULL, 100),
		(108, 'login', 1, 'Login to site (requires logout)', 1, 10, 86400, NULL, 100),
		(109, 'adclick', 1, 'Clicking on an ad', 10, 100, 0, NULL, 100),
		(110, 'newevent', 1, 'Create new event', 10, 200, 86400, 'Sngine Events plugin', 3),
		(111, 'postblog', 1, 'Post a blog', 10, 200, 86400, 'Sngine Blogs plugin', 5),
		(112, 'postclassified', 1, 'Create a classified', 10, 200, 86400, 'Sngine Classifieds plugin', 4),
		(113, 'newalbum', 1, 'Upload an album', 10, 200, 86400, NULL, 6),
		(114, 'newmedia', 1, 'Upload new photo / photo to album', 10, 1000, 86400, NULL, 6),
		(115, 'newgroup', 1, 'Create new group', 20, 200, 86400, NULL, 1),
		(116, 'joingroup', 1, 'Join a group', 10, 100, 86400, NULL, 1),
		(117, 'leavegroup', 1, 'Leave a group', 1, 10, 86400, NULL, 1),
		(118, 'newpoll', 1, 'Create a poll', 10, 0, 0, 'Sngine Polls plugin', 2),
		(119, 'votepoll', 1, 'Participate in poll', 1, 0, 0, 'Sngine Polls plugin', 2),
		(120, 'newmusic', 1, 'Adding a Song', 10, 100, 86400, NULL, 9),
		(121, 'post', 1, 'New Post', 1, 100, 0, NULL, 100),
		(122, 'comment', 1, 'Comment a post', 1, 100, 0, NULL, 100),
		(123, 'like', 1, 'Like a post', 1, 100, 0, NULL, 100),
		(124, 'share', 1, 'Share a post', 1, 100, 0, NULL, 100),
		(125, 'newpage', 1, 'Create new page', 10, 200, 86400, NULL, 100),
		(126, 'signup_with_invite', 1, 'Signup with invitation', 200, 0, 0, NULL, 0);
		";
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	// CREATE userpointcounters
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'userpointcounters'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");	
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `userpointcounters` (
		`user_id` int(11) NOT NULL,
		`action_id` int(11) NOT NULL,
		`lastrollover` int(4) NOT NULL default '0',
		`amount` int(11) NOT NULL default '0',
		`cumulative` int(11) NOT NULL default '0',
		PRIMARY KEY  (`user_id`,`action_id`)
		) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	// CREATE userpointstats
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'userpointstats'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");	
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `userpointstats` (
		`id` int(9) NOT NULL auto_increment,
		`user_id` int(11) NOT NULL,
		`date` int(9) NOT NULL default '0',
		`earn` int(9) NOT NULL default '0',
		`spend` int(9) NOT NULL default '0',
		PRIMARY KEY  (`id`),
		UNIQUE KEY `user_id` (`user_id`,`date`)
		) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	// CREATE userpointstats
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'uptransactions'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");	
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `uptransactions` (
		`id` int(11) NOT NULL auto_increment,
		`user_id` int(11) NOT NULL,
		`type` int(11) NOT NULL,
		`cat` int(11) NOT NULL default '0',
		`state` tinyint(4) NOT NULL,
		`text` text,
		`date` int(11) NOT NULL,
		`amount` int(11) NOT NULL,
		PRIMARY KEY  (`id`)
		) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	// CREATE userpointranks
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'userpointranks'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");	
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `userpointranks` (
		`id` int(11) NOT NULL auto_increment,
		`amount` int(11) NOT NULL,
		`text` varchar(100) NOT NULL,
		PRIMARY KEY  (`id`)
		) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	$sql = "SELECT id FROM userpointranks WHERE id = '1' LIMIT 1";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");	
	if( !$resource->num_rows ){
		$sql="
		INSERT IGNORE INTO `userpointranks` (`id`, `amount`, `text`) VALUES 
		(1, 0, 'Rookie'),
		(2, 500, 'Lieutenant'),
		(3, 1000, 'Member'),
		(4, 2000, 'Advanced Member'),
		(5, 10000, 'King'),
		(6, 100000, 'Impossible');
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	
	// CREATE userpoints
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'userpoints'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");	
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `userpoints` (
		`user_id` int(11) NOT NULL,
		`count` int(11) NOT NULL default '0',
		`totalearned` int(11) NOT NULL default '0',
		`totalspent` int(11) NOT NULL default '0',
		PRIMARY KEY  (`user_id`),
		KEY `totalearned` (`totalearned`)
		) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	//$sqlConnect->query("DROP TABLE userpointspender"); // clears
	
	// CREATE userpointspender
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'userpointspender'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `userpointspender` (
		`id` int(11) NOT NULL auto_increment,
		`type` int(11) NOT NULL default '0',
		`name` varchar(100) NOT NULL,
		`title` varchar(255) NOT NULL,
		`body` text NOT NULL,
		`date` int(11) NOT NULL default '0',
		`photo` int(11) NOT NULL default '0',
		`ext` varchar(10) default NULL,
		`cost` int(11) NOT NULL default '0',
		`cant` int(11) NOT NULL default '0',
		`views` int(11) NOT NULL default '0',
		`comments` int(11) NOT NULL default '0',
		`comments_allowed` tinyint(1) NOT NULL default '1',
		`enabled` tinyint(1) NOT NULL default '1',
		`transact_state` int(11) NOT NULL default '0',
		`metadata` text NOT NULL,
		`redirect_on_buy` varchar(255) default NULL,
		`tags` varchar(255) default NULL,
		`engagements` int(11) NOT NULL default '0',
		`levels` text,
		`appear_in_transactions` int(11) NOT NULL default '1',
		PRIMARY KEY  (`id`),
		KEY `type` (`type`)
		) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	$sql = "SELECT id FROM userpointspender WHERE id ='1' LIMIT 1";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	//echo json_encode($resource->num_rows);
	if(!$resource->num_rows){
		$sql=" INSERT INTO `userpointspender` (`id`, `type`, `name`, `title`, `body`, `date`, `photo`, `ext`, `cost`, `cant`, `views`, `comments`, `comments_allowed`, `enabled`, `transact_state`, `metadata`, `redirect_on_buy`, `tags`, `engagements`, `levels`, `appear_in_transactions`) VALUES
		(1, 1, '', 'Test', 'Spend 1 point in this test', 1485375516, 0, NULL, 1, 100000, 0, 0, 1, 1, 1, 'a:2:{s:3:\"url\";s:0:\"\";s:1:\"t\";i:1;}', '', '', 0, NULL, 1),
		(2, 2, '', 'Active ads', 'Active an ads of this website, the admin will to contact for active you ads.', 1485375517, 0, NULL, 1000, 10, 0, 0, 1, 1, 1, 'a:2:{s:3:\"url\";s:0:\"\";s:1:\"t\";i:1;}', '', '', 0, NULL, 1),
		(3, 3, '', 'Invitations', 'Buy more invitations, and win more points', 1485375518, 0, NULL, 1000, 1000, 0, 0, 1, 1, 1, 'a:2:{s:3:\"url\";s:0:\"\";s:1:\"t\";i:1;}', '', '', 0, NULL, 1);
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'userpointspender_users'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `userpointspender_users` (
		`id` int(11) NOT NULL auto_increment,
		`user_id` int(4) NOT NULL default '0',
		`offer` int(11) NOT NULL default '0',
		`stamp` int(11) NOT NULL default '0',
		`status` int(11) NOT NULL default '0',
		`transaction_id` int(11) NOT NULL default '0',
		PRIMARY KEY  (`id`)
		) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	//$sqlConnect->query("DROP TABLE userpointearner"); // of combo 1.0 to 1.0.1
	
	// CREATE userpoints
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'userpointearner'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `userpointearner` (
		`id` int(11) NOT NULL auto_increment,
		`type` int(4) NOT NULL default '0',
		`name` varchar(100) NOT NULL,
		`title` varchar(255) NOT NULL,
		`body` text NOT NULL,
		`date` int(4) NOT NULL default '0',
		`photo` int(11) NOT NULL default '0',
		`ext` varchar(10) default NULL,
		`cost` int(11) NOT NULL default '0',
		`cant` int(11) NOT NULL default '0',
		`views` int(11) NOT NULL default '0',
		`comments` int(11) NOT NULL default '0',
		`comments_allowed` tinyint(1) NOT NULL default '1',
		`enabled` tinyint(1) NOT NULL default '1',
		`transact_state` int(11) NOT NULL default '0',
		`metadata` text NOT NULL,
		`redirect_on_buy` varchar(255) default NULL,
		`tags` varchar(255) default NULL,
		`field1` int(11) NOT NULL default '0',
		`engagements` int(11) NOT NULL default '0',
		`levels` text NOT NULL,
		`appear_in_transactions` int(11) NOT NULL default '1',
		PRIMARY KEY  (`id`),
		KEY `field1` (`field1`)
		) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	$sql = "SELECT type FROM userpointearner WHERE type = 100 LIMIT 1";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	
	if( !$resource->num_rows ){
		$sql=" INSERT INTO `userpointearner` (`id`, `type`, `name`, `title`, `body`, `date`, `photo`, `ext`, `cost`, `cant`, `views`, `comments`, `comments_allowed`, `enabled`, `transact_state`, `metadata`, `redirect_on_buy`, `tags`, `field1`, `engagements`, `levels`, `appear_in_transactions`) VALUES
		(1, 100, '', 'Visit MiRed10.com', 'This is the site of he autor, this point is free for you.', 1479363323, 0, '', 25, 1000, 0, 0, 1, 1, 0, 'a:2:{s:3:\"url\";s:19:\"http://mired10.com/\";s:1:\"t\";i:1;}', 'http://mired10.com/', 'affiliate', 0, 0, '', 1);	
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'userpointearner_users'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `userpointearner_users` (
		`id` int(11) NOT NULL auto_increment,
		`user_id` int(4) NOT NULL default '0',
		`offer` int(11) NOT NULL default '0',
		`stamp` int(11) NOT NULL default '0',
		`status` int(11) NOT NULL default '0',
		`transaction_id` int(11) NOT NULL default '0',
		PRIMARY KEY  (`id`)
		) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	// ADD COLUMNS/VALUES TO  users TABLE
	$resource = $sqlConnect->query("SHOW COLUMNS FROM `".$sql_db_name."`.Wo_Users LIKE 'user_allowed'");
	if($resource->num_rows == 0) {
		$sqlConnect->query("ALTER TABLE  Wo_Users ADD COLUMN `user_allowed` TINYINT( 1 ) NOT NULL DEFAULT '1'");
	}
	
	// USER LEVELS
	$resource = $sqlConnect->query("SHOW COLUMNS FROM `".$sql_db_name."`.`plugins_system` LIKE 'level_allow'");
	if($resource->num_rows == 0) {
		$sqlConnect->query("ALTER TABLE  plugins_system
		ADD`level_allow` tinyint(1) NOT NULL default '1',
		ADD`level_allow_transfer` tinyint(1) NOT NULL default '1',
		ADD`level_max_transfer` int(11) NOT NULL default '0',
		ADD `setting_userpoints_charge_postclassified` tinyint(1) NOT NULL default '0',
		ADD `setting_userpoints_charge_newevent` tinyint(1) NOT NULL default '0',
		ADD `setting_userpoints_charge_newgroup` tinyint(1) NOT NULL default '0',
		ADD `setting_userpoints_charge_newpoll` tinyint(1) NOT NULL default '0',
		ADD `setting_userpoints_enable_offers` tinyint(1) NOT NULL default '1',
		ADD `setting_userpoints_enable_shop` tinyint(1) NOT NULL default '1',
		ADD `setting_userpoints_enable_topusers` tinyint(1) NOT NULL default '1',
		ADD `setting_userpoints_enable_statistics` tinyint(1) NOT NULL default '1',
		ADD `setting_userpoints_enable_pointrank` tinyint(1) NOT NULL default '1',
		ADD `setting_userpoints_exchange_rate` int(11) NOT NULL default '1000'
		");
	}
	
	/* invite*/
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'invites'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `invites` (
		`invite_id` int(9) NOT NULL AUTO_INCREMENT,
		`invite_user_id` int(9) NOT NULL DEFAULT '0',
		`invite_date` int(14) NOT NULL DEFAULT '0',
		`invite_email` varchar(70) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		`invite_code` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		PRIMARY KEY (`invite_id`),
		UNIQUE KEY `invite_set` (`invite_user_id`,`invite_email`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'invites_stats_user'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");	
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE IF NOT EXISTS `invites_stats_user` (
		`user_id` int(9) NOT NULL default '0',
		`invites_sent` int(11) NOT NULL default '0',
		`invites_converted` int(11) NOT NULL default '0',
		`invites_sent_counter` int(11) NOT NULL default '0',
		`invites_sent_last` datetime NOT NULL default '0000-00-00',
		PRIMARY KEY  (`user_id`)
		);
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'unsubscribe'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");	
	if( !$resource->num_rows ){  
		$sql = "
		CREATE TABLE `unsubscribe` (
		`unsubscribe_id` int(11) NOT NULL auto_increment,
		`unsubscribe_user_id` int(11) NOT NULL default '0',
		`unsubscribe_user_email` varchar(255) NOT NULL,
		`unsubscribe_type` tinyint(4) NOT NULL default '0',
		PRIMARY KEY  (`unsubscribe_id`),
		KEY `unsubscribe_user_email` (`unsubscribe_user_email`)
		);
		";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	//######### ADD COLUMNS/VALUES TO users TABLE
	$resource = $sqlConnect->query("SHOW COLUMNS FROM `".$sql_db_name."`.Wo_Users LIKE 'user_invitesleft'");
	if($resource->num_rows == 0) {
		$sqlConnect->query("ALTER TABLE Wo_Users ADD COLUMN `user_invitesleft` INT( 11 ) NOT NULL DEFAULT '10' ");
	}	
	$resource = $sqlConnect->query("SHOW COLUMNS FROM `".$sql_db_name."`.Wo_Users LIKE 'user_referer'");
	if($resource->num_rows == 0) {
		$sqlConnect->query("ALTER TABLE Wo_Users ADD COLUMN `user_referer` INT( 11 ) NOT NULL DEFAULT '0' ");
	}	
	/*invite*/
	$resource = $sqlConnect->query("SHOW COLUMNS FROM `".$sql_db_name."`.`plugins_system` LIKE 'userpoints_enable'");
	if($resource->num_rows == 0) {
		$sqlConnect->query("ALTER TABLE  `plugins_system` ADD `userpoints_enable` int(11) NOT NULL default '1' ");
	}
	$resource = $sqlConnect->query("SHOW COLUMNS FROM `".$sql_db_name."`.`plugins_system` LIKE 'max_photo_size'");
	if($resource->num_rows == 0) {
		$sqlConnect->query("ALTER TABLE  `plugins_system` ADD `max_photo_size` int(11) NOT NULL default '5120' ");
	}	
	//CREATING BONUS FOR ALL PLUGINS  
	$sql = "SHOW COLUMNS FROM `".$sql_db_name."`.`plugins_system` LIKE 'bonus_enable_home_left_column'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `bonus_enable_home_left_column` INT NOT NULL DEFAULT 0 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 
	$sql = "SHOW COLUMNS FROM `".$sql_db_name."`.`plugins_system` LIKE 'bonus_enable_plublisher_new'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){	
		$sql = " ALTER TABLE plugins_system ADD COLUMN `bonus_enable_plublisher_new` INT NOT NULL DEFAULT 1 ";    
		$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	} 		

	/*gifts*/
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'gifts'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	if( !$resource->num_rows ){  
		$sql = "CREATE TABLE `gifts` (
		`gift_id` int(11) PRIMARY KEY auto_increment,
		`gift_filetype` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
		`gift_category` INT(11) NOT NULL DEFAULT '0',
		`gift_status` INT(11) NOT NULL DEFAULT '1',
		`gift_cost` INT(11) NOT NULL DEFAULT '0',
		`gift_lang` INT(11) NOT NULL DEFAULT '0',
		`gift_date` INT(11) NOT NULL DEFAULT '0',
		`gift_hits` INT(55) NOT NULL DEFAULT '0') ";  
		$sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");
	}
	
	$sql = "SHOW TABLES FROM `".$sql_db_name."` LIKE 'gifts_category'";
	$resource = $sqlConnect->query($sql) or die($sqlConnect->error." <b>SQL was: </b>$sql");	
	if( !$resource->num_rows ){  
		$sql = "CREATE TABLE `gifts_category` (
		`gifts_category_id` INT(11) PRIMARY KEY auto_increment,
		`gifts_category_lang` INT(11) NOT NULL,
		`gifts_category_icon` INT(11) NOT NULL
		)";  
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
						<div class="panel-heading"> Welcome to <strong>Plugin Points Advanced Wowonder</strong> installation!</div>
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
									<div class="panel-heading"><strong>File Permissions 777</strong></div>
									<div class="panel-body" style="text-align:justify;">upload/points/earn/<br>upload/points/spend/</div>
								</div>	
							</div>
							
							
							<div class="col-sm-12">				
								<div class="panel panel-default">
									<div class="panel-heading"><b>Installing Plugins:</b></div>
									<div class="panel-body" style="text-align:justify;">
										Before continuing, please be sure you have reviewed the installation instructions provided in <b>Documentation</b>.				
									</div>	
								</div>					
								<div class="panel panel-default">
									<div class="panel-heading"><b>After of Install:</b></div>
									<div class="panel-body" style="text-align:justify;">
										Delete this file "install_points.php", this version can delete information of you point shop, and offerts in your the database
									</div>	
								</div>				
							</div>
							
							<form autocomplete="off" action="install_points.php" method="post">                             
							<?php if($ready == 0){
							echo '<button name="submit" type="submit" class="btn btn-primary col-sm-12" style="width:100%;">Update / Install Now!!</button>';
							} else if($ready == 1){ header('Location: ../index.php?link1=admin-plugins');?>
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