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

}