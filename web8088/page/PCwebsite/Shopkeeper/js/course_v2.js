(function ($) {
	var bullet = document.getElementById('bullet');
	var bullets = bullet.getElementsByTagName('li');
	var imgWrapper = document.getElementById('img-wrapper');
	var imgWidth = imgWrapper.getElementsByTagName('img')[0].clientWidth;
	$(bullet).on('click', 'li', function (event) {
		var index = 0;
		for (var i = 0; i < bullets.length; i++) {
			if (this == bullets[i]) {
				index = i;
				break;
			}
		};
		$(imgWrapper).animate({'left': - imgWidth * index + 'px'});
	});

	var cMenu = document.getElementById('c-menu');
	var cMenuLis = cMenu.getElementsByTagName('li');
	var contentCen = document.getElementById('content-cen');
	var $infoBoxes = $(contentCen).find('.info-box');
	console.log($infoBoxes);
	$(cMenu).on('mouseover', 'li', function (event) {
		var index = 0;
		for (var i = 0; i < cMenuLis.length; i++) {
			if (this == cMenuLis[i]) index = i;
			$(cMenuLis[i]).removeClass('active');
		};
		$(this).addClass('active');
		// show the corresponding infobox
		for (var i = 0; i < $infoBoxes.length; i++) {
			$($infoBoxes[i]).addClass('hidden');
		}
		
		$($infoBoxes[index]).removeClass('hidden');
	});
})($);