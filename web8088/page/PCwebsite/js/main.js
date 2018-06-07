(function ($) {
	
$('div.course-content').hover(function (e) {
	/*$(e.currentTargrt).find('.course-brief').css('height','60px').css('opacity','1');*/	
	var temp = $(e.currentTarget).next();
	temp.css('height',"50px").css('opacity',"1");
},
function (e) {
	var temp = $(e.currentTarget).next();
	temp.css('height',"0px").css('opacity',"0");
}

);

$('.btn-group').hover(
	function (e){
 	$(e.currentTarget).find('ul').show();
 	$(e.currentTarget).find('.caret').css('transform','rotate(180deg)');
},function (e) {
	$(e.currentTarget).find('ul').hide();
	$(e.currentTarget).find('.caret').css('transform','rotate(0deg)');
}
);

$('.menu_box').hover(function (e) {
	
	$(e.currentTarget).find('.menu_sub').addClass('show');
},function (e) {
	
	$(e.currentTarget).find('.menu_sub').removeClass('show');
}
);

$('.menu_sub').hover(function (e) {
	$(e.target).prev().addClass('current');
},function  (e) {
	$(e.target).prev().removeClass('current');
}
);

$("#new-pre").click(function (e) {
	
}
);
$("#new-next").click(function (e) {
	
}
);

})(jQuery);

var FormValidator = {
	isPhone: function (str) {return /^1[3|4|5|7|8][0-9]\d{8}$/.test(str);},
	isEmail: function (str) {return /^[\w-]+(\.[\w-]+)*@(\w)+(\.\w+)+$/.test(str);},
	isPasswd: function (str) {return /^\S{6,12}$/.test(str);}
};

/**
 * Masker
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
 */
var boxController = (function () {
	var instance;
	var LOGIN = 0;
	var REG_ONE = 1;
	var REG_ONE_U = 2;
	var REG_ONE_O = 1;
	var REG_ONE_T = 0;
	var REG_TWO = 2;
	var FORGET_ONE = 3;
	var FORGET_TWO = 4;

	var regSelIndex = 0;

	var currBox;
	var boxes = [];
	function init () {
		/* close the login box with animation and remove the hMasker */
		function closeBox () {
			if (!currBox) return;
			currBox[0].className = 'ease-up-animate login-wrap';
			setTimeout(function () {
				hMasker.getInstance().hide();
				currBox[0].className = 'hidden';
			}, 500);
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
		/*boxes[LOGIN].twoBtn = $('#l-two-btn');*/
		boxes[LOGIN].oneInput[0].onfocus = function (event) {boxes[LOGIN].oneClr.removeClass('op-0');boxes[LOGIN].infoWrap.addClass('hidden');};
		boxes[LOGIN].oneInput[0].onblur = function (event) {boxes[LOGIN].oneClr.addClass('op-0');};
		boxes[LOGIN].twoInput[0].onfocus = function (event) {boxes[LOGIN].twoClr.removeClass('op-0');boxes[LOGIN].infoWrap.addClass('hidden');};
		boxes[LOGIN].twoInput[0].onblur = function (event) {boxes[LOGIN].twoClr.addClass('op-0');};
		boxes[LOGIN].oneClr[0].onclick = function (event) {boxes[LOGIN].oneInput[0].value='';};
		boxes[LOGIN].twoClr[0].onclick = function (event) {boxes[LOGIN].twoInput[0].value='';};

		boxes[REG_ONE] = $('#reg-one-box');
		boxes[REG_ONE].regSel = $('#reg-sel');
		boxes[REG_ONE].oneInput = $('#ro-one-input');
		boxes[REG_ONE].twoInput = $('#ro-two-input');
		boxes[REG_ONE].threeInput = $('#ro-three-input');
		boxes[REG_ONE].oneClr = $('#ro-u-clr');
		boxes[REG_ONE].twoClr = $('#ro-c-clr');
		boxes[REG_ONE].threeClr = $('#ro-ic-clr');
		boxes[REG_ONE].codeBtn = $('#code-btn');
		boxes[REG_ONE].checkBox = $('#ro-agree');
		boxes[REG_ONE].infoWrap = $('#ro-info-wrap');
		boxes[REG_ONE].oneBtn = $('#ro-one-btn');
		/*boxes[REG_ONE].twoBtn = $('#ro-two-btn');*/
		boxes[REG_ONE].regSel.on('click', 'a', function (event) {
			boxes[REG_ONE].regSel.find('a').each(function () {$(this).removeClass('active');});
			$(this).addClass('active');
			/* BAD design */
			if (this.innerHTML.indexOf('用户') != -1) regSelIndex = REG_ONE_U;
			else if (this.innerHTML.indexOf('老师') != -1) regSelIndex = REG_ONE_T;
			else if (this.innerHTML.indexOf('机构') != -1) regSelIndex = REG_ONE_O;
		});
		boxes[REG_ONE].oneInput[0].onfocus = function (event) {boxes[REG_ONE].oneClr.removeClass('op-0');boxes[REG_ONE].infoWrap.addClass('hidden');};
		boxes[REG_ONE].oneInput[0].onblur = function (event) {boxes[REG_ONE].oneClr.addClass('op-0');};
		boxes[REG_ONE].twoInput[0].onfocus = function (event) {boxes[REG_ONE].twoClr.removeClass('op-0');boxes[REG_ONE].infoWrap.addClass('hidden');};
		boxes[REG_ONE].twoInput[0].onblur = function (event) {boxes[REG_ONE].twoClr.addClass('op-0');};
		boxes[REG_ONE].threeInput[0].onfocus = function (event) {boxes[REG_ONE].threeClr.removeClass('op-0');boxes[REG_ONE].infoWrap.addClass('hidden');};
		boxes[REG_ONE].threeInput[0].onblur = function (event) {boxes[REG_ONE].threeClr.addClass('op-0');};
		boxes[REG_ONE].oneClr[0].onclick = function (event) {boxes[REG_ONE].oneInput[0].value='';};
		boxes[REG_ONE].twoClr[0].onclick = function (event) {boxes[REG_ONE].twoInput[0].value='';};
		boxes[REG_ONE].threeClr[0].onclick = function (event) {boxes[REG_ONE].threeInput[0].value='';};

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
		/*boxes[REG_TWO].twoBtn = $('#rt-two-btn');*/
		boxes[REG_TWO].oneInput[0].onfocus = function (event) {boxes[REG_TWO].oneClr.removeClass('op-0');boxes[REG_TWO].infoWrap.addClass('hidden');};
		boxes[REG_TWO].oneInput[0].onblur = function (event) {boxes[REG_TWO].oneClr.addClass('op-0');};
		boxes[REG_TWO].twoInput[0].onfocus = function (event) {boxes[REG_TWO].twoClr.removeClass('op-0');boxes[REG_TWO].infoWrap.addClass('hidden');};
		boxes[REG_TWO].twoInput[0].onblur = function (event) {boxes[REG_TWO].twoClr.addClass('op-0');};
		boxes[REG_TWO].threeInput[0].onfocus = function (event) {boxes[REG_TWO].threeClr.removeClass('op-0');boxes[REG_TWO].infoWrap.addClass('hidden');};
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
		boxes[FORGET_ONE].oneInput[0].onfocus = function (event) {boxes[FORGET_ONE].oneClr.removeClass('op-0');boxes[FORGET_ONE].infoWrap.addClass('hidden');};
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
		boxes[FORGET_TWO].oneInput[0].onfocus = function (event) {boxes[FORGET_TWO].oneClr.removeClass('op-0');boxes[FORGET_TWO].infoWrap.addClass('hidden');};
		boxes[FORGET_TWO].oneInput[0].onblur = function (event) {boxes[FORGET_TWO].oneClr.addClass('op-0');};
		boxes[FORGET_TWO].twoInput[0].onfocus = function (event) {boxes[FORGET_TWO].twoClr.removeClass('op-0');boxes[FORGET_TWO].infoWrap.addClass('hidden');};
		boxes[FORGET_TWO].twoInput[0].onblur = function (event) {boxes[FORGET_TWO].twoClr.addClass('op-0');};
		boxes[FORGET_TWO].threeInput[0].onfocus = function (event) {boxes[FORGET_TWO].threeClr.removeClass('op-0');boxes[FORGET_TWO].infoWrap.addClass('hidden');};
		boxes[FORGET_TWO].threeInput[0].onblur = function (event) {boxes[FORGET_TWO].threeClr.addClass('op-0');};
		boxes[FORGET_TWO].oneClr[0].onclick = function (event) {boxes[FORGET_TWO].oneInput[0].value='';};
		boxes[FORGET_TWO].twoClr[0].onclick = function (event) {boxes[FORGET_TWO].twoInput[0].value='';};
		boxes[FORGET_TWO].threeClr[0].onclick = function (event) {boxes[FORGET_TWO].threeInput[0].value='';};
		return {
			/* because the box may change its apparence while user clicking the buttons, so I provide this functional method for you guys to rock and roll */
			switchTo: function (index, regSwitchIndex) {
				var that = this;
				if (currBox) {currBox.addClass('hidden')};
				currBox = boxes[index];
				currBox[0].className = 'login-wrap';
				switch(index){
					case LOGIN:
					boxes[LOGIN].oneBtn[0].onclick = function (event) {
                        var account = $.trim(boxes[LOGIN].oneInput.val());
                        var pass = boxes[LOGIN].twoInput.val();
                        var isRemenber = boxes[LOGIN].checkBox.is(":checked");

                        var type='';
                        if(window.FormValidator.isPhone(account)){
                            type = 'phone';
                        }else if( window.FormValidator.isEmail(account)){
                            type = 'email';
                        }

					}
					/*boxes[LOGIN].twoBtn[0].onclick = function (event) {
						that.switchTo(REG_ONE);
					}*/
					boxes[LOGIN].forgetPass[0].onclick = function (event) {
						that.switchTo(FORGET_ONE);
					}
					break;
					case REG_ONE:
					if (regSwitchIndex >= 0 && regSwitchIndex <= 2) boxes[REG_ONE].regSel.find('a').get(regSwitchIndex).click();
					boxes[REG_ONE].codeBtn[0].onclick = function (event) {
                        
					};
					boxes[REG_ONE].oneBtn[0].onclick = function (event) {
                        that.switchTo(REG_TWO);
					};
					/*boxes[REG_ONE].twoBtn[0].onclick = function (event) {
                        that.switchTo(LOGIN);
					};*/
					break;
					case REG_TWO:
					if (regSelIndex == REG_ONE_U) boxes[REG_TWO].oneInput[0].setAttribute('placeholder', '请输入昵称');
					else if (regSelIndex == REG_ONE_T) boxes[REG_TWO].oneInput[0].setAttribute('placeholder', '请输入老师昵称');
					else if (regSelIndex == REG_ONE_O) boxes[REG_TWO].oneInput[0].setAttribute('placeholder', '请输入机构全称');
					boxes[REG_TWO].oneBtn[0].onclick = function (event) {
                        
					}
					/*boxes[REG_TWO].twoBtn[0].onclick = function (event) {
						that.switchTo(LOGIN);
					}*/
					break;
					case FORGET_ONE:
					boxes[FORGET_ONE].oneBtn[0].onclick = function (event) {
                        that.switchTo(FORGET_TWO);
					};
					/*boxes[FORGET_ONE].twoBtn[0].onclick = function (event) {
						that.switchTo(LOGIN);
					};*/
					break;
					case FORGET_TWO:
					boxes[FORGET_TWO].oneBtn[0].onclick = function (event) {
                        
					}
					/*boxes[FORGET_TWO].twoBtn[0].onclick = function (event) {
						that.switchTo(LOGIN);
					}*/
					break;
				}
			},
			/* make sure pass through a string array */
			showInfo: function (strings) {
				if (typeof strings === 'string') strings = [strings];
				currBox.infoWrap.html('');
				for (var i = 0; i < strings.length; i++) {
					var li = document.createElement('li');
					li.innerHTML = strings[i];
					currBox.infoWrap.append(li);
				};
				currBox.infoWrap.removeClass('hidden');
			},
			hideInfo: function () {
				currBox.infoWrap.addClass('hidden');
			},
			/* show the specified box with animation and show a hMasker */
			show: function (index, regSwitchIndex) {
				var that = this;
				index = typeof index === 'number' ? index : LOGIN;
				/* first we may want to do something to the masker */
				var masker = hMasker.getInstance();
				masker.show();
				masker.click(that.close);
				currBox = boxes[index];
				this.switchTo(index, regSwitchIndex);
				currBox.addClass('bounce-down-animate');
			},
			/* close the login box with animation and remove the hMasker */
			close: closeBox
		};
	};
	return {
		LOGIN: LOGIN,
		REG_ONE: REG_ONE,
		REG_ONE_U: REG_ONE_U,
		REG_ONE_O: REG_ONE_O,
		REG_ONE_T: REG_ONE_T,
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

/* fire in the hole */
var orgRegBtn = document.getElementById('org-reg-btn');
var loginBtn = document.getElementById('login-btn');
var regBtn = document.getElementById('reg-btn');
var boxInstance = boxController.getInstance();
orgRegBtn.onclick = function (event) {
	boxInstance.show(boxController.REG_ONE, boxController.REG_ONE_O);
}
loginBtn.onclick = function (event) {
	boxInstance.show();
}
regBtn.onclick = function (event) {
	boxInstance.show(boxController.REG_ONE, boxController.REG_ONE_U);
}