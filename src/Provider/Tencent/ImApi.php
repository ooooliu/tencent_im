<?php
/**
 * Created by PhpStorm.
 * User: lyn
 * Date: 2018/3/5
 * Time: 20:40
 */

namespace Ooooliu\Im\Provider\Tencent;


class ImApi extends ImBaseApi
{

    /**
     * ImApi constructor.
     * register configs
     * @throws \Exception
     */
    public function __construct()
    {
        $driver = config('im.driver');

        if(!$driver){
            throw new \Exception('Driver is not null');
        }

        #获取SdkAppId
        $this->app_id = config('im.' . $driver . '.app_id');
        $this->identifier = config('im.' . $driver . '.identifier');
        $this->config_path = config('im.' . $driver . '.config_path');
        $this->private_key = config('im.' . $driver . '.private_key');
        parent::__construct();
    }

    /**
     * 账户导入
     * @param $identifier
     * @param $nick_name
     * @param $face_url
     * @return mixed|string
     */
    public function registerUser($identifier, $nick_name, $face_url)
    {
        //构造新消息
        $msg = [
            'Identifier' => $identifier,
            'Nick' => $nick_name,
            'FaceUrl' => $face_url
        ];
        //将消息序列化为json串
        $data = json_encode($msg);

        $ret = self::api('im_open_login_svc', 'account_import', $identifier, '', $data);
        $ret = json_decode($ret, true);
        return $ret;
    }

    /**
     * 单发信息
     * @param $from_id
     * @param $to_id
     * @param $content
     * @return mixed
     */
    public function sendMsg($from_id, $to_id, $content)
    {
        #构造高级接口所需参数
        $msg_content = [];
        //创建array 所需元素
        $msg_content_elem = [
            'MsgType' => 'TIMTextElem',       //文本类型
            'MsgContent' => [
                'Text' => $content,           //hello 为文本信息
            ]
        ];
        //将创建的元素$msg_content_elem, 加入array $msg_content
        array_push($msg_content, $msg_content_elem);

        $ret = parent::openIMSendMsg($from_id, $to_id, $msg_content);
        return $ret;
    }

    /**
     * 查询用户在线状态
     * @param array $account
     * @return mixed
     * @throws \Exception
     */
    public function queryState($account = [])
    {
        if(empty($account)){
            throw new \Exception('Account is not null');
        }
        $msg = ['To_Account' => $account];
        //将消息序列化为json串
        $data = json_encode($msg);
        $ret = parent::api('openim', 'querystate', $this->identifier, $this->user_sig, $data);

        $ret = json_decode($ret, true);
        return $ret;
    }
}