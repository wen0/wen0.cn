<?php
/**
 * 217测试配置
 * 
 * @author lihuanlin <birdy@findlaw.cn>
 */
return array (
    'DB_TYPE'   =>  'mysql',          // 数据库类型
    'DB_HOST'   =>  '192.168.1.216',  // 服务器地址
    'DB_NAME'   =>  'www_fl580_com',  // 数据库名
    'DB_USER'   =>  'lihuanlin_web',      // 用户名
    'DB_PWD'    =>  'YVEWS936FCTGHB8',   // 密码
    'DB_PORT'   =>  '50001',          // 端口
    'DB_PREFIX' =>  'mk_',            // 数据库表前缀  
    
    "HOST_UC_RPC" => array(    //用户中心RPC配置
        '192.168.3.235:18080',
    ),
    "HOST_FINDLAW_RPC" => array(     //找法RPC配置
        '192.168.3.235:8080',
    ),
    "HOST_CURL" => array (
        array('192.168.1.217', 80)
    ),
    "HOST_SPLIT"=>'http://192.168.3.234:7777',   //中文分词库
    'UC_CURL_IP' => '192.168.1.217:80'  //UC内网API接口代理配置，99和外网不需要配置此参数
);