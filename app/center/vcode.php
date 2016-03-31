<?php

/**
 * 验证码输出
 * @author liuzilu <fotomxq@gmail.com>
 * @version 2
 * @package center
 */
//全局配置文件
require('..' . DIRECTORY_SEPARATOR . 'config.php');
//引用验证码处理
require(DIR_LIB . DS . 'plug-vcode.php');
//生成验证码
$vcodeFontSrc = DIR_APP . DS . 'assets' . DS . 'fonts' . DS . 'vcode.ttf';
PlugVCode(4, 26, 100, 40, 'user-vcode', $vcodeFontSrc);
?>