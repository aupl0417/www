/**
 * Created by Administrator on 2015/3/24.
 */
(function () {
    /**
     * 特技Input框
     */
    $("input").on("focus", function (event) {
        $(event.target).next().removeClass("op_0");

    });
    $("input").on("blur", function (event) {
        $(event.target).next().addClass("op_0");

    });
    $(".input_close_icon").click(function (event) {
        $(event.target).prev().val("");
    });

    $("#btn").click(function () {
        resetPassword();
    });
    /**
     * 重置密码操作
     */
    function resetPassword() {
        // 检验验证码
        var token = $("#token").val();
        if (token == "") {
            showErr("验证码不能为空");
            return
        }
        if (!/^\d{6}$/.test(token)) {
            showErr("验证码不正确");
            return
        }
        // 验证密码
        var password = $("#password").val();
        if (password == "") {
            showErr("密码不能为空");
            return
        }
        if (!/^\S{6,12}$/.test(password)) {
            showErr("密码必须为6到12个字符");
            return
        }
        // 验证确认密码
        var repassword = $("#repassword").val();
        if (repassword == "") {
            showErr("确认密码不能为空");
            return
        }
        if (repassword != password) {
            showErr("两次密码不一致");
            return
        }
        var args = {
            "token":	token,
            "password":	password
        };
        $.post(handleResetPasswd_url, args, function(data) {
            if (data.status != "200") {
                showErr(data.msg);
                return
            }
            location.href = signin_url;
        }, "json")
    }

    /**
     * 展示错误信息
     */
    function showErr(msg) {
        $("#err_span").html(msg);
        $("#err_panel").removeClass("hidden")
    }
})();