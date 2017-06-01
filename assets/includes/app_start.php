<?php
error_reporting(0);
require_once('config.php');
require_once('assets/includes/phpMailer_config.php');

// require 'assets/import/ffmpeg-class/vendor/autoload.php';
$wo         = array();
// Connect to SQL Server
$sqlConnect = $wo['sqlConnect'] = mysqli_connect($sql_db_host, $sql_db_user, $sql_db_pass, $sql_db_name, 3306);

// Handling Server Errors
$ServerErrors = array();
if (mysqli_connect_errno()) {
    $ServerErrors[] = "Failed to connect to MySQL: " . mysqli_connect_error();
}
if (!function_exists('curl_init')) {
    $ServerErrors[] = "PHP CURL is NOT installed on your web server !";
}
if (!extension_loaded('gd') && !function_exists('gd_info')) {
    $ServerErrors[] = "PHP GD library is NOT installed on your web server !";
}
if (!extension_loaded('zip')) {
    $ServerErrors[] = "ZipArchive extension is NOT installed on your web server !";
}
if (!version_compare(PHP_VERSION, '5.5.0', '>=')) {
    $ServerErrors[] = "Required PHP_VERSION >= 5.5.0 , Your PHP_VERSION is : " . PHP_VERSION . "\n";
}
if (!function_exists('exif_read_data')) {
    // $ServerErrors[] = "Exif reader extension is NOT installed on your web server !";
}
$query = mysqli_query($sqlConnect, "SET NAMES utf8");

if (isset($ServerErrors) && !empty($ServerErrors)) {
    foreach ($ServerErrors as $Error) {
        echo "<h3>" . $Error . "</h3>";
    }
    die();
}

$baned_ips = Wo_GetBanned('user');

if (in_array($_SERVER["REMOTE_ADDR"], $baned_ips)) {
    exit();
}

$config = Wo_GetConfig();

// Config Url
$config['theme_url'] = $site_url . '/themes/' . $config['theme'];

$config['site_url'] = $site_url;
$s3_site_url        = 'https://test.s3.amazonaws.com';
if (!empty($config['bucket_name'])) {
    $s3_site_url = "https://{bucket}.s3.amazonaws.com";
    $s3_site_url = str_replace('{bucket}', $config['bucket_name'], $s3_site_url);
}
$config['s3_site_url'] = $s3_site_url;

$wo['config']     = $config;
$wo['site_pages'] = array(
    'home',
    'welcome',
    'activate',
    'search',
    'timeline',
    'pages',
    'page',
    'groups',
    'group',
    'create-group',
    'group-setting',
    'create-page',
    'setting',
    'page-setting',
    'messages',
    'logout',
    '404',
    'post',
    'games',
    'admincp',
    'saved-posts',
    'hashtag',
    'terms',
    'contact-us',
    'albums',
    'album',
    'game',
    'go_pro',
    'upgraded',
    'oops',
    'user_activation',
    'boosted-pages',
    'boosted-posts',
    'video-call',
    'read-blog',
    'blog',
    'My-Blogs',
    'edit-blog',
    'create_blog'
);

$wo['script_version'] = '1.4.4.5';

$http_header = 'http://';
if (!empty($_SERVER['HTTPS'])) {
    $http_header = 'https://';
}

$wo['actual_link']   = $http_header . $_SERVER['HTTP_HOST'] . urlencode($_SERVER['REQUEST_URI']);
// Define Cache Vireble
$cache               = new Cache();
$wo['purchase_code'] = '';
if (!empty($purchase_code)) {
    $wo['purchase_code'] = $purchase_code;
}
// Login With Url
$wo['facebookLoginUrl']   = $config['site_url'] . '/login-with.php?provider=Facebook';
$wo['twitterLoginUrl']    = $config['site_url'] . '/login-with.php?provider=Twitter';
$wo['googleLoginUrl']     = $config['site_url'] . '/login-with.php?provider=Google';
$wo['linkedInLoginUrl']   = $config['site_url'] . '/login-with.php?provider=LinkedIn';
$wo['VkontakteLoginUrl']  = $config['site_url'] . '/login-with.php?provider=Vkontakte';
$wo['instagramLoginUrl']  = $config['site_url'] . '/login-with.php?provider=Instagram';
// Defualt User Pictures 
$wo['userDefaultAvatar']  = 'upload/photos/d-avatar.jpg';
$wo['userDefaultCover']   = 'upload/photos/d-cover.jpg';
$wo['pageDefaultAvatar']  = 'upload/photos/d-page.jpg';
$wo['groupDefaultAvatar'] = 'upload/photos/d-group.jpg';

// Get LoggedIn User Data
$wo['loggedin'] = false;
$langs          = Wo_LangsNamesFromDB();
if (Wo_IsLogged() == true) {
    $session_id         = (!empty($_SESSION['user_id'])) ? $_SESSION['user_id'] : $_COOKIE['user_id'];
    $wo['user_session'] = Wo_GetUserFromSessionID($session_id);
    $wo['user']         = Wo_UserData($wo['user_session']);
    if (!empty($wo['user']['language'])) {
        if (in_array($wo['user']['language'], $langs)) {
            $_SESSION['lang'] = $wo['user']['language'];
        }
    }
    if ($wo['user']['user_id'] < 0 || empty($wo['user']['user_id']) || !is_numeric($wo['user']['user_id']) || Wo_UserActive($wo['user']['username']) === false) {
        header("Location: " . Wo_SeoLink('index.php?link1=logout'));
    }
    $wo['loggedin'] = true;
}
if (!empty($_POST['user_id']) && !empty($_POST['s'])) {
    $application = 'windows';
    if (!empty($_GET['application'])) {
        if ($_GET['application'] == 'phone') {
            $application = Wo_Secure($_GET['application']);
        }
    }
    if ($application == 'windows') {
        $_POST['s'] = md5($_POST['s']);
    }
    $s                = Wo_Secure($_POST['s']);
    $user_id          = Wo_Secure($_POST['user_id']);
    $check_if_session = Wo_CheckUserSessionID($user_id, $s, $application);
    if ($check_if_session === true) {
        $wo['user'] = Wo_UserData($user_id);
        if ($wo['user']['user_id'] < 0 || empty($wo['user']['user_id']) || !is_numeric($wo['user']['user_id']) || Wo_UserActive($wo['user']['username']) === false) {
            $json_error_data = array(
                'api_status' => '400',
                'api_text' => 'failed',
                'errors' => array(
                    'error_id' => '7',
                    'error_text' => 'User id is wrong.'
                )
            );
            header("Content-type: application/json");
            echo json_encode($json_error_data, JSON_PRETTY_PRINT);
            exit();
        }
        $wo['loggedin'] = true;
    } else {
        $json_error_data = array(
            'api_status' => '400',
            'api_text' => 'failed',
            'errors' => array(
                'error_id' => '6',
                'error_text' => 'Session id is wrong.'
            )
        );
        header("Content-type: application/json");
        echo json_encode($json_error_data, JSON_PRETTY_PRINT);
        exit();
    }
}
// Language Function
if (isset($_GET['lang']) AND !empty($_GET['lang'])) {
    $lang_name = Wo_Secure(strtolower($_GET['lang']));
    if (in_array($lang_name, $langs)) {
        $_SESSION['lang'] = $lang_name;
        if ($wo['loggedin'] == true) {
            mysqli_query($sqlConnect, "UPDATE " . T_USERS . " SET `language` = '" . $lang_name . "' WHERE `user_id` = " . Wo_Secure($wo['user']['user_id']));
        }
    }
}
if (empty($_SESSION['lang'])) {
    $_SESSION['lang'] = $wo['config']['defualtLang'];
}
$wo['language']      = $_SESSION['lang'];
$wo['language_type'] = 'ltr';
// Add rtl languages here.
$rtl_langs           = array(
    'arabic'
);
// checking if corrent language is rtl.
foreach ($rtl_langs as $lang) {
    if ($wo['language'] == strtolower($lang)) {
        $wo['language_type'] = 'rtl';
    }
}
// Icons Virables
$error_icon   = '<i class="fa fa-exclamation-circle"></i> ';
$success_icon = '<i class="fa fa-check"></i> ';
// Include Language File
$wo['lang']   = Wo_LangsFromDB($wo['language']);
if (file_exists('assets/languages/extra/' . $wo['language'] . '.php')) {
    require 'assets/languages/extra/' . $wo['language'] . '.php';
}
if (empty($wo['lang'])) {
    $wo['lang'] = Wo_LangsFromDB();
}
$wo['second_post_button_icon']  = ($config['second_post_button'] == 'wonder') ? 'exclamation-circle' : 'thumbs-down';
$wo['second_post_button_text']  = ($config['second_post_button'] == 'wonder') ? $wo['lang']['wonder'] : $wo['lang']['dislike'];
$wo['second_post_button_texts'] = ($config['second_post_button'] == 'wonder') ? $wo['lang']['wonders'] : $wo['lang']['dislikes'];

$wo['marker'] = '?';
if ($wo['config']['seoLink'] == 0) {
    $wo['marker'] = '&';
}

$wo['feelingIcons'] = array(
    'happy' => 'smile',
    'loved' => 'heart-eyes',
    'sad' => 'disappointed',
    'so_sad' => 'sob',
    'angry' => 'angry',
    'confused' => 'confused',
    'smirk' => 'smirk',
    'broke' => 'broken-heart',
    'expressionless' => 'expressionless',
    'cool' => 'sunglasses',
    'funny' => 'joy',
    'tired' => 'tired-face',
    'lovely' => 'heart',
    'blessed' => 'innocent',
    'shocked' => 'scream',
    'sleepy' => 'sleeping',
    'pretty' => 'relaxed',
    'bored' => 'unamused'
);
$emo                = array(
    ':)' => 'smile',
    '(&lt;' => 'joy',
    '**)' => 'relaxed',
    ':p' => 'stuck-out-tongue-winking-eye',
    ':_p' => 'stuck-out-tongue',
    'B)' => 'sunglasses',
    ';)' => 'wink',
    ':D' => 'grin',
    '/_)' => 'smirk',
    '0)' => 'innocent',
    ':_(' => 'cry',
    ':__(' => 'sob',
    ':(' => 'disappointed',
    ':*' => 'kissing-heart',
    '&lt;3' => 'heart',
    '&lt;/3' => 'broken-heart',
    '*_*' => 'heart-eyes',
    '&lt;5' => 'star',
    ':o' => 'open-mouth',
    ':0' => 'scream',
    'o(' => 'anguished',
    '-_(' => 'unamused',
    'x(' => 'angry',
    'X(' => 'rage',
    '-_-' => 'expressionless',
    ':-/' => 'confused',
    ':|' => 'neutral-face',
    '!_' => 'exclamation',
    ':|' => 'neutral-face'
);



$wo['emo'] = $emo;

$wo['profile_picture_width_crop']    = 150;
$wo['profile_picture_height_crop']   = 150;
$wo['profile_picture_image_quality'] = 70;

$wo['redirect'] = 0;

$wo['footer_pages'] = array(
    'terms',
    'oops',
    'messages',
    'start_up',
    '404',
    'search',
    'admin',
    'user_activation',
    'upgraded',
    'go_pro',
    'video',
    'custom_page',
    'products',
    'read-blog',
    'blog',
    'My-Blogs',
    'edit-blog',
    'create_blog'
);

$wo['css_file_header'] = "
<style>
.navbar-default {
   height:45px !important;
   display: block !important;
   visibility: visible !important;
}
</style>
";

$star_package_duration   = 604800; // week in seconds
$hot_package_duration    = 2629743; // month in seconds
$ultima_package_duration = 31556926; // year in seconds
$vip_package_duration    = 0; // life time package

require_once('assets/includes/paypal_config.php');
require_once('assets/import/twilio-php-master/Services/Twilio.php');
require_once('assets/includes/stripe_config.php');
require_once('assets/import/s3/aws-autoloader.php');
?>