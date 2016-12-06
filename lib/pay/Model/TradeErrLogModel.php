<?php
/**
 * 交易errorlog model
 * User: tsy
 * Date: 16/6/2
 * Time: 下午5:06
 */

namespace Cilibs\Pay\Model;

use Cilibs\Pay\D;

class TradeErrLogModel extends BaseModel {

    public function __construct($appkey, $test=false)
    {
        parent::__construct($appkey, $test);
        $this->db = D::B($this->tbl_prefix . 'trade_err_logs');
    }

    /**
     * 根据trade_no获取log信息
     * @param $trade_no 交易单号
     * @return 成功-交易信息 失败-null
     */
    public function getByTradeNo($trade_no) {
        return $this->db->getby_trade_no($trade_no);
    }

    /**
     * 插入log
     * @param $trade_no 交易号
     * @param $error_msg 错误信息
     * @return int 0-失败 id-插入数据id
     */
    public function insertLog($trade_no, $error_msg) {
        $id = $this->db->insert(array(
            'trade_no' => $trade_no,
            'error_msg' => $error_msg
        ));

        if($id <= 0) {
            return 0;
        }

        return $id;
    }
}