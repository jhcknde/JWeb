<?php
/**
 * Created by PhpStorm.
 * User: 袁  亮
 * Date: 2015/12/2
 * Time: 16:55
 * Desc: 调用api.shop.ci123.com接口的客户端
 */

namespace Cilibs\UploadPic;

define('API_XINFOTEK_APPID', '100013');
define('API_XINFOTEK_APISECRET', '12783d44ebbcc6200d79dc72221c508e');

class ApiXinfotekClient
{
    private $base_url = 'http://api.xinfotek.com/';//api地址的根url，切换正式版
    private $dev_base_url = 'http://devapi.xinfotek.com/';//api地址的根url，测试版的时候
    public $run_log = array();//运行log
    public $traceid = null; //调用链跟踪，调用链id
    public $rpcid = null; //嵌套调用
    public $base_rpcid = null; //同一个调用链中的嵌套调用

    function __construct($is_dev = '')
    {
        if ($is_dev) {
            $this->base_url = $this->dev_base_url . $is_dev . '/';
        }
    }

    /**
     * 执行一个api接口
     */
    public function execApi($api_name, $p, $method = 'post', $multi = false, $debug = false)
    {
        $url = $this->base_url . $api_name; //调用链跟踪，包括appid和trace_id及rpcid设置
        $url .= (strpos($url, '?') === false) ? '?' : '&';
        $url .= 'appid=' . API_XINFOTEK_APPID;
        $url = $this->setTrace($url);

        $s = microtime(1);
        $param = $this->buildRequestPara($p);
        $data = $this->curlRequest($url, $param, $method, $multi);
        $this->run_log[$api_name][] = (microtime(1) - $s) * 1000; //执行时间统计

        if ($debug) { //调试模式
            return $data;
        }

        return json_decode($data, 1);
    }

    /**
     * 调用链跟踪相关参数设置
     */
    private function setTrace($url)
    {
        if (is_null($this->traceid)) {
            if (isset($_GET['traceid']) && preg_match('/^[\w]{32}$/', $_GET['traceid'])) {
                $traceid = $_GET['traceid'];
            } else {
                $traceid = date('YmdHis') . uniqid() . rand(10000, 99999);
            }

            $this->traceid = $traceid;
        }

        if (is_null($this->rpcid)) {
            if (isset($_GET['rpcid']) && preg_match('/^[\d\.]+$/', $_GET['rpcid'])) {
                $base_rpcid = $_GET['rpcid'];
            } else {
                $base_rpcid = 0;
            }
            $this->base_rpcid = trim($base_rpcid, '.');
        }

        $rpcid = $this->base_rpcid . '.' . (count($this->run_log) + 1);

        return $url . '&traceid=' . $this->traceid . '&rpcid=' . $rpcid;
    }

    /**
     * 对参数数组，增加过期时间以及签名
     * @param $p 请求的参数数组
     * return 增加了签名和过期时间的请求数组
     */
    private function buildRequestPara($p)
    {
        $para_filter = array(); //去除签名和过期时间，并按字典顺序对参数进行排序
        if ($p) {
            foreach ($p as $k => $v) {
                if (!in_array($k, array('mdstr', 'expire'))) {
                    $v = is_null($v) ? '' : $v;
                    $para_filter[$k] = $v;
                }
            }
        }
        $para_filter['expire'] = time();
        ksort($para_filter);

        $para_string = $this->createLinkstring($para_filter);
        $para_filter['mdstr'] = md5($para_string . API_XINFOTEK_APISECRET);
        return $para_filter;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    private function createLinkstring($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * curl模拟http的GET和POST请求
     * @param $url string 请求地址
     * @param $params array 请求参数数组
     * @param $method string 请求方式，get或post
     * @param $multi array 发送图片信息
     * @param $extheaders array 额外发送的头信息
     * return string 请求的http正文内容
     */
    private function curlRequest($url, $params = array(), $method = 'GET', $multi = false, $extheaders = array())
    {
        if (!function_exists('curl_init')) {
            die('请开启curl扩展');
        }
        $method = strtoupper($method);
        //代理配置
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_USERAGENT, 'PHP-SDK OAuth2.0');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ci, CURLOPT_TIMEOUT, 3);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);
        $headers = (array)$extheaders;
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($params)) {
                    if ($multi) {
                        foreach ($multi as $key => $file) {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    } else {
                        curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                }
                break;
            case 'GET':
                if (!empty($params)) {
                    $url = $url . '?' . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);
        curl_setopt($ci, CURLOPT_URL, $url);
        if ($headers) {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ci);
        curl_close($ci);
        return $response;
    }

    /**
     * 查看执行统计
     */
    public function runLogs()
    {
        $run_log = $this->run_log;

        $total = 0;
        $total_time = 0;
        foreach ($run_log as $k => $v) {
            $v['total_time'] = 0;
            foreach ($v as $tk => $tv) {
                $v['total_time'] += $tv;
                $total++;
            }

            $total_time += $v['total_time'];
            $run_log[$k] = $v;
        }

        $run_log['total'] = $total;
        $run_log['total_time'] = $total_time;
        return $run_log;
    }
}

//////////////////使用demo///////////////////
//$c = new ApiXinfotekClient();
//
//$url = 'upload/pic';
//$multi = array(
//    'file'  => '/Users/miaozhou/projects/vms/centos6_prod/html/Cilibs/JWT/docs/images/apple-touch-icon-72x72.png',
//);
//$data = $c->execApi($url, '', 'post', $multi);
////echo json_encode($data);
//var_dump($data);
//die();