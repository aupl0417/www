(function ($) {
	var bullet = document.getElementById('bullet');
	var bullets = bullet.getElementsByTagName('li');
	var imgWrapper = document.getElementById('img-wrapper');
	var imgWidth = imgWrapper.getElementsByTagName('img')[0].clientWidth;
	$(bullet).on('click', 'li', function (event) {
		var index = 0;
		for (var i = 0; i < bullets.length; i++) {
			if (this == bullets[i]) index = i;
			$(bullets[i]).removeClass('active');
		};
		$(bullets[index]).addClass('active');
		$(imgWrapper).animate({'left': - imgWidth * index + 'px'});
	});

	var tabController = new TabController({
		menuId: 'c-menu',
		itemsWrapperId: 'content-cen',
		itemClass: 'info-box',
		eventName: 'mouseover'
	});

	var noticeTip = document.getElementById('noticetip');
	var chatOnline = document.getElementById('chat-online');
	if(chatOnline)chatOnline.onmouseover = function (event) {$(noticeTip).removeClass('hidden');}
	if(chatOnline)chatOnline.onmouseout = function (event) {$(noticeTip).addClass('hidden');}
})($);