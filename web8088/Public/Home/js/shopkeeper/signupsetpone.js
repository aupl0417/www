/**
 * Created by Administrator on 2015/3/24.
 */
(function () {
    /**
     * 按钮点击事件
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
        gotoSetpTwo();
    });
    function gotoSetpTwo() {
        var tel = $.trim($("#tel_prefix").val()) + "-" + $.trim($("#tel").val());
        var companyEmail = $.trim($("#compay_email").val());

        // 先禁用
        $(".register_submit").attr("disabled", "disabeld")
        // 组装数据
        var args = {
            "tel":				tel,
            "company_email":	companyEmail
        };
        // 连网检验
        $.post(validateTelAndCompanyEmail_url, args, function(data) {
            console.log(data);

            if (data.status != 200) {
                $("#err_span").html(data.msg);
                $("#err_panel").removeClass("hidden");
                $(".register_submit").removeAttr("disabled");
                return
            }

            // 检验通过，跳转到下一步
            location.href = signUpSetpTwo_url;

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
})();
