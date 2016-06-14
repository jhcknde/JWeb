<?php
/**
 * Alipay相关Action
 * User: tsy
 * Date: 16/5/31
 * Time: 下午6:28
 */

namespace Cilibs\Pay\Action;

use Cilibs\Pay\Config;
use Cilibs\Pay\Model\AlipayMobileConfigModel;
use Cilibs\Pay\Lib\Func;
use Cilibs\Pay\Model\TradeModel;

class AlipayAction {

    /**
     * 生成支付宝支付参数
     * @param $app_key appkey
     * @param $trade_no 交易单号
     * @param $subject 交易subject
     * @param $detail 交易detail
     * @param $total_fee 交易金额 单位分
     * @param bool $test
     * @return
     *      成功 string params
     *      失败 int:
     *          -1 配置文件缺失
     *          -2 金额错误
     *          -3 签名私钥错误
     */
    public static function generatePayParams($app_key, $trade_no, $subject, $detail, $total_fee, $test=false) {

        $alipay_mobile_config_model = new AlipayMobileConfigModel($app_key, $test);
        $config = $alipay_mobile_config_model->get();
        if(empty($config)) {
            return -1;
        }

        $fee_yuan = Func::fen_to_yuan($total_fee);
        if(!$fee_yuan) {
            return -2;
        }

        $rsa = $config['alipay_rsa'];

        $data = array();
        $data['service'] = "mobile.securitypay.pay";
        $data['partner'] = $config['alipay_partnerid'];
        $data['_input_charset'] = "utf-8";
        $data['notify_url'] = Config::$domin . "/pay/main.php/json/pay_notify/notifyAlipay/" .
            TradeModel::TRADETYPE_ALIPAYMOBILE . "/{$app_key}/{$test}";
        $data['out_trade_no'] = $trade_no;
        $data['subject'] = $subject;
        $data['payment_type'] = "1";
        $data['seller_id'] = $config['alipay_sellerid'];
        $data['total_fee'] = $fee_yuan;
        $data['body'] = $detail;

        //签名
        $unsign_str = Func::createLinkString(Func::argSort($data));
        $sign = Func::rsa_sign($unsign_str, $rsa);
        if(!$sign) {
            return -3;
        }
        $sign = urlencode(mb_convert_encoding($sign, "UTF-8"));

        //拼接签名参数
        $params = $unsign_str . "&sign=" . $sign . "&sign_type=RSA";

        return $params;
    }
}