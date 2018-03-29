<?php
/**
 * Created by PhpStorm.
 * User: lyn
 * Date: 2018/3/5
 * Time: 20:40
 */

namespace Ooooliu\Im\Provider\Tencent;


use Ooooliu\Im\Contracts\ProviderInterface;

class ImApi extends ImBaseApi implements ProviderInterface
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
     * @param $account
     * @param $nick_name
     * @param $face_url
     * @return mixed|string
     */
    public function registerUser($account, $nick_name, $face_url)
    {
        //构造新消息
        $msg = [
            'Identifier' => (string)$account,
            'Nick' => $nick_name,
            'FaceUrl' => $face_url
        ];
        //将消息序列化为json串
        $data = json_encode($msg);

        $ret = parent::api('im_open_login_svc', 'account_import', $data);
        $ret = json_decode($ret, true);
        return $ret;
    }

    /**
     * 批量账户导入
     * @param array $accounts
     * @return mixed|string
     * @throws \Exception
     */
    public function multRegisterUser($accounts = [])
    {
        if(empty($accounts) || !is_array($accounts)){
            throw new \Exception('Account is wrong');
        }

        #最多一次批量添加100
        $max = 100;

        if(count($accounts) > $max){
            throw new \Exception('The maximum number of users can not be over 100');
        }

        $msg = [
            'Accounts' => (string)$accounts
        ];
        //将消息序列化为json串
        $data = json_encode($msg);

        $ret = parent::api('im_open_login_svc', 'multiaccount_import', $data);
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
                'Text' => (string)$content,           //hello 为文本信息
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
        $ret = parent::api('openim', 'querystate', $data);

        $ret = json_decode($ret, true);
        return $ret;
    }

    /**
     * 批量发信息
     * @param array $account_list
     * @param $text_content
     * @return mixed|string
     * @throws \Exception
     */
    public function batchSendMsg($account_list = [], $text_content)
    {
        if(empty($account_list) || !is_array($account_list)){
            throw new \Exception('account_list is wrong');
        }

        #构造高级接口所需参数
        $msg_content = [];
        //创建array 所需元素
        $msg_content_elem = [
            'MsgType' => 'TIMTextElem',       //文本类型
            'MsgContent' => [
                'Text' => (string)$text_content,      //hello 为文本信息
            ]
        ];
        //将创建的元素$msg_content_elem, 加入array $msg_content
        array_push($msg_content, $msg_content_elem);

        $ret = parent::openImBatchSendMsg($account_list, $msg_content);
        return $ret;
    }

    /**
     * 创建群组
     * @param $owner_id
     * @param $group_name
     * @param string $group_type
     * @return mixed|string
     */
    public function createGroup($owner_id, $group_name, $group_type = 'Public')
    {
        #构造高级接口所需参数
        $info_set = [
            'group_id' => null,             //用户自定义的群组ID(选填)
            'introduction' => null,         //群简介(选填)
            'notification' => null,         //群公告(选填)
            'face_url' => null,             //群头像
            'max_member_num' => 500
        ];
        $mem_list = [];

        $ret = parent::createGroupApi($group_type, $group_name, $owner_id, $info_set, $mem_list);
        return $ret;
    }

    /**
     * 发送群组消息
     * @param $account_id
     * @param $group_id 腾讯群组ID
     * @param $text_content
     * @return mixed|string
     */
    public function sendGroupMsg($account_id, $group_id, $text_content)
    {
        #构造高级接口所需参数
        $msg_content = [];
        //创建array 所需元素
        $msg_content_elem = [
            'MsgType' => 'TIMTextElem',                 //文本类型
            'MsgContent' => [
                'Text' => (string)$text_content,                //hello 为文本信息
            ]
        ];
        array_push($msg_content, $msg_content_elem);
        $ret = parent::sendGroupMsgApi($account_id, $group_id, $msg_content);
        return $ret;
    }

    /**
     * 在群组中发送系统通知
     * @param $group_id
     * @param $text_content
     * @param $receiver_id
     * @return mixed|string
     */
    public function sendGroupSystemNotification($group_id, $text_content, $receiver_id)
    {
        #构造高级接口所需参数
        $receiver_list = [];
        if($receiver_id != null){
            array_push($receiver_list, $receiver_id);
        }
        $ret = parent::sendGroupSystemNotificationApi($group_id, $text_content, $receiver_list);
        return $ret;
    }

    /**
     * 增加群组成员
     * @param $group_id
     * @param $member_list
     * @param int $silence 是否静默加人。0：非静默加人；1：静默加人。不填该字段默认为0
     * @return mixed|string
     * @throws \Exception
     */
    public function addGroupMember($group_id, $member_list, $silence = 0)
    {
        #最多一次批量添加500
        $max = 500;

        if(!is_array($member_list)){
            $member_list = explode(',', $member_list);
        }

        if(count($member_list) > $max){
            throw new \Exception('The maximum number of users can not be over 500');
        }

        #构造新消息
        $mem_list =[];
        foreach ($member_list as $member_id){
            $mem_list[]['Member_Account'] = $member_id;
        }

        $msg = [
            'GroupId' => $group_id,
            'MemberList' => $mem_list,
            'Silence' => $silence
        ];
        #将消息序列化为json串
        $req_data = json_encode($msg);

        $ret = parent::api('group_open_http_svc', 'add_group_member', $req_data);
        $ret = json_decode($ret, true);
        return $ret;
    }

    /**
     * 获取用户再群组中的角色
     * @param $group_id
     * @param $member_list
     * @return mixed|string
     * @throws \Exception
     */
    public function getRoleInGroup($group_id, $member_list)
    {
        #最多支持500个
        $max = 500;

        if(!is_array($member_list)){
            $member_list = explode(',', $member_list);
        }

        if(count($member_list) > $max){
            throw new \Exception('The maximum number of users can not be over 500');
        }

        $msg = [
            'GroupId' => $group_id,
            'User_Account' => $member_list,
        ];
        #将消息序列化为json串
        $req_data = json_encode($msg);

        $ret = parent::api('group_open_http_svc', 'get_role_in_group', $req_data);
        $ret = json_decode($ret, true);
        return $ret;
    }

    /**
     * 批量禁言和取消禁言
     * @param $group_id
     * @param $member_list
     * @param $second [0:取消禁言 大于0:设置禁言时间]
     * @return mixed|string
     */
    public function groupForbidSendMsg($group_id, $member_list, $second = 0)
    {
        if(!is_array($member_list)){
            $member_list = explode(',', $member_list);
        }

        $msg = [
            'GroupId' => $group_id,
            'Members_Account' => $member_list,
            'ShutUpTime' => $second
        ];

        #将消息序列化为json串
        $req_data = json_encode($msg);

        $ret = parent::api('group_open_http_svc', 'forbid_send_msg', $req_data);
        $ret = json_decode($ret, true);
        return $ret;
    }

    /**
     * 获取群组被禁言用户列表
     * @param $group_id
     * @return mixed|string
     */
    public function getGroupShuttedUin($group_id)
    {
        $msg = [
            'GroupId' => $group_id
        ];

        #将消息序列化为json串
        $req_data = json_encode($msg);
        $ret = parent::api('group_open_http_svc', 'get_group_shutted_uin', $req_data);
        $ret = json_decode($ret, true);
        return $ret;
    }

    /**
     * 删除群组成员
     * @param $group_id
     * @param $member_list
     * @param int $silence 是否静默删人。0：非静默删人；1：静默删人。不填该字段默认为0。
     * @return mixed|string
     * @throws \Exception
     */
    public function deleteGroupMember($group_id, $member_list, $silence = 0)
    {
        #最多支持500个
        $max = 500;

        if(!is_array($member_list)){
            $member_list = explode(',', $member_list);
        }

        if(count($member_list) > $max){
            throw new \Exception('The maximum number of users can not be over 500');
        }

        $msg = [
            'GroupId' => $group_id,
            'MemberToDel_Account' => $member_list,
            'Silence' => $silence
        ];

        #将消息序列化为json串
        $req_data = json_encode($msg);

        $ret = parent::api('group_open_http_svc', 'delete_group_member', $req_data);
        $ret = json_decode($ret, true);
        return $ret;
    }
}