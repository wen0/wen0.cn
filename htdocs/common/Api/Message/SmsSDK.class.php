<?php
/**
 * Wycms - 短信平台SDK文件
 *
 * 短信发送有此下限制:
 *   1. 重复内容一天内不允许发送
 *   2. 每个号码每天上限为20条
 *   3. 一个号码后台有发送间隔时间,默认为:5分钟
 *   4. 敏感词会使用*来代替
 *   5. 每个帐号都有配额控制
 *
 * 对应状态:
 *   状态       描述
 *    1          成功，is_up=1时返回唯一标识的ID（上行会话ID，可用于持久会话）用于上行会话模式，is_up=0或者is_up参数不传则返回1表示成功
 *    0          写入短信平台数据库失败
 *   -1          参数错误
 *   -2          短信配额已用完
 *   -3          手机号码格式错误
 *   -4          用户未开通短信服务或短信服务已暂停
 *   -5          短信服务已到期
 *   -7          短信内容过长
 *   -8          不满足发送条件（参阅上面的短信限制）
 *   -9          上行会话up_id错误,会话up_id不存在
 *   -10         curl请求超时
 *
 * @author mijian <mijian@lawjob.cn>
 */
// 环境依赖
if (! function_exists('curl_init')) {
    throw new Exception('Snda needs the CURL PHP extension.');
}
if (! function_exists('json_decode')) {
    throw new Exception('Snda needs the JSON PHP extension.');
}


/**
 * Wycms - 短信平台发送SDK类
 *
 * @author mijian <mijian@lawjob.cn>
 */
class smsSDK
{
    private $user,$passwd;
    // 是否使用上行模式
    public $isend   = 0;
    // 上行ID
    public $upid    = 0;
    // 备注
    public $comment = '';
    // 默认超时时间,单位: 秒
    public $timeout    = 30;
    // 发起连接前等待时间
    public $connecttimeout = 30;
    // 设置 useragent
    public $useragent  = 'Wycms-Sms-SDK v1.1';
    // 发送地址
    protected $sendURL = 'http://14.17.121.98:9090/index.php?r=smsSDK/sendMessage';
    // 查询上一次短信发送时间地址
    protected $selURL = 'http://14.17.121.98:9090/index.php?r=smsSDK/selMobileTime';
    // SDK版本,用于版本判断
    protected $version = 'v1.1';
    
    /**
     * 主要参数
     *
     * @param string  $user    用户名
     * @param string  $passwd  密码
     * @param integer $timeout 请求超时时间,单位: 秒
     *
     * @return void
     */
    function __construct ($user,$passwd, $timeout=30)
    {
        $this->user   = $user;
        $this->passwd = $passwd;
        if (intval($timeout)) {
            $this->timeout = intval($timeout);
        }
    }

    /**
     * 依附本次信息的备注信息,发送一条短信只允许备注一次.
     * 注: 备注字符编码必须为: UTF-8
     *
     * @param string $comment 需要备注的信息,如果$comment是数组,将把数组转换成json格式
     *
     * @return 如果设置成功返回true,否则返回false
     */
    function comment ($comment)
    {
        if (is_array($comment)) {
            $this->comment = json_encode($comment);
        } else {
            $this->comment = trim($comment);
        }
        return empty($this->comment) ?false :true;
    }

    /**
     * 查询某时间到当前时间上一次短信发送时间，如果查询起始时间则查询当天短信上一次发送时间
     * 返回值：0表示该时间段内短信账号对应的平台下没有短信发送，大于0为上一次发送时间（UNIX时间戳），其它返回值参考SDK上面的返回状态值
     *
     * @param integer $mobile  接收的号码
     * @param integer $seltime 查询起始时间，必须是UNIX时间戳
     *
     * @return integer
     */
    function getLastSendtime ($mobile, $seltime=0)
    {
        $fields = array();
        $fields['user']   = trim($this->user);
        $fields['passwd'] = $this->passwd;
        if (preg_match('/^[0-9]{11}$/', $mobile)) {
            $fields['mobile'] = $mobile;
        } else {
            //手机格式错误
            return -3;
        }
        $fields['seltime'] = intval($seltime);
        $r = $this->http($fields, $this->selURL);
        $r = json_decode($r, true);
        if (!is_array($r) || empty($r)) {
            return -10;
        }
        if ($r['status'] == 1) {
            return $r['time'];
        } else {
            return $r['status'];
        }
    }

    /**
     * 发送
     *
     * @param integer $mobile  接收的号码
     * @param string  $content 需要发送的内容,内容必须是 UTF-8 编码
     * @param integer $timer   指定时间发送,必须是UNIX时间戳
     *
     * @return 返回状态码
     */
    function send ($mobile, $content, $timer=0)
    {
        $fields = array();
        $fields['json']   = 1;
        $fields['ip'] = $this->getClientIp();
        $fields['user']   = trim($this->user);
        $fields['passwd'] = $this->passwd;
        if (preg_match('/^[0-9]{11}$/', $mobile)) {
            $fields['mobile'] = $mobile;
        } else {
            // 手机格式错误
            return array('status'=>'-3', 'message'=>'Content Or Tel Error');
        }

        $content = trim($content);
        if (!empty($content)) {
            $fields['content'] = trim($content);
        }
        $timer = trim($timer);
        if (preg_match('/^[0-9]{10,}$/', $timer)) {
            $fields['sendtime'] = $timer;
        }

        $infos = array();
        $infos['version']     = $this->version;
        $filename = '';
        if (isset($_SERVER['SCRIPT_FILENAME']) && !empty($_SERVER['SCRIPT_FILENAME'])) {
            $filename = $_SERVER['SCRIPT_FILENAME'];
        } else {
            $filename = $_SERVER['DOCUMENT_URI'];
        }
        $request_url = isset($_SERVER['REQUEST_URI']) ?$_SERVER['REQUEST_URI'] :'';
        $http_host = isset($_SERVER['HTTP_HOST']) ?$_SERVER['HTTP_HOST'] :(isset($_SERVER['SERVER_NAME']) ?$_SERVER['SERVER_NAME'] :'');
        // 用于快速定义错误
        $infos['request_url'] = $http_host.$request_url;
        $infos['filename'] = $filename;
        $fields['infos'] =  json_encode($infos);

        // 上行模式
        if ($this->isend) {
            $fields['is_up'] = 1;
            $upid = intval($this->upid);
            if ($upid) {
                $fields['up_id'] = $this->upid;
            }
            
            // 清除模式
            $this->isend = 0;
        }

        // 备注
        if (!empty($this->comment)) {
            $fields['comment'] = $this->comment;

            $this->comment = '';
        }
        
        $r = $this->http($fields, $this->sendURL);
        $r = json_decode($r, true);
        if (!is_array($r) || empty($r)) {
            return array(
                'status'  => -10,
                'message' => 'Connection timeout (CURL)',
            );
        }
        
        return $r;
    }

    /**
     * 需要回送的发送
     *
     * @param integer $mobile  接收的号码
     * @param string  $content 需要发送的内容,内容必须是 UTF-8 编码
     * @param integer $upid    会话ID,第一次发送时会被返回,继续使用被返回的会话ID可以保持会话
     * @param integer $timer   指定时间发送,必须是UNIX时间戳
     *
     * @return 发送成功返回当然会话ID,失败则返回对应错误码
     */
    function isend ($mobile, $content, $upid=0, $timer=0)
    {
        $this->isend = 1;
        $this->upid  = intval($upid);
        return self::send($mobile, $content, $timer);
    }

    /**
     * HTTP POST 请求
     *
     * @param array  $fields 需要请求的数据
     * @param string $url    请求路径
     * @param string $method 请求方式
     *
     * @return 返回服务器的返回信息
     */
    function http ($fields=null, $url='', $method='POST')
    {
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ci, CURLOPT_HEADER, 0);

        switch ($method) {
        case 'POST' :
            curl_setopt($ci, CURLOPT_POST, 1);
            if (!empty($fields)) {
                curl_setopt($ci, CURLOPT_POSTFIELDS, $fields);
            }
            break;
        }

        $r = curl_exec($ci);
        curl_close($ci);

        return $r;
    }

    /** 
     * 获取用户IP
     *
     * @return integer
     */
    function getClientIP()
    {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown') && strpos(getenv('HTTP_CLIENT_IP'), '192.168')===true) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown') && strpos(getenv('HTTP_X_FORWARDED_FOR'), '192.168')===true) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }   
        preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
        $onlineip = $onlineipmatches[0] ? $onlineipmatches[0] : '127.0.0.1';
        return ip2long($onlineip);
    }

    /**
     * 用于调试
     *
     * @return 调试内容
     */
    function prints ()
    {
    
    }
}
