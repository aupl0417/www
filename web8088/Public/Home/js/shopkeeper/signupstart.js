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

    $("#btn").click(function () {
        gotoSetpOne();
    });
    /**
     * 注册按钮点击事件
     */
    function gotoSetpOne() {
        var phone_email = $.trim($("#phone_email").val());
        var password = $("#password").val();
        var verify = $.trim($("#verifytext").val());

        // 判断类型
        if (/^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/.test(phone_email)) {
            var type = "email";

        } else if (/^(1[3|5|8])[\d]{9}$/.test(phone_email)) {
            var type= "phone";

        } else {
            return showErr("邮箱或者手机号码不正确！");
        }

        if (!password) {
            return showErr("密码不能为空！");
        }

        if (!verify) {
            return showErr("验证码不能为空！");
        }

        if (verify.length != 4) {
            return showErr("验证码不正确！");
        }

        // 先禁用
        $(".register_submit").attr("disabled", "disabeld");
        // 组装数据
        var args = {
            "type":		type,
            "arg":		phone_email,
            "password":	password,
            "verify":   verify
        };
        // 连网检验
        $.post(validateLoginAndPassword_url, args, function(data) {

            if (data.status != 200) {
                $("#err_span").html(data.msg);
                $("#err_panel").removeClass("hidden");
                $(".register_submit").removeAttr("disabled");
                return
            }

            // 检验通过，跳转到下一步
            location.href = signUpSetpOne_url;

        }, "json")

    }

    /**
     * 消除错误消息
     */
    window.onload = function() {
        $("input[type=text]").focus(function() {
            $("#err_panel").addClass("hidden")
        })
    }

    /**
     * 展示错误信息
     */
    function showErr(str) {
        $("#err_span").html(str);
        $("#err_panel").removeClass("hidden");
    }
})();
