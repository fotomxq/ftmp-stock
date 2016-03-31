<?php

/**
 * sqlite数据库处理器
 * PDO数据库处理封装。
 * 
 * @author liuzilu <fotomxq@gmail.com>
 * @version 2
 * @package core
 */
class CoreSQLite extends PDO {

    /**
     * 初始化
     * @param string $fileSrc sqlite数据库路径
     * @return PDO 连接成功并返回连接句柄，失败返回NULL
     */
    public function __construct($fileSrc, $encoding = 'utf8') {
        try {
            $dns = 'sqlite:' . $fileSrc;
            if (parent::__construct($dns)) {
                $this->setEncoding($encoding);
                return parent;
            }
        } catch (PDOException $pdoe) {
            return false;
        }
    }

    /**
     * 遍历插入PDO数据
     * @param string $sql SQL语句
     * @param array $attrs 数据数组 eg:array(':id'=>array('value','PDO::PARAM_INT'),...)
     * @param int $resType 返回类型 0-boolean 1-fetch 2-fetchColumn 3-fetchAll 4-lastID
     * @param int $resFetch PDO-FETCH类型，如果返回fetchColumn则为列偏移值
     * @return boolean|PDOStatement 成功则返回PDOStatement句柄，失败返回false
     */
    public function runSQL($sql, $attrs = null, $resType = 0, $resFetch = null) {
        try {
            $sth = $this->prepare($sql);
            if ($attrs != null) {
                foreach ($attrs as $k => $v) {
                    if (is_array($v) == true) {
                        $sth->bindParam($k, $v[0], $v[1]);
                    } else {
                        $sth->bindParam($k, $v);
                    }
                }
            }
            if ($sth->execute() == true) {
                if ($resType == 1) {
                    return $sth->fetch($resFetch);
                } elseif ($resType == 2) {
                    return $sth->fetchColumn($resFetch);
                } elseif ($resType == 3) {
                    return $sth->fetchAll($resFetch);
                } elseif ($resType == 4) {
                    return $this->lastInsertId();
                } else {
                    return true;
                }
            }
            return false;
        } catch (PDOException $e) {
            return false;
        } catch (PDOStatement $e) {
            return false;
        }
    }

    /**
     * 执行插入SQL
     * @param  string $sql SQL语句
     * @return int      插入记录ID
     */
    public function runSQLInsert($sql) {
        try {
            $sth = $this->prepare($sql);
            if ($sth->execute() == true) {
                return $this->lastInsertId();
            }
        } catch (PDOException $e) {
            return 0;
        } catch (PDOStatement $e) {
            return 0;
        }
    }

    /**
     * 执行非查询SQL
     * @param  string $sql SQL语句
     * @return int      影响记录数
     */
    public function runSQLExec($sql) {
        try {
            return $this->exec($sql);
        } catch (PDOException $e) {
            return 0;
        } catch (PDOStatement $e) {
            return 0;
        }
    }

    /**
     * SQL获取数据
     * @param  string  $table     表名称
     * @param  array  $fields    字段组
     * @param  string  $where     条件语句
     * @param  array  $attrs     条件语句对应PDO过滤器
     * @param  int $page      页数，如果为0则表明获取单个数据
     * @param  int $max       页长
     * @param  string  $sortField 排序字段
     * @param  boolean $desc      是否倒序
     * @return array             数据组，如果为空则返回null
     */
    public function sqlSelect($table, $fields, $where = '1', $attrs = null, $page = 0, $max = 10, $sortField = 'id', $desc = false) {
        $descStr = $desc === true ? 'DESC' : 'ASC';
        $sql = 'SELECT `' . implode('`,`', $fields) . '` FROM `' . $table . '` WHERE ' . $where;
        if ($page > 0) {
            $sql .= ' ORDER BY ' . $sortField . ' ' . $descStr . ' LIMIT ' . (($page - 1) * $max) . ',' . $max;
            return $this->runSQL($sql, $attrs, 3, PDO::FETCH_ASSOC);
        }
        return $this->runSQL($sql, $attrs, 1, PDO::FETCH_ASSOC);
    }

    /**
     * SQL插入语句
     * @param  string $table  表名称
     * @param  array $fields 字段组
     * @param  string $value  值字符串
     * @param  array $attrs  PDO过滤器
     * @return int 新的ID
     */
    public function sqlInsert($table, $fields, $value, $attrs) {
        $sql = 'INSERT INTO `' . $table . '`(`' . implode('`,`', $fields) . '`) VALUES(' . $value . ')';
        return $this->runSQL($sql, $attrs, 4);
    }

    /**
     * SQL更新语句
     * @param  string $table 表名称
     * @param  array $sets  SET部分组,eg : array(字段名=>:对应值)
     * @param  string $where 条件语句
     * @param  array $attrs PDO过滤器
     * @return boolean 是否成功
     */
    public function sqlUpdate($table, $sets, $where, $attrs) {
        $sql = 'UPDATE `' . $table . '` SET ';
        $sqlSet = '';
        foreach ($sets as $k => $v) {
            $sqlSet .= ',`' . $k . '` = ' . $v;
        }
        $sql .= substr($sqlSet, 1);
        $sql .= ' WHERE ' . $where;
        return $this->runSQL($sql, $attrs, 0);
    }

    /**
     * SQL删除语句
     * @param  string $table 表名称
     * @param  string $where 条件语句
     * @param  array $attrs PDO过滤器
     * @return boolean        是否成功
     */
    public function sqlDelete($table, $where, $attrs) {
        $sql = 'DELETE FROM `' . $table . '` WHERE ' . $where;
        return $this->runSQL($sql, $attrs, 0);
    }

    /**
     * 设定编码
     * @param string $encoding 编码名称
     * @return boolean
     */
    private function setEncoding($encoding) {
        $bool = false;
        if ($this->status == true) {
            $sql = 'SET NAMES ' . $encoding . '';
            $bool = $this->exec($sql);
        }
        return $bool;
    }

}
