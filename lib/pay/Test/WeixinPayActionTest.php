<?php
/**
 * WeixinPayAction测试用例
 * User: tsy
 * Date: 16/5/31
 * Time: 下午3:55
 */

namespace Cilibs\Pay\Test;

use Cilibs\Pay\Action\WeixinPayAction;

class WeixinPayActionTest {

    public static function testunifiedOrder() {
        echo WeixinPayAction::unifiedOrder("12121212", "2011231212", "微信支付",
            "微信支付detail", 123, "192.168.2.12", true);
    }
}