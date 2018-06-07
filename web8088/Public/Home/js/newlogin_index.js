/**
 * Created by Administrator on 2015/3/23.
 */
(function () {
    function touchHandler(e){
        e.preventDefault();
        e.stopPropagation();
    }
    $(".index_login").click(function (e) {
        e.preventDefault();
        $(".login_box_wrap").removeClass("bounceOutRight").addClass("bounceInDown");;
        $("#login_box_bg").removeClass("hidden");
        document.addEventListener("touchmove",touchHandler,false);
    });
    $("#login_box_bg").click(function(e) {
        if($(e.target).hasClass('login_box_bg')){
            $(".login_box_wrap").removeClass("bounceInDown");
            $(".login_box_wrap").addClass("bounceOutRight");
            setTimeout(function() {
                $("#login_box_bg").addClass("hidden");
            },1000);
            document.removeEventListener("touchmove",touchHandler,false);
        }
    });

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
            datalogin.type=typename;
            datalogin.logintype=typevalue;
            datalogin.password=password;
            datalogin.remember=remember;
            //$(".loading").removeClass("hidden");
            $(".css-loading").removeClass("hidden");
            $.ajax({
                type: 'POST',
                url:post_url,
                dataType: 'json',
                data:datalogin,
                success: function(data) {
                    //$(".loading").addClass("hidden");
                    $(".css-loading").addClass("hidden");
                    if(data.status!=200){
                        $('#reg_err').removeClass('hidden');
                        $("#reg_err_text").html(data.msg);
                    }else{
//									$('#reg_err').removeClass('hidden');
//									$("#reg_err_text").html("登录成功！");
//									setTimeout("window.location.href='{:U("Index/index")}'",1200);

                    	login_type_status=2;
                    //	$('#my').attr("id","panelSwitch");
                    	
                    	
  //                  	  var newlogin_user_header = document.getElementById('newlogin_user_header').innerHTML;
   //                  	 $('#user_html_hidden-hidden').html(newlogin_user_header);

                    	/*$("#user_html_hidden").removeClass('hidden');
                    	$("#user_html_hidden-hidden").addClass('hidden');*/
                    	
                   	  var newlogin_user_left = document.getElementById('newlogin_user_left').innerHTML;
                      var html_left_load_user = juicer(newlogin_user_left, data);
                  	 $('#panelNav').html(html_left_load_user);

                  	 var newlogin_user_footer = document.getElementById('newlogin_user_footer').innerHTML;
                     var html_footer_load_user = juicer(newlogin_user_footer, data);
                 	 $('#footer_id_ajax').html(html_footer_load_user);
                  	
                  	userRedot_login();
                  	
                        $(".login_box_wrap").removeClass("bounceInDown");
                        $(".login_box_wrap").addClass("bounceOutRight");
                        setTimeout(function() {
                            $("#login_box_bg").addClass("hidden");
                        },1000);
                        document.removeEventListener("touchmove",touchHandler,false);

                        
                        console.log(data);
                        //当普通用户登陆成功后，把“登陆”改成“我的”
                        var my = document.getElementById("my");
                        var newMy = my.getElementsByTagName("a")[0];
                        var oldLogin = my.getElementsByTagName("a")[1];
                        oldLogin.className = "hidden";
                        newMy.className = "";
/*
                        var my = document.getElementById("my");
                        var newMy = my.getElementsByTagName("a")[0];
                        var oldLogin = my.getElementsByTagName("a")[1];
                        oldLogin.className = "hidden";
                        newMy.className = "";
        */                
  //                      window.location.href=index_url;
                        // 如果是第一次登陆，则弹出提示
                        /*var startIndex;
                        var endIndex;

                        if(typeof(localStorage.UserFirstLogin) == "undefined") {
                            localStorage.UserFirstLogin = false;
                            startIndex = 5;
                            endIndex = 6;
                            document.addEventListener("touchmove", function(e) {
                                if(startIndex > 0 && startIndex <= endIndex) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                }
                            }, false);

                            var $tipsBg = $(".tips-bg");
                            window.scrollTo(0, 0);
                            $tipsBg.removeClass("hidden");
                            $tipsBg.find("#tip" + startIndex).removeClass("hidden");
                            var $btns = $(".tips>button");
                            $btns.click(closeTip);
                        }

                        function closeTip(e) {
                            $(e.target).parent().addClass("hidden");
                            if (++startIndex <= endIndex) {
                                $("#tip" + startIndex).removeClass("hidden");
                                window.scrollTo(0, 0);
                            } else {
                                $tipsBg.addClass("hidden");
                                $btns.unbind("click");
                            }
                        }*/
                    }
                },
                error:function(data){
                    //$(".loading").addClass("hidden");
                    $(".css-loading").addClass("hidden");
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