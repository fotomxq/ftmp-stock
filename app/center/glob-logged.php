<?php

/**
 * 已登录用户引用
 * @author liuzilu <fotomxq@gmail.com>
 * @version 3
 * @package center
 */
//引用全局
require('../center/glob.php');
//如果没有登录则跳转
$userID = $user->logged($ipAddress);
if ($userID < 1) {
    CoreHeader::toURL('../center/index.php?status=login-no');
}
//获取用户权限组
$userPowerBool = $user->viewNowUserPowers($userID, $appList);
//获取用户基本数据
$userData = $user->viewUser($userID);

//引用权限检查插件
require(DIR_PAGE . DS . 'plug-check-user-power.php');
?>