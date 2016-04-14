<?php

/**
 * 全局引用
 * @author liuzilu <fotomxq@gmail.com>
 * @version 3
 * @package center
 */
//全局配置文件
require('..' . DIRECTORY_SEPARATOR . 'config.php');
//引用核心文件
require(DIR_LIB . DS . 'core-db.php');
require(DIR_LIB . DS . 'core-file.php');
require(DIR_LIB . DS . 'core-filter.php');
require(DIR_LIB . DS . 'core-header.php');
require(DIR_LIB . DS . 'core-ip.php');
require(DIR_LIB . DS . 'core-log.php');
//获取IP地址
$ip = new CoreIP();
$ipAddress = $ip->getIP();
//构建数据库处理
$db = new CoreDB($dbDSN, $dbUser, $dbPasswd, $dbPersistent, $dbEncoding);
//构建配置处理
require(DIR_LIB . DS . 'sys-config.php');
$config = new SysConfig($db, $tables['config']['name']);
//读取核心配置数据
$webTitle = $config->get('WEB-TITLE');
$userLimitTime = $config->get('USER-LIMIT-TIME');
$loginVcodeBool = $config->get('USER-VCODE-OPEN');
//构建用户处理
require(DIR_LIB . DS . 'sys-user.php');
$user = new SysUser($db, $tables['user']['name'], $tables['user-meta']['name'], USER_SESSION_LOGIN_NAME, $userLimitTime);
//创建过滤器对象
$filter = new CoreFilter();
//缓冲器
require(DIR_LIB . DS . 'core-cache.php');
$cache = new CoreCache(CACHE_ON, CACHE_LIMIT_TIME, CACHE_DIR);
//token计算工具
require(DIR_LIB . DS . 'plug-token.php');
//系统设定的token编码
$pageSetSysTokenVar = 'ftmp-stock-set-sys_20160413';
$pageSetSysTokenLen = 10;
//用户设定的token编码
$pageSetUserTokenVar = 'ftmp-stock-set-user_20160414';
$pageSetUserTokenLen = 10;
?>