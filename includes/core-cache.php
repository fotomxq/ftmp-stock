<?php

/**
 * 缓冲处理器
 * 缓冲相关变量或值。
 * 
 * @author liuzilu <fotomxq@gmail.com>
 * @version 5
 * @package core
 * @todo 创建图片缓冲
 */
class CoreCache {

    /**
     * 是否开启缓冲器
     * @var boolean
     */
    private $cacheOpen = true;

    /**
     * 缓冲失效时间 (s)
     * @var int
     */
    private $limitTime = 1296000;

    /**
     * 缓冲目录
     * @var string
     */
    private $cacheDir;

    /**
     * 路径分隔符
     * @var string
     */
    private $ds = DIRECTORY_SEPARATOR;

    /**
     * 缓冲文件后缀名
     * @var string
     */
    private $suffix = '.cache';

    /**
     * 缓冲级别
     * 分级存储即将文件名称拆分成多个层级，避免单一文件夹文件过多的问题
     * @var int 
     */
    private $level = 2;

    /**
     * 初始化
     * @param boolean $cacheOpen   是否开启缓冲
     * @param int $limitTime 缓冲失效时间 (s)
     * @param string $cacheDir 缓冲目录
     */
    public function __construct($cacheOpen, $limitTime, $cacheDir) {
        $this->cacheOpen = $cacheOpen;
        $this->limitTime = $limitTime;
        $this->cacheDir = $cacheDir;
    }

    /**
     * 获取一个缓冲值
     * 如果发现缓冲失效，则删除缓冲文件。
     * @param  string $name 标识码
     * @return string       值
     */
    public function get($name) {
        if ($this->cacheOpen == true) {
            $src = $this->getSrc($this->getName($name));
            if (is_file($src) == true) {
                if ($this->checkTime($src) == true) {
                    return $this->loadFile($src);
                } else {
                    $this->clear($name);
                }
            }
        }
        return false;
    }

    /**
     * 设定缓冲
     * @param string $name  标识名
     * @param string $value 值
     * @return boolean 是否成功
     */
    public function set($name, $value) {
        $src = $this->getSrc($this->getName($name), 'database', true);
        return $this->saveFile($src, $value);
    }

    /**
     * 缓冲缩略图
     * 仅支持PNG、JPG、JPEG格式；
     * 图形文件默认以jpg格式存储，节约存储空间，其他格式会被自动转换。
     * @param  string $src 文件路径
     * @param  int $w 宽度
     * @param  int $h 高度
     * @param  boolean $isOutput 是否直接输出图像
     * @return string 返回文件路径
     */
    public function img($src, $w, $h, $isOutput = false) {
        $fileSha1 = sha1_file($src);
        $fileName = $fileSha1 . $w . $h;
        $cacheSrc = $this->getSrc($fileName, 'imgs', true);
        $img = null;
        if (!is_file($cacheSrc)) {
            $fileType = CoreFile::getFileType($src);
            switch ($fileType) {
                case 'jpg':
                case 'jpeg':
                    $img = imagecreatefromjpeg($src);
                    $fileType = 'jpeg';
                    break;
                case 'png':
                    $img = imagecreatefrompng($src);
                    break;
                default:
                    return false;
                    break;
            }
            $imgW = imagesx($img);
            $imgH = imagesy($img);
            //如果图像本身小于压缩大小，则直接输出图像
            if ($imgW <= $w && $imgH <= $h) {
                imagejpeg($img, $cacheSrc);
            } else {
                //压缩图像后输出
                $p = 1;
                $pW = $w / $imgW;
                $pH = $h / $imgH;
                $p = $pW < $pH ? $pW : $pH;
                $newW = $imgW * $p;
                $newH = $imgH * $p;
                $newImg = imagecreatetruecolor($newW, $newH);
                if (!imagecopyresized($newImg, $img, 0, 0, 0, 0, $newW, $newH, $imgW, $imgH)) {
                    return false;
                }
                imagejpeg($newImg, $cacheSrc);
            }
        }
        if ($isOutput) {
            $img = imagecreatefromjpeg($cacheSrc);
            CoreHeader::noCache();
            CoreHeader::toImg('jpeg');
            imagejpeg($img);
        } else {
            return $cacheSrc;
        }
    }

    /**
     * 清理缓冲文件
     * @param  string $name 标识名 (可选)
     * @return boolean      是否成功
     */
    public function clear($name = null) {
        if ($name == null) {
            $s = $this->getSrc('*', 'database', false, true);
            $fileList = glob($s);
            if ($fileList) {
                foreach ($fileList as $v) {
                    CoreFile::deleteDir($v);
                }
            }
            return true;
        } else {
            $src = $this->getSrc($this->getName($name));
            if (is_file($src) == true) {
                return CoreFile::deleteFile($src);
            }
            return true;
        }
    }

    /**
     * 清空图像缓冲
     * @return boolean 是否成功
     */
    public function clearImg() {
        $s = $this->getSrc('*', 'imgs', false, true);
        $fileList = glob($s);
        if ($fileList) {
            foreach ($fileList as $v) {
                CoreFile::deleteDir($v);
            }
        }
        return true;
    }

    /**
     * 保存文件
     * @param  string $src  文件路径
     * @param  string $data 数据
     * @return boolean		是否成功
     */
    private function saveFile($src, $data) {
        return file_put_contents($src, $data);
    }

    /**
     * 读取文件
     * @param  string $src  文件路径
     * @return string		数据
     */
    private function loadFile($src) {
        return file_get_contents($src);
    }

    /**
     * 检查是否超过时间限制
     * @param  string $src 文件路径
     * @return boolean      是否成功
     */
    private function checkTime($src) {
        $fileTime = filemtime($src);
        $nowTime = time();
        $t = (int) $nowTime - (int) $fileTime;
        if ($t < $this->limitTime) {
            return true;
        }
        return false;
    }

    /**
     * 加密标识
     * @param  string $name 标识
     * @return string       标识SHA1值
     */
    private function getName($name) {
        return sha1($name);
    }

    /**
     * 获取文件全部路径
     * @param  string $name 文件名称
     * @param  string $type 数据类型
     * @param  boolean $isCreate 是否创建模式
     * @param  boolean $isClear 是否清理模式
     * @return string       文件路径
     */
    private function getSrc($name, $type = 'database', $isCreate = false, $isClear = false) {
        $res = $this->cacheDir . $this->ds . $type . $this->ds;
        if ($isClear) {
            if ($name == '*') {
                return $res . '*';
            }
        }
        if ($this->level > 0) {
            for ($i = $this->level; $i > 0; $i--) {
                $res = $res . substr($name, ($i * 2), 2) . $this->ds;
            }
        }
        if ($isCreate) {
            CoreFile::newDir($res);
        }
        $res = $res . $name . $this->suffix;
        return $res;
    }

}

?>