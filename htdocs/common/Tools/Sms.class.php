<?php
/**
 *  短信发送工具类
 *
 *  @author lihuanlin <birdy@findlaw.cn>
 *
 */
namespace Tools;
/**
 *  短信发送工具类
 *
 *  @author lihuanlin <birdy@findlaw.cn>
 */
class Sms
{
    /**
     * 向手机发送短信
     *
     * @param str $mobile  接收短信的手机号码
     * @param str $content 短信内容
     * @param str $account 发送账号 默认找法
     * @param str $pwd     发送密码 默认找法
     * @param str $iscode  是否是发送验证码,发送验证码多了一分钟倒计时验证
     *
     * @return int 1 发送成功; -99 错误的请求; -8 不满足发送条件; -7 短信内容过长; -5 短信服务已到期; -4 用户未开通短信服务或短信服务已暂停; -2 短信已用完; -1 参数错误
     */
    public static function send_mobile_sms($mobile, $content, $account='lawyermarketing', $pwd='marketing2015', $iscode='0')
    {
        include_once API_PATH."Message/SmsSDK.class.php";
        $sms = new \SmsSDK($account, $pwd, 40);
        //发送之前查询上次发送时间
        $lastsendtime=$sms->getLastSendtime($mobile);
        if (time()-$lastsendtime < 60 && $iscode == 1) {
            \Tools\Ajax::ajaxReturn(array('errno' => '请倒计时一分钟！！lastsendtime:'.date('Y-m-d H:i:s', $lastsendtime), 'error' => -1));
        } else {
            $result=$sms->send($mobile, $content);
            return $result;
        }
    }
    
    /**
     * 向手机发送短信
     *
     * @param str $mobile  接收短信的手机号码
     * @param str $content 短信内容
     * @param str $key     发送账号 类别:1.验证码
     * @param str $iscode  是否是发送验证码
     *
     * @return int 1 发送成功; -99 错误的请求; -8 不满足发送条件; -7 短信内容过长; -5 短信服务已到期; -4 用户未开通短信服务或短信服务已暂停; -2 短信已用完; -1 参数错误
     */
    public static function sendSms($mobile, $content, $key='1', $iscode = 1)
    {
        switch ($key) {
        case 1:
        default:
            $account = 'lawyermarketing';
            $pwd = 'marketing2015';
            $iscode = 1;
            break;
        }
        return self::send_mobile_sms($mobile, $content, $account, $pwd, $iscode);
    }
}
