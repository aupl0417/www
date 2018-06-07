(function ($) {
	var bullet = document.getElementById('bullet');
	var bullets = bullet.getElementsByTagName('li');
	var imgWrapper = document.getElementById('img-wrapper');
	var imgWidth = imgWrapper.getElementsByTagName('img')[0].clientWidth;
    /* picture carousel */
	$(bullet).on('click', 'li', function (event) {
		var index = 0;
		for (var i = 0; i < bullets.length; i++) {
			if (this == bullets[i]) index = i;
			$(bullets[i]).removeClass('active');
		};
		$(bullets[index]).addClass('active');
		$(imgWrapper).animate({'left': - imgWidth * index + 'px'});
	});
    /* according to the PM, this tab handle `mouseover` event */
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

//    // comment tools fold and open
//    var formValidator = new FormValidator();
//    var $commentPanel = $('#commentPanel');
//    $commentPanel.on('click', 'a', function(){
//        // reply
//        if($(this).hasClass('cmt-reply')){
//            $(this).parent().next().removeClass('hidden');
//        }
//        // delete comment
//        else if($(this).hasClass('cmt-delete')){
//            prompt('Are you sure to delete this comment?');
//        }
//    });
//    $commentPanel.on('click', 'button', function(){
//        // reply
//        if($(this).hasClass('cmt-reply-btn')){
//            $(this).parent().addClass('hidden');
//            if(!formValidator.isBlank($(this).prev().val())) alert('You just said that ' + $(this).prev().val());
//            else alert('Oh, guy you did not leave anything here');
//
//        }
//    });

    // click the heart icon to favorite the course/wish-list
    $('#heart').click(function(e){
        if(!$(this).hasClass('heart-active')) {
            $(this).addClass('heart-active');
            alert('You indeed love this course or wish list!');
        }else{
            $(this).removeClass('heart-active');
            alert('I am very sad to see that you canceled the choice just now.');
        }
    });
})($);