<?php

/**
 * 检查用户权限插件
 * 检查当前用户的权限，如果不符合要求则禁止访问该页面，并自动跳转回首页
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package page-plug
 */

/**
 * 检查用户权限插件
 * @param array $userPowers 用户具备权限列
 * @param string $needPower 访问需要的权限
 */
function PlugCheckUserPower($userPowerBool, $needPower) {
    if (!in_array($needPower, $userPowerBool)) {
        CoreHeader::toURL('../center/center.php');
        die();
    }
}

?>