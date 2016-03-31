<?php

/**
 * 全局配置文件
 * @author liuzilu <fotomxq@gmail.com>
 * @version 4
 * @package glob
 */

//////////////////
//路径定义
//////////////////
//路径分隔符
define('DS', DIRECTORY_SEPARATOR);
//绝对路径
define('DIR_ROOT', dirname(__FILE__) . DS . '..' . DS);
//用户数据
define('DIR_DATA', DIR_ROOT . 'content');
//库路径
define('DIR_LIB', DIR_ROOT . 'includes');
//APP路径
define('DIR_APP', DIR_ROOT . 'app');
//page页面引用路径
define('DIR_PAGE', DIR_LIB . DS . 'page');

//////////////////
//数据库定义
//////////////////
//PDO-DSN eg: mysql:host=localhost;dbname=databasename;charset=utf8
$dbDSN = 'mysql:host=localhost;dbname=ftmp;charset=utf8';
//数据库用户名
$dbUser = 'admin';
//数据库密码
$dbPasswd = 'adminadmin';
//是否持久化连接
$dbPersistent = true;
//连接编码
$dbEncoding = 'UTF8';

//////////////////
//表信息注册
//////////////////
//注，其他应用表数据在对应应用的glob.php内进行配置，同样会使用该变量。
$sysPrefix = 'sys_';
$tables = array(
    'user' => array('name' => $sysPrefix . 'user', 'fields' => array('id', 'user_nicename', 'user_login', 'user_passwd', 'user_date', 'user_ip', 'user_status')),
    'user-meta' => array('name' => $sysPrefix . 'usermeta', 'fields' => array('id', 'user_id', 'meta_name', 'meta_value')),
    'config' => array('name' => $sysPrefix . 'config', 'fields' => array('id', 'config_name', 'config_value'))
);

//////////////////
//APP
//////////////////
//APP注册表
$appList = array(
    array('name' => 'stock', 'title' => '股票', 'des' => '股票交易记录、计划、统计。')
);

//////////////////
//上传文件全局设定
//////////////////
//允许的文件类型
define('UPLOAD_TYPE', 'jpg,png,gif,jpeg,wmp,zip,rar,7z,pdf,doc,docx,ppt,cvs,xls,txt,wma,wmv,mp3,mp4,avi,mpeg');
//拒绝的文件类型
define('UPLOAD_BAN_TYPE', 'exe,bat,sh,php,html,htm,msi', 'py');
//允许的图片文件类型
define('UPLOAD_IMG_TYPE', 'jpg,png,gif');
//支持的在线文档类型
define('UPLOAD_WORD _TYPE', 'pdf');
//是否开启文件上传白名单
define('UPLOAD_TYPE_ON', true);
//是否开启文件上传黑名单
define('UPLOAD_BAN_TYPE_ON', true);
//最大文件大小 (KB)
define('UPLOAD_SIZE_MAX', 51200);
//如果图片超出尺寸是否自动压缩图片
define('UPLOAD_IMG_SIZE_P_ON', true);
//图片最大尺寸
define('UPLOAD_IMG_SIZE_W', 3000);
define('UPLOAD_IMG_SIZE_H', 3000);
//是否直接跳转到文件下载，否则通过脚本下载
define('UPLOAD_DOWN_PHP', true);

//////////////////
//日志系统
//////////////////
//日志开关
define('LOG_ON', true);
//////////////////
//日志目录
define('LOG_DIR', DIR_DATA . DS . 'logs');
//////////////////
//日志记录形式
//0 - 发送到PHP日志记录系统 ; 1 - 年月.log ; 2 - 年月/日.log ; 3 - 年月/日-时.log ; 4 - 年/月/日-时.log
define('LOG_TYPE', 0);

//////////////////
//缓冲器
//////////////////
//缓冲器开关
define('CACHE_ON', true);
//失效时间长度 ( 秒 )
define('CACHE_LIMIT_TIME', 1296000);
//缓冲目录
define('CACHE_DIR', DIR_DATA . DS . 'cache');

//////////////////
//用户系统
//////////////////
//全局登录Session设定
define('USER_SESSION_LOGIN_NAME', 'ftmp-stock-user');

//////////////////
//其他设定
//////////////////
//URL
define('WEB_URL', 'http://localhost/ftmp-stock');
//Debug模式开关
define('DEBUG_ON', true);
//全局网站开关（关闭后后台也将无法登录）
define('WEB_ON', true);
//默认访客用户 (该用户不能被删除，且被用于上传和其他系统创建任务)
define('VISITOR_USER', 1);
//默认访客用户组，作用同上 (如果需要自定义，请勿赋予任何权限)
define('VISITOR_USER_GROUP', 1);
//定义时区
date_default_timezone_set('PRC');
//启动session
@session_start();
//错误页面
define('ERROR_PAGE', 'error.php');
//百度开发者KEY
define('BAIDU_KEY', 'GnfQtIB7WZTAXSMbm6I6HbhL');
?>