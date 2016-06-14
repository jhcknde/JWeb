<?php
/**
 * NotifyAction测试用例
 * User: tsy
 * Date: 16/6/3
 * Time: 上午11:48
 */

namespace Cilibs\Pay\Test;

use Cilibs\Pay\Action\NotifyAction;

class NotifyActionTest {

    public static function testcreateTradeNotify() {
        var_dump(NotifyAction::createTradeNotify("asas", "http://asasasasadddd", "SUCCESS",
            "OK", "203283203232", '2015-05-23 11:11:11', '2015-05-23 11:12:15', true));
    }

    public static function testdoSendNotify() {
        var_dump(NotifyAction::doSendNotify("asas", "20160603115640471441", true));
    }

    public static function testsignNotify() {
        $param = array();
        $param['return_code'] = "SUCCESS";
        $param['return_msg'] = "OK";
        $param['trade_no'] = "203283203232";
        $param['trade_createtime'] = '2015-05-23 11:11:11';
        $param['trade_endtime'] = '2015-05-23 11:12:15';

        var_dump(NotifyAction::signNotify("appsecret", $param));
    }

    public static function testvertifyNotify() {
        $param = array();
        $param['return_code'] = "SUCCESS";
        $param['return_msg'] = "OK";
        $param['trade_no'] = "20160601162810986706";
        $param['trade_createtime'] = '2016-06-01 08:28:10';
        $param['trade_endtime'] = '2016-06-03 14:04:38';
        $param['notify_no'] = '20160603140438899863';
        $param['notify_time'] = '2016-06-03 14:04:38';
        $param['sign'] = 'D7D034488C7F130E72C9E2CCE5ABF9C7';

        var_dump(NotifyAction::vertifyNotify("appsecret", $param));
    }

    public static function testdoCrontabNotify() {
        NotifyAction::doCrontabNotify(true);
    }
}