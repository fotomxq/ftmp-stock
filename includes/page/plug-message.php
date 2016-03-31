<?php

/**
 * 输出消息HTML
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package page-plug
 */

/**
 * 输出消息HTML
 * 如果status没有定义或为空，则返回空
 * @param string $status 状态
 * @param array $showList 模式数据列
 * @return string HTML
 */
function PlugMessage($status, $showList) {
    if (isset($status) && isset($showList[$status])) {
        $showTitle = isset($showList[$status]['title']) ? $showList[$status]['title'] : '失败';
        $showText = isset($showList[$status]['text']) ? $showList[$status]['text'] : '失败！';
        $showType = isset($showList[$status]['type']) ? $showList[$status]['type'] : 'info';
        $html = '<div class="ui ' . $showType . ' message"><div class="header">' . $showTitle . '</div><p>' . $showText . '</p></div>';
        return $html;
    } else {
        return '';
    }
}

?>