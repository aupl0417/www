	/*
 * release
 *
 * Copyright (c) 2015 xyc
 * Licensed under the MIT license.
 */

$(function(){
	var cate_id="";
    (function() {
        var myScroll;
        myScroll = new IScroll('#wrapper', { mouseWheel: true ,click:true});

        window.addEventListener('DOMContentLoaded', function(){
            var $slide = document.getElementById('swipeSlide'),
                    aBullet = $slide.querySelectorAll('.icon-bullet'),
                    len = aBullet.length;

            var mySwipe = new Swipe($slide,{
                continuous: false,
                stopPropagation: true,
                callback: function(i){
                    if(i>=0||i<len) {
                        $('.icon-bullet').removeClass('active')
                        aBullet[i].classList.add('active');
                    }
                }
            });
        });
      
    })();
    sessionStorage.catename = $(".course_active").find("p").text();
	sessionStorage.cate_id=$(".course_active").find("img").attr('alt');
  document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);
    $('.course_slide').on('click','li',function (event) {
        if($(event.target).attr('src')!=null) {
        	cate_id = $(event.target).attr("alt");
        	sessionStorage.cate_id = cate_id;
        	sessionStorage.catename=$(event.target).next().text();
            var old_url = $('.course_active img').attr('src');
            var alt_url = old_url.split('a.')[0] + '.png';
            $('.course_active img').attr('src', alt_url);
            $('.course_active').removeClass('course_active');
            if (!$(event.target).hasClass('course_active')) {
                var url = $(event.target).attr('src');
                var new_url = url.split('.')[0] + 'a.png';
                $(event.target).attr('src', new_url);
                $(event.target).parent().addClass('course_active');
            }
        }
    });
    var $sid=sessionStorage.sid;
	var $cateid=sessionStorage.cateid;
	var $nickname=sessionStorage.nickname;
	var $tags=sessionStorage.tags;
	var $area_id=sessionStorage.area_id;
	
	var $title;
	var $price;
	var $model;
	var $content;

	if($nickname===undefined){
		$('#local').html("请选择");
	}else{
		$('#local').html($nickname);
    }
		$('#course_select_price').bind('change',function(){
  		$price=$('#course_select_price').val();
  		sessionStorage.price=$price;
  		$('#price').html($price);
     });   

      $('#course_select_model').bind('change',function(){
    	  $model=$('#course_select_model').val();
    	  sessionStorage.model=$model;
    	  if($model==0){
    		  $model="星期六天";
    	  }
    	  $('#model').html($model);
       });   
      
      if($tags===undefined){
    	  $('#feature').html("请选择");
  	}else{
  	    $('#feature').html($tags);
    }
   
    /*用户提交 发布心愿的数据*/
     $('#wrap_btn').on("touchstart",function (event) {
       	console.log(event.target)

      	 $title=($('.register_input').val());      /*填写的组课的标题 */
      	 var reg = eval('/^.{5,11}$/gi');
      	 flg=reg.test($title);
      	 if(!flg){
   			alert('组团标题字数范围为5至11个!');
   			return false;
   		}
    	$content=($('#course_textarea').val());  /*填写的组课的课程内容 */
     	 if($content==''){
     	  	 alert('组团课程内容不可以为空!');
     	  	return false;
     	 }
     	$model=sessionStorage.model;
     	$price=sessionStorage.price;
    	alert($sid);
    	alert($cateid);
    	alert($area_id);
    	alert($nickname);
    	alert($tags);
  		alert($title);
  		alert($model);
  		alert($price);
  		alert($content);
  		args={sid:$sid,cateid:$cateid,areaid:$area_id,nickname:$nickname,
  				tags:$tags,title:$title,model:$model,price:$price,content:$content};
  	   $.post(insertUrl,args,function(msg){
  		  if(msg==true){alert('发布成功');}
  	 	},'json');
  	    });
});

