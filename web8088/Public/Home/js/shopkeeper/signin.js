/**
 * Created by Administrator on 2015/3/24.
 */
(function () {
    $("input").on("focus", function (event) {
        $(event.target).next().removeClass("op_0");

    });
    $("input").on("blur", function (event) {
        $(event.target).next().addClass("op_0");

    });
    $(".input_close_icon").click(function (event) {
        $(event.target).prev().val("");
    });
    $(".input_close_icon_second").click(function (event) {
        $(event.target).prev().val("");
    });
    $("#btn").click(function () {
        handleSignIn();
    });
    /**
     * 处理登陆请求
     */
    function handleSignIn() {
        // 加载loading动画
        $(".css-loading").removeClass("hidden");
        var type = "";
        var arg = $.trim($("#arg").val());
        var password = $("#password").val();
        // 判断arg是不是为空
        if (arg == "") {
            showErr("登陆邮箱或者手机号码不能为空");
            return
        }
        // 判断密码是不是为空
        if (password == "") {
            showErr("密码不能为空");
            return
        }
        // 判断arg的类型
        if ((/^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/.test(arg))) {
            type = "email"
        } else if (/^1\d{10}$/.test(arg)) {
            type = "phone"
        } else {
            showErr("登陆邮箱或者手机号码不合法");
            return
        }
        // 判断要不要自动登陆
        var autoLogin = false;
        if ($("#auto_login").is(":checked")) {
            autoLogin = true;
        }
        // 获取参数
        var args = {
            "type":			type,
            "arg":			arg,
            "password":		password,
            "autoLogin":	autoLogin
        };
        // 先禁用按钮
        $(".register_submit").attr("disabled", "disabeld");
        // 提交到服务器
        $.post(handleLogin_url, args, function(data) {
            if (data.status != "200") {
                showErr(data.msg);
                $(".register_submit").removeAttr("disabled");
                return
            }

            //// 获取登录过时储存的那个地址
            //if (window.localStorage) {
                //var loc = localStorage.getItem("s_sign_timeout_loc");
                //localStorage.removeItem("s_sign_timeout_loc");
                //if (loc) {
                    //location.href = loc;
                    //return
                //}
            //}
            $(".css-loading").addClass("hidden");
            location.href = index_url + new Date().getTime()
        }, 'json')
    }

    /**
     * 展示错误信息
     */
    function showErr(msg) {
        $(".css-loading").addClass("hidden");
        $("#err_span").html(msg);
        $("#err_panel").removeClass("hidden");
        return
    }
})();
