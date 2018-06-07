/**
 * Created by Administrator on 2015/3/24.
 */
(function () {
    $("input").on("focus", function (event) {
        ontext();
        document.getElementById('reg_err').className='u_register_err hidden';
        $(event.target).next().removeClass("op_0");
    });
    $("input").on("blur", function (event) {
        $(event.target).next().addClass("op_0");
    });
    $(".input_close_icon").click(function (event) {
        $(event.target).prev().val("");
    });
    function ontext(){
        document.getElementById('reg_err').className='u_register_err hidden';
    }

    function errtext(classn,etext){
        document.getElementById('reg_err').className=classn;
        document.getElementById('reg_err_text').innerHTML =etext;
    }

    function TrueNa()			//验证真实姓名
    {
        var TrfName=$("#firstname").val();
        var TrlName=$("#lastname").val();
        var firstName=/^[\u4E00-\u9FA5]{1,2}$/;	//只能是汉字
        var lastName=/^[\u4E00-\u9FA5]{1,3}$/;	//只能是汉字
        if(TrfName.length==0){
            errtext('u_register_err','姓未填写');
            return false;
        }
        else if(!firstName.test(TrfName)){
            errtext('u_register_err','姓只能是1~2个汉字');
            return false;
        }
        else if(TrlName.length==0){
            errtext('u_register_err','名未填写');
            return false;
        }
        else if(!lastName.test(TrlName)){
            errtext('u_register_err','名只能是1~3个汉字');
            return false;
        }
        return true;
    }

    function checktype(){
        var type=$("#hiddentypevalue").val();
        var email=/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;//邮件地址必须包含@符号和.com等网络域名
        var tel=/^(13[0-9]|15[0|3|5|6|7|8|9]|18[8|9])\d{8}$/;	//包括移动电话和固定电话，但是必须大于7位
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

    function regcodecheck(){
    	var code=$("#reg_code_input").val();
    	var codese=/^\d{6}$/;
    	if(!codese.test(code)){
    		errtext('u_register_err','验证码必须是6为数字');
            return false;
    	}
        return true;
    }
    
    function pwcheck(){
        var pw=$("#password").val();
        var passw=/^\S{6,12}$/;
        if(!passw.test(pw)){
            errtext('u_register_err','密码必须是6~12个字符');
            return false;
        }
        return true;
    }

//*********************************************************************************
    function AllCheck()
    {
//将上面n个函数打包成一个AllCheck()函数，在前台界面中调用该AllCheck()；
        if(TrueNa()&&checktype()&&regcodecheck()&&pwcheck()){
            return true;
        }else{
            return false;
        }
    }
    function CheckRegCode()
    {
//将上面n个函数打包成一个AllCheck()函数，在前台界面中调用该AllCheck()；
        if(TrueNa()&&checktype()){
            return true;
        }else{
            return false;
        }
    }

    function regsendcode(){
    	 if(CheckRegCode()){
			 // 禁用滚动
			 $('#register_sendCode').unbind('click', regsendcode)
             var firstname=$("#firstname").val();
             var lastname=$("#lastname").val();
             var typename=$("#hiddentype").val();
             var typevalue=$("#hiddentypevalue").val();
             var datareg={};
             datareg.firstname=firstname;
             datareg.lastname=lastname;
             datareg.type=typename;
             datareg.typevalue=typevalue;
             console.log(datareg);
             $.ajax({
                 type: 'POST',
                 url: register_send_url,
                 dataType: 'json',
                 data:datareg,
                 success: function(data) {
                     if(data.status!=200){
                         $('#reg_err').removeClass('hidden');
                         $("#reg_err_text").html(data.msg);
						//绑定滚动事件
						$('#register_sendCode').bind('click', regsendcode);
                     }else{
                         $('#reg_err').removeClass('hidden');
                         $("#reg_err_text").html("验证码发送成功！");
                         onaclick();
                     }
                 },
                 error:function(data){
                     $('#reg_err').removeClass('hidden');
                     $("#reg_err_text").html('请重新刷新页面再注册');
                 }
             });

         }else{
             return false;
         }
    }
    
    
    function regdata(){

        if(AllCheck()){
 /*
            var firstname=$("#firstname").val();
            var lastname=$("#lastname").val();
            var typename=$("#hiddentype").val();
            var typevalue=$("#hiddentypevalue").val();
            var password=$("#password").val();
            var verify=$("#verifytext").val();
            var datareg={};
            datareg.firstname=firstname;
            datareg.lastname=lastname;
            datareg.type=typename;
            datareg.typevalue=typevalue;
            datareg.password=password;
            datareg.verify=verify;
            $.ajax({
                type: 'POST',
                url: register_url,
                dataType: 'json',
                data:datareg,
                success: function(data) {
                    if(data.status!=200){
                        $('#reg_err').removeClass('hidden');
                        $("#reg_err_text").html(data.msg);
                    }else{
                        $('#reg_err').removeClass('hidden');
                        $("#reg_err_text").html("注册成功！");
                        setTimeout(function () {
                            window.location.href=index_url;
                        },1000);
                    }
                },
                error:function(data){
                    $('#reg_err').removeClass('hidden');
                    $("#reg_err_text").html('请重新刷新页面再注册');
                }
            });

*/
            var firstname=$("#firstname").val();
            var lastname=$("#lastname").val();
            var typename=$("#hiddentype").val();
            var typevalue=$("#hiddentypevalue").val();
            var password=$("#password").val();
            var regcode=$("#reg_code_input").val();
            var datareg={};
            datareg.firstname=firstname;
            datareg.lastname=lastname;
            datareg.type=typename;
            datareg.typevalue=typevalue;
            datareg.password=password;
            datareg.code=regcode;
            $.ajax({
                type: 'POST',
                url: register_check_url,
                dataType: 'json',
                data:datareg,
                success: function(data) {
                    if(data.status!=200){
                        $('#reg_err').removeClass('hidden');
                        $("#reg_err_text").html(data.msg);
                    }else{
                        $('#reg_err').removeClass('hidden');
                        $("#reg_err_text").html("注册成功！");
                        setTimeout(function () {
                            window.location.href=index_url;
                        },1000);
                    }
                },
                error:function(data){
                    $('#reg_err').removeClass('hidden');
                    $("#reg_err_text").html('请重新刷新页面再注册');
                }
            });

        }else{
            return false;
        }
    }
    
    //倒计时
    function onaclick(){
        var i=60;
        setInterval(function(){
            if(i>=0){
                // sendtel.setAttribute('color','red'); //改变id为sendtel的颜色
                munid.innerHTML=i+'s';
            }
            else{
                //绑定滚动事件
                $('#register_sendCode').bind('click', regsendcode);
                window.clearInterval(this); //停止计时
				munid.innerHTML='60s';
				ontext();
                register_sendCode.innerHTML='重新获取';
            }
            i--;
        },1000);
    }
    
    $('#register_sendCode').bind('click', regsendcode);
    
    $("#regyueke").click(function(){
        regdata();
    });
})();