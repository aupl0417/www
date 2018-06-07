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
        gotoSetpThree();
    });
    /**
     * 按钮点击事件
     */
    function gotoSetpThree() {
        var company_name = $.trim($("#company_name").val());

        // 先禁用
        $(".register_submit").attr("disabled", "disabeld");
        // 组装数据
        var args = {
            "company_email":	company_name
        };
        // 连网检验
        $.post(validateCompanyName_url, args, function(data) {

            if (data.status != 200) {
                $("#err_span").html(data.msg);
                $("#err_panel").removeClass("hidden");
                $(".register_submit").removeAttr("disabled");
                return
            }

            // 检验通过，跳转到下一步
            location.href = signUpSetpThree_url;

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
