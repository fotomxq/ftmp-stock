<?php

/**
 * 文件操作封装
 * @author liuzilu <fotomxq@gmail.com>
 * @version 2
 * @package core
 */
class CoreFile {

    /**
     * 路径分隔符
     * @var string 
     */
    static public $ds = DIRECTORY_SEPARATOR;

    /**
     * 是否为文件
     * @param string $src 路径
     * @return boolean 是否成功
     */
    static public function isFile($src) {
        return is_file($src);
    }

    /**
     * 是否为目录
     * @param string $src 路径
     * @return boolean
     */
    static public function isDir($src) {
        return is_dir($src);
    }

    /**
     * 读出文件内容
     * @param string $src 路径
     * @return string 字符串
     */
    static public function loadFile($src) {
        return file_get_contents($src);
    }

    /**
     * 写入文件
     * @param string $src 路径
     * @param string $data 数据
     * @param boolean $append 是否后续插入
     * @return boolean 是否成功
     */
    static public function saveFile($src, $data, $append = false) {
        if ($append == true) {
            return file_put_contents($src, $data, FILE_APPEND);
        } else {
            return file_put_contents($src, $data);
        }
    }

    /**
     * 拷贝文件
     * @param string $src 原路径
     * @param string $target 目标路径
     * @return boolean 是否成功
     */
    static public function copyFile($src, $target) {
        return copy($src, $target);
    }

    /**
     * 删除文件
     * @param string $src 路径
     * @return boolean 是否成功
     */
    static public function deleteFile($src) {
        return unlink($src);
    }

    /**
     * 移动上传文件
     * @param string $src 原路径
     * @param string $target 新路径
     * @return boolean 是否成功
     */
    static public function moveUpload($src, $target) {
        return move_uploaded_file($src, $target);
    }

    /**
     * 移动或重命名文件或文件夹
     * @param string $src 原路径
     * @param string $target 新路径
     * @return boolean 是否成功
     */
    static public function cutF($src, $target) {
        return rename($src, $target);
    }

    /**
     * 搜索目录
     * @param string $src 路径
     * @param int $flags 筛选值，如GLOB_ONLYDIR
     * @return array|null 内容数组，找不到返回NULL
     */
    static public function searchDir($src, $flags = null) {
        if ($flags) {
            return glob($src, $flags);
        } else {
            return glob($src);
        }
    }

    /**
     * 创建目录
     * @param string $src 路径
     * @return boolean 是否成功
     */
    static public function newDir($src) {
        if (CoreFile::isDir($src) == true) {
            return true;
        } else {
            return mkdir($src, 0777, true);
        }
    }

    /**
     * 拷贝文件夹
     * @param string $src 原路径
     * @param string $target 目标路径
     * @return boolean 是否成功
     */
    static public function copyDir($src, $target) {
        if (CoreFile::isDir($src) == true) {
            if (CoreFile::newDir($target) == true) {
                $search = $src . CoreFile::$ds . '*';
                $list = CoreFile::searchDir($search);
                if ($list) {
                    foreach ($list as $v) {
                        $vTarget = $target . CoreFile::$ds . basename($v);
                        if (CoreFile::copyDir($v, $vTarget) == false) {
                            return false;
                        }
                    }
                }
                return true;
            } else {
                return false;
            }
        } else {
            return CoreFile::copyFile($src, $target);
        }
    }

    /**
     * 删除文件夹
     * @param string $src 路径
     * @return boolean 是否成功
     */
    static public function deleteDir($src) {
        if (CoreFile::isDir($src) == true) {
            $search = $src . CoreFile::$ds . '*';
            $list = CoreFile::searchDir($search);
            if ($list) {
                foreach ($list as $v) {
                    if (CoreFile::deleteDir($v) == false) {
                        return false;
                    }
                }
            }
            return rmdir($src);
        } else {
            return CoreFile::deleteFile($src);
        }
    }

    /**
     * 创建Zip文件
     * @param string $src 要压缩的文件或目录
     * @param string $target 压缩包路径
     * @param boolean $append 如果文件存在，是否向后添加
     * @return boolean 是否成功
     */
    static public function createZip($src, $target, $append = true) {
        if (class_exists('ZipArchive') == true) {
            $zip = new ZipArchive();
            if (CoreFile::isFile($target) == true) {
                if ($append == true) {
                    if (CoreFile::deleteFile($target) == true) {
                        $zip->open($target, ZIPARCHIVE::CREATE);
                    }
                } else {
                    $zip->open($target);
                }
            } else {
                $zip->open($target, ZIPARCHIVE::CREATE);
            }
            $return = CoreFile::createZipAdd($zip, $src);
            $zip->close();
            return $return;
        }
    }

    /**
     * 向压缩包添加内容
     * @param ZipArchive $zip 压缩操作句柄
     * @param string $src 要压缩内容路径
     * @param string $target 压缩包内目标路径
     * @return boolean 是否成功
     */
    static public function createZipAdd(&$zip, $src, $target = null) {
        $return = false;
        if ($target == null) {
            $target = basename($src);
        }
        if (corefile::isDir($src)) {
            if ($zip->addEmptyDir($target) == true) {
                $search = $src . CoreFile::$ds . '*';
                $dirList = corefile::searchDir($search);
                foreach ($dirList as $v) {
                    $vSrc = basename($v);
                    $vTarget = $target . DS . $vSrc;
                    $return = corefile::createZipAdd($zip, $v, $vTarget);
                }
            }
        } else {
            $return = $zip->addFile($src, $target);
        }
        return $return;
    }

    /**
     * 解压缩压缩文件
     * @param string $src 压缩包路径
     * @param string $target 解压到路径
     * @return boolean 是否成功
     */
    static public function extractZip($src, $target) {
        if (class_exists('ZipArchive') == true) {
            $zip = new ZipArchive();
            if (CoreFile::isDir($target) == false) {
                CoreFile::newDir($target);
            }
            if ($zip->open($src) === true) {
                $return = $zip->extractTo($target);
                $zip->close();
                return true;
            }
        }
        return false;
    }

    /**
     * 获取文件后缀类型
     * @param string $src 文件名，不包含路径
     * @return string 文件后缀类型
     */
    static public function getFileType($src) {
        $arr = explode('.', $src);
        if (count($arr) > 1) {
            return $arr[count($arr) - 1];
        }
        return null;
    }

    /**
     * 获取路径文件名称部分
     * @param string $src 路径
     * @return string 文件名称部分
     */
    static public function getBasename($src) {
        return preg_replace('/^.+[\\\\\\/]/', '', $src);
    }

}

?>
