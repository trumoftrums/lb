<?php    	
if($f == 'admin_poke'){

	if (Wo_IsAdmin($wo['user']['user_id']) === false) {
		  $data = array('status' => 200, 'message' => "You don't have the right permission to access this");
		  /* return ajax */		
		  return_json($data);
	}

	$user_id = ( isset($_POST['user_id']) ? $_POST['user_id'] : ( isset($_GET['user_id']) ? $_GET['user_id'] : 0 ) );
	$owner_id = ( isset($_POST['owner_id']) ? $_POST['owner_id'] : ( isset($_GET['owner_id']) ? $_GET['owner_id'] : 0 ) );

switch ($task) {
		case 'poke_delete':
		    
			$sqlConnect->query("DELETE FROM pokes WHERE user_id='{$user_id}' AND owner_id='{$owner_id}' LIMIT 1");
			
			/* return ajax */		
      		return_json($data);	
			break;	  

		default: 
      	    /* return ajax */		
      	    return_json(array('status' => 200, 'message' => "This option no exist"));
		 break;
	}

}
	if($f == 'pokes'){
			$owner_id = ( isset($_POST['owner_id']) ? $_POST['owner_id'] : ( isset($_GET['owner_id']) ? $_GET['owner_id'] : 0 ) );
			$owner_id =  Wo_Secure($owner_id);			   
		if($s == 'send'){		 
			$user_array = array();
			
			if($owner_id != $wo['user']['user_id']){
				if($owner_id == 0){ return false; }
				//get info
				$getPoke = $sqlConnect->query("SELECT * FROM `pokes`  WHERE `user_id`='{$wo['user']['user_id']}' AND `owner_id`='{$owner_id}'");
				$toque_exist = $getPoke->num_rows;
				
				if ( $toque_exist == 0 ){
					// add
					$sqlConnect->query("INSERT INTO pokes ( user_id, owner_id, poke_stamp ) VALUES ( '{$wo['user']['user_id']}', '{$owner_id}', '".time()."' )");
					$sqlConnect->insert_id;
					
					//variables
					$action = 'poke';
					$to_user_id = $owner_id;
					$node_url = '';		
					$sqlConnect->query("DELETE FROM Wo_Notifications WHERE type='poke' AND recipient_id = '{$to_user_id}' AND notifier_id= '{$wo['user']['user_id']}' LIMIT 1");
					
					$notification_data_array = array(
						'recipient_id' => $to_user_id,
						'type' => 'poke',
						'url' => 'index.php?link1=pokes'
					);
					$test = Register_Notification($notification_data_array);	
					
					//get info of user sending  
					$getUserPoke = $sqlConnect->query("SELECT user_id, username, first_name, last_name, avatar, email, gender FROM Wo_Users WHERE user_id='{$owner_id}' LIMIT 1");
					
					$user_array = $getUserPoke->fetch_assoc();	
					if($user_array['avatar']!= ''){ $user_array['photo'] =  $user_array['avatar']; } else { $user_array['user_picture'] = 'upload/photos/d-avatar.jpg'; }
					if($user_array['first_name']!= ''){ $user_array['user_fullname'] =  $user_array['first_name'].' '.$user_array['last_name']; } else { $user_array['user_fullname'] = $user_array['username']; }	
					
					$subject = $wo['lang']['plugin_poke_new_poke_in'].' '.$wo['config']['siteName'];
					$body  = ucwords($user_array['username']).", ".$wo['lang']['plugin_poke_a_person_poke'];
					$body .= "\r\n\r\n".$wo['lang']['plugin_poke_next_link'].":";
					$body .= "\r\n\r\n".$wo['config']['site_url']."/pokes";
					$email =  $user_array['email'];
					
					// send email
					send_email($email, $subject, $body);				
					
					//delete old poke if exist
					$sqlConnect->query("DELETE FROM pokes WHERE user_id='{$owner_id}' AND  owner_id='{$wo['user']['user_id']}' LIMIT 1");
					
					$result = array('result' => '1', 'message' => $wo['lang']['plugin_poke_poke_sending'], 'sendto' => $user_array);
				} else {
					$result = array('result' => '0', 'message' => $wo['lang']['plugin_poke_poke_prev']);
				}
				
			} else {
				$result = array('result' => '0', 'message' => $wo['lang']['plugin_poke_pokecant']);
			}
			/* return ajax */		
			return_json($result);
			
		}
		
		
		if($s == 'delete'){			
			//delete sql & return ajax
			if ($owner_id != 0 && $wo['user']['user_id'] != $owner_id){ 
				$sqlConnect->query("DELETE FROM pokes WHERE user_id='{$owner_id}' AND owner_id='{$wo['user']['user_id']}' LIMIT 1");
				return_json(array('result' => '1', 'message' => $wo['lang']['plugin_poke_pokedeleted']));	
			} 
			return_json(array('result' => '0', 'message' => $wo['lang']['plugin_poke_try_more_late']));	
		}
		
			
	}
?>