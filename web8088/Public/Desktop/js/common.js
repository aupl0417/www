var FormValidator = {
	isPhone: function (str) {
		var pattern = /^1[3|4|5|7|8][0-9]\d{8}$/;
		return pattern.test(str);
	},
	isEmail: function (str) {
		var pattern = /^[\w-]+(\.[\w-]+)*@(\w)+(\.\w+)+$/;
		return pattern.test(str);
	},
	isPasswd: function (str) {
		var pattern = /^\S{6,12}$/;
		return pattern.test(str);
	}
};

/**
 * Masker
 * Using singleton design pattern, which means thant there is only one instance in the application no matter how many
 * times you invoke the `getInstance` method.
 */
var hMasker = (function () {
	var instance;
	function init () {
		return {
			masker: null,
			isShow: false,
			show: function () {
				if (this.isShow === false) {
					this.masker = document.createElement('div');
					this.masker.className = 'h-masker';
					document.body.appendChild(this.masker);
					this.isShow = true;
				}
			},
			hide: function () {
				if (this.masker && this.isShow === true) {
					document.body.removeChild(this.masker);
					this.isShow = false;
				}
			},
			/* you can bind a click handler to the masker of course */
			click: function (handler) {
				if (typeof(handler) === 'function' && this.masker) {
					this.masker.onclick = handler;
				}
			}
		};
	};
	return {
		getInstance: function () {
			if (!instance) {
				instance = init();
			}
			return instance;
		}
	};
})();
/**
 * login box
 * Also using singleton design pattern
 */
var boxController = (function () {
	var instance;
	var isShow = false;
	var LOGIN = 0;
	var REG_ONE = 1;
	var REG_TWO = 2;
	var FORGET_ONE = 3;
	var FORGET_TWO = 4;

	var currBox;
	var boxes = [];
	function init () {
		/* close the login box with animation and remove the hMasker */
		function closeBox () {
			if (!currBox) return;
			currBox[0].className = 'ease-up-animate login-wrap';
			setTimeout(function () {
				hMasker.getInstance().hide();
				currBox[0].className = 'none';
			}, 500);
			isShow = false;
		};
		/* bind close handler to all close buttons */
		$('.l-close').click(closeBox);
		/* find all boxes/components */
		boxes[LOGIN] = $('#login-box');
		boxes[LOGIN].oneInput = $('#one-input');
		boxes[LOGIN].twoInput = $('#two-input');
		boxes[LOGIN].oneClr = $('#l-u-clr');
		boxes[LOGIN].twoClr = $('#l-p-clr');
		boxes[LOGIN].checkBox = $('#remenber-me');
		boxes[LOGIN].forgetPass = $('#forget-pass');
		boxes[LOGIN].infoWrap = $('#info-wrap');
		boxes[LOGIN].oneBtn = $('#l-one-btn');
		boxes[LOGIN].twoBtn = $('#l-two-btn');
		boxes[LOGIN].oneInput[0].onfocus = function (event) {boxes[LOGIN].oneClr.removeClass('op-0');boxes[LOGIN].infoWrap.addClass('none');};
		boxes[LOGIN].oneInput[0].onblur = function (event) {boxes[LOGIN].oneClr.addClass('op-0');};
		boxes[LOGIN].twoInput[0].onfocus = function (event) {boxes[LOGIN].twoClr.removeClass('op-0');boxes[LOGIN].infoWrap.addClass('none');};
		boxes[LOGIN].twoInput[0].onblur = function (event) {boxes[LOGIN].twoClr.addClass('op-0');};
		boxes[LOGIN].oneClr[0].onclick = function (event) {boxes[LOGIN].oneInput[0].value='';};
		boxes[LOGIN].twoClr[0].onclick = function (event) {boxes[LOGIN].twoInput[0].value='';};

		boxes[REG_ONE] = $('#reg-one-box');
		boxes[REG_ONE].oneInput = $('#ro-one-input');
		boxes[REG_ONE].twoInput = $('#ro-two-input');
		boxes[REG_ONE].oneClr = $('#ro-u-clr');
		boxes[REG_ONE].twoClr = $('#ro-c-clr');
		boxes[REG_ONE].codeBtn = $('#code-btn');
		boxes[REG_ONE].checkBox = $('#ro-agree');
		boxes[REG_ONE].infoWrap = $('#ro-info-wrap');
		boxes[REG_ONE].oneBtn = $('#ro-one-btn');
		boxes[REG_ONE].twoBtn = $('#ro-two-btn');
		boxes[REG_ONE].oneInput[0].onfocus = function (event) {boxes[REG_ONE].oneClr.removeClass('op-0');boxes[REG_ONE].infoWrap.addClass('none');};
		boxes[REG_ONE].oneInput[0].onblur = function (event) {boxes[REG_ONE].oneClr.addClass('op-0');};
		boxes[REG_ONE].twoInput[0].onfocus = function (event) {boxes[REG_ONE].twoClr.removeClass('op-0');boxes[REG_ONE].infoWrap.addClass('none');};
		boxes[REG_ONE].twoInput[0].onblur = function (event) {boxes[REG_ONE].twoClr.addClass('op-0');};
		boxes[REG_ONE].oneClr[0].onclick = function (event) {boxes[REG_ONE].oneInput[0].value='';};
		boxes[REG_ONE].twoClr[0].onclick = function (event) {boxes[REG_ONE].twoInput[0].value='';};

		boxes[REG_TWO] = $('#reg-two-box');
		boxes[REG_TWO].oneInput = $('#rt-one-input');
		boxes[REG_TWO].twoInput = $('#rt-two-input');
		boxes[REG_TWO].threeInput = $('#rt-three-input');
		boxes[REG_TWO].oneClr = $('#rt-u-clr');
		boxes[REG_TWO].twoClr = $('#rt-p-clr');
		boxes[REG_TWO].threeClr = $('#rt-r-clr');
		boxes[REG_TWO].checkBox = $('#rt-agree');
		boxes[REG_TWO].infoWrap = $('#rt-info-wrap');
		boxes[REG_TWO].oneBtn = $('#rt-one-btn');
		boxes[REG_TWO].twoBtn = $('#rt-two-btn');
		boxes[REG_TWO].oneInput[0].onfocus = function (event) {boxes[REG_TWO].oneClr.removeClass('op-0');boxes[REG_TWO].infoWrap.addClass('none');};
		boxes[REG_TWO].oneInput[0].onblur = function (event) {boxes[REG_TWO].oneClr.addClass('op-0');};
		boxes[REG_TWO].twoInput[0].onfocus = function (event) {boxes[REG_TWO].twoClr.removeClass('op-0');boxes[REG_TWO].infoWrap.addClass('none');};
		boxes[REG_TWO].twoInput[0].onblur = function (event) {boxes[REG_TWO].twoClr.addClass('op-0');};
		boxes[REG_TWO].threeInput[0].onfocus = function (event) {boxes[REG_TWO].threeClr.removeClass('op-0');boxes[REG_TWO].infoWrap.addClass('none');};
		boxes[REG_TWO].threeInput[0].onblur = function (event) {boxes[REG_TWO].threeClr.addClass('op-0');};
		boxes[REG_TWO].oneClr[0].onclick = function (event) {boxes[REG_TWO].oneInput[0].value='';};
		boxes[REG_TWO].twoClr[0].onclick = function (event) {boxes[REG_TWO].twoInput[0].value='';};
		boxes[REG_TWO].threeClr[0].onclick = function (event) {boxes[REG_TWO].threeInput[0].value='';};

		boxes[FORGET_ONE] = $('#forget-one-box');
		boxes[FORGET_ONE].oneInput = $('#fo-one-input');
		boxes[FORGET_ONE].oneClr = $('#fo-u-clr');
		boxes[FORGET_ONE].infoWrap = $('#fo-info-wrap');
		boxes[FORGET_ONE].oneBtn = $('#fo-one-btn');
		boxes[FORGET_ONE].twoBtn = $('#fo-two-btn');
		boxes[FORGET_ONE].oneInput[0].onfocus = function (event) {boxes[FORGET_ONE].oneClr.removeClass('op-0');boxes[FORGET_ONE].infoWrap.addClass('none');};
		boxes[FORGET_ONE].oneInput[0].onblur = function (event) {boxes[FORGET_ONE].oneClr.addClass('op-0');};
		boxes[FORGET_ONE].oneClr[0].onclick = function (event) {boxes[FORGET_ONE].oneInput[0].value='';};

		boxes[FORGET_TWO] = $('#forget-two-box');
		boxes[FORGET_TWO].oneInput = $('#ft-one-input');
		boxes[FORGET_TWO].twoInput = $('#ft-two-input');
		boxes[FORGET_TWO].threeInput = $('#ft-three-input');
		boxes[FORGET_TWO].oneClr = $('#ft-c-clr');
		boxes[FORGET_TWO].twoClr = $('#ft-p-clr');
		boxes[FORGET_TWO].threeClr = $('#ft-r-clr');
		boxes[FORGET_TWO].infoWrap = $('#ft-info-wrap');
		boxes[FORGET_TWO].oneBtn = $('#ft-one-btn');
		boxes[FORGET_TWO].twoBtn = $('#ft-two-btn');
		boxes[FORGET_TWO].oneInput[0].onfocus = function (event) {boxes[FORGET_TWO].oneClr.removeClass('op-0');boxes[FORGET_TWO].infoWrap.addClass('none');};
		boxes[FORGET_TWO].oneInput[0].onblur = function (event) {boxes[FORGET_TWO].oneClr.addClass('op-0');};
		boxes[FORGET_TWO].twoInput[0].onfocus = function (event) {boxes[FORGET_TWO].twoClr.removeClass('op-0');boxes[FORGET_TWO].infoWrap.addClass('none');};
		boxes[FORGET_TWO].twoInput[0].onblur = function (event) {boxes[FORGET_TWO].twoClr.addClass('op-0');};
		boxes[FORGET_TWO].threeInput[0].onfocus = function (event) {boxes[FORGET_TWO].threeClr.removeClass('op-0');boxes[FORGET_TWO].infoWrap.addClass('none');};
		boxes[FORGET_TWO].threeInput[0].onblur = function (event) {boxes[FORGET_TWO].threeClr.addClass('op-0');};
		boxes[FORGET_TWO].oneClr[0].onclick = function (event) {boxes[FORGET_TWO].oneInput[0].value='';};
		boxes[FORGET_TWO].twoClr[0].onclick = function (event) {boxes[FORGET_TWO].twoInput[0].value='';};
		boxes[FORGET_TWO].threeClr[0].onclick = function (event) {boxes[FORGET_TWO].threeInput[0].value='';};
		return {
			/* the box may change its appearance while user clicking the buttons, so I provide this functional method for you guys to rock and roll */
			switchTo: function (index) {
				var that = this;
				if (currBox) {currBox.addClass('none')};
				currBox = boxes[index];
				currBox[0].className = 'login-wrap';
				switch(index){
					case LOGIN:
					boxes[LOGIN].oneBtn[0].onclick = function (event) {
                        //alert('先检查用户名和密码');

                        var account = $.trim(boxes[LOGIN].oneInput.val());
                        var pass = boxes[LOGIN].twoInput.val();
                        var isRemenber = boxes[LOGIN].checkBox.is(":checked");

                        var type='';
                        if(window.FormValidator.isPhone(account)){
                            type = 'phone';
                        }else if( window.FormValidator.isEmail(account)){
                            type = 'email';
                        }

                        // 获取参数
                        var args = {
                            "type":			type,
                            "arg":			account,
                            "password":		pass,
                            "autoLogin":	isRemenber
                        };

                        $.post(handleLogin_url, args, function(data) {
                            if (data.status != "200") {
                                that.showInfo(data.msg);
                                return;
                            }
                            location.href ='/Home/Index/index.html';

                        },"json");

					}
					boxes[LOGIN].twoBtn[0].onclick = function (event) {
						that.switchTo(REG_ONE);
					}
					boxes[LOGIN].forgetPass[0].onclick = function (event) {
						that.switchTo(FORGET_ONE);
					}
					break;
					case REG_ONE:
					boxes[REG_ONE].codeBtn[0].onclick = function (event) {
                        var value = $.trim(boxes[REG_ONE].oneInput.val());
                        regsendcode(that, this, value);
					};
					boxes[REG_ONE].oneBtn[0].onclick = function (event) {
                        var code = $.trim(boxes[REG_ONE].twoInput.val());
                        check_code(boxes[REG_ONE], that, code, REG_TWO);
					};
					boxes[REG_ONE].twoBtn[0].onclick = function (event) {
                        that.switchTo(LOGIN);
					};
					break;
					case REG_TWO:
					boxes[REG_TWO].oneBtn[0].onclick = function (event) {
                        var companyName = $.trim(boxes[REG_TWO].oneInput.val());
                        var passwd = boxes[REG_TWO].twoInput.val();
                        var repasswd = boxes[REG_TWO].threeInput.val();
                        handleSignUp(boxes[REG_TWO], that, companyName, passwd, repasswd);
					}
					boxes[REG_TWO].twoBtn[0].onclick = function (event) {
						that.switchTo(LOGIN);
					}
					break;
					case FORGET_ONE:
					boxes[FORGET_ONE].oneBtn[0].onclick = function (event) {
                        var value = $.trim(boxes[FORGET_ONE].oneInput.val());
                        sendForgetCode(this, that, value, FORGET_TWO);
					};
					boxes[FORGET_ONE].twoBtn[0].onclick = function (event) {
						that.switchTo(LOGIN);
					};
					break;
					case FORGET_TWO:
					boxes[FORGET_TWO].oneBtn[0].onclick = function (event) {
                        var code = $.trim(boxes[FORGET_TWO].oneInput.val());
                        var passwd = boxes[FORGET_TWO].twoInput.val();
                        var repasswd = boxes[FORGET_TWO].threeInput.val();
                        resetPassword(that, code, passwd, repasswd);
					}
					boxes[FORGET_TWO].twoBtn[0].onclick = function (event) {
						that.switchTo(LOGIN);
					}
					break;
				}
			},
			/* make sure pass through a string or one string array to display multiple-line information */
			showInfo: function (strings) {
				if (typeof strings === 'string') strings = [strings];
				currBox.infoWrap.html('');
				for (var i = 0; i < strings.length; i++) {
					var li = document.createElement('li');
					li.innerHTML = strings[i];
					currBox.infoWrap.append(li);
				};
				currBox.infoWrap.removeClass('none');
			},
			hideInfo: function () {
				currBox.infoWrap.addClass('none');
			},
			/* show the login box with animation and show a hMasker */
			show: function () {
				if (isShow) return;
				var that = this;
				/* first we may want to do something with the masker */
				var masker = hMasker.getInstance();
				masker.show();
				masker.click(that.close);
				currBox = boxes[LOGIN];
				this.switchTo(LOGIN);
				currBox.addClass('bounce-down-animate');
				isShow = true;
			},
			/* close the login box with animation and remove the hMasker */
			close: closeBox
		};
	};
	return {
		LOGIN: LOGIN,
		REG_ONE: REG_ONE,
		REG_TWO: REG_TWO,
		FORGET_ONE: FORGET_ONE,
		FORGET_TWO: FORGET_TWO,
		getInstance: function () {
			if (!instance) {
				instance = init();
			}
			return instance;
		}
	};
})();

/**
 * TabController. We must give config params before working
 *
 * menuId(ul element supposed),
 * itemsWrapperId,
 * itemClass
 */
function TabController (config) {
	if (!config) return;
	this.config = config;
	this.menu = $('#' + config.menuId);
	this.menuLis = $('#' + config.menuId + ' li');
	this.items = $('#' + config.itemsWrapperId + ' .' + config.itemClass);
	var that = this;
	function reset () {
		for (var i = 0; i < that.menuLis.length; i++) {
			$(that.menuLis[i]).removeClass('active');
		}
		for (var i = 0; i < that.items.length; i++) {
			$(that.items[i]).addClass('none');
		};
	}

	this.menuLis.mouseover(function (event) {
		var index = $.inArray(this, that.menuLis);
		reset();
		$(this).addClass('active');
		$(that.items[index]).removeClass('none');
	});
}


/**
 * 发送验证码
 */
function regsendcode(that, btn, value) {
    var type = '';
    if(FormValidator.isPhone(value)){
        type = 'phone';
    }else if(FormValidator.isEmail(value)){
        type = 'email';
    }else {
        that.showInfo('请输入正确的电话号码或邮箱');
        return false;
    }

    var data_send={};
    data_send.type = type;
    data_send.typevalue = value;

    // 禁用按钮
    $(btn).attr("disabled", "disabled");

    $(".loading").removeClass("hidden");
    $.ajax({
        type: 'POST',
        url: register_send_url,
        dataType: 'json',
        data:data_send,
        success: function(data) {

            $(".loading").addClass("hidden");

            if(data.status!=200){
                $(btn).removeAttr("disabled");
                that.showInfo(data.msg);
                return false;
            }

            that.showInfo('验证码已发送至您的手机或邮箱');
            var limit = 60;
            var timer = setInterval(function () {
                btn.innerHTML = limit + 's后重发';
                if(limit-- <= 0) {
                    clearInterval(timer);
                    btn.innerHTML = '获取验证码';
                    $(btn).removeAttr("disabled");
                }
            }, 1000);

        },
        error:function(data){

            $(btn).removeAttr("disabled");
            $(".loading").addClass("hidden");

            that.showInfo('请重新刷新页面再注册');
        }
    });
}


/**
 * 检验验证码
 */
function check_code(regBox, that, code, reg_two) {
    // 检查有没有同意条款
    if (!regBox.checkBox.is(":checked")) {
        that.showInfo('请先同意17约课用户使用条款');
        return false;
    }

    var codese=/^\d{6}$/;
    if(!codese.test(code)){
        that.showInfo('验证码必须是6位数字');
        return false;
    }

    var data_check={};
    data_check.code = code;

    $.ajax({
        type: 'POST',
        url: register_check_url,
        dataType: 'json',
        data:data_check,
        success: function(data) {
            $(".loading").addClass("hidden");
            if(data.status!=200){
                that.showInfo(data.msg);
                return false;
            }

            that.showInfo("验证码正确！");
            that.switchTo(reg_two);
        },
        error:function(data){
            that.showInfo('请重新刷新页面再注册');
        }
    });
}


/**
 * 处理注册请求
 */
function handleSignUp(regBox, that, companyName, passwd, repasswd) {
    // 检查有没有同意条款
    if (!regBox.checkBox.is(":checked")) {
        that.showInfo('请先同意17约课用户使用条款');
        return false;
    }

    if (passwd != repasswd) {
        that.showInfo('两次密码不一致');
        return false;
    }

    var data_save={};
    data_save.company_name=companyName; // 公司全称
    data_save.password=passwd;     // 密码

    $.ajax({
        type: 'POST',
        url: register_save_url,
        dataType: 'json',
        data: data_save,
        success: function(data) {

            if(data.status!=200){
                that.showInfo(data.msg);
                return false;
            }

            //清除LocalStorage内容，以显示提示
           /* localStorage.removeItem("UserFirstLogin");
            localStorage.removeItem("UserFirstOpenHisOwnWishList");*/
            //$('.shoucan').removeClass('hidden');
          /*  loginSussess();*/

            that.showInfo('注册成功');

            setTimeout(function(){
                $('.shoucan').addClass('hidden');
            },2000);
            if(data.histhref==null||data.histhre==""){
                window.location.href=index_url;
            }else{
                window.location.href=data.histhref;
            }

        },
        error:function(data){
            that.showInfo('请重新刷新页面再注册');
        }
    });

}


/**
 * 忘记密码发送验证码
 */
function sendForgetCode(btn, that, value, forget_two) {

    var type = '';
    if(FormValidator.isPhone(value)){
        type = 'phone';
    }else if(FormValidator.isEmail(value)){
        type = 'email';
    }else {
        that.showInfo('请输入正确的电话号码或邮箱');
        return false;
    }

    // 获取参数
    var args = {
        "type":			type,
        "arg":			value
    };


    // 禁用按钮
    $(btn).attr("disabled", "disabled");

    // 提交到服务器
    $.post(handleForgetPassword_url, args, function(data) {
        $(btn).removeAttr("disabled");

        if (data.status != 200) {
            that.showInfo(data.msg);
            return false;
        }

        // 成功发送验证码
        that.switchTo(forget_two);
    }, 'json');
}


/**
 * 重置密码操作
 */
function resetPassword(that, token, password, repassword) {
    // 检验验证码
    if (token == "") {
        that.showInfo("验证码不能为空");
        return false;
    }
    if (!/^\d{6}$/.test(token)) {
        that.showInfo("验证码不正确");
        return false;
    }
    // 验证密码
    if (password == "") {
        that.showInfo("密码不能为空");
        return false;
    }
    if (!/^\S{6,12}$/.test(password)) {
        that.showInfo("密码必须为6到12个字符");
        return false;
    }
    // 验证确认密码
    if (repassword == "") {
        that.showInfo("确认密码不能为空");
        return false;
    }
    if (repassword != password) {
        that.showInfo("两次密码不一致");
        return false;
    }
    var args = {
        "token":	token,
        "password":	password
    };


    $.post(handleResetPasswd_url, args, function(data) {
        if (data.status != "200") {
            that.showInfo(data.msg);
            return false;
        }

        that.showInfo("密码修改成功");
        setTimeout(function() {
            location = "/";
        }, 1500);

    }, "json")
}
