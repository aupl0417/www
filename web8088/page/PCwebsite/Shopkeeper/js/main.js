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
