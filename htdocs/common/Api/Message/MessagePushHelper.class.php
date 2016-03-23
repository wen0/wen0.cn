<?php
/**
 * 消息推送
 * @author WY <chenjinlian@findlaw.com> 
 *
 */

/**
 * 消息推送
 * @author WY <chenjinlian@findlaw.com> 
 *
 */
class MessagePushHelper
{
    //推送平台api
    private  $apiurl;
    //接入表示
    private  $channe;
    //约定明文
    private  $plainkey;
    //约定aes密文,16
    private  $aeskey;
    
    private static $entity;
    
    
    /**
     * 【描述】获取一个新对象
     * 
     * @return MessagePushHelper
     */
    public static function getInstance()
    {
        if (self::$entity == null) {
            self::$entity = new MessagePushHelper();
            self::$entity->channel = C("WEILV.CHANNEL");
            self::$entity->apiurl = C("WEILV.APIURL");
            self::$entity->aeskey = C("WEILV.AESKEY");
            self::$entity->plainkey = C("WEILV.PLAINKEY");
            
        }
        return self::$entity;
    }
    
    /**
     * 【描述】获取token
     * 
     * @return string
     */
    private function getAuthtoken()
    {
        require_once API_PATH . 'Encrypt/AES.class.php';
        $aes = new \AES(true); // 把加密后的字符串按十六进制进行存储
        
        $keys = $aes->makeKey($this->aeskey);
        $split = "||";
        $timeLimit = time();
        $plainText = $this->channel.$split.$this->plainkey.$split.$timeLimit;
        $encryptText = $aes->encryptString($plainText, $keys);
        $authtoken = base64_encode($encryptText);
        return $authtoken;
    }
    
    /**
     * 【描述】格式化post数据
     * 
     * @param array $postData postData
     * 
     * @return string
     */
    private function formatPostData($postData)
    {
        $postStr = '';
        if (is_array($postData)) {
            if (empty($postData['channel'] )) {
                $postData['channel'] = $this->channel;
            }
            foreach ($postData as $k => $value) {
                
                if (!is_numeric($value)) {
                    
                    
                    $value = urlencode($value);
                    
                }
                $postStr .='&'.$k.'='. $value;
            }
            $postStr = substr($postStr, 1);
        } else {
            $postStr = $postData;
        }
        return $postStr;
    }
    
    /**
     * 【描述】识别gbk
     * 
     * @param String $str 字符
     * 
     * @return boolean
     */
    private function isGBK($str) {
        $typeArr = array('ASCII','GB2312','GBK','UTF-8');
        $type = mb_detect_encoding($str, $typeArr);
        if ($type == "GB2312" || $type == 'GBK') {
            return  true;
        } else {
            return false;
        }
    }
    
    
    /**
     * 【描述】识别utf88
     * 
     * @param String $str 字符
     * 
     * @return boolean
     */
    private function isUTF8($str) {
        $typeArr = array('ASCII','GB2312','GBK','UTF-8');
        if (mb_detect_encoding($str, $typeArr) == "UTF-8") {
            return  true;
        } else {
            return false;
        }
    }
    
    
    /**
     * 【描述】 提交http请求
     * 
     * @param Array $postData postData
     * 
     * @return String
     */
    private function doPost($postData)
    {
        $postStr = $this->formatPostData($postData);
        
        $ch = curl_init();       
        
        $apiurl = $this->apiurl;
        
        $header = array();
        $header[] = 'content-type: application/x-www-form-urlencoded; charset=UTF-8';
        curl_setopt($ch, CURLOPT_URL, $apiurl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postStr);
        $returnJson = curl_exec($ch);
        curl_close($ch);
        return $returnJson;
    }
    
    /**
     * 【描述】获取用户未读数据
     * 
     * @param int    $uid   uid
     * @param String $xpath xpath
     * 
     * @return array
     */
    public function getUserBadge($uid, $xpath)
    {
        $authtoken = $this->getAuthtoken();
        $postData = array(
                'method' => 'wanyou.app.user.get.badge',
                'authtoken' => $authtoken,
                'uid' => $uid,
                'xpath' => $xpath,
        );
        
        
        $jsonStr = $this->doPost($postData);
        $jsonObj = json_decode($jsonStr);
        $arr = null;
        if ($jsonObj->code == 0) {
            $data = $jsonObj->data;
            $arr = $this->jsonToArray($data);
            if (count($arr) == 0) {
                $arr = null;
            }
        }
        return  $arr;
        
    }
    
    /**
     * 【描述】json2array
     * 
     * @param sdtClass $jsonObj json对象
     * 
     * @return array
     */
    private function jsonToArray($jsonObj)
    {
        $arr = array();
        foreach ($jsonObj as $k => $v) {
            if ( is_array($v) || is_object($v)) {
                $arr[$k] = $this->jsonToArray($v);
            } else {
                $arr[$k] = $v;
            }
        }
        return $arr;
    }
    
    /**
     * 【描述】设置未读消息数
     *
     * @param int    $uid    uid
     * @param String $xpath  xpath
     * @param int    $num    未读数
     *
     * @return boolean
     */
    public function setUserBadge($uid, $xpath, $num)
    {
        $authtoken = $this->getAuthtoken();
        $postData = array(
                'method' => 'wanyou.app.user.set.badge',
                'authtoken' => $authtoken,
                'uid' => $uid,
                'xpath' => $xpath,
                'num' => intval($num),
        );
    
    
        $jsonStr = $this->doPost($postData);
        $jsonObj = json_decode($jsonStr);
        $bool = false;
        if ($jsonObj->code == 0 && $jsonObj->data == 'SUCC') {
            if ($num > 0) {
                //更新数据库时间
                $this->updateQuestionLastTime($xpath);
            }
           
            $bool = true;
        }
        return $bool;
    
    }
    
    
    /**
     * 【描述】添加/减少未读消息数
     *
     * @param int    $uid    uid
     * @param String $xpath  xpath
     * @param int    $addNum 增量数
     *
     * @return boolean
     */
    public function addUserBadge($uid, $xpath, $addNum)
    {
        $authtoken = $this->getAuthtoken();
        $postData = array(
                'method' => 'wanyou.app.user.add.badge',
                'authtoken' => $authtoken,
                'uid' => $uid,
                'xpath' => $xpath,
                'addNum' => $addNum,
        );
    
    
        $jsonStr = $this->doPost($postData);
        $jsonObj = json_decode($jsonStr);
        $bool = false;
        if ($jsonObj->code == 0 && $jsonObj->data == 'SUCC') {
            if ($addNum > 0) {
                //更新数据库时间
                $this->updateQuestionLastTime($xpath);;
            }
            
            $bool = true;
        }
        return $bool;
    
    }
    
    /**
     * 【描述】更新某问题的最后更新时间
     * 
     * @param String $xpath
     * 
     * @return void
     */
    public function updateQuestionLastTime($xpath)
    {
        $qid = $this->getQidByXpath($xpath);
        if ($qid == 0) {
            return;
        }
        $header = null;
        
        $params = array(
                array(
                        'id'=>$qid,
                        'lastTime'=> time()
                        
        )
        );
        $rpcId = "AppQuestion.Admin.updateDetails";
        RpcCallFactory::getData($rpcId, $header, $params, 'utf-8');
        
    }
    /**
     * 从xpath获取qid
     * 
     * @param String $path
     * @return number
     */
    private function getQidByXpath($path)
    {
        $key = 'lscn/dialogue/qid/';
        $offset = strpos($path, $key);
        $qid = 0;
        if ($offset >= 0) {
            $str = substr($path, $offset + strlen($key));
            $offset2 = strpos($str, '/');
            if ($offset2 > 0) {
                $qid = substr($str, 0, $offset2);
            } else {
                $qid = $str;
            }
            
            $qid = intval($qid);
        }
        
       
        return $qid;
    }
    
    
    /**
     * 【描述】推送消息
     * 
     * @param String $appName 推送对象app应用名称，例如：weilv haolvshi lscnapp
     * @param String $uidstr  用户uid组合, 例如"123,122,124"
     * @param String $title   消息标题
     * @param String $content 消息内容
     * @param String $params  附带参数，例如 "id=23&name=abc"
     * @param int    $timing  定时发送的时间戳（精度:秒），0表示即时发送
     * @param String $xpath   未读数更新的路径
     * 
     * @return boolean
     */
    public  function sendPushMsg($appName, $uidstr, $title, $content, $params, $timing=0, $xpath=null)
    {
       
        $authtoken = $this->getAuthtoken();
        $postData = array(
                'method' => 'wanyou.app.message.push',
                'authtoken' => $authtoken,
                'channel' => $this->channel,
                'uids' => $uidstr,
                'appName' => $appName,
                'title' => $title,
                'content' => $content,
                'params' => urlencode($params),
                'timing' => $timing,
                'xpath' => $xpath,
        );
        $returnJson = $this->doPost($postData);
        $returnJson = json_decode($returnJson);
        
        S("pushConfig", $postData);
        if ($returnJson->code == 0) {
            S("pushData", $postData);
            return true;
        } else {
            S("pushData", $returnJson);
        }
        return false;
    }
    
    /**
     * 【描述】插入一条首页通知消息
     * 
     * @param unknown $qid
     * @param unknown $fromuid
     * @param unknown $touid
     * @param unknown $alert
     * @param unknown $createTime
     * 
     * @return boolean
     */
    public function insertAppHomeAlert($qid, $fromuid, $touid, $alert, $createTime)
    {
        $authtoken = $this->getAuthtoken();
        $postData = array(
                'method' => 'weilv.app.InsertAppHomeAlert',
                'authtoken' => $authtoken,
                'channel' => $this->channel,
                'qid' => $qid,
                'fromuid' => $fromuid,
                'touid' => $touid,
                'alert' => $alert,
                'createTime' => $createTime,
                
        );
        
        $returnJson = $this->doPost($postData);
        $returnJson = json_decode($returnJson);
        
        if ($returnJson->code == 0) {
            return true;
        }
        return false;
    }
    
    /**
     * 【描述】咨询广播给订阅律师
     * 
     * @param int    $qid      咨询id
     * @param int    $areacode 地区id
     * @param int    $fromUid  作者id
     * @param int    $sid1     1级专长id
     * @param int    $sid2     2级专长id
     * @param String $title    标题
     * @param String $content  广播内容
     * @param int    $pubTime  广播时间
     * 
     * @return boolean
     */
    public function boardcastQuestion($qid, $areacode, $fromUid, $sid1, $sid2, $title, $content, $pubTime=0)
    {
       if ($pubTime == 0) {
           $pubTime = time();
       }
       $authtoken = $this->getAuthtoken();
       $postData = array(
               'method' => 'wanyou.app.message.boardcast',
               'authtoken' => $authtoken,
               'channel' => $this->channel,
               'areacode' => $areacode,
               'qid' => $qid,
               'fromUid' => $fromUid,
               'sid1' => $sid1,
               'sid2' => $sid2,
               'title' => $title,
               'content' => $content,
               'pubTime' => $pubTime
       );
       
       $returnJson = $this->doPost($postData);
       
       $returnJson = json_decode($returnJson);
       
       if ($returnJson->code == 0) {
           return true;
       }
       return false;
        
    }
    
}

?>