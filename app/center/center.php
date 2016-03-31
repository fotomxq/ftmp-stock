<?php
/**
 * 中心首页
 * @author liuzilu <fotomxq@gmail.com>
 * @version 4
 * @package center
 */
//引用全局
require('glob-logged.php');
//定义页面变量
$pageSets['header-title'] = $webTitle . ' - 中心';
$pageSets['header-css'] = array('center.css');
$pageSets['footer-js'] = array('center.js');
//引用头文件
require(DIR_PAGE . DS . 'page-header.php');
//引用菜单文件
require('page-menu.php');
//过滤出的应用信息组合
$centerAppList = null;

foreach ($appList as $v) {
    if ($userPowerBool[$v['name']]) {
        $centerAppList[] = $v;
    }
}
?>
<div class="ui main container">
    <h1 class="ui header">应用列表</h1>
    <div class="ui three column grid">
    <?php for($i=0;$i<count($centerAppList);$i++){ ?>
        <div class="column">
            <div class="ui" name="app-list" data-title="<?php echo $centerAppList[$i]['title']; ?>" data-content="<?php echo $centerAppList[$i]['des']; ?>">
                <a href="../<?php echo $centerAppList[$i]['name']; ?>/index.php" target="_self">
                    <img src="../<?php echo $centerAppList[$i]['name']; ?>/assets/imgs/logo.png" class="ui image">
                </a>
            </div>
        </div>
    <?php } ?>
    </div>
</div>

<?php
require(DIR_PAGE . DS . 'page-footer.php');
?>