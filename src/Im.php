<?php
/**
 * Created by PhpStorm.
 * User: lyn
 * Date: 2018/3/5
 * Time: 20:33
 */

namespace Ooooliu\Im;


use Ooooliu\Im\Contracts\ProviderInterface;

class Im implements ProviderInterface
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

    public function registerUser($identifier, $nick_name, $face_url)
    {
        $this->dirver->registerUser($identifier, $nick_name, $face_url);
    }

    public function sendMsg($from_id, $to_id, $content)
    {
        $this->dirver->sendMsg($from_id, $to_id, $content);
    }
}