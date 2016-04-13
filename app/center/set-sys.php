<?php
/**
 * 系统设定页面
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package center
 */
//引用全局
require('glob-logged.php');
//检查权限
PlugCheckUserPower($userPowerBool, 'admin');
//定义页面变量
$pageSets['header-title'] = $webTitle . ' - 系统设定';
$pageSets['header-css'] = array('center.css');
$pageSets['footer-js'] = array('set-sys.js');
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
//获取用户列表
$userList = $user->viewUserList('1', null, 1, 99999);
//补充所有用户权限元数据
foreach ($userList as $k => $v) {
    $userList[$k]['powers'] = $user->viewUserPowers($v['id'], $appList);
}
//用户权限组键值序列
$userPowerKeyList = null;
foreach ($userList[0]['powers'] as $k => $v) {
    $userPowerKeyList[] = $k;
}
//权限列表
$powerList = array(
    'VISITOR' => '访客',
    'ADMIN' => '管理员',
    'NORMAL' => '普通用户'
);
foreach ($appList as $v) {
    $powerList[$v['name']] = $v['title'];
}
?>
<div class="ui main container">
    <?php echo PlugMessage($status, $messageSet); ?>
    <div class="ui huge inverted grey dividing header"><i class="icon options"></i> 系统设置</div>
    <form class="ui form" method="post" action="action-set-sys.php?action=set-sys-basic">
        <div class="field">
            <label>用户登录限时 (s)</label>
            <input type="text" name="sys-user-limit-time" placeholder="1800 (s)" value="<?php echo $userLimitTime; ?>">
        </div>
        <div class="ui segment">
            <div class="field">
                <div class="ui toggle checkbox">
                    <input type="checkbox" name="sys-login-vcode-bool" value="1" tabindex="0" class="hidden"<?php if($loginVcodeBool){ echo ' checked="checked"'; } ?>>
                    <label>登录验证码开关，开启后登录需要填写验证码。</label>
                </div>
            </div>
        </div>
        <button class="ui button" type="submit"><i class="icon save"></i> 保存设定</button>
    </form>
    <div class="ui huge inverted grey dividing header"><i class="icon users"></i> 用户列表</div>
    <table class="ui inverted compact celled definition table">
        <thead>
            <tr>
                <th>ID</th>
                <th>昵称</th>
                <th>用户名</th>
                <th>登录时间</th>
                <th>登录IP</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody data-power-list="<?php echo implode('|', $userPowerKeyList); ?>" id="user-table-tbody">
            <?php
            foreach($userList as $v){
                //抽取出该用户权限
                $vPowers = null;
                foreach($v['powers'] as $powerK=>$powerV){
                    if($powerV){
                        $vPowers[] = $powerK;
                    }
                }
                ?>
            <tr>
                <td><?php echo $v['id']; ?></td>
                <td><?php echo $v['user_nicename']; ?></td>
                <td><?php echo $v['user_login']; ?></td>
                <td><?php echo $v['user_date']; ?></td>
                <td><?php echo $v['user_ip']; ?></td>
                <td data-id="<?php echo $v['id']; ?>" data-powers="<?php echo implode('|', $vPowers); ?>">
                    <div class="ui olive icon button" name="show-edit-modal">
                        <i class="edit icon"></i>
                    </div>
                    <div class="ui negative icon button" name="show-del-modal">
                        <i class="remove add user icon"></i>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </tbody>
        <tfoot class="full-width">
            <tr>
                <th></th>
                <th colspan="5">
                    <div class="ui right floated small primary labeled icon button" id="show-add-modal">
                        <i class="add user icon"></i> 添加用户
                    </div>
                </th>
            </tr>
            </tfoot>
    </table>
    <div class="ui huge inverted grey dividing header"><i class="icon upload"></i> 提供数据</div>
    <div class="ui message">
        <div class="header">请注意</div>
        <p>系统一般会自动采集数据，但存在不完整性，所以可以手动导入数据补充。相同数据将混合呈现。</p>
    </div>
    <div class="ui inverted form" token="<?php echo PlugToken($pageSetSysTokenVar, date('Ymd'), $pageSetSysTokenLen); ?>">
        <div class="fields">
            <div class="field">
                <label>来源</label>
                <select class="ui dropdown" id="data-upload-type">
                    <option value="1">东方财富客户端</option>
                    <option value="2">Choice</option>
                </select>
            </div>
            <div class="field">
                <label>选择文件</label>
                <div id="data-uploadfile"></div>
            </div>
        </div>
        <a class="ui button" href="#data-upload-ok"><i class="icon upload"></i> 上传数据</a>
    </div>
    <div class="ui huge inverted grey dividing header"><i class="icon cloud"></i> 自动采集系统</div>
    <div class="ui message">
        <div class="header">工作情况</div>
        <p>采集系统正在运行中...</p>
    </div>
    <a class="ui button" href="#data-auto-run-or-stop"><i class="icon cloud"></i> 开始采集数据</a>
</div>

<div class="ui basic modal" id="del-modal">
    <i class="close icon"></i>
    <div class="header">
        删除用户
    </div>
    <div class="image content">
        <div class="image">
            <i class="remove user icon"></i>
        </div>
        <div class="description">
            <p>确定要删除该用户么？删除后将无法恢复！</p>
        </div>
    </div>
    <div class="actions">
        <div class="two fluid ui inverted buttons">
            <div class="ui red basic inverted deny button">
                <i class="remove icon"></i>
                取消
            </div>
            <div class="ui green basic inverted deny button" id="ok-del-user">
                <i class="checkmark icon"></i>
                删除
            </div>
        </div>
    </div>
</div>

<div class="ui modal" id="edit-modal">
    <i class="close icon"></i>
    <div class="header">
        修改用户
    </div>
    <div class="image content">
        <form class="ui form" method="post" action="action-set-sys.php?action=user-edit" id="form-user-edit">
            <div class="field">
                <label>新的昵称</label>
                <input type="text" name="edit-nicename" placeholder="新的昵称" value="<?php echo $userData['user_nicename']; ?>">
            </div>
            <div class="field">
                <label>新的密码</label>
                <input type="password" name="edit-passwd" placeholder="新的密码">
                <input type="text" name="edit-user-id">
            </div>
            <div class="field">
                <label>权限</label>
                <div class="inline field">
                    <?php foreach($powerList as $k=>$v){ ?>
                    <div class="ui checkbox">
                      <input type="checkbox" name="edit-powers[]" tabindex="0" class="hidden" value="<?php echo $k; ?>" data-id="edit-power-<?php echo strtolower($k); ?>">
                      <label><?php echo $v; ?></label>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny button">
            取消修改
        </div>
        <div class="ui positive right labeled icon button" id="ok-edit-user">
            保存修改
            <i class="checkmark icon"></i>
        </div>
    </div>
</div>

<div class="ui modal" id="add-modal">
    <i class="close icon"></i>
    <div class="header">
        添加新的用户
    </div>
    <div class="image content">
        <form class="ui form" method="post" action="action-set-sys.php?action=user-add" id="form-user-add">
            <div class="field">
                <label>昵称</label>
                <input type="text" name="add-nicename" placeholder="昵称">
            </div>
            <div class="field">
                <label>登录用户名</label>
                <input type="text" name="add-login" placeholder="用户名 (user@user.com)">
            </div>
            <div class="field">
                <label>登录密码</label>
                <input type="password" name="add-passwd" placeholder="密码">
            </div>
            <div class="field">
                <label>权限</label>
                <div class="inline field">
                    <?php foreach($powerList as $k=>$v){ ?>
                    <div class="ui checkbox">
                      <input type="checkbox" name="add-powers[]" tabindex="0" class="hidden" value="<?php echo $k; ?>">
                      <label><?php echo $v; ?></label>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny button">
            取消
        </div>
        <div class="ui positive right labeled icon button" id="ok-add-user">
            添加用户
            <i class="checkmark icon"></i>
        </div>
    </div>
</div>
<?php
require(DIR_PAGE . DS . 'page-footer.php');
?>