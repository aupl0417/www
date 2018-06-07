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
        sendVerify();
    });
    /**
     * 提交表单之前执行
     */
    function sendVerify() {
        var type = "";
        var arg = $("#arg").val();
        // 判断arg是不是为空
        if (arg == "") {
            showErr("登陆邮箱或者手机号码不能为空");
            return
        }
        // 判断arg的类型
        if ((/^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/.test(arg))) {
            type = "email";
        } else if (/^1\d{10}$/.test(arg)) {
            type = "phone";
        } else {
            showErr("邮箱或者手机号码不合法");
            return
        }
        // 获取参数
        var args = {
            "type":			type,
            "arg":			arg
        };
        // 提交到服务器
        $(".register_submit").attr("disabled", "disabled");
        $.post(handleForgetPassword_url, args, function(data) {

            if (data.status != "200") {
                $(".register_submit").removeAttr("disabled");
                showErr(data.msg);
                return;
            }
            location.href = ResetPassword_url;
        }, 'json');
    }

    /**
     * 展示错误信息
     */
    function showErr(msg) {
        $("#err_span").html(msg);
        $("#err_panel").removeClass("hidden");
    }
})();
