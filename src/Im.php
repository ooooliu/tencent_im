<?php
/**
 * Created by PhpStorm.
 * User: lyn
 * Date: 2018/3/5
 * Time: 20:33
 */

namespace Ooooliu\Im;


class Im
{
    public $driver;

    public function __construct()
    {
        $provider = config('im.driver');

        if(!$provider){
            throw new \Exception('Config driver is not null');
        }

        if(!file_exists(__DIR__ . '/Provider/' . ucfirst($provider) . '/ImApi.php')){
            throw new \Exception('Provider ' . $provider . ' is not found');
        }

        $class = __NAMESPACE__ . '\\Provider\\'  .ucfirst($provider) . '\\ImApi';

        $this->driver = new $class();
    }

    /**
     * 注册用户
     * @param $account
     * @param $nick_name
     * @param $face_url
     * @return mixed
     */
    public function registerUser($account, $nick_name, $face_url)
    {
        return $this->driver->registerUser($account, $nick_name, $face_url);
    }

    /**
     * 批量注册用户
     * @param array $accounts
     * @return mixed
     */
    public function multRegisterUser($accounts = [])
    {
        return $this->driver->multRegisterUser($accounts);
    }

    /**
     * 发送单聊消息
     * @param $from_id
     * @param $to_id
     * @param $content
     * @return mixed
     */
    public function sendMsg($from_id, $to_id, $content)
    {
        return $this->driver->sendMsg($from_id, $to_id, $content);
    }

    /**
     * 发送批量消息
     * @param array $account_list
     * @param $text_content
     * @return mixed
     */
    public function batchSendMsg($account_list = [], $text_content)
    {
        return $this->driver->batchSendMsg($account_list = [], $text_content);
    }

    /**
     * 创建群组
     * @param $owner_id
     * @param $group_name
     * @return mixed
     */
    public function createGroup($owner_id, $group_name)
    {
        return $this->driver->createGroup($owner_id, $group_name);
    }

    /**
     * 发送群组消息
     * @param $account_id
     * @param $group_id
     * @param $text_content
     * @return mixed
     */
    public function sendGroupMsg($account_id, $group_id, $text_content)
    {
        return $this->driver->sendGroupMsg($account_id, $group_id, $text_content);
    }

    /**
     * 在群组中发送系统通知
     * @param $group_id
     * @param $text_content
     * @param $receiver_id
     * @return mixed
     */
    public function sendGroupSystemNotification($group_id, $text_content, $receiver_id)
    {
        return $this->driver->sendGroupSystemNotification($group_id, $text_content, $receiver_id);
    }

    /**
     * 查询用户是否在线
     * @param array $account
     * @return mixed
     */
    public function queryState($account = [])
    {
        return $this->driver->queryState($account);
    }

    /**
     * 获取用户user_sig
     * @param $identifier
     * @return mixed
     */
    public function getSignature($identifier)
    {
        return $this->driver->getSignature($identifier);
    }

    /**
     * 删除用户user_sig缓存
     * @param $identifier
     * @return mixed
     */
    public function forgetSignatureCache($identifier = '')
    {
        if(empty($identifier)){
            $identifier = config('im.' . config('im.driver') . '.identifier');
        }
        return $this->driver->forgetSignatureCache($identifier);
    }

    /**
     * 增加群组成员
     * @param $group_id
     * @param $member_list string:user; array:[user1,user2,user3..]
     * @param int $silence 是否静默加人。0：非静默加人；1：静默加人。不填该字段默认为0
     * @return mixed
     */
    public function addGroupMember($group_id, $member_list, $silence = 0)
    {
        return $this->driver->addGroupMember($group_id, $member_list, $silence);
    }

    /**
     * 获取用户再群组中的角色
     * @param $group_id
     * @param $member_list
     * @return mixed
     */
    public function getRoleInGroup($group_id, $member_list)
    {
        return $this->driver->getRoleInGroup($group_id, $member_list);
    }

    /**
     * 批量禁言和取消禁言
     * @param $group_id
     * @param $member_list
     * @param int $second
     * @return mixed
     */
    public function groupForbidSendMsg($group_id, $member_list, $second = 0)
    {
        return $this->driver->groupForbidSendMsg($group_id, $member_list, $second);
    }

    /**
     * 获取群组被禁言用户列表
     * @param $group_id
     * @return mixed
     */
    public function getGroupShuttedUin($group_id)
    {
        return $this->driver->getGroupShuttedUin($group_id);
    }
}