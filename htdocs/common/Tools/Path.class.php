<?php
/**
 * 路径处理类
 *
 * @author lihuanlin <birdy@findlaw.cn>
 */
namespace Tools;
/**
 * 路径处理类
 *
 * @author lihuanlin <birdy@findlaw.cn>
 */
class Path
{
    /**
     * 获取站点页缓存地址
     * 
     * @param int $website_id 站点ID
     * 
     * @return void
     */
    static public function getHtmlCachePath($website_id = 0)
    {
        return self::getMd5Path($website_id, RUNTIME_PATH."html/{md5}/");
    }
    
    /**
     * 获取站点素材路径
     * 
     * @param int  $website_id 站点ID,后面都改成uid了
     * @param int  $type       素材类型 logo,banner,qrcode
     * @param int  $pic        文件名称，空时，只返回路径
     * @param bool $http       是否加http前缀域名
     * 
     * @return type
     */
    static public function getWebsiteImgPath($website_id = 0, $type = 'logo', $pic = '', $http = false)
    {
        $path=self::getMd5Path($website_id, "my/marketing/{md5}/".$type."/".$pic);
        $pos = (abs(crc32(str_replace('thumb_', '', trim($path))))%3)+1;
        return $http ? "http://d0".$pos.".findlawimg.com/".$path : $path;
    }
    
    /**
     * 设置Md5路径
     * 
     * @param string $val   要加密的值
     * @param string $path  路径，必须包含 {md5}
     * @param int    $level 路径深度，默认为2
     * 
     * @return void
     */
    static public function getMd5Path($val, $path='{md5}', $level = 2)
    {
        if ($level != 1) {
            $level = 2;
        }
        $str = substr(md5($val), 0, $level);
        $md5 = array();
        for ($i = 0; $i < $level; $i++) {
            $md5[] = $str[$i];
        }
        $md5 = implode("/", $md5)."/".$val;
        $path=str_replace("//", '/', $path);
        return str_replace("{md5}", $md5, $path);
    }
    
    /**
     * 删除图片缓存
     * 
     * @param type $url 图片url
     * 
     * @return boolean
     */
    public function clearImgCache($url)
    {
        if (APP_DEBUG) {
            return '内网无需清理';
        }
        if (is_array($url)) {
            $url=  implode(',', $url);
        } elseif (is_string($url)) {
            $url=  str_replace('|', ',', $url);
        }
        $addr='https://www.cdnzz.com/api/json?user=wusiliang@lawjob.cn&signature=8d72afcbfbb24434d148c23fc94c5c33&method=PurgeCache&url=';
        $addr.=$url;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $addr);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
        $return = curl_exec($curl);
        if ($return && $_GET['debug']!='debug') {
            return $return; 
        } else {
            $return =trim($return);
            $return=substr($return, strpos($return, '{'));
            $return=json_decode($return, true);
            header('Content-Type:application/json; charset=utf-8');
            $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
            exit($handler.'('.json_encode(array('status'=>0, 'info'=>$addr), 0).');');  
        }
    }
    
    /**
     * 获取相册图片信息
     * 
     * @param mix  $ids     相册ID，可以是整型或者数组
     * @param int  $uid     UID
     * @param bool $formate 组合原地址数据
     * @param str  $thum    缩略图
     * 
     * @return void
     */
    static public function getPhotoByIds($ids, $uid = 0, $formate = false, $thum='')
    {
        if (empty($ids)) {
            return array();
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        sort($ids);
        $cacheKey = "album_photos_".md5(implode("_", $ids)).$uid;
        $data = S($cacheKey);
        if (!$data) {
            $map = array(
                "id" => array('in', $ids)
            );
            if ($uid > 0) {
                $map['uid'] = $uid;
            }
            $data = D("Common/Album")->where($map)->getField("id,photo");
            S($cacheKey, $data, 3600 * 24);
        }
        
        if ($uid > 0 && $formate) {
            foreach ($data as $k=>$v) {
                $data[$k] = self::getWebsiteImgPath($uid, "album", $thum.$v, true);
            }
        }
        return $data;
    }
    
    /**
     * 获取完整URL
     * 
     * @return string
     */
    static public function curPageURL() 
    {
        $pageURL = 'http';

        if ($_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

}
