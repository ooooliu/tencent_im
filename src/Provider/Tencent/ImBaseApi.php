<?php
/**
 * Created by PhpStorm.
 * User: lyn
 * Date: 2018/3/5
 * Time: 21:33
 */

namespace Ooooliu\Im\Provider\Tencent;


class ImBaseApi
{
    protected $app_id;
    protected $identifier;
    protected $config_path;
    protected $private_key;
    protected $user_sig;

    #开放IM https接口参数, 一般不需要修改
    protected $http_type = 'https://';
    protected $method = 'post';
    protected $im_yun_url = 'console.tim.qq.com';
    protected $version = 'v4';
    protected $content_type = 'json';
    protected $apn = '0';
    #一个月请求一次(注意：生成的sig有效期为180天，开发者需要在sig过期前，重新生成sig)
    protected $expiry = 30 * 24 * 60;
    protected $usersig_cache_key = 'usersig';

    /**
     * IMBaseApi constructor.
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
        #获取usersig
        $this->user_sig = self::getSignature($this->identifier);
    }
}