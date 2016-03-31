<?php
/**
 * 中心登录页面
 * @author liuzilu <fotomxq@gmail.com>
 * @version 2
 * @package center
 */
//引用全局文件
require('glob.php');
//如果已经登录则进入中心首页
if($user->logged($ipAddress)){
    CoreHeader::toURL('center.php');
}
//定义页面变量
$pageSets['header-title'] = $webTitle . ' - 登录';
$pageSets['header-css'] = array('index.css');
$pageSets['footer-js'] = array('index.js');
$pageSets['glob-js'] = array('sha1.js');
//引用头文件
require(DIR_PAGE . DS . 'page-header.php');
//消息
$status = isset($_GET['status']) ? $_GET['status'] : 'none';
$statusTitle = '登录失败';
$statusType = 'negative';
if(isset($_GET['status'])){
    $statusMessage = '';
    switch($_GET['status']){
        case 'logout':
            $statusType = 'success';
            $statusTitle = '成功';
            $statusMessage = '退出成功！';
            break;
        case 'vcode-none':
            $statusMessage = '请输入验证码。';
            break;
        case 'vcode-error':
            $statusMessage = '验证码错误，请重新输入。';
            break;
        case 'username-or-password-error':
            $statusMessage = '用户名或密码错误，请重新输入。';
            break;
        case 'login-no':
            $statusType = 'info';
            $statusTitle = '成功';
            $statusMessage = '请先登录。';
            break;
        default:
            $statusMessage = '';
    }
?>
<div class="ui <?php echo $statusType; ?> message">
    <div class="header"><?php echo $statusTitle; ?></div>
    <p><?php echo $statusMessage; ?></p>
</div>
<?php
}
?>

<div class="ui middle aligned center aligned grid">
    <div class="column">
        <h2 class="ui teal image header">
            <img src="../assets/imgs/logo.png" class="image">
            <div class="content">
                登录你的用户
            </div>
        </h2>
        <form class="ui large form" action="action-login.php" method="post">
            <div class="ui stacked segment">
                <div class="field">
                    <div class="ui left icon input">
                        <i class="user icon"></i>
                        <input type="text" name="email" placeholder="用户名（user@user.com）">
                    </div>
                </div>
                <div class="field">
                    <div class="ui left icon input">
                        <i class="lock icon"></i>
                        <input type="password" name="password" placeholder="密码">
                    </div>
                </div>
                <?php if($loginVcodeBool === '1') { ?>
                <div class="field">
                    <div class="two fields">
                        <div class="field">
                            <div class="ui left icon input">
                                <i class="spy icon"></i>
                                <input type="text" name="vcode" placeholder="验证码">
                            </div>
                        </div>
                        <div class="field">
                            <img src="vcode.php">
                        </div>
                    </div>
                </div>
                <?php } ?>
                <div class="ui fluid large teal submit button">登录</div>
            </div>

            <div class="ui error message"></div>

        </form>

        <div class="ui message">
            注册新的用户？<a href="#">抱歉，系统已关闭开放注册，请联系管理员。</a>
        </div>
    </div>
</div>
<?php
require(DIR_PAGE . DS . 'page-footer.php');
?>