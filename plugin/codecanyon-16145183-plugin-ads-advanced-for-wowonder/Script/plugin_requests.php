<?php
// +------------------------------------------------------------------------+
// | @author Deen Doughouz (DoughouzForest)
// | @author_url 1: http://www.wowonder.com
// | @author_url 2: http://codecanyon.net/user/doughouzforest
// | @author_email: wowondersocial@gmail.com   
// +------------------------------------------------------------------------+
// | WoWonder - The Ultimate Social Networking Platform
// | Copyright (c) 2016 WoWonder. All rights reserved.
// +------------------------------------------------------------------------+
require_once('assets/init.php');
use Aws\S3\S3Client;
$f = '';
$s = '';

$f = ( isset($_POST['f']) ? $_POST['f'] : ( isset($_GET['f']) ? $_GET['f'] : '0' ) );
$s = ( isset($_POST['s']) ? $_POST['s'] : ( isset($_GET['s']) ? $_GET['s'] : '0' ) );
$task = ( isset($_POST['task']) ? $_POST['task'] : ( isset($_GET['task']) ? $_GET['task'] : '' ) );

if (isset($f)) {
    $f = Wo_Secure($f, 0);
}
if (isset($s)) {
    $s = Wo_Secure($s, 0);
}
$hash_id = '';
if (!empty($_POST['hash_id'])) {
    $hash_id = $_POST['hash_id'];
} else if (!empty($_GET['hash_id'])) {
    $hash_id = $_GET['hash_id'];
} else if (!empty($_GET['hash'])) {
    $hash_id = $_GET['hash'];
} else if (!empty($_POST['hash'])) {
    $hash_id = $_POST['hash'];
}
$data = array();


/*voguepay pro*/
if ($f == 'get_voguepay_method') {
    if (!empty($_GET['type'])) {
        $html            = '';
		$pro_type        = $_GET['type'];
		$pro_type_case = $_GET['type'];
        $pro_types_array = array(1, 2, 3, 4);
        if (in_array($_GET['type'], $pro_types_array)) {
            switch ($pro_type_case) {
                case 1:
                    $type        = 'week';
                    $description = 'Star package (1 week)';
                    $price       = $wo['config']['weekly_price'] . '.00';
                    break;
                case 2:
                    $type        = 'month';
                    $description = 'Hot package (1 month)';
                    $price       = $wo['config']['monthly_price'] . '.00';
                    break;
                case 3:
                    $type        = 'year';
                    $description = 'Ultima package (1 year)';
                    $price       = $wo['config']['yearly_price'] . '.00';
                    break;
                case 4:
                    $type        = 'life-time';
                    $description = 'Vip package (life-time)';
                    $price       = $wo['config']['lifetime_price'] . '.00';
                    break;
            }
            $load = Wo_LoadPage('plugins/voguepay-go-pro');
            $load = str_replace('{pro_type_case}', $pro_type_case, $load);
			$load = str_replace('{type}', $type, $load);
			$load = str_replace('{pro_type}', $pro_type, $load);
            $load = str_replace('{pro_type_id}', $_GET['type'], $load);
            $load = str_replace('{pro_type_description}', $description, $load);
            $load = str_replace('{pro_type_price}', $price, $load);
            if (!empty($load)) {
                $data = array(
                    'status' => 200,
                    'html' => $load
                );
            }
        }
    }
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}

if ($f == 'voguepay_upgrade') {

    if (!isset($_GET['success'])) { header("Location: " . Wo_SeoLink('index.php?link1=oops')); exit(); }

    $is_pro = 0;
    $stop   = 0;
    $user   = Wo_UserData($wo['user']['user_id']);

    if ($user['is_pro'] != 0) { $stop = 1; }
   
	if ($stop == 0) {
        $pro_types_array = array(1,2,3,4);
        $pro_type = 0;
        if (!isset($_GET['pro_type']) || !in_array($_GET['pro_type'], $pro_types_array)) { header("Location: " . Wo_SeoLink('index.php?link1=oops')); exit(); }
        $pro_type = $_GET['pro_type'];
        
if(!empty($_POST['transaction_id'])){
    if($wo['system']['voguePayMerchantId'] == ''){ $json = file_get_contents('https://voguepay.com/?v_transaction_id='.$_POST['transaction_id'].'&type=json&demo=true'); }
	else { $json = file_get_contents('https://voguepay.com/?v_transaction_id='.$_POST['transaction_id'].'&type=json&demo=true'); }
	$transaction = json_decode($json, true);
	if($transaction['status'] == 'Approved'){ $is_pro = 1; }
}

    }
	
    if ($stop == 0) {
        $time = time();
        if ($is_pro == 1) {

            $update_array = array( 'pro_time' => time(), 'pro_type' => $pro_type , 'is_pro' => 1 );
            $mysqli       = Wo_UpdateUserData($wo['user']['user_id'], $update_array);

	  $payment_type = $pro_type;
	  $user_id = $wo['user']['user_id'];
      if ($payment_type == 1) {
            $amount = $wo['config']['weekly_price'];
            $type = 'weekly';
      } else if ($payment_type == 2) {
            $amount = $wo['config']['monthly_price'];
            $type = 'monthly';
      } else if ($payment_type == 3) {
            $amount = $wo['config']['yearly_price'];
            $type = 'yearly';
      } else if ($payment_type == 4) {
            $amount = $wo['config']['lifetime_price'];
            $type = 'lifetime';
      } else {
            return false;
      }
      $date = date('n') . '/' . date("Y");
      $query = mysqli_query($sqlConnect, "INSERT INTO " . T_PAYMENTS . " (`user_id`, `amount`, `date`, `type`) VALUES ({$user_id}, {$amount}, '{$date}', '{$type}')");
	  
            if ($mysqli) { header("Location: " . Wo_SeoLink('index.php?link1=upgraded')); exit(); } 
        } else { header("Location: " . Wo_SeoLink('index.php?link1=oops')); exit(); }
    }else { header("Location: " . Wo_SeoLink('index.php?link1=oops')); exit(); }
}
/*end voguepay pro*/

/* plugins ajax */
if( file_exists("assets/plugins/request_admin.php") ){ include "assets/plugins/request_admin.php";  }
if(in_array('Ads', $wo['plugin_list']['plugin_actived'])){ if( file_exists("assets/plugins/request_ads.php") ){  include "assets/plugins/request_ads.php";  } }
if(in_array('Question', $wo['plugin_list']['plugin_actived'])){ if( file_exists("assets/plugins/request_question.php") ){  include "assets/plugins/request_question.php";  } }
if(in_array('Share', $wo['plugin_list']['plugin_actived'])){ if( file_exists("assets/plugins/request_share.php") ){  include "assets/plugins/request_share.php";  } }
if(in_array('Pokes', $wo['plugin_list']['plugin_actived'])){ if( file_exists("assets/plugins/request_pokes.php") ){  include "assets/plugins/request_pokes.php";  } }
if(in_array('Points', $wo['plugin_list']['plugin_actived'])){ if( file_exists("assets/plugins/request_points.php") ){  include "assets/plugins/request_points.php";  } }
if(in_array('Combo', $wo['plugin_list']['plugin_actived'])){ if( file_exists("assets/plugins/request_combo.php") ){  include "assets/plugins/request_combo.php";  } }

mysqli_close($sqlConnect);
unset($wo);
?>