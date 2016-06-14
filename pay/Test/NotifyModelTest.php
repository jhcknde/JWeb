<?php
/**
 * NotifyModel测试用例
 * User: tsy
 * Date: 16/6/3
 * Time: 上午10:44
 */

namespace Cilibs\Pay\Test;

use Cilibs\Pay\Model\NotifyModel;

class NotifyModelTest
{
    public static function testgetByNotifyNo() {
        $model = new NotifyModel("", true);
        var_dump($model->getByNotifyNo("20160603104813499007"));
    }

    public static function testcreateNotify() {
        $model = new NotifyModel("", true);
        var_dump($model->createNotify(NotifyModel::NOTIFYTYPE_TRADE, "http://asasas", "content"));
    }

    public static function teststartNotify() {
        $model = new NotifyModel("", true);
        var_dump($model->startNotify("20160603104813499007"));
    }

    public static function testupdateNotifySuc() {
        $model = new NotifyModel("", true);
        var_dump($model->updateNotifySuc("20160603104813499007"));
    }

    public static function testupdateNotifyFail() {
        $model = new NotifyModel("", true);
        var_dump($model->updateNotifyFail("20160603104813499007"));
    }

    public static function testgetNotSucNotify() {
        $model = new NotifyModel("", true);
        var_dump($model->getNotSucNotify(1, 10, NotifyModel::NOTIFYTYPE_TRADE, 8));
    }
}