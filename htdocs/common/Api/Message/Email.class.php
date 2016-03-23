<?php
/**
 *  email 发送工具类
 *
 *  @author zsg <xxx@qq.com>
 *
 */
/**
 *  email 发送工具类
 *
 *  @author zsg <xxx@qq.com>
 */
class Email
{
    /**
     * 邮件发送
     *
     * @param string $mailto    收件人,需要同时发给多个帐号时，帐号间用英文逗号(,)隔开
     * @param string $subject   邮件标题
     * @param string $body      邮件正文内容
     * @param int    $try_limit 遇发送失败尝试次数
     *
     * @return unknow
     */
    public static function send($mailto, $subject, $body, $try_limit=3)
    {
        $data = array();
        $data['mailto']  =   $mailto;
        $data['subject'] =   $subject;
        $data['body']    =   $body;
    
        $mail = new \Org\Util\Email();
        //邮件发送，如果遇到发送失效，最多尝试3次
        $try_count = 0;
        while ($try_count < $try_limit) {
            if ($mail->send($data)) {
                // write log
                return true;
            }
            $try_count = $try_count + 1;
        }
    
        return false;
    }
}
