<?php
/**
 * Created by PhpStorm.
 * User: miaozhou
 * Date: 5/11/16
 * Time: 14:57
 */
namespace Cilibs\JWT;

require __DIR__ . '/JWT.php';
require __DIR__ . '/BeforeValidException.php';
require __DIR__ . '/ExpiredException.php';
require __DIR__ . '/SignatureInvalidException.php';

use Firebase\JWT\JWT;

class JWTI
{
    /**
     * @param $data array|object 数据
     * @param $secret string 密钥字符串
     * @return string token字符串
     */
    public static function encode($data, $secret)
    {
        return JWT::encode($data, $secret);
    }

    /**
     * @param $token string encode的返回值
     * @param $secret string 密钥字符串
     * @return object encode之前的$data, 通过 (array)$data 转为数组
     */
    public static function decode($token, $secret)
    {
        return JWT::decode($token, $secret, array('HS256'));
    }
}