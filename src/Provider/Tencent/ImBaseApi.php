<?php
/**
 * Created by PhpStorm.
 * User: lyn
 * Date: 2018/3/5
 * Time: 21:33
 */

namespace Ooooliu\Im\Provider\Tencent;


class ImBaseApi
{
    protected $app_id;
    protected $identifier;
    protected $config_path;
    protected $private_key;
    protected $user_sig;
    #一个月请求一次(注意：生成的sig有效期为180天，开发者需要在sig过期前，重新生成sig)
    protected $expiry = 30 * 24 * 60;
    protected $usersig_cache_key = 'usersig';

    #开放IM https接口参数, 一般不需要修改
    protected $http_type = 'https://';
    protected $method = 'post';
    protected $im_yun_url = 'console.tim.qq.com';
    protected $version = 'v4';
    protected $content_type = 'json';
    protected $apn = '0';

    /**
     * IMBaseApi constructor.
     * register configs
     * @throws \Exception
     */
    public function __construct()
    {
        if(!$this->app_id){
            throw new \Exception('Please set app_id');
        }
        if(!$this->identifier){
            throw new \Exception('Please set identifier');
        }
        if(!$this->private_key){
            throw new \Exception('Please set private_key');
        }
        #获取user_sig
        $this->user_sig = self::getSignature($this->identifier);
    }

    /**
     * 获取usersig
     * 36000为usersig的保活期
     * signature为获取私钥脚本，详情请见 账号登录集成 http://avc.qcloud.com/wiki2.0/im/
     * @param $identifier 管理者身份
     * @return mixed|string
     * @throws \Exception
     */
    public function getSignature($identifier)
    {
        if(!$this->usersig_cache_key){
            throw new \Exception('Please set usersig_cache_key');
        }

        $user_sig = \Cache::get($this->usersig_cache_key);

        if($user_sig) return $user_sig;

        #获取私钥地址
        $private_pem_path = $this->config_path . $this->private_key;

        if(!file_exists($private_pem_path)){
            throw new \Exception('Please import private_pem_path');
        }

        #获取环境操作系统
        if(self::is_64bit()){
            if(PATH_SEPARATOR == ':'){
                $system = 'signature/linux-signature64';
            }else{
                $system = 'signature\\windows-signature64.exe';
            }
        }else{
            if(PATH_SEPARATOR == ':'){
                $system = 'signature/linux-signature32';
            }else{
                $system = 'signature\\windows-signature32.exe';
            }
        }
        $signature = $this->config_path . $system;

        $ret = self::generateUserSig($identifier, $private_pem_path, $signature);
        if($ret == null){
            throw new \Exception('获取usrsig失败, 请确保TimRestApiConfig.json配置信息正确');
        }

        \Cache::put($this->usersig_cache_key, $ret, $this->expiry);
        return $ret;
    }

    /**
     * 清除usersig缓存
     * @return bool
     */
    public function forgetSignatureCache()
    {
        return \Cache::delete($this->usersig_cache_key);
    }

    /**
     * 独立模式根据Identifier生成UserSig的方法
     * @param int $identifier 用户账号
     * @param string $protected_key_path 私钥的存储路径及文件名
     * @return string $out 返回的签名字符串
     */
    private function generateUserSig($identifier, $protected_key_path, $tool_path)
    {
        # 这里需要写绝对路径，开发者根据自己的路径进行调整
        $command = escapeshellarg($tool_path)
            . ' '. escapeshellarg($protected_key_path)
            . ' ' . escapeshellarg($this->app_id)
            . ' ' .escapeshellarg($identifier);

        $ret = exec($command, $out, $status);
        if( $status == -1) {
            return null;
        }
        return $ret;
    }

    public function openIMSendMsg($from_id, $to_id, $msg_content)
    {
        #构造新消息
        $msg = [
            'To_Account' => $from_id,
            'MsgSeq' => rand(1, 65535),
            'MsgRandom' => rand(1, 65535),
            'MsgTimeStamp' => time(),
            'MsgBody' => $msg_content,
            'From_Account' => $to_id
        ];
        #将消息序列化为json串
        $req_data = json_encode($msg);

        $ret = self::api('openim', 'sendmsg', $req_data);
        $ret = json_decode($ret, true);
        return $ret;
    }

    /**
     * 批量发送消息
     * @param $account_list
     * @param $msg_content
     * @return mixed|string
     */
    public function openImBatchSendMsg($account_list, $msg_content)
    {
        #构造新消息
        $msg = [
            'To_Account' => $account_list,
            'MsgRandom' => rand(1, 65535),
            'MsgBody' => $msg_content,
        ];
        #将消息序列化为json串
        $req_data = json_encode($msg);

        $ret = self::api('openim', 'batchsendmsg', $req_data);
        $ret = json_decode($ret, true);
        return $ret;
    }

    /**
     * 创建群组
     * @param $group_type
     * @param $group_name
     * @param $owner_id
     * @param $info_set
     * @param $mem_list
     * @return mixed|string
     */
    public function createGroupApi($group_type, $group_name, $owner_id, $info_set, $mem_list)
    {
        #构造新消息
        $msg = array(
            'Type' => $group_type,
            'Name' => $group_name,
            'Owner_Account' => $owner_id,
            'GroupId' => $info_set['group_id'],
            'Introduction' => $info_set['introduction'],
            'Notification' => $info_set['notification'],
            'FaceUrl' => $info_set['face_url'],
            'MaxMemberCount' => $info_set['max_member_num'],
            //	'ApplyJoinOption' => $info_set['apply_join'],
            'MemberList' => $mem_list
        );
        #将消息序列化为json串
        $req_data = json_encode($msg);

        $ret = self::api('group_open_http_svc', 'create_group', $req_data);
        $ret = json_decode($ret, true);
        return $ret;
    }

    /**
     * 发送群组消息
     * @param $account_id
     * @param $group_id
     * @param $msg_content
     * @return mixed|string
     */
    public function sendGroupMsgApi($account_id, $group_id, $msg_content)
    {
        #构造新消息
        $msg = [
            'GroupId' => $group_id,
            'From_Account' => $account_id,
            'Random' => rand(1, 65535),
            'MsgBody' => $msg_content
        ];

        #将消息序列化为json串
        $req_data = json_encode($msg);

        $ret = self::api('group_open_http_svc', 'send_group_msg', $req_data);
        $ret = json_decode($ret, true);
        return $ret;
    }

    /**
     * 在群组中发送系统通知
     * @param $group_id
     * @param $content
     * @param $receiver_list
     * @return mixed|string
     */
    public function sendGroupSystemNotificationApi($group_id, $content, $receiver_list = [])
    {
        #构造新消息
        $msg = [
            'GroupId' => $group_id,
            'ToMembers_Account' => $receiver_list,
            'Content' => $content,
        ];
        #将消息序列化为json串
        $req_data = json_encode($msg);

        $ret = self::api("group_open_http_svc", "send_group_system_notification", $req_data);
        $ret = json_decode($ret, true);
        return $ret;
    }

    /**
     * 判断操作系统位数
     */
    protected function is_64bit() {
        $int = "9223372036854775807";
        $int = intval($int);
        if ($int == 9223372036854775807) {
            /* 64bit */
            return true;
        }
        elseif ($int == 2147483647) {
            /* 32bit */
            return false;
        }
        else {
            /* error */
            return "error";
        }
    }

    /**
     * 构造访问REST服务器的参数,并访问REST接口
     * @param string $server_name 服务名
     * @param string $cmd_name 命令名
     * @param string $req_data 传递的json结构
     * $param bool $print_flag 是否打印请求，默认为打印
     * @return string $out 返回的签名字符串
     */
    protected function api($service_name, $cmd_name, $req_data, $print_flag = true)
    {
        //$req_tmp用来做格式化输出
        $req_tmp = json_decode($req_data, true);
        # 构建HTTP请求参数，具体格式请参考 REST API接口文档 (http://avc.qcloud.com/wiki/im/)(即时通信云-数据管理REST接口)
        $parameter =  "usersig=" . $this->user_sig
            . "&identifier=" . $this->identifier
            . "&sdkappid=" . $this->app_id
            . "&contenttype=" . $this->content_type;

        $url = $this->http_type . $this->im_yun_url . '/' . $this->version . '/' . $service_name . '/' .$cmd_name . '?' . $parameter;

        if($print_flag){
            echo "Request Url:\n";
            echo $url;
            echo "\n";
            echo "Request Body:\n";
            echo self::jsonFormat($req_tmp);
            echo "\n";
        }

        $ret = self::sendHttpRequset('https', 'post', $url, $req_data);
        return $ret;
    }

    /**
     * json_formart辅助函数
     * @param String $val 数组元素
     */
    protected function jsonFormatProtect(&$val)
    {
        if($val!==true && $val!==false && $val!==null)
        {
            $val = urlencode($val);
        }
    }

    /**
     * 格式化数据
     * @param $data
     * @param null $indent
     * @return string
     */
    protected function jsonFormat($data, $indent = null)
    {
        // 对数组中每个元素递归进行urlencode操作，保护中文字符
        array_walk_recursive($data, [$this, 'jsonFormatProtect']);

        // json encode
        $data = json_encode($data);

        // 将urlencode的内容进行urldecode
        $data = urldecode($data);

        // 缩进处理
        $ret = '';
        $pos = 0;
        $length = strlen($data);
        $indent = isset($indent)? $indent : '    ';
        $newline = "\n";
        $prevchar = '';
        $outofquotes = true;
        for($i=0; $i<=$length; $i++){
            $char = substr($data, $i, 1);
            if($char == '"' && $prevchar != '\\'){
                $outofquotes = !$outofquotes;
            }elseif(($char == '}' || $char == ']') && $outofquotes){
                $ret .= $newline;
                $pos --;
                for($j=0; $j<$pos; $j++){
                    $ret .= $indent;
                }
            }
            $ret .= $char;
            if(($char == ',' || $char == '{' || $char== '[') && $outofquotes){
                $ret .= $newline;
                if($char == '{' || $char == '['){
                    $pos ++;
                }

                for($j=0; $j<$pos; $j++){
                    $ret .= $indent;
                }
            }
            $prevchar = $char;
        }
        return $ret;
    }

    /**
     * 向 Rest服务器发送请求
     * @param string $http_type http类型,比如https
     * @param string $method 请求方式，比如POST
     * @param string $url 请求的url
     * @param string $data 请求的数据
     * @return mixed|string
     */
    private static function sendHttpRequset($http_type, $method, $url, $data)
    {
        $ch = curl_init();
        if (strstr($http_type, 'https')) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        else {
            $url = $url . '?' . $data;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,100000);//超时时间

        try {
            $ret = curl_exec($ch);
        }
        catch(\Exception $e) {
            curl_close($ch);
            return json_encode([
                'ret' => 0,
                'msg' => 'failure'
            ]);
        }
        curl_close($ch);
        return $ret;
    }
}