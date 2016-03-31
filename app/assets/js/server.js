/**
 * 服务器对接处理器
 * @author liuzilu <fotomxq@gmail.com>
 * @version 2
 * @package js-server
 */
//服务器对接处理器对象
var server = new Object;
//联合锁定
server.lock = false;
//是否开启联合锁定
server.lockOn = true;
//默认获取数据类型
server.dataType = 'json';
//提交post
server.post = function (url, data, func) {
    if (server.lock && server.lockOn) {
        return false;
    }
    server.lock = true;
    $.ajax({
        url: url,
        data: data,
        success: function (result) {
            func(result);
        },
        type: 'post',
        dataType: server.dataType,
        complete: function (xhr, status) {
            server.lock = false;
        },
        error: function (xhr, status, err) {
            server.lock = false;
        }
    });
}
//提交get
server.get = function (url, func) {
    if (server.lock && server.lockOn) {
        return false;
    }
    server.lock = true;
    $.ajax({
        url: url,
        success: function (result) {
            server.lock = false;
            func(result);
        },
        type: 'get',
        dataType: server.dataType,
        complete: function (xhr, status) {
            server.lock = false;
        },
        error: function (xhr, status, err) {
            server.lock = false;
        }
    });
}