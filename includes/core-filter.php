<?php

/**
 * 用户输入过滤器
 * @author liuzilu <fotomxq@gmail.com>
 * @version 8
 * @package core
 */
class CoreFilter {

    /**
     * 获取UTF-8文本
     * 会根据系统不同类型进行甄别，主要用于文件系统，其他方面不可用
     * @param string $str 文本
     * @return string 新的文本
     */
    static public function getUTF8($str) {
        if (php_uname('s') == 'Windows NT') {
            //$encode = mb_detect_encoding($str, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
            return mb_convert_encoding($str, 'utf-8', array('UTF-8', 'ASCII', 'GB2312', 'GBK', 'BIG5'));
        } else {
            return $str;
        }
    }

    /**
     * 专用获取UTF-8数据
     * 会根据系统不同类型进行甄别，主要用于文件系统，其他方面不可用
     * @param string $str 文本
     * @return string 新的文本
     */
    static public function getGBKUTF8($str) {
        if (php_uname('s') == 'Windows NT') {
            return iconv('gbk', 'utf-8', $str);
        } else {
            return $str;
        }
    }

    /**
     * 专用获取GBK数据
     * 会根据系统不同类型进行甄别，主要用于文件系统，其他方面不可用
     * @param string $str 文本
     * @return string 新的文本
     */
    static public function getUTF8GBK($str) {
        if (php_uname('s') == 'Windows NT') {
            return iconv('utf-8', 'gbk', $str);
        } else {
            return $str;
        }
    }

    /**
     * 获取gb2312文本
     * 会根据系统不同类型进行甄别，主要用于文件系统，其他方面不可用
     * @param string $str 文本
     * @return string 新的文本
     */
    static public function getGBK($str) {
        if (php_uname('s') == 'Windows NT') {
            return iconv('utf-8', 'gb2312', $str);
        } else {
            return $str;
        }
    }

    /**
     * 过滤整数
     * @param int $int 数字
     * @return int 过滤后数字
     */
    static public function getInt($int) {
        return filter_var($int, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * 过滤email
     * @param string $str 邮箱地址
     * @return string 过滤后字符串
     */
    static public function getEmail($str, $len = null) {
        $res = $str;
        if ($len) {
            $res = CoreFilter::getSubStr($res, $len);
        }
        $res = filter_var($res, FILTER_VALIDATE_EMAIL);
        return $res;
    }

    /**
     * 过滤URL
     * @param string $str URL
     * @return string 过滤后字符串
     */
    static public function getURL($str, $len = null) {
        $res = $str;
        if ($len) {
            $res = CoreFilter::getSubStr($res, $len);
        }
        $res = filter_var($res, FILTER_VALIDATE_URL);
        return $res;
    }

    /**
     * 检查字符串
     * @param string $str 字符串
     * @param int $length 长度
     * @return boolean 是否通过
     */
    static public function isString($str, $min, $max) {
        $strlen = CoreFilter::getStrLen($str);
        if ($strlen >= $min && $strlen <= $max) {
            if (filter_var($str)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 过滤字符串
     * @param string $str 字符串
     * @param int $length 长度
     * @param int $start 起始
     * @param boolean $nohtml 是否去HTML标签
     * @param boolean $stripTag 是否去特殊字符
     * @return string 过滤后的字符串
     */
    static public function getString($str, $length, $start = 0, $nohtml = false, $stripTag = false) {
        $res = CoreFilter::getSubStr($str, $length, $start);
        if ($res) {
            $res = filter_var($res);
            if ($stripTag == true) {
                $res = strip_tags($res);
            } else {
                if ($nohtml == true) {
                    $res = htmlspecialchars($res);
                }
            }
            return $res;
        }
        return '';
    }

    /**
     * 截取字符串
     * @param string $string 字符串
     * @param int $sublen 长度
     * @param int $start 起始位置
     * @param string $code 编码类型
     * @return string 截取后的字符串
     */
    static public function getSubStr($string, $sublen, $start = 0, $code = 'UTF-8') {
        if ($code == 'UTF-8') {
            $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
            preg_match_all($pa, $string, $t_string);
            if (count($t_string[0]) - $start > $sublen)
                return join('', array_slice($t_string[0], $start, $sublen)) . "...";
            return join('', array_slice($t_string[0], $start, $sublen));
        }
        else {
            $start = $start * 2;
            $sublen = $sublen * 2;
            $strlen = strlen($string);
            $tmpstr = '';

            for ($i = 0; $i < $strlen; $i++) {
                if ($i >= $start && $i < ($start + $sublen)) {
                    if (ord(substr($string, $i, 1)) > 129) {
                        $tmpstr.= substr($string, $i, 2);
                    } else {
                        $tmpstr.= substr($string, $i, 1);
                    }
                }
                if (ord(substr($string, $i, 1)) > 129)
                    $i++;
            }
            if (strlen($tmpstr) < $strlen)
                $tmpstr.= "...";
            return $tmpstr;
        }
    }

    /**
     * 获取UTF8字符串长度
     * @param string $str 字符串
     * @param string $encoding 编码
     * @return int 长度
     */
    static public function getStrLen($str, $encoding = 'UTF-8') {
        return mb_strlen($str, $encoding);
    }

}

?>
