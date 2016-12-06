<?php
/**
 * AlipayPayAction测试用例
 * User: tsy
 * Date: 16/5/31
 * Time: 下午6:57
 */

namespace Cilibs\Pay\Test;

use Cilibs\Pay\Action\AlipayAction;

class AlipayActionTest {

    public static function testgeneratePayParams() {
        echo AlipayAction::generatePayParams("asas", "2012331222", "支付宝支付", "支付宝支付detail", 12, true);
    }
}