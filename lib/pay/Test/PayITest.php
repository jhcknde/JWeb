<?php
/**
 * PayI测试用例
 * User: tsy
 * Date: 16/5/31
 * Time: 下午2:07
 */

namespace Cilibs\Pay\Test;

use Cilibs\Pay\PayI;

class PayITest {
    public static function testcreateWeixinMobileTrade() {
        $trade_ret_json = PayI::createWeixinMobileTrade("qwqw", "微信支付", "微信支付detail", 1, "127.0.0.1", "http://192.168.2.128/test_notify.php", true);
        $trade_ret = json_decode($trade_ret_json, true);
        var_dump($trade_ret['pay_param']);
    }

    public static function testcreateAlipayMobileTrade() {
        $trade_ret_json = PayI::createAlipayMobileTrade("qwqw", "支付宝支付测试", "支付宝支付测试detail", 1, "http://192.168.2.128/test_notify.php", true);
        $trade_ret = json_decode($trade_ret_json, true);
        var_dump($trade_ret['pay_param']);
    }

    public static function testdoRefund() {
        echo PayI::doRefund("qwqw", "20160531143013044379", 1, true);
    }
}