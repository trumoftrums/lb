<?php
/**
* functions reaction
* @Autor : LdrMx
*/
	
	/*reaction funtion*/
	function isReacted($r = '', $user_id, $post_id) {
		global $sqlConnect;
		
		if(!$user_id ){ return false; }		
		$query = $sqlConnect->query("SELECT * FROM Wo_Likes WHERE post_id='{$post_id}' AND user_id = '{$user_id}' LIMIT 1");
		if (empty($r)){ $query = $sqlConnect->query("SELECT * FROM Wo_Likes WHERE post_id='{$post_id}' AND user_id = '{$user_id}' LIMIT 1"); }
		else { $query = $sqlConnect->query("SELECT * FROM Wo_Likes WHERE post_id='{$post_id}' AND user_id = '{$user_id}' AND reaction='{$r}' LIMIT 1"); }
		if ($query->num_rows == 1){	$fetch = $query->fetch_array(MYSQLI_ASSOC); return $fetch['reaction']; }
	}
	
	
	function putReaction($r, $user_id, $post_id){
		global $sqlConnect, $wo;
		if (!$wo['loggedin'] == true && (empty($post_id) || !is_numeric($post_id) || $post_id < 1)) {
			return false;
		}
		$logged_user_id = Wo_Secure($wo['user']['user_id']);
		$post = Wo_PostData($post_id);
		$type2 = '';
		if (isset($post['postText']) && !empty($post['postText'])) {
			$text = substr($post['postText'], 0, 10) . '..';
		}
		if (isset($post['postYoutube']) && !empty($post['postYoutube'])) {
			$type2 = 'post_youtube';
		} elseif (isset($post['postSoundCloud']) && !empty($post['postSoundCloud'])) {
			$type2 = 'post_soundcloud';
		} elseif (isset($post['postVine']) && !empty($post['postVine'])) {
			$type2 = 'post_vine';
		} elseif (isset($post['postFile']) && !empty($post['postFile'])) {
			if (strpos($post['postFile'], '_image') !== false) {
				$type2 = 'post_image';
			} else if (strpos($post['postFile'], '_video') !== false) {
				$type2 = 'post_video';
			} else if (strpos($post['postFile'], '_avatar') !== false) {
				$type2 = 'post_avatar';
			} else if (strpos($post['postFile'], '_sound') !== false) {
				$type2 = 'post_soundFile';
			} else if (strpos($post['postFile'], '_cover') !== false) {
				$type2 = 'post_cover';
			} else if (strpos($post['postFile'], '_cover') !== false) {
				$type2 = 'post_cover';
			} else {
				$type2 = 'post_file';
			}
		}
		/*$get_post = $sqlConnect->query("SELECT Wo_Posts.* FROM Wo_Posts WHERE post_id = '{$post_id}' LIMIT 1");
		if($get_post->num_rows == 0) { return false; }  else { $post = $get_post->fetch_assoc(); }   */  
	
		if (empty($r)){ $r = "like"; }	  	
		if (isReacted($r, $user_id, $post_id)){
			/* delete of post like */
			$sqlConnect->query("DELETE FROM Wo_Likes WHERE post_id='{$post_id}' AND user_id = '{$user_id}' LIMIT 1");		
			/* update post likes counter */
			$sqlConnect->query("UPDATE Wo_Posts SET likes = IF(likes=0,0,likes-1) WHERE post_id = '{$post_id}' LIMIT 1");
			/* delete notification */
			$sqlConnect->query("DELETE FROM Wo_Notifications WHERE notifier_id = '{$user_id}' AND post_id = '{$post_id}'");		
		} else {		
			/* delete of post like */
			$sqlConnect->query("DELETE FROM Wo_Likes WHERE post_id='{$post_id}' AND user_id = '{$user_id}' AND reaction<> '{$r}'");			
			/* update post likes counter */
			$sqlConnect->query("UPDATE Wo_Posts SET likes = IF(likes=0,0,likes-1) WHERE post_id = '{$post_id}' LIMIT 1");
			/* delete notification */
			$sqlConnect->query("DELETE FROM Wo_Notifications WHERE notifier_id = '{$user_id}' AND post_id = '{$post_id}'");			
			/* insert like */
			$sql_query_two = $sqlConnect->query("INSERT INTO Wo_Likes (user_id,post_id,reaction) VALUES ('{$user_id}','{$post_id}','{$r}')");			
			/* update post likes counter */
			$sqlConnect->query("UPDATE Wo_Posts SET likes = likes+1 WHERE post_id = '{$post_id}' LIMIT 1");

			$resource = $sqlConnect->query("SELECT reaction_filetype FROM reactions WHERE reaction_key = '{$r}' LIMIT 1");
			$file = $resource->fetch_assoc();	

				if ($type2 != 'post_avatar') {
					$activity_data = array(
						'post_id' => $post_id,
						'user_id' => $logged_user_id,
						'post_user_id' => $post['user_id'],
						'activity_type' => 'reaction_post',
						'app_src' => 'reaction_'.$r.'.'.$file['reaction_filetype']
					);
					$add_activity  = Register_Activity($activity_data);
				}

				$notification_data_array = array(
					'recipient_id' => $post['user_id'],
					'post_id' => $post_id,
					'type' => 'reaction_post',
					'text' => '',
					'type2' => $type2,
					'url' => 'index.php?link1=post&id=' . $post_id,
					'app_src' => 'reaction_'.$r.'.'.$file['reaction_filetype']
				);
				Register_Notification($notification_data_array);
			
		}
		return true;
	}
	
	function numReactions($r = "", $post_id){ 
		global $sqlConnect;
		$allowed_reactions = array('like', 'love', 'haha', 'wow', 'sad', 'angry');
		if (in_array($r, $allowed_reactions))
		   { $query = $sqlConnect->query("SELECT COUNT(post_id) AS count FROM Wo_Likes WHERE post_id='{$post_id}' AND reaction='$r'"); }
			 else
		   { $query = $sqlConnect->query("SELECT COUNT(post_id) AS count FROM Wo_Likes WHERE post_id='{$post_id}'"); }		   
			$fetch = $query->fetch_array(MYSQLI_ASSOC);	  
	  return $fetch['count'];
	}
	
	function getReactions($r="", $post_id){ 
		global $sqlConnect;
				
		$allowed_reactions = array('like', 'love', 'haha', 'wow', 'sad', 'angry');
		$get = array();
		$query = $sqlConnect->query("SELECT * FROM Wo_Likes WHERE post_id='{$post_id}'");
		
		if (in_array($r, $allowed_reactions)){ 
		   $query = $sqlConnect->query("SELECT * FROM Wo_Likes WHERE post_id='{$post_id}' AND reaction='{$r}'");
		} else { 
		   $query = $sqlConnect->query("SELECT * FROM Wo_Likes WHERE post_id='{$post_id}'");
		}
		
		if($query->num_rows > 0){ 
			while($fetch = $query->fetch_array(MYSQLI_ASSOC)){ 
				$get[$fetch['user_id']] = $fetch['reaction'];
			}
		}
	   
		return $get;
	}
	
	function getTopReactions($post_id, $l=5)
	{	global $sqlConnect;
		$l = (int) $l;
		$g = array();
		$query = $sqlConnect->query("SELECT DISTINCT reaction FROM Wo_Likes WHERE post_id='{$post_id}' LIMIT $l");
		while($fetch = $query->fetch_assoc()){ $g[] = $fetch['reaction']; }
		return $g;
	}
	
	
	function getReactButtonTemplate($r = '', $user_id, $post_id){ 
		global $wo;
		if ($reaction = isReacted($r, $user_id, $post_id)){ 
			 ob_start();
			 include "upload/reaction/reaction-".$reaction."-button.php";
			 $html = ob_get_contents();
			 ob_end_clean(); ob_get_level();
			 $html = str_replace("\t",'',$html);
			 $html = str_replace("\r",'',$html);
			 $html = str_replace("\n",'',$html);
			 return $html;
		} 
		else { return '<span class="text-link story-like-btn opt" onclick="set_reaction($(this), \'like\', '.$post_id.');" title="'.$wo['lang']['like'].'"><i class="fa fa-thumbs-o-up progress-icon" data-icon="thumbs-up"></i> '.$wo['lang']['like'].'</span>'; }
	}
	
	
	function getReactionActivityTemplate($r = '', $post_id){	
	  global $wo, $sqlConnect;
	  
		  //$story_likes_num = numReactions($r, $post_id);
		  $story_likes_num = numReactions('', $post_id);
		  $list_reactions = ''; 				
		
		  foreach(getTopReactions($post_id) as $k => $v){
		     $resource = $sqlConnect->query("SELECT reaction_filetype FROM reactions WHERE reaction_key = '{$v}' LIMIT 1");
			 $file = $resource->fetch_assoc();	 
			 $list_reactions .= '<img src="'.$wo['config']['site_url'].'/upload/reaction/reaction_'.$v.'.'.$file['reaction_filetype'].'" width="14px">';
		  }
				
		  $Html_react = '<span class="like-activity activity-btn" title="Reactions">'.$list_reactions.'<span class="stat-item post-like-status" id="likes">'.$story_likes_num.'</span></span>';		
		
		return $Html_react;		
	}
	
	/*list reaction - no use*/ 
	function getReactionsTemplate($r="", $post_id)
	{  global $wo;
			
		 ob_start();
		 include "upload/reaction/pr_view-reactions.php";
		 $html = ob_get_contents();
		 ob_end_clean(); ob_get_level();
		 $html = str_replace("\t",'',$html);
		 $html = str_replace("\r",'',$html);
		 $html = str_replace("\n",'',$html);
		
		 return $html; 
	}
	/*reaction function*/ 
	
	/* welcome */
   function combo_welcome($user_time = 0){
		global $wo;
		$hour_normal = date("H") + $user_time;
		//echo json_encode($hour_normal); 
		if($hour_normal < 0){
		 return $wo['lang']['plugin_combo_good_evening'];
		}elseif($hour_normal == 0){
		 return $wo['lang']['plugin_combo_good_nigth'];
		}elseif($hour_normal >= 1 && $hour_normal < 12){
		 return $wo['lang']['plugin_combo_good_morning'];
		}elseif($hour_normal >= 12 && $hour_normal < 20){
		 return $wo['lang']['plugin_combo_good_afternoon'];
		}elseif($hour_normal >= 20){
		 return $wo['lang']['plugin_combo_good_evening'];
		}
	}
	/*list tag friend */
	function tags_in_post($post_id){
		global $sqlConnect;
		if(!$post_id){ return false; } 
		$resource = $sqlConnect->query("SELECT T.*, U.* FROM posts_tags AS T LEFT JOIN Wo_Users AS U ON T.user_id = U.user_id WHERE T.post_id='{$post_id}'");
		$CountPeoples = $resource->num_rows;

		if($CountPeoples > 0){
			$counts_P = $CountPeoples-1;
			while ($row = $resource->fetch_assoc()){ 
					if($row['avatar']!= ''){ $row['user_picture'] =  $row['avatar']; } else { $row['user_picture'] = 'upload/photos/d-avatar.jpg'; }
					if($row['first_name']!= ''){ $row['user_fullname'] =  $row['first_name'].' '.$row['last_name']; } else { $row['user_fullname'] = $row['username']; }
				$peoples[] = $row;
			}
			if($CountPeoples == 1){ 
				foreach ( $peoples as $key => $people){ 
					$text_return = " with ".'<span class="user-popover" data-type="user" data-id="'.$people['user_id'].'">
					<a class="profile_Link  text-link" href="'.$people['username'].'">'.$people['user_fullname'].'</a>
					</span>';
				}
			}elseif($CountPeoples == 2){
				$text_return .= " with "; 
				foreach ( $peoples as $key => $people){ if($key == 1){ $text_return .= ' and ';} 
					$text_return .= '<span class="user-popover" data-type="user" data-id="'.$people['user_id'].'">
					<a class="profile_Link text-link" href="'.$people['username'].'">'.$people['user_fullname'].'</a>
					</span>';
				}
			}elseif($CountPeoples > 2){ 
				$text_return .= " with "; 
				foreach ( $peoples as $key => $people){
					if($key == 0){ 
						$text_return .= '<span class="user-popover" data-type="user" data-id="'.$people['user_id'].'">
						<a class="profile_Link text-link" href="'.$people['username'].'">'.$people['user_fullname'].'</a>
						</span> and <a data-hover="tooltip" aria-label=" ';
					} 
					if($key != 0){ 
						$text_return .= ' '.$people['user_fullname'];
					}
				} 
				$text_return .= '" data-tooltip-position="below" data-toggle="modal" data-url="'.$wo['config']['site_url'].'/plugin_requests.php?f=combo&s=who_tag&post_id='.$post_id.'">'.$counts_P.' people more</a> ';
			}
			//echo json_encode($text_return);
			return $text_return;
		}	
	}
?>