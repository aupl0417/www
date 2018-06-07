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
 * Using sigleton design pattern.
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
var loginBox = (function () {
	var instance;
	var O_LOGIN = 0;
	var O_REG_ONE = 1;
	var O_REG_TWO = 2;
	/* all of these below are the IDs of elements in the login box(It's a bad design) */
	var option = {
		loginBox: document.getElementById('login-box'),

		closeBtn: document.getElementById('l-close-btn'),

		oneInputWrap: document.getElementById('one-input-wrap'),
		oneInput: document.getElementById('one-input'),
		codeBtn: document.getElementById('code-btn'),
		lUClr: document.getElementById('l-u-clr'),

		twoInputWrap: document.getElementById('two-input-wrap'),	
		twoInput: document.getElementById('two-input'),
		lPClr: document.getElementById('l-p-clr'),

		optionWrap: document.getElementById('option-wrap'),
		optionCheckBox: document.getElementById('remenber-me'),

		infoWrap: document.getElementById('info-wrap'),
	
		lOneBtn: document.getElementById('l-one-btn'),
		lTwoBtn: document.getElementById('l-two-btn')
	};
	option.uIcon = option.oneInputWrap.getElementsByTagName('img')[0];
	option.pIcon = option.twoInputWrap.getElementsByTagName('img')[0];
	option.optionLabel = option.optionWrap.getElementsByTagName('label')[0];
	option.optionSpan = option.optionWrap.getElementsByTagName('span')[0];
	function init () {
		function reset () {
			$(option.uIcon).addClass('none');
			$(option.pIcon).addClass('none');
			option.oneInput.className = 'l-input';
			option.oneInput.value = '';
			$(option.codeBtn).addClass('none');
			option.twoInput.className = 'l-input';
			option.twoInput.value = '';
			$(option.optionSpan).addClass('none');
			$(option.infoWrap).addClass('none');
		};
		return {
			/* because the box may change its apparence while user clicking the buttons, so I provide this functional method for you guys to rock and roll */	
			switchTo: function (index) {
				var that = this;
				reset();
				switch(index){
					/* organization login */
					case O_LOGIN:
						$(option.oneInput).addClass('mleft42px').addClass('width75');
						$(option.twoInput).addClass('mleft42px');
						/* input icons */
						$(option.uIcon).removeClass('none');
						$(option.pIcon).removeClass('none');
						$(option.optionSpan).removeClass('none');
						/* input close btn */
						option.oneInput.onfocus = function (event) { $(option.lUClr).removeClass('op-0');that.hideInfo();};
						option.oneInput.onblur = function (event) { $(option.lUClr).addClass('op-0');};
						option.lUClr.onclick = function (event) { option.oneInput.value = ''; }

						option.twoInput.onfocus = function (event) { $(option.lPClr).removeClass('op-0');that.hideInfo();};
						option.twoInput.onblur = function (event) { $(option.lPClr).addClass('op-0');};
						option.lPClr.onclick = function (event) { option.twoInput.value = ''; };
						/* option panel */
						option.optionLabel.innerHTML = '记住我';
						/* login btn */
						option.lOneBtn.innerHTML = '商家登陆';
						option.lOneBtn.onclick = function (event) {
							var account = option.oneInput.value;
							var pass = option.twoInput.value;
							var isRemenber = option.optionCheckBox.checked;
							var strings = [
							'账号：' + account + ' 是否合法：' + (window.FormValidator.isPhone(account) || window.FormValidator.isEmail(account)),
							'密码：' + pass + ' 是否合法：' + (window.FormValidator.isPasswd(pass)),
							'记住我：' + isRemenber									
							];
							that.showInfo(strings);
						}
						/* register btn */
						option.lTwoBtn.innerHTML = '商家注册';
						option.lTwoBtn.onclick = function (event) {
							that.switchTo(O_REG_ONE);
						}
					break;
					case O_REG_ONE:
						$(option.oneInput).addClass('mleft16px').addClass('width55');
						$(option.twoInput).addClass('mleft16px');
						$(option.oneInput).attr('placeholder', '请输入手机号码/邮箱');
						$(option.codeBtn).removeClass('none');
						$(option.twoInput).attr('placeholder', '请输入验证码');

						/* bind click handler to code btn */
						option.codeBtn.onclick = function (event) {
							if (FormValidator.isPhone(option.oneInput.value) || FormValidator.isEmail(option.oneInput.value)) {
								that.showInfo(['验证码已发送至您的手机或邮箱']);
							} else {
								that.showInfo(['请输入正确的手机号码或邮箱']);
								return;
							}
							this.disabled = 'disabled';
							var self = this;
							var countDown = 10;
							var timer = setInterval(function () {

								self.innerHTML = countDown + 's后重发';
								if (countDown <= 0) {clearInterval(timer);self.innerHTML = '获取验证码'; self.removeAttribute('disabled'); return;};
								countDown--;
							}, 1000);
						}

						option.optionLabel.innerHTML = '同意<a href="#" style="color:white">17约课商家使用条款</a>';
						option.lOneBtn.innerHTML = '下一步';
						/* next step btn click handler */
						option.lOneBtn.onclick = function (event) {
							/* before jumping to next step you must check usr's inputs here */
							that.switchTo(O_REG_TWO);
						}
						option.lTwoBtn.innerHTML = '返回登录';

						option.lTwoBtn.onclick = function (event) {
							that.switchTo(O_LOGIN);
						}
					break;
					case O_REG_TWO:
						$(option.oneInput).addClass('mleft16px');
						$(option.twoInput).addClass('mleft16px');
						$(option.oneInput).attr('placeholder', '请输入机构全称');
						$(option.twoInput).attr('placeholder', '请输入登陆密码');

						option.optionLabel.innerHTML = '同意<a href="#" style="color:white">17约课商家使用条款</a>';
						option.lOneBtn.innerHTML = '下一步';
						/* next step btn click handler */
						option.lOneBtn.onclick = function (event) {
							/* before jumping to next step you must check usr's inputs here */
							that.showInfo(['下一步是什么呢？']);
						}
						option.lTwoBtn.innerHTML = '返回登录';

						option.lTwoBtn.onclick = function (event) {
							that.switchTo(O_LOGIN);
						}
					break;
				}
			},
			/* show the login box with animation and show a hMasker */
			show: function () {
				var that = this;
				/* first we may want to do something to the masker */
				var masker = hMasker.getInstance();
				masker.show();
				masker.click(function (event) { that.close(); });
				/* of course you can close the box */
				option.closeBtn.onclick = function (event) { that.close() };
				option.loginBox.className = 'bounce-down-animate login-wrap';
			},
			/* close the login box with animation and remove the hMasker */
			close: function () {
				option.loginBox.className = 'ease-up-animate login-wrap';	
				setTimeout(function () {
					hMasker.getInstance().hide();
					option.loginBox.className = 'none';
				}, 500);
			},
			/* make sure pass through a string array */
			showInfo: function (strings) {
				option.infoWrap.innerHTML = '';
				for (var i = 0; i < strings.length; i++) {
					var li = document.createElement('li');
					li.innerHTML = strings[i];
					option.infoWrap.appendChild(li);
				};
				$(option.infoWrap).removeClass('none');
			},
			hideInfo: function () {
				$(option.infoWrap).addClass('none');
			}
		};
	};
	return {
		/* constants */
		O_LOGIN: O_LOGIN,
		O_REG_ONE: O_REG_ONE,
		O_REG_TWO: O_REG_TWO,
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