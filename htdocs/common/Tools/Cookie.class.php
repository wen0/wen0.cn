<?php
/**
 *  Cookie工具类
 *
 *  @author zsg <xxx@qq.com>
 *
 */

namespace Tools;
use Tools;

/**
 *  Cookie工具类
 *
 *  @author zsg <xxx@qq.com>
 */
class Cookie
{
    /**
     * cookie设置级销除
     *
     * @param string $var      cookie名称
     * @param string $value    ccookie值
     * @param string $life     有效期
     * @param bool   $httponly httponly
     *
     * @return unknow
     */
    public static function dsetcookie($var, $value = '', $life = 0, $httponly = false)
    {
        $timenow = time();
        $cookiedomain = '.'.C("domain.main");
        $cookiepath = '/';
        if ($value == '' || $life < 0) {
            $value = '';
            $life = -1;
        }
        $life = $life > 0 ? ($timenow + $life) : ( $timenow - 31536000 );
        $path = $httponly && PHP_VERSION < '5.2.0' ? "$cookiepath; HttpOnly" : $cookiepath;
        $secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
        if (PHP_VERSION < '5.2.0') {
            $ret = setcookie($var, $value, $life, $path, $cookiedomain, $secure);
        } else {
            $ret = setcookie($var, $value, $life, $path, $cookiedomain, $secure, $httponly);
        }
        return $ret ;
    }
    
    /**
     * 初始化上传cookie， 解决flash上传cookie丢失问题
     * 
     * @return null
     */
    public static function init_upload_cookies()
    {
        $uploadify_cookies = $_REQUEST['uploadify_cookies'];
        $tmp = explode(';', $uploadify_cookies);
        foreach ($tmp as $str) {
            $pos = strpos($str, '=', 0);
            if ($pos) {
                $k = trim(substr($str, 0, $pos));
                $v = substr($str, $pos+1, strlen($str));
                $v = urldecode($v);
                $_COOKIE[$k] = $v;
            }
        }
    }
    
}
