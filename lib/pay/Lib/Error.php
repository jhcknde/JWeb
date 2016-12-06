<?php
/**
 * 错误code msg
 * User: tsy
 * Date: 16/5/30
 * Time: 下午2:45
 */

namespace Cilibs\Pay\Lib;

class Error
{
    /***通用错误码***/
    const UNKOWN_ERROR = -1;        //其它错误
    const INVALID_APPKEY = -2;      //appkey不合法

    /***Lib错误码***/
    const LIB_INVALID_SUBJECT_FORMAT = 1000;    //subject格式非法
    const LIB_INVALID_DETAIL_FORMAT = 1001;     //detail格式非法
    const LIB_INVALID_FEE_FORMAT = 1002;        //金额格式非法
    const LIB_INVALID_IP_FORMAT = 1003;         //ip格式非法
    const LIB_INVALID_NOTIFYURL_FORMAT = 1004;  //notify_url格式非法
    const LIB_INVALID_TRADENO_FORMAT = 1005;    //trade_no格式非法

    const LIB_ERROR_CREATE_TRADE = 1100;        //创建交易错误
    const LIB_NO_TRADE_NO = 1101;               //无该交易信息
    const LIB_OVER_REFUND_FEE = 1102;           //退款总金额超过交易金额
    const LIB_ERROR_CREATE_REFUND = 1103;       //创建退款错误,重新创建
    const LIB_ERROR_WEIXIN_MOBILE_CONFIG = 1104;    //微信移动支付配置文件错误
    const LIB_ERROR_WEIXIN_UNIFIED_ORDER = 1105;    //微信统一下单错误
    const LIB_ERROR_ALIPAY_MOBILE_CONFIG = 1106;    //支付宝移动支付配置文件错误
    const LIB_ERROR_ALIPAY_RSA_SIGN = 1107;    //支付宝签名私钥错误

    public static $msg = array(
        Error::UNKOWN_ERROR                 => '其它错误',
        Error::INVALID_APPKEY               => 'appkey不合法',

        Error::LIB_INVALID_SUBJECT_FORMAT   => 'subject格式非法',
        Error::LIB_INVALID_DETAIL_FORMAT    => 'detail格式非法',
        Error::LIB_INVALID_FEE_FORMAT       => '金额格式非法',
        Error::LIB_INVALID_IP_FORMAT        => 'ip格式非法',
        Error::LIB_INVALID_NOTIFYURL_FORMAT => 'notify_url格式非法',
        Error::LIB_INVALID_TRADENO_FORMAT   => 'trade_no格式非法',

        Error::LIB_ERROR_CREATE_TRADE       => '创建交易错误,请重新创建',
        Error::LIB_NO_TRADE_NO              => '无该交易信息',
        Error::LIB_OVER_REFUND_FEE          => '退款总金额超过交易金额',
        Error::LIB_ERROR_CREATE_REFUND      => '创建退款错误,重新创建',
        Error::LIB_ERROR_WEIXIN_MOBILE_CONFIG   => '微信移动支付配置文件错误',
        Error::LIB_ERROR_WEIXIN_UNIFIED_ORDER   => '微信统一下单错误',
        Error::LIB_ERROR_ALIPAY_MOBILE_CONFIG   => '支付宝移动支付配置文件错误',
        Error::LIB_ERROR_ALIPAY_RSA_SIGN    => '支付宝签名私钥错误'

    );

    public static function getMsg($code) {
        if(empty(Error::$msg[$code])) {
            return "其他错误";
        }

        return Error::$msg[$code];
    }
}