<?php
/**
 * 菜单引用页面
 * @author liuzilu <fotomxq@gmail.com>
 * @version 2
 * @package center
 */
//确保不是直接访问
if (!isset($pageSets)) {
    die();
}
?>
<div class="ui fixed inverted menu">
    <div class="ui container">
        <div href="#" class="header item">
            <img class="logo" src="../assets/imgs/logo-white.png">
        </div>
        <a href="../center/center.php" class="item"><i class="inbox icon"></i>持仓</a>
        <a href="../center/count.php" class="item"><i class="bar chart icon"></i>统计</a>
        <a href="../center/data.php" class="item"><i class="database icon"></i>数据</a>
        <div class="ui dropdown item">
            <i class="lab icon"></i>工具箱 <i class="dropdown icon"></i>
            <div class="menu">
                <div class="item">持仓统计</div>
                <div class="item">Choice 2</div>
                <div class="item">Choice 3</div>
            </div>
        </div>
        <div class="right menu">
            <div class="ui dropdown item">
                <i class="settings icon"></i> 设定 <i class="dropdown icon"></i>
                <div class="menu">
                    <a href="../center/set-user.php" class="item"><i class="user icon"></i>用户</a>
                    <?php if ($userPowerBool['admin']) { ?><a href="../center/set-sys.php" class="item"><i class="options icon"></i>系统</a><?php } ?>
                </div>
            </div>
            <a href="../center/action-logout.php" class="item"><i class="sign out icon"></i>退出</a>
        </div>
    </div>
</div>
