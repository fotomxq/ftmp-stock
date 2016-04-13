<?php

/**
 * 临时token计算工具
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package Plug
 */

/**
 * 根据随机码和日期计算随机变量，用于提交表单，避免重复等操作
 * @param string $var 固定的某个字符串，用于避免被撞库
 * @param string $date 日期，可以是任意单位，如2015-01-01 11:15分钟为单位；或者把该项目当作第二个字符串使用，但需要注意随机性。
 * @param int $maxLen 最大保留字符串个数，默认为0，返回全部
 * @return string 结果
 */
function PlugToken($var, $date, $maxLen = 0) {
    $text = $var . $date;
    $sha1 = sha1($text);
    $res = '';
    if ($maxLen > 0) {
        $res = substr($sha1, 0, $maxLen);
    } else {
        $res = $sha1;
    }
    return $res;
}

?>