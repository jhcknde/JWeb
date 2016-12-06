<?php
/**
 * BaseModel
 * User: tsy
 * Date: 16/5/30
 * Time: 下午2:32
 */

namespace Cilibs\Pay\Model;

use Cilibs\Pay\Lib\Error;

include_once(dirname(__FILE__)."/../config.php");

class BaseModel {
    protected $appid;
    protected $appkey;
    protected $appsecret;
    protected $tbl_prefix;  //表前缀

    protected $test;    //测试环境

    protected $db;

    public function __construct($app_key, $is_test=false)
    {
        $this->test = $is_test;

        if($this->test) {
            $this->appkey = "";
            $this->tbl_prefix = "";
            $this->appsecret = "appsecret";
            return ;
        }

        if(empty($app_key)) {
            throw new \Exception(Error::getMsg(Error::INVALID_APPKEY), Error::INVALID_APPKEY);
        }

        // 验证app_key
        $admin = new \Cilibs\Admin\AdminWeb();
        $app = $admin->getApp($app_key);

        if(empty($app)) {
            throw new \Exception(Error::getMsg(Error::INVALID_APPKEY), Error::INVALID_APPKEY);
        }

        $this->appid = $app['id'];
        $this->appkey = $app_key;
        $this->appsecret = $app['appsecret'];
        $this->tbl_prefix = 'app_' . $this->appid . '_';
    }

    public function getAppSecret() {
        return $this->appsecret;
    }
}