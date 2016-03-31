<?php

/**
 * 标签统一化处理模块
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package plug
 */
class PlugTags {

    /**
     * 数据库操作句柄
     * @var CoreDB
     */
    private $db;

    /**
     * 表名称
     * @var string
     */
    private $tableName;

    /**
     * 初始化
     * @param CoreDB $db 数据库句柄
     * @param string $tableName 表名称
     */
    public function __construct(&$db, $tableName) {
        $this->db = $db;
        $this->tableName = $tableName;
    }

    /**
     * 查看标签
     * @param int $parent 上一级ID
     * @param int $sort 排序字段
     * @return array 数据数组
     */
    public function tagView($parent = 0, $sort = 'sort') {
        $sql = 'select `id`,`parent`,`sort`,`use`,`name`,`ico` from `' . $this->tableName . '` where `parent` = :parent order by :sort';
        $attrs = array(
            ':parent' => array($parent, DPO::PARAM_INT),
            ':sort' => array($sort, PDO::PARAM_STR)
        );
        return $this->db->runSQL($sql, $attrs, 3, PDO::FETCH_ASSOC);
    }

    /**
     * 添加新的标签
     * @param int $parent 上一级ID
     * @param int $sort 排序
     * @param string $name 名称
     * @param string $ico 图标
     * @return int 新的ID
     */
    public function tagAdd($parent, $sort, $name, $ico) {
        $searchSql = 'select `id` from `' . $this->tableName . '` where `parent` = :parent and `name` = :name';
        $searchAttrs = array(':parent' => array($parent, PDO::PARAM_INT), ':name' => array($name, PDO::PARAM_STR));
        $searchRes = $this->db->runSQL($searchSql, $searchAttrs, 2);
        if ($searchRes > 0) {
            return false;
        }
        $sql = 'insert into `' . $this->tableName . '`(`id`,`parent`,`sort`,`use`,`name`,`ico`) values(null,:parent,:sort,0,:name,:ico)';
        $attrs = array(
            ':parent' => array($parent, PDO::PARAM_INT),
            ':sort' => array($sort, PDO::PARAM_INT),
            ':name' => array($name, PDO::PARAM_STR),
            ':ico' => array($ico, PDO::PARAM_STR)
        );
        return $this->db->runSQL($sql, $attrs, 4);
    }

    public function tagEdit($id, $name, $parent, $ico) {
        
    }

    public function tagDel($id) {
        
    }

    public function saveJsonFile($src) {
        
    }

}

?>