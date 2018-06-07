/**
 * Created by Administrator on 2015/3/23.
 */
(function () {
    //隐藏错误提示
    $("#typevalue").click(function () {
        ontext();
    });
    function ontext(){
        document.getElementById('reg_err').className='u_register_err hidden';
    }
//错误提示
    function errtext(classn,etext){
        document.getElementById('reg_err').className=classn;
        document.getElementById('reg_err_text').innerHTML =etext;
    }
//检查类型
    function checktype(){
        var type=$("#typevalue").val();
        var email=/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;//邮件地址必须包含@符号和.com等网络域名
        var tel=/^(13[0-9]|15[0|3|6|7|8|9]|18[8|9])\d{8}$/;	//包括移动电话和固定电话，但是必须大于7位
        if(email.test(type))
        {
            document.getElementById('hiddentype').value ='email';
            return true;
        }else if(tel.test(type)){
            document.getElementById('hiddentype').value ='phone';
            return true;
        }else{
            errtext('u_register_err','请输入正确的电话号码或邮箱');
            return false;
        }
    }

    function for_get(){
        if(checktype()){
            var resetdata={};
            resetdata.sendtype=$("#typevalue").val();
            resetdata.type=$("#hiddentype").val();
            $.ajax({
                type: 'POST',
                url:post_url ,
                dataType: 'json',
                data:resetdata,
                success: function(data) {
                    console.log(data);
                    if(data.status!=200){
                        errtext('u_register_err','请输入正确的电话号码或邮箱'+data.msg);
                    }else{
                        errtext('u_register_err','验证码发送成功!');
                        setTimeout(function () {
                            window.location.href=ret_url;
                        },1200);
                    }
                },
                error:function(data){
                    errtext('u_register_err','请重新刷新页面重新发送');
                }
            });
        }else{
            return false;
        }
    }

    $("#send_forget").click(function(){
        for_get();
    });

})()