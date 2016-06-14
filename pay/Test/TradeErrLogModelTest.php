<?php
/**
 * TradeErrLogModel测试用例
 * User: tsy
 * Date: 16/6/2
 * Time: 下午5:10
 */

namespace Cilibs\Pay\Test;

use Cilibs\Pay\Model\TradeErrLogModel;

class TradeErrLogModelTest
{
    public static function testgetByTradeNo() {
        $model = new TradeErrLogModel("", true);
        var_dump($model->getByTradeNo("2088701752529231"));
    }

    public static function testinsertLog() {
        $model = new TradeErrLogModel("", true);
        var_dump($model->insertLog("2088701752529231", "错误msg"));
    }
}