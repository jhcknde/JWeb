<?php
/**
 * 支付宝移动支付配置Model
 * User: tsy
 * Date: 16/5/30
 * Time: 下午2:22
 */

namespace Cilibs\Pay\Model;

use Cilibs\Pay\D;

class AlipayMobileConfigModel extends BaseModel {

    public function __construct($appkey, $test=false)
    {
        parent::__construct($appkey, $test);
        $this->db = D::B($this->tbl_prefix . 'alipay_mobile_config');
    }

    /**
     * 获取配置数据
     * @return alipay config数据
     */
    public function get() {
        return $this->db->getby(1);
    }

    /**
     * 插入配置数据
     * @param $alipay_partnerid 商家id
     * @param $alipay_sellerid 卖家账号
     * @param $alipay_rsa_url rsa密钥
     * @param $alipay_public_key 支付宝验证公钥
     * @return int 0-失败 id-插入数据id
     */
    public function insert($alipay_partnerid, $alipay_sellerid, $alipay_rsa_url, $alipay_public_key) {
        $id = $this->db->insert(array(
            'alipay_partnerid' => $alipay_partnerid,
            'alipay_sellerid' => $alipay_sellerid,
            'alipay_rsa' => $alipay_rsa_url,
            'alipay_public_key' => $alipay_public_key
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