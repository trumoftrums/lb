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
/* Script Main Functions (File 3) */

function Wo_RegisterProductMedia($id, $media) {
    global $wo, $sqlConnect;
    if (empty($id) or !is_numeric($id) or $id < 1) {
        return false;
    }
    if (empty($media)) {
        return false;
    }
    $query_one = mysqli_query($sqlConnect, "INSERT INTO " . T_PRODUCTS_MEDIA . " (`product_id`,`image`) VALUES ({$id}, '{$media}')");
    if ($query_one) {
        return true;
    }
}

function Wo_RegisterProduct($registration_data) {
	global $wo, $sqlConnect;
    if (empty($registration_data)) {
        return false;
    }
    if (!empty($registration_data['description'])) {
        $link_regex = '/(http\:\/\/|https\:\/\/|www\.)([^\ ]+)/i';
        $i          = 0;
        preg_match_all($link_regex, $registration_data['description'], $matches);
        foreach ($matches[0] as $match) {
            $match_url           = strip_tags($match);
            $syntax              = '[a]' . urlencode($match_url) . '[/a]';
            $registration_data['description'] = str_replace($match, $syntax, $registration_data['description']);
        }
    }
    $fields                          = '`' . implode('`, `', array_keys($registration_data)) . '`';
    $data                            = '\'' . implode('\', \'', $registration_data) . '\'';
    $query                           = mysqli_query($sqlConnect, "INSERT INTO " . T_PRODUCTS . " ({$fields}) VALUES ({$data})");
    if ($query) {
        return mysqli_insert_id($sqlConnect);
    }
    return false;
}

function Wo_GetProduct($id = 0) {
	global $wo, $sqlConnect;
    $data      = array();
    if (empty($id) or !is_numeric($id) or $id < 1) {
        return false;
    }
    $query_one = " SELECT * FROM " . T_PRODUCTS . " WHERE `id` = '{$id}' ORDER BY `id` DESC";
    $sql       = mysqli_query($sqlConnect, $query_one);
    $fetched_data = mysqli_fetch_assoc($sql);
    if (empty($fetched_data)) {
    	return array();
    }

    $fetched_data['images'] = Wo_GetProductImages($fetched_data['id']);
    $fetched_data['time_text'] = Wo_Time_Elapsed_String($fetched_data['time']);
    $fetched_data['post_id'] = Wo_GetPostIDFromProdcutID($fetched_data['id']);
    $fetched_data['edit_description'] = Wo_EditMarkup(br2nl($fetched_data['description'], true, false, false));
    $fetched_data['description'] = Wo_Markup($fetched_data['description'], true, false, false);
    $fetched_data['url'] = Wo_SeoLink('index.php?link1=post&id=' . $fetched_data['post_id']);
    return $fetched_data; 
}

function Wo_GetProductImages($id = 0) {
	global $wo, $sqlConnect;
    $data      = array();
    $id   = Wo_Secure($id);
    $query_one = "SELECT `id`,`image`,`product_id` FROM " . T_PRODUCTS_MEDIA . " WHERE `product_id` = {$id} ORDER BY `id` DESC";
    $sql       = mysqli_query($sqlConnect, $query_one);
    while ($fetched_data = mysqli_fetch_assoc($sql)) {
        $explode2                  = @end(explode('.', $fetched_data['image']));
        $explode3                  = @explode('.', $fetched_data['image']);
        $fetched_data['image_org'] = $explode3[0] . '_small.' . $explode2;
        $fetched_data['image_org'] = Wo_GetMedia($fetched_data['image_org']);
        $fetched_data['image']     = Wo_GetMedia($fetched_data['image']);
        $data[]                    = $fetched_data;
    }
    return $data;
}

function Wo_ProductImageData($data = array()) {
	global $wo, $sqlConnect;
    if (!empty($data['id'])) {
        $id = Wo_Secure($data['id']);
    }
    $order_by = '';
    if (!empty($data['after_image_id']) && is_numeric($data['after_image_id'])) {
        $data['after_image_id'] = Wo_Secure($data['after_image_id']);
        $subquery               = " `id` <> " . $data['after_image_id'] . " AND `id` < " . $data['after_image_id'];
        $order_by               = 'DESC';
    } else if (!empty($data['before_image_id']) && is_numeric($data['before_image_id'])) {
        $data['before_image_id'] = Wo_Secure($data['before_image_id']);
        $subquery                = " `id` <> " . $data['before_image_id'] . " AND `id` > " . $data['before_image_id'];
        $order_by                = 'ASC';
    } else {
        $subquery = " `id` = {$id}";
    }
    if (!empty($data['post_id']) && is_numeric($data['post_id'])) {
        $data['post_id'] = Wo_Secure($data['post_id']);
        $subquery .= " AND `post_id` = " . $data['post_id'];
    }
    $query_one    = "SELECT * FROM " . T_PRODUCTS_MEDIA . " WHERE $subquery ORDER by `id` {$order_by}";
    $sql          = mysqli_query($sqlConnect, $query_one);
    $fetched_data = mysqli_fetch_assoc($sql);
    if (!empty($fetched_data)) {
        $fetched_data['image_org'] = Wo_GetMedia($fetched_data['image']);
    }
    return $fetched_data;
}

function Wo_GetWelcomeFileds() {
    global $wo, $sqlConnect;
    $data      = array();
    $query_one = " SELECT * FROM " . T_FIELDS . " WHERE `registration_page` = '1' ORDER BY `id` ASC";
    $sql       = mysqli_query($sqlConnect, $query_one);
    while ($fetched_data = mysqli_fetch_assoc($sql)) {
        $fetched_data['fid']      = 'fid_' . $fetched_data['id']; 
        $fetched_data['name'] = preg_replace_callback("/{{LANG (.*?)}}/", function($m) use($wo) {
            return (isset($wo['lang'][$m[1]])) ? $wo['lang'][$m[1]] : '';
        },  $fetched_data['name']);
        $fetched_data['description'] = preg_replace_callback("/{{LANG (.*?)}}/", function($m) use($wo) {
            return (isset($wo['lang'][$m[1]])) ? $wo['lang'][$m[1]] : '';
        }, $fetched_data['description']);
        $fetched_data['type'] = preg_replace_callback("/{{LANG (.*?)}}/", function($m) use($wo) {
           return (isset($wo['lang'][$m[1]])) ? $wo['lang'][$m[1]] : '';
        }, $fetched_data['type']);
        $data[]                   = $fetched_data;
    }
    return $data;
}

function Wo_MarkPostAsSold($post_id = 0, $product_id = 0) {
    global $sqlConnect, $wo;
    if ($wo['loggedin'] == false) {
        return false;
    }
    $user_id = Wo_Secure($wo['user']['user_id']);
    $post_id = Wo_Secure($post_id);
    if (empty($user_id) || !is_numeric($user_id) || $user_id < 1) {
        return false;
    }
     if (empty($product_id) || !is_numeric($product_id) || $product_id < 1) {
        return false;
    }
    if (empty($post_id) || !is_numeric($post_id) || $post_id < 1) {
        return false;
    }
    if (Wo_PostExists($post_id) === false) {
        return false;
    }
    if (Wo_IsPostOnwer($post_id, $user_id) === false) {
        return false;
    }
    if (Wo_IsProductSold($product_id)) {
        return false;
    }
    $query_text = "UPDATE " . T_PRODUCTS . " SET `status` = '1' WHERE `id` = '{$product_id}'";
    $query_two  = mysqli_query($sqlConnect, $query_text);
    if ($query_two) {
        return true;
    }
}

function Wo_IsProductSold($id) {
    global $sqlConnect, $wo;
    if ($wo['loggedin'] == false) {
        return false;
    }
    if (empty($id) || !is_numeric($id) || $id < 1) {
        return false;
    }
    $id       = Wo_Secure($id);
    $query_one     = mysqli_query($sqlConnect, "SELECT COUNT(`id`) as `count` FROM " . T_PRODUCTS . " WHERE `id` = {$id} AND `status` = '1'");
    $sql_query_one = mysqli_fetch_assoc($query_one);
    return ($sql_query_one['count'] == 1) ? true : false;
}

function Wo_GetPostIDFromProdcutID($id) {
    global $sqlConnect;
    if (empty($id) or !is_numeric($id) or $id < 1) {
        return false;
    }
    $id            = Wo_Secure($id);
    $query_one     = "SELECT `id` FROM " . T_POSTS . " WHERE `product_id` = '{$id}'";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
    return $sql_fetch_one['id'];
}

function Wo_UpdateProductData($product_id, $update_data) {
    global $wo, $sqlConnect;
    if ($wo['loggedin'] == false) {
        return false;
    }
    if (empty($product_id) || !is_numeric($product_id) || $product_id < 0) {
        return false;
    }
    if (empty($update_data)) {
        return false;
    }
    $product_id = Wo_Secure($product_id);
    $post_id    = Wo_GetPostIDFromProdcutID($product_id);
    if (empty($post_id)) {
        return false;
    }
    if (Wo_IsPostOnwer($post_id, $wo['user']['user_id']) === false) {
        return false;
    }
    $update = array();
    if (!empty($update_data['description'])) {
        $link_regex = '/(http\:\/\/|https\:\/\/|www\.)([^\ ]+)/i';
        $i          = 0;
        preg_match_all($link_regex, $update_data['description'], $matches);
        foreach ($matches[0] as $match) {
            $match_url           = strip_tags($match);
            $syntax              = '[a]' . urlencode($match_url) . '[/a]';
            $update_data['description'] = str_replace($match, $syntax, $update_data['description']);
        }
    }
    foreach ($update_data as $field => $data) {
        $update[] = '`' . $field . '` = \'' . Wo_Secure($data, 0) . '\'';
    }
    $impload   = implode(', ', $update);
    $query_one = " UPDATE " . T_PRODUCTS . " SET {$impload} WHERE `id` = {$product_id}";
    $query     = mysqli_query($sqlConnect, $query_one);
    if ($query) {
        return true;
    }
    return false;
}

function Wo_GetProducts($filter_data = array()) {
    global $wo, $sqlConnect;
    $data      = array();
    $query_one = " SELECT `id`, `user_id` FROM " . T_PRODUCTS . " WHERE status <> '1'";
    if (!empty($filter_data['c_id'])) {
        $category = $filter_data['c_id'];
        $query_one .= " AND `category` = '{$category}'";
    }
    if (!empty($filter_data['after_id'])) {
        if (is_numeric($filter_data['after_id'])) {
            $after_id = Wo_Secure($filter_data['after_id']);
            $query_one .= " AND `id` < '{$after_id}' AND `id` <> $after_id";
        }
    }
    if (!empty($filter_data['keyword'])) {
        $keyword = Wo_Secure($filter_data['keyword']);
        $query_one .= " AND `name` LIKE '%{$keyword}%'";
    }
    if (!empty($filter_data['user_id'])) {
        $user_id = Wo_Secure($filter_data['user_id']);
        $query_one .= " AND `user_id` = '{$user_id}'";
    }
    $query_one .= " ORDER BY `id` DESC";
    if (!empty($filter_data['limit'])) {
        if (is_numeric($filter_data['limit'])) {
            $limit = Wo_Secure($filter_data['limit']);
            $query_one .= " LIMIT {$limit}";
        }
    }
    $sql       = mysqli_query($sqlConnect, $query_one);
    while ($fetched_data = mysqli_fetch_assoc($sql)) {
        $products   = Wo_GetProduct($fetched_data['id']);
        $products['seller']   = Wo_UserData($fetched_data['user_id']);
        $data[]     = $products;
    }
    return $data;
}

function Wo_AddOption($post_id, $text) {
    global $wo, $sqlConnect;
    if ($wo['loggedin'] == false) {
        return false;
    }
    if (empty($post_id) || !is_numeric($post_id) || $post_id < 0) {
        return false;
    }
    if (empty($text)) {
        return false;
    }
    $post_id = Wo_Secure($post_id);
    $text = Wo_Secure($text);
    $time = time();
    $query_one = "INSERT INTO " . T_POLLS . " (`post_id`, `text`, `time`) VALUES ('{$post_id}', '{$text}', '{$time}')";
    $query     = mysqli_query($sqlConnect, $query_one);
    if ($query) {
        return true;
    }
    return false;
}

function Wo_GetPostOptions($post_id = 0) {
    global $sqlConnect;
    if (empty($post_id) or !is_numeric($post_id) or $post_id < 1) {
        return false;
    }
    $data = array();
    $post_id       = Wo_Secure($post_id);
    $query_one     = "SELECT * FROM " . T_POLLS . " WHERE `post_id` = '{$post_id}'";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    while ($fetched_data = mysqli_fetch_assoc($sql_query_one)) {
        $data[]  = $fetched_data;
    }
    return $data;
}
function Wo_GetPostIDFromOptionID($id) {
    global $sqlConnect;
    if (empty($id) or !is_numeric($id) or $id < 1) {
        return false;
    }
    $id            = Wo_Secure($id);
    $query_one     = "SELECT `post_id` FROM " . T_POLLS . " WHERE `id` = '{$id}'";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    $sql_fetch_one = mysqli_fetch_assoc($sql_query_one);
    return $sql_fetch_one['post_id'];
}
function Wo_VoteUp($option_id = 0, $user_id = 0) {
    global $sqlConnect;
    if (empty($user_id) or !is_numeric($user_id) or $user_id < 1) {
        return false;
    }
    if (empty($option_id) or !is_numeric($option_id) or $option_id < 1) {
        return false;
    }
    $user_id   = Wo_Secure($user_id);
    $option_id = Wo_Secure($option_id);
    $post_id   = Wo_GetPostIDFromOptionID($option_id);
    if (empty($post_id)) {
        return false;
    }
    if (Wo_IsPostVoted($post_id, $user_id)) {
        return false;
    }
    if (Wo_IsOptionVoted($option_id, $user_id)) {
        return false;
    }
    $fields = '(`option_id`, `user_id`, `post_id`)';
    $query_one = "INSERT INTO " . T_VOTES . " {$fields} VALUES ('{$option_id}', '{$user_id}', '{$post_id}')";
    $sql       = mysqli_query($sqlConnect, $query_one);
    if ($sql) {
        return true;
    }
}
function Wo_IsOptionVoted($option_id, $user_id) {
    global $wo, $sqlConnect;
    if (empty($user_id) || empty($option_id)) {
        return false;
    }
    if (!is_numeric($option_id)) {
        return false;
    }
    $user_id   = Wo_Secure($user_id);
    $option_id = Wo_Secure($option_id);
    $query_one = "SELECT COUNT(id) as count FROM " . T_VOTES . " WHERE `option_id` = '{$option_id}' AND `user_id` = '{$user_id}'";
    $sql       = mysqli_query($sqlConnect, $query_one);
    $sql_fetch = mysqli_fetch_assoc($sql);
    if ($sql_fetch['count'] > 0) {
        return true;
    }
    return false;
}
function Wo_IsPostVoted($post_id, $user_id) {
    global $wo, $sqlConnect;
    if (empty($user_id) || empty($post_id)) {
        return false;
    }
    if (!is_numeric($post_id)) {
        return false;
    }
    $user_id   = Wo_Secure($user_id);
    $post_id   = Wo_Secure($post_id);
    $query_one = "SELECT COUNT(id) as count FROM " . T_VOTES . " WHERE `post_id` = '{$post_id}' AND `user_id` = '{$user_id}'";
    $sql       = mysqli_query($sqlConnect, $query_one);
    $sql_fetch = mysqli_fetch_assoc($sql);
    if ($sql_fetch['count'] > 0) {
        return true;
    }
    return false;
}

function Ju_GetPercentageOfOptionPost($post_id) {
    global $wo, $sqlConnect;
    if (empty($post_id)) {
        return false;
    }
    if (!is_numeric($post_id)) {
        return false;
    }
    $data = array();
    $post_id       = Wo_Secure($post_id);
    $query_one     = "SELECT * FROM " . T_POLLS . " WHERE `post_id` = '{$post_id}'";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    $all           = 0;
    while ($fetched_data = mysqli_fetch_assoc($sql_query_one)) {
        $fetched_data['option_votes'] = Wo_GetVotes($fetched_data['id']);
        $data[]  = $fetched_data;
    }
    foreach ($data as $key => $value) {
        $all += $value['option_votes'];
    }
    $percentage_total = $all;
    foreach ($data as $key => $value) {
        $percentage                   = 0;
        $data[$key]['percentage']     = '0%';
        $data[$key]['percentage_num'] = 0;
        $data[$key]['all']            = $all;
        if ($percentage_total > 0) {
            $data[$key]['percentage']     = number_format(($value['option_votes'] / $percentage_total) * 100) . '%';
            $data[$key]['percentage_num'] = number_format(($value['option_votes'] / $percentage_total) * 100);
            $data[$key]['all']            = $all;
        }
    }
    return $data;
}

function Wo_GetVotes($option_id) {
    global $wo, $sqlConnect;
    if (empty($option_id) || !is_numeric($option_id)) {
        return false;
    }
    $data         = array();
    $option_id    = Wo_Secure($option_id);
    $query_one    = "SELECT COUNT(id) as count FROM " . T_VOTES . " WHERE `option_id` = {$option_id}";
    $sql          = mysqli_query($sqlConnect, $query_one);
    $fetched_data = mysqli_fetch_assoc($sql);
    if (empty($fetched_data)) {
        return array();
    }
    return $fetched_data['count'];
}

function Wo_GetCustomPages() {
    global $sqlConnect;
    $data = array();
    $query_one     = "SELECT * FROM " . T_CUSTOM_PAGES . " ORDER BY `id` DESC";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    while ($fetched_data = mysqli_fetch_assoc($sql_query_one)) {
        $data[]  = Wo_GetCustomPage($fetched_data['page_name']);
    }
    return $data;
}

function Wo_GetCustomPage($page_name) {
    global $sqlConnect;
    if (empty($page_name)) {
        return false;
    }
    $data = array();
    $page_name       = Wo_Secure($page_name);
    $query_one     = "SELECT * FROM " . T_CUSTOM_PAGES . " WHERE `page_name` = '{$page_name}'";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    $fetched_data = mysqli_fetch_assoc($sql_query_one);
    return $fetched_data;
}

function Wo_RegisterNewPage($registration_data) {
    global $wo, $sqlConnect;
    if (empty($registration_data)) {
        return false;
    }
    $fields                          = '`' . implode('`, `', array_keys($registration_data)) . '`';
    $data                            = '\'' . implode('\', \'', $registration_data) . '\'';
    $query                           = mysqli_query($sqlConnect, "INSERT INTO " . T_CUSTOM_PAGES . " ({$fields}) VALUES ({$data})");
    if ($query) {
        return true;
    }
    return false;
}

function Wo_DeleteCustomPage($id) {
    global $wo, $sqlConnect;
    if ($wo['loggedin'] == false) {
        return false;
    }
    if (Wo_IsAdmin() === false) {
        return false;
    }
    $id = Wo_Secure($id);
    $query     = mysqli_query($sqlConnect, "DELETE FROM " . T_CUSTOM_PAGES . " WHERE `id` = {$id}");
    if ($query) {
        return true;
    }
    return false;
}

function Wo_UpdateCustomPageData($id, $update_data) {
    global $wo, $sqlConnect, $cache;
    if ($wo['loggedin'] == false) {
        return false;
    }
    if (empty($id) || !is_numeric($id) || $id < 0) {
        return false;
    }
    if (empty($update_data)) {
        return false;
    }
    $id = Wo_Secure($id);
    if (Wo_IsAdmin() === false) {
        return false;
    }
    $update = array();
    foreach ($update_data as $field => $data) {
        $update[] = '`' . $field . '` = \'' . Wo_Secure($data, 0) . '\'';
    }
    $impload   = implode(', ', $update);
    $query_one = "UPDATE " . T_CUSTOM_PAGES . " SET {$impload} WHERE `id` = {$id} ";
    $query     = mysqli_query($sqlConnect, $query_one);
    if ($query) {
        return true;
    }
    return false;
}


function Wo_GetReferrers($user_id = 0) {
    global $sqlConnect, $wo;
    if ($wo['loggedin'] == false) {
        return false;
    }
    if (empty($user_id)) {
        $user_id = Wo_Secure($wo['user']['user_id']);
    } else {
        $user_id = Wo_Secure($user_id);
    }
    $data = array();
    $query_one     = "SELECT * FROM " . T_USERS . " WHERE `referrer` = '{$user_id}' ORDER BY `user_id` DESC";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    while ($fetched_data = mysqli_fetch_assoc($sql_query_one)) {
        $data[]  = Wo_UserData($fetched_data['user_id']);
    }
    return $data;
}

function Wo_UpdateBalance($user_id = 0, $balance = 0, $type = '+') {
    global $wo, $sqlConnect;
    if (empty($user_id) || !is_numeric($user_id) || $user_id < 0) {
        return false;
    }
    if (empty($balance)) {
        return false;
    }
    $user_id = Wo_Secure($user_id);
    $balance = Wo_Secure($balance);
    $user_data = Wo_UserData($user_id);
    if ($type == '+') {
        $balance = ($user_data['balance'] + $balance);
    } else {
        $balance = ($user_data['balance'] - $balance);
    }
    $query_one = "UPDATE " . T_USERS . " SET `balance` = '{$balance}' WHERE `user_id` = {$user_id} ";
    $query     = mysqli_query($sqlConnect, $query_one);
    if ($query) {
        return true;
    }
    return false;
}
function Wo_RequestNewPayment($user_id = 0, $amount = 0) {
    global $sqlConnect;
    if (empty($user_id)) {
        return false;
    }
    if (empty($amount)) {
        return false;
    }
    $user_id  = Wo_Secure($user_id);
    $amount   = Wo_Secure($amount);
    if (Wo_IsUserPaymentRequested($user_id) === true) {
        return false;
    }
    $user_data = Wo_UserData($user_id);
    $full_amount = Wo_Secure($user_data['balance']);
    $time = time();
    $query_text = "INSERT INTO " . T_A_REQUESTS . " (`user_id`, `amount`, `full_amount`, `time`) VALUES ('$user_id', '$amount', '$full_amount', '$time')";
    $query = mysqli_query($sqlConnect, $query_text);
    if ($query) {
        return true;
    }
    return false;
}
function Wo_IsUserPaymentRequested($user_id = 0) {
    global $sqlConnect;
    if (empty($user_id)) {
        return false;
    }
    $user_id  = Wo_Secure($user_id);
    $query    = mysqli_query($sqlConnect, "SELECT COUNT(`user_id`) FROM " . T_A_REQUESTS . " WHERE `user_id` = '{$user_id}' AND status = '0'");
    return (Wo_Sql_Result($query, 0) == 1) ? true : false;
}

function Wo_GetPaymentsHistory($user_id = 0) {
    global $sqlConnect;
    if (empty($user_id)) {
        return false;
    }
    $user_id  = Wo_Secure($user_id);
    $data = array();
    $query_one     = "SELECT `id` FROM " . T_A_REQUESTS . " WHERE `user_id` = '{$user_id}' ORDER BY `id` DESC";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    while ($fetched_data = mysqli_fetch_assoc($sql_query_one)) {
        $data[]  = Wo_GetPaymentHistory($fetched_data['id']);
    }
    return $data;
}
function Wo_GetAllPaymentsHistory($type = 0) {
    global $sqlConnect;
    $type  = Wo_Secure($type);
    $data = array();
    $where = "";
    if ($type != 'all') {
        $where = "WHERE `status` = '{$type}'";
    }
    $query_one     = "SELECT * FROM " . T_A_REQUESTS . " {$where} ORDER BY `id` DESC";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    while ($fetched_data = mysqli_fetch_assoc($sql_query_one)) {
        $data[]  = Wo_GetPaymentHistory($fetched_data['id']);
    }
    return $data;
}
function Wo_CountPaymentHistory($id) {
    global $sqlConnect;
    $data = array();
    $id       = Wo_Secure($id);
    $query_one     = "SELECT COUNT(`id`) as count FROM " . T_A_REQUESTS . " WHERE `status` = '{$id}'";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    $fetched_data = mysqli_fetch_assoc($sql_query_one);
    return $fetched_data['count'];
}

function Wo_GetPaymentHistory($id) {
    global $sqlConnect, $wo;
    if (empty($id)) {
        return false;
    }
    $data = array();
    $id       = Wo_Secure($id);
    $query_one     = "SELECT * FROM " . T_A_REQUESTS . " WHERE `id` = '{$id}'";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    $fetched_data = mysqli_fetch_assoc($sql_query_one);
    $fetched_data['user']  = Wo_UserData($fetched_data['user_id']);
    $fetched_data['total_refs']  = Wo_CountRefs($fetched_data['user_id']);
    $fetched_data['time_text'] = Wo_Time_Elapsed_String($fetched_data['time']);
    $fetched_data['callback_url'] = $wo['config']['site_url'] . '/' . 'requests.php?f=admincp&paid_user_id=' . $fetched_data['user_id'] . '&paid_ref_id=' . $fetched_data['id'];
    return $fetched_data;
}

function Wo_CountRefs($user_id = 0) {
    global $sqlConnect;
    $data = array();
    $user_id       = Wo_Secure($user_id);
    $query_one     = "SELECT COUNT(`user_id`) as count FROM " . T_USERS . " WHERE `referrer` = '{$user_id}'";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    $fetched_data = mysqli_fetch_assoc($sql_query_one);
    return $fetched_data['count'];
}

// Blog SYSYTEM

function Wo_InsertBlog($registration_data = array()) {
    global $sqlConnect, $wo;    
    if ($wo['loggedin'] == false) {
        return false;
    }
    $fields       = '`' . implode('`, `', array_keys($registration_data)) . '`';
    $data         = '\'' . implode('\', \'', $registration_data) . '\'';
    $query        = mysqli_query($sqlConnect, "INSERT INTO " . T_BLOG . " ({$fields}) VALUES ({$data})");
    if ($query) {
        return mysqli_insert_id($sqlConnect);
    }
    return false;
}

function Wo_IsBlogOwner($blog_id = 0, $user_id = 0) {
    global $sqlConnect, $wo;
    if (empty($blog_id)) {
        return false;
    }
    if (empty($user_id)) {
        $user_id = $wo['user']['user_id'];
    }
    $user_id = Wo_Secure($user_id);
    $blog_id = Wo_Secure($blog_id);
    $query = mysqli_query($sqlConnect, "SELECT COUNT(`id`) as count FROM " . T_BLOG . " WHERE `user` = {$user_id} AND `id` = $blog_id");
    $query_ = mysqli_fetch_assoc($query);
    return ($query_['count'] > 0) ? true : false;
}
function Wo_UpdateBlog($id = 0, $update_data = array()) {
    global $sqlConnect, $wo;
    $update = array();
    if ($wo['loggedin'] == false) {
        return false;
    }
    if (empty($update_data)) {
        return false;
    }
    if (empty($id)) {
        return false;
    }
    $id = Wo_Secure($id);
    if (Wo_IsBlogOwner($id) === false) {
        return false;
    }
    foreach ($update_data as $field => $data) {
        $update[] = '`' . $field . '` = \'' . Wo_Secure($data, 0) . '\'';
    }
    $impload   = implode(', ', $update);
    $query_one = "UPDATE " . T_BLOG . " SET {$impload} WHERE `id` = {$id} ";
    $query     = mysqli_query($sqlConnect, $query_one);
    return $query;
}

function Wo_GetMyBlogs($user = 0, $offset = 0){
    global $sqlConnect,$wo;
    $data  = array();
    if ($wo['loggedin'] == false) {
        return false;
    }
    $after_blogs = '';
    if ($offset > 0) {
        $after_blogs = " AND `id` < {$offset} AND `id` <> {$offset} ";
    }
    $user = Wo_Secure($user);
    $offset = Wo_Secure($offset);
    $query = mysqli_query($sqlConnect, "SELECT * FROM  " . T_BLOG . " WHERE `user` = '$user' {$after_blogs} ORDER BY `id` DESC LIMIT 10");
    while ($fetched_data = mysqli_fetch_assoc($query)) {
        $data[] = Wo_GetArticle($fetched_data['id']);
    }
    return $data;
}

function Wo_GetBlogs($args = array()){
    global $sqlConnect, $wo;
    $options = array("category" => false, "offset" => 0, "limit" => 10, 'order_by' => 'DESC', 'id' => '0');
    $args = array_merge($options, $args);
    $offset   = Wo_Secure($args['offset']);
    $limit    = Wo_Secure($args['limit']);
    $category = Wo_Secure($args['category']);
    $order_by = Wo_Secure($args['order_by']);
    $id = Wo_Secure($args['id']);
    $query_one = 'WHERE posted > 0';
    if ($offset > 0) {
        $query_one .= " AND `id` < {$offset} AND `id` <> {$offset} ";
    }
    if ($category) {
        $query_one .= " AND `category` = '$category' ";
    }
    if ($category && $offset > 0) {
        $query_one .= " AND `category` = '$category' AND `id` < {$offset} AND `id` <> {$offset} ";
    }
    if (!empty($id)) {
        $query_one .= " AND `id` <> '$id' ";
    }
    $order_by_text = '';
    if ($order_by == 'DESC') {
        $order_by_text = '`id` DESC';
    } else if ($order_by == 'RAND') {
        $order_by_text = 'RAND()';
    }
    $query_two = "SELECT * FROM  " . T_BLOG . " {$query_one} ORDER BY $order_by_text LIMIT {$limit} ";
    $query = mysqli_query($sqlConnect, $query_two);
    $data = array();
    while ($fetched_data = mysqli_fetch_assoc($query)) {
        $data[] = Wo_GetArticle($fetched_data['id']);
    }
    return $data;
}

function Wo_DeleteMyBlog($id = 0){
    global $sqlConnect, $wo;
    if ($wo['loggedin'] == false) {
        return false;
    }
    if (empty($id)) {
        return false;
    }
    if (Wo_IsBlogOwner($id) === false) {
        if (Wo_IsAdmin() === false) {
            return false;
        }
    }
    $id = Wo_Secure($id);
    $query_one = "DELETE FROM " . T_BLOG . " WHERE `id` = '$id'";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    if ($sql_query_one) {
        $sql_query_two = mysqli_query($sqlConnect, "SELECT `id` FROM " . T_POSTS . " WHERE `blog_id` = '$id' LIMIT 1");
        $mysqli = mysqli_fetch_assoc($sql_query_two);
        $delete_post = Wo_DeletePost($mysqli['id']);
    }
    return $sql_query_one;
}

function Wo_GetArticle($id = 0){
    global $sqlConnect, $wo;
    if (empty($id)) {
        return false;
    }
    $id = Wo_Secure($id);
    $sql_query_one = mysqli_query($sqlConnect, "SELECT * FROM " . T_BLOG . " WHERE `id` = '$id'");
    $fetched_data = mysqli_fetch_assoc($sql_query_one);

    if (!empty($fetched_data)) {
        $fetched_data['author'] = Wo_UserData($fetched_data['user']);
        $fetched_data['thumbnail'] = Wo_GetMedia($fetched_data['thumbnail']);
        $fetched_data['tags_array'] = @explode(',', $fetched_data['tags']);
        $fetched_data['url'] = Wo_SeoLink('index.php?link1=read-blog&id=' . $fetched_data['id'] . '_' . Wo_SlugPost($fetched_data['title']));
        $fetched_data['author'] = Wo_UserData($fetched_data['user']);
        $fetched_data['category_link'] = Wo_SeoLink('index.php?link1=blog-category&id=' . $fetched_data['category']);
        $fetched_data['category_name'] = '';
        $fetched_data['is_post_admin'] = false;
        if ($wo['loggedin'] == true) {
            $fetched_data['is_post_admin'] = ($fetched_data['user'] == $wo['user']['id']) ? true: false;
        }
        if (!empty($wo['page_categories'][$fetched_data['category']])) {
            $fetched_data['category_name'] = $wo['page_categories'][$fetched_data['category']];
        }
    }
    return $fetched_data;
}

function Wo_SearchBlogs($args = array()){
    global $sqlConnect,$wo;
    if ($wo['loggedin'] == false) {
        return false;
    }
    $options = array("category" => false, "keyword" => false);
    $args = array_merge($options,$args);
    $category = Wo_Secure($args['category']);
    $keyword = Wo_Secure($args['keyword']);
    if (!$keyword || !$category) {
        return false;
    }
    $query_two = "SELECT * FROM " . T_BLOG . " WHERE  `category` = '$category' AND  `title` LIKE '%$keyword%' OR `description` LIKE '%$keyword%' ";
    $query = mysqli_query($sqlConnect, $query_two);
    $data =  array();
    while ($fetched_data = mysqli_fetch_assoc($query)) {
        $data[] = Wo_GetArticle($fetched_data['id']);
    }
    return $data;
}