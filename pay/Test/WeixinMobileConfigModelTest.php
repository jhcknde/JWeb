<?php
/**
 * AlipayMobileConfigModel测试用例
 * User: tsy
 * Date: 16/5/30
 * Time: 下午6:13
 */

namespace Cilibs\Pay\Test;

use Cilibs\Pay\Model\WeixinMobileConfigModel;

class WeixinMobileConfigModelTest
{
    public static function testGet() {
        $model = new WeixinMobileConfigModel("", true);
        var_dump($model->get());
    }

    public static function testInsert() {
        $model = new WeixinMobileConfigModel("", true);
        var_dump($model->insert("wx6b69bdbf2adca4f8", "1307700001",
            "NBG1405GIRLS2016GOGOGOGOGOGOGOGO", 'pemcert.pem', 'pemkey.pem', 'pemca.pem'));
    }

    public static function testUpdate() {
        $model = new WeixinMobileConfigModel("", true);
        var_dump($model->update(1, array("wx_appid"=>"989898989")));
    }
}