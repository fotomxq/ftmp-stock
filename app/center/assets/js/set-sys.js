/**
 * 系统设定页面特效处理
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package center
 */
//当前操作用户ID
var nowOperateUserID = 0;
//权限组键位序列数组
var powerKeyList = new Array();
//初始化
$(function () {
    //激活复选框效果
    $('.ui.checkbox').checkbox();
    //隐藏组件
    $('input[name="edit-user-id"]').hide();
    //获取权限组键位序列
    var powerKeyStr = $('#user-table-tbody').attr('data-power-list');
    powerKeyList = powerKeyStr.split('|');
    //编辑用户界面
    $('div[name="show-edit-modal"]').click(function () {
        userPowerKeyStr = $(this).parent().attr('data-powers');
        userPowerKeys = userPowerKeyStr.split('|');
        $('input[name="edit-powers[]"]').removeAttr('checked');
        for(var i=0;i<userPowerKeys.length;i++){
            $('input[data-id="edit-power-'+userPowerKeys[i]+'"]').parent().checkbox('check');
        }
        nowOperateUserID = $(this).parent().attr('data-id');
        $('input[name="edit-user-id"]').val(nowOperateUserID);
        $('input[name="edit-nicename"]').val($(this).parent().parent().children('td').eq(1).html());
        $('#edit-modal').modal('show');
    });
    $('#ok-edit-user').click(function () {
        $('#form-user-edit').submit();
    });
    //添加用户
    $('#show-add-modal').click(function () {
        $('#add-modal').modal('show');
    });
    $('#ok-add-user').click(function () {
        $('#form-user-add').submit();
    });
    //删除用户界面
    $('div[name="show-del-modal"]').click(function () {
        nowOperateUserID = $(this).parent().attr('data-id');
        $('#del-modal').modal('show');
    });
    $('#ok-del-user').click(function () {
        window.location.href = 'action-set-sys.php?action=user-del&id=' + nowOperateUserID;
    });
});