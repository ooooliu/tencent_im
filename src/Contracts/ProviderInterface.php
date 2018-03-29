<?php
/**
 * Created by PhpStorm.
 * User: lyn
 * Date: 2018/3/6
 * Time: 10:48
 */

namespace Ooooliu\Im\Contracts;


interface ProviderInterface
{

    /**
     * send a message.
     *
     * @param $from_id
     * @param $to_id
     * @param $content
     * @return mixed
     */
    public function sendMsg($from_id, $to_id, $content);

    /**
     * send batch message.
     *
     * @param array $account_list
     * @param $text_content
     * @return mixed
     */
    public function batchSendMsg($account_list = [], $text_content);

    /**
     * add group member
     *
     * @param $group_id
     * @param $member_list
     * @param int $silence
     * @return mixed
     */
    public function addGroupMember($group_id, $member_list, $silence = 0);

    /**
     * delete group member
     *
     * @param $group_id
     * @param $member_list
     * @param int $silence
     * @return mixed
     */
    public function deleteGroupMember($group_id, $member_list, $silence = 0);

}