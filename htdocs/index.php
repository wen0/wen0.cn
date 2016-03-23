<?php
/**
 * wen0.cn
 * 
 * @author wenfeng <admin@wen0.cn>
 */ 
// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    die('require PHP > 5.3.0 !');
}

ini_set("display_errors", "On");
error_reporting(E_ALL);

define('SITE_PATH', str_replace('\\', '/', dirname(__FILE__)). '/');

define('APP_NAME', 'wen0.cn');

//定义缓存目录
define('RUNTIME_PATH', SITE_PATH.'Runtime/'.APP_NAME."/");

//定义第三方类库目录
define('API_PATH', SITE_PATH.'common/Api/');


$serverIP = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0';

//IP前三段
$serverIP       = explode('.', $serverIP);
$ServerIP_3     = $serverIP[0].'.'.$serverIP[1];
$serverIP_end   = $serverIP[3];


//应用场景
switch ($ServerIP_3) {
case "192.168" :
case "127.0" :
	define('APP_DEBUG', true);            //开启调试模式
	define('APP_STATUS', 'local');        //开发环境
	define('APP_PATH', SITE_PATH.APP_NAME.'/trunk/');
    break;
default :
	define('APP_DEBUG', true);            //开启调试模式
	define('APP_STATUS', 'product');        //开发环境
	define('APP_PATH', SITE_PATH.APP_NAME.'/trunk/');
    break;
}


if (!defined('APP_PATH')) {
    define('APP_PATH', SITE_PATH.APP_NAME.'/braches/second/'); // 定义应用目录  当在分支开发时， 可更改到指定目录
}

define("VIEW_PATH", SITE_PATH."media/tpl/");
define("UPLOAD_PATH", SITE_PATH."media/upload/");

// 引入ThinkPHP入口文件
require SITE_PATH.'common/Framework/ThinkPHP3.2.3/ThinkPHP.php';
?>