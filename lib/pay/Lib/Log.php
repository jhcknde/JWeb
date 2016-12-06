<?php
/**
 * log处理
 * User: tsy
 * Date: 16/6/3
 * Time: 下午2:57
 */

namespace Cilibs\Pay\Lib;

class Log {
    //todo 优化成buffer 一段log一起写 防止并发

    /**
     * 写入info log信息
     * @param $module 模块
     * @param $word log
     */
    public static function i($module, $app_key, $word, $filename_tail="") {
        Log::do_log('i', $module, $app_key, $word, $filename_tail);
    }

    /**
     * 写入error log信息
     * @param $module 模块
     * @param $word log
     */
    public static function e($module, $app_key, $word, $filename_tail="") {
        Log::do_log('e', $module, $app_key, $word, $filename_tail);
    }

    private static function do_log($type, $module, $app_key, $word, $filename_tail) {
        if(!file_exists("../log")) {
            mkdir("../log", 0775);
        }

        if(empty($filename_tail)) {
            $log_filename = date('Y-m-d');
        } else {
            $log_filename = date('Y-m-d') . '_' . $filename_tail;
        }

        $time = date('H:i:s');
        $type_name = "[LOG]";
        if($type == "e") {
            $type_name = "[ERROR]";
        }

        $log_word = "{$time} {$type_name}[{$module}][appkey={$app_key}] {$word}";
        $fp = fopen("../log/" . $log_filename,"a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,$log_word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}