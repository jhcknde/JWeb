<?php
/**
 * 通知model
 * User: tsy
 * Date: 16/6/3
 * Time: 上午10:18
 */

namespace Cilibs\Pay\Model;

use Cilibs\Pay\D;

class NotifyModel extends BaseModel {

    //notify type
    const NOTIFYTYPE_TRADE = "TRADE_NOTIFY";    //支付完成通知
    const NOTIFYTYPE_REFUND = "REFUND_NOTIFY";  //退款通知

    //notify state
    const NOTIFYSTATE_WAIT = "WAIT";        //等待通知
    const NOTIFYSTATE_ING = "ING";        //通知中
    const NOTIFYSTATE_SUC = "SUCCESS";      //通知成功
    const NOTIFYSTATE_FAIL = "FAIL";        //通知失败

    public function __construct($appkey, $test=false)
    {
        parent::__construct($appkey, $test);
        $this->db = D::B($this->tbl_prefix . 'notifys');
    }

    /**
     * 分页获取未成功的通知
     * @param $page
     * @param $limit
     * @param $type 类型
     * @param $limit_time 次数上限(包括)
     */
    public function getNotSucNotify($page, $limit, $type, $limit_time) {

        //查找所有通知次数小于等于次数上限 且尚未通知成功的通知
        $condition = array("type"=>$type, "notify_time<="=>$limit_time,
            "notify_time>"=>0, "state!"=>NotifyModel::NOTIFYSTATE_SUC);
        $order = "order by id asc";
        return $this->db->getAllByPage($page, $limit, $condition, $order);
    }

    /**
     * 根据$notify_no获取notify信息
     * @param $notify_no
     * @return 成功-通知信息 失败-null
     */
    public function getByNotifyNo($notify_no) {
        return $this->db->getby_notify_no($notify_no);
    }

    /**
     * 创建通知
     * @param $type 通知类型 TRADE_NOTIFY REFUND_NOTIFY
     * @param $notify_url 通知地址
     * @param $notify_content 通知内容
     * @return mixed
     *      成功: string notify_no
     *      失败: int -1 类型错误 -2 创建notify错误 重新创建
     */
    public function createNotify($type, $notify_url, $notify_content) {
        if($type != NotifyModel::NOTIFYTYPE_REFUND && $type != NotifyModel::NOTIFYTYPE_TRADE) {
            return -1;
        }

        $notify_no = $this->generate_nofity_no();
        $id = $this->db->insert(array(
            'notify_no' => $notify_no,
            'type' => $type,
            'notify_content' => $notify_content,
            'notify_url' => $notify_url,
            'notify_time' => 0,
            'state' => NotifyModel::NOTIFYSTATE_WAIT
        ));

        if($id <= 0) {
            return -2;
        }

        return $notify_no;
    }

    /**
     * 开始一个通知
     * @param $notify_no 通知id
     * @return int 0-更新失败 1-更新成功
     */
    public function startNotify($notify_no) {
        return $this->db->update(array(
            'notify_no' => $notify_no
        ), array(
            'notify_time' => 1,     //自增1
            'last_notify_at' => date('Y-m-d H:i:s'),
            'state' => NotifyModel::NOTIFYSTATE_ING
        ), array('notify_time'));
    }

    /**
     * 更新通知成功
     * @param $notify_no 通知id
     * @return int 0-失败 1-成功
     */
    public function updateNotifySuc($notify_no) {
        return $this->db->update(array(
            'notify_no' => $notify_no
        ), array(
            'state' => NotifyModel::NOTIFYSTATE_SUC
        ));
    }

    /**
     * 更新通知失败
     * @param $notify_no 通知id
     * @return int 0-失败 1-成功
     */
    public function updateNotifyFail($notify_no) {
        return $this->db->update(array(
            'notify_no' => $notify_no
        ), array(
            'state' => NotifyModel::NOTIFYSTATE_FAIL
        ));
    }

    /**
     * 生成一个通知号
     * 生成规则:appid+datetime+6位随机数
     * @return string nofity_no
     */
    private function generate_nofity_no() {
        $trade_no = $this->appid + date("YmdGis", time());
        for ($i = 0; $i < 6; $i++) {
            $rand = rand(0, 9);
            $trade_no .= $rand;
        }

        return $trade_no;
    }
}