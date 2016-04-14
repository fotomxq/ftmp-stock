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
$pageSets['footer-js'] = array('set-user.js');
$pageSets['glob-js'] = array('jquery.uploadify.js');
$pageSets['glob-css'] = array('uploadify.css');
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
<div class="ui main container" token="<?php echo PlugToken($pageSetUserTokenVar, date('Ymd'), $pageSetUserTokenLen); ?>">
    <div class="ui huge inverted grey dividing header"><i class="icon user"></i> 用户设置</div>
    <?php echo PlugMessage($status, $messageSet); ?>
    <form class="ui inverted form" method="post" action="action-set-user.php?action=user-passwd">
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
    <div class="ui huge inverted grey dividing header"><i class="icon file excel outline"></i> 导入证券交割单</div>
    <div class="ui inverted form">
        <div class="fields">
            <div class="field">
                <label>来源</label>
                <select class="ui dropdown" id="data-upload-type">
                    <option value="1">自编辑格式</option>
                    <option value="2">同信证券</option>
                    <option value="3">招商证券</option>
                    <option value="4">长城证券</option>
                    <option value="5">银河证券</option>
                </select>
            </div>
            <div class="field">
                <label>选择文件</label>
                <div id="data-uploadfile"></div>
            </div>
        </div>
        <a class="ui button" href="#data-upload-ok"><i class="icon upload"></i> 上传数据</a>
    </div>
</div>
<?php
require(DIR_PAGE . DS . 'page-footer.php');
?>