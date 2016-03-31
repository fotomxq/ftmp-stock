<?php

/**
 * IP处理器
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package core
 * @todo 修正IP实际地址无法获取问题
 */
class CoreIP {

    /**
     * 获取IP地址
     * @return string IP地址
     */
    public function getIP() {
        $ip = '';
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["REMOTE_ADDR"])) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "0.0.0.0";
        }
        return $ip;
    }

    /**
     * 获取IP物理地址
     * @param  string $ip IP地址
     * @return string     地址
     */
    public function getIPAddress($ip) {
        $url = 'http://api.map.baidu.com/location/ip?ak=' . BAIDU_KEY . '&ip=' . $ip;
        $dataJson = file_get_contents($url);
        if ($dataJson) {
            $data = json_decode($dataJson);
            return $data;
        }
        return '';
    }

}

?>