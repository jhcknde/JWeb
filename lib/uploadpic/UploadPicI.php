<?php
/**
 * Created by PhpStorm.
 * User: miaozhou
 * Date: 5/24/16
 * Time: 17:16
 */
namespace ztphplibs\UploadPic;

class UploadPicI
{
    /**
     * @param $file
     * @return array
     *
     * 正确返回示例:
     * {
     *  "ret": 1,
     *  "data": {
     *      "state": "SUCCESS",
     *      "url": "http://img2.ciurl.cn/flashsale/upload/upload/2016/04/07/1460022076443316.png", //图片地址，图片地址后加_a_100x100或者_b_100x100，可以实现图片裁减功能
     *      "title": "1460022076443316.png",//图片名
     *      "original": "demo.png",//图片原名
     *      "type": ".png",//图片格式
     *      "size": 146160//图片大小
     *  }
     * }
     *
     * 错误返回示例:
     * {
     *  "ret": -1,
     *  "msg": "文件不存在"
     * }
     */
    public static function upload($file)
    {
        if (!file_exists($file)) {
            return array('ret' => '-1', 'msg' => '文件不存在');
        }

        $c = new ApiXinfotekClient();
        $data = $c->execApi('upload/pic', '', 'post', array('file' => $file));
        if (!$data || $data['state'] != 1) {
            return array('ret' => '-1', 'msg' => $data['mess']);
        }

        return array('ret' => 1, 'data' => $data['data']);
    }
}