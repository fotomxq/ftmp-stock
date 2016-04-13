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
$pageSets['header-title'] = $webTitle . ' - 持仓';
$pageSets['header-css'] = array('center.css');
$pageSets['footer-js'] = array('center.js');
//引用头文件
require(DIR_PAGE . DS . 'page-header.php');
//引用菜单文件
require('page-menu.php');
?>
<div class="ui main container">
    <h2 class="ui inverted grey header"><i class="icon inbox"></i> 待办事项</h2>
    <div class="ui clearing divider"></div>
    <table class="ui celled inverted selectable table">
        <thead>
            <tr>
                <th>市场</th>
                <th>代码</th>
                <th>名称</th>
                <th>操作方向</th>
                <th>说明</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>SS</td>
                <td>300024</td>
                <td>生意宝</td>
                <td>平仓</td>
                <td>（自动）触发23.33元止损线</td>
                <td>
                    <div class="ui buttons">
                        <a class="circular ui icon inverted button"><i class="icon checkmark"></i></a>
                        <div class="or" data-text="或"></div>
                        <a class="circular ui icon inverted button"><i class="icon remove"></i></a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>SZ</td>
                <td>600023</td>
                <td>山东黄金</td>
                <td>建仓</td>
                <td>符合30K底部结构</td>
                <td>
                    <div class="ui buttons">
                        <a class="circular ui icon inverted button"><i class="icon checkmark"></i></a>
                        <div class="or" data-text="或"></div>
                        <a class="circular ui icon inverted button"><i class="icon remove"></i></a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="ui inverted mini input">
                        <select class="ui compact selection dropdown">
                            <option value="SS">SS</option>
                            <option value="SZ">SZ</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="ui inverted mini input">
                        <input type="text" placeholder="股票代码">
                    </div>
                </td>
                <td> - </td>
                <td>
                    <div class="ui inverted mini input">
                        <select class="ui compact selection dropdown">
                            <option value="1">建仓</option>
                            <option value="2">加仓</option>
                            <option value="3">观望</option>
                            <option value="4">减仓</option>
                            <option value="5">平仓</option>
                        </select>
                    </div>
                </td>
                <td>
                    <div class="ui inverted mini input">
                        <input type="text" placeholder="说明">
                    </div>
                </td>
                <td>
                    <a href="#new-plan" class="ui mini floated button">
                        <i class="icons">
                            <i class="inbox icon"></i>
                            <i class="corner add icon"></i>
                        </i>
                        新增计划
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
    <h2 class="ui inverted grey header"><i class="icon pie chart"></i> 持仓</h2>
    <div class="ui clearing divider"></div>
    <table class="ui celled inverted selectable table">
        <thead>
            <tr>
                <th>市场</th>
                <th>代码</th>
                <th>名称</th>
                <th>现价</th>
                <th>股份</th>
                <th>市值</th>
                <th>盈亏</th>
                <th>目标价</th>
                <th>止损位</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>SZ</td>
                <td>300024</td>
                <td>生意宝</td>
                <td>24.55 (+3.2%)</td>
                <td>3,000</td>
                <td>140,389.00</td>
                <td>+7203 (+6.2%)</td>
                <td>57.23</td>
                <td>23.33</td>
                <td>
                    <a class="circular ui icon inverted button"><i class="icon inbox"></i></a>
                    <div class="ui buttons">
                        <a class="circular ui icon inverted button">卖</a>
                        <div class="or" data-text="或"></div>
                        <a class="circular ui icon inverted button">买</a>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <h2 class="ui inverted grey header"><i class="icon line chart"></i> 收益表现</h2>
    <div class="ui clearing divider"></div>
    <div id="history-chart" style="width: 600px;height:400px;"></div>
    <h2 class="ui inverted grey header"><i class="icon rocket"></i> 历史持仓</h2>
    <div class="ui clearing divider"></div>
    <table class="ui celled inverted selectable table">
        <thead>
            <tr>
                <th>市场</th>
                <th>代码</th>
                <th>名称</th>
                <th>介入股份</th>
                <th>介入市值</th>
                <th>收益</th>
                <th>收益率</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>SZ</td>
                <td>300024</td>
                <td>生意宝</td>
                <td>3,000</td>
                <td>140,389.00</td>
                <td>10,600</td>
                <td>12.3%</td>
            </tr>
        </tbody>
    </table>
    <a href="#history-page-prev" class="ui left floated inverted button"><i class="icon long arrow left"></i> 上一页</a>
    <a href="#history-page-next" class="ui right floated inverted button">下一页 <i class="icon long arrow right"></i></a>
</div>

<?php
require(DIR_PAGE . DS . 'page-footer.php');
?>