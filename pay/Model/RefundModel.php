<?php
/**
 * 退款Model
 * User: tsy
 * Date: 16/5/30
 * Time: 下午2:23
 */

namespace Cilibs\Pay\Model;

use Cilibs\Pay\D;

class RefundModel extends BaseModel {

    //退款平台
    const REFUNDPLAT_WEIXIN = "Weixin";     //微信
    const REFUNDPLAT_ALIPAY = "Alipay";     //支付宝

    //退款状态
    const REFUNDSTATE_SUCCESS = "SUCCESS";  //退款成功
    const REFUNDSTATE_FAIL = "ERROR";       //退款失败
    const REFUNDSTATE_ING = "REFUNDING";    //退款中

    public function __construct($appkey, $test=false)
    {
        parent::__construct($appkey, $test);
        $this->db = D::B($this->tbl_prefix . 'refunds');
    }

    /**
     * 获取指定退款信息
     * @param $refund_no 退款单号
     * @return 退款信息
     */
    public function getByRefundNo($refund_no) {
        return $this->db->getby_refund_no($refund_no);
    }

    /**
     * 获取指定交易的所有退款记录
     * @param $trade_no 交易单号
     * @param $page page
     * @param $limit 每页数目
     * @return array 退款记录数组
     */
    public function getByTradeNo($trade_no, $page=1, $limit=100) {
        $order = "order by `id` asc";
        return $this->db->getAllByPage($page, $limit, array('trade_no' => $trade_no), $order);
    }

    /**
     * 获取指定交易的所有非失败的退款记录
     * @param $trade_no 交易单号
     * @param $page page
     * @param $limit 每页数目
     * @return array 退款记录数组
     */
    public function getNotFailRefundByTradeNo($trade_no, $page=1, $limit=100) {
        $order = "order by `id` asc";
        $condition = array('trade_no' => $trade_no,
            'state' => array(RefundModel::REFUNDSTATE_SUCCESS, RefundModel::REFUNDSTATE_ING));
        return $this->db->getAllByPage($page, $limit, $condition, $order);
    }

    /**
     * 创建退款信息
     * @param $trade_no 交易单号
     * @param $refund_fee 退款金额
     * @return json
     *          成功: ret-1 $refund_no-退款单号
     *          失败: ret-0[其他错误] -1[无该交易信息] -2[退款金额溢出] -3[重复的退款号]
     */
    public function createRefund($trade_no, $refund_fee) {
        $trade_model = new TradeModel($this->appkey, $this->test);
        $trade_info = $trade_model->getByTradeNo($trade_no);
        if(empty($trade_info)) {
            return json_encode(array('ret' => -1));
        }

        //检查退款总金额是否溢出
        $total_refund_fee = 0;
        $page = 1;
        $refund_arr = $this->getNotFailRefundByTradeNo($trade_no, $page, 100);
        while(!empty($refund_arr)) {
            foreach ($refund_arr as $val) {
                $total_refund_fee = $total_refund_fee + $val['refund_fee'];
            }

            $page = $page + 1;
            $refund_arr = $this->getNotFailRefundByTradeNo($trade_no, $page, 100);
        }
        if($total_refund_fee + $refund_fee > $trade_info['total_fee']) {
            return json_encode(array('ret' => -2));
        }

        if($trade_info['trade_type'] == TradeModel::TRADETYPE_WEIXINMOBILE) {
            $refund_platform = RefundModel::REFUNDPLAT_WEIXIN;
        } else if($trade_info['trade_type'] == TradeModel::TRADETYPE_ALIPAYMOBILE) {
            $refund_platform = RefundModel::REFUNDPLAT_ALIPAY;
        } else {
            return json_encode(array('ret' => 0));
        }

        //插入退款记录
        $refund_no = $this->generate_refund_no();
        $id = $this->db->insert(array(
            'refund_no' => $refund_no,
            'trade_no' => $trade_no, 
            'refund_platform' => $refund_platform,
            'refund_fee' => $refund_fee,
            'state' => RefundModel::REFUNDSTATE_ING
        ));

        if($id <= 0) {
            return json_encode(array('ret' => -3));
        }

        return json_encode(array('ret' => 1, 'refund_no' => $refund_no));
    }

    /**
     * 更新退款成功
     * @param $refund_no 退款单号
     * @return int effect_rows
     */
    public function updateRefundSuccess($refund_no) {
        return $this->db->update(array(
            'refund_no' => $refund_no
        ), array(
            'updated_at' => date('Y-m-d H:i:s'),
            'state' => RefundModel::REFUNDSTATE_SUCCESS
        ));
    }

    /**
     * 更新退款失败
     * @param $refund_no 退款单号
     * @return int effect_rows
     */
    public function updateRefundFail($refund_no) {
        return $this->db->update(array(
            'refund_no' => $refund_no
        ), array(
            'updated_at' => date('Y-m-d H:i:s'),
            'state' => RefundModel::REFUNDSTATE_FAIL
        ));
    }

    /**
     * 生成一个退款号
     * 生成规则:appid+datetime+6位随机数
     * @return string refund_no
     */
    private function generate_refund_no() {
        $refund_no = $this->appid + date("YmdGis", time());
        for ($i = 0; $i < 6; $i++) {
            $rand = rand(0, 9);
            $refund_no .= $rand;
        }

        return $refund_no;
    }
}