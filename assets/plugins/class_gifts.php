<?php 
class gifts{	
	
	//CREATE CATEGORY
	function create_category($new_category){
		global $sqlConnect;
		if(!$new_category){ return false; }
		$new_category = substr($new_category,0,255);
		$new_category = htmlspecialchars(stripslashes($new_category));
		
		$new_type = $sqlConnect->query("INSERT INTO gifts_category (	gifts_category_lang) VALUES ('0')");
		$cat_id = $sqlConnect->insert_id;
		
		
		//get all langs
		$resource = $sqlConnect->query("SHOW COLUMNS FROM Wo_Langs");
		while ($fetched_data = $resource->fetch_assoc()) {
			$data[] = $fetched_data['Field'];
		}
		unset($data[0]);
		unset($data[1]);
	
		//insert lang
		$langz = "";
		$sqlConnect->query("INSERT INTO `Wo_Langs` (`lang_key`) VALUES ('gift_".$cat_id."')");
		$new_lang = $sqlConnect->insert_id;
		$javascript_lang_import_first = TRUE; 	
		foreach($data as $key => $val){
			if( !$javascript_lang_import_first ) $langz .= ", ";
			$langz .= "`{$val}`='{$new_category}'";
			$javascript_lang_import_first = FALSE;
		}			
		$sql_new = "UPDATE `Wo_Langs` SET {$langz} WHERE id='{$new_lang}' LIMIT 1";			
		$sqlConnect->query($sql_new);
		
		//update
		$sqlConnect->query("UPDATE gifts_category SET gifts_category_lang ='{$new_lang}' WHERE gifts_category_id='{$cat_id}' LIMIT 1");	
		$db_update =  $sqlConnect->affected_rows;	
		
		if($db_update == 1){
		   return array('result' => 1, 'message' => "Successfully gift was created", 'results'=> $db_update, 'success' => true);
		} else {
		   return array('result' => 0, 'message' => 'Cant update try more late!', 'results'=> $db_update, 'error' => true);
		}
		
	}
	
	
	
	//DELETE CATEGORY
	function delete_category($category_id){
		global $sqlConnect;
		
		if(!$category_id){return false; }
		
		//delete all images
		$query = $sqlConnect->query("SELECT * FROM gifts WHERE gift_category='{$category_id}'");	
		while($row = $query->fetch_assoc()) {		
		    //delete images
			unlink("upload/gifts/{$row['gift_id']}.{$row['gift_filetype']}");
			unlink("upload/gifts/{$row['gift_id']}_t.jpg");
			unlink("upload/gifts/{$row['gift_id']}_s.jpg");
			//delete info
		    $sqlConnect->query('DELETE FROM gifts WHERE gift_id = '.$row['gift_id'].'');
		}
		
		//delete category
		$resource = $sqlConnect->query("SELECT * FROM gifts_category WHERE gifts_category_id = '{$category_id}' LIMIT 1");
		$cat = $resource->fetch_array(MYSQLI_ASSOC);
		// delete lang
		$sqlConnect->query("DELETE FROM Wo_Langs WHERE id = '{$cat['gifts_category_lang']}' LIMIT 1");
		$sqlConnect->query('DELETE FROM gifts_category WHERE gifts_category_id = '.$cat['gifts_category_id'].'');
        return array('result' => 1); 
	}
	
	
	//DELETE IMAGE
	function delete_image($image_id){
		global $sqlConnect;
		
		$resource = $sqlConnect->query('SELECT * FROM gifts WHERE gift_id = '.$image_id.'');
		$file = $resource->fetch_array(MYSQLI_ASSOC);
		
		$sqlConnect->query('DELETE FROM gifts WHERE gift_id = '.$file['gift_id'].'');
		
		unlink("upload/gifts/{$file['gift_id']}.{$file['gift_filetype']}");
		unlink("upload/gifts/{$file['gift_id']}_t.jpg");
		unlink("upload/gifts/{$file['gift_id']}_s.jpg");
	}

	
	//UPLOAD
	function upload($category, $cost, $width, $height) {
		  global $sqlConnect, $url;
		  
			$name = $_FILES['file']['name'];  
			$ext = pathinfo($name, PATHINFO_EXTENSION);
			
			//create directory 
			if(!file_exists('upload/gifts')) { mkdir('upload/gifts', 0777, true); } 
		  
			$allowed           = 'jpg,png,jpeg,gif';
			$new_string        = pathinfo($name, PATHINFO_FILENAME) . '.' . strtolower(pathinfo($name, PATHINFO_EXTENSION));
			$extension_allowed = explode(',', $allowed);
			$file_extension    = pathinfo($new_string, PATHINFO_EXTENSION);
			
			//extension allowed
			if(!in_array($file_extension, $extension_allowed)) { return false; }
		  
			$sqlConnect->query("INSERT INTO `gifts` (`gift_category`, `gift_cost`, `gift_date`) VALUES ('{$category}', '{$cost}', '".time()."')");
			$media_id = $sqlConnect->insert_id;
			
			$dest = "upload/gifts/";
		  
			$file_dest = $dest.$media_id.'.'.$ext;
			$file_dest_t = $dest.$media_id.'_t.jpg';
			$file_dest_s = $dest.$media_id.'_s.jpg';
			
			if(move_uploaded_file($_FILES['file']['tmp_name'], $file_dest)){
			     //Wo_Resize_Crop_Image($width, $height, $file_dest, $file_dest, 20);
				 Wo_Resize_Crop_Image(100, 100, $file_dest, $file_dest_t, 20);
				 Wo_Resize_Crop_Image(16, 16, $file_dest, $file_dest_s, 30);
				 /**/ 
				 $last_file = $file_dest;
				  $explode2  = @end(explode('.', $file_dest));
				  $explode3  = @explode('.', $file_dest);
				  //$last_file = $explode3[0] . '_full.' . $explode2;
				  //@Wo_CompressImage($file_dest, $last_file, 20);	 							  
			}
	 
			// UPLOAD AND RESIZE PHOTO IF NO ERROR
			if(!file_exists($file_dest)) {		
					$sqlConnect->query("DELETE FROM gifts WHERE gift_id='{$media_id}' LIMIT 1");
					@unlink($file_dest);
					return array( 'result' => 0);
			} else { 
			        //UPDATE COVER
					$sqlConnect->query("UPDATE gifts_category SET gifts_category_icon='{$media_id}' WHERE gifts_category_id='{$category}' LIMIT 1"); 
					
					// UPDATE ROW IF NO ERROR
					$sqlConnect->query("UPDATE gifts SET gift_filetype='{$ext}' WHERE gift_id='{$media_id}' LIMIT 1"); 
					return array( 'result' => 1, 'media_id' => $media_id ); 
			}	
	
		}
		
	}
	//
	
	
	
	//FUNCTIONS WILL TO ADD IN FUNCTIONS_STICKERS
	function giftgifts_list($last_id = false, $first_id = false, $limit = "10", $sort_by = "gift_id DESC", $where="", $depth = ""){
		  global $sqlConnect, $wo;
		  $sql = "";
		  $sql .= "SELECT * FROM gifts WHERE gift_filetype !='' ";
		  $sql .= ( $last_id ) ? " AND gift_id < " . $last_id : '';
		  $sql .= ( $first_id ) ? " AND gift_id > " . $first_id : '';
		  if( $where ) $sql .= " && {$where} ";
		  $sql .= " ORDER BY {$sort_by} ";
		  $sql .= " LIMIT {$limit} ";	

		  $AllGifts = $sqlConnect->query($sql);
		  $CountGC = $AllGifts->num_rows;	

		  $gifts = array();
		  $gifts_ids = array(); 

		  $gifts_file =  $wo['config']['site_url'].'/upload/gifts/'; 
		  while($row = $AllGifts->fetch_array(MYSQLI_ASSOC)){
			  $row['gift']  = $gifts_file.$row['gift_id'].'.'.$row['gift_filetype'];
			  $row['text']  = $wo['lang']['gift_'.$row['gift_lang']];
			  $gifts[] = $row;
			  $gifts_ids[] = $row['gift_id'];
		  }
		  return array('count'=>$CountGC, 'gift_ids'=>$gifts_ids, 'gift'=> $gifts);
	}
?>