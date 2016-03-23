<?php
/**
 * 云建站入口
 * 
 * @author lihuanlin <birdy@findlaw.cn>
 */ 
// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    die('require PHP > 5.3.0 !');
}

//项目名称,  这里请不要修改
$tmp = array_reverse(explode('/', str_replace('\\', '/', __FILE__)));
define('APP_NAME', ucfirst($tmp[1]));

//定义缓存目录
define('RUNTIME_PATH', '../Runtime/'.APP_NAME."/");

//定义第三方类库目录
define('API_PATH', '../common/Api/');

$serverIP = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : (isset($serverIP) ? $serverIP : '14.17.121.98');

//IP前三段
$serverIP       = explode('.', $serverIP);
$ServerIP_3     = $serverIP[0].'.'.$serverIP[1].'.'.$serverIP[2];
$serverIP_end   = $serverIP[3];
//应用场景
switch ($ServerIP_3) {
case "122.13.150" :  //电信正式环境
    define('APP_PATH', './trunk/'); // 定义应用目录  当在分支开发时， 可更改到指定目录
    if ($serverIP_end == "99") {
        define('APP_DEBUG', true);      //内网集成测试环境
        define('APP_STATUS', 'product_test'); //联通测试环境
    } else {
        define('APP_STATUS', 'product_lt');   //联通正式环境
    }
    break;
case "14.17.121" :
    define('APP_PATH', './trunk/'); // 定义应用目录  当在分支开发时， 可更改到指定目录
    if ($serverIP_end == "99") {
        define('APP_DEBUG', true);      //内网集成测试环境
        define('APP_STATUS', 'product_test'); //电信测试环境
    } else {
        define('APP_STATUS', 'product_dx');   //电信正式环境
    }
    break;
case "192.168.1" :
    define('APP_PATH', './trunk/');
    define('APP_STATUS', 'test');
    define('APP_DEBUG', true);      //内网集成测试环境
    break;
default :
    define('APP_PATH', './trunk/');
    define('APP_DEBUG', true);            //开启调试模式
    define('APP_STATUS', 'local');        //开发环境
    break;
}
if (!defined('APP_PATH')) {
    define('APP_PATH', './braches/second/'); // 定义应用目录  当在分支开发时， 可更改到指定目录
}

define("VIEW_PATH", "../media/tpl/");
define("UPLOAD_PATH", "../media/upload/");
// 引入ThinkPHP入口文件
require '../common/Framework/ThinkPHP3.2.3/ThinkPHP.php';
?>