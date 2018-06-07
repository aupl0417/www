$(function() {

    /**
     * 特技Input框
     */
    $("input").on("focus", function (event) {
        $("#top_header").addClass("fixedIOSBug");
        $(".enterprise_cont_footer").addClass("fixedIOSBug");
        $(event.target).next().removeClass("op_0");

    });
    $("input").on("blur", function (event) {
        $(event.target).next().addClass("op_0");
        $("#top_header").removeClass("fixedIOSBug");
        $(".enterprise_cont_footer").removeClass("fixedIOSBug");

    });
    $(".input_close_icon").click(function (event) {
        $(event.target).prev().val("");
    });

    /**
     * 分享
     **/
    $("#share_btn").click(function () {
        share.show();
    })

    $("#winSubmitBtn").on("click", phoneWinSubmit);
    $("#winCancelBtn").on("click", phoneWinCancel);

    $("#uAvatarsMore").on("click",showUserAvatars);
    $("#commentsMore").on("click",showComments);
    $("#shopInfosMore").on("click",showShopInfos);
    $("#commentBtn").on("click",postComment);

    $("#enrollBtn").bind("click", enroll);
    $("#starBtn").bind("click", star);

    $("#getSmsVerifyBtn").bind("click", getSmsVerify);

    showUserAvatars();	// 9 （每次多少条）
    showComments();		// 5
    showShopInfos();		// 3
    showGener();			// 2

});

function showLoading() {$(".css-loading").removeClass("hidden");}
function hiddeLoading() {$(".css-loading").addClass("hidden");}

/**
 * 展示用户头像
 */
function showUserAvatars() {
    //$(".loading").removeClass("hidden");
    showLoading();
    $.get(userAvatarUrl, {'id':infoId, 'page': uAvatarPage}, function(data) {
        console.log(data);

        //$(".loading").addClass("hidden");
        hiddeLoading();

        // 判断有没有数据
        if (!data.data) {
            $("#uAvatarsMore").addClass("hidden");
            return;
        }

        // 判断是不是最后一页
        if (data.isLast) {
            $("#uAvatarsMore").addClass("hidden");
        } else {
            $("#uAvatarsMore").removeClass("hidden");
        }

        // 模板输出
        bindTpl("uAvatarTpl", "uAvatarPanel", data);

        uAvatarPage++;
        //游客是否报名-改变html
        visitor_enrolled_status();
    }, "json");
}

/**
 * 展示评论
 */
function showComments() {
    //$(".loading").removeClass("hidden");
    showLoading();

    $.get(commentUrl, {'id':infoId, 'page': commentPage}, function(data) {
        console.log(data);

        //$(".loading").addClass("hidden")
        hiddeLoading();

        // 判断有没有数据
        if (!data.data) {
            $("#commentsMore").addClass("hidden")
            return
        }

        // 判断是不是最后一页
        if (data.isLast) {
            $("#commentsMore").addClass("hidden")
        } else {
            $("#commentsMore").removeClass("hidden")
        }

        // 模板输出
        bindTpl("commentTpl", "commentPanel", data)

        commentPage++
    }, "json")
}

/**
 * 展示课程信息
 */
function showShopInfos() {
    //$(".loading").removeClass("hidden")
    $(".css-loading").removeClass("hidden")

    $.get(shopInfoUrl, {"id": shopkeeperId, "page": shopInfoPage}, function(data) {
        console.log(data)

        //$(".loading").addClass("hidden")
        hiddeLoading();

        // 判断有没有数据
        if (!data.data) {
            $("#shopInfosMore").addClass("hidden")
            return
        }

        // 判断是不是最后一页
        if (data.isLast) {
            $("#shopInfosMore").addClass("hidden")
        } else {
            $("#shopInfosMore").removeClass("hidden")
        }

        // 模板输出
        bindTpl("shopInfoTpl", "shopInfoPanel", data)

        shopInfoPage++

    }, "json")

}

/**
 * 发表评论
 */
function postComment() {
    var arg = {};
    arg.id = infoId;
    arg.content = $("#commentInput").val();

    //游客
    var visitorid=localStorage.visitor;
    if(visitorid!=null&&visitorid!=''){
    	arg.visitorid=visitorid;
    }


    // 校验评论
    if (arg.content.length == 0) {
        share.setText("评论不能为空");
        share.showhare();
        share.hiddenShare();
        return false;
    }
    if (arg.content.length > 255) {
        share.setText("评论不能超过255个字");
        share.showhare();
        share.hiddenShare();
        return false;
    }

    $("#commentBtn").unbind("click", postComment)

    //$(".loading").removeClass("hidden")
    $(".css-loading").removeClass("hidden")
    $.post(postCommentUrl, arg, function(data) {
        console.log(data)

        //$(".loading").addClass("hidden")
        hiddeLoading();

        if (data.status != 200) {
            $("#commentBtn").bind("click", postComment);
            share.setText(data.msg);
            share.showhare();
            share.hiddenShare();

            handleNotSignIn(data.msg);

            return false;
        }

        //-----------------/游客
        if(typeof(data.data.vid) != "undefined"&&typeof(data.data.vid) != ""&&typeof(data.data.vid) != null){
        	localStorage.visitor=data.visitor; //游客
        }

        // 模板输出
        var tpl = document.getElementById("commentTpl").innerHTML;
        var html = juicer(tpl, data);
        $("#commentPanel").prepend(html)

        $("#commentInput").val("")

        $("#commentBtn").bind("click", postComment)
    }, "json")
}

/**
 * 报名
 */
function enroll() {
    if (isUserEnroll) {
        share.setText("您已经报名了");
        share.showhare();
        share.hiddenShare();
        return false;
    }
    // 只用用户才可以报名
    if (!isUserSignIn) {
    	return visitorEnroll(); //游客报名
        //return handleNotSignInLocal();
    }

    // 上传了头像才能报名
    if (!isUserHasAvatar) {
        return alertBlackWin("请您上传头像后再报名");
    }

    // 满了40分才可以报名
    if (userScore < 40) {
        return alertBlackWin("请您填写个人资料满40分后再报名");
    }

    // 您确定要报名吗
    if (!confirm("您确定要报名，已便商家联系您吗？")) {
        return false ;
    }

    // 登录了但没有手机号码
    if (visitUserId && !visitUserPhone) {
       return  $("section.window_bg").removeClass('hidden')
    }

    handleEnroll(visitUserPhone);
}

function handleEnroll(phone) {
    $("#enrollBtn").unbind("click", enroll)

    var smsVerify = $.trim($("#smsVerifyInput").val());
    $.post(enrollUrl, {'id': infoId, 'phone': phone, "sms_verify": smsVerify}, function(data) {
        console.log(data);

        $("#enrollBtn").bind("click", enroll)

        if (data.status != 200) {
            share.setText(data.msg);
            share.showhare();
            share.hiddenShare();

            handleNotSignIn(data.msg);

            return false;
        }

        // 报名成功
        easyAlert("恭喜您报名成功！");
        isUserEnroll = true;
        $("#enrollBtn").html("已报名");

        // 隐藏手机输入框
        $("section.window_bg").addClass('hidden')

        // 回显头像到报名用户栏
        $("#enrollUserCount").html(parseInt($("#enrollUserCount").html()) + 1);
        bindTpl("uAvatarTpl", "uAvatarPanel", {"data": [userData]});

    }, "json")

}

function phoneWinSubmit() {
    var phone = $.trim($("#phoneInput").val());
    var smsVerify = $.trim($("#smsVerifyInput").val());

    // 验证
    if (!phone) {
        share.setText("手机号码不能为空");
        share.showhare();
        share.hiddenShare();
        return false;
    }
    if (!/^1\d{10}$/.test(phone)) {
        share.setText("手机号码不合法");
        share.showhare();
        share.hiddenShare();
        return false;
    }

    if (!smsVerify) {
        share.setText("请输入验证码");
        share.showhare();
        share.hiddenShare();
        return false;
    }
    if (!/^\d{6}$/.test(smsVerify)) {
        share.setText("验证码不正确.");
        share.showhare();
        share.hiddenShare();
        return false;
    }

    handleEnroll(phone);
}

function phoneWinCancel() {
    $("section.window_bg").addClass('hidden')
}
var seed=60;
var t1=null;
//60s倒计时
function tip() {
        seed--;
        if (seed < 1) {
            enableBtn();
            seed = 60;
            $("#getSmsVerifyBtn").text('获取验证码');
            var t2 = clearInterval(t1);
        } else {
            $("#getSmsVerifyBtn").text(seed + 's后重新发送');
        }
    }
    function disableBtn(){
    	$('#getSmsVerifyBtn').attr("disabled","disabled");
	    $("#getSmsVerifyBtn").css("background-color","#D3D3D3");
        $("#getSmsVerifyBtn").css("border-color", "DDD8CE");
    }
    function enableBtn(){
    	$('#getSmsVerifyBtn').removeAttr("disabled");
	$("#getSmsVerifyBtn").css("background-color","#4496ff");
    }
function getSmsVerify() {

    var phone = $.trim($("#phoneInput").val());
    // 验证
    if (!phone) {
        share.setText("手机号码不能为空");
        share.showhare();
        share.hiddenShare();
        return false;
    }
    if (!/^1\d{10}$/.test(phone)) {
        share.setText("手机号码不合法");
        share.showhare();
        share.hiddenShare();
        return false;
    }
    disableBtn();
	t1 = setInterval(tip, 1000);
    // 联网获取短信验证码
    $("#getSmsVerifyBtn").unbind("click", getSmsVerify);
    $.post(getSmsVerifyUrl, {"phone": phone}, function(data) {
        console.log(data);

        $("#getSmsVerifyBtn").bind("click", getSmsVerify);

        if (data.status != 200) {
            share.setText(data.msg);
        } else {
            share.setText("验证码已发送...");
        }
        share.showhare();
        share.hiddenShare();

    }, "json");
}


/**
 * 收藏
 */
function star() {
    // 只有用户才能收藏
    if (!isUserSignIn) {
        handleNotSignInLocal();
    }

    $("#starBtn").unbind("click", star)
    $.post(starUrl, {'id': infoId}, function(data) {
        $("#starBtn").bind("click", star)

        if (data.status != 200) {
            $("#starBtn").html(starIcon + '收藏');
            share.setText(data.msg);
            share.showhare();
            share.hiddenShare();

            handleNotSignIn(data.msg);

            return false;
        }

        $("#starBtn").html(starIcon + '已收藏');
        share.setText("收藏成功");
        share.showhare();
        share.hiddenShare();
        return false;

    }, "json")
}


/**
 * 获取热门课程
 */
function showGener() {
    $.post(generUrl, function(data) {

        console.log(data)

        // 模板输出
        bindTpl("generTpl", "generPanel", data)

    }, "json")
}

/**
 * 拼接模板了
 */
function bindTpl(tplId, panelId, data) {
    var tpl = document.getElementById(tplId).innerHTML;
    var html = juicer(tpl, data);
    $("#"+panelId).append(html)
}

/**
 * 删除评论
 */
function deleteComment(node, id) {
    if (!confirm("您确定要删除这条评论吗？")) {
        return false;
    }

    $(node).attr("disabled", "disabled");


    var delarg={};
    delarg.id=id;
    //游客
    var visitorid=localStorage.visitor;
    if(visitorid!=null&&visitorid!=''){
    	delarg.visitorid=visitorid;
    }
    // 联网了
    $.post(deleteCommentUrl, delarg, function(data) {
        console.log(data);

        // 删除不成功
        if (data.status != 200) {
            share.setText(data.msg);
            share.showhare();
            share.hiddenShare();
            $(node).removeAttr("disabled");
            return false;
        }

        // 删除成功
        share.setText("删除成功");
        share.showhare();
        share.hiddenShare();
        $("#comment_id_" + id).remove();

    }, "json");
}

/**
 * 显示评论子回复框
 */
function showSubCommentPanel(commentId) {
    $(".comment_sub_id").addClass("hidden");
    $("#comment_sub_id_" + commentId).removeClass("hidden");
}

/**
 * 这里是发布回复
 */
function postSubComment(node, parentId) {
    var arg = {};
    arg.id = infoId;
    arg.content = $("#comment_sub_input_" + parentId).val();
    arg.parent_id = parentId;

    // 校验评论
    if (arg.content.length == 0) {
        share.setText("评论不能为空");
        share.showhare();
        share.hiddenShare();
        $("#comment_sub_id_" + parentId).addClass("hidden");
        return false;
    }
    if (arg.content.length > 255) {
        share.setText("评论不能超过255个字");
        share.showhare();
        share.hiddenShare();
        return false;
    }

    $(node).attr("disabled", "disabled");
    //$(".loading").removeClass("hidden");
    showLoading();

    $.post(postCommentUrl, arg, function(data) {
        console.log(data);

        //$(".loading").addClass("hidden");
        hiddeLoading();
        $(node).removeAttr("disabled");

        if (data.status != 200) {
            share.setText(data.msg);
            share.showhare();
            share.hiddenShare();

            handleNotSignIn(data.msg);

            return false;
        }

        // 模板输出
        var tpl = document.getElementById("commentTpl").innerHTML;
        var html = juicer(tpl, data);
        $("#commentPanel").prepend(html);

        $("#comment_sub_input_" + parentId).val("");
        $("#comment_sub_id_" + parentId).addClass("hidden");

    }, "json");

}

/**
 * 处理没有登录的情况
 */
function handleNotSignIn(msg) {
    if (msg == "请先登录") {
        pushnowhref(function() {
            setTimeout(function() {
                location = loginRegisterUrl;
            }, 1000);
        });
    }
}

/**
 * 不联网的情况下处理没有登录的情况
 */
function handleNotSignInLocal() {
    share.setText("请先登录");
    share.showhare();
    share.hiddenShare();
    pushnowhref(function() {
        setTimeout(function() {
            location = loginRegisterUrl;
        }, 1000);
    });
}

/**
 * 上传当前的url地址
 * @param func
 */
function pushnowhref(func){
	var now_href={};
	now_href.nowhref=nowshref;
	console.log(now_href);
	 $.post("/Api/User/nowshref",now_href , function(data) {
		 console.log(data);

         func();

		 return;
	 }, "json")
	 return;
}

/**
 * 上传当前的url地址
 * @param func
 */
function pushnowhref1(){
	var now_href={};
	now_href.nowhref=nowshref;
	console.log(now_href);
	 $.post("/Api/User/nowshref",now_href , function(data) {
		 console.log(data);
		 return;
	 }, "json")
	 return;
}
//-------------游客-------------------------------
function visitorEnroll(){
    // 您确定要报名吗
    if (!confirm("您确定要报名，已便商家联系您吗？")) {
        return false ;
    }
    return visitorhandleEnroll();
}

function visitorhandleEnroll() {
	pushnowhref1();
    $("#enrollBtn").unbind("tap", enroll)
    var enrolldata={};
    var visitorid=localStorage.visitor;
    if(visitorid!=null&&visitorid!=''){
    	enrolldata.visitorid=visitorid;
    }
    enrolldata.id=infoId;
    $.post(enrollUrl, enrolldata, function(data) {
        console.log(data);

        $("#enrollBtn").bind("tap", enroll)

        if (data.status != 200) {
            share.setText(data.msg);
            share.showhare();
            share.hiddenShare();

            handleNotSignIn(data.msg);

            return false;
        }

        // 报名成功
        isUserEnroll = true;
        $("#enrollBtn").html("已报名");


 //-------------游客
        localStorage.visitor=data.visitor; //游客
        // 回显游客头像到报名用户栏
    	var visitorData = {
    	        "id":       0,
    	        "visitor_id":       data.visitor,
    	        "avatar":  visitor_avatar,
    	    };
    	$("#enrollUserCount").html(parseInt($("#enrollUserCount").html()) + 1);
        bindTpl("uAvatarTpl", "uAvatarPanel", {"data": [visitorData]});
        visitAssistTrue(); //判断是否游客-则下步操作
    	return true;//游客报名成功
//-------------游客

    }, "json")

}
function visitor_enrolled_status(){
	var visitor_status=localStorage.visitor;
	if(visitor_status!=''&&visitor_status!=null){
		$("[data-visitid]").each(function() {
			var visitid_list=$(this).attr("data-visitid");
			if(visitid_list==visitor_status){
				 $("#enrollBtn").html("已报名");
			}
		});
	}
}

//--------------------------------------------------------------游客

//上传头像
$(function(){
	if(isUserSignIn&&!isUserHasAvatar){
		$(".t_avatar_bg").removeClass("hidden");
	}
});
//是游客则弹出提示
function visitAssistTrue(){
	if(!isShopkeepSign&&!isUserSignIn){
		$(".tourist_succ_bg").removeClass("hidden");
	}
}
//游客选择残忍拒绝
$("#t_refuse_btn").click(function(){
	$(".tourist_succ_bg").addClass("hidden");
});
//游客选择登录
$("#t_login_btn").click(function(){
	window.location.href=loginRegisterUrl;
});
//游客选择注册
$("#t_reg_btn").click(function(){
	window.location.href=regUserUrl;
});

/*Input上传图片*/
$("#btn").bind("click", btnavatarfile);
//点击上传头像
function btnavatarfile(){
    $('#btn').click(function () {
        $('#file').trigger('click');
    });
};
//回显
$("#file").change(function(){
        var objUrl = getObjectURL(this.files[0]) ;
        console.log("objUrl = "+objUrl) ;
        if (objUrl) {
            $("#btn").attr("src", objUrl) ;
            $("#imgsrc").val(objUrl) ;
            submitavatar();
        }
    });


//用户头像上传
function submitavatar(){
	var files 	 = document.getElementById("file").files;
	var avatarData = new FormData();
	avatarData.append("useravatar", files[0]);

	// 禁用上传操作
    $("#btn").unbind("click", btnavatarfile);
    easyAlert("正在上传中，请耐心等待...");
    // 开始上传信息
    $.ajax({
        url: handleavatar,
        type: 'POST',
        data: avatarData,
        cache: false,
        dataType: 'json',
        processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        success: function (data, textStatus, jqXHR) {
            console.log(data);

            if (data.status != "200") {
	            $("#btn").bind("click", btnavatarfile);
	            easyAlert(data.msg);
                return false;
            }
            $(".t_avatar_bg").addClass("hidden");
            easyAlert("头像上传成功");
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            easyAlert('ERRORS: ' + textStatus);
            $("#btn").bind("click", btnavatarfile);
        }
    });
}

/**
 * 弹出黑色提示框
 */
function alertBlackWin(msg) {
    share.setText(msg);
    share.showhare();
    share.hiddenShare();
}
