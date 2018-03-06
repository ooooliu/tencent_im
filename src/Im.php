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
}