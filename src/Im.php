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
        if(!file_exists(__DIR__ . '/Provider/' . ucfirst($provider) . '/Api.php')){
            throw new \Exception('Provider ' . $provider . ' is not found');
        }

        $class = __NAMESPACE__ . '\\'  .ucfirst($provider) . '\\Api';

        return new $class();
    }
}