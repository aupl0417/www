/*
 * business
 *
 * Copyright (c) 2015 xyc
 * Licensed under the MIT license.
 */

$(function(){
	//定义变量
	var $sid=sessionStorage.pagecount;
	var $cateid=sessionStorage.cateid;
	var $area_id;
	var $content;
	var nickname;
	
	var args={sid:$sid};
	
	/*
	 * 发送AJAX数据获取选取的商家信息 
	 */
	 $.post(busajaxUrl,args,function(msg){
		 $area_id=msg.areaid;
		  nickname=msg.nickname;
		 	if(msg.nickname!=''){
				$('.business_info h3').html(msg.nickname);
			}else{
				$('.business_info h3').html('暂无');
			}
			if(msg.remark!=''){
				$('.business_info p').html(msg.remark);
			}else{
				$('.business_info p').html('暂无');
			}
			if(msg.login_phone!=''){
				$('#phone img').after(msg.login_phone);
			}else{
				$('#phone img').after('暂无');
			}
			if(msg.area_detail!=''){
				$('#addr img').after(msg.area_detail);
			}else{
				$('#addr img').after('暂无');
			}
   	},'json');
	 

	   /*
		* 发送AJAX数据获取选取的该商家的评论内容
		*/
	 $.post(comajaxUrl,args,function(msg){
		 var tpl = document.getElementById('comment').innerHTML;
			var html = juicer(tpl, msg);			 
			 $("#business").append(html);
	},'json');
	 
	 /*
	  * 点击事件（评论）
	  * 利用ajax发送评论的内容和当前的商家ID
	  */
	 $('.business_reply_btn').click(function (event) {
		 $content=$('.register_input').val();
		 args={sid:$sid,content:$content};
		 $.post(commUrl,args,function(msg){
			if(msg){location =busUrl;}		 
		},'text');
	    });
	 
	 /*
	  * 点击事件（保存用户选择该商家的ID、简称和分类）
	  */
	 $('.business_set').click(function (event) {
		 sessionStorage.sid=$sid;
		 sessionStorage.cateid=$cateid;
		 sessionStorage.nickname=nickname;
		 sessionStorage.area_id=$area_id;
	      	location = relUrl;		 
	  });
});