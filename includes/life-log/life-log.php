<?php

/**
 * 生活日志
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package life-log
 */
class AppLifeLog {

    /**
     * 用户数据路径
     * @var string
     */
    private $userDataSrc = '';

    /**
     * 用户数据库路径
     * @var string
     */
    private $dbSrc = '';

    /**
     * 数据路径
     * @var string 
     */
    private $dataSrc = '';

    /**
     * 数据库对象
     * @var CoreSQLite
     */
    private $db;

    /**
     * 初始化
     * @param string $userName 用户名称
     * @param string $dataSrc 数据存放路径
     */
    public function __construct($userName, $dataSrc) {
        $this->dataSrc = $dataSrc;
        $this->setUser($userName);
    }

    public function getList($startDateTime = '', $endDateTime = '', $tags = null, $searchDes = '', $page = 1, $max = 30, $desc = false) {
        $sql = 'select log.id,log.time,log.des from `log`,`log_tag` where 1';
        if ($startDateTime) {
            $sql .= ' AND ';
        }
        if ($endDateTime) {
            
        }
        if ($tags) {
            
        } else {
            
        }
        $sqlDesc = $desc ? 'DESC' : 'ASC';
        $sql .= ' ORDER BY log.time ' . $sqlDesc . ' LIMIT ' . $page . ',' . ($page - 1) * $max;
    }

    public function edit($id, $dateTime, $des, $tags) {
        
    }

    /**
     * 删除日志
     * @param int $id ID
     * @return boolean 是否成功
     */
    public function del($id) {
        $sql = 'delete from `log` where `id` = :id';
        $attrs = array(':id' => array($id, PDO::PARAM_INT));
        $res = $this->db->runSQL($sql, $attrs);
        if (!$res) {
            return false;
        }
        $sqlLogTag = 'delete from `log_tag` where `log_id` = :id';
        return $this->db->runSQL($sqlLogTag, $attrs);
    }

    /**
     * 编辑标签
     * @param array $tags 标签组 array('tagA','tagB',...)
     * @return boolean
     */
    public function editTag($tags) {
        $sqlTags = 'select * from `tags`';
        $resTags = $this->runSQL($sqlTags, null, 3, PDO::FETCH_ASSOC);
        $addTags = null;
        $delTags = null;
        if (!$resTags) {
            if ($tags) {
                $addTags = $tags;
            }
        } else {
            if ($tags) {
                $newResTags = null;
                foreach ($resTags as $v) {
                    $newResTags[] = $v['name'];
                }
                foreach ($newResTags as $v) {
                    if (!in_array($v, $tags)) {
                        $delTags[] = $v;
                    }
                }
                foreach ($tags as $v) {
                    if (!in_array($v, $newResTags)) {
                        $addTags[] = $v;
                    }
                }
            } else {
                $sqlDelAll = 'delete from `tag` where 1';
                return $this->runSQL($sqlDelAll);
            }
        }
        if ($addTags) {
            $sqlAdd = '';
            $attrsAdd = null;
            foreach ($addTags as $k => $v) {
                $sqlAdd.= ',(null,:tag' . $k . ')';
                $attrsAdd[':tag' . $k] = array($v, PDO::PARAM_STR);
            }
            $sqlAdd = 'insert into `tag`(`id`,`name`) values' . substr($sqlAdd, 1);
            if (!$this->runSQL($sqlAdd, $attrsAdd, 4)) {
                return false;
            }
        }
        if ($delTags) {
            $sqlDel = '';
            $attrsDel = null;
            foreach ($delTags as $k => $v) {
                $sqlDel .= ' or `name` = :tag' . $k;
                $attrsDel[':tag' . $k] = array($v, PDO::PARAM_STR);
            }
            $sqlDel = 'delete from `tag` where ' . substr($sqlDel, 4);
            if (!$this->runSQL($sqlDel, $attrsDel, 0)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 执行SQL
     * @param string $sql SQL语句
     * @param array $attrs 数据数组 eg:array(':id'=>array('value','PDO::PARAM_INT'),...)
     * @param int $resType 返回类型 0-boolean 1-fetch 2-fetchColumn 3-fetchAll 4-lastID
     * @param int $resFetch PDO-FETCH类型，如果返回fetchColumn则为列偏移值
     * @return boolean|PDOStatement 成功则返回PDOStatement句柄，失败返回false
     */
    private function runSQL($sql, $attrs = null, $resType = 0, $resFetch = null) {
        if (!$this->db) {
            return false;
        }
        return $this->db->runSQL($sql, $attrs, $resType, $resFetch);
    }

    /**
     * 设定用户名称及必要数据
     * @param string $userName 用户名称
     */
    private function setUser($userName) {
        $this->userDataSrc = $this->dataSrc . DS . $userName;
        if (!is_dir($this->userDataSrc)) {
            CoreFile::newDir($this->userDataSrc);
        }
        $this->dbSrc = $this->userDataSrc . DS . 'data.sqlite';
        if (!is_file($this->dbSrc)) {
            $dbCopySrc = DIR_DATA . DS . 'life-log' . DS . 'data.sqlite';
            CoreFile::copyFile($dbCopySrc, $this->dbSrc);
        }
        $this->dbSqlite = new CoreSQLite($this->dbSrc);
    }

}
