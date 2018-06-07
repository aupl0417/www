/**
 * Created by Administrator on 2015/3/24.
 */
(function () {
    /**
     * 特技Input框
     */
    $("input").on("focus", function (event) {
        ontext();
        $(event.target).next().removeClass("op_0");

    });
    $("input").on("blur", function (event) {
        $(event.target).next().addClass("op_0");

    });
    $(".input_close_icon").click(function (event) {
        $(event.target).prev().val("");
    });
//隐藏错误提示
    function ontext(){
        document.getElementById('reg_err').className='input-wrap hidden';
    }
//错误提示
    function errtext(classn,etext){
        document.getElementById('reg_err').className=classn;
        document.getElementById('err_text').innerHTML=etext;
    }
    function clearpw(){
        $('.input-wrap-input').val("");
    }

    $("#submitBtn").click(function () {
        pwCheck();
    });
    function pwCheck(){
        var pw=$('#pw').val();
        var pw1=$('#pw1').val();
        var rule=/^\S{6,12}$/;
        if(pw==''){
            errtext('input-wrap','密码未填写');
            clearpw();
            return false;
        }
        if(pw1==''){
            errtext('input-wrap','密码未填写');
            clearpw();
            return false;
        }
        if(pw!=pw1){
            errtext('input-wrap','密码不一致');
            clearpw();
            return false;
        }
        if(!rule.test(pw)){
            errtext('input-wrap','密码必须是6~12个正常字符');
            clearpw();
            return false;
        }

        // 禁用按钮
        $("#submitBtn").attr("disabled", "disabled");
        $.post(changePassword_url, {"reset":"do","password":pw},function(data){
            console.log(data);

            handleNotSignIn(data);


            if(data.status != 200){
                errtext('input-wrap',data.msg);

            } else {
                clearpw();
                location.href = index_url;
            }

            $("#submitBtn").removeAttr("disabled")

        }, "json");
        return true;
    }

    /**
     * 处理没有登录的情况
     */
    function handleNotSignIn(data) {
        if (data.status != 403) {
            return
        }

        easyAlert(data.msg)
        if (window.localStorage) {
            localStorage.setItem("s_sign_timeout_loc",s_sign_timeout_loc )
        }
        location.href = signup_url;
        throw "exit"
    }
})();
