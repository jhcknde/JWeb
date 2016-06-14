<?php
/**
 * Created by PhpStorm.
 * User: tsy
 * Date: 16/5/30
 * Time: 下午1:44
 */
namespace Cilibs\Pay;

use Cilibs\Pay\Model\TradeModel;
use Cilibs\Pay\Model\RefundModel;
use Cilibs\Pay\Lib\Error;
use Cilibs\Pay\Action\WeixinPayAction;
use Cilibs\Pay\Action\AlipayAction;
use Cilibs\Pay\Action\NotifyAction;

include_once(dirname(__FILE__)."/config.php");

class PayI
{
    /**
     * 创建微信移动支付交易
     * @param $app_key string appkey
     * @param $subject string 交易主题 长度[1, 128]
     * @param $detail string 交易详细描述 [1, 512]
     * @param $total_fee int 交易总金额 单位分 >1
     * @param $client_ip string 用户端ip ipv4 长度[1, 16] 调用客户端sdk获取
     * @param $notify_url string 支付结果通知回调地址
     * @param $test bool 测试参数 传测试参数则appkey不需要验证
     * @return json
     *      成功:
     *          ret string SUCCESS
     *          trade_no string 交易单号
     *          pay_param string 微信支付参数
     *      失败:
     *          ret string FAIL
     *          error_code int 错误码:
     *              UNKOWN_ERROR 其它错误
     *              INVALID_APPKEY appkey不合法
     *              LIB_INVALID_SUBJECT_FORMAT subject格式非法
     *              LIB_INVALID_DETAIL_FORMAT detail格式非法
     *              LIB_INVALID_FEE_FORMAT 金额格式非法
     *              LIB_INVALID_IP_FORMAT ip格式非法
     *              LIB_INVALID_NOTIFYURL_FORMAT notify_url格式非法
     *              LIB_ERROR_CREATE_TRADE 创建交易错误
     *              LIB_ERROR_WEIXIN_MOBILE_CONFIG 微信移动支付配置文件错误
     *              LIB_ERROR_WEIXIN_UNIFIED_ORDER 微信统一下单错误
     *          msg string 错误描述
     */
    public static function createWeixinMobileTrade($app_key, $subject, $detail, $total_fee,
                                                   $client_ip, $notify_url, $test=false) {
        //格式判断
        if(empty($app_key) || !is_string($app_key)) {
            return PayI::error_return(Error::INVALID_APPKEY);
        }
        if(empty($subject) || !is_string($subject) || strlen($subject) > 128) {
            return PayI::error_return(Error::LIB_INVALID_SUBJECT_FORMAT);
        }
        if(empty($detail) || !is_string($detail) || strlen($detail) > 512) {
            return PayI::error_return(Error::LIB_INVALID_DETAIL_FORMAT);
        }
        if(empty($total_fee) || !is_int($total_fee) || $total_fee <= 0) {
            return PayI::error_return(Error::LIB_INVALID_FEE_FORMAT);
        }
        if(empty($client_ip) || !is_string($client_ip)) {
            return PayI::error_return(Error::LIB_INVALID_IP_FORMAT);
        }
        if(empty($notify_url) || !is_string($notify_url)) {
            return PayI::error_return(Error::LIB_INVALID_NOTIFYURL_FORMAT);
        }

        try {
            $trade_model = new TradeModel($app_key, $test);

            //创建交易信息
            $trade_ret_json = $trade_model->createTrade($subject, $total_fee, TradeModel::TRADETYPE_WEIXINMOBILE, $notify_url);
            $trade_ret = json_decode($trade_ret_json, true);
            if($trade_ret['ret'] != 1) {
                return PayI::error_return(Error::LIB_ERROR_CREATE_TRADE);
            }
            $trade_no = $trade_ret['trade_no'];

            //微信统一下单
            $gen_ret = WeixinPayAction::generatePayParams($app_key, $trade_no, $subject, $detail,
                $total_fee, $client_ip, $test);
            if(!is_string($gen_ret)) {
                if($gen_ret == -1) {
                    return PayI::error_return(Error::LIB_ERROR_WEIXIN_MOBILE_CONFIG);
                } else if($gen_ret == -2) {
                    return PayI::error_return(Error::LIB_ERROR_WEIXIN_UNIFIED_ORDER);
                } else {
                    return PayI::error_return(Error::UNKOWN_ERROR);
                }
            }

            //成功返回
            return json_encode(array('ret' => 'SUCCESS',
                'trade_no' => $trade_no, 'pay_param' => $gen_ret));
        } catch(\Exception $e) {
            return PayI::error_return($e->getCode());
        }
    }

    /**
     * 创建支付宝移动支付交易
     * @param $app_key string appkey
     * @param $subject string 交易主题 长度[1, 128]
     * @param $detail string 交易详细描述 [1, 512]
     * @param $total_fee int 交易总金额 单位分
     * @param $notify_url string 支付结果通知回调地址
     * @param $test bool 测试参数 传测试参数则appkey不需要验证
     * @return json
     *      成功:
     *          ret string SUCCESS
     *          trade_no string 交易单号
     *          pay_param string 支付宝支付需要的支付参数
     *      失败:
     *          ret string FAIL
     *          error_code int 错误码:
     *              UNKOWN_ERROR 其它错误
     *              INVALID_APPKEY appkey不合法
     *              LIB_INVALID_SUBJECT_FORMAT subject格式非法
     *              LIB_INVALID_DETAIL_FORMAT detail格式非法
     *              LIB_INVALID_FEE_FORMAT 金额格式非法
     *              LIB_INVALID_NOTIFYURL_FORMAT notify_url格式非法
     *              LIB_ERROR_CREATE_TRADE 创建交易错误
     *              LIB_ERROR_ALIPAY_MOBILE_CONFIG 支付宝移动支付配置文件错误
     *              LIB_ERROR_ALIPAY_RSA_SIGN 支付宝签名私钥错误
     *          msg string 错误描述
     */
    public static function createAlipayMobileTrade($app_key, $subject, $detail, $total_fee,
                                                   $notify_url, $test=false) {
        //格式判断
        if(empty($app_key) || !is_string($app_key)) {
            return PayI::error_return(Error::INVALID_APPKEY);
        }
        if(empty($subject) || !is_string($subject) || strlen($subject) > 128) {
            return PayI::error_return(Error::LIB_INVALID_SUBJECT_FORMAT);
        }
        if(empty($detail) || !is_string($detail) || strlen($detail) > 512) {
            return PayI::error_return(Error::LIB_INVALID_DETAIL_FORMAT);
        }
        if(empty($total_fee) || !is_int($total_fee) || $total_fee <= 0) {
            return PayI::error_return(Error::LIB_INVALID_FEE_FORMAT);
        }
        if(empty($notify_url) || !is_string($notify_url)) {
            return PayI::error_return(Error::LIB_INVALID_NOTIFYURL_FORMAT);
        }

        try {
            $trade_model = new TradeModel($app_key, $test);

            //创建交易信息
            $trade_ret_json = $trade_model->createTrade($subject, $total_fee, TradeModel::TRADETYPE_ALIPAYMOBILE, $notify_url);
            $trade_ret = json_decode($trade_ret_json, true);
            if($trade_ret['ret'] != 1) {
                return PayI::error_return(Error::LIB_ERROR_CREATE_TRADE);
            }
            $trade_no = $trade_ret['trade_no'];

            //创建支付宝支付参数
            $gen_ret = AlipayAction::generatePayParams($app_key, $trade_no, $subject, $detail,
                $total_fee, $test);
            if(!is_string($gen_ret)) {
                if($gen_ret == -1) {
                    return PayI::error_return(Error::LIB_ERROR_ALIPAY_MOBILE_CONFIG);
                } else if($gen_ret == -2) {
                    return PayI::error_return(Error::LIB_INVALID_FEE_FORMAT);
                } else if($gen_ret == -3) {
                    return PayI::error_return(Error::LIB_ERROR_ALIPAY_RSA_SIGN);
                } else {
                    return PayI::error_return(Error::UNKOWN_ERROR);
                }
            }

            //成功返回
            return json_encode(array('ret' => 'SUCCESS',
                'trade_no' => $trade_no, 'pay_param' => $gen_ret));
        } catch(\Exception $e) {
            return PayI::error_return($e->getCode());
        }
    }

    /**
     * 退款
     * @param $app_key string appkey
     * @param $trade_no string 待退款的交易单号
     * @param $refund_fee int 退款金额 单位分
     * @param $test bool 测试参数 传测试参数则appkey不需要验证
     * @return json
     *      成功:
     *          ret string SUCCESS
     *      失败:
     *          ret FAIL
     *          error_code int 错误码
     *              UNKOWN_ERROR 其它错误
     *              INVALID_APPKEY appkey不合法
     *              LIB_INVALID_TRADENO_FORMAT trade_no格式非法
     *              LIB_INVALID_FEE_FORMAT 金额格式非法
     *              LIB_NO_TRADE_NO 无该交易信息
     *              LIB_OVER_REFUND_FEE 退款总金额超过交易金额
     *              LIB_ERROR_CREATE_REFUND 创建退款错误,重新创建
     *          msg string 错误描述
     */
    public static function doRefund($app_key, $trade_no, $refund_fee, $test=false) {
        //格式判断
        if(empty($app_key) || !is_string($app_key)) {
            return PayI::error_return(Error::INVALID_APPKEY);
        }
        if(empty($trade_no) || !is_string($trade_no)) {
            return PayI::error_return(Error::LIB_INVALID_TRADENO_FORMAT);
        }
        if(empty($refund_fee) || !is_int($refund_fee) || $refund_fee <= 0) {
            return PayI::error_return(Error::LIB_INVALID_FEE_FORMAT);
        }

        try {
            $refund_model = new RefundModel($app_key, $test);

            //创建退款信息
            $refund_ret_json = $refund_model->createRefund($trade_no, $refund_fee);
            $refund_ret = json_decode($refund_ret_json, true);
            if($refund_ret['ret'] != 1) {
                if($refund_ret['ret'] == -1) {
                    return PayI::error_return(Error::LIB_NO_TRADE_NO);
                } else if($refund_ret['ret'] == -2) {
                    return PayI::error_return(Error::LIB_OVER_REFUND_FEE);
                } else if($refund_ret['ret'] == -3) {
                    return PayI::error_return(Error::LIB_ERROR_CREATE_REFUND);
                } else {
                    return PayI::error_return(Error::UNKOWN_ERROR);
                }
            }
            $refund_no = $refund_ret['refund_no'];

            //创建退款请求
            //todo
        } catch(\Exception $e) {
            return PayI::error_return($e->getCode());
        }
    }

    /**
     * 验证notify有效性
     * @param $app_secret $app_secret2
     * @param $param_arr 带签名参数数组 验证时将整个$_POST传入
     * @return bool 成功-true 失败-false
     */
    public static function vertifyNotify($app_secret, $param_arr) {
        return NotifyAction::vertifyNotify($app_secret, $param_arr);
    }

    private static function error_return($code) {
        return json_encode(array('ret' => 'FAIL', 'error_code' => $code, 'msg' => Error::getMsg($code)));
    }
}