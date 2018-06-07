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
    var $panelNav = $('#panelNav');
    $('#panelSwitch').click(function(event){
        event.preventDefault();
        if($panelNav.hasClass('active')){
            $panelNav.removeClass('active');
            $('.panel_wrap').css("display","none");
        }else{
            $('.panel_wrap').css("display","block");
            $panelNav.addClass('active');
        }
    });
    $panelNav.click(function(event){
        if($panelNav.hasClass('active')){
            $panelNav.removeClass('active');
            $('.panel_wrap').css("display","none");
        }
    });
    $('.panel_wrap').click(function(event){
        if($panelNav.hasClass('active')){
            $panelNav.removeClass('active');
            $('.panel_wrap').css("display","none");
        }
    });
    /**
     * 搜索部分
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
});