<?php
/**
 * 交易Model
 * User: tsy
 * Date: 16/5/30
 * Time: 下午2:22
 */

namespace Cilibs\Pay\Model;

use Cilibs\Pay\D;

class TradeModel extends BaseModel {

    //交易类型
    const TRADETYPE_ALIPAYMOBILE = "AlipayMobile";      //支付宝移动支付
    const TRADETYPE_WEIXINMOBILE = "WeixinMobile";      //微信移动支付

    //交易状态
    const TRADESTATE_SUCCESS = "SUCCESS";   //支付成功
    const TRADESTATE_NOTPAY = "NOTPAY";     //未支付
    const TRADESTATE_CLOSED = "CLOSED";     //支付关闭
    const TRADESTATE_PAYERROR = "PAYERROR";     //支付失败

    public function __construct($appkey, $test=false)
    {
        parent::__construct($appkey, $test);
        $this->db = D::B($this->tbl_prefix . 'trades');
    }

    /**
     * 根据trade_no获取交易信息
     * @param $trade_no 交易单号
     * @return 成功-交易信息 失败-null
     */
    public function getByTradeNo($trade_no) {
        return $this->db->getby_trade_no($trade_no);
    }

    /**
     * 创建交易
     * @param $subject 交易主题
     * @param $total_fee 总金额 单位分
     * @param $trade_type 交易类型
     * @param $notify_url 通知地址
     * @return json
     *          成功: ret-1 trade_no-交易单号
     *          失败: ret-0[重复交易单号 建议重新创建]
     */
    public function createTrade($subject, $total_fee, $trade_type, $notify_url) {
        //创建交易号
        $trade_no = $this->generate_trade_no();
        $id = $this->db->insert(array(
            'trade_no' => $trade_no,
            'subject' => $subject,
            'total_fee' => $total_fee,
            'trade_type' => $trade_type,
            'notify_url' => $notify_url, 
            'state' => TradeModel::TRADESTATE_NOTPAY
        ));

        if($id <= 0) {
            return json_encode(array('ret' => 0));
        }

        return json_encode(array('ret' => 1, 'trade_no' => $trade_no));
    }

    /**
     * 更新交易成功
     * @param $trade_no 交易单号
     * @param $third_trade_no 第三方交易号
     * @param $buyer_id 买家id
     * @return int effect_rows
     */
    public function updateTradeSuccess($trade_no, $third_trade_no, $buyer_id) {

        return $this->db->update(array(
            'trade_no' => $trade_no
        ), array(
            'third_trade_no' => $third_trade_no,
            'buyer_id' => $buyer_id,
            'updated_at' => date('Y-m-d H:i:s'),
            'state' => TradeModel::TRADESTATE_SUCCESS
        ));
    }

    /**
     * 更新交易失败
     * @param $trade_no 交易单号
     * @param $error_msg 错误log
     * @return int effect_rows
     */
    public function updateTradeFail($trade_no, $error_msg="") {
        $ret = $this->db->update(array(
            'trade_no' => $trade_no
        ), array(
            'updated_at' => date('Y-m-d H:i:s'),
            'state' => TradeModel::TRADESTATE_PAYERROR
        ));

        if(!empty($ret) && !empty($error_msg)) {
            $trade_err_log_model = new TradeErrLogModel($this->appkey, $this->test);
            $trade_err_log_model->insertLog($trade_no, $error_msg);
        }

        return $ret;
    }

    /**
     * 生成一个交易号
     * 生成规则:appid+datetime+6位随机数
     * @return string trade_no
     */
    private function generate_trade_no() {
        $trade_no = $this->appid + date("YmdGis", time());
        for ($i = 0; $i < 6; $i++) {
            $rand = rand(0, 9);
            $trade_no .= $rand;
        }

        return $trade_no;
    }
}