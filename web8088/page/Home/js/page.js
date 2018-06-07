/**
 * index page
 */
function ShowController () {
	this._timer = null;
	this._$sections = $('#fullpage .section');
	this.currIndex = window.index;	
}
ShowController.prototype.play = function (delay) {
	if (this._timer === null) {
		var that = this;
		this._timer = setTimeout(function () {
			if (window.index == that._$sections.length) $.fn.fullpage.moveTo(1);
			else $.fn.fullpage.moveSectionDown();
		}, delay);
	}
}
ShowController.prototype.stop = function () {
	if (this._timer) {
		clearTimeout(this._timer);
		this._timer = null;
	}
}
var showController = new ShowController();
window.onload = function () {
	var fpNav = document.getElementById('fp-nav');
	if (!fpNav) return;
	showController.play(15000);
	fpNav.onmouseover = function (event) {
		showController.stop();
	}
	fpNav.onmouseout = function (event) {
		showController.play(10000);
	}
}

$(document).ready(function() {
	if (!document.getElementById('fullpage')) return;
	
    $('#fullpage').fullpage({
    	anchors: ['first', 'second', 'third', 'fourth', 'fifth'],
		sectionsColor: ['#2C9ACF', '#2C9ACF', '#2C9ACF', '#2C9ACF', '#2C9ACF'],
		navigation: true,
		navigationPosition: 'right',
		afterLoad: function (anchorLink, index) {
			window.index = index;
			switch(index) {
				case 1:
				document.getElementById('text-2').className = 'hidden';
				document.getElementById('map-2').className = 'hidden';
				document.getElementById('mascot-2').className = 'hidden';
				break;
				case 2:
				document.getElementById('text-2').className = 'text-2-in';
				document.getElementById('map-2').className = 'map-2-in';
				document.getElementById('mascot-2').className = 'mascot-2-in';

				document.getElementById('text-3').className = 'hidden';
				document.getElementById('phone-3').className = 'hidden';
				document.getElementById('mascot-3').className = 'hidden';
				break;
				case 3:
				document.getElementById('text-2').className = 'hidden';
				document.getElementById('map-2').className = 'hidden';
				document.getElementById('mascot-2').className = 'hidden';

				document.getElementById('text-3').className = 'text-3-in';
				document.getElementById('phone-3').className = 'phone-3-in';
				document.getElementById('mascot-3').className = 'mascot-3-in';

				document.getElementById('text-4').className = 'hidden';
				document.getElementById('phone-4').className = 'hidden';
				document.getElementById('mascot-4').className = 'hidden';
				break;
				case 4:
				document.getElementById('text-3').className = 'hidden';
				document.getElementById('phone-3').className = 'hidden';
				document.getElementById('mascot-3').className = 'hidden';

				document.getElementById('text-4').className = 'text-4-in';
				document.getElementById('phone-4').className = 'phone-4-in';
				document.getElementById('mascot-4').className = 'mascot-4-in';

				document.getElementById('text-5').className = 'hidden';
				document.getElementById('phone-5').className = 'hidden';
				document.getElementById('mascot-5').className = 'hidden';
				break;
				case 5:
				document.getElementById('text-4').className = 'hidden';
				document.getElementById('phone-4').className = 'hidden';
				document.getElementById('mascot-4').className = 'hidden';

				document.getElementById('text-5').className = 'text-5-in';
				document.getElementById('phone-5').className = 'phone-5-in';
				document.getElementById('mascot-5').className = 'mascot-5-in';
				break;
			}
			window.showController.stop();
			window.showController.play(10000);
		}
    });
});

var myLoginBox = loginBox.getInstance();
myLoginBox.switchTo(loginBox.O_LOGIN);
document.getElementById('s-login-btn').onclick = function (event) { myLoginBox.show(); };

/* other page */
(function () {
	var poTabController = new TabController({
		menuId: 'po-menu',
		itemsWrapperId: 'po-item-wrap',
		itemClass: 'section'
	});
	var prTabController = new TabController({
		menuId: 'pr-menu',
		itemsWrapperId: 'pr-item-wrap',
		itemClass: 'pr-item'
	});
})();