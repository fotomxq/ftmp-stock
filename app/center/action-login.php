<?php

/**
 * 登录操作
 * @author liuzilu <fotomxq@gmail.com>
 * @version 2
 * @package center
 */
//引用全局
require('glob.php');
//判断是否已经登录，如果已经登录则直接跳转，否则进行判断
if ($user->logged($ipAddress)) {
    CoreHeader::toURL('center.php');
    die();
}
//开始尝试登录
$post = isset($_POST) ? $_POST : null;
if ($post) {
    if (isset($post['email']) && isset($post['password'])) {
        if ($loginVcodeBool) {
            if (!isset($post['vcode']) || !$_SESSION['user-vcode']) {
                CoreHeader::toURL('index.php?status=vcode-none');
                die();
            }
            if (strcasecmp($post['vcode'], $_SESSION['user-vcode']) !== 0) {
                CoreHeader::toURL('index.php?status=vcode-error');
                die();
            }
        }
        $username = $post['email'];
        $password = sha1($post['password']);
        if ($user->login($ipAddress, $username, $password, true)) {
            //登录成功
            //跳转页面
            CoreHeader::toURL('center.php');
            die();
        }else{
            CoreHeader::toURL('index.php?status=username-or-password-error');
        }
    }
}
?>