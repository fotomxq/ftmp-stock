<?php

/**
 * header头信息操作类
 * @author liuzilu <fotomxq@gmail.com>
 * @version 5
 * @package core
 */
class CoreHeader {

    /**
     * 输出图片
     * @param string $type 图片类型 eg:png|jpeg|gif
     */
    static public function toImg($type = 'png') {
        header('Content-type: image/' . $type . ';charset=utf-8');
    }

    /**
     * 输出Json
     * @param array $data 数据组
     */
    static public function toJson($data) {
        CoreHeader::noCache();
        header('Content-Type: text/plain, charset=utf-8');
        die(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 输出Json
     * 编码JSON并准备输出，主要用于通用的编码行为逻辑，先使用URL编码进行编码，之后交给该函数处理成JSON，最后再由URL解码输出即可。
     * @param array $data 数据组
     */
    static public function toJson2($data) {
        CoreHeader::noCache();
        header('Content-Type: text/plain, charset=utf-8');
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 输出页面UTF-8编码
     * @param string $charset 编码
     */
    static public function toPage($charset = 'utf-8') {
        header('Content-type: text/html; charset=' . $charset);
    }

    /**
     * 输出PDF文件
     * @param string $src 文件路径
     */
    static public function toPDF($src) {
        header('Content-type: application/pdf; charset=utf-8');
        $c = CoreFile::loadFile($src);
        echo $c;
    }

    /**
     * 跳转URL
     * @param string $url URL
     */
    static public function toURL($url) {
        header('Location:' . $url);
    }

    /**
     * 拒绝缓冲
     */
    static public function noCache() {
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
    }

    /**
     * 下载文件
     * @param int $size 大小
     * @param string $fileName 文件名
     * @param string $fileSrc 文件源
     */
    static public function downloadFile($size, $fileName, $fileSrc) {
        $file = fopen($fileSrc, "r");
        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . $size);
        header("Content-Disposition: attachment; filename=" . $fileName);
        echo fread($file, $size);
        fclose($file);
    }

    /**
     * 输出HTML
     * @param  string $data HTML内容
     */
    static public function outHTML($data) {
        CoreHeader::noCache();
        CoreHeader::toPage();
        die($data);
    }

}

?>
