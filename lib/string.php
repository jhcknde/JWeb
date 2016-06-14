<?php
/**
 * Created by PhpStorm.
 * User: miaozhou
 * Date: 3/22/16
 * Time: 17:57
 */
namespace {

    if (!function_exists('random_string')) {
        /**
         * Create a Random String
         *
         * Useful for generating passwords or hashes.
         *
         * @param    string    type of random string.  basic, alpha, alnum, numeric, nozero, md5 and sha1
         * @param    int    number of characters
         * @return    string
         */
        function random_string($type = 'alnum', $len = 8)
        {
            switch ($type) {
                case 'basic':
                    return mt_rand();
                case 'alnum':
                case 'numeric':
                case 'nozero':
                case 'alpha':
                    switch ($type) {
                        case 'alpha':
                            $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            break;
                        case 'alnum':
                            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            break;
                        case 'numeric':
                            $pool = '0123456789';
                            break;
                        case 'nozero':
                            $pool = '123456789';
                            break;
                    }
                    return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
                case 'md5':
                    return md5(uniqid(mt_rand()));
                case 'sha1':
                    return sha1(uniqid(mt_rand(), true));
            }
        }
    }

    if (!function_exists('gen_uuid')) {
        function gen_uuid()
        {
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),

                // 16 bits for "time_mid"
                mt_rand(0, 0xffff),

                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand(0, 0x0fff) | 0x4000,

                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand(0, 0x3fff) | 0x8000,

                // 48 bits for "node"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }
    }
}