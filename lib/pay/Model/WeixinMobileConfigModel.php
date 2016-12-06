<?php
/**
 * 微信移动支付配置Model
 * User: tsy
 * Date: 16/5/30
 * Time: 下午2:22
 */

namespace Cilibs\Pay\Model;

use Cilibs\Pay\D;

class WeixinMobileConfigModel extends BaseModel {

    public function __construct($appkey, $test=false)
    {
        parent::__construct($appkey, $test);
        $this->db = D::B($this->tbl_prefix . 'weixin_mobile_config');
    }

    /**
     * 获取配置数据
     * @return weixin config数据
     */
    public function get() {
        return $this->db->getby(1);
    }
    
    /**
     * 插入配置数据
     * @param $wx_appid 微信开放平台app唯一标识
     * @param $wx_mchid 商户号
     * @param $wx_key 签名密钥
     * @param $wx_pemcert 证书
     * @param $wx_pemkey 证书密钥
     * @param $wx_pemca ca证书
     * @return int 0-失败 id-插入数据id
     */
    public function insert($wx_appid, $wx_mchid, $wx_key, $wx_pemcert, $wx_pemkey, $wx_pemca) {
        $id = $this->db->insert(array(
            'wx_appid' => $wx_appid,
            'wx_mchid' => $wx_mchid,
            'wx_key' => $wx_key,
            'wx_pemcert' => $wx_pemcert,
            'wx_pemkey' => $wx_pemkey,
            'wx_pemca' => $wx_pemca
        ));

        if($id <= 0) {
            return 0;
        }

        return $id;
    }

    /**
     * 更新配置数据
     * @param $id
     * @param $update_arr 更新数组 字段见插入
     * @return int effect_rows
     */
    public function update($id, $update_arr) {
        if(empty($update_arr)) {
            return false;
        }

        $update_arr['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->update(array(
            'id' => $id
        ), $update_arr);
    }
}