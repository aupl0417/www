/**
 * Created by Administrator on 2015/3/23.
 */
(function () {
    var oldScroll = $(".login_height").height();
    $("input").on("focus", function (event) {
        ontext();
        var height = oldScroll + 150;
        $(".login_height").css("height",height+"px");
        $(event.target).next().removeClass("op_0");
    }).on("blur", function (event) {
        $(".login_height").css("height",oldScroll+"px");
        $(event.target).next().addClass("op_0");
    });

    $(".input_close_icon").click(function (event) {
        $(event.target).prev().val("");
    });
    $(".input_close_icon_second").click(function (event) {
        $(event.target).prev().val("");
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
    function showLoading(){$(".css-loading").removeClass("hidden");}
    function hiddeLoading(){$(".css-loading").addClass("hidden");}
//检查类型
    function checktype(){
        var type=$("#logintype").val();
        type=$.trim(type);
        var email=/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;//邮件地址必须包含@符号和.com等网络域名
        var tel=/^(1)\d{10}$/;	//包括移动电话和固定电话，但是必须大于7位
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
//检查密码
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
        if(checktype()&&pwcheck()){
            return true;
        }else{
            return false;
        }
    }

    function logindata(){
        if(AllCheck()){
            showLoading();
            var typename=$("#hiddentype").val();
            var typevalue=$("#logintype").val();
            var password=$("#password").val();
            var remember=$("#remember").is(":checked");
        	if(remember){
            	localstorage_user();
        	}else{
        		dellocalstorage();
        	}
            var datalogin={};
            var visitorid=localStorage.visitor;
            if(visitorid!=null&&visitorid!=''){
            	datalogin.visitorid=visitorid;
            }
            datalogin.type=typename;
            datalogin.logintype=typevalue;
            datalogin.password=password;
            datalogin.remember=remember;
            $.ajax({
                type: 'POST',
                url:post_url,
                dataType: 'json',
                data:datalogin,
                success: function(data) {
                    hiddeLoading();
                    if(data.status!=200){
                        $('#reg_err').removeClass('hidden');
                        $("#reg_err_text").html(data.msg);
                    }else{
//									$('#reg_err').removeClass('hidden');
//									$("#reg_err_text").html("登录成功！");
//									setTimeout("window.location.href='{:U("Index/index")}'",1200);
                    	if(data.histhref!=400){
                    		window.location.href=data.histhref;
	                   	}else{
	                  		window.location.href=index_url;
	                   	}
                    }
                },
                error:function(data){
                    hiddeLoading();
                    $('#reg_err').removeClass('hidden');
                    $("#reg_err_text").html('请重新刷新页面再登录');
                }
            });
        }else{
            return false;
        }
    }

    $("#loginyueke").click(function(){
        logindata();
    });


    function dellocalstorage(){
    	localStorage.removeItem("logintype");
    	localStorage.removeItem("hiddentype");
    	localStorage.removeItem("password");
    	return true;
    }
    function localstorage_user(){
    	localStorage.logintype=$("#logintype").val();
    	localStorage.hiddentype=$("#hiddentype").val();
    	localStorage.password=$("#password").val();
    	return true;
    }
    
    $(function(){
    	var logintype_user=localStorage.logintype;
    	var hiddentype_user=localStorage.hiddentype;
    	var password_user=localStorage.password;
    	if(logintype_user!=null&&logintype_user!=''&&hiddentype_user!=null&&hiddentype_user!=''){
    		$("#logintype").val(logintype_user);
    		$("#hiddentype").val(hiddentype_user);
    	}
    	if(password_user!=null&&password_user!=""){
    		$("#password").val(password_user);
    		$("#remember").prop("checked","checked");
    	}
    });
    
    
    
    
    
    
    
    
    
    
    
})();