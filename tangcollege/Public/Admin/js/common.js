//dom加载完成后执行的js
;$(function(){

	//全选的实现
	$(".check-all").click(function(){
		$(".ids").prop("checked", this.checked);
	});
	$(".ids").click(function(){
		var option = $(".ids");
		option.each(function(i){
			if(!this.checked){
				$(".check-all").prop("checked", false);
				return false;
			}else{
				$(".check-all").prop("checked", true);
			}
		});
	});

    //ajax get请求
    $('.ajax-get').click(function(){
        var target;
        var that = this;
        if ( $(this).hasClass('confirm') ) {
        	if(!confirm('确认要执行该操作吗?')){
	            return false;
	        }
        }
        if ( (target = $(this).attr('href')) || (target = $(this).attr('url')) ) {
            $.get(target).success(function(data){
                if (data.status==1) {
                    if (data.url) {
                        updateAlert(data.info + ' 页面即将自动跳转~','alert-success');
                    }else{
                        updateAlert(data.info,'alert-success');
                    }
                    setTimeout(function(){
                        if (data.url) {
                            location.href=data.url;
                        }else if( $(that).hasClass('no-refresh')){
                            $('#top-alert').find('button').click();
                        }else{
                            location.reload();
                        }
                    },1500);
                }else{
                    updateAlert(data.info);
                    setTimeout(function(){
                        if (data.url) {
                            location.href=data.url;
                        }else{
                            $('#top-alert').find('button').click();
                        }
                    },1500);
                }
            });

        }
        return false;
    });

    //ajax post submit请求
    $('.ajax-post').click(function(){
        var target,query,form;
        var target_form = $(this).attr('target-form');
        var that = this;
        var nead_confirm=false;
        if( ($(this).attr('type')=='submit') || (target = $(this).attr('href')) || (target = $(this).attr('url')) ){
            form = $('.'+target_form);

            if ($(this).attr('hide-data') === 'true'){//无数据时也可以使用的功能
            	form = $('.hide-data');
            	query = form.serialize();
            }else if (form.get(0)==undefined){
            	return false;
            }else if ( form.get(0).nodeName=='FORM' ){
                if ( $(this).hasClass('confirm') ) {
                	if(!confirm('确认要执行该操作吗?')){
	                    return false;
	                }
                }
                if($(this).attr('url') !== undefined){
                	target = $(this).attr('url');
                }else{
                	target = form.get(0).action;
                }
                query = form.serialize();
            }else if( form.get(0).nodeName=='INPUT' || form.get(0).nodeName=='SELECT' || form.get(0).nodeName=='TEXTAREA') {
                form.each(function(k,v){
                    if(v.type=='checkbox' && v.checked==true){
                        nead_confirm = true;
                    }
                })
                if ( nead_confirm && $(this).hasClass('confirm') ) {
                    if(!confirm('确认要执行该操作吗?')){
                        return false;
                    }
                }
                query = form.serialize();
            }else{
                if ( $(this).hasClass('confirm') ) {
                    if(!confirm('确认要执行该操作吗?')){
                        return false;
                    }
                }
                query = form.find('input,select,textarea').serialize();
            }
            $(that).addClass('disabled').attr('autocomplete','off').prop('disabled',true);
            $.post(target,query).success(function(data){
                if (data.status==1) {
                    if (data.url) {
                        updateAlert(data.info + ' 页面即将自动跳转~','alert-success');
                    }else{
                    	if(data.msg != undefined){
                    		layer.msg(data.msg);
                    	}else if(data.info != undefined){
                    		updateAlert(data.info ,'alert-success');
                    	}else {
                    		updateAlert(data.info ,'alert-success');
                    	}
                    }
                    setTimeout(function(){
                        if (data.url) {
                            location.href=data.url;
                        }else if( $(that).hasClass('no-refresh')){
                            $('#top-alert').find('button').click();
                            $(that).removeClass('disabled').prop('disabled',false);
                        }else{
                            location.reload();
                        }
                    },1500);
                }else{
                	if(data.msg != undefined){
                		layer.msg(data.msg);
                	}else if(data.info != undefined){
                		updateAlert(data.info);
                	}else {
                		updateAlert(data.info);
                	}
                    setTimeout(function(){
                        if (data.url) {
                            location.href=data.url;
                        }else{
                            $('#top-alert').find('button').click();
                            $(that).removeClass('disabled').prop('disabled',false);
                        }
                    },1500);
                }
            });
        }
        return false;
    });

	/**顶部警告栏*/
	var content = $('#main');
	var top_alert = $('#top-alert');
	top_alert.find('.close').on('click', function () {
		top_alert.removeClass('block').slideUp(200);
		// content.animate({paddingTop:'-=55'},200);
	});

    window.updateAlert = function (text,c) {
		text = text||'default';
		c = c||false;
		if ( text!='default' ) {
            top_alert.find('.alert-content').text(text);
			if (top_alert.hasClass('block')) {
			} else {
				top_alert.addClass('block').slideDown(200);
				// content.animate({paddingTop:'+=55'},200);
			}
		} else {
			if (top_alert.hasClass('block')) {
				top_alert.removeClass('block').slideUp(200);
				// content.animate({paddingTop:'-=55'},200);
			}
		}
		if ( c!=false ) {
            top_alert.removeClass('alert-error alert-warn alert-info alert-success').addClass(c);
		}
	};

    //按钮组
    (function(){
        //按钮组(鼠标悬浮显示)
        $(".btn-group").mouseenter(function(){
            var userMenu = $(this).children(".dropdown ");
            var icon = $(this).find(".btn i");
            icon.addClass("btn-arrowup").removeClass("btn-arrowdown");
            userMenu.show();
            clearTimeout(userMenu.data("timeout"));
        }).mouseleave(function(){
            var userMenu = $(this).children(".dropdown");
            var icon = $(this).find(".btn i");
            icon.removeClass("btn-arrowup").addClass("btn-arrowdown");
            userMenu.data("timeout") && clearTimeout(userMenu.data("timeout"));
            userMenu.data("timeout", setTimeout(function(){userMenu.hide()}, 100));
        });

        //按钮组(鼠标点击显示)
        // $(".btn-group-click .btn").click(function(){
        //     var userMenu = $(this).next(".dropdown ");
        //     var icon = $(this).find("i");
        //     icon.toggleClass("btn-arrowup");
        //     userMenu.toggleClass("block");
        // });
        $(".btn-group-click .btn").click(function(e){
            if ($(this).next(".dropdown").is(":hidden")) {
                $(this).next(".dropdown").show();
                $(this).find("i").addClass("btn-arrowup");
                e.stopPropagation();
            }else{
                $(this).find("i").removeClass("btn-arrowup");
            }
        })
        $(".dropdown").click(function(e) {
            e.stopPropagation();
        });
        $(document).click(function() {
            $(".dropdown").hide();
            $(".btn-group-click .btn").find("i").removeClass("btn-arrowup");
        });
    })();

    // 独立域表单获取焦点样式
    $(".text").focus(function(){
        $(this).addClass("focus");
    }).blur(function(){
        $(this).removeClass('focus');
    });
    $("textarea").focus(function(){
        $(this).closest(".textarea").addClass("focus");
    }).blur(function(){
        $(this).closest(".textarea").removeClass("focus");
    });
});

/* 上传图片预览弹出层 */


//标签页切换(无下一步)
function showTab() {
    $(".tab-nav li").click(function(){
        var self = $(this), target = self.data("tab");
        self.addClass("current").siblings(".current").removeClass("current");
        window.location.hash = "#" + target.substr(3);
        $(".tab-pane.in").removeClass("in");
        $("." + target).addClass("in");
    }).filter("[data-tab=tab" + window.location.hash.substr(1) + "]").click();
}

//标签页切换(有下一步)
function nextTab() {
     $(".tab-nav li").click(function(){
        var self = $(this), target = self.data("tab");
        self.addClass("current").siblings(".current").removeClass("current");
        window.location.hash = "#" + target.substr(3);
        $(".tab-pane.in").removeClass("in");
        $("." + target).addClass("in");
        showBtn();
    }).filter("[data-tab=tab" + window.location.hash.substr(1) + "]").click();

    $("#submit-next").click(function(){
        $(".tab-nav li.current").next().click();
        showBtn();
    });
}

// 下一步按钮切换
function showBtn() {
    var lastTabItem = $(".tab-nav li:last");
    if( lastTabItem.hasClass("current") ) {
        $("#submit").removeClass("hidden");
        $("#submit-next").addClass("hidden");
    } else {
        $("#submit").addClass("hidden");
        $("#submit-next").removeClass("hidden");
    }
}

//导航高亮
function highlight_subnav(url){
    $('.side-sub-menu').find('a[href="'+url+'"]').closest('li').addClass('current');
}
//选择班级
function selectClass(){ 
            var otra = new Object; 
            otra.box = null; 
			otra.element = null;
			otra.target = null;
			otra.callback = null;
            otra.get = function(e,callback){ 
			   otra.element = '#classId';
			   otra.callback = (typeof callback === "function") ? callback : null;
			   otra.target = e;
               $.get("/index.php?s=/Admin/Class/getselectClassListTempletByAjax.html", function(str){
                otra.box = layer.open({
                       type: 1,
                       content: str,
					   area:['800px','550px'],
					   scrollbar: false,
					 //  offset: [$(e).offset().top, $(e).offset().left],
                     });
              });
           }; 
           return otra; 
        } 
var selectClass = selectClass();

function selectTrainingsite(){ 
            var otra = new Object; 
            otra.box = null; 
			otra.element = null;
            otra.get = function(e){
			  otra.element = e; 
               $.get("/index.php?s=/Admin/Trainingsite/getTrainingsiteListByAjax.html", function(str){
                otra.box = layer.open({
                       type: 1,
                       content: str,
					   area:['1000px','600px'],
					  // offset: [$(e).offset().top, $(e).offset().left],
                     });
              });
           }; 
		  
           return otra; 
        } 
		
var selectTrainingsite = selectTrainingsite();


function selectTeacher() {
	 var object =  new Object; 
	 object.box = null;
	 object.element = null;
	 object.target = null;
	 object.callBack = null;
	 object.get = function(e,callBack) {
		/* if(typeof(branchId) == "undefined") {
			 alert('请传入分院ID');
			 return false;
		 }*/
		object.target = e;
		object.element = "#teacherId";
		object.callBack = typeof callBack == 'function' ? callBack : null;
        $.get("/index.php?s=/Admin/Teacher/getTeacherTempletByAjax.html",{branchId:0}, function(str){
                object.box = layer.open({
                       type: 1,
                       content: str,
					   scrollbar: false,
					   area:['600px','600px'],
					  // offset: [$(e).offset().top, $(e).offset().left],
                     });
              });
	 }  
	 return object; 
 }

 var selectTeacher = selectTeacher();

//删除或是否共享操作
function delAndShareHandle(className){
	$('.'+className).click(function(){
		var url = $(this).data('url');
		var msg = $(this).data('msg');
		layer.confirm(msg, {
			btn: ['确定','取消'] //按钮
		}, function(){
			$.ajax({
				type : 'get',
				url  : url,
				dataType : 'json',
				success : function(data){
					if(data.status == 1){
						layer.msg(data.msg);
						setInterval(function(){
							window.location.reload();
						}, 2000);
					}else {
						layer.msg(data.msg);//alert(data.msg);
					}
				}
			});
		});
	});
}

function selectCourse(){ 
            var object = new Object; 
            object.box = null; 
			object.callBack = null;
            object.get = function(e,callBack){ 
			   object.target = e; 
			   object.callBack = typeof callBack == 'function' ? callBack : function() {};
               $.ajax({type:'GET',url:"/index.php?s=/Admin/course/getselectCourseListTempletByAjax.html",success: function(str){
                  object.box = layer.open({
					                type : 1,
                                    content: str,
									area   : ['600px'],
									//offset: [$(e).offset().top, $(e).offset().left],
                  }); 
              }});
           }; 
           return object; 
        } 
		
var selectCourse = selectCourse();


function selectStudent(){ 
            var object = new Object; 
            object.box = null; 
			object.callBack = null;
            object.get = function(e,callBack){ 
			   object.target = e; 
			   object.callBack = typeof callBack == 'function' ? callBack : function() {};
               $.ajax({type:'GET',url:"/index.php?s=/Admin/student/getStudentTempletByAjax.html",success: function(str){
                  object.box = layer.open({
					                type : 1,
                                    content: str,
									area   : ['600px','550px'],
									//offset: [$(e).offset().top, $(e).offset().left],
                  }); 
              }});
           }; 
           return object; 
        } 
		
var selectStudent = selectStudent();

function popLayer(className){
	$('.'+className).click(function(){
		var box;
		var url = $(this).data('url');
		$.get(url, function(str){
            box = layer.open({
               type: 1,
               content: str,
				area: '600px',
            });
         });
	});
}

function ajaxSubmitForm(){
	var loading = null;
	var options = {
		   beforeSubmit: function (formData, jqForm, options) {
			  loading = layer.load(); 
		   },
			success: function (data) {
	                    layer.close(loading);
					    layer.msg(data.info,{time:1000},function(){
							if(data.status == 1) {
								self.location.reload();
							}
						});
					   
	                }
	            }; 
	$("#form").ajaxForm(options);
}

