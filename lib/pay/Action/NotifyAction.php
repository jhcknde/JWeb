<?php
/**
 * Nofity相关action
 * User: tsy
 * Date: 16/6/3
 * Time: 上午10:53
 */

namespace Cilibs\Pay\Action;

use Cilibs\Admin\AdminI;
use Cilibs\Pay\Config;
use Cilibs\Pay\Model\NotifyModel;
use Cilibs\Pay\Lib\Curl;
use Cilibs\Pay\Lib\Func;
use Cilibs\Pay\Lib\Log;


class NotifyAction {

    //交易通知频率 2m 10min 10min 1h 2h 6h 15h
    public static $NOTIFY_TRADE_CONFIG = array(120, 600, 600, 3600, 7200, 21600, 54000);
    //public static $NOTIFY_TRADE_CONFIG = array(1, 1, 1, 1, 1, 1, 1);

    /**
     * 轮询检查通知
     */
    public static function doCrontabNotify($test=false) {
        $module_tag = "doCrontabNotify";
        $filename_tail = "crontab_notify";
        
        $now_time = time();

        Log::i($module_tag, "", "start crontab notify. notifytime={$now_time}", $filename_tail);

        //获取所有开启了该服务的app_key
        if($test) {
            $all_app_key = array("asas");
        } else {
            $all_app_key = AdminI::getServiceApps(Config::$service_key);
        }

        $to_notify_arr = array();

        //遍历所有appkey所在表
        foreach ($all_app_key as $app_key) {
            $notify_model = new NotifyModel($app_key, $test);

            $page = 1;
            $limit = 100;
            $notify_arr = $notify_model->getNotSucNotify($page, $limit,
                NotifyModel::NOTIFYTYPE_TRADE, count(NotifyAction::$NOTIFY_TRADE_CONFIG));
            while(!empty($notify_arr)) {
                foreach ($notify_arr as $notify_info) {
                    //判断频率是否达到
                    $diff_time = $now_time - strtotime($notify_info['last_notify_at']);
                    $time_freq = NotifyAction::$NOTIFY_TRADE_CONFIG[$notify_info['notify_time'] - 1];
                    if(empty($time_freq) || $diff_time <= 0) {
                        continue ;
                    }

                    //更新通知状态 将信息放到待通知数组中
                    if($diff_time >= $time_freq) {
                        $ret = $notify_model->startNotify($notify_info['notify_no']);
                        if($ret == 0) {
                            continue ;
                        }
                        if(empty($to_notify_arr[$app_key])) {
                            $to_notify_arr[$app_key] = array();
                        }
                        $to_notify_arr[$app_key][] = $notify_info;
                    } else {
                        continue ;
                    }
                }

                $page = $page + 1;
                $notify_arr = $notify_model->getNotSucNotify($page, $limit,
                    NotifyModel::NOTIFYTYPE_TRADE, count(NotifyAction::$NOTIFY_TRADE_CONFIG));
            }
        }

        //开始通知
        foreach ($to_notify_arr as $key=>$val) {
            $notify_model = new NotifyModel($key, $test);

            foreach ($val as $notify_info) {
                $param = json_decode($notify_info['notify_content'], true);
                $param['notify_no'] = $notify_info['notify_no'];
                $param['notify_time'] = date('Y-m-d H:i:s');
                $sign = NotifyAction::signNotify($notify_model->getAppSecret(), $param);
                $param['sign'] = $sign;

                $result = Curl::curl_post($notify_info['notify_url'], http_build_query($param));
                $order_time = $notify_info['notify_time'] + 1;  //第几次通知
                if(!$result || ($result !== 'SUCCESS' && $result !== 'success')) {      //通知失败
                    Log::i($module_tag, $key, "send notify. notifytime={$now_time}, notify_no={$notify_info['notify_no']}, time={$order_time}, result=FAIL", $filename_tail);
                    $ret = $notify_model->updateNotifyFail($notify_info['notify_no']);
                    if($ret == 0) {
                        Log::e($module_tag, $key, "updateNotifyFail fail. notifytime={$now_time}, notify_no={$notify_info['notify_no']}, time={$order_time}", $filename_tail);
                        continue;
                    }
                } else {    //通知成功
                    Log::i($module_tag, $key, "send notify. notifytime={$now_time}, notify_no={$notify_info['notify_no']}, time={$order_time}, result=SUCCESS", $filename_tail);
                    $ret = $notify_model->updateNotifySuc($notify_info['notify_no']);
                    if($ret == 0) {
                        Log::e($module_tag, $key, "updateNotifySuc fail. notifytime={$now_time}, notify_no={$notify_info['notify_no']}, time={$order_time}", $filename_tail);
                        continue;
                    }
                }
            }
        }

        $cost_time = time() - $now_time;
        Log::i($module_tag, "", "over crontab notify. notifytime={$now_time}, cost {$cost_time}s.", $filename_tail);
    }

    /**
     * 创建交易通知
     * @param $app_key appkey
     * @param $return_code SUCCESS/FAIL 支付成功/支付失败
     * @param $return_msg 支付成功：OK 支付失败：失败原因
     * @param $trade_no 交易单号
     * @param $total_fee 支付金额 单位分
     * @param $trade_createtime 交易创建时间 yyyy-MM-dd HH:mm:ss
     * @param $trade_endtime 交易结束时间 yyyy-MM-dd HH:mm:ss
     * @param bool $test
     * @return mixed
     *          成功: string nofity_no
     *          失败: int -1
     */
    public static function createTradeNotify($app_key, $notify_url, $return_code, $return_msg, $trade_no,
                                             $trade_createtime, $trade_endtime, $test=false) {
        $param = array();
        $param['return_code'] = $return_code;
        $param['return_msg'] = $return_msg;
        $param['trade_no'] = $trade_no;
        $param['trade_createtime'] = $trade_createtime;
        $param['trade_endtime'] = $trade_endtime;

        $content = json_encode($param);

        $notify_model = new NotifyModel($app_key, $test);
        $ret = $notify_model->createNotify(NotifyModel::NOTIFYTYPE_TRADE, $notify_url, $content);
        if(!is_string($ret)) {
            return -1;
        }

        return $ret;
    }

    /**
     * 发送notify
     * @param $app_key appkey
     * @param $notify_no 通知id
     * @param bool $test
     * @return
     *      成功: 1 通知成功 结果成功
     *      失败: -1 无该notify_no信息 -2 更新通知次数错误 -3 失败更新数据表错误 -4 成功更新数据表错误 -5 通知结果失败
     */
    public static function doSendNotify($app_key, $notify_no, $test=false) {
        $notify_model = new NotifyModel($app_key, $test);
        $notify_info = $notify_model->getByNotifyNo($notify_no);

        if(empty($notify_info)) {
            return -1;
        }

        $ret = $notify_model->startNotify($notify_no);
        if($ret == 0) {
            return -2;
        }
        $param = json_decode($notify_info['notify_content'], true);
        $param['notify_no'] = $notify_no;
        $param['notify_time'] = date('Y-m-d H:i:s');
        $sign = NotifyAction::signNotify($notify_model->getAppSecret(), $param);
        $param['sign'] = $sign;

        $result = Curl::curl_post($notify_info['notify_url'], http_build_query($param));
        if(!$result || ($result !== 'SUCCESS' && $result !== 'success')) {      //通知失败
            $ret = $notify_model->updateNotifyFail($notify_no);
            if($ret == 0) {
                return -3;
            }

            return -5;
        }
        
        //通知成功
        $ret = $notify_model->updateNotifySuc($notify_no);
        if($ret == 0) {
            return -4;
        }

        return 1;
    }

    /**
     * sign notify
     * @param $app_secret $app_secret
     * @param $param_arr 待签名的参数
     * @return string sign
     */
    public static function signNotify($app_secret, $param_arr) {
        $unsign_str = Func::createLinkString(Func::argSort($param_arr)) . "&key=" . $app_secret;
        $sign = strtoupper(md5($unsign_str));

        return $sign;
    }

    /**
     * 验证sign有效性
     * @param $app_secret $app_secret
     * @param $param_arr POST数据
     * @return bool 成功-true 失败-false
     */
    public static function vertifyNotify($app_secret, $param) {
        if(empty($param) || empty($app_secret)) {
            return false;
        }

        $sign = "";
        $para_filter = array();
        while (list ($key, $val) = each ($param)) {
            if($key == "sign") {
                $sign = $val;
            } else if(!empty($val)) {
                $para_filter[$key] = $param[$key];
            }
        }

        if(empty($sign) || empty($para_filter)) {
            return false;
        }

        $unsign_str = Func::createLinkString(Func::argSort($para_filter)) . "&key=" . $app_secret;
        $sign_str = strtoupper(md5($unsign_str));

        if($sign === $sign_str) {
            return true;
        }

        return false;
    }
}