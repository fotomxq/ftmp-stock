<?php

/**
 * 配置处理器
 * 处理数据库配置信息
 * 
 * @author liuzilu <fotomxq@gmail.com>
 * @version 2
 * @package sys
 */
class SysConfig {

    /**
     * 数据库对象
     * @var CoreDB
     */
    private $db;

    /**
     * 数据表名称
     * @var string
     */
    private $tableName;

    /**
     * 字段组
     * @var array
     */
    private $fields = array('id', 'config_name', 'config_value');

    /**
     * 初始化
     * @param CoreDB $db        数据库对象
     * @param string $tableName 数据表名称
     */
    public function __construct(&$db, $tableName) {
        $this->db = $db;
        $this->tableName = $tableName;
    }

    /**
     * 获取配置
     * @param  int|string $target 目标ID或Name
     * @return string         值
     */
    public function get($target) {
        $sql = 'SELECT `' . $this->fields[2] . '` FROM `' . $this->tableName . '` WHERE ';
        $attrs;
        if (is_int($target) == true) {
            $sql .= '`' . $this->fields[0] . '` = :target';
            $attrs = array(':target' => array($target, PDO::PARAM_INT));
        } else {
            $sql .= '`' . $this->fields[1] . '` = :target';
            $attrs = array(':target' => array($target, PDO::PARAM_STR));
        }
        return $this->db->runSQL($sql, $attrs, 2, 0);
    }

    /**
     * 保存配置
     * @param  int|string $target 目标ID或Name
     * @param  string $value  新的值
     * @return boolean         是否成功
     */
    public function save($target, $value) {
        $sql = 'UPDATE `' . $this->tableName . '` SET `' . $this->fields[2] . '` = :value WHERE ';
        $attrs = array(':value' => array($value, PDO::PARAM_STR));
        if (is_int($target) == true) {
            $sql .= '`' . $this->fields[0] . '` = :target';
            $attrs[':target'] = array($target, PDO::PARAM_INT);
        } else {
            $sql .= '`' . $this->fields[1] . '` = :target';
            $attrs[':target'] = array($target, PDO::PARAM_STR);
        }
        return $this->db->runSQL($sql, $attrs, 0);
    }

    /**
     * 注册新的配置
     * @param string $name  名称
     * @param string $value 初始化值 (注意要提前过滤引号等特殊字符)
     * @return int         新的ID
     */
    public function add($name, $value) {
        $sql = 'INSERT INTO `' . $this->tableName . '`(`' . $this->fields[1] . '`,`' . $this->fields[2] . '`) VALUES(\'' . $name . '\',\'' . $value . '\')';
        return $this->db->runSQLInsert($sql);
    }

    /**
     * 删除配置项
     * @param  int|string $target 目标ID或名称
     * @return boolean         是否成功
     */
    public function del($target) {
        $sql = 'DELETE FROM `' . $this->tableName . '` WHERE ';
        $attrs;
        if (is_int($target) == true) {
            $sql .= '`' . $this->fields[0] . '` = :target';
            $attrs = array(':target' => array($target, PDO::PARAM_INT));
        } else {
            $sql .= '`' . $this->fields[1] . '` = :target';
            $attrs = array(':target' => array($target, PDO::PARAM_STR));
        }
        return $this->db->runSQL($sql, $attrs, 0);
    }

}

?>