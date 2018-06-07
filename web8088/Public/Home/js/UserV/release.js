  /*
   * release
   *
   * Copyright (c) 2015 xyc
   * Licensed under the MIT license.
   */

  function hiddenTagById(id){
      var selector = "#"+id;
      $(selector).addClass("hidden");
  }
  function showTagById(id){
      var selector = "#"+id;
      $(selector).removeClass("hidden");
  }
  $(function() {
      /**
       * 特技Input框
       */
      $("input").on("focus", function (event) {
          $(event.target).next().removeClass("op_0");

      });
      $("input").on("blur", function (event) {
          $(event.target).next().addClass("op_0");

      });
      $("textarea").on("focus", function (event) {
          $(event.target).next().removeClass("op_0");

      });
      $("textarea").on("blur", function (event) {
          $(event.target).next().addClass("op_0");

      });
      $(".input_close_icon").click(function (event) {
          $(event.target).prev().val("");
      });

      /**
       * 获取sessionStorage
       */
      if(!sessionStorage.click){
          sessionStorage.selectOra=2;
      }
    var cate_id = "";
    var $sid = sessionStorage.sid;
    var $cateid = sessionStorage.cateid;
    var $nickname = sessionStorage.nickname;
    var $tags = sessionStorage.tags;
    var $area_id = sessionStorage.area_id;
    var $title;
    var price = 0;
    var $model = sessionStorage.modelnum;
    var $content;
    var time = sessionStorage.timenum;
    var timehtml = sessionStorage.timehtml;
    var checkSubmitFlg = false;
    var time = undefined;
    /*插入myAlert插件*/
    var myAlert = new alertWin();
    myAlert.work();
    var success_Win = new successWin();
    success_Win.work();
    $(".tuan_title").on("keyup", function() {
      var data = $(".tuan_title").val();
      if (data.length > 15) {
          $("#title_err").html("组团标题字数为1至15个").removeClass("op_0");
          var data = $(".tuan_title").val(data.slice(0, 10));
      }
    });
    /**
     * 课程价格点击处理
     */
    if (!isNaN($('.course_select_price_input').val())) {
      $('.course_select_price_input').css("opacity", 1);
    }
    $("#price_wrap").click(function() {
      $('.course_select_price_input').css("opacity", 1);
      $('.course_select_price_input').trigger("focus");
      if (!isNaN($('#price').html())) {
        $('.course_select_price_input').val($('#price').html());
      }
    });
   /* $('.course_select_price_input').on("blur", function() {
      $('#price').css("opacity", 1);
      var price = $(this).val();
      if (!isNaN(price)) {
        if (price > 0) {
            console.log(price)
          $('#price').html(price);
          sessionStorage.price = price;
        }
          else{
            $('#price').html("输入价格");
            sessionStorage.price = null;
        }
        $(this).css("opacity", 0);
      } else {
        $('.course_select_price_input').css("opacity", 1);
          $("#course_release_err").html("请输入数字").removeClass("hidden");

        $('#price').html("输入价格");
        $(this).css("opacity", 0);
          $('.course_select_price_input').val("");
      }
    });
    $('.course_select_price_input').on("focus", function() {
      $('#price').css("opacity", 0);
    })*/

      //价格点击
      $('#price_select').on('change', function() {
          price = $('#price_select').val();
          var priceHtml = $('#price_select option').not(function(){
              return !this.selected;
          })[0].innerHTML;

          sessionStorage.price = priceHtml;
          $('#price').html(priceHtml);
      });
    /**
     *根据sessionStorage对页面的提示做出处理
     **/
    sessionStorage.catename = $(".course_active").find("p").text();
    sessionStorage.cate_id = $(".course_active").find("img").attr('alt');
    if ($tags == undefined) {
        if(sessionStorage.selectOra!=2){
            showTagById("feature_release_err");
        }
      $('#feature').html("请选择")

    } else {
      $('#feature').html($tags).css("color","#999");
    }
    if (sessionStorage.price == null) {
        $('#price').html("输入价格");
    } else {
        $('#price').html(sessionStorage.price).css("color","#999");
    }
    if (sessionStorage.model == null) {
        if(sessionStorage.selectOra!=2){
            showTagById("model_release_err");
        }
        $('#model').html("请选择");
    } else {
      $('#model').html(sessionStorage.model).css("color","#999");
    }
      if (sessionStorage.course_title == null) {
          if(sessionStorage.selectOra!=2){
              $("#title_err").html("组团标题字数为1至15个").removeClass("op_0");
          }
      } else {
          $('#title').val(sessionStorage.course_title);
      }

      if (sessionStorage.course_cont == null) {
          if(sessionStorage.selectOra!=2){
              $("#cont_err").removeClass("op_0");
          }
      } else {
          $('#course_textarea').val(sessionStorage.course_cont);
      }

    if ($nickname == null) {
        if(sessionStorage.selectOra!=2){
            showTagById("ora_release_err");
        }
        $('#local').html("请选择");
    } else {
      $('#local').html($nickname).css("color","#999");;
    }
      sessionStorage.selectOra=2;


      if (timehtml == null) {
          //$('#local').html("请选择");
      } else {
        $('#time').html(timehtml).css("color","#999");
      }
      
      
      /***
       * 滑动点击换图片的js
       */
    $('.course_slide').on('click', 'li', function(event) {
      if ($(event.target).attr('src') != null) {
        cate_id = $(event.target).attr("alt");
        sessionStorage.cate_id = cate_id;
        sessionStorage.catename = $(event.target).next().text();
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
          $('.select_feature').removeClass('hidden');
      }
    });

      /***
       * 滑动部分的js代码
       * @type {HTMLElement}
       */
      var $slide = document.getElementById('swipeSlide'),
      aBullet = $slide.querySelectorAll('.icon-bullet'),
      len = aBullet.length;

        var mySwipe = new Swipe($slide, {
          continuous: false,
          stopPropagation: true,
          callback: function(i) {
            if (i >= 0 || i < len) {
              $('.icon-bullet').removeClass('active')
              aBullet[i].classList.add('active');
            }
          }
        });

    var select_alt = "[alt='" + $cateid + "']";
    var select_li = $(select_alt);
    var select_ul = select_li.parents("ul");
    $(select_li).trigger("click");
    var index = select_ul.attr("index");
    if (index != null) {
      mySwipe.slide(index, 100)
    }

    

      /**
       * 约课模式的select的change事件，改变对应的p的内容
       */
    $('#course_select_model').bind('change', function() {
      $model = $('#course_select_model').val();
      console.log($model);
      sessionStorage.modelnum = $model;
      sessionStorage.model = $model;
      if ($model == 1) {
        sessionStorage.model = "全日制";
      } else if ($model == 2) {
        sessionStorage.model = "工作日";
      } else if ($model == 3) {
        sessionStorage.model = "周末班";
      } else if ($model == 4) {
        sessionStorage.model = "寒暑假";
      }else if ($model == 5) {
        sessionStorage.model = "网络班";
      }else{
    	  sessionStorage.model = "其他";
      }
      $('#model').html(sessionStorage.model);
    });

      $('#course_select_time').on('change', function() {
    	  time = $('#course_select_time').val();
    	  var timeHtml = $('#course_select_time option').not(function(){
    	        return !this.selected;
    	    })[0].innerHTML;
          
           sessionStorage.timenum = time;
           sessionStorage.time = time;
           sessionStorage.timehtml = timeHtml;
         
           
          $('#time').html(timeHtml);
      });
 

    /*检查用户有没有达到一天发布的上限*/
    $.post(checkGroupSend, function(msg) {
        console.log("today:", msg);

        if (msg.status != 200) {
            easyAlert(msg.res);
        }

    }, "json");

  //检查用户的资料完善度
    function checkInfo(){
    $.post(checkinfo, function(msg) {
        if (msg == false) {
        	//调回登陆页面
        	location.href = login;
        	return false;
        } 
        if(msg<40) {
          myAlert.setText("抱歉，您的资料完善度低于40%，请先完善您的个人资料！");
          myAlert.show();
          myAlert.work(function(){
        	  myAlert.hidden();
              location.href = moInfo;
          })
          return false;
        }
      }, 'json');
    }
    
    
    //检查用户的资料完善度
    function checkAvatar(){
    $.post(checkAvatars, function(msg) {
        if (msg == false) {
        	//调回登陆页面
        	 myAlert.setText("抱歉，请您先上传个人头像");
        	 myAlert.show();
             myAlert.work(function(){
           	  myAlert.hidden();
                 location.href = moInfo;
             })
        	return false;
        } 
        if(msg==true) {
        	 checkInfo(); 
        }
      }, 'json');
    }
    checkAvatar();//立即执行
    
    $.post(timelists,function(datatime){
    	if(datatime.status==200){
    		var htmltime='';
    		for (var i=0;i<datatime.timelist.length;i++){
    			htmltime=htmltime+'<option value='+i+'>'+datatime.timelist[i]+'</option>';
    		}
    		$("#course_select_time").html(htmltime);
    	}
    },'json');

    /*用户提交 发布心愿的数据*/
    $('#wrap_btn').on("click", function(event) {
    	 checkAvatar();
        if(time==null||time==""){
            $("#time_err").html("截止日期不能为空").removeClass("hidden");
        }
        else{
            $("#time_err").addClass("hidden");
        }
        sessionStorage.click = true;
        var check = true;//check变量控制是不是不让提交，如果某部分出错了，就设置为false
        $content = ($('#course_textarea').val()); //填写的组课的课程内容
        if ($content == ""|| $content == null) {
            check=false;
            $("#cont_err").removeClass("op_0");
            $("#course_textarea").trigger("focus");
        }
        else{
            sessionStorage.course_cont = $content;
            $("#cont_err").addClass("op_0");
        }
     /*   var time = $("#course_select_time").val();
        if(time==null||time==""){
            alert("ss")
        }*/
        $title = ($('.register_input').val()); //填写的组课的标题
        var reg = eval('/^.{1,16}$/gi');
        flg = reg.test($title);
        if (!flg) {
            check=false;
            $("#title_err").html("组团标题字数为1至15个").removeClass("op_0");
            $(".tuan_title").trigger("focus");
        }
        else{
            $("#title_err").addClass("op_0");
            sessionStorage.course_title = $title;

        }
      var prices = $('#price').html();
      if ($nickname == null || $nickname == "") {
          console.log("培训机构不可以为空")

          check=false;
          showTagById("ora_release_err");
          $(window).scrollTop(0,0);
      }
        else{
          hiddenTagById("ora_release_err");
      }
      if (isNaN(prices) ||prices == null || prices<=0) {
    	  console.log(prices)
        $(".course_select_price_input").trigger("click");
          $("#course_release_err").html("课程价格不能为空").removeClass("hidden");
          check=false;
      }
        else{
          hiddenTagById("course_release_err");
      }
      if ($model == null || $model == "") {
          check=false;
          showTagById("model_release_err");
      }
        else{
          hiddenTagById("model_release_err");
      }
      if (time == null || time == "") {
    	  	   check=false;
    	  	   showTagById("time_err");
      }else{
        	    hiddenTagById("time_err");
      }
      if ($tags == null || $tags == "") {
          check=false;
          showTagById("feature_release_err");
      }
      else{
          hiddenTagById("feature_release_err");
      }

    if(check){//判断check是不是为true

        if (!checkSubmitFlg) {
          // 第一次提交
        checkSubmitFlg = true;

        args = {
          sid: $sid,
          cateid: $cateid,
          areaid: $area_id,
          nickname: $nickname,
          tags: $tags,
          title: $title,
          model: $model,
          price: prices,
          content: $content,
          overtime: time
        };
        $.post(insertUrl, args, function(msg) {
          if (msg == true) {
            sessionStorage.clear();
            success_Win.show();
          } else {
            checkSubmitFlg = true;
            myAlert.setText("发布失败或者次数达到上限");
            myAlert.show();

          }
        }, 'json');

        return true;
      } else {
        //重复提交
        myAlert.setText("抱歉，不可以重复提交哟!");
        myAlert.show();
        return false;
      }
    }


    });
      /**
       * 判断是不是手机，加滑动导航
       * @type {*|jQuery}
       */
      var  isMobile = $(window).width();
      if(isMobile>800){
          $(".index_slide_pre").removeClass("hidden");
          $(".index_slide_next").removeClass("hidden");
          $(".index_slide_pre").click(function () {
              mySwipe.prev();
          });
          $(".index_slide_next").click(function () {
              mySwipe.next();
          });
      }

      // 点击图片时间
      $('#image').on("click", function (event) {
          $('#file').trigger("click");
      });
  });
