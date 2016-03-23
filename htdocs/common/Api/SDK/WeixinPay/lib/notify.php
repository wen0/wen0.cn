<?php
require_once 'WxPay.Notify.php';
class PayNotifyCallBack extends WxPayNotify
{
    protected $data=array(); 
    //查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		//Log::DEBUG("call back:" . json_encode($data));
                //$weixin_notify = S('weixin_notify');
                //if (!$weixin_notify) {
                //  $weixin_notify=array();
                //}
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
                /*$weixin_notify[] = $data;
                S('weixin_notify', $weixin_notify, 36000);*/
                $order = $this->successOrder($data);
                if (!$order) {
                    return false;
                }
		return true;
	}
        
    public function setNotifyObj($obj)
    {
        $this->data['NotifyObj'] = $obj;
    }        
    
    public function setNotifyMethod($method)
    {
        $this->data['Notifymethod'] = $method;
    } 
    private function successOrder($data) 
    { 
        $method = $this->data['Notifymethod'];
        return $this->data['NotifyObj']->$method($data);
    } 
}

