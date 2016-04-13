/**
 * 登录页JS
 * @author liuzilu <fotomxq@gmail.com>
 * @version 1
 * @package center
 */

$(document).ready(function () {
    //表单响应处理
    $('.ui.form').form({
        fields: {
            email: {
                identifier: 'email',
                rules: [
                    {
                        type: 'empty',
                        prompt: '请输入用户名'
                    },
                    {
                        type: 'email',
                        prompt: '用户名必须为邮箱地址'
                    }
                ]
            },
            password: {
                identifier: 'password',
                rules: [
                    {
                        type: 'empty',
                        prompt: '请输入密码'
                    },
                    {
                        type: 'length[6]',
                        prompt: '登录密码必须大于6位数'
                    }
                ]
            }
        }
    });
    //提交表单处理加密
    $('form').submit(function () {
        $('input[name="password"]').val(hex_sha1($('input[name="password"]').val()));
    });
});