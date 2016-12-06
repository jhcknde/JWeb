<?php
/**
 * 一些通用function
 * User: tsy
 * Date: 16/5/31
 * Time: 下午3:24
 */

namespace Cilibs\Pay\Lib;

class Func {
    /**
     * 数组排序 按照ASCII字典升序
     * @param $para 排序前数组
     * @return 排序后数组
     */
    public static function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    public static function createLinkString($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            if(empty($val)) {
                continue;
            }
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

        return $arg;
    }

    /**
     * 生成指定长度的随机字符串(包含大写英文字母, 小写英文字母, 数字)
     * @param int $length 需要生成的字符串的长度
     * @return string 包含 大小写英文字母 和 数字 的随机字符串
     */
    public static function random_str($length)
    {
        //生成一个包含 大写英文字母, 小写英文字母, 数字 的数组
        $arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));

        $str = '';
        $arr_len = count($arr);
        for ($i = 0; $i < $length; $i++)
        {
            $rand = mt_rand(0, $arr_len-1);
            $str.=$arr[$rand];
        }

        return $str;
    }

    /**
     * array转xml
     * @param $array
     * @param $xml_info
     */
    public static function array_to_xml($array, &$xml_info) {
        foreach($array as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subnode = $xml_info->addChild("$key");
                    array_to_xml($value, $subnode);
                }else{
                    $subnode = $xml_info->addChild("item$key");
                    array_to_xml($value, $subnode);
                }
            }else {
                $xml_info->addChild("$key",htmlspecialchars("$value"));
            }
        }
    }

    /**
     * 分转元
     * @param $fee_fen 分
     * @return
     *      失败:false
     *      成功:string fee_yuan 元
     */
    public static function fen_to_yuan($fee_fen) {
        $fee_fen = floatval($fee_fen);
        if($fee_fen <= 0) {
            return false;
        }

        $fee_yuan = $fee_fen / 100;
        if($fee_yuan <= 0) {
            return false;
        }

        return number_format($fee_yuan, 2);
    }

    /**
     * RSA签名
     * @param $data 待签名数据
     * @param $private_rsa 用户私钥
     * return
     *      失败:false
     *      成功:签名结果
     */
    public static function rsa_sign($data, $private_rsa) {
        $res = openssl_get_privatekey($private_rsa);
        if(!$res) {
            return false;
        }
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }
}