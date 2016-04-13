<?php

/**
 * 系统设置页面处理
 * @author liuzilu <fotomxq@gmail.com>
 * @version 2
 * @package center
 */
//引用全局
require('glob-logged.php');
//检查权限
PlugCheckUserPower($userPowerBool, 'admin');
//输出消息
$status = '';
switch ($_GET['action']) {
    case 'set-sys-basic':
        $postUserLimitTime = isset($_POST['sys-user-limit-time']) ? (int) $_POST['sys-user-limit-time'] : $userLimitTime;
        $postVcodeBool = isset($_POST['sys-login-vcode-bool']) ? '1' : '0';
        $status = 'set-failure';
        if ($config->save('USER-LIMIT-TIME', $postUserLimitTime) && $config->save('USER-VCODE-OPEN', $postVcodeBool)) {
            $status = 'set-success';
        }
        break;
    case 'user-add':
        $postNicename = isset($_POST['add-nicename']) ? $_POST['add-nicename'] : null;
        $postLogin = isset($_POST['add-login']) ? $_POST['add-login'] : null;
        $postPasswd = isset($_POST['add-passwd']) ? $_POST['add-passwd'] : null;
        $postPowers = isset($_POST['add-powers']) ? $_POST['add-powers'] : null;
        $newUserID = 0;
        $status = 'add-failure';
        if ($postNicename && $postLogin && $postPasswd && $postPowers && is_array($postPowers)) {
            $newUserID = $user->addUser($postNicename, $postLogin, $postPasswd);
            if ($newUserID > 0) {
                if ($user->setMetaValList($newUserID, $user->powerMetaName, $postPowers)) {
                    $status = 'add-success';
                }
            }
        }
        break;
    case 'user-edit':
        $postEditUserID = isset($_POST['edit-user-id']) ? $_POST['edit-user-id'] : 0;
        $postNicename = isset($_POST['edit-nicename']) ? $_POST['edit-nicename'] : null;
        $postPasswd = isset($_POST['edit-passwd']) ? $_POST['edit-passwd'] : null;
        $postPowers = isset($_POST['edit-powers']) ? $_POST['edit-powers'] : null;
        $status = 'set-failure';
        if ($postEditUserID > 0 && ($postNicename || $postPasswd)) {
            if ($user->editUser($postEditUserID, $postNicename, $postPasswd)) {
                $status = 'set-success';
            }
            if ($postPowers && is_array($postPowers)) {
                $status = 'set-failure';
                if ($user->setMetaValList($postEditUserID, $user->powerMetaName, $postPowers)) {
                    $status = 'set-success';
                }
            }
        }
        break;
    case 'user-del':
        $postDelUserID = isset($_GET['id']) ? $_GET['id'] : 0;
        $status = 'del-failure';
        if ($postDelUserID > 0) {
            if ($user->delUser($postDelUserID)) {
                $status = 'del-success';
            }
        }
        break;
    case 'data-uploadfile':
        //上传数据类文件
        //确保token相同
        $token = PlugToken($pageSetSysTokenVar, date('Ymd'), $pageSetSysTokenLen);
        if (isset($_GET['token']) && $token === $_GET['token']) {
            die('1');
        }
        die('提交超时，请刷新页面。');
        break;
}
//跳转页面
CoreHeader::toURL('set-sys.php?status=' . $status);
?>