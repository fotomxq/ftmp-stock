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
switch ($_GET['action']) {
    case 'user-passwd':
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
        break;
    case 'data-uploadfile':
        //上传数据类文件
        //确保token相同
        $token = PlugToken($pageSetUserTokenVar, date('Ymd'), $pageSetUserTokenLen);
        if (isset($_GET['token']) && $token === $_GET['token']) {
            die('1');
        }
        die('提交超时，请刷新页面。');
        break;
}
//跳转页面
CoreHeader::toURL('set-user.php?status=' . $status);
?>