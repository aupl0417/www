/*
 * lequ
 *
 * Copyright (c) 2015 xyc
 * Licensed under the MIT license.
 */

$(function(){
    /**
     * panel部分
     * @type {*|jQuery|HTMLElement}
     */
    var jinzhi=1;//禁止滑动的变量，值为0不让滑动
    var $panelNav = $('#panelNav');
    var win_height = $(window).height()+120;//设置panel的高度
    $(".panel").css("height",win_height+"px");
    /*弹出panel的按钮*/
    $('#panelSwitch').click(function(event){
        jinzhi=0;
        event.preventDefault();
        if($panelNav.hasClass('active')){ //判读panel是否已经弹出
            $panelNav.removeClass('active').addClass('op_0');
            $('.panel_wrap').removeClass("panel_wrap_active");//去掉panel的全屏背景
        }else{
            $('.panel_wrap').addClass("panel_wrap_active");
            $panelNav.addClass('active').removeClass('op_0'); //设置panel为透明，防止按返回按钮时候panel闪一下的bug
        }
    });
    
    /*弹出penel的按钮----AJAX*/
    function panelswitchloginnow(){
    	  jinzhi=0;
          event.preventDefault();
          if($panelNav.hasClass('active')){ //判读panel是否已经弹出
              $panelNav.removeClass('active').addClass('op_0');
              $('.panel_wrap').removeClass("panel_wrap_active");//去掉panel的全屏背景
          }else{
              $('.panel_wrap').addClass("panel_wrap_active");
              $panelNav.addClass('active').removeClass('op_0'); //设置panel为透明，防止按返回按钮时候panel闪一下的bug
          }
    }
    /*弹出panel的按钮*/
    $('#panelSwitch1').click(function(event){
        jinzhi=0;
        event.preventDefault();
        if($panelNav.hasClass('active')){ //判读panel是否已经弹出
            $panelNav.removeClass('active').addClass('op_0');
            $('.panel_wrap').removeClass("panel_wrap_active");//去掉panel的全屏背景
        }else{
            $('.panel_wrap').addClass("panel_wrap_active");
            $panelNav.addClass('active').removeClass('op_0'); //设置panel为透明，防止按返回按钮时候panel闪一下的bug
        }
    });
    $panelNav.click(function(event){
        if($panelNav.hasClass('active')){
            $panelNav.removeClass('active');
            $('.panel_wrap').removeClass("panel_wrap_active");
        }
    });
    $('.panel_wrap').click(function(event){
        if($panelNav.hasClass('active')){
            $panelNav.removeClass('active');
            $('.panel_wrap').removeClass("panel_wrap_active");
        }
    });
    $panelNav.find("a").click(function (e) {
        var url = $(e.currentTarget).attr("href");
        e.preventDefault();
        setTimeout(function () {
            location.href =url;
        },500);
    });
    $(".panel_wrap").on("touchmove", function (e) {
        if(jinzhi==0){
            if($panelNav.hasClass('active')){
                e.preventDefault();
                e.stopPropagation();
            }
            else{
                jinzhi=1;
            }
        }
    });
    $("#container").on("touchmove", function (e) {
        if(jinzhi==0){
            if($panelNav.hasClass('active')){
                e.preventDefault();
                e.stopPropagation();
            }
            else{
                jinzhi=1;
            }
        }
    });
    document.addEventListener("touchmove",function(e){
        if(jinzhi==0){
            if($panelNav.hasClass('active')){
                e.preventDefault();
                e.stopPropagation();
            }
            else{
                jinzhi=1;
            }
        }
    },false);

    /**
     * 首页搜索部分
     */
    $(".search_font").click(function (event) {
        $('#input_win').trigger('focus');
        $('.search_font').css('display','none');
    });
    $('#input_win').blur(function(event){
        $('.search_font').css('display','block');
    });
    $('#input_win').focus(function(event){
        $('.search_font').css('display','none');
    });
    /**
     *判断宽度，显示滑动
     */
    var  isMobile = $(window).width();
    if(isMobile>800){
        $(".index_slide_pre").removeClass("hidden");
        $(".index_slide_next").removeClass("hidden");
    }

});
