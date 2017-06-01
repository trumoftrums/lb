<?php 
/* plugins-> functions general
* Plugins for Wowonder 
* @autor Pp Galvan - LdrMx
*/
	
	function check_post($id, $privacy_check = true) {
		global $wo, $sqlConnect;
		$id = Wo_Secure($id);
		
		/* get post */
		$get_post = $sqlConnect->query("SELECT Wo_Posts.* FROM Wo_Posts WHERE post_id = '{$id}' LIMIT 1");
		if($get_post->num_rows == 0) { return false; }
		$post = $get_post->fetch_assoc();
		
		/* get the author */
		$post['author_id'] = $post['user_id'];
		
		/* check privacy */
		if(!$privacy_check || ($privacy_check && check_privacy($post['postPrivacy'], $post['user_id']))) { return $post; }		
		return false;
	}

	
	function check_privacy($privacy, $author_id) {
		global $wo;
		if($privacy == '3') { return false; }
		if($privacy != '3') { return true; }
		if($wo['loggedin'] == true) {		
			/* check if the viewer is the target */
			if($author_id == $wo['user']['user_id']) { return true; }
		}
		return false;
	}

	
	function truncate($text, $length = "20", $points = true) {
		if( strlen($text) <= $length ) { return $text; }
		$add = '';
		if($points == true){ $add = '..'; }
		
		return preg_replace('/\s+?(\S+)?$/', '', substr($text, 0, $length)).$add;	
	}
	

	function send_email($email, $subject, $body) {
		global $system;
		$header  = "MIME-Version: 1.0\r\n";
		$header .= "Mailer: ".$system['system_title']."\r\n";
		$header .= "Content-Type: text/plain; charset=\"utf-8\"\r\n";
		$header .= "Content-Transfer-Encoding: 7bit\r\n";
		if(@mail($email, $subject, $body, $header)) {
			return true;
		}else {
			return false;
		}
	}
	
	
	function return_json($response = '') {
		header("Expires: Mon, 14 Jun 2005 05:00:00 GMT"); // Date in the past
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Pragma: no-cache"); // HTTP/1.0
		header("Content-Type: application/json");
		exit(json_encode($response));
	}
	
	
	function Register_Post($re_data = array('recipient_id' => 0)) {
		global $wo, $sqlConnect;
		
		$is_there_video = false;
		if (empty($re_data['user_id']) or $re_data['user_id'] == 0) {
			$re_data['user_id'] = $wo['user']['user_id'];
		}
		if (!is_numeric($re_data['user_id']) or $re_data['user_id'] < 0) {
			return false;
		}
		if ($re_data['user_id'] == $wo['user']['user_id']) {
			$timeline = $wo['user'];
		} else {
			$re_data['user_id'] = Wo_Secure($re_data['user_id']);
			$timeline           = Wo_UserData($re_data['user_id']);
		}
		if ($timeline['user_id'] != $wo['user']['user_id']) {
			return false;
		}
		if (!empty($re_data['page_id'])) {
			if (Wo_IsPageOnwer($re_data['page_id']) === false) {
				return false;
			}
		}
		if (!empty($re_data['group_id'])) {
			if (Wo_CanBeOnGroup($re_data['group_id']) === false) {
				return false;
			}
		}
		if (Wo_CheckIfUserCanPost($wo['config']['post_limit']) === false) {
			return false;
		}
		if (!empty($re_data['postText'])) {
			if ($wo['config']['maxCharacters'] > 0) {
				if (mb_strlen($re_data['postText']) > $wo['config']['maxCharacters']) {
				}
			}
			$re_data['postVine']        = '';
			$re_data['postYoutube']     = '';
			$re_data['postVimeo']       = '';
			$re_data['postDailymotion'] = '';
			$re_data['postFacebook']    = '';
			if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $re_data['postText'], $match)) {
				$re_data['postYoutube'] = Wo_Secure($match[1]);
				$is_there_video         = true;
			}
			if (preg_match("#(?<=vine.co/v/)[0-9A-Za-z]+#", $re_data['postText'], $match)) {
				$re_data['postVine'] = Wo_Secure($match[0]);
				$is_there_video      = true;
			}
			if (preg_match("#https?://vimeo.com/([0-9]+)#i", $re_data['postText'], $match)) {
				$re_data['postVimeo'] = Wo_Secure($match[1]);
				$is_there_video       = true;
			}
			if (preg_match('#http://www.dailymotion.com/video/([A-Za-z0-9]+)#s', $re_data['postText'], $match)) {
				$re_data['postDailymotion'] = Wo_Secure($match[1]);
				$is_there_video             = true;
			}
			if (preg_match('~([A-Za-z0-9]+)/videos/(?:t\.\d+/)?(\d+)~i', $re_data['postText'], $match)) {
				$re_data['postFacebook'] = Wo_Secure($match[0]);
				$is_there_video          = true;
			}
			if (preg_match("~\bfacebook\.com.*?\bv=(\d+)~", $re_data['postText'], $match)) {
				$is_there_video = true;
			}
			if (preg_match('%(?:https?://)(?:www\.)?soundcloud\.com/([\-a-z0-9_]+/[\-a-z0-9_]+)%im', $re_data['postText'], $match)) {
				$arrContextOptions = array(
					"ssl" => array(
						"verify_peer" => false,
						"verify_peer_name" => false
					)
				);
				$url               = "https://api.soundcloud.com/resolve.json?url=" . $match[0] . "&client_id=d4f8636b1b1d07e4461dcdc1db226a53";
				$track_json        = @file_get_contents($url, false, stream_context_create($arrContextOptions));
				$track             = json_decode($track_json, true);
				if (!empty($track[0]['tracks'][0]['id'])) {
					$re_data['postSoundCloud'] = $track[0]['tracks'][0]['id'];
				} else if (!empty($track['id'])) {
					$re_data['postSoundCloud'] = $track['id'];
				}
				$is_there_video = true;
			}
			$link_regex = '/(http\:\/\/|https\:\/\/|www\.)([^\ ]+)/i';
			$i          = 0;
			preg_match_all($link_regex, $re_data['postText'], $matches);
			foreach ($matches[0] as $match) {
				$match_url           = strip_tags($match);
				$syntax              = '[a]' . urlencode($match_url) . '[/a]';
				$re_data['postText'] = str_replace($match, $syntax, $re_data['postText']);
			}
			$mention_regex = '/@([A-Za-z0-9_]+)/i';
			preg_match_all($mention_regex, $re_data['postText'], $matches);
			foreach ($matches[1] as $match) {
				$match         = Wo_Secure($match);
				$match_user    = Wo_UserData(Wo_UserIdFromUsername($match));
				$match_search  = '@' . $match;
				$match_replace = '@[' . $match_user['user_id'] . ']';
				if (isset($match_user['user_id'])) {
					$re_data['postText'] = str_replace($match_search, $match_replace, $re_data['postText']);
					$mentions[]          = $match_user['user_id'];
				}
			}
			$hashtag_regex = '/#([^`~!@$%^&*\#()\-+=\\|\/\.,<>?\'\":;{}\[\]* ]+)/i';
			preg_match_all($hashtag_regex, $re_data['postText'], $matches);
			foreach ($matches[1] as $match) {
				if (!is_numeric($match)) {
					$hashdata = Wo_GetHashtag($match);
					if (is_array($hashdata)) {
						$match_search        = '#' . $match;
						$match_replace       = '#[' . $hashdata['id'] . ']';
						if (mb_detect_encoding($match_search, 'ASCII', true)) {
							$re_data['postText'] = preg_replace("/$match_search\b/i", $match_replace,  $re_data['postText']);
						} else {
							$re_data['postText'] = str_replace($match_search, $match_replace, $re_data['postText']);
						}
						$hashtag_query       = "UPDATE " . T_HASHTAGS . " SET `last_trend_time` = " . time() . ", `trend_use_num` = " . ($hashdata['trend_use_num'] + 1) . " WHERE `id` = " . $hashdata['id'];
						$hashtag_sql_query   = mysqli_query($sqlConnect, $hashtag_query);
					}
				}
			}
		}
		$re_data['registered'] = date('n') . '/' . date("Y");
		if ($is_there_video == true) {
			$re_data['postFile']        = '';
			$re_data['postLinkImage']   = '';
			$re_data['postLinkTitle']   = '';
			$re_data['postLinkContent'] = '';
			$re_data['postLink']        = '';
		}
		if (!empty($re_data['postVine'])) {
			$re_data['postYoutube']     = '';
			$re_data['postVimeo']       = '';
			$re_data['postDailymotion'] = '';
			$re_data['postFacebook']    = '';
			$re_data['postSoundCloud']  = '';
		}
		if (!empty($re_data['postYoutube'])) {
			$re_data['postVine']        = '';
			$re_data['postVimeo']       = '';
			$re_data['postDailymotion'] = '';
			$re_data['postFacebook']    = '';
			$re_data['postSoundCloud']  = '';
		}
		if (!empty($re_data['postVimeo'])) {
			$re_data['postVine']        = '';
			$re_data['postYoutube']     = '';
			$re_data['postDailymotion'] = '';
			$re_data['postFacebook']    = '';
			$re_data['postSoundCloud']  = '';
		}
		if (!empty($re_data['postDailymotion'])) {
			$re_data['postYoutube']    = '';
			$re_data['postVimeo']      = '';
			$re_data['postVine']       = '';
			$re_data['postFacebook']   = '';
			$re_data['postSoundCloud'] = '';
		}
		if (!empty($re_data['postFacebook'])) {
			$re_data['postYoutube']     = '';
			$re_data['postVimeo']       = '';
			$re_data['postDailymotion'] = '';
			$re_data['postVine']        = '';
			$re_data['postSoundCloud']  = '';
		}
		if (!empty($re_data['postSoundCloud'])) {
			$re_data['postYoutube']     = '';
			$re_data['postVimeo']       = '';
			$re_data['postDailymotion'] = '';
			$re_data['postFacebook']    = '';
			$re_data['postVine']        = '';
		}
		
		if (empty($re_data['multi_image'])) {
			$re_data['multi_image'] = 0;
		}
		if (empty($re_data['origin_id']) && empty($re_data['postText']) && empty($re_data['album_name']) && $re_data['multi_image'] == 0 && empty($re_data['postFacebook']) && empty($re_data['postVimeo']) && empty($re_data['postDailymotion']) && empty($re_data['postVine']) && empty($re_data['postYoutube']) && empty($re_data['postFile']) && empty($re_data['postSoundCloud']) && empty($re_data['postFeeling']) && empty($re_data['postListening']) && empty($re_data['postPlaying']) && empty($re_data['postWatching']) && empty($re_data['postTraveling']) && empty($re_data['postMap']) && empty($re_data['product_id'])) {
			return false;
		}
		
		if (!empty($re_data['recipient_id']) && is_numeric($re_data['recipient_id']) && $re_data['recipient_id'] > 0) {
			if ($re_data['recipient_id'] == $re_data['user_id']) {
				return false;
			}
			$recipient = Wo_UserData($re_data['recipient_id']);
			if (empty($recipient['user_id'])) {
				return false;
			}
			if (!empty($recipient['user_id'])) {
				if ($recipient['post_privacy'] == 'ifollow') {
					if (Wo_IsFollowing($recipient['user_id'], $wo['user']['user_id']) === false) {
						return false;
					}
				} else if ($recipient['post_privacy'] == 'nobody') {
					return false;
				}
			}
		}
		if (!isset($re_data['postType'])) {
			$re_data['postType'] = 'post';
		}
		if (isset($re_data['origin_id']) && $re_data['origin_id'] != 0) {
			$re_data['postType'] = 'share';
		}
		if (!empty($re_data['page_id'])) {
			$re_data['user_id'] = 0;
		}
		$fields  = '`' . implode('`, `', array_keys($re_data)) . '`';
		$data    = '\'' . implode('\', \'', $re_data) . '\'';
		$query   = mysqli_query($sqlConnect, "INSERT INTO " . T_POSTS . " ({$fields}) VALUES ({$data})");
		$post_id = mysqli_insert_id($sqlConnect);
		if ($query) {
			mysqli_query($sqlConnect, "UPDATE " . T_POSTS . " SET `post_id` = {$post_id} WHERE `id` = {$post_id}");
			if (isset($recipient['user_id'])) {
				$notification_data_array = array(
					'recipient_id' => $recipient['user_id'],
					'post_id' => $post_id,
					'type' => 'profile_wall_post',
					'url' => 'index.php?link1=post&id=' . $post_id
				);
				Wo_RegisterNotification($notification_data_array);
			}
			if (isset($mentions) && is_array($mentions)) {
				foreach ($mentions as $mention) {
					$notification_data_array = array(
						'recipient_id' => $mention,
						'page_id' => $re_data['page_id'],
						'type' => 'post_mention',
						'url' => 'index.php?link1=post&id=' . $post_id
					);
					Register_Notification($notification_data_array);
				}
			}
			return $post_id;
		}	
	}
	
	/*delete post*/
	function Delete_Post($post_id = 0) {
		global $wo, $sqlConnect, $cache;
		if ($post_id < 1 || empty($post_id) || !is_numeric($post_id)) {
			return false;
		}
		if ($wo['loggedin'] == false) {
			return false;
		}
		$user_id = Wo_Secure($wo['user']['user_id']);
		$post_id = Wo_Secure($post_id);
		$query   = mysqli_query($sqlConnect, "SELECT * FROM " . T_POSTS . " WHERE `id` = {$post_id} AND (`user_id` = {$user_id} OR `recipient_id` = {$user_id} OR `page_id` IN (SELECT `page_id` FROM " . T_PAGES . " WHERE `user_id` = {$user_id}) OR `group_id` IN (SELECT `id` FROM " . T_GROUPS . " WHERE `user_id` = {$user_id}))");
		if (mysqli_num_rows($query) > 0 || (Wo_IsAdmin() || Wo_IsModerator())) {
			$fetched_data = mysqli_fetch_assoc($query);
			if ($fetched_data['postType'] == 'profile_picture' || $fetched_data['postType'] == 'profile_picture_deleted' || $fetched_data['postType'] == 'profile_cover_picture') {
				$query_delete_3 = mysqli_query($sqlConnect, "UPDATE " . T_POSTS . " SET `postType` = 'profile_picture_deleted' WHERE `id` = '" . $fetched_data['id'] . "'");
				return true;
			}
			if (isset($fetched_data['postFile']) && !empty($fetched_data['postFile'])) {
				if ($fetched_data['postType'] != 'profile_picture' && $fetched_data['postType'] != 'profile_cover_picture') {
					@unlink(trim($fetched_data['postFile']));
					$delete_from_s3 = Wo_DeleteFromToS3($fetched_data['postFile']);
				}
			}
			/*share*/
			if (isset($fetched_data['origin_id']) && !empty($fetched_data['origin_id'])) {
					  $sqlConnect->query("UPDATE Wo_Posts SET shares = shares-1 WHERE post_id = '{$fetched_data['origin_id']}' LIMIT 1");  
			}
			if (isset($fetched_data['postLinkImage']) && !empty($fetched_data['postLinkImage'])) {
				@unlink($fetched_data['postLinkImage']);
				$delete_from_s3 = Wo_DeleteFromToS3($fetched_data['postLinkImage']);
			}
			if (!empty($fetched_data['album_name']) || !empty($fetched_data['multi_image'])) {
				$query_delete_4 = mysqli_query($sqlConnect, "SELECT `image` FROM " . T_ALBUMS_MEDIA . " WHERE `post_id` = {$post_id}");
				while ($fetched_delete_data = mysqli_fetch_assoc($query_delete_4)) {
					$explode2 = @end(explode('.', $fetched_delete_data['image']));
					$explode3 = @explode('.', $fetched_delete_data['image']);
					$media_2  = $explode3[0] . '_small.' . $explode2;
					@unlink(trim($media_2));
					@unlink($fetched_delete_data['image']);
					$delete_from_s3 = Wo_DeleteFromToS3($media_2);
					$delete_from_s3 = Wo_DeleteFromToS3($fetched_delete_data['image']);
				}
			}
			$query_two_2 = mysqli_query($sqlConnect, "SELECT `id` FROM " . T_COMMENTS . " WHERE `post_id` = {$post_id}");
			while ($fetched_data = mysqli_fetch_assoc($query_two_2)) {
				Wo_DeletePostComment($fetched_data['id']);
			}
			$product = Wo_PostData($post_id);
			$product_id = $product['product_id'];
			if (!empty($product_id)) {
				$query_two_3 = mysqli_query($sqlConnect, "SELECT `image` FROM " . T_PRODUCTS_MEDIA . " WHERE `product_id` = {$product_id}");
				while ($fetched_data = mysqli_fetch_assoc($query_two_3)) {
					$explode2 = @end(explode('.', $fetched_data['image']));
					$explode3 = @explode('.', $fetched_data['image']);
					$media_2  = $explode3[0] . '_small.' . $explode2;
					@unlink(trim($media_2));
					@unlink($fetched_data['image']);
					$delete_from_s3 = Wo_DeleteFromToS3($media_2);
					$delete_from_s3 = Wo_DeleteFromToS3($fetched_data['image']);
				}
			}
			$query_delete = mysqli_query($sqlConnect, "DELETE FROM " . T_POSTS . " WHERE `id` = {$post_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_POSTS . " WHERE `post_id` = {$post_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_ALBUMS_MEDIA . " WHERE `post_id` = {$post_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_COMMENTS . " WHERE `post_id` = {$post_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_WONDERS . " WHERE `post_id` = {$post_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_LIKES . " WHERE `post_id` = {$post_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_NOTIFICATION . " WHERE `post_id` = {$post_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_SAVED_POSTS . " WHERE `post_id` = {$post_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_REPORTS . " WHERE `post_id` = {$post_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_PINNED_POSTS . " WHERE `post_id` = {$post_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_ACTIVITIES . " WHERE `post_id` = {$post_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_PRODUCTS_MEDIA . " WHERE `product_id` = {$product_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_PRODUCTS . " WHERE `id` = {$product_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_POLLS . " WHERE `post_id` = {$post_id}");
			$query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_VOTES . " WHERE `post_id` = {$post_id}");
			
			$get_posts = $sqlConnect->query("SELECT * FROM ".T_POSTS." WHERE `origin_id` = {$post_id}");
			if($get_posts->num_rows){
			   while($row = $get_posts->fetch_assoc()){
				   $query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_POSTS . " WHERE `post_id` = {$row['id']}");
				   $query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_COMMENTS . " WHERE `post_id` = {$row['id']}");
				   $query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_WONDERS . " WHERE `post_id` = {$row['id']}");
				   $query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_LIKES . " WHERE `post_id` = {$row['id']}");
				   $query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_NOTIFICATION . " WHERE `post_id` = {$row['id']}");
				   $query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_SAVED_POSTS . " WHERE `post_id` = {$row['id']}");
				   $query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_REPORTS . " WHERE `post_id` = {$row['id']}");
				   $query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_PINNED_POSTS . " WHERE `post_id` = {$row['id']}");
				   $query_delete .= mysqli_query($sqlConnect, "DELETE FROM " . T_ACTIVITIES . " WHERE `post_id` = {$row['id']}");
			   }
			}
						/*question*/
			if (!empty($fetched_data['question_id']) && $fetched_data['question_id'] != 0) {
				$query_delete .= mysqli_query($sqlConnect, "DELETE FROM posts_questions WHERE question_id='{$fetched_data['question_id']}' ");
				$query_delete .= mysqli_query($sqlConnect, "DELETE FROM posts_questions_options WHERE question_id='{$fetched_data['question_id']}' ");
				$query_delete .= mysqli_query($sqlConnect, "DELETE FROM questions_options_votes WHERE question_id='{$fetched_data['question_id']}' ");
			}
			
			return true;
		} else {
			return false;
		}
	}
		
	/*create notification and send email*/
	function Register_Notification($data = array()) {
		global $wo, $sqlConnect;
		if (empty($data['session_id'])) {
			if ($wo['loggedin'] == false) {
			   return false;
			}
		}
		if (!isset($data['recipient_id']) or empty($data['recipient_id']) or !is_numeric($data['recipient_id']) or $data['recipient_id'] < 1) {
			return false;
		}
		if (Wo_IsBlocked($data['recipient_id'])) {
			return false;
		}
		if (!isset($data['post_id']) or empty($data['post_id'])) {
			$data['post_id'] = 0;
		}
		if (!is_numeric($data['post_id']) or $data['recipient_id'] < 0) {
			return false;
		}
		if (empty($data['notifier_id']) or $data['notifier_id'] == 0) {
			$data['notifier_id'] = Wo_Secure($wo['user']['user_id']);
		}
		if (!is_numeric($data['notifier_id']) or $data['notifier_id'] < 1) {
			return false;
		}
		if ($data['notifier_id'] == $wo['user']['user_id']) {
			$notifier = $wo['user'];
		} else {
			$data['notifier_id'] = Wo_Secure($data['notifier_id']);
			$notifier            = Wo_UserData($data['notifier_id']);
			if (!isset($notifier['user_id'])) {
				return false;
			}
		}
		/**/if ($notifier['user_id'] != $wo['user']['user_id']) {
			return false;
		}
		if ($data['recipient_id'] == $data['notifier_id']) {
			return false;
		}
		if (!isset($data['text'])) {
			$data['text'] = '';
		}
		if (!isset($data['type']) or empty($data['type'])) {
			return false;
		}
		if (!isset($data['url']) or empty($data['url'])) {
			return false;
		}
		$recipient = Wo_UserData($data['recipient_id']);
		if (!isset($recipient['user_id'])) {
			return false;
		}
		$url                  = $data['url'];
		$recipient['user_id'] = Wo_Secure($recipient['user_id']);
		$data['post_id']      = Wo_Secure($data['post_id']);
		$data['type']         = Wo_Secure($data['type']);
		if (!empty($data['type2'])) {
			$data['type2'] = Wo_Secure($data['type2']);
		} else {
			$data['type2'] = '';
		}
		if ($data['text'] != strip_tags($data['text'])) {
			$data['text'] = '';
		}
		$data['text']            = Wo_Secure($data['text']);
		$notifier['user_id']     = Wo_Secure($notifier['user_id']);
		$page_notifcation_query  = '';
		$page_notifcation_query2 = '';
		if (!empty($data['page_id']) && $data['page_id'] > 0) {
			$page = Wo_PageData($data['page_id']);
			if (!isset($page['page_id'])) {
				return false;
			}
			$page_id = Wo_Secure($page['page_id']);
			if (isset($data['page_enable'])) {
				if ($data['page_enable'] !== false) {
					$notifier['user_id'] = 0;
				}
			} else {
				$notifier['user_id'] = 0;
			}
			$page_notifcation_query  = '`page_id`,';
			$page_notifcation_query2 = "{$page_id}, ";
		}
		$group_notifcation_query  = '';
		$group_notifcation_query2 = '';
		if (!empty($data['group_id']) && $data['group_id'] > 0) {
			$group = Wo_GroupData($data['group_id']);
			if (!isset($group['id'])) {
			}
			$group_id                 = Wo_Secure($group['id']);
			$group_notifcation_query  = '`group_id`,';
			$group_notifcation_query2 = "{$group_id}, ";
		}
		$query_one     = " SELECT `id` FROM " . T_NOTIFICATION . " WHERE `recipient_id` = " . $recipient['user_id'] . " AND `post_id` = " . $data['post_id'] . " AND `type` = '" . $data['type'] . "'";
		$sql_query_one = mysqli_query($sqlConnect, $query_one);
		if (mysqli_num_rows($sql_query_one) > 0) {
			if ($data['type'] != "following") {
				$query_two     = " DELETE FROM " . T_NOTIFICATION . " WHERE `recipient_id` = " . $recipient['user_id'] . " AND `post_id` = " . $data['post_id'] . " AND `type` = '" . $data['type'] . "'";
				$sql_query_two = mysqli_query($sqlConnect, $query_two);
			}
		}
		if (!isset($data['undo']) or $data['undo'] != true) {
			$query_three     = "INSERT INTO " . T_NOTIFICATION . " (`recipient_id`, `notifier_id`, {$page_notifcation_query} {$group_notifcation_query} `post_id`, `type`, `type2`, `text`, `url`, `time`, `app_src`) VALUES (" . $recipient['user_id'] . "," . $notifier['user_id'] . ",{$page_notifcation_query2} {$group_notifcation_query2} " . $data['post_id'] . ",'" . $data['type'] . "','" . $data['type2'] . "','" . $data['text'] . "','{$url}'," . time() . ",'" . $data['app_src'] . "')";
			$sql_query_three = mysqli_query($sqlConnect, $query_three);
			if ($sql_query_three) {
				if ($wo['config']['emailNotification'] == 1 && $recipient['emailNotification'] == 1) {
					$send_mail = false;
					if ($data['type'] == 'liked_post' && $recipient['e_liked'] == 1) {
						$send_mail = true;
					}
					if ($data['type'] == 'share_post' && $recipient['e_shared'] == 1) {
						$send_mail = true;
					}
					if ($data['type'] == 'share') {
						$send_mail = true;
					}
					if ($data['type'] == 'reaction_post') {
						$send_mail = true;
					}
					if ($data['type'] == 'comment' && $recipient['e_commented'] == 1) {
						$send_mail = true;
					}
					if ($data['type'] == 'following' && $recipient['e_followed'] == 1) {
						$send_mail = true;
					}
					if ($data['type'] == 'wondered_post' && $recipient['e_wondered'] == 1) {
						$send_mail = true;
					}
					if (($data['type'] == 'comment_mention' || $data['type'] == 'post_mention') && $recipient['e_mentioned'] == 1) {
						$send_mail = true;
					}
					if ($data['type'] == 'accepted_request' && $recipient['e_accepted'] == 1) {
						$send_mail = true;
					}
					if ($data['type'] == 'visited_profile' && $recipient['e_visited'] == 1) {
						$send_mail = true;
					}
					if ($data['type'] == 'joined_group' && $recipient['e_joined_group'] == 1) {
						$send_mail = true;
					}
					if ($data['type'] == 'liked_page' && $recipient['e_liked_page'] == 1) {
						$send_mail = true;
					}
					if ($data['type'] == 'profile_wall_post' && $recipient['e_profile_wall_post'] == 1) {
						$send_mail = true;
					}
					if ($send_mail == true) {
						$post_data_id      = Wo_PostData($data['post_id']);
						$post_data['text'] = '';
						if (!empty($post_data_id['postText'])) {
							$post_data['text'] = substr($post_data_id['postText'], 0, 20);
						}
						$data['notifier']        = $notifier;
						$data['url']             = Wo_SeoLink($url);
						$data['post_data']       = $post_data;
						$wo['emailNotification'] = $data;
						$send_message_data       = array(
							'from_email' => $wo['config']['siteEmail'],
							'from_name' => $wo['config']['siteName'],
							'to_email' => $recipient['email'],
							'to_name' => $recipient['name'],
							'subject' => 'New notification',
							'charSet' => 'utf-8',
							'message_body' => Wo_LoadPage('emails/notifiction-email'),
							'is_html' => true
						);
						if ($wo['config']['smtp_or_mail'] == 'smtp') {
							$send_message_data['insert_database'] = 1;
						}
						$send = Wo_SendMessage($send_message_data);
					}
				}
				return true;
			}
		}
	}


	function Register_Activity($data = array()) {
		global $sqlConnect, $wo;
		if ($wo['loggedin'] == false) {
			return false;
		}
		if ($wo['user']['show_activities_privacy'] == 0) {
			return false;
		}
		if (empty($data['post_id']) || !is_numeric($data['post_id']) || $data['post_id'] < 1) {
			return false;
		}
		if (empty($data['user_id']) || !is_numeric($data['user_id']) || $data['user_id'] < 1) {
			return false;
		}
		if (empty($data['activity_type'])) {
			return false;
		}
		$post_id       = Wo_Secure($data['post_id']);
		$user_id       = Wo_Secure($data['user_id']);
		$post_user_id  = Wo_Secure($data['post_user_id']);
		$activity_type = Wo_Secure($data['activity_type']);
		$app_src = Wo_Secure($data['app_src']);
		$time          = time();
	  /**/  if ($user_id == $post_user_id) {
			return false;
		} 
		$query_insert = "INSERT INTO " . T_ACTIVITIES . " (`user_id`, `post_id`, `activity_type`, `time`, `app_src`) VALUES ('{$user_id}', '{$post_id}', '{$activity_type}', '{$time}', '{$app_src}')";
		if (Wo_IsActivity($post_id, $user_id, $activity_type) === true) {
			$query_delete = mysqli_query($sqlConnect, "DELETE FROM " . T_ACTIVITIES . " WHERE `user_id` = '{$user_id}' AND `post_id` = '{$post_id}'");
			if ($query_delete) {
				$query_one = mysqli_query($sqlConnect, $query_insert);
			}
		} else {
			$query_one = mysqli_query($sqlConnect, $query_insert);
		}
		if ($query_one) {
			return true;
		}
	}
	

	function who_shares($post_id, $offset = 0) {
		global $sqlConnect;
		$rows = array();
		$offset *= 15;
		$get_posts = $sqlConnect->query("SELECT P.post_id, U.* FROM Wo_Posts AS P INNER JOIN Wo_Users AS U ON P.user_id=U.user_id WHERE P.postType='share' AND P.origin_id='{$post_id}'");
		if($get_posts->num_rows > 0) {
			while($row = $get_posts->fetch_assoc()) {
					if($row['avatar']!= ''){ $row['user_picture'] =  $row['avatar']; } else { $row['user_picture'] = 'upload/photos/d-avatar.jpg'; }
					if($row['first_name']!= ''){ $row['user_fullname'] =  $row['first_name'].' '.$row['last_name']; } else { $row['user_fullname'] = $row['username']; }
					$rows[] = $row;
			}
		}
		return $rows;
	}

	
	function who_likes($post_id, $offset = 0) {
		global $sqlConnect;
		$rows = array();
		$offset *= 15;
		$get_posts = $sqlConnect->query("SELECT L.*, U.* FROM Wo_Likes AS L INNER JOIN Wo_Users AS U ON L.user_id=U.user_id WHERE L.post_id='{$post_id}'");
		if($get_posts->num_rows > 0) {
			while($row = $get_posts->fetch_assoc()) {
				    $resource = $sqlConnect->query("SELECT reaction_filetype FROM reactions WHERE reaction_key='{$row['reaction']}' LIMIT 1");
					if($resource->num_rows > 0){ $get_info = $resource->fetch_assoc(); $row['reaction_filetype'] = $get_info['reaction_filetype']; } else { $row['reaction'] = 'like'; $row['reaction_filetype'] = 'png'; }
					if($row['avatar']!= ''){ $row['user_picture'] =  $row['avatar']; } else { $row['user_picture'] = 'upload/photos/d-avatar.jpg'; }
					if($row['first_name']!= ''){ $row['user_fullname'] =  $row['first_name'].' '.$row['last_name']; } else { $row['user_fullname'] = $row['username']; }
					$rows[] = $row;
			}
		}
		return $rows;
	}
?>