<?php
/**
 * 配置
 * 
 * @author lihuanlin <birdy@findlaw.cn>
 */
return array (
    'AUTOLOAD_NAMESPACE' => array(
        'Tools'   => './common/Tools', //公共类自动加载
    ),  
    'CHECK_APP_DIR'         =>  false,  //不进行目录检查
    'TMPL_TEMPLATE_SUFFIX'  =>  '.tpl', // 默认模板文件后缀
    'DEFAULT_MODULE' => "Space",
    'APP_SUB_DOMAIN_DEPLOY' =>  1,      //开启二级域名配置
    'APP_SUB_DOMAIN_RULES'  =>  array(
        'u'     => 'User',
        'admin' => 'Backend',
        'weixin'     => "Weixin",
        '*'     => array('Space', 'domain=*'), // 二级泛域名指向Space模块
    ),
    'TMPL_ACTION_ERROR'     =>  COMMON_PATH.'View/jump_new.tpl',      //默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  COMMON_PATH.'View/jump_new.tpl',      //默认成功跳转对应的模板文件
    'TMPL_EXCEPTION_FILE'   =>  COMMON_PATH.'View/exception.tpl',   //异常页面的模板文件
    'DOMAIN' => array(
        'main' => 'wen0.cn',  //主站域名
        'website' => 'www.wen0.cn',        //律师站域名
        'static' => 'http://static.wen0.cn/',  //静态资源地址
        'css_web' => 'http://static.lawyermarketing.cn/css/fl580/',    //CSS静态资源地址
        'js_web' => 'http://static.lawyermarketing.cn/js/fl580/',      //JS静态资源地址
        'img_web' => 'http://static.lawyermarketing.cn/images/fl580/', //图片静态资源地址
        'tpl' => 'http://static.lawyermarketing.cn/tpl/',              //模板路径
        'upload' => 'http://static.wen0.cn/upload/',        //上传路径
    ),
    'SESSION' => array(
        'login'      => 'login_user',          //当前登录用户
        'login_sign' => 'login_user_sign',     //当前登录用户的签名
        'website'    => 'current_website',     //当前站点
        'tpl'        => 'current_tpl',         //当前模板
        'phone_code_reg' => 'phone_code_reg',  //注册短信验证码
        'phone_code_getpwd' => 'phone_code_getpwd'  //找回密码短信验证码
    ),
    'DATA_CACHE_SUBDIR' => true,  //使用子目录缓存
    'DATA_PATH_LEVEL' => 2,       //缓存目录深度   
    'ENCRYPT_KEY' => 'djfoapeujwoifj412342fsklfdf', //登录Ump加密串
    'AUTH_MD5_KEY' => '^777ABcde#$%!IE$' //字符加密密钥
);
