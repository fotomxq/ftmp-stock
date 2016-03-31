<?php

/**
 * 文件管理器
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package sys
 */
class SysFile {

    /**
     * 数据库对象
     * @var CoreDB
     */
    private $db;

    /**
     * 文件保存目录
     * @var string
     */
    private $fileDir;

    /**
     * 文件路径分隔符
     * @var string
     */
    private $ds = DIRECTORY_SEPARATOR;

    /**
     * 文件数据表名称
     * @var string
     */
    private $fileTableName;

    /**
     * 文件表字段
     * @var array
     */
    private $fileFields = array('id', 'file_name', 'file_src', 'file_date', 'file_sha1', 'file_size', 'file_type');

    /**
     * 文件服务器关系表名称
     * @var string
     */
    private $fsTableName;

    /**
     * 文件服务器字段
     * @var array
     */
    private $fsFields = array('id', 'file_id', 'server_id');

    /**
     * 初始化
     * @param CoreDB 	$db        		数据库对象
     * @param string 	$fileDir 		文件保存目录
     * @param string 	$fileTableName 	文件数据表名称
     * @param string 	$fsTableName 	文件服务器关系表名称
     */
    public function __construct(&$db, $fileDir, $fileTableName, $fsTableName) {
        $this->db = $db;
        $this->fileDir = $fileDir;
        $this->fileTableName = $fileTableName;
    }

    /**
     * 获取文件信息
     * @param  int $id 文件ID
     * @return array 文件信息
     */
    public function view($id) {
        $where = '`' . $this->fileFields[0] . '` = :id';
        $attrs = array(':id' => array($id, PDO::PARAM_INT));
        return $this->db->sqlSelect($this->fileTableName, $this->fileFields, $where, $attrs);
    }

    /**
     * 查询文件
     * @param  string  $where 条件语句
     * @param  array  $attrs 条件筛选
     * @param  int $page  页数
     * @param  int $max   页长
     * @param  int $sort  排序字段键
     * @param  boolean $desc  是否倒叙
     * @return array 文件信息列
     */
    public function search($where, $attrs, $page = 1, $max = 10, $sort = 0, $desc = true) {
        $sortField = isset($this->fileFields[$sort]) == true ? $this->fileFields[$sort] : $this->fileFields[0];
        return $this->db->sqlSelect($this->fileTableName, $this->fileFields, $where, $attrs, $page, $max, $sortField, $desc);
    }

    /**
     * 上传新的文件
     * @param  $_FILE $uploadfile 上传的文件
     * @return int             新的文件ID，如果失败则返回0
     */
    public function upload($uploadfile) {
        if (is_uploaded_file($uploadfile['tmp_name']) == true) {
            $fileName = $uploadfile['name'];
            return $this->createFile($uploadfile['tmp_name'], $fileName);
        }
        return 0;
    }

    /**
     * 移动本地文件入库
     * @param  string $src 文件路径
     * @return int             新的文件ID，如果失败则返回0
     */
    public function move($src) {
        return $this->createFile($src, basename($src), false);
    }

    /**
     * 删除文件
     * @param  int $id 文件ID
     * @return boolean 是否删除成功
     */
    public function del($id) {
        //获取文件路径
        $res = $this->view($id);
        if ($res) {
            //删除文件
            $fileSrc = $this->fileDir . $this->ds . $res[$this->fileFields[2]];
            if (CoreFile::isFile($fileSrc)) {
                if (CoreFile::deleteFile($fileSrc)) {
                    //删除文件信息
                    $where = '`' . $this->fileFields[0] . '` = :id';
                    $attrs = array(':id' => array($id, PDO::PARAM_INT));
                    return $this->db->sqlDelete($this->fileTableName, $where, $attrs);
                }
            }
        }
        return false;
    }

    /**
     * 检查重复文件
     * <p>如果没有提供要查询的SHA1值，则查询所有重复的文件记录个数和对应的任意ID。</p>
     * @param string $sha1 要匹配的SHA1值，如果提供则查询该SHA1出现的文件
     * @param int $page 步数，数据过大时分段执行
     * @return array 重复文件列，如果没有则返回null
     */
    public function checkRepeat($sha1 = null, $page = null) {
        if ($sha1 == null) {
            $sql = 'SELECT `' . $this->fileFields[0] . '`,COUNT(*) FROM `' . $this->fileTableName . '` GROUP BY ' . $this->fileFields[4] . ' HAVING COUNT(*) > 1';
            if ($page != null) {
                $max = 10;
                $page = (int) $page;
                $sql .= ' LIMIT ' . ($page - 1) * $max . ',' . $max;
            }
            return $this->db->runSQL($sql, null, 3, PDO::FETCH_ASSOC);
        } else {
            $where = '`' . $this->fileFields[4] . '` = :sha1';
            $attrs = array(':sha1' => array($sha1, PDO::PARMA_STR));
            return $this->db->sqlSelect($this->fileTableName, $this->fileFields, $where, $attrs);
        }
        return null;
    }

    /**
     * 检查文件信息匹配度
     * <p>检查文件信息对应的文件是否存在。</p>
     * <p>如果没有提供分步值，也会进行内部分步处理。分步过程中，nofile可能和noinfo不对应，达到某些步后可能会发现其中一个有内容，另外一项已经搜索完成。</p>
     * <p>注意在使用前尽量停止任何建立文件的行为，否则潜在会导致异常。</p>
     * @param int $page 步数，数据过大时分段执行
     * @return array 检查结果，如果没有则返回null
     */
    public function checkInfos($page = null) {
        $arr = array('nofile' => array(), 'noinfo' => array());
        $max = 30;
        if ($page == null) {
            $page = 1;
            while ($res = $this->search('1', null, $page, $max)) {
                if ($res) {
                    foreach ($res as $v) {
                        $vSrc = $this->fileDir . $this->ds . $v[$this->fileFields[2]];
                        if (!CoreFile::isFile($vSrc)) {
                            $arr['nofile'][] = $v;
                        }
                    }
                }
                $page++;
            }
        } else {
            
        }
        return $arr;
    }

    /**
     * 优化文件结构
     * <p>需要配合检查结果使用。</p>
     * @param  array $arr 检查结果
     * @return boolean 优化是否成功
     */
    public function optimizationFile($arr) {
        return false;
    }

    public function moveServer($fileID, $serverID, $targetServerID) {
        
    }

    /**
     * 创建新的文件记录
     * @param  string  $src      文件路径
     * @param  string  $name     文件名称
     * @param  boolean $isUpload 是否是上传文件
     * @return int            文件ID，如果失败则返回0
     */
    private function createFile($src, $name, $isUpload = true) {
        $date = $this->getTime();
        $sha1 = $this->getSha1($src);
        $fileSize = $this->getSize($src);
        $fileType = $this->getTypeMeta($src);
        $fileSrc = $this->createNewSrc($date, $sha1);
        $fileSrcD = $this->fileDir . $this->ds . $fileSrc;
        if ($fileSrc) {
            if ($isUpload == true) {
                if (!CoreFile::moveUpload($src, $fileSrcD)) {
                    return 0;
                }
            } else {
                if (!CoreFile::copyFile($src, $fileSrcD)) {
                    return 0;
                }
            }
            $vals = 'NULL,:name,:src,:date,:sha1,:size,:type';
            $attrs = array(':name' => array($name, PDO::PARAM_STR), ':src' => array($fileSrc, PDO::PARAM_STR), ':date' => array($date, PDO::PARAM_STR), ':sha1' => array($sha1, PDO::PARAM_STR), ':size' => array($fileSize, PDO::PARAM_INT), ':type' => array($fileType, PDO::PARAM_STR));
            return $this->db->sqlInsert($this->fileTableName, $this->fileFields, $vals, $attrs);
        }
        return 0;
    }

    /**
     * 获取新的文件路径
     * @param  string $date 日期时间
     * @param  string $sha1 文件SHA1值
     * @return string       文件路径
     */
    private function createNewSrc($date, $sha1) {
        $dir = substr($date, 0, 4) . substr($date, 5, 2) . $this->ds . substr($date, 8, 2);
        if (CoreFile::newDir($dir)) {
            return $dir . $this->ds . $sha1 . '_' . rand(1, 9999);
        }
        return false;
    }

    /**
     * 获取当前时间
     * @return string 时间
     */
    private function getTime() {
        return Date('Y-m-d H:i:s');
    }

    /**
     * 获取文件大小KB
     * @param  string $src 文件路径
     * @return int      文件大小KB
     */
    private function getSize($src) {
        return filesize($src) / 1024;
    }

    /**
     * 获取文件Meta文件类型
     * @param  string $src 文件路径
     * @return string      文件类型
     */
    private function getTypeMeta($src) {
        return mime_content_type($src);
    }

    /**
     * 获取文件SHA1值
     * @param  string $src 文件路径
     * @return string      文件SHA1
     */
    private function getSha1($src) {
        return sha1_file($src);
    }

}

?>