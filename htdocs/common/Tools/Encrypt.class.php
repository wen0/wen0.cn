<?php
/**
 *  加密工具类
 *
 *  @author lihuanlin <birdy@findlaw.cn>
 *
 */
namespace Tools;
/**
 *  加密工具类
 *
 *  @author lihuanlin <birdy@findlaw.cn>
 */


/**
 *  加密工具类
 *
 *  @author zsg <xxx@qq.com>
 *
 */
/**
 *  加密工具类
 *
 *  @author zsg <xxx@qq.com>
 */

class Encrypt
{

    /**
     * 用dede的方法进行加解密 来源于旧目录结构的Miscfunc
     * 
     * @param string $string    字符串
     * @param string $operation 操作方式 decode encode
     * @param string $key       密钥
     *
     * @return .....
     * */
    public static function authcode($string, $operation = 'DECODE', $key = '')
    {
        $auth_key = !empty($key) ? $key : '';
        $key = md5($auth_key);
        $key_length = strlen($key);
        $string = $operation == "DECODE" ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;
        $string_length = strlen($string);
        $rndkey = $box = array();
        $result = "";
        $i = 0;
        for (; $i <= 255; ++$i) {
            $rndkey[$i] = ord($key[$i % $key_length]);
            $box[$i] = $i;
        }
        $j = $i = 0;
        for (; $i < 256; ++$i) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        $a = $j = $i = 0;
        for (; $i < $string_length; ++$i) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ $box[($box[$a] + $box[$j]) % 256]);
        }
        if ($operation == "DECODE") {
            if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
                return substr($result, 8);
            } else {
                return "";
            }
        } else {
            return str_replace("=", "", base64_encode($result));
        }
    }

    /**
     * 加解密方法 来源于旧目录结构的 china.findlaw.cn/Common/common.php
     *
     * @param string $string    字符串
     * @param string $operation 操作方式 decode encode
     * @param string $key       密钥
     * @param string $expiry    .....
     *
     * @return .....
     * */
    public static function auth_code($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        $ckey_length = 4;

        $key = md5($key ? $key : '9e13yK8RN2M0lKP8CLRLhGs468d1WMaSlbDeCcI');
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    /**
     * AES加密解密方法
     *
     * @param unknown $string    str
     * @param unknown $aesKey    key
     * @param string  $operation ope
     *
     * @return string
     */
    public static function aes_crypt($string, $aesKey, $operation = 'DECODE')
    {
        require_once API_PATH . 'Encrypt/AES.class.php';
        $aes = new \AES(true);    // 把加密后的字符串按十六进制进行存储
        $keys = $aes->makeKey($aesKey);
        if ($operation == 'DECODE') {
            return $aes->decryptString($string, $keys);
        } else {
            return $aes->encryptString($string, $keys);
        }
    }

    /**
     * 数据签名认证
     * 
     * @param array $data 被认证的数据
     * 
     * @return string 签名
     */
    public static function data_auth_sign($data)
    {
        //数据类型检测
        if (!is_array($data)) {
            $data = (array) $data;
        }
        ksort($data); //排序
        $code = http_build_query($data); //url编码并生成query字符串
        $sign = sha1($code); //生成签名
        return $sign;
    }
    
    /** 
     * 【描述】加密, 用DES算法加密/解密字符串，注意，加密前需要把数组转换为json格式的字符串
     * 
     * @param string $string  待加密的字符串
     * @param string $key     密匙，和管理后台需保持一致
     * @param string $charset 字符集
     * 
     * @return string 返回经过加密/解密的字符串
     */
    public static function desEncrypt($string, $key, $charset='GBK') 
    {
        $size = mcrypt_get_block_size('des', 'ecb');
        if (strtolower($charset)=='gbk') {
            $string = mb_convert_encoding($string, 'UTF-8', 'GBK');
        }
        $pad = $size - (strlen($string) % $size);
        $string = $string . str_repeat(chr($pad), $pad);
        $td = mcrypt_module_open('des', '', 'ecb', '');
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        @mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $string);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }
    
    /**
     * 【描述】解密，解密后返回的是json格式的字符串
     * 
     * @param string $string  加密字符
     * @param string $key     加密密码
     * @param string $charset 字符集
     * 
     * @return boolean|string
     */
    public static function desDecrypt($string, $key, $charset='GBK') 
    {
        $string = base64_decode($string);
        $td = mcrypt_module_open('des', '', 'ecb', '');
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        $decrypted = mdecrypt_generic($td, $string);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $pad = ord($decrypted{strlen($decrypted) - 1});
        if ($pad > strlen($decrypted) ) {
            return false;
        }
        if (strspn($decrypted, chr($pad), strlen($decrypted) - $pad) != $pad) {
            return false;
        }
        $result = substr($decrypted, 0, -1 * $pad);
        if (strtolower($charset)=='gbk') {
            $result = mb_convert_encoding($result, 'GBK', 'UTF-8');
        }
        return $result;
    }

}
