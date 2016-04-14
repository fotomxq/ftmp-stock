/**
 * 用户设定
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package center
 */
$(function(){
    //数据文件上传框架初始化
    $('#data-uploadfile').uploadify({
        height: 30,
        width: 120,
        swf: '../assets/swf/uploadify.swf',
        uploader: '../../center/action-set-user.php?action=data-uploadfile',
        buttonText: '选择本地文件',
        auto: false,
        uploadLimit: 1,
        itemTemplate: '<div id="${fileID}" class="uploadify-queue-item">\<div class="cancel">\</div>\<span class="fileName">${fileName} (${fileSize})</span><span class="data"></span>\</div>'
    });
    //单击数据文件上传按钮
    $('a[href="#data-upload-ok"]').click(function () {
        var type = $('#data-upload-type').val();
        var token = $('.container').attr('token');
        var url = '../../center/action-set-user.php?action=data-uploadfile&type=' + type + '&token=' + token;
        $('#data-uploadfile').uploadify('settings', 'uploader', url);
        $('#data-uploadfile').uploadify('upload', '*');
    });
});
