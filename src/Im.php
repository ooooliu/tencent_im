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
    public $dirver;

    public function __construct($provider)
    {
        if(!file_exists(__DIR__ . '/Provider/' . ucfirst($provider) . '/ImApi.php')){
            throw new \Exception('Provider ' . $provider . ' is not found');
        }

        $class = __NAMESPACE__ . '\\Provider\\'  .ucfirst($provider) . '\\ImApi';

        $this->dirver = new $class();
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
        return $this->dirver->registerUser($account, $nick_name, $face_url);
    }

    /**
     * 批量注册用户
     * @param array $accounts
     * @return mixed
     */
    public function multRegisterUser($accounts = [])
    {
        return $this->dirver->multRegisterUser($accounts);
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
        return $this->dirver->sendMsg($from_id, $to_id, $content);
    }

    /**
     * 发送批量消息
     * @param array $account_list
     * @param $text_content
     * @return mixed
     */
    public function batchSendMsg($account_list = [], $text_content)
    {
        return $this->dirver->batchSendMsg($account_list = [], $text_content);
    }

    /**
     * 创建群组
     * @param $owner_id
     * @param $group_name
     * @return mixed
     */
    public function createGroup($owner_id, $group_name)
    {
        return $this->dirver->createGroup($owner_id, $group_name);
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
        return $this->dirver->sendGroupMsg($account_id, $group_id, $text_content);
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
        return $this->dirver->sendGroupSystemNotification($group_id, $text_content, $receiver_id);
    }

    /**
     * 查询用户是否在线
     * @param array $account
     * @return mixed
     */
    public function queryState($account = [])
    {
        return $this->dirver->queryState($account = []);
    }
}