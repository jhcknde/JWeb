<?php
/**
 * 微信支付Action
 * User: tsy
 * Date: 16/5/31
 * Time: 下午3:04
 */

namespace Cilibs\Pay\Action;

use Cilibs\Pay\Config;
use Cilibs\Pay\Model\WeixinMobileConfigModel;
use Cilibs\Pay\Lib\Func;
use Cilibs\Pay\Lib\Curl;
use Cilibs\Pay\Model\TradeModel;

class WeixinPayAction {

    const UNIFIED_ORDER_URL = "https://api.mch.weixin.qq.com/pay/unifiedorder";

    /**
     * 微信生成支付参数
     * @param $app_key appkey
     * @param $trade_no 交易单号
     * @param $subject 交易subject
     * @param $detail 交易detail
     * @param $total_fee 交易金额 单位分
     * @param $client_ip 终端ip
     * @param bool $test
     * @return
     *      成功 string pay_param
     *      失败 int:
     *          -1 配置文件缺失
     *          -2 统一下单失败
     */
    public static function generatePayParams($app_key, $trade_no, $subject, $detail, $total_fee,
                                             $client_ip, $test=false) {

        $weixin_mobile_config_model = new WeixinMobileConfigModel($app_key, $test);
        $config = $weixin_mobile_config_model->get();
        if(empty($config)) {
            return -1;
        }

        //微信统一下单
        $order_ret = WeixinPayAction::unifiedOrder($app_key, $trade_no, $subject, $detail, $total_fee,
            $client_ip, $config, $test);
        if(!is_string($order_ret)) {
            return $order_ret;
        }

        //生成支付参数
        $wx_key = $config['wx_key'];

        $data = array();
        $data['appid'] = $config['wx_appid'];
        $data['partnerid'] = $config['wx_mchid'];
        $data['prepayid'] = $order_ret;
        $data['package'] = "Sign=WXPay";
        $data['noncestr'] = Func::random_str(20);
        $data['timestamp'] = time();

        //签名
        $unsign_str = Func::createLinkString(Func::argSort($data)) . "&key=" . $wx_key;
        $sign = strtoupper(md5($unsign_str));
        $data['sign'] = $sign;

        return json_encode($data);
    }
    /**
     * 微信统一下单
     * @param $app_key appkey
     * @param $trade_no 交易单号
     * @param $subject 交易subject
     * @param $detail 交易detail
     * @param $total_fee 交易金额 单位分
     * @param $client_ip 终端ip
     * @return
     *      成功 string prepay_id
     *      失败 int:
     *          -2 统一下单失败
     */
    public static function unifiedOrder($app_key, $trade_no, $subject, $detail, $total_fee,
                                        $client_ip, $config, $test=false) {

        $wx_key = $config['wx_key'];

        $data = array();
        $data['appid'] = $config['wx_appid'];
        $data['mch_id'] = $config['wx_mchid'];
        $data['nonce_str'] = Func::random_str(20);
        $data['body'] = $subject;
        $data['detail'] = $detail;
        $data['out_trade_no'] = $trade_no;
        $data['total_fee'] = $total_fee;
        $data['spbill_create_ip'] = $client_ip;
        $data['notify_url'] = Config::$domin . "/pay/main.php/json/pay_notify/notifyWeixin/" .
            TradeModel::TRADETYPE_WEIXINMOBILE . "/{$app_key}/{$test}";
        $data['trade_type'] = "APP";

        //签名
        $unsign_str = Func::createLinkString(Func::argSort($data)) . "&key=" . $wx_key;
        $sign = strtoupper(md5($unsign_str));
        $data['sign'] = $sign;

        //转为xml格式
        $xml_data = new \SimpleXMLElement("<xml></xml>");
        Func::array_to_xml($data, $xml_data);
        $xml_str = $xml_data->asXML();
        
        //post统一下单
        $result = Curl::curl_post(WeixinPayAction::UNIFIED_ORDER_URL, $xml_str);

        //解析返回结果
        $get_data = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        if(!$get_data) {
            return -2;
        }
        $get_para = array();
        $get_sign = "";
        foreach ($get_data->children() as $child) {
            if($child->getName() == 'sign') {
                $get_sign = strval($child);
            } else {
                $get_para[strval($child->getName())] = strval($child);
            }
        }
        if($get_para['return_code'] !== "SUCCESS" || empty($get_sign)) {
            return -2;
        }

        //验证签名
        if(!WeixinPayAction::vertifySign($get_sign, $get_para, $wx_key)) {
            return -2;
        }

        if($get_para['result_code'] !== 'SUCCESS') {
            return -2;
        }

        if(empty($get_para['prepay_id'])) {
            return -2;
        }

        return $get_para['prepay_id'];
    }
    
    /**
     * 签名验证
     * @param $sign
     * @param $para
     * @param $key
     * @return false-验证失败 true-验证成功
     */
    public static function vertifySign($sign, $para, $key) {
        $unsign_str = Func::createLinkString(Func::argSort($para)) . "&key=" . $key;
        $sign_str = strtoupper(md5($unsign_str));

        if($sign === $sign_str) {
            return true;
        }

        return false;
    }
}