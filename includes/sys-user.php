<?php

/**
 * 用户处理器
 * @author liuzilu <fotomxq@gmail.com>
 * @version 7
 * @package sys
 */
class SysUser {

    /**
     * 数据库对象
     * @var CoreDB
     */
    private $db;

    /**
     * 用户表名称
     * @var string
     */
    private $tableNameUser;

    /**
     * 元数据表名称
     * @var string
     */
    private $tableNameMeta;

    /**
     * 用户表字段组
     * @var array
     */
    public $fieldsUser = array('id', 'user_nicename', 'user_login', 'user_passwd', 'user_date', 'user_ip', 'user_status');

    /**
     * 用户元数据字段组
     * @var array
     */
    public $fieldsMeta = array('id', 'user_id', 'meta_name', 'meta_value');

    /**
     * 登录状态
     * logout-未登录 logged-已登录 disable-禁用 forever-永久在线
     * @var array
     */
    private $status = array('logout' => 0, 'logged' => 1, 'disable' => 2, 'forever' => 3);

    /**
     * 登录状态Session变量名称
     * @var string
     */
    private $sessionLoginName = 'login';

    /**
     * 记录超时时间Session变量名称
     * @var string
     */
    private $sessionLimitTime = 'login-time';

    /**
     * 登录超时时间
     * @var int
     */
    private $limitTime = 1800;

    /**
     * 权限元数据名称
     * @var string
     */
    public $powerMetaName = 'POWER';

    /**
     * 权限列
     * VISITOR - 游客 ; ADMIN - 管理员 ; NORMAL - 普通用户
     * @var array
     */
    public $powerValues = array('VISITOR', 'ADMIN', 'NORMAL');

    /**
     * 应用元数据名称
     * @var string
     */
    public $appMetaName = 'APP';

    /**
     * 保存用户权限组的session变量名称
     * @var string
     */
    private $powerSessionName = 'user-powers';

    /**
     * 应用元数据列
     * <p>需要在配置文件中指定。</p>
     * @var array
     */
    public $appMetaValues;

    /**
     * 初始化
     * @param CoreDB $db               数据库对象
     * @param string $tableNameUser    用户表名称
     * @param string $tableNameMeta    元数据表名称
     * @param string $sessionLoginName 登录状态Session变量名称
     * @param string $limitTime 登录超时时间 (秒)
     */
    public function __construct(&$db, $tableNameUser, $tableNameMeta, $sessionLoginName, $limitTime) {
        $this->db = $db;
        $this->tableNameUser = $tableNameUser;
        $this->tableNameMeta = $tableNameMeta;
        $this->sessionLoginName = $sessionLoginName;
        $this->limitTime = $limitTime;
        $this->sessionLimitTime = $this->sessionLoginName . '-time';
    }

    /**
     * 登录
     * @param  string  $ip      IP地址
     * @param  string  $login   登录用户名
     * @param  string  $passwd  登录密码
     * @param  boolean $forever 是否永久在线，需要保证客户端Cookie保留session会话为基础
     * @return boolean 是否成功
     */
    public function login($ip, $login, $passwd, $forever = false) {
        if ($this->logged($ip) == true) {
            return true;
        } else {
            //$passwdSha1 = $this->getPasswd($passwd);
            $where = '`' . $this->fieldsUser[2] . '` = :login and `' . $this->fieldsUser[3] . '` = :passwd';
            $attrs = array(':login' => array($login, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT), ':passwd' => array($passwd, PDO::PARAM_STR));
            $res = $this->db->sqlSelect($this->tableNameUser, array($this->fieldsUser[0], $this->fieldsUser[6]), $where, $attrs);
            if ($res) {
                if ($res['id'] > 0 && $res['user_status'] != $this->status['disable']) {
                    $this->saveLoginStatus($res['id']);
                    $status = $forever == true ? 'forever' : 'logged';
                    $this->saveLastTime();
                    return $this->updateStatus($res['id'], $ip, $status);
                }
            }
        }
        return false;
    }

    /**
     * 判断登录状态
     * <p>如果超时，则自动退出</p>
     * @param string $ip 当前客户端的IP地址
     * @return int 用户ID，如果不在线则为0
     */
    public function logged($ip) {
        $nowID = $this->getLoginStatus();
        if ($nowID > 0) {
            $res = $this->viewUser($nowID);
            if ($res) {
                if ($res['user_status'] == $this->status['forever'] && $res['user_ip'] == $ip) {
                    //如果永久在线
                    $this->saveLastTime();
                    return $res['id'];
                } else if ($res['user_status'] == $this->status['logged'] && $res['user_ip'] == $ip) {
                    //否则普通登录，则判定登录时间限制
                    $lastTime = $this->getLastTime();
                    $limitTime = time() - $lastTime;
                    if ($limitTime <= $this->limitTime) {
                        $this->saveLastTime();
                        return $res['id'];
                    } else {
                        $this->logout($ip);
                    }
                }
            }
        }
        return 0;
    }

    /**
     * 退出登录
     * @param  string  $ip     IP地址
     * @param  int $userID 用户ID，如果提供则强制下线某用户
     */
    public function logout($ip, $userID = 0) {
        $id;
        if ($userID > 0) {
            $id = $userID;
        } else {
            $id = $this->getLoginStatus();
        }
        if ($id > 0) {
            $this->updateStatus($id, $ip, 'logout');
            $this->clearLoginStatus();
        }
    }

    /**
     * 查看用户
     * @param  int $id 用户ID
     * @return array     数据，如果为空则返回null
     */
    public function viewUser($id) {
        $sql = 'SELECT `' . implode('`,`', $this->fieldsUser) . '` FROM `' . $this->tableNameUser . '` WHERE `' . $this->fieldsUser[0] . '` = :id';
        $attrs = array(':id' => array($id, PDO::PARAM_INT));
        return $this->db->runSQL($sql, $attrs, 1, PDO::FETCH_ASSOC);
    }

    /**
     * 查询用户列表
     * @param  string  $where 条件语句，如果不存在则为'1'
     * @param  array  $attrs 条件语句对应PDO过滤器，不存在则为null
     * @param  int $page  页数
     * @param  int $max   页长
     * @param  int $sort  排序字段键值
     * @param  boolean $desc  是否倒序
     * @return array         数据组，如果为空则返回null
     */
    public function viewUserList($where = '1', $attrs = null, $page = 1, $max = 10, $sort = 0, $desc = false) {
        $sortFields = isset($this->fieldsUser[$sort]) == true ? $this->fieldsUser[$sort] : $this->fieldsUser[0];
        $descStr = $desc == true ? 'DESC' : 'ASC';
        $sql = 'SELECT `' . implode('`,`', $this->fieldsUser) . '` FROM `' . $this->tableNameUser . '` WHERE ' . $where . ' ORDER BY ' . $sortFields . ' ' . $descStr . ' LIMIT ' . (($page - 1) * $max) . ',' . $max;
        return $this->db->runSQL($sql, $attrs, 3, PDO::FETCH_ASSOC);
    }

    /**
     * 获取条件下用户记录数
     * @param  string $where 条件
     * @param  array $attrs 条件PDO过滤
     * @return int        总数
     */
    public function viewUserListCount($where = '1', $attrs = null) {
        $sql = 'SELECT COUNT(`' . $this->fieldsUser[0] . '`) FROM `' . $this->tableNameUser . '` WHERE ' . $where;
        return $this->db->runSQL($sql, $attrs, 2, 0);
    }

    /**
     * 查看用户某个元数据
     * @param  int $userID   ID
     * @param  string $metaName 元数据名称
     * @return array 数据组，如果为空则返回null
     */
    public function viewMeta($userID, $metaName) {
        $sql = 'SELECT `' . implode('`,`', $this->fieldsMeta) . '` FROM `' . $this->tableNameMeta . '` WHERE `' . $this->fieldsMeta[1] . '` = :userID and `' . $this->fieldsMeta[2] . '` = :metaName';
        $attrs = array(':userID' => array($userID, PDO::PARAM_INT), ':metaName' => array($metaName, PDO::PARAM_STR));
        return $this->db->runSQL($sql, $attrs, 3, PDO::FETCH_ASSOC);
    }

    /**
     * 获取用户所有元数据
     * @param  int $userID 用户ID
     * @return array         数据组，如果为空则返回null
     */
    public function viewMetaList($userID) {
        $sql = 'SELECT `' . implode('`,`', $this->fieldsMeta) . '` FROM `' . $this->tableNameMeta . '` WHERE `' . $this->fieldsMeta[1] . '` = :userID';
        $attrs = array(':userID' => array($userID, PDO::PARAM_INT));
        return $this->db->runSQL($sql, $attrs, 3, PDO::FETCH_ASSOC);
    }

    /**
     * 获取用户的权限组
     * @param int $userID 需要获取的用户ID
     * @param array $apps 应用参数列，配置文件中的应用列变量
     * @return array 数据数组，对应所有已知权限和应用的权限Bool值
     */
    public function viewUserPowers($userID, $apps) {
        $userPowerBool = array(
            'admin' => false,
            'visitor' => false,
            'normal' => false
        );
        foreach ($apps as $v) {
            $userPowerBool[$v['name']] = false;
        }
        $userPowerMeta = $this->viewMeta($userID, $this->powerMetaName);
        $userPowerList = $userPowerMeta[0][$this->fieldsMeta[3]];
        if ($userPowerList) {
            $userPowerList = strtolower($userPowerList);
            $userPowers = explode('|', $userPowerList);
            foreach ($userPowers as $v) {
                $userPowerBool[$v] = true;
            }
        }
        return $userPowerBool;
    }

    /**
     * 获取当前用户的权限集合
     * 该方法可自动缓冲数据到SESSION变量组，避免反复加载
     * @param int $userID 当前用户ID
     * @param array $apps 应用参数列，配置文件中的应用列变量
     * @return array 数据数组
     */
    public function viewNowUserPowers($userID, $apps) {
        if (!isset($_SESSION[$this->powerSessionName])) {
            $_SESSION[$this->powerSessionName] = $this->viewUserPowers($userID, $apps);
        }
        return $_SESSION[$this->powerSessionName];
    }

    /**
     * 添加新的用户
     * @param string $nicename 昵称
     * @param string $login    登录用户名
     * @param string $passwd   登录密码
     * @return int         新的用户ID，如果失败则返回0
     */
    public function addUser($nicename, $login, $passwd) {
        //判断用户名是否存在
        $attrs = array(':login' => array($login, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT));
        $res = $this->db->sqlSelect($this->tableNameUser, array($this->fieldsUser[0]), '`' . $this->fieldsUser[2] . '` = :login', $attrs);
        if ($res) {
            return false;
        }
        //创建用户
        $passwdSha1 = $this->getPasswd($passwd);
        $attrsInsert = array(
            ':nicename' => array($nicename, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT),
            ':login' => array($login, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT),
            ':passwd' => array($passwdSha1, PDO::PARAM_STR),
            ':status' => array($this->status['logout'], PDO::PARAM_INT));
        return $this->db->sqlInsert($this->tableNameUser, $this->fieldsUser, 'NULL,:nicename,:login,:passwd,NOW(),\'0.0.0.0\',:status', $attrsInsert);
    }

    /**
     * 添加元数据
     * @param int $userID 用户ID
     * @param string $name   元数据名称
     * @param string $value  元数据值
     * @return int 新的ID
     */
    public function addMeta($userID, $name, $value) {
        $res = $this->viewUser($userID);
        if ($res) {
            $attrs = array(
                ':userID' => array($userID, PDO::PARAM_INT),
                ':name' => array($name, PDO::PARAM_STR),
                ':value' => array($value, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT)
            );
            return $this->db->sqlInsert($this->tableNameMeta, $this->fieldsMeta, 'NULL,:userID,:name,:value', $attrs);
        }
        return false;
    }

    /**
     * 编辑用户
     * @param  int $id       用户ID
     * @param  string $nicename 昵称
     * @param  string $passwd   密码
     * @return boolean 是否成功
     */
    public function editUser($id, $nicename = null, $passwd = null, $status = null) {
        $where = '`' . $this->fieldsUser[0] . '` = :id';
        $attrs = array(':id' => array($id, PDO::PARAM_INT));
        $sets;
        if ($nicename) {
            $attrs[':nicename'] = array($nicename, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
            $sets[$this->fieldsUser[1]] = ':nicename';
        }
        if ($passwd) {
            $passwdSha1 = $this->getPasswd($passwd);
            $attrs[':passwd'] = array($passwdSha1, PDO::PARAM_STR);
            $sets[$this->fieldsUser[3]] = ':passwd';
        }
        if ($status !== null) {
            $attrs[':status'] = array($this->status[$status], PDO::PARAM_INT);
            $sets[$this->fieldsUser[6]] = ':status';
        }
        return $this->db->sqlUpdate($this->tableNameUser, $sets, $where, $attrs);
    }

    /**
     * 编辑元数据
     * @param  int $id    元数据ID
     * @param  string $value 新的值
     * @return boolean 是否成功
     */
    public function editMeta($id, $value) {
        $attrs = array(':id' => array($id, PDO::PARAM_INT), ':value' => array($value, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT));
        return $this->db->sqlUpdate($this->tableNameMeta, array($this->fieldsMeta[3] => ':value'), '`' . $this->fieldsMeta[0] . '` = :id', $attrs);
    }

    /**
     * 删除用户
     * <p>不能删除当前使用的用户.</p>
     * @param  int $id 用户ID
     * @return boolean     是否成功
     */
    public function delUser($id) {
        $nowUserID = $this->getLoginStatus();
        if ($nowUserID != $id) {
            $sqlMeta = 'DELETE FROM `' . $this->tableNameMeta . '` WHERE `' . $this->fieldsMeta[1] . '` = :id';
            $attrs = array(':id' => array($id, PDO::PARAM_INT));
            if ($this->db->runSQL($sqlMeta, $attrs, 0) == true) {
                $sqlUser = 'DELETE FROM `' . $this->tableNameUser . '` WHERE `' . $this->fieldsUser[0] . '` = :id';
                return $this->db->runSQL($sqlUser, $attrs, 0);
            }
        }
    }

    /**
     * 删除元数据
     * @param  int $id 元数据ID
     * @return boolean     是否成功
     */
    public function delMeta($id) {
        $sql = 'DELETE FROM `' . $this->tableNameMeta . '` WHERE `' . $this->fieldsMeta[0] . '` = :id';
        $attrs = array(':id' => array($id, PDO::PARAM_INT));
        return $this->db->runSQL($sql, $attrs, 0);
    }

    /**
     * 检测权限具备情况
     * @param  int $userID 用户ID
     * @param  array $powers 权限组
     * @return array|boolean 检测结果以数组呈现，或返回false
     */
    public function checkPower($userID, $powers) {
        $res = $this->getMetaValList($userID, $this->powerMetaName);
        $resPowers;
        foreach ($powers as $v) {
            $resPowers[$v] = false;
        }
        if ($res) {
            foreach ($powers as $v) {
                $resPowers[$v] = in_array($v, $res);
            }
        }
        return $resPowers;
    }

    /**
     * 检查应用权限具备情况
     * @param  int $userID 用户ID
     * @param  array $apps 应用组
     * @return array 检测结果以数组呈现，对应boolean值
     */
    public function checkApp($userID, $apps) {
        $res = $this->getMetaValList($userID, $this->appMetaName);
        $resApps;
        foreach ($apps as $v) {
            $resApps[$v] = false;
        }
        if ($res) {
            foreach ($apps as $v) {
                $resApps[$v] = in_array($v, $res);
            }
        }
        return $resApps;
    }

    /**
     * 获取元数据列
     * <p>重组为数据数组。</p>
     * @param  int $userID 用户ID
     * @param  string $metaName 元数据名称
     * @return array       数据数组
     */
    public function getMetaValList($userID, $metaName) {
        $res = $this->viewMeta($userID, $metaName);
        if ($res) {
            return explode('|', $res[0][$this->fieldsMeta[3]]);
        }
        return null;
    }

    /**
     * 设定元数据值
     * @param  int $userID 用户ID
     * @param  string $metaName 元数据名称
     * @param array $vals 数据数组
     * @return boolean|int       是否成功，如果第一次添加则返回元数据ID
     */
    public function setMetaValList($userID, $metaName, $vals) {
        $val = implode('|', $vals);
        $res = $this->viewMeta($userID, $metaName);
        if ($res) {
            return $this->editMeta($res[0][$this->fieldsMeta[0]], $val);
        } else {
            return $this->addMeta($userID, $metaName, $val);
        }
    }

    /**
     * 获取加密密码
     * @param  string $passwd 密码
     * @return string         加密后的密码
     */
    public function getPasswd($passwd) {
        return sha1(sha1($passwd));
    }

    /**
     * 获取当前登录状态
     * @return int 用户ID
     */
    private function getLoginStatus() {
        return isset($_SESSION[$this->sessionLoginName]) == true ? $_SESSION[$this->sessionLoginName] : false;
    }

    /**
     * 保存当前登录状态
     * @param  int $userID 用户ID
     */
    private function saveLoginStatus($userID) {
        $_SESSION[$this->sessionLoginName] = $userID;
    }

    /**
     * 清除当前登录状态
     */
    private function clearLoginStatus() {
        if (isset($_SESSION[$this->sessionLoginName]) == true) {
            $_SESSION[$this->sessionLoginName] = 0;
            $_SESSION[$this->powerSessionName] = null;
            unset($_SESSION[$this->sessionLoginName], $_SESSION[$this->powerSessionName]);
        }
    }

    /**
     * 更新登录状态
     * @param  int $id     用户ID
     * @param  string $ip     IP地址
     * @param  string $status 登录状态
     * @return boolean 是否成功
     */
    private function updateStatus($id, $ip, $status) {
        $attrs = array(':id' => array($id, PDO::PARAM_INT),
            ':ip' => array($ip, PDO::PARAM_STR));
        $statusStr = $this->status[$status];
        $sets = array($this->fieldsUser[5] => ':ip', $this->fieldsUser[6] => $statusStr);
        $where = '`' . $this->fieldsUser[0] . '` = :id';
        return $this->db->sqlUpdate($this->tableNameUser, $sets, $where, $attrs);
    }

    /**
     * 获取最后一次时间记录
     * @return int unix时间戳
     */
    private function getLastTime() {
        return isset($_SESSION[$this->sessionLimitTime]) == true ? $_SESSION[$this->sessionLimitTime] : 0;
    }

    /**
     * 保存最后一次时间记录
     */
    private function saveLastTime() {
        $_SESSION[$this->sessionLimitTime] = time();
    }

}

?>