/*
 * feature
 *
 * Copyright (c) 2015 xyc
 * Licensed under the MIT license.
 */

$(function(){

	 $('.course_select_ul').on('click','li',function (event) {
		
	       
	        if($(event.target).hasClass("course_select_ul_active")){
	            $(event.target).removeClass("course_select_ul_active");
	            
	        }
	        else if($('.course_select_ul_active').length==3){
	            alert("最多选择3个")
	          
	        }
	        else{
	            $(event.target).addClass("course_select_ul_active");
	           
	        }
	       
	   
	    });
	 
	
	    $('.course_feature_btn').click(function (event) {
	        var value = $('.register_input').val();
	        if(value==""){
	            alert("内容不能为空")
	        }
	        else if(value.length>4){
	            alert("长度不能大于4")
	        }
	        else {
	            $('.course_select_ul').append('<li>' + value + '</li>');
	        }

	    });
	    
	    
	    $('#button').click(function (event) {
	    	var tags='';
	    	$('.course_select_ul_active').each(function(i, item){
	    		tags=$(item).text()+'|'+tags;
	    	});
	    	tags=tags.substring(0,tags.length-1);
	    	sessionStorage.tags=tags;
	      	location = relUrl;	
		  });
	   
});