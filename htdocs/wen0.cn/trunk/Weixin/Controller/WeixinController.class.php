<?php
namespace Weixin\Controller;
use Common\Controller\CommonController;
class WeixinController extends CommonController 
{
    const TOKEN = 'wen0';
    const AppID = 'wx9bbab1f8799b6091';
    const AppSecret = '71596f12d5c835e6b8b705e2ceb96284';
    
    protected function _initialize()
    {
        $this->SData('wen0', 'aa');
        if (!empty($_GET['echostr'])) {
            if($this->checkSignature()){
                echo $_GET["echostr"];
            }
            exit;
        }
        $this->getWeixinPUsh();
        exit;
    }

    protected function getWeixinPUsh ()
    {
        $data1 = $GLOBALS["HTTP_RAW_POST_DATA"];
        $this->SData('wen0', $data1);
        $data = file_get_contents("php://input");
        $this->SData('wen0', $data);
        if (!$data) {
            return false;
        }
        $this->responseMsg($data);
    }
    
    protected function SData($var,  $data = null) 
    {
        $SData = S(md5($var)) ;
        if ($data === null) {
            return $SData;
        }
        
        if (!is_array($SData)) {
            $SData = array();
        }
        $SData[] = $data;
        S(md5($var), $SData); 
        return true;
    }
    
    
    
    public function responseMsg($postStr)
    {
      	//extract post data
		if (!empty($postStr)){
                /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
                   the best way is to check the validity of xml by yourself */
                libxml_disable_entity_loader(true);
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";             
				if(!empty( $keyword ))
                {
              		$msgType = "text";
                	$contentStr = "Welcome to wechat world!";
                	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                	echo $resultStr;
                }else{
                	echo "Input something...";
                }

        }else {
        	echo "";
        	exit;
        }
    }
		
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
		$token = self::TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
    
}