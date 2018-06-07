/*
 * business
 *
 * Copyright (c) 2015 xyc
 * Licensed under the MIT license.
 */

$(function(){
    
    /**
     * 特技Input框
     */
    $("input").on("focus", function (event) {
        $(event.target).next().removeClass("op_0");

    });
    $("input").on("blur", function (event) {
        $(event.target).next().addClass("op_0");

    });
    $(".input_close_icon").click(function (event) {
        $(event.target).prev().val("");
    });
	var  area_id;
	var  streetname='';
	var  areaname='';
	var catename='';
	var avatar='';  
	var checkSubmitFlg = false;
	        var args={};
		 	$.post(cateAllUrl,args,function(msg){
		 		console.log(msg);
		 		//获取地址
				var tpl = document.getElementById('catename').innerHTML;
					var html = juicer(tpl, msg);
		//			$("#third_new_select").html('');
					$("#third_new_select").append(html); 		 		
		 	},'json');    	
	    	 	 	
	/**
     * 选择文件
     */
        $('#image').on("click",function (event) {
            $('#file').trigger("click");
        })
    /**
     *
     */
      
	 		 var args={};
	     	$.post(areaUrl,args,function(msg){
	 				//获取地址
	     			var tpl = document.getElementById('one').innerHTML;
	     			var html = juicer(tpl, msg);
	     			$("#first_new_select").append(html);
	     	},'json');	
	     	
    $('#first_new_select').on("change", function (event) {
        $('#first_new_p').text($('#first_new_select option').not(function(){
        	return !this.selected;
        })[0].innerHTML);
        areaname=$('#first_new_select option').not(function(){
        	return !this.selected;
        })[0].innerHTML;
          area_id= $('#first_new_select').val();
    var args={area_id:area_id};
    $.post(secondUrl,args,function(msg){
		//获取地址
		var tpl = document.getElementById('second').innerHTML;
		var html = juicer(tpl, msg);
		$("#second_new_select").html('');
		$("#second_new_select").append(html);    			
	},'json');
    $('#second_new_p').text('请选择');
    streetname='';
    })
$('#second_new_select').on("change", function (event) {
    	$('#second_new_p').text($('#second_new_select option').not(function(){
        	return !this.selected;
        })[0].innerHTML);
    	streetname=$('#second_new_select option').not(function(){
        	return !this.selected;
        })[0].innerHTML;
    	
        area_id= $('#second_new_select').val();
    });
   /* $('#third_new_select').on("change", function (event) {
    	$('#third_new_p').text($('#third_new_select option').not(function(){
        	return !this.selected;
        })[0].innerHTML);
    	catename=$('#third_new_select option').not(function(){
        	return !this.selected;
        })[0].innerHTML;
    	cate_id= $('#third_new_select').val();
    	console.log(cate_id);
    });*/
    
    
    

    $("#third_new_select").on("click",".shopkeeper_type_item",function(e){
        var item_first=$(e.currentTarget);
        var array=item_first.children().eq(0).children().eq(0);
        changeArray(array);
        var slb=item_first.next();
        if($(slb).hasClass("hidden")){
            $(slb).removeClass("hidden");
        }else{
            $(slb).addClass("hidden");
        }
    });
    
    $('#third_new_select').on("click","input",function(e){
    	var item=$(e.currentTarget).next();
    	cate_id = item.next().val();
    	catename = item.html();
    	sessionStorage.setItem("check_catename",catename);
    	$("#featurePanel").html(item.html());
    	sessionStorage.setItem("check_catename_id",cate_id);
    	
    });
    
    
    
    
    
    
    
    $('#file').on("change", function () {

    	avatar=$('#file').val();
    	$('#image').text(avatar);
    	  /**
         * 文件上传
         */
        var xhr;
        function createXMLHttpRequest()
        {
            if(window.ActiveXObject)
            {
                xhr = new ActiveXObject("Microsoft.XMLHTTP");
            }
            else if(window.XMLHttpRequest)
            {
                xhr = new XMLHttpRequest();
            }
        }
        function UpladFile()
        {
            var fileObj = document.getElementById("file").files[0];
            var FileController = testUrl;
            var form = new FormData();
            form.append("myfile", fileObj);
            createXMLHttpRequest();
            xhr.onreadystatechange = handleStateChange;
            xhr.open("post", FileController, true);
            xhr.send(form);
        }
        function handleStateChange()
        {
            if(xhr.readyState == 4)
            {
                if (xhr.status == 200 || xhr.status == 0)
                {
                	
                    var result = xhr.responseText;
                    
                    var data = result.split('<div id="think_page_trace');
                    if(data[0]!=null){
                    	avatar=data[0];
                    }else{
                    	avatar=null;
                    }
                    var json = eval("(" + result + ")");
                    
                
                   easyAlert('图片链接:n'+json.file);
                }
            }
        }

        UpladFile();
     
   });
   
    	
      	 


    	$('.new_wish_btn').click(function () {
    		 var $comname=$('#comname').val();
        	 var reg = eval('/^.{2,15}$/gi');
        	 flg=reg.test($comname);
          	 if(!flg){
       			easyAlert('机构名称字数范围为2至15个!');
       			return false;
       		}
          	var $nickname=$('#nickname').val();
       	 var reg = eval('/^.{2,5}$/gi');
       	 flg=reg.test($nickname);
         	 if(!flg){
      			easyAlert('机构简称字数范围为2至5个!');
      			return false;
      		}
          	 if(streetname==""){
          		easyAlert('请选择地址!');
          		streetname="";
       			return false;
          	 }
          	 var $detailarea=$('#detailarea').val();  
          	 if($detailarea==''){
       			easyAlert('请填写完整的详细地址!');
       			return false;
       		}
          	 var $phone=$('#phone').val();  
          	var m =/^\d{3}-\d{8}|\d{4}-\d{7}$/;//验证电话号码为7-8位数字并带有区号
          	flg=m.test($phone);
          	if($phone==''){
       			easyAlert('电话号码不能为空!');
       			return false;
       		}
          	if(!flg){
       			easyAlert('电话号码格式不正确!');
       			return false;
       		}
          	
          	 if(catename==""){
           		easyAlert('请选择分类!');
        			return false;
           	 }
      /*    	 if(avatar==""){
            	easyAlert('请上传机构头像!');
         			return false;
            	 }*/
 	   if (!checkSubmitFlg) {

	    	// 第一次提交
	    	  checkSubmitFlg = true;
	    	  area=areaname+streetname+$detailarea;

	        	
	      	args={cateid:cate_id,areaid:area_id,comname:$comname,areaname:area,nickname:$nickname,
	      			phone:$phone,avatar:avatar};
	      	
	    	   $.post(newshopkeepUrl,args,function(msg){
	    		  if(msg){
	   			sessionStorage.sid=msg;
	    			 sessionStorage.cateid=cate_id;
	    			 sessionStorage.nickname=$nickname;
	    			 sessionStorage.area_id=area_id;
	    			location.href= relUrl;
	    			easyAlert('新添加商家机构成功'); 
	    			  }else{
	    				checkSubmitFlg = true;
	    				easyAlert('新添加商家机构失败');  
	    			  }    		 
	    	 	},'json');
	    	  return true;
	    	 } else {

	    	//重复提交
	    	  easyAlert("抱歉，不可以重复提交哟!");
	    	  return false;
	    	 }
    	});


});