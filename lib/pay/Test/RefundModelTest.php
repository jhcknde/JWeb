<?php
/**
 * RefundModel测试用例
 * User: tsy
 * Date: 16/5/31
 * Time: 上午10:33
 */

namespace Cilibs\Pay\Test;

use Cilibs\Pay\Model\RefundModel;

class RefundModelTest
{
    public static function testgetByRefundNo() {
        $model = new RefundModel("", true);
        var_dump($model->getByRefundNo("20160531105341744445"));
    }

    public static function testgetByTradeNo() {
        $model = new RefundModel("", true);
        var_dump($model->getByTradeNo("20160531105242395959"));
    }

    public static function testgetNotFailRefundByTradeNo() {
        $model = new RefundModel("", true);
        var_dump($model->getNotFailRefundByTradeNo("20160531105242395959"));
    }

    public static function testcreateRefund() {
        $model = new RefundModel("", true);
        var_dump($model->createRefund("20160531105242395959", 100));
    }

    public static function testupdateRefundSuccess() {
        $model = new RefundModel("", true);
        var_dump($model->updateRefundSuccess("20160531105341744445"));
    }

    public static function testupdateRefundFail() {
        $model = new RefundModel("", true);
        var_dump($model->updateRefundFail("20160531105356190832"));
    }
}