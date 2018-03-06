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

    public function __construct($provider)
    {
        if(!file_exists(__DIR__ . '/Provider/' . ucfirst($provider) . '/ImApi.php')){
            throw new \Exception('Provider ' . $provider . ' is not found');
        }

        $class = __NAMESPACE__ . '\\Provider\\'  .ucfirst($provider) . '\\ImApi';

        return new $class();
    }
}