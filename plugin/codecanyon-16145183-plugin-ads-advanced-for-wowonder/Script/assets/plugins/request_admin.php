<?php
   if($f == 'autocomplete'){
      // valid inputs
		 $limit = ( isset($_POST['limit']) ? $_POST['limit'] : ( isset($_GET['limit']) ? $_GET['limit'] : 10 ) );	
		 $query = ( isset($_POST['query']) ? $_POST['query'] : ( isset($_GET['query']) ? $_GET['query'] : '' ) );
		 $type = ( isset($_POST['type']) ? $_POST['type'] : ( isset($_GET['type']) ? $_GET['type'] : 'share' ) );
		 $skipped_ids = ( isset($_POST['skipped_ids']) ? $_POST['skipped_ids'] : ( isset($_GET['skipped_ids']) ? $_GET['skipped_ids'] : '' ) );	
		 $skipped_array =  array();
		// valid inputs
		/* if both (query & skipped_ids) not set */
		if(!isset($query)) {
			return false;
		} else {
			if(!empty($skipped_ids)){
				/* if skipped_ids not array */
				$skipped_ids = json_decode($skipped_ids);
				if(!is_array($skipped_ids)) {
					return false;
				}
				/* skipped_ids must contain numeric values only */
				$skipped_array = array_filter($skipped_ids, 'is_numeric');
			}
		}
		 
      if($s == 'users'){	 
	     // autocomplete
		     // initialize the return array
		     $return = array();
		     // get users
			 $users = array();
			 
           	 /* get users */
			 /*get user input and limits*/
  		     $input = strtolower( $query );
  		     $len = strlen($input);

			if(count($skipped_array) > 0) {
				/* make a skipped list (including the viewer) */
				$skipped_list = implode(',', $skipped_array);
				/* get users */			
				$sql = "SELECT `user_id`, `username`, `first_name`, `last_name`, `gender`, `avatar` FROM " . T_USERS . " WHERE user_id NOT IN ('{$skipped_list}') AND (SUBSTRING(username, 1, $len)='$input' OR SUBSTRING(first_name, 1, $len)='$input' OR SUBSTRING(last_name, 1, $len)='$input' )  LIMIT $limit";
			} else {
				$sql = "SELECT `user_id`, `username`, `first_name`, `last_name`, `gender`, `avatar` FROM " . T_USERS . " WHERE (SUBSTRING(username, 1, $len)='$input' OR SUBSTRING(first_name, 1, $len)='$input' OR SUBSTRING(last_name, 1, $len)='$input' )  LIMIT $limit";
			}
			$resource = $sqlConnect->query($sql);
	    	 if($resource->num_rows > 0) {
            	 while($row = $resource->fetch_assoc()) {
				     $row['id'] = $row['user_id'];
                	 if($row['avatar']!= ''){ $row['photo'] =  $row['avatar']; } else { $row['photo'] = 'upload/photos/d-avatar.jpg'; }
			         if($row['first_name']!= ''){ $row['fullname'] =  $row['first_name'].' '.$row['last_name']; } else { $row['fullname'] = $row['username']; }			
                	 $users[] = $row;
           	     }
             }
			 
			 
		     if(count($users) > 0) {
		  	   /* return */
			  ob_start();
	  	      include './themes/' . $wo['config']['theme'] . '/layout/plugins/ajax_autocomplete.phtml';
	          $html = ob_get_contents();
              ob_end_clean(); 
			  $html = str_replace("\t",'',$html);
			  $html = str_replace("\r",'',$html);
			  $html = str_replace("\n",'',$html);
			  $return['suggest'] = $html;
		     }
		     // return & exit
		     return_json($return);
	  }
	  if($s == 'friend'){	     	      
	     // autocomplete
		     // initialize the return array
		     $return = array();
		     // get users
			 $users = array();
			 
           	 /* get users */
			 /*get user input and limits*/
  		     $input = strtolower( $query );
  		     $len = strlen($input);
	
  		     /*get info of database of friends*/
			if(count($skipped_array) > 0) {
				/* make a skipped list (including the viewer) */
				$skipped_list = implode(',', $skipped_array);				
				$sql = "SELECT Wo_Followers.*, user_id, username, first_name, last_name, avatar, gender FROM Wo_Followers LEFT JOIN Wo_Users ON (Wo_Followers.following_id = Wo_Users.user_id AND Wo_Followers.following_id != '{$wo['user']['user_id']}') OR (Wo_Followers.follower_id = Wo_Users.user_id AND Wo_Followers.follower_id != '{$wo['user']['user_id']}') WHERE user_id NOT IN ('{$skipped_list}') AND Wo_Followers.active = '1' AND 
  (following_id = '{$wo['user']['user_id']}' 
  /* OR follower_id = '{$wo['user']['user_id']}'*/
  )
  AND (SUBSTRING(username, 1, $len)='$input' OR SUBSTRING(first_name, 1, $len)='$input' OR SUBSTRING(last_name, 1, $len)='$input') LIMIT $limit";
			} else {
				$sql = "SELECT Wo_Followers.*, user_id, username, first_name, last_name, avatar, gender FROM Wo_Followers LEFT JOIN Wo_Users ON (Wo_Followers.following_id = Wo_Users.user_id AND Wo_Followers.following_id != '{$wo['user']['user_id']}') OR (Wo_Followers.follower_id = Wo_Users.user_id AND Wo_Followers.follower_id != '{$wo['user']['user_id']}') WHERE  Wo_Followers.active = '1' AND 
  (following_id = '{$wo['user']['user_id']}' 
  /* OR follower_id = '{$wo['user']['user_id']}'*/
  )
  AND (SUBSTRING(username, 1, $len)='$input' OR SUBSTRING(first_name, 1, $len)='$input' OR SUBSTRING(last_name, 1, $len)='$input') LIMIT $limit";
			}
  		     
  		     $resource = $sqlConnect->query($sql);

	    	 if($resource->num_rows > 0) {
            	 while($row = $resource->fetch_assoc()) {
				     $row['id'] = $row['user_id'];
                	 if($row['avatar']!= ''){ $row['photo'] =  $row['avatar']; } else { $row['photo'] = 'upload/photos/d-avatar.jpg'; }
			         if($row['first_name']!= ''){ $row['fullname'] =  $row['first_name'].' '.$row['last_name']; } else { $row['fullname'] = $row['username']; }			
                	 $users[] = $row;
           	     }
             }
			 
			 
		     if(count($users) > 0) {
		  	   /* return */
			  ob_start();
	  	      include './themes/' . $wo['config']['theme'] . '/layout/plugins/ajax_autocomplete.phtml';
	          $html = ob_get_contents();
              ob_end_clean(); 
			  $html = str_replace("\t",'',$html);
			  $html = str_replace("\r",'',$html);
			  $html = str_replace("\n",'',$html);
			  $return['suggest'] = $html;
		     }
		     // return & exit
		     return_json($return);
	  }
	  if($s == 'page'){ 
	     // autocomplete
		     // initialize the return array
		     $return = array();
		     // get users
			 $users = array();
			 
           	 /* get users */
			 /*get user input and limits*/
  		     $input = strtolower( $query );
  		     $len = strlen($input);
	
  		     /*get info of database of page*/
  		     $sql = "SELECT `page_id`, `user_id`, `page_name`, `page_title`, `avatar` FROM Wo_Pages WHERE user_id = '{$wo['user']['user_id']}' AND (SUBSTRING(page_name, 1, $len)='$input' OR SUBSTRING(page_title, 1, $len)='$input') LIMIT $limit";
  		     $resource = $sqlConnect->query($sql);

	    	 if($resource->num_rows > 0) {
            	 while($row = $resource->fetch_assoc()) {
				     $row['id'] = $row['page_id'];
                	 if($row['avatar']!= ''){ $row['photo'] =  $row['avatar']; } else { $row['photo'] = 'upload/photos/d-page.jpg'; }
			         if($row['page_title']!= ''){ $row['fullname'] =  $row['page_title']; } else { $row['fullname'] = $row['page_name']; }			
                	 $users[] = $row;
           	     }
             }
			 
			 
		     if(count($users) > 0) {
		  	   /* return */
			  ob_start();
	  	      include './themes/' . $wo['config']['theme'] . '/layout/plugins/ajax_autocomplete.phtml';
	          $html = ob_get_contents();
              ob_end_clean(); 
			  $html = str_replace("\t",'',$html);
			  $html = str_replace("\r",'',$html);
			  $html = str_replace("\n",'',$html);
			  $return['suggest'] = $html;
		     }
		     // return & exit
		     return_json($return);
	  }
	  if($s == 'group'){	 
	     // autocomplete
		     // initialize the return array
		     $return = array();
		     // get users
			 $users = array();
			 
           	 /* get users */
			 /*get user input and limits*/
  		     $input = strtolower( $query );
  		     $len = strlen($input);
	
  		     /*get info of database of group*/
  		     $sql = "SELECT `id`, `user_id`, `group_name`, `group_title`, `avatar` FROM Wo_Groups WHERE user_id = '{$wo['user']['user_id']}' AND (SUBSTRING(group_name, 1, $len)='$input' OR SUBSTRING(group_title, 1, $len)='$input') LIMIT $limit";
  		     $resource = $sqlConnect->query($sql);

	    	 if($resource->num_rows > 0) {
            	 while($row = $resource->fetch_assoc()) {
				     $row['id'] = $row['id'];
                	 if($row['avatar']!= ''){ $row['photo'] =  $row['avatar']; } else { $row['photo'] = 'upload/photos/d-group.jpg'; }
			         if($row['group_title']!= ''){ $row['fullname'] =  $row['group_title']; } else { $row['fullname'] = $row['group_name']; }			
                	 $users[] = $row;
           	     }
             }
			 
			 
		     if(count($users) > 0) {
		  	   /* return */
			  ob_start();
	  	      include './themes/' . $wo['config']['theme'] . '/layout/plugins/ajax_autocomplete.phtml';
	          $html = ob_get_contents();
              ob_end_clean(); 
			  $html = str_replace("\t",'',$html);
			  $html = str_replace("\r",'',$html);
			  $html = str_replace("\n",'',$html);
			  $return['suggest'] = $html;
		     }
		     // return & exit
		     return_json($return);
	  }
	  if($s == 'event'){
	  	         // valid inputs
		 $limit = ( isset($_POST['limit']) ? $_POST['limit'] : ( isset($_GET['limit']) ? $_GET['limit'] : 5 ) );	
		 $query = ( isset($_POST['query']) ? $_POST['query'] : ( isset($_GET['query']) ? $_GET['query'] : '' ) );	 
	     // autocomplete
		     // initialize the return array
		     $return = array();
		     // get users
			 $users = array();
			 
           	 /* get users */
			 /*get user input and limits*/
  		     $input = strtolower( $query );
  		     $len = strlen($input);
	
  		     /*get info of database of events*/
  		     $sql = "SELECT `id`, `user_id`, `event_name`, `event_title`, `avatar` FROM Wo_Events WHERE user_id = '{$wo['user']['user_id']}' AND (SUBSTRING(event_name, 1, $len)='$input' OR SUBSTRING(event_title, 1, $len)='$input') LIMIT $limit";
  		     $resource = $sqlConnect->query($sql);

	    	 if($resource->num_rows > 0) {
            	 while($row = $resource->fetch_assoc()) {
				     $row['id'] = $row['id'];
                	 if($row['avatar']!= ''){ $row['photo'] =  $row['avatar']; } else { $row['photo'] = 'upload/photos/d-event.jpg'; }
			         if($row['event_title']!= ''){ $row['fullname'] =  $row['event_title']; } else { $row['fullname'] = $row['event_name']; }			
                	 $users[] = $row;
           	     }
             }
			 
			 
		     if(count($users) > 0) {
		  	   /* return */
			  ob_start();
	  	      include './themes/' . $wo['config']['theme'] . '/layout/plugins/ajax_autocomplete.phtml';
	          $html = ob_get_contents();
              ob_end_clean(); 
			  $html = str_replace("\t",'',$html);
			  $html = str_replace("\r",'',$html);
			  $html = str_replace("\n",'',$html);
			  $return['suggest'] = $html;
		     }
		     // return & exit
		     return_json($return);
	  }
   }
   
   
/* PLUGINS GENERAL */
if($f == 'admin_plugins'){

	if(Wo_IsAdmin($wo['user']['user_id']) === false) {
		return_json( array('error' => true, 'message' => "You don't have the right permission to access this"));
	}

	switch ($task) {
		case 'plugin':
		
			/* prepare */
			$p_active = (isset($_POST['p_active']))? '1' : '0';
			$p_key = ( isset($_POST['p_key']) ? $_POST['p_key'] : ( isset($_GET['p_key']) ? $_GET['p_key'] : '' ) );
			$p_id = ( isset($_POST['p_id']) ? $_POST['p_id'] : ( isset($_GET['p_id']) ? $_GET['p_id'] : 0 ) );			
		
			// valid inputs
			if(!isset($p_id) || !is_numeric($p_id)) { return_json(array('error' => true, 'message' => "This no is a number")); }
						
			$p_active = Wo_Secure($p_active);	
			$p_id = Wo_Secure($p_id);
			$p_key = Wo_Secure($p_key);			
			
			$sqlConnect->query("UPDATE plugins_ldrsoft SET p_active='{$p_active}', p_key='{$p_key}' WHERE p_id='{$p_id}' LIMIT 1");
			$db_update =  $sqlConnect->affected_rows;					
			/* return */		
			if($db_update == 1){
			   return_json(array('result' => 1, 'message' => 'Done, Plugin info have been updated', 'results'=> $db_update, 'success' => true));
			} else {
			   return_json(array('result' => 0, 'message' => 'Cant update try more late!', 'results'=> $db_update, 'error' => true));
			}
		break;
		
		default: 
			return_json(array('result' => 0, 'error' => true, 'message' => "This option no exist"));
		break;
	}
}


/*ADMIN BONUS SETTING*/
if($f == 'admin_bonus'){

	if (Wo_IsAdmin($wo['user']['user_id']) === false) {
		  return_json(array('error' => true, 'message' => "You don't have the right permission to access this"));
	}
	
	$task = ( isset($_POST['task']) ? $_POST['task'] : ( isset($_GET['task']) ? $_GET['task'] : '' ) );
	$val = ( isset($_POST['val']) ? $_POST['val'] : ( isset($_GET['val']) ? $_GET['val'] : array() ) );

// setting
	switch ($task) {
	   					
		 case 'setting': 
			$home_left_column = ( isset($_POST['home_left_column']) ? $_POST['home_left_column'] : ( isset($_GET['home_left_column']) ? $_GET['home_left_column'] : '1') );
			$plublisher_new = ( isset($_POST['plublisher_new']) ? $_POST['plublisher_new'] : ( isset($_GET['plublisher_new']) ? $_GET['plublisher_new'] : '1' ) );  
			
			$bonus_enable_home_left_column = Wo_Secure($home_left_column);
			$bonus_enable_plublisher_new = Wo_Secure($plublisher_new);			
			
			$sqlConnect->query("UPDATE plugins_system SET 
			bonus_enable_home_left_column = '{$bonus_enable_home_left_column}',
			bonus_enable_plublisher_new = '{$bonus_enable_plublisher_new}'
			WHERE system_id = '1' LIMIT 1");
			$db_update =  $sqlConnect->affected_rows;
			
			/* return */
			if($db_update == 1){
					   return_json(array('result' => 1, 'message' => "Done, Plugin info have been updated", 'results'=> $db_update, 'success' => true));
					} else {
					   return_json(array('result' => 0, 'message' => 'Cant update try more late!', 'results'=> $db_update, 'error' => true));
					}
			break;		
			
			default: 
			return_json(array('result' => 0, 'message' => 'This task no exist', 'error' => true));
			break;
	}

}
/////
if($f == 'delete_all'){
// valid inputs
if(!isset($_POST['id']) || !is_numeric($_POST['id'])) { $data = array('status' => 'error', 'message' => "This id no exist"); // return
	return_json($data); }
$_id = Wo_Secure($_POST['id']);

	switch ($_POST['handle']) {
		case 'payment':		    
			$sqlConnect->query("DELETE FROM ads_purchased WHERE id = '{$_id}' AND user_id = '{$wo['user']['user_id']}' LIMIT 1");
			break;

		case 'report':
			$sqlConnect->query("DELETE FROM reports WHERE report_id = '{$_id}' AND user_id = '{$wo['user']['user_id']}' LIMIT 1");
			break;
	}

	// return
	return_json();
}

/* general codes */
/*post of ldrsoft*/
	if($f == 'post_delete') {
	   $post_id = ( isset($_POST['post_id']) ? $_POST['post_id'] : ( isset($_GET['post_id']) ? $_GET['post_id'] : '0' ) );
		if (!empty($post_id)) {
			if (Delete_Post($post_id) === true) {
				$data = array(
					'status' => 200
				);
			}
		}
		header("Content-type: application/json");
		echo json_encode($data);
		exit();
	}
	
	if ($f == 'insert_new_post') {
		$media         = '';
		$mediaFilename = '';
		$mediaName     = '';
		$html          = '';
		$recipient_id  = 0;
		$page_id       = 0;
		$group_id      = 0;
		$event_id      = 0;
		$image_array   = array();

		if (Wo_CheckSession($hash_id) === false) {
			return false;
			die();
		}
		
		if (isset($_POST['recipient_id']) && !empty($_POST['recipient_id'])) {
			$recipient_id = Wo_Secure($_POST['recipient_id']);
		} else if (isset($_POST['page_id']) && !empty($_POST['page_id'])) {
			$page_id = Wo_Secure($_POST['page_id']);
		} else if (isset($_POST['group_id']) && !empty($_POST['group_id'])) {
			$group_id = Wo_Secure($_POST['group_id']);
			$group    = Wo_GroupData($group_id);
			if (!empty($group['id'])) {
				if ($group['privacy'] == 1) {
					$_POST['postPrivacy'] = 0;
				} else if ($group['privacy'] == 2) {
					$_POST['postPrivacy'] = 2;
				}
			}
		} else if (isset($_POST['event_id']) && !empty($_POST['event_id'])) {
			$event_id = Wo_Secure($_POST['event_id']);
			$event    = EventData($event_id);
			if (!empty($event['id'])) {
				if ($event['privacy'] == 1) {
					$_POST['postPrivacy'] = 0;
				} else if ($event['privacy'] == 2) {
					$_POST['postPrivacy'] = 2;
				}
			}
		}
		
		if (isset($_FILES['postFile']['name'])) {
			$fileInfo = array(
				'file' => $_FILES["postFile"]["tmp_name"],
				'name' => $_FILES['postFile']['name'],
				'size' => $_FILES["postFile"]["size"],
				'type' => $_FILES["postFile"]["type"]
			);
			$media    = Wo_ShareFile($fileInfo);
			if (!empty($media)) {
				$mediaFilename = $media['filename'];
				$mediaName     = $media['name'];
			}
		}
		if (isset($_FILES['postVideo']['name']) && empty($mediaFilename)) {
			$fileInfo = array(
				'file' => $_FILES["postVideo"]["tmp_name"],
				'name' => $_FILES['postVideo']['name'],
				'size' => $_FILES["postVideo"]["size"],
				'type' => $_FILES["postVideo"]["type"],
				'types' => 'mp4,m4v,webm,flv,mov,mpeg'
			);
			$media    = Wo_ShareFile($fileInfo);
			if (!empty($media)) {
				$mediaFilename = $media['filename'];
				$mediaName     = $media['name'];
			}
		}
		if (isset($_FILES['postMusic']['name']) && empty($mediaFilename)) {
			$fileInfo = array(
				'file' => $_FILES["postMusic"]["tmp_name"],
				'name' => $_FILES['postMusic']['name'],
				'size' => $_FILES["postMusic"]["size"],
				'type' => $_FILES["postMusic"]["type"],
				'types' => 'mp3,wav'
			);
			$media    = Wo_ShareFile($fileInfo);
			if (!empty($media)) {
				$mediaFilename = $media['filename'];
				$mediaName     = $media['name'];
			}
		}
		$multi = 0;
		if (isset($_FILES['postPhotos']['name']) && empty($mediaFilename) && empty($_POST['album_name'])) {
			if (count($_FILES['postPhotos']['name']) == 1) {
				$fileInfo = array(
					'file' => $_FILES["postPhotos"]["tmp_name"][0],
					'name' => $_FILES['postPhotos']['name'][0],
					'size' => $_FILES["postPhotos"]["size"][0],
					'type' => $_FILES["postPhotos"]["type"][0]
				);
				$media    = Wo_ShareFile($fileInfo);
				if (!empty($media)) {
					$mediaFilename = $media['filename'];
					$mediaName     = $media['name'];
				}
			} else {
				$multi = 1;
			}
		}
		if (empty($_POST['postPrivacy'])) {
			$_POST['postPrivacy'] = 0;
		}
		$post_privacy  = 0;
		$privacy_array = array(
			'0',
			'1',
			'2',
			'3'
		);
		if (isset($_POST['postPrivacy'])) {
			if (in_array($_POST['postPrivacy'], $privacy_array)) {
				$post_privacy = $_POST['postPrivacy'];
			}
		}
		$import_url_image = '';
		$url_link         = '';
		$url_content      = '';
		$url_title        = '';
		if (!empty($_POST['url_link']) && !empty($_POST['url_title'])) {
			$url_link  = $_POST['url_link'];
			$url_title = $_POST['url_title'];
			if (!empty($_POST['url_content'])) {
				$url_content = $_POST['url_content'];
			}
			if (!empty($_POST['url_image'])) {
				$import_url_image = @Wo_ImportImageFromUrl($_POST['url_image']);
			}
		}
		$post_text = '';
		$post_map  = '';
		if (!empty($_POST['postText']) && !ctype_space($_POST['postText'])) {
			$post_text = $_POST['postText'];
		}
		if (!empty($_POST['postMap'])) {
			$post_map = $_POST['postMap'];
		}
		$album_name = '';
		if (!empty($_POST['album_name'])) {
			$album_name = $_POST['album_name'];
		}
		if (!isset($_FILES['postPhotos']['name'])) {
			$album_name = '';
		}
		
		/*plugin question value*/
		$_tmp_options = ( isset($_POST['options']) ? $_POST['options'] : ( isset($_GET['options']) ? $_GET['options'] : array() ) );
		if(count($_tmp_options) > 0){
		   $_tmp_opts = array();
		   foreach ($_tmp_options as $_opt){ if ($_opt){ $_tmp_opts[] = $_opt; }}
		   if(count($_tmp_opts) > 0){
			  if(count($_tmp_opts) > 0 && empty($post_text)){
				  $data = array('status' => '400', 'errors' => 'Please add a question');
				  header("Content-type: application/json");
				  echo json_encode($data);
				  exit();
			  }
			  if(count($_tmp_opts) == 1 && !empty($post_text)){
				  $data = array('status' => '400', 'errors' => 'Please add more of a answer');
				  header("Content-type: application/json");
				  echo json_encode($data);
				  exit();
			  }
			  if(count($_tmp_opts) >= $wo['system']['question_limit_answers'] && !empty($post_text)){
				  $data = array('status' => '400', 'errors' => $wo['system']['question_limit_answers'].' is number limit of answers');
				  header("Content-type: application/json");
				  echo json_encode($data);
				  exit();
			  }
		   }
		}

		/*plugin question value*/		
		$traveling = '';
		$watching  = '';
		$playing   = '';
		$listening = '';
		$feeling   = '';
		if (!empty($_POST['feeling_type'])) {
			$array_types = array(
				'feelings',
				'traveling',
				'watching',
				'playing',
				'listening'
			);
			if (in_array($_POST['feeling_type'], $array_types)) {
				if ($_POST['feeling_type'] == 'feelings') {
					if (!empty($_POST['feeling'])) {
						if (array_key_exists($_POST['feeling'], $wo['feelingIcons'])) {
							$feeling = $_POST['feeling'];
						}
					}
				} else if ($_POST['feeling_type'] == 'traveling') {
					if (!empty($_POST['feeling'])) {
						$traveling = $_POST['feeling'];
					}
				} else if ($_POST['feeling_type'] == 'watching') {
					if (!empty($_POST['feeling'])) {
						$watching = $_POST['feeling'];
					}
				} else if ($_POST['feeling_type'] == 'playing') {
					if (!empty($_POST['feeling'])) {
						$playing = $_POST['feeling'];
					}
				} else if ($_POST['feeling_type'] == 'listening') {
					if (!empty($_POST['feeling'])) {
						$listening = $_POST['feeling'];
					}
				}
			}
		}
		if (isset($_FILES['postPhotos']['name'])) {
			$allowed = array(
				'gif',
				'png',
				'jpg',
				'jpeg'
			);
			for ($i = 0; $i < count($_FILES['postPhotos']['name']); $i++) {
				$new_string = pathinfo($_FILES['postPhotos']['name'][$i]);
				if (!in_array(strtolower($new_string['extension']), $allowed)) {
					$errors[] = $error_icon . $wo['lang']['please_check_details'];
				}
			}
		}
		if (!empty($_POST['answer']) && array_filter($_POST['answer'])) {
			if (!empty($_POST['postText'])) {
				foreach ($_POST['answer'] as $key => $value) {
					if (empty($value) || ctype_space($value)) {
						$errors = 'Answer #' . ($key + 1) . ' is empty.';
					}
				}
			} else {
				$errors = 'Please write the question.';
			}
		}
		if (empty($errors)) {
			$is_option = false;
			if (!empty($_POST['answer']) && array_filter($_POST['answer'])) {
				$is_option = true;
			}
			$post_data = array(
				'user_id' => Wo_Secure($wo['user']['user_id']),
				'page_id' => Wo_Secure($page_id),
				'group_id' => Wo_Secure($group_id),
				//'event_id' => Wo_Secure($event_id),
				'postText' => Wo_Secure($post_text),
				'recipient_id' => Wo_Secure($recipient_id),
				'postFile' => Wo_Secure($mediaFilename, 0),
				'postFileName' => Wo_Secure($mediaName),
				'postMap' => Wo_Secure($post_map),
				'postPrivacy' => Wo_Secure($post_privacy),
				'postLinkTitle' => Wo_Secure($url_title),
				'postLinkContent' => Wo_Secure($url_content),
				'postLink' => Wo_Secure($url_link),
				'postLinkImage' => Wo_Secure($import_url_image, 0),
				'album_name' => Wo_Secure($album_name),
				'multi_image' => Wo_Secure($multi),
				'postFeeling' => Wo_Secure($feeling),
				'postListening' => Wo_Secure($listening),
				'postPlaying' => Wo_Secure($playing),
				'postWatching' => Wo_Secure($watching),
				'postTraveling' => Wo_Secure($traveling),
				'time' => time()
			);
			if (!empty($is_option)) {
				$post_data['poll_id'] = 1;
			}
			$id = Register_Post($post_data);
			$question_id = 0;
			if ($id) {
				//question
					
				$question = $post_text;
				$tmp_options = ( isset($_POST['options']) ? $_POST['options'] : ( isset($_GET['options']) ? $_GET['options'] : array() ) );
	
				$options = array();
			   if(count($tmp_options) > 0 && $question != ''){			  
				  $question = Wo_Secure($question);
			  
				  foreach ($tmp_options as $option){ 
					 if($option){ $options[] = $option; }
				  }
				  if(count($options) > 0){ 
				    $post_data['question_id'] = 1;					
					
					$sqlConnect->query("INSERT INTO posts_questions (question_user_id, question_question, post_id) VALUES ('{$wo['user']['user_id']}', '{$question}', '{$id}')");
					$question_id = $sqlConnect->insert_id;					  
					  foreach ($options as $answer){
							$answer = Wo_Secure($answer);
							$sqlConnect->query("INSERT INTO posts_questions_options (title, question_id) VALUES ('{$answer}', '{$question_id}')");
					  }
					$sqlConnect->query("UPDATE Wo_Posts SET postType='question', question_id='{$question_id}' WHERE post_id = '{$id}' LIMIT 1");
				  }
				}		   			 
		//question
				//tag_friend
				$tag_friends = ( isset($_POST['tag_friends']) ? $_POST['tag_friends'] : ( isset($_GET['tag_friends']) ? $_GET['tag_friends'] : array() ) );
				$tag_friend = array();
			   if(count($tag_friends) > 0){			  
				  foreach ($tag_friends as $tf){ 
					 if($tf && is_numeric($tf)){ $tag_friend[] = $tf; }
				  }
				  if(count($tag_friend) > 0){ 				  
					  foreach ($tag_friend as $tagfriend){
							$tagfriend = Wo_Secure($tagfriend);
							if($tagfriend != $wo['user']['user_id']){
								$sqlConnect->query("INSERT INTO posts_tags (user_id, post_id) VALUES ('{$tagfriend}', '{$id}')");
								/*$notification here*/
							}
					  }
				  }
				}		   			 
		//tag_friend
		
				if ($is_option == true) {
					foreach ($_POST['answer'] as $key => $value) {
						$add_opition = Wo_AddOption($id, $value);
					}
				}
				if (isset($_FILES['postPhotos']['name'])) {
					if (count($_FILES['postPhotos']['name']) > 0) {
						for ($i = 0; $i < count($_FILES['postPhotos']['name']); $i++) {
							$fileInfo = array(
								'file' => $_FILES["postPhotos"]["tmp_name"][$i],
								'name' => $_FILES['postPhotos']['name'][$i],
								'size' => $_FILES["postPhotos"]["size"][$i],
								'type' => $_FILES["postPhotos"]["type"][$i],
								'types' => 'jpg,png,jpeg,gif'
							);
							$file     = Wo_ShareFile($fileInfo, 1);
							if (!empty($file)) {
								$media_album = Wo_RegisterAlbumMedia($id, $file['filename']);
							}
						}
					}
				}
				
				/* plugin points */
				if(!empty($wo['plugin_list']['plugin_actived']) && in_array('Points', $wo['plugin_list']['plugin_actived']) && !empty($wo['system']['userpoints_enable']) && $wo['system']['userpoints_enable']){ update_points( $wo['user']['user_id'], 'post' ); }	
				
				$wo['story'] = Wo_PostData($id);
				$html .= Wo_LoadPage('story/content');
				$data = array(
					'status' => 200,
					'html' => $html, 
					'is_question' => $question_id
				);
			}
		} else {
			header("Content-type: application/json");
			echo json_encode(array(
				'status' => 400,
				'errors' => $errors
			));
			exit();
		}
		header("Content-type: application/json");
		echo json_encode($data);
		exit();
	}
?>