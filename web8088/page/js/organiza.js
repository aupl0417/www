/*
 * organiza
 *
 * Copyright (c) 2015 xyc
 * Licensed under the MIT license.
 */

$(function(){
	
	
	//定义变量
	var city = "广州市";
	var area;
	var area_id;
	var address;
	var cateid;
	var cate_id;
	var sort;
	var $greet;
	var diqu;
	var $x;
	var $y;

	if(sessionStorage.cate_id==undefined){
		$('#course_constitution span').attr('alt','');
	}else{
		 $('#course_constitution span').attr('alt',sessionStorage.cate_id);
	}
	if(sessionStorage.catename==undefined){
		$('#course_constitution span').text("选择分类");
	}else{
		$('#course_constitution span').text(sessionStorage.catename);
	}
     
	$('#course_constitution').click(function () {
        $('#constitution-img').toggleClass('course_institution_nav_img_active');
        $('#course_constitution').toggleClass("select_color");
        $('.course_constitution_select').toggleClass('course_sort_select_active');
        $('.course-box').toggleClass('course-box-active');
    });
    $('.course_constitution_select_li').click(function (event) {
        $('.course_constitution_select_li').removeClass('first_select_active');
        $(event.target).addClass('first_select_active');
        cate_id=$(event.target).attr('alt');
        $('#course_constitution span').text($(event.target).text());
        $('#course_constitution span').attr('alt',cate_id);
        $('.course_constitution_select').toggleClass('course_sort_select_active');
        $('#course_constitution').removeClass("select_color");
        $('#constitution-img').toggleClass('course_institution_nav_img_active');
        $('.course-box').toggleClass('course-box-active');
        getDate();
    });
    
	/*
	 * 进入到页面时，页面加载获取的当前分类的所有商家信息 
	 */
    
    cate_id = $('#course_constitution span').attr('alt');
     
     function getDate(){
         var args={cate_id:cate_id};
    	 $.post(areasUrl,args,function(msg){
    	 		var tpl = document.getElementById('shopinfo').innerHTML;
    				var html = juicer(tpl, msg); 
    				 $("#shopkeeper").html('');
    				 $("#shopkeeper").append(html);
    	 	},'json');
     }
     getDate();
     
 	
 	 
 	 
	/*课程特色*/
    $("#course_select_feature").change(function (event) {
        $('#course_select_feature_p').text($(event.target).val());
    });
    /*约课模式*/
    $("#course_select_model").change(function (event) {
        $('#course_select_model_p').text($(event.target).val());
    });
    /*价格区间*/
    $("#course_select_price").change(function (event) {
        $('#course_select_price_p').text($(event.target).val());
    });
    /*地区选择栏第一次*/
    $('#course_local').click(function (event) {
        $('.course-box').toggleClass('course-box-active');
        $('#course_local').toggleClass("select_color");
        $('#local-img').toggleClass('course_institution_nav_img_active');
        $('.course_institution_nav_all_img').attr('src',"__HIMG__/icon (1).png");
        /*此处写ajax获取区*/
        $('.course_local_first_select').toggleClass('course_local_first_select_active');
        if(!$('.course_local_first_select').hasClass('course_local_first_select_active')){
            $('.course_local_second_select').addClass('course_local_second_select_hidden')
        }
    });
    $('.course_local_first_select').on('click','li',function (event) {    
    	area = $(event.target).text();
    	area_id=$(event.target).attr('alt');
    	var args={area_id:area_id};
    	$.post(secondUrl,args,function(msg){
    		//获取地址
			var tpl = document.getElementById('second').innerHTML;
			var html = juicer(tpl, msg);
			$("#secondArea").html('');
			$("#secondArea").append(html);    			
    	},'json');
		$(".course_local_first_select_li").removeClass('first_select_active');
        $(event.target).addClass('first_select_active');
        /*此处ajax获取二级地址*/
        $('.course_local_second_select').removeClass('course_local_second_select_hidden');

    });
    $('.course_local_second_select').on('click','li',function () {
        $('#course_local span').text($(event.target).text())
        address=$(event.target).text();
        $('.course_local_second_select').addClass('course_local_second_select_hidden');
        $('.course_local_first_select').toggleClass('course_local_first_select_active');
        $('#course_local').removeClass("select_color");
        $('#local-img').toggleClass('course_institution_nav_img_active');
        $('.course-box').toggleClass('course-box-active');
      /* 返回当前用户选择的一个地区*/
        area_id=$(event.target).attr('alt');
        diqu=area_id;
        cate_id=$('#cateid').attr('alt');
        var args={area_id:area_id,cate_id:cate_id,greet:$greet};
    	$.post(areasUrl,args,function(msg){
    		var tpl = document.getElementById('shopinfo').innerHTML;
    		var html = juicer(tpl, msg); 
    		 $("#shopkeeper").html('');
    		 $("#shopkeeper").append(html);
    	},'json');
        
    });
    /*排序*/
    $('#course_sort').click(function (event) {
        $('#sort-img').toggleClass('course_institution_nav_img_active');
        $('#course_sort').toggleClass("select_color");
        $('.course_sort_select').toggleClass('course_sort_select_active');
        $('.course-box').toggleClass('course-box-active');
    })
    $('.course_sort_select_li').click(function (event) {
        $('.course_sort_select_li').removeClass('first_select_active');
        $(event.target).addClass('first_select_active');
        $('#course_sort span').text($(event.target).text());
        $('.course_sort_select').toggleClass('course_sort_select_active');
        $('#course_sort').removeClass("select_color");
        $('#sort-img').toggleClass('course_institution_nav_img_active');
        $('.course-box').toggleClass('course-box-active');
    });
    $('.wrap-page').scroll( function() {
        var totalheight = parseFloat($('.wrap-page').height()) + parseFloat($('.wrap-page').scrollTop());
        if ($('#wrap').height()-5 <= totalheight)
        {
           /*ajax获取更多数据*/
        }
    });
    
    /*此处ajax动态获取数据库的二级地址*/
    $('.course_all_btn').click(function (event) {
    	var args={c:1}
    	$.post(areaUrl,args,function(msg){
				//获取地址
    			var tpl = document.getElementById('one').innerHTML;
    			var html = juicer(tpl, msg);
    	console.log(msg);
    			$("#area").append(html);
    	},'json');	
    });
    
    $('.course_institution_btn').click(function (event) {
    	var $keywords=$('.register_input').val();
    	var args={keywords:$keywords};
    	$.post(searchUrl,args,function(msg){
				//获取地址
    			var tpl = document.getElementById('shopinfo').innerHTML;
    			var html = juicer(tpl, msg);
    			 $("#shopkeeper").html('');
    			 $("#shopkeeper").append(html);
    	},'json');	
    });
    
    /*点击默认排序的数据*/
    $('#sort').click(function (event) {
    	$sort=$('#sort').attr('alt');
    	area_id=diqu;
          cate_id=$('#cateid').attr('alt');
          var args={area_id:area_id,cate_id:cate_id};
      	$.post(areasUrl,args,function(msg){
      		var tpl = document.getElementById('shopinfo').innerHTML;
      		var html = juicer(tpl, msg); 
      		 $("#shopkeeper").html('');
      		 $("#shopkeeper").append(html);
      	},'json');
  });
    
    /*点击离我最近的数据*/
	$('#nearby').click(function (event) {
		 $nearby=$('#nearby').attr('alt');
		 //GPS定位
		  /*if (navigator.geolocation)
		    {
		    navigator.geolocation.getCurrentPosition(showPosition,showError);
		    }
		  else{
			  alert("Geolocation is not supported by this browser.");
			  }
		  
		function showPosition(position)
		  {			
			$x=position.coords.longitude;
			$y=position.coords.latitude;
		  }
		function showError(error)
		  {
		  switch(error.code) 
		    {
		    case error.PERMISSION_DENIED:
		    	alert("User denied the request for Geolocation.");
		      break;
		    case error.POSITION_UNAVAILABLE:
		    	alert("Location information is unavailable.");
		      break;
		    case error.TIMEOUT:
		    	alert("The request to get user location timed out.");
		      break;
		    case error.UNKNOWN_ERROR:
		    	alert("An unknown error occurred.");
		      break;
		    }
		  }*/
		$x="113.34656056958562";
		$y="23.165189595914597";
  	   area_id=diqu;
  	   cate_id=$('#cateid').attr('alt');
  	   var args={area_id:area_id,cate_id:cate_id,nearby:$nearby,x:$x,y:$y};
  	   $.post(areasUrl,args,function(msg){
  		var tpl = document.getElementById('shopinfo').innerHTML;
 		var html = juicer(tpl, msg); 
 		 $("#shopkeeper").html('');
 		 $("#shopkeeper").append(html);
 	},'json');
  });
	
	/*点击最受欢迎的数据*/
	 $('#greet').click(function (event) {
    	   $greet=$('#greet').attr('alt');
    	   area_id=diqu;
      	   cate_id=$('#cateid').attr('alt');
      	   var args={area_id:area_id,cate_id:cate_id,greet:$greet};
      	   $.post(areasUrl,args,function(msg){
      		var tpl = document.getElementById('shopinfo').innerHTML;
     		var html = juicer(tpl, msg); 
     		 $("#shopkeeper").html('');
     		 $("#shopkeeper").append(html);
     	},'json');
    	
    });
	 
	 /*
	  * 点击某一个商家的信息
	  * 跳转到商家的详细页面（利用sessionStorage发送当前点击的商家ID和分类ID ）
	  */
	 $('#shopkeeper').on('click','.course_institution_item',function (event) {
		 var $sid=$(event.currentTarget).attr('alt');
		 sessionStorage.pagecount=$sid;
		 sessionStorage.cateid=cate_id;
	      	location = busUrl;
		 
  });
	 
	 
	
});