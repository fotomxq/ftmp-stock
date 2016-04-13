<?php
/**
 * 用户个人设定页面
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package center
 */
//引用全局
require('glob-logged.php');
//定义页面变量
$pageSets['header-title'] = $webTitle . ' - 用户设置';
$pageSets['header-css'] = array('center.css');
//引用头文件
require(DIR_PAGE . DS . 'page-header.php');
//引用菜单文件
require('page-menu.php');
//引用消息模块
require(DIR_PAGE . DS . 'page-message-set-yn.php');
require(DIR_PAGE . DS . 'plug-message.php');
//消息接收
$status = isset($_GET['status']) ? $_GET['status'] : null;
?>
<div class="ui main container">
    <div class="ui huge inverted grey header"><i class="icon user"></i> 用户设置</div>
    <?php echo PlugMessage($status, $messageSet); ?>
    <form class="ui inverted form" method="post" action="action-set-user.php">
        <div class="field">
            <label>昵称</label>
            <input type="text" name="new-nicename" placeholder="新的昵称" value="<?php echo $userData['user_nicename']; ?>">
        </div>
        <div class="field">
            <label>新的密码</label>
            <input type="password" name="new-password" placeholder="新的密码">
        </div>
        <button class="ui button" type="submit">保存</button>
    </form>
</div>
<?php
require(DIR_PAGE . DS . 'page-footer.php');
?>