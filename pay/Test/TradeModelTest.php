<?php
/**
 * TradeModel测试用例
 * User: tsy
 * Date: 16/5/30
 * Time: 下午6:27
 */

namespace Cilibs\Pay\Test;

use Cilibs\Pay\Model\TradeModel;

class TradeModelTest
{
    public static function testGetByTradeNo() {
        $model = new TradeModel("", true);
        var_dump($model->getByTradeNo("123"));
    }

    public static function testCreateTrade() {
        $model = new TradeModel("", true);
        var_dump($model->createTrade("测试物品", 100, TradeModel::TRADETYPE_WEIXINMOBILE, "http://asasas"));
    }

    public static function testUpdateTradeSuccess() {
        $model = new TradeModel("", true);
        var_dump($model->updateTradeSuccess("123", "7896j830k0k3990", "467640669@qq.com"));
    }

    public static function testUpdateTradeFail() {
        $model = new TradeModel("", true);
        var_dump($model->updateTradeFail("20160531191249862231", "错误log"));
    }
}
