<?php

/* Class Poke
 * Plugins for Sngine v2+
 * @author Pepe Galvan  - LdrMx
 */
 
class Poke{
 
     /**
     * get_pokes
     * @param integer $owner_id
     * @return array
     */	 
 public static function get_pokes( $owner_id )
    { global $sqlConnect;
        if ( !$owner_id ){  return false;  }
				
		/*get of database*/
		$res = $sqlConnect->query("SELECT t.*, u.user_id, u.username, u.first_name, u.last_name, u.gender, u.avatar FROM pokes t JOIN Wo_Users u ON u.user_id = t.user_id WHERE t.owner_id='{$owner_id}'");
        
		$user_arr = array();
		if($res->num_rows > 0){
          while ( $row = $res->fetch_array(MYSQLI_ASSOC) ){	
	        if($row['avatar']!= ''){ $row['user_picture'] =  $row['avatar']; } else { $row['user_picture'] = 'upload/photos/d-avatar.jpg'; }
			if($row['first_name']!= ''){ $row['user_fullname'] =  $row['first_name'].' '.$row['last_name']; } else { $row['user_fullname'] = $row['username']; }			
		    $row['time'] = date('Y-m-d H:i:s', $row['poke_stamp']);
            $user_arr[] = $row;
          }
		}
        return  $user_arr;
    }
	
    /**
     * get_friends limit if poke exist
     * @param integer $wo['user']_id
     * @param integer $offset
     * @return array
     */
    public static function get_friends($user_id, $offset = 0) {
        global $sqlConnect, $wo;
        $friends = array();

		$sql         = "SELECT `user_id`, `username`, `first_name`, `last_name`, `gender`, `avatar` FROM " . T_USERS . " WHERE `user_id` IN (SELECT `following_id` FROM " . T_FOLLOWERS . " WHERE `follower_id` = {$user_id} AND `following_id` <> {$user_id} AND `active` = '1') AND `active` = '1' 
		AND `user_id` NOT IN (SELECT `owner_id` FROM `pokes` WHERE `user_id` = {$user_id})
		AND `user_id` NOT IN (SELECT `user_id` FROM `pokes` WHERE `owner_id` = {$user_id})
		LIMIT 10
		";
		$get_friends = $sqlConnect->query($sql);
        if($get_friends->num_rows > 0) {
		/*generate array*/
            while($friend = $get_friends->fetch_assoc()) {
			if($friend['avatar']!= ''){ $friend['user_picture'] =  $friend['avatar']; } else { $friend['user_picture'] = 'upload/photos/d-avatar.jpg'; }
			if($friend['first_name']!= ''){ $friend['user_fullname'] =  $friend['first_name'].' '.$friend['last_name']; } else { $friend['user_fullname'] = $friend['username']; }			
                $friends[] = $friend;
            }
        }
        return $friends;
    }
 
  	
}
?>