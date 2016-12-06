<?php

/**
 * AlipayMobileConfigModel测试用例
 * User: tsy
 * Date: 16/5/30
 * Time: 下午6:00
 */
namespace Cilibs\Pay\Test;

use Cilibs\Pay\Model\AlipayMobileConfigModel;

class AlipayMobileConfigModelTest
{
    public static function testGet() {
        $model = new AlipayMobileConfigModel("", true);
        var_dump($model->get());
    }

    public static function testInsert() {
        $model = new AlipayMobileConfigModel("", true);
        $rsa = "-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQCfN/RVWrAmZdjNrAzBdoBwB1rYESQbgiEiocJt085g7gtReWXu
sfATU+37YWh/7dVVJBmm7g1xhDI9ftjB3hZ0QnZkgmIsVKTQ4pTZAud0+6g7NAH3
TYlFR8ofFWXFqU2zGTsi50UmimKwli7U53SJU67tZFrC8R2/N12MWID2jQIDAQAB
AoGAXA07pAV0hh6oA/EOxuM+SobWDMmMOKM5iQ5AnMKyNPQrcwVe22vgwyvpEUc1
5ZCZoEno0swa1aB6c3dc1mkSTWeP/6tvu8r7LTTcpIwMNGtylyJVZf9HJyJ2Z8Is
B0sO9Bpf+BMvzjgwIoo8TR0kW9nC90akvJJxouW5IEYz1IECQQDMb3A2eAqDupVr
L+Fh69cNYxkMJ+3Ia+EIHYJhQ2vy7ou3b6jWcWS5jGieVns1OcaxRUPnIWBhnlE9
NvxO0shdAkEAx2DVRmRR7injy4olHyyAyTYMmLT/EeyGykBKwhSxTvvMxQu9m8DK
BKETiTXLuJISw7dd/uuApUP21tCDXgpD8QJBAK4DDRjQBOMrppOeJdIb1OloOKHI
OvYmHV2zAI+ZvAEEW5jASo595qaphUOBiU4854ts0eei2U8+Wxgn/Yt6j2ECQQCD
j50MBvSdMF0VGQIn0OjmXNjxBzXssOO8n7H04UyirrrPJ1ElbpCff15xwuK71v+0
z9Ghfer0oqVF2G9m5WUxAkAwGloci/60h4eZdSYCh5jv980turTAOJRGQRT6mBDw
3R3XMzZVNunk3j71i8Cqxcg8qLevrICp6vq7vSJAToW8
-----END RSA PRIVATE KEY----- ";

        $public_pem = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRA
FljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQE
B/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5Ksi
NG9zpgmLCUYuLkxpLQIDAQAB
-----END PUBLIC KEY-----";
        var_dump($model->insert("2088701752529231", "zhaowenjie@corp-ci.com", $rsa, $public_pem));
    }

    public static function testUpdate() {
        $model = new AlipayMobileConfigModel("", true);
        var_dump($model->update(1, array("alipay_partnerid"=>"989898989")));
    }
}
