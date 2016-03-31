<?php

/**
 * 菜单引用页面
 * @author liuzilu <fotomxq@gmail.com>
 * @version 2
 * @package center
 */
//确保不是直接访问
if(!isset($pageSets)){
    die();
}
?>
<div class="ui fixed inverted menu">
    <div class="ui container">
        <div href="#" class="header item">
            <img class="logo" src="../assets/imgs/logo-white.png">
        </div>
        <a href="center.php" class="item"><i class="grid layout icon"></i>首页</a>
        <a href="set-user.php" class="item"><i class="user icon"></i>用户设置</a>
        <?php if($userPowerBool['admin']){ ?><a href="set-sys.php" class="item"><i class="options icon"></i>系统设置</a><?php } ?>
        <a href="action-logout.php" class="item"><i class="sign out icon"></i>退出</a>
    </div>
</div>