/**
 * Created by Administrator on 2015/3/24.
 */
(function () {
    $("textarea").on("focus", function (event) {
        $(event.target).next().removeClass("op_0");
    });
    $("textarea").on("blur", function (event) {
        $(event.target).next().addClass("op_0");
    });
    $(".input_close_icon").click(function (event) {
        $(event.target).prev().val("");
    });
    $("#submitBtn").click(function () {
        AllCheck();
    });
    function AllCheck(){
        var remarkvalue = $("textarea[name=feedback]").val();
        var remarktest=/^.{1,255}$/;	//字符长度
        if(remarkvalue.length == 0){
            easyAlert('反馈信息未填写');
            return false;
        }
        if(!remarktest.test(remarkvalue)){
            easyAlert('反馈信息不能超过250个字');
            return false;
        }

        // 这里要联网访问了，先禁用按钮
        $("#submitBtn").attr("disabled", "disabled");
        $.post(addFeedback_url, {"content": remarkvalue}, function(data) {
            // 失败
            handleNotSignIn(data);
            if (data.status != 200) {
                $("#submitBtn").removeAttr("disabled");
                return easyAlert(data.msg);
            }

            easyAlert("意见反馈成功！");
            location.href = system_url;
        }, "json");

    }

    /**
     * 处理没有登录的情况
     */
    function handleNotSignIn(data) {
        if (data.status != 403) {
            return;
        }

        easyAlert(data.msg);
        if (window.localStorage) {
            localStorage.setItem("s_sign_timeout_loc",s_sign_timeout_loc);
        }
        location.href = signin_url;
        throw "exit";
    }
})();