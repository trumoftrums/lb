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
    'video-call'
);

$wo['script_version'] = '1.4.4.4';

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
$emo = array(
    ':smile:' => 'smile',
    ':laughing:' => 'laughing',
    ':blush:' => 'blush',
    ':smiley:' => 'smiley',
    ':relaxed:' => 'relaxed',
    ':smirk:' => 'smirk',
    ':hearteyes:' => 'heart-eyes',
    ':kissingheart:' => 'kissing-heart', 
    ':kissingclosedeyes:' => 'kissing-closed-eyes',
    ':flushed:' => 'flushed',
    ':relieved:' => 'relieved',
    ':satisfied:' => 'satisfied',
    ':grin:' => 'grin',
    ':wink:' => 'wink',
    ':stuckouttonguewinkingeye:' => 'stuck-out-tongue-winking-eye',
    ':stuckouttongueclosedeyes:' => 'stuck-out-tongue-closed-eyes',
    ':grinning:' => 'grinning',
    ':kissing:' => 'kissing',
    ':kissingsmilingeyes:' => 'kissing-smiling-eyes',
    ':stuckouttongue:' => 'stuck-out-tongue',
    ':sleeping:' => 'sleeping',
    ':worried:' => 'worried',
    ':frowning:' => 'frowning',
    ':anguished:' => 'anguished', 
    ':openmouth:' => 'open-mouth',
    ':grimacing:' => 'grimacing',
    ':confused:' => 'confused',
    ':hushed:' => 'hushed',
    ':expressionless:' => 'expressionless',
	':unamused:' => 'unamused',
    ':sweatsmile:' => 'sweat-smile',
    ':sweat:' => 'sweat',
    ':weary:' => 'weary',
    ':pensive:' => 'pensive',
    ':disappointed:' => 'disappointed',
    ':confounded:' => 'confounded',
    ':fearful:' => 'fearful', 
    ':cold:' => 'cold-sweat',
    ':persevere:' => 'persevere',
    ':cry:' => 'cry',
    ':sob:' => 'sob',
    ':joy:' => 'joy',
    ':astonished:' => 'astonished',
    ':scream:' => 'scream',
    ':tiredface:' => 'tired-face',
    ':angry:' => 'angry',
    ':rage:' => 'rage',
    ':triumph:' => 'triumph',
    ':sleepy:' => 'sleepy',
    ':yum:' => 'yum',
    ':mask:' => 'mask',
    ':sunglasses:' => 'sunglasses',
    ':dizzyface:' => 'dizzy-face', 
    ':imp:' => 'imp',
    ':smilingimp:' => 'smiling-imp',
    ':neutralface:' => 'neutral-face',
    ':nomouth:' => 'no-mouth',
    ':innocent:' => 'innocent',	
	':alien:' => 'alien',
    ':yellowheart:' => 'yellow-heart',
    ':blueheart:' => 'blue-heart',
    ':purpleheart:' => 'purple-heart',
    ':heart:' => 'heart',
    ':greenheart:' => 'green-heart',
    ':brokenheart:' => 'broken-heart',
    ':heartbeat:' => 'heartbeat', 
    ':heartpulse:' => 'heartpulse',
    ':twohearts:' => 'two-hearts',
    ':revolvinghearts:' => 'revolving-hearts',
    ':cupid:' => 'cupid',
    ':sparklingheart:' => 'sparkling-heart',
    ':sparkles:' => 'sparkles',
    ':star:' => 'star',
    ':star2:' => 'star2',
    ':dizzy:' => 'dizzy',
    ':boom:' => 'boom',
    ':anger:' => 'anger',
    ':exclamation:' => 'exclamation',
    ':question:' => 'question',
    ':greyexclamation:' => 'grey-exclamation',
    ':greyquestion:' => 'grey-question',
    ':zzz:' => 'zzz', 
    ':dash:' => 'dash',
    ':sweatdrops:' => 'sweat-drops',
    ':notes:' => 'notes',
    ':musicalnote:' => 'musical-note',
    ':fire:' => 'fire',	
	':poop:' => 'poop',
    ':thumbsup:' => 'thumbsup',
    ':thumbsdown:' => 'thumbsdown',
    ':okhand:' => 'ok-hand',
    ':punch:' => 'punch',
    ':fist:' => 'fist',
    ':v:' => 'v',
    ':wave:' => 'wave', 
    ':hand:' => 'hand',
    ':openhands:' => 'open-hands',
    ':pointup:' => 'point-up',
    ':pointdown:' => 'point-down',
    ':pointleft:' => 'point-left',
    ':pointright:' => 'point-right',
    ':raisedhands:' => 'raised-hands',
    ':pray:' => 'pray',
    ':pointup2:' => 'point-up-2',
    ':clap:' => 'clap',
    ':muscle:' => 'muscle',
    ':walking:' => 'walking',
    ':runner:' => 'runner',
    ':couple:' => 'couple',
    ':family:' => 'grey-question',
    ':twomenholdinghands:' => 'two-men-holding-hands', 
    ':twowomenholdinghands:' => 'two-women-holding-hands',
    ':dancer:' => 'dancer',
    ':dancers:' => 'dancers',
    ':okwoman:' => 'ok-woman',
    ':nogood:' => 'no-good',	
	':informationdeskperson:' => 'information-desk-person',
    ':raisedhand:' => 'raised-hand',
    ':bridewithveil:' => 'bride-with-veil',
    ':personwithpoutingface:' => 'person-with-pouting-face',
    ':personfrowning:' => 'person-frowning',
    ':bow:' => 'bow',
    ':couplekiss:' => 'couplekiss',
    ':couplewithheart:' => 'couple-with-heart', 
    ':massage:' => 'massage',
    ':haircut:' => 'haircut',
    ':nailcare:' => 'nail-care',
    ':boy:' => 'boy',
    ':girl:' => 'girl',
    ':woman:' => 'woman',
    ':man:' => 'man',
    ':baby:' => 'baby',
    ':olderwoman:' => 'older-woman',
    ':olderman:' => 'older-man',
    ':personwithblondhair:' => 'person-with-blond-hair',
    ':manwithguapimao:' => 'man-with-gua-pi-mao',
    ':manwithturban:' => 'man-with-turban',
    ':constructionworker:' => 'construction-worker',
    ':cop:' => 'cop',
    ':angel:' => 'angel', 
    ':princess:' => 'princess',
    ':smileycat:' => 'smiley-cat',
    ':smilecat:' => 'smile-cat',
    ':hearteyescat:' => 'heart-eyes-cat',
    ':kissingcat:' => 'kissing-cat',	
	':smirkcat:' => 'smirk-cat',
    ':screamcat:' => 'scream-cat',
    ':cryingcatface:' => 'crying-cat-face',
    ':joycat:' => 'joy-cat',
    ':poutingcat:' => 'pouting-cat',
    ':japaneseogre:' => 'japanese-ogre',
    ':japanesegoblin:' => 'japanese-goblin',
    ':seenoevil:' => 'see-no-evil', 
    ':hearnoevil:' => 'hear-no-evil',
    ':speaknoevil:' => 'speak-no-evil',
    ':guardsman:' => 'guardsman',
    ':skull:' => 'skull',
    ':feet:' => 'feet',
    ':lips:' => 'lips',
    ':kiss:' => 'kiss',
    ':droplet:' => 'droplet',
    ':ear:' => 'ear',
    ':eyes:' => 'eyes',
    ':nose:' => 'nose',
    ':tongue:' => 'tongue',
    ':loveletter:' => 'love-letter',
    ':bustinsilhouette:' => 'bust-in-silhouette',
    ':bustsinsilhouette:' => 'busts-in-silhouette',
    ':speechballoon:' => 'speech-balloon', 
    ':thoughtballoon:' => 'thought-balloon',
    ':sunny:' => 'sunny',
    ':umbrella:' => 'umbrella',
    ':cloud:' => 'cloud',
    ':snowflake:' => 'snowflake',	
	':snowman:' => 'snowman',
    ':zap:' => 'zap',
    ':cyclone:' => 'cyclone',
    ':foggy:' => 'foggy',
    ':ocean:' => 'ocean',
    ':cat:' => 'cat',
    ':dog:' => 'dog',
    ':mouse:' => 'mouse', 
    ':hamster:' => 'hamster',
    ':rabbit:' => 'rabbit',
    ':wolf:' => 'wolf',
    ':frog:' => 'frog',
    ':tiger:' => 'tiger',
    ':koala:' => 'koala',
    ':bear:' => 'bear',
    ':pig:' => 'pig',
    ':pignose:' => 'pig-nose',
    ':cow:' => 'cow',
    ':boar:' => 'boar',
    ':monkeyface:' => 'monkey-face',
    ':monkey:' => 'monkey',
    ':horse:' => 'horse',
    ':racehorse:' => 'racehorse',
    ':camel:' => 'camel', 
    ':sheep:' => 'sheep',
    ':elephant:' => 'elephant',
    ':pandaface:' => 'panda-face',
    ':snake:' => 'snake',
    ':bird:' => 'bird',	
	':babychick:' => 'baby-chick',
    ':hatchedchick:' => 'hatched-chick',
    ':hatchingchick:' => 'hatching-chick',
    ':chicken:' => 'chicken',
    ':penguin:' => 'penguin',
    ':turtle:' => 'turtle',
    ':bug:' => 'bug',
    ':honeybee:' => 'honeybee', 
    ':ant:' => 'ant',
    ':beetle:' => 'beetle',
    ':snail:' => 'snail',
    ':octopus:' => 'octopus',
    ':tropicalfish:' => 'tropical-fish',
    ':fish:' => 'fish',
    ':whale:' => 'whale',
    ':whale2:' => 'whale2',
    ':dolphin:' => 'dolphin',
    ':cow2:' => 'cow2',
    ':ram:' => 'ram',
    ':rat:' => 'rat',
    ':waterbuffalo:' => 'water-buffalo',
    ':tiger2:' => 'tiger2',
    ':rabbit2:' => 'rabbit2',
    ':dragon:' => 'dragon', 
    ':goat:' => 'goat',
    ':rooster:' => 'rooster',
    ':dog2:' => 'dog2',
    ':pig2:' => 'pig2',
    ':mouse2:' => 'mouse2',
	':ox:' => 'ox',
    ':dragonface:' => 'dragon-face',
    ':blowfish:' => 'blowfish',
    ':crocodile:' => 'crocodile',
    ':dromedarycamel:' => 'dromedary-camel',
    ':leopard:' => 'leopard',
    ':cat2:' => 'cat2',
    ':poodle:' => 'poodle', 
    ':pawprints:' => 'paw-prints',
    ':bouquet:' => 'bouquet',
    ':cherryblossom:' => 'cherry-blossom',
    ':tulip:' => 'tulip',
    ':tropicalfish:' => 'tropical-fish',
    ':fourleafclover:' => 'four-leaf-clover',
    ':rose:' => 'rose',
    ':sunflower:' => 'sunflower',
    ':hibiscus:' => 'hibiscus',
    ':mapleleaf:' => 'maple-leaf',
    ':leaves:' => 'leaves',
    ':fallenleaf:' => 'fallen-leaf',
    ':herb:' => 'herb',
    ':mushroom:' => 'mushroom',
    ':cactus:' => 'cactus',
    ':palmtree:' => 'palm-tree', 
    ':evergreentree:' => 'evergreen-tree',
    ':deciduoustree:' => 'deciduous-tree',
    ':chestnut:' => 'chestnut',
    ':seedling:' => 'seedling',
    ':blossom:' => 'blossom',	
	':earofrice:' => 'ear-of-rice',
    ':shell:' => 'shell',
    ':globewithmeridians:' => 'globe-with-meridians',
    ':sunwithface:' => 'sun-with-face',
    ':fullmoonwithface:' => 'full-moon-with-face',
    ':newmoonwithface:' => 'new-moon-with-face',
    ':newmoon:' => 'new-moon',
    ':waxingcrescentmoon:' => 'waxing-crescent-moon', 
    ':firstquartermoon:' => 'first-quarter-moon',
    ':waxinggibbousmoon:' => 'waxing-gibbous-moon',
    ':fullmoon:' => 'full-moon',
    ':waninggibbousmoon:' => 'waning-gibbous-moon',
    ':lastquartermoon:' => 'last-quarter-moon',
    ':waningcrescentmoon:' => 'waning-crescent-moon',
    ':lastquartermoonwithface:' => 'last-quarter-moon-with-face',
    ':firstquartermoonwithface:' => 'first-quarter-moon-with-face',
    ':moon:' => 'moon',
    ':earthafrica:' => 'earth-africa',
    ':earthamericas:' => 'earth-americas',
    ':earthasia:' => 'earth-asia',
    ':volcano:' => 'volcano',
    ':milkyway:' => 'milky-way',
    ':partlysunny:' => 'partly-sunny',
    ':bamboo:' => 'bamboo', 
    ':giftheart:' => 'gift-heart',
    ':dolls:' => 'dolls',
    ':schoolsatchel:' => 'school-satchel',
    ':mortarboard:' => 'mortar-board',
    ':flags:' => 'flags',	
	':fireworks:' => 'fireworks',
    ':sparkler:' => 'sparkler',
    ':windchime:' => 'wind-chime',
    ':ricescene:' => 'rice-scene',
    ':jackolantern:' => 'jack-o-lantern',
    ':ghost:' => 'ghost',
    ':santa:' => 'santa',
    ':8ball:' => '8ball', 
    ':alarmclock:' => 'alarm-clock',
    ':apple:' => 'apple',
    ':art:' => 'art',
    ':babybottle:' => 'baby-bottle',
    ':balloon:' => 'balloon',
    ':banana:' => 'banana',
    ':barchart:' => 'bar-chart',
    ':baseball:' => 'baseball',
    ':basketball:' => 'basketball',
    ':bath:' => 'bath',
    ':bathtub:' => 'bathtub',
    ':battery:' => 'battery',
    ':beer:' => 'beer',
    ':beers:' => 'beers',
    ':bell:' => 'bell',
    ':bento:' => 'bento', 
    ':bicyclist:' => 'bicyclist',
    ':bikini:' => 'bikini',
    ':birthday:' => 'birthday',
    ':blackjoker:' => 'black-joker',
    ':blacknib:' => 'black-nib',	
	':bluebook:' => 'blue-book',
    ':bomb:' => 'bomb',
    ':bookmark:' => 'bookmark',
    ':bookmarktabs:' => 'bookmark-tabs',
    ':books:' => 'books',
    ':boot:' => 'boot',
    ':bowling:' => 'bowling',
    ':bread:' => 'bread', 
    ':briefcase:' => 'briefcase',
    ':bulb:' => 'bulb',
    ':cake:' => 'cake',
    ':calendar:' => 'calendar',
    ':calling:' => 'calling',
    ':camera:' => 'camera',
    ':candy:' => 'candy',
    ':cardindex:' => 'card-index',
    ':cd:' => 'cd',
    ':chartwithdownwardstrend:' => 'chart-with-downwards-trend',
    ':chartwithupwardstrend:' => 'chart-with-upwards-trend',
    ':cherries:' => 'cherries',
    ':chocolatebar:' => 'chocolate-bar',
    ':christmastree:' => 'christmas-tree',
    ':clapper:' => 'clapper',
    ':clipboard:' => 'clipboard', 
    ':closedbook:' => 'closed-book',
    ':closedlockwithkey:' => 'closed-lock-with-key',
    ':closedumbrella:' => 'closed-umbrella',
    ':clubs:' => 'clubs',
    ':cocktail:' => 'cocktail',	
	':coffee:' => 'coffee',
    ':computer:' => 'computer',
    ':confettiball:' => 'confetti-ball',
    ':cookie:' => 'cookie',
    ':corn:' => 'corn',
    ':creditcard:' => 'credit-card',
    ':crown:' => 'crown',
    ':crystalball:' => 'crystal-ball', 
    ':curry:' => 'curry',
    ':custard:' => 'custard',
    ':dango:' => 'dango',
    ':dart:' => 'dart',
    ':date:' => 'date',
    ':diamonds:' => 'diamonds',
    ':dollar:' => 'dollar',
    ':door:' => 'door',
    ':doughnut:' => 'doughnut',
    ':dress:' => 'dress',
    ':dvd:' => 'dvd',
    ':email:' => 'e-mail',
    ':egg:' => 'egg',
    ':eggplant:' => 'eggplant',
    ':electricplug:' => 'electric-plug',
    ':email:' => 'email', 
    ':euro:' => 'euro',
    ':eyeglasses:' => 'eyeglasses',
    ':fax:' => 'fax',
    ':filefolder:' => 'file-folder',
    ':fishcake:' => 'fish-cake',	
	':fishingpoleandfish:' => 'fishing-pole-and-fish',
    ':flashlight:' => 'flashlight',
    ':floppydisk:' => 'floppy-disk',
    ':flowerplayingcards:' => 'flower-playing-cards',
    ':football:' => 'corn',
    ':creditcard:' => 'credit-card',
    ':forkandknife:' => 'fork-and-knife',
    ':friedshrimp:' => 'fried-shrimp', 
    ':fries:' => 'fries',
    ':gamedie:' => 'game-die',
    ':gem:' => 'gem',
    ':gift:' => 'gift',
    ':golf:' => 'golf',
    ':grapes:' => 'grapes',
    ':greenapple:' => 'green-apple',
    ':greenbook:' => 'green-book',
    ':guitar:' => 'guitar',
    ':gun:' => 'gun',
    ':hamburger:' => 'hamburger',
    ':hammer:' => 'hammer',
    ':handbag:' => 'handbag',
    ':headphones:' => 'headphones',
    ':hearts:' => 'hearts',
    ':highbrightness:' => 'high-brightness', 
    ':highheel:' => 'high-heel',
    ':hocho:' => 'hocho',
    ':honeypot:' => 'honey-pot',
    ':horseracing:' => 'horse-racing',
    ':hourglass:' => 'hourglass',	
	':hourglassflowingsand:' => 'hourglass-flowing-sand',
    ':icecream:' => 'ice-cream',
    ':icecream:' => 'icecream',
    ':inboxtray:' => 'inbox-tray',
    ':incomingenvelope:' => 'incoming-envelope',
    ':iphone:' => 'iphone',
    ':jeans:' => 'jeans',
    ':key:' => 'key', 
    ':kimono:' => 'kimono',
    ':ledger:' => 'ledger',
    ':lemon:' => 'lemon',
    ':lipstick:' => 'lipstick',
    ':lock:' => 'lock',
    ':lockwithinkpen:' => 'lock-with-ink-pen',
    ':lollipop:' => 'lollipop',
    ':loop:' => 'loop',
    ':loudspeaker:' => 'loudspeaker',
    ':lowbrightness:' => 'low-brightness',
    ':mag:' => 'mag',
    ':magright:' => 'mag-right',
    ':mahjong:' => 'mahjong',
    ':mailbox:' => 'mailbox',
    ':mailboxclosed:' => 'mailbox-closed',
    ':mailboxwithmail:' => 'mailbox-with-mail', 
    ':mailboxwithnomail:' => 'mailbox-with-no-mail',
    ':mansshoe:' => 'mans-shoe',
    ':meatonbone:' => 'meat-on-bone',
    ':mega:' => 'mega',
    ':melon:' => 'melon',	
	':memo:' => 'memo',
    ':microphone:' => 'microphone',
    ':microscope:' => 'microscope',
    ':minidisc:' => 'minidisc',
    ':moneywithwings:' => 'money-with-wings',
    ':moneybag:' => 'moneybag',
    ':mountainbicyclist:' => 'mountain-bicyclist',
    ':moviecamera:' => 'movie-camera', 
    ':musicalkeyboard:' => 'musical-keyboard',
    ':musicalscore:' => 'musical-score',
    ':mute:' => 'mute',
    ':namebadge:' => 'name-badge',
    ':necktie:' => 'necktie',
    ':newspaper:' => 'newspaper',
    ':nobell:' => 'no-bell',
    ':notebook:' => 'notebook',
    ':notebookwithdecorativecover:' => 'notebook-with-decorative-cover',
    ':nutandbolt:' => 'nut-and-bolt',
    ':oden:' => 'oden',
    ':openfilefolder:' => 'open-file-folder',
    ':orangebook:' => 'orange-book',
    ':outboxtray:' => 'outbox-tray',
    ':pagefacingup:' => 'page-facing-up',
    ':pagewithcurl:' => 'page-with-curl', 
    ':pager:' => 'pager',
    ':paperclip:' => 'paperclip',
    ':peach:' => 'peach',
    ':pear:' => 'pear',
    ':pencil2:' => 'pencil2',	
	':phone:' => 'phone',
    ':pill:' => 'pill',
    ':pineapple:' => 'pineapple',
    ':pizza:' => 'pizza',
    ':postalhorn:' => 'postal-horn',
    ':postbox:' => 'postbox',
    ':pouch:' => 'pouch',
    ':poultryleg:' => 'poultry-leg', 
    ':pound:' => 'pound',
    ':purse:' => 'purse',
    ':pushpin:' => 'pushpin',
    ':radio:' => 'radio',
    ':ramen:' => 'ramen',
    ':ribbon:' => 'ribbon',
    ':rice:' => 'rice',
    ':ricecracker:' => 'rice-cracker',
    ':ring:' => 'ring',
    ':rugbyfootball:' => 'rugby-football',
    ':runningshirtwithsash:' => 'running-shirt-with-sash',
    ':sake:' => 'sake',
    ':sandal:' => 'sandal',
    ':satellite:' => 'satellite',
    ':saxophone:' => 'saxophone',
    ':scissors:' => 'scissors', 
    ':scroll:' => 'scroll',
    ':seat:' => 'seat',
    ':shavedice:' => 'shaved-ice',
    ':shirt:' => 'shirt',
    ':shower:' => 'shower',
	':ski:' => 'ski',
    ':smoking:' => 'smoking',
    ':snowboarder:' => 'snowboarder',
    ':soccer:' => 'soccer',
    ':sound:' => 'sound',
    ':spaceinvader:' => 'space-invader',
    ':spades:' => 'spades',
    ':spaghetti:' => 'spaghetti', 
    ':speaker:' => 'speaker',
    ':stew:' => 'stew',
    ':straightruler:' => 'straight-ruler',
    ':strawberry:' => 'strawberry',
    ':surfer:' => 'surfer',
    ':sushi:' => 'sushi',
    ':sweetpotato:' => 'sweet-potato',
    ':swimmer:' => 'swimmer',
    ':syringe:' => 'syringe',
    ':tada:' => 'tada',
    ':tanabatatree:' => 'tanabata-tree',
    ':tangerine:' => 'tangerine',
    ':tea:' => 'tea',
    ':telephonereceiver:' => 'telephone-receiver',
    ':telescope:' => 'telescope',
    ':tennis:' => 'tennis', 
    ':toilet:' => 'toilet',
    ':tomato:' => 'tomato',
    ':tophat:' => 'tophat',
    ':triangularruler:' => 'triangular-ruler',
    ':trophy:' => 'trophy',	
	':tropicaldrink:' => 'tropical-drink',
    ':trumpet:' => 'trumpet',
    ':tv:' => 'tv',
    ':unlock:' => 'unlock',
    ':vhs:' => 'vhs',
    ':videocamera:' => 'video-camera',
    ':videogame:' => 'video-game',
    ':violin:' => 'violin', 
    ':watch:' => 'watch',
    ':watermelon:' => 'watermelon',
    ':wineglass:' => 'wine-glass',
    ':womansclothes:' => 'womans-clothes',
    ':womanshat:' => 'womans-hat',
    ':wrench:' => 'wrench',
    ':yen:' => 'yen',
    ':aerialtramway:' => 'aerial-tramway',
    ':airplane:' => 'airplane',
    ':ambulance:' => 'ambulance',
    ':anchor:' => 'anchor',
    ':articulatedlorry:' => 'articulated-lorry',
    ':atm:' => 'atm',
    ':bank:' => 'bank',
    ':barber:' => 'barber',
    ':beginner:' => 'beginner', 
    ':bike:' => 'bike',
    ':bluecar:' => 'blue-car',
    ':boat:' => 'boat',
    ':bridgeatnight:' => 'bridge-at-night',
    ':bullettrainfront:' => 'bullettrain-front',	
	':bullettrainside:' => 'bullettrain-side',
    ':bus:' => 'bus',
    ':busstop:' => 'busstop',
    ':car:' => 'car',
    ':carouselhorse:' => 'carousel-horse',
    ':checkeredflag:' => 'checkered-flag',
    ':church:' => 'church',
    ':circustent:' => 'circus-tent', 
    ':citysunrise:' => 'city-sunrise',
    ':citysunset:' => 'city-sunset',
    ':construction:' => 'construction',
    ':conveniencestore:' => 'convenience-store',
    ':crossedflags:' => 'crossed-flags',
    ':departmentstore:' => 'department-store',
    ':europeancastle:' => 'european-castle',
    ':europeanpostoffice:' => 'european-post-office',
    ':factory:' => 'factory',
    ':ferriswheel:' => 'ferris-wheel',
    ':fireengine:' => 'fire-engine',
    ':fountain:' => 'fountain',
    ':fuelpump:' => 'fuelpump',
    ':helicopter:' => 'helicopter',
    ':hospital:' => 'hospital',
    ':hotel:' => 'hotel', 
    ':hotel:' => 'hotel',
    ':hotsprings:' => 'hotsprings',
    ':house:' => 'house',
    ':housewithgarden:' => 'house-with-garden',
    ':japan:' => 'japan',
	':japanesecastle:' => 'japanese-castle',
    ':lightrail:' => 'light-rail',
    ':lovehotel:' => 'love-hotel',
    ':minibus:' => 'minibus',
    ':monorail:' => 'monorail',
    ':mountfuji:' => 'mount-fuji',
    ':mountaincableway:' => 'mountain-cableway',
    ':mountainrailway:' => 'mountain-railway', 
    ':moyai:' => 'moyai',
    ':office:' => 'office',
    ':oncomingautomobile:' => 'oncoming-automobile',
    ':oncomingbus:' => 'oncoming-bus',
    ':oncomingpolicecar:' => 'oncoming-police-car',
    ':oncomingtaxi:' => 'oncoming-taxi',
    ':performingarts:' => 'performing-arts',
    ':policecar:' => 'police-car',
    ':postoffice:' => 'post-office',
    ':railwaycar:' => 'railway-car',
    ':rainbow:' => 'rainbow',
    ':rocket:' => 'rocket',
    ':rollercoaster:' => 'roller-coaster',
    ':rotatinglight:' => 'rotating-light',
    ':roundpushpin:' => 'round-pushpin',
    ':rowboat:' => 'rowboat', 
    ':school:' => 'school',
    ':ship:' => 'ship',
    ':slotmachine:' => 'slot-machine',
    ':speedboat:' => 'speedboat',
    ':stars:' => 'stars',	
	':station:' => 'station',
    ':statueofliberty:' => 'statue-of-liberty',
    ':steamlocomotive:' => 'steam-locomotive',
    ':sunrise:' => 'sunrise',
    ':sunriseovermountains:' => 'sunrise-over-mountains',
    ':suspensionrailway:' => 'suspension-railway',
    ':taxi:' => 'taxi',
    ':tent:' => 'tent', 
    ':ticket:' => 'ticket',
    ':tokyotower:' => 'tokyo-tower',
    ':tractor:' => 'tractor',
    ':trafficlight:' => 'traffic-light',
    ':train2:' => 'train2',
    ':tram:' => 'tram',
    ':triangularflagonpost:' => 'triangular-flag-on-post',
    ':trolleybus:' => 'trolleybus',
    ':truck:' => 'truck',
    ':verticaltrafficlight:' => 'railway-car',
    ':warning:' => 'warning',
    ':wedding:' => 'wedding',
    ':tr:' => 'turkey-flag',
	':jp:' => 'jp',
    ':kr:' => 'kr',
    ':cn:' => 'cn', 
    ':us:' => 'us',
    ':fr:' => 'fr',
    ':es:' => 'es',
    ':it:' => 'it',
    ':ru:' => 'ru',
	':gb:' => 'gb',
	':de:' => 'de',
    ':100:' => '100',
    ':1234:' => '1234',
    ':a:' => 'a',
    ':ab:' => 'ab',
    ':abc:' => 'abc',
    ':abcd:' => 'abcd',
    ':accept:' => 'accept', 
    ':aquarius:' => 'aquarius',
    ':aries:' => 'aries',
    ':arrowbackward:' => 'arrow-backward',
    ':arrowdoubledown:' => 'arrow-double-down',
    ':arrowdoubleup:' => 'arrow-double-up',
    ':arrowdown:' => 'arrow-down',
    ':arrowdownsmall:' => 'arrow-down-small',
    ':arrowforward:' => 'arrow-forward',
    ':arrowheadingdown:' => 'arrow-heading-down',
    ':arrowheadingup:' => 'arrow-heading-up',
    ':arrowleft:' => 'arrow-left',
    ':arrowlowerleft:' => 'arrow-lower-left',
    ':arrowlowerright:' => 'arrow-lower-right',
    ':arrowright:' => 'arrow-right',
    ':arrowrighthook:' => 'arrow-right-hook',
    ':arrowup:' => 'arrow-up', 
    ':arrowupdown:' => 'arrow-up-down',
    ':arrowupsmall:' => 'arrow-up-small',
    ':arrowupperleft:' => 'arrow-upper-left',
    ':arrowupperright:' => 'arrow-upper-right',
    ':arrowsclockwise:' => 'arrows-clockwise',	
	':arrowscounterclockwise:' => 'arrows-counterclockwise',
    ':b:' => 'b',
    ':babysymbol:' => 'baby-symbol',
    ':baggageclaim:' => 'baggage-claim',
    ':ballotboxwithcheck:' => 'ballot-box-with-check',
    ':bangbang:' => 'bangbang',
    ':blackcircle:' => 'black-circle',
    ':blacksquarebutton:' => 'black-square-button', 
    ':cancer:' => 'cancer',
    ':capitalabcd:' => 'capital-abcd',
    ':capricorn:' => 'capricorn',
    ':chart:' => 'chart',
    ':childrencrossing:' => 'children-crossing',
    ':cinema:' => 'cinema',
    ':cl:' => 'cl',
    ':clock1:' => 'clock1',
    ':clock10:' => 'clock10',
    ':clock1030:' => 'clock1030',
    ':clock11:' => 'clock11',
    ':clock1130:' => 'clock1130',
    ':clock12:' => 'clock12',
    ':clock1230:' => 'clock1230',
    ':clock130:' => 'clock130',
    ':clock2:' => 'clock2', 
    ':clock230:' => 'clock230',
    ':clock3:' => 'clock3',
    ':clock330:' => 'clock330',
    ':clock4:' => 'clock4',
    ':clock430:' => 'clock430',	
	':clock5:' => 'clock5',
    ':clock530:' => 'clock530',
    ':clock6:' => 'clock6',
    ':clock630:' => 'clock630',
    ':clock7:' => 'clock7',
    ':clock730:' => 'clock730',
    ':clock8:' => 'clock8',
    ':clock830:' => 'clock830',
    ':clock9:' => 'clock9',
    ':clock930:' => 'clock930',
    ':congratulations:' => 'congratulations', 
    ':cool:' => 'cool',
    ':copyright:' => 'copyright',
    ':curlyloo:' => 'curly-loop',
    ':currencyexchange:' => 'currency-exchange',
    ':customs:' => 'customs',	
	':diamondshapewithadotinside:' => 'diamond-shape-with-a-dot-inside',
    ':donotlitter:' => 'do-not-litter',
    ':eight:' => 'eight',
    ':eightpointedblackstar:' => 'eight-pointed-black-star',
    ':eightspokedasterisk:' => 'eight-spoked-asterisk',
    ':end:' => 'end',
    ':fastforward:' => 'fast-forward',
    ':five:' => 'five',
    ':four:' => 'four',
    ':free:' => 'free',
    ':gemini:' => 'gemini', 
    ':hash:' => 'hash',
    ':heartdecoration:' => 'heart-decoration',
    ':heavycheckmark:' => 'heavy-check-mark',
    ':heavydivisionsign:' => 'heavy-division-sign',
    ':heavydollarsign:' => 'heavy-dollar-sign',	
	':heavyminussign:' => 'heavy-minus-sign',
    ':heavymultiplicationx:' => 'heavy-multiplication-x',
    ':heavyplussign:' => 'heavy-plus-sign',
    ':id:' => 'id',
    ':ideographadvantage:' => 'ideograph-advantage',
    ':informationsource:' => 'information-source',
    ':interrobang:' => 'interrobang',
    ':keycapten:' => 'keycap-ten',
    ':koko:' => 'koko',
    ':largebluecircle:' => 'large-blue-circle',
    ':largebluediamond:' => 'large-blue-diamond', 
    ':largeorangediamond:' => 'large-orange-diamond',
    ':leftluggage:' => 'left-luggage',
    ':leftrightarrow:' => 'left-right-arrow',
    ':leftwardsarrowwithhook:' => 'leftwards-arrow-with-hook',
    ':leo:' => 'leo',	
	':libra:' => 'libra',
    ':link:' => 'link',
    ':m:' => 'm',
    ':mens:' => 'mens',
    ':metro:' => 'metro',
    ':mobilephoneoff:' => 'mobile-phone-off',
    ':negativesquaredcrossmark:' => 'negative-squared-cross-mark',
    ':new:' => 'new',
    ':ng:' => 'ng',
    ':nine:' => 'nine',
    ':nobicycles:' => 'no-bicycles', 
    ':noentry:' => 'no-entry',
    ':noentrysign:' => 'no-entry-sign',
    ':nomobilephones:' => 'no-mobile-phones',
    ':nopedestrians:' => 'no-pedestrians',
    ':nosmoking:' => 'no-smoking',	
	':nonpotablewater:' => 'non-potable-water',
    ':o:' => 'o',
    ':o2:' => 'o2',
    ':ok:' => 'ok',
    ':on:' => 'on',
    ':one:' => 'one',
    ':ophiuchus:' => 'ophiuchus',
    ':parking:' => 'parking',
    ':passportcontrol:' => 'passport-control',
    ':pisces:' => 'pisces',
    ':potablewater:' => 'potable-water', 
    ':putlitterinitsplace:' => 'put-litter-in-its-place',
    ':noentrysign:' => 'no-entry-sign',
    ':radiobutton:' => 'radio-button',
    ':recycle:' => 'recycle',
    ':redcircle:' => 'red-circle',
	':registered:' => 'registered',
    ':repeat:' => 'repeat',
    ':repeaton:' => 'repeat-one',
    ':restroom:' => 'restroom',
    ':rewind:' => 'rewind',
    ':sa:' => 'sa',
    ':sagittarius:' => 'sagittarius',
    ':scorpius:' => 'scorpius',
    ':secret:' => 'secret',
    ':seven:' => 'seven',
    ':signalstrength:' => 'signal-strength', 
    ':six:' => 'six',
    ':sixpointedstar:' => 'six-pointed-star',
    ':smallbluediamond:' => 'small-blue-diamond',
    ':smallorangediamond:' => 'small-orange-diamond',
    ':smallredtriangle:' => 'small-red-triangle',	
	':smallredtriangledown:' => 'small-red-triangle-down',
    ':soon:' => 'soon',
    ':sos:' => 'sos',
    ':symbols:' => 'symbols',
    ':taurus:' => 'taurus',
    ':three:' => 'three',
    ':tm:' => 'tm',
    ':top:' => 'top',
    ':trident:' => 'trident',
    ':twistedrightwardsarrows:' => 'twisted-rightwards-arrows',
    ':two:' => 'two', 
    ':u5272:' => 'u5272',
    ':u5408:' => 'u5408',
    ':u55b6:' => 'u55b6',
    ':u6307:' => 'u6307',
    ':u6708:' => 'u6708',	
	':u6709:' => 'u6709',
    ':u6e80:' => 'u6e80',
    ':u7121:' => 'u7121',
    ':u7533:' => 'u7533',
    ':u7981:' => 'u7981',
    ':u7a7a:' => 'u7a7a',
    ':underage:' => 'underage',
    ':up:' => 'up',
    ':vibrationmode:' => 'vibration-mode',
    ':virgo:' => 'virgo',
    ':vs:' => 'vs', 
    ':wavydash:' => 'wavy-dash',
    ':wc:' => 'wc',
    ':wheelchair:' => 'wheelchair',
    ':whitecheckmark:' => 'white-check-mark',
    ':whitecircle:' => 'white-circle',	
	':whiteflower:' => 'white-flower',
    ':whitesquarebutton:' => 'white-square-button',
    ':womens:' => 'womens',
    ':x:' => 'x',
    ':zero:' => 'zero',
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
    'products'
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