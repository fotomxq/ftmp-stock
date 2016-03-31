/**
 * 页面消息组件
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package js-message
 */
//消息框架
var message = new Object;
//消息框架ID
message.id = 'message';
//消息框架原始内容
message.firstHtml = '';
//消息列表，array('msg'=>'消息内容','type'=>'样式类型')
message.list = new Array();
//等待几毫秒消退
message.waitHide = 5000;
//计时器
message.time = '';
//初始化
message.start = function () {
    var html = '<div class="ui info message" id="' + message.id + '"><i class="close icon"></i><div class="header"></div><ul class="list"></ul></div>';
    if ($('div[class="ui main container"]')) {
        $('div[class="ui main container"]').prepend(html);
    } else {
        $('div:eq(0)').before(html);
    }
    message.id = '#' + message.id;
    $(message.id).hide();
    message.firstHtml = $(message.id).html();
    $(message.id).children('i').click(function () {
        message.clear();
    });
}
//消退处理
message.clear = function () {
    message.list = new Array();
    $(message.id).fadeOut();
    clearTimeout(message.time);
}
//发送新的消息
message.send = function (type, msg) {
    message.list.push({'type': type, 'msg': msg});
    message.ref();
    clearTimeout(message.time);
    message.time = setInterval(function () {
        message.clear();
    }, message.waitHide);
}
//刷新消息
message.ref = function () {
    $(message.id + '').children('ul[class="list"]').html('');
    for (var i = 0; i < message.list.length; i++) {
        $(message.id).children('ul[class="list"]').append('<li>' + message.list[i]['msg'] + '</li>');
        $(message.id).attr('class', 'ui message ' + message.list[i]['type']);
    }
    $(message.id).fadeIn();
}