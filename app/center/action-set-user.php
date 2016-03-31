<?php

/**
 * 修改用户数据提交处理页面
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package center
 */
//引用全局
require('glob-logged.php');
//输出消息
$status = 'set-failure';
//修改昵称和密码
$postNewNicename = isset($_POST['new-nicename']) ? $_POST['new-nicename'] : null;
$postNewPassword = isset($_POST['new-password']) ? $_POST['new-password'] : null;
$checkBool = true;
if ($postNewNicename) {
    if (strlen($postNewNicename) < 4) {
        $checkBool = false;
    }
}
if ($postNewPassword) {
    if (strlen($postNewPassword) < 6) {
        $checkBool = false;
    }
}
if ($checkBool) {
    if ($user->editUser($userID, $postNewNicename, $postNewPassword)) {
        $status = 'set-success';
    }
}
//跳转页面
CoreHeader::toURL('set-user.php?status=' . $status);
?>