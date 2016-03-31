<?php

/**
 * 退出登录
 * @author liuzilu <fotomxq@gmail.com>
 * @version 2
 * @package center
 */
//引用全局
require('glob.php');
//判断是否已经登录，如果已登录则开始退出
if ($user->logged($ipAddress)) {
    $user->logout($ipAddress);
}
//跳转页面
CoreHeader::toURL('../center/index.php?status=logout');
?>