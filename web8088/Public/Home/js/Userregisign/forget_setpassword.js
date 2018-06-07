/**
 * Created by Administrator on 2015/3/23.
 */
(function () {
    $("#code").click(function () {
        ontext();
    });

    $("#password_one").click(function () {
        ontext();
    });
    $("#password_two").click(function () {
        ontext();
    });
//隐藏错误提示
    function ontext(){
        document.getElementById('reg_err').className='u_register_err hidden';
    }
//错误提示
    function errtext(classn,etext){
        document.getElementById('reg_err').className=classn;
        document.getElementById('reg_err_text').innerHTML =etext;
    }
//检查验证码格式
    function checkcode(){
        var checkcodemun=$("#code").val();
        var ccode=/^(\d){6}$/;
        if(!ccode.test(checkcodemun)){
            errtext('u_register_err','请输入正确的验证码');
            return false;
        }
        return true;
    }
//检查密码
    function pwcheck(){
        var pw=$("#password_one").val();
        var pwargin=$("#password_two").val();
        var passw=/^\S{6,12}$/;
        if(!passw.test(pw)){
            errtext('u_register_err','密码必须是6~12个字符');
            return false;
        }else if(!passw.test(pwargin)){
            errtext('u_register_err','密码必须是6~12个字符');
            return false;
        }else if(pw!=pwargin){
            errtext('u_register_err','两次密码不一致');
            return false;
        }
        return true;
    }
//*********************************************************************************
    function AllCheck()
    {
//将上面n个函数打包成一个AllCheck()函数，在前台界面中调用该AllCheck()；
        if(checkcode()&&pwcheck()){
            return true;
        }else{
            return false;
        }
    }

    function reset_get(){
        if(AllCheck()){
            var resetdata={};
            resetdata.code=$("#code").val();
            resetdata.password=$("#password_one").val();
            resetdata.passwordargin=$("#password_two").val();
            $.ajax({
                type: 'POST',
                url: reset_url,
                dataType: 'json',
                data:resetdata,
                success: function(data) {
                    console.log(data);
                    if(data.status!=200){
                        errtext('u_register_err',data.msg);
                    }else{
                        errtext('u_register_err','密码重新设置成功!');
                        setTimeout(function () {
                            window.location.href=login_url;
                        },1200);
                    }
                },
                error:function(data){
                    errtext('u_register_err','请重新设置');
                }
            });
        }else{
            return false;
        }
    }

    $("#reset_passowrd").click(function(){
        reset_get();
    });
})()