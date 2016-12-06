<?php
namespace Cilibs\Pay;

class D extends \Cilibs\Core\Model\D
{
    protected static $config = array(
        array("default", "127.0.0.1:3306", "root", "123456", "nbg_service_pay")
    );
}

class Config {
    //线上版本 把env修改成 production
    public static $env = "develop";
    public static $domin = "http://ciservice.tunnel.nibaguai.com/";
    public static $service_key = "hpbaqs31jsidmncz9ea4fzkrnjcgy6ky";
    public static $service_secret = "pgd4zijz7x1b8hmsnks2egkeacoiaqnm";
}