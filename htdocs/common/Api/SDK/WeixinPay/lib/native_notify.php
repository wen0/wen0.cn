<?php
require_once 'WxPay.Notify.php';
class NativeNotifyCallBack extends WxPayNotify
{
    protected $data=array(); 
    public function setNotifyObj($obj)
    {
        $this->data['NotifyObj'] = $obj;
    }
    public function setNotifyMethod($method)
    {
        $this->data['Notifymethod'] = $method;
    } 
    public function unifiedorder($data) 
    { 
        $method = $this->data['Notifymethod'];
        return $this->data['NotifyObj']->$method($data);
    } 
    public function NotifyProcess($data, &$msg)
    {
        if(!array_key_exists("openid", $data) ||
                !array_key_exists("product_id", $data))
        {
                $msg = "回调数据异常";
                return false;
        }
        //$openid = $data["openid"];
        //$product_id = $data["product_id"];
        //统一下单
        $result = $this->unifiedorder($data);
        $weixin_notify = S('weixin_notify');
        if (!$weixin_notify) {
            $weixin_notify=array();
        }
        $weixin_notify[] = array($data, $result);
        S('weixin_notify', $weixin_notify, 36000);
        if (!is_array($result)) {
            $msg = $result;
            return false;
        }
        if(!array_key_exists("appid", $result) ||
                 !array_key_exists("mch_id", $result) ||
                 !array_key_exists("prepay_id", $result))
        {
                $msg = "统一下单失败";
                return false;
        }
        $this->SetData("appid", $result["appid"]);
        $this->SetData("mch_id", $result["mch_id"]);
        $this->SetData("nonce_str", WxPayApi::getNonceStr());
        $this->SetData("prepay_id", $result["prepay_id"]);
        $this->SetData("result_code", "SUCCESS");
        $this->SetData("err_code_des", "OK");
        return true;
    }
}
