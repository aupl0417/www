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
	var pages=0;
	
	var args={sid:$sid};
	 
	   function showLoadCom(){
	    	$('.business_relay_more').removeClass("hidden");
	    }
	    function hiddenLoadCom(){
	        $('.business_relay_more').addClass("hidden");
	    }
	
	/*
	 * 发送AJAX数据获取选取的商家信息 
	 */
	 $.post(busajaxUrl,args,function(msg){
		 $area_id=msg.areaid;
		  nickname=msg.nickname;
         var url = $("#photo_link").attr("href");
         url = url.split(".")[0];
         url = url + "?id="+$sid;
         var url = $("#photo_link").attr("href",url);
         console.log(url);
		  if(msg.avatar!=''){
			  $img=$('#avatar').attr('path');
			  $('#avatar').attr('src',$img+msg.avatar);
		  }
		 	if(msg.nickname!=''){
				$('.business_info h3').html(msg.nickname);
				$('title').html(msg.nickname+ " - 17约课");
			}else{
				$('title').html("机构详细 - 17约课");
				$('.business_info h3').html('暂无');
			}
			if(msg.remark!=''){
				$('.business_info p').html(msg.remark);
			}else{
				$('.business_info p').html('暂无');
			}
			if(msg.login_phone!=''){
                $("#phone_a").attr("href","tel:"+msg.login_phone);
				$('#phone_info').html(msg.login_phone);
            }else{
				$('#phone_info').html('暂无');
			}
			if(msg.area_detail!=''){
				$('#add_info').html(msg.area_detail);
			}else{
				$('#add_info').html('暂无');
			}
   	},'json');
	 

	   /*
		* 发送AJAX数据获取选取的该商家的评论内容
		*/

	 /*
	  * 点击事件（评论）
	  * 利用ajax发送评论的内容和当前的商家ID
	  */
	 $('.business_reply_btn').tap(function (event) {
		 $content=$('.register_input').val();
         if($content!=null&&$content!="") {
             args = {sid: $sid, content: $content};
             $.post(commUrl, args, function (msg) {
                 if (msg) {
                     location.href = busUrl;
                 }
             }, 'text');
         }
         else{
             easyAlert('评论内容不能为空');
         }


	    });
	  
	 
	//回复-点击触发
	 var answer_p=0;
	 var answer_d=0;
	 $("#business").on("click",".comment_rep_b",function(e) {
	          e.preventDefault();
	          var flat = 0;//判断是不是已经展开回复；
	 	      var currentClick = $(e.target);
	          var curInput = currentClick.parent(".business_reply_item").next();
	          if(curInput.hasClass("hidden")){
	    	 
	         $(".comment_rep_wrap").addClass("hidden");
	         curInput.removeClass("hidden");

	         answer_p=currentClick.parent(".business_reply_item").find(".now_com_id").text();
	         answer_d=currentClick.parent(".business_reply_item").find(".now_com_depth").text();

	         var placeholder_user=currentClick.parent(".business_reply_item").find(".name_comment_1").text();
	         currentClick.parent(".business_reply_item").next().find(".register_input").attr("placeholder","回复"+placeholder_user+":");
	     }else{
	         curInput.addClass("hidden");

	     }
	 	console.log(currentClick.parent(".business_reply_item").next().find(".register_input"));
	 /* 	console.log(answer_p);//上一级的评论的id
	 	console.log(answer_d);//上一级的评论深度 */
	 });
	 
	 
	 

	//回复-ajax请求
	$("#business").on("tap",".business_reply_btn",function(e){
		var answer_info=$(e.target).parents(".comment_rep_wrap").find(".register_input").val();
	    e.preventDefault();
	    if(answer_info==''){
	        share.setText("评论不能为空");
	        share.showhare();
	        share.hiddenShare();
		}else{
		    var datas={};//'"gid":gid,"id":id,"c_info":com,"depth":answer_d,  "pid":answer_p;
			datas.sid = $sid;
			datas.content=answer_info;
			datas.parent_id =answer_p;
			datas.depth =answer_d;
			console.log(datas);
		    $.post(commUrl,datas,function(data){
				console.log(data);
				if(data.status=="200"){
					location.href = busUrl;
				}else{
					alert(data.comment);
		            share.setText(data.comment);
		            share.showhare();
		            share.hiddenShare();
				}
			}, "json");
		 }
	});
	//删除
	$("#business").on("click",".comment_delete_b",function(e) {
	    e.preventDefault();
		if (!confirm("确认要删除？")) {
		    window.event.returnValue = false;
		}else{
			var delClick = $(e.target);
		 	var delcomid=delClick.parent(".business_reply_item").find(".now_com_id").text();
		 	var datas={};
		 	datas.comid = delcomid;
		 	datas.sid   = $sid;
		 	$.post(commDel,datas,function(data){
				
				if(data.status!="200"){
		            share.setText("请先登录");
		            share.showhare();
		            share.hiddenShare();
	                setTimeout(function(){window.location.href='{:U("Index/loginregister")}'},800);
				}else{
					$("#delcom"+delcomid).remove();
				}
			}, "json");
		}
	});
	 
	 
	 function getcomm(){
		 if($(".business_reply_item").size()==0){
		hiddenLoadCom();
		 }
			pages=pages+1;
			args={pages:pages,sid:$sid};
				 $.post(comajaxUrl,args,function(msg){
					 console.log(msg);
					 var tpl = document.getElementById('comment').innerHTML;			
						var html = juicer(tpl, msg.info);	
						if(msg.info==null){
							
							if($(".business_reply_item").size()!=0){
								easyAlert('加载完毕');
								hiddenLoadCom();
		    				}else{
		    					
		    					hiddenLoadCom();
		    				}
						}else{
							showLoadCom();
						 $("#business").append(html);
                            $(".business_reply_item").last().find(".business_reply_info p").addClass("border_none");
						}
				},'json');
	 }
	 getcomm();
	 $('.business_relay_more').tap(function (event) {
		 getcomm();
	    });
	
	 /*
	  * 点击事件（保存用户选择该商家的ID、简称和分类）
	  */
	 $('.business_set').click(function (event) {
		
		 sessionStorage.sid=$sid;
		 sessionStorage.cateid=$cateid;
		 sessionStorage.nickname=nickname;
		 sessionStorage.area_id=$area_id;
         easyAlert('选择成功');
         setTimeout(function(){
             location.href = relUrl;
         },1000);
	  });
});