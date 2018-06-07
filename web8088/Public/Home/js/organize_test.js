/*
 * organiza
 *
 * Copyright (c) 2015 xyc
 * Licensed under the MIT license.
 */

$(function(){
    var  isMobile = $(window).width();
    sessionStorage.selectOra = 1;//release页面判断是不是这个页面返回去的
    function showLoading(){
        $('.loading').removeClass("hidden");
    }
    function hiddenLoading(){
        $('.loading').addClass("hidden");
    }
    function showPages(){
        $('.resbut').removeClass("hidden");
    }
    function hiddenPages(){
        $('.resbut').addClass("hidden");
    }
    //定义变量
    var city = "广州市";
    var area;
    var area_id="";
    var address;
    var cateid=0;
    var cate_id=0;
    var sort;
    var $greet="";
    var $nearby="";
    var $x;
    var $y;
    var pages=1;
    var arg="";
    var temp="";
    var args={};
    var ajaxFlat = {};//ajaxFlat做判断，避免网速太慢，数据append到不该append的地方
    ajaxFlat.flat = "cate";

    //根据sessionStorage对页面做对应操作
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

    /**
     * 第一次进来的时候，获取培训分类列表
     */
    $.post(cateUrl,args,function(msg){
        //获取地址
        var tpl = document.getElementById('catename').innerHTML;
        var html = juicer(tpl, msg);
        $("#cate").html('');
        $("#cate").append(html);
    },'json');

    /**
     * 培训机构点击后处理的函数
     */
    if(isMobile>800) {
        $('#course_constitution').click(function () {
            //下面两个if都是判断其他的下拉组件是否被选中了，被选中了就让组件弹回去
            if($('#course_local').hasClass("select_color")){
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').toggleClass("select_color");
                $('.course-box').toggleClass('course-box-active');
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course_institution_nav_all_img').attr('src', "__HIMG__/icon (1).png");
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
            if($('#course_sort').hasClass("select_color")) {
                $('#sort-img').toggleClass('course_institution_nav_img_active');
                $('#course_sort').toggleClass("select_color");
                $('.course_sort_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            //弹出培训类型的列表
            $('#constitution-img').toggleClass('course_institution_nav_img_active');
            $('#course_constitution').toggleClass("select_color");
            $('.course_constitution_select').toggleClass('course_sort_select_active');
            $('.course-box').toggleClass('course-box-active');

        });
    }
    else{
        $('#course_constitution').tap(function () {
            //下面两个if都是判断其他的下拉组件是否被选中了，被选中了就让组件弹回去
            if($('#course_local').hasClass("select_color")){
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').toggleClass("select_color");
                $('.course-box').toggleClass('course-box-active');
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course_institution_nav_all_img').attr('src', "__HIMG__/icon (1).png");
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
            if($('#course_sort').hasClass("select_color")) {
                $('#sort-img').toggleClass('course_institution_nav_img_active');
                $('#course_sort').toggleClass("select_color");
                $('.course_sort_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            //弹出培训类型的列表
            $('#constitution-img').toggleClass('course_institution_nav_img_active');
            $('#course_constitution').toggleClass("select_color");
            $('.course_constitution_select').toggleClass('course_sort_select_active');
            $('.course-box').toggleClass('course-box-active');

        });
    }


    if(isMobile>800) {
        $('#cate').on('click','li',function (event) {

            pages=1;//把当前页数设置为1
            $("#shopkeeper").html('');
            $('.course_constitution_select_li').removeClass('first_select_active');
            $(event.target).addClass('first_select_active');
            //cate_id=$(event.target).attr('alt');
            //$('#course_constitution span').text($(event.target).text());
            //$('#course_constitution span').attr('alt',cate_id);
            $('.course_constitution_select_second').removeClass('select_hidden');
        });

        $('.course_constitution_select_second').on('click','.course_constitution_select_second_li', function (event) {
            $('.course_constitution_select_second').addClass('select_hidden');
            $('.course_constitution_select_first').toggleClass('course_sort_select_active');
            $('#course_constitution').removeClass("select_color");
            $('#constitution-img').toggleClass('course_institution_nav_img_active');
            $('.course-box').toggleClass('course-box-active');
            $('#course_constitution span').text($(event.target).text());

            //ajaxFlat = {};//清空ajaxFlat
            //tempFlat = "cate"+cate_id;//设置新的ajaxFlat
            //ajaxFlat.flat = tempFlat;
            //getInfo(ajaxFlat.flat);//把这个新的ajaxFlat赋值给获取数据的函数，下面的函数append数据的时候，判断ajaxFlat是不是和传给他的参数相同，相同才append
        });
    }
    else{
        $('#cate').on('tap','li',function (event) {

            pages=1;//把当前页数设置为1
            $("#shopkeeper").html('');
            $('.course_constitution_select_li').removeClass('first_select_active');
            $(event.target).addClass('first_select_active');
            cate_id=$(event.target).attr('alt');
            $('#course_constitution span').text($(event.target).text());
            $('#course_constitution span').attr('alt',cate_id);
            $('.course_constitution_select').toggleClass('course_sort_select_active');
            $('#course_constitution').removeClass("select_color");
            $('#constitution-img').toggleClass('course_institution_nav_img_active');
            $('.course-box').toggleClass('course-box-active');
            ajaxFlat = {};//清空ajaxFlat
            tempFlat = "cate"+cate_id;//设置新的ajaxFlat
            ajaxFlat.flat = tempFlat;
            getInfo(ajaxFlat.flat);//把这个新的ajaxFlat赋值给获取数据的函数，下面的函数append数据的时候，判断ajaxFlat是不是和传给他的参数相同，相同才append
        });
    }


    /*
     * 进入到页面时，页面加载获取的当前分类的所有商家信息
     */

    cate_id = $('#course_constitution span').attr('alt');





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

    if(isMobile>800) {
        /*地区选择栏第一次*/
        $('#course_local').click(function (event) {
            //注释基本同培训机构点击后处理的函数
            if($('#course_constitution').hasClass("select_color")){
                $('#constitution-img').toggleClass('course_institution_nav_img_active');
                $('#course_constitution').toggleClass("select_color");
                $('.course_constitution_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            if($('#course_sort').hasClass("select_color")) {
                $('#sort-img').toggleClass('course_institution_nav_img_active');
                $('#course_sort').toggleClass("select_color");
                $('.course_sort_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }

            $('.course-box').toggleClass('course-box-active');
            $('#course_local').toggleClass("select_color");
            $('#local-img').toggleClass('course_institution_nav_img_active');
            $('.course_institution_nav_all_img').attr('src', "__HIMG__/icon (1).png");
            /*此处写ajax获取区*/
            $('.course_local_first_select').toggleClass('course_local_first_select_active');
            if (!$('.course_local_first_select').hasClass('course_local_first_select_active')) {
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
        });
    }
    else{
        /*地区选择栏第一次*/
        $('#course_local').tap(function (event) {
            //注释基本同培训机构点击后处理的函数
            if($('#course_constitution').hasClass("select_color")){
                $('#constitution-img').toggleClass('course_institution_nav_img_active');
                $('#course_constitution').toggleClass("select_color");
                $('.course_constitution_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            if($('#course_sort').hasClass("select_color")) {
                $('#sort-img').toggleClass('course_institution_nav_img_active');
                $('#course_sort').toggleClass("select_color");
                $('.course_sort_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }

            $('.course-box').toggleClass('course-box-active');
            $('#course_local').toggleClass("select_color");
            $('#local-img').toggleClass('course_institution_nav_img_active');
            $('.course_institution_nav_all_img').attr('src', "__HIMG__/icon (1).png");
            /*此处写ajax获取区*/
            $('.course_local_first_select').toggleClass('course_local_first_select_active');
            if (!$('.course_local_first_select').hasClass('course_local_first_select_active')) {
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
        });
    }


    if(isMobile>800) {
        $('.course_local_first_select').on('click','li',function (event) {
            $("#secondArea").html('');
            area = $(event.target).text();
            area_id=$(event.target).attr('alt');
            if(area_id==0){
                pages=1;
                $('#course_local span').text("全部地区")
                $("#shopkeeper").html('');
                $(".course_local_first_select_li").removeClass('first_select_active');
                $('.course_local_second_select').addClass('course_local_second_select_hidden');
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').removeClass("select_color");
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course-box').toggleClass('course-box-active');
                ajaxFlat = {};
                ajaxFlat.flat = "area"+area_id;
                getInfo(ajaxFlat.flat);
            }else{
                var args={area_id:area_id};
                $.post(secondUrl,args,function(msg){
                    //获取地址
                    var tpl = document.getElementById('second').innerHTML;
                    var html = juicer(tpl, msg);
                    $("#secondArea").html(html);
                },'json');
                $('.course_local_second_select').removeClass('course_local_second_select_hidden');

            }
            $(".course_local_first_select_li").removeClass('first_select_active');
            $(event.target).addClass('first_select_active');
            /*此处ajax获取二级地址*/

        });
    }
    else{
        $('.course_local_first_select').on('tap','li',function (event) {
            $("#secondArea").html('');
            area = $(event.target).text();
            area_id=$(event.target).attr('alt');
            if(area_id==0){
                pages=1;
                $('#course_local span').text("全部地区")
                $("#shopkeeper").html('');
                $(".course_local_first_select_li").removeClass('first_select_active');
                $('.course_local_second_select').addClass('course_local_second_select_hidden');
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').removeClass("select_color");
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course-box').toggleClass('course-box-active');
                ajaxFlat = {};
                ajaxFlat.flat = "area"+area_id;
                getInfo(ajaxFlat.flat);
            }else{
                var args={area_id:area_id};
                $.post(secondUrl,args,function(msg){
                    //获取地址
                    var tpl = document.getElementById('second').innerHTML;
                    var html = juicer(tpl, msg);
                    $("#secondArea").html(html);
                },'json');
                $('.course_local_second_select').removeClass('course_local_second_select_hidden');

            }
            $(".course_local_first_select_li").removeClass('first_select_active');
            $(event.target).addClass('first_select_active');
            /*此处ajax获取二级地址*/

        });
    }


    if(isMobile>800) {
        /**
         * 二级地区点击
         */
        $('.course_local_second_select').on('click','li',function () {

            pages=1;
            $("#shopkeeper").html('');
            $('#course_local span').text($(event.target).text())
            address=$(event.target).text();
            $('.course_local_second_select').addClass('course_local_second_select_hidden');
            $('.course_local_first_select').toggleClass('course_local_first_select_active');
            $('#course_local').removeClass("select_color");
            $('#local-img').toggleClass('course_institution_nav_img_active');
            $('.course-box').toggleClass('course-box-active');
            /* 返回当前用户选择的一个地区*/
            area_id=$(event.target).attr('alt');
            ajaxFlat = {};
            ajaxFlat.flat = "area"+area_id;
            getInfo(ajaxFlat.flat);

        });
    }
    else{
        /**
         * 二级地区点击
         */
        $('.course_local_second_select').on('tap','li',function () {

            pages=1;
            $("#shopkeeper").html('');
            $('#course_local span').text($(event.target).text())
            address=$(event.target).text();
            $('.course_local_second_select').addClass('course_local_second_select_hidden');
            $('.course_local_first_select').toggleClass('course_local_first_select_active');
            $('#course_local').removeClass("select_color");
            $('#local-img').toggleClass('course_institution_nav_img_active');
            $('.course-box').toggleClass('course-box-active');
            /* 返回当前用户选择的一个地区*/
            area_id=$(event.target).attr('alt');
            ajaxFlat = {};
            ajaxFlat.flat = "area"+area_id;
            getInfo(ajaxFlat.flat);

        });
    }

    if(isMobile>800) {
        /*排序*/

        $('#course_sort').click(function (event) {
            //注释大致同培训机构点击后处理的函数
            if($('#course_constitution').hasClass("select_color")){
                $('#constitution-img').toggleClass('course_institution_nav_img_active');
                $('#course_constitution').toggleClass("select_color");
                $('.course_constitution_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            if($('#course_local').hasClass("select_color")){
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').toggleClass("select_color");
                $('.course-box').toggleClass('course-box-active');
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course_institution_nav_all_img').attr('src', "__HIMG__/icon (1).png");
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
            $('#sort-img').toggleClass('course_institution_nav_img_active');
            $('#course_sort').toggleClass("select_color");
            $('.course_sort_select').toggleClass('course_sort_select_active');
            $('.course-box').toggleClass('course-box-active');

        });
    }
    else{
        /*排序*/

        $('#course_sort').tap(function (event) {
            //注释大致同培训机构点击后处理的函数
            if($('#course_constitution').hasClass("select_color")){
                $('#constitution-img').toggleClass('course_institution_nav_img_active');
                $('#course_constitution').toggleClass("select_color");
                $('.course_constitution_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            if($('#course_local').hasClass("select_color")){
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').toggleClass("select_color");
                $('.course-box').toggleClass('course-box-active');
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course_institution_nav_all_img').attr('src', "__HIMG__/icon (1).png");
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
            $('#sort-img').toggleClass('course_institution_nav_img_active');
            $('#course_sort').toggleClass("select_color");
            $('.course_sort_select').toggleClass('course_sort_select_active');
            $('.course-box').toggleClass('course-box-active');

        });
    }

    if(isMobile>800) {
        /**
         * 排序列表内容点击的处理函数
         */
        $('.course_sort_select_li').click(function (event) {
            $('.course_sort_select_li').removeClass('first_select_active');
            $(event.target).addClass('first_select_active');
            $('#course_sort span').text($(event.target).text());
            $('.course_sort_select').toggleClass('course_sort_select_active');
            $('#course_sort').removeClass("select_color");
            $('#sort-img').toggleClass('course_institution_nav_img_active');
            $('.course-box').toggleClass('course-box-active');
        });
    }
    else{
        /**
         * 排序列表内容点击的处理函数
         */
        $('.course_sort_select_li').tap(function (event) {
            $('.course_sort_select_li').removeClass('first_select_active');
            $(event.target).addClass('first_select_active');
            $('#course_sort span').text($(event.target).text());
            $('.course_sort_select').toggleClass('course_sort_select_active');
            $('#course_sort').removeClass("select_color");
            $('#sort-img').toggleClass('course_institution_nav_img_active');
            $('.course-box').toggleClass('course-box-active');
        });
    }

    if(isMobile>800) {
        /*此处ajax动态获取数据库的二级地址*/
        $('.course_all_btn').click(function (event) {
            var args={c:1}
            $.post(areaUrl,args,function(msg){
                //获取地址
                var tpl = document.getElementById('one').innerHTML;
                var html = juicer(tpl, msg);
                $("#area").html('');
                $("#area").append(html);
            },'json');
        });
    }
    else{
        /*此处ajax动态获取数据库的二级地址*/
        $('.course_all_btn').tap(function (event) {
            var args={c:1}
            $.post(areaUrl,args,function(msg){
                //获取地址
                var tpl = document.getElementById('one').innerHTML;
                var html = juicer(tpl, msg);
                $("#area").html('');
                $("#area").append(html);
            },'json');
        });
    }

    if(isMobile>800) {
        $('.course_institution_btn').click(function (event) {
            $("#shopkeeper").html("");
            hiddenPages();
            showLoading();
            var $keywords=$('.register_input').val();
            var args={keywords:$keywords,pages:pages};
            $.post(searchUrl,args,function(msg){
                //获取地址
                var tpl = document.getElementById('shopinfo').innerHTML;
                var html = juicer(tpl, msg);
                if(msg.info==null){
                    $('.search_nofound').removeClass("visualization");
                }else{
                    $('.search_nofound').addClass("visualization");
                    $("#shopkeeper").append(html);
                }
                hiddenLoading();
            },'json');
        });
    }
    else{
        $('.course_institution_btn').tap(function (event) {
            $("#shopkeeper").html("");
            hiddenPages();
            showLoading();
            var $keywords=$('.register_input').val();
            var args={keywords:$keywords,pages:pages};
            $.post(searchUrl,args,function(msg){
                //获取地址
                var tpl = document.getElementById('shopinfo').innerHTML;
                var html = juicer(tpl, msg);
                if(msg.info==null){
                    $('.search_nofound').removeClass("visualization");
                }else{
                    $('.search_nofound').addClass("visualization");
                    $("#shopkeeper").append(html);
                }
                hiddenLoading();
            },'json');
        });
    }

    if(isMobile>800) {
        /*点击默认排序的数据*/
        $('#sort').click(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "sort";
            $greet="";
            $nearby="";
            pages=1;
            $("#shopkeeper").html('');
            $sort=$('#sort').attr('alt');
            getInfo(ajaxFlat.flat);
        });
    }
    else{
        /*点击默认排序的数据*/
        $('#sort').tap(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "sort";
            $greet="";
            $nearby="";
            pages=1;
            $("#shopkeeper").html('');
            $sort=$('#sort').attr('alt');
            getInfo(ajaxFlat.flat);
        });
    }

    /**
     * GPS定位
     */

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
                easyAlert("用户拒绝定位请求.");
                break;
            case error.POSITION_UNAVAILABLE:
                easyAlert("位置信息不可用.");
                break;
            case error.TIMEOUT:
                easyAlert("获取用户位置的请求超时.");
                break;
            case error.UNKNOWN_ERROR:
                easyAlert("出现未知错误.");
                break;
        }
    }

    /**
     * 地理位置函数
     */
    function getGPS(){
        if (navigator.geolocation)
        {
            navigator.geolocation.getCurrentPosition(showPosition,showError);
        }
        else{
            easyAlert("地理位置是该浏览器不支持.");
        }
    }

    getGPS();   //进入时马上访问GPS定位
    if(isMobile>800) {
        /*点击离我最近的数据*/
        $('#nearby').click(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "nearby";
            $greet="";
            pages=1;
            $("#shopkeeper").html('');
            $nearby=$('#nearby').attr('alt');
            //GPS定位
            getGPS();
            ajaxFlat.nearby = true;
            getInfo(ajaxFlat.flat,"nearby");
        });
    }
    else{
        /*点击离我最近的数据*/
        $('#nearby').tap(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "nearby";
            $greet="";
            pages=1;
            $("#shopkeeper").html('');
            $nearby=$('#nearby').attr('alt');
            //GPS定位
            getGPS();
            ajaxFlat.nearby = true;
            getInfo(ajaxFlat.flat,"nearby");
        });
    }

    if(isMobile>800) {
        /*点击最受欢迎的数据*/
        $('#greet').click(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "greet";
            $nearby="";
            pages=1;
            $("#shopkeeper").html('');
            $greet=$('#greet').attr('alt');
            getInfo(ajaxFlat.flat);

        });
    }
    else{
        /*点击最受欢迎的数据*/
        $('#greet').tap(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "greet";
            $nearby="";
            pages=1;
            $("#shopkeeper").html('');
            $greet=$('#greet').attr('alt');
            getInfo(ajaxFlat.flat);

        });
    }

    if(isMobile>800) {
        /*
         * 点击某一个商家的信息
         * 跳转到商家的详细页面（利用sessionStorage发送当前点击的商家ID和分类ID ）
         */
        $('#shopkeeper').on('click','.course_institution_item',function (event) {
            var $sid=$(event.currentTarget).attr('alt');
            sessionStorage.pagecount=$sid;
            sessionStorage.cateid=cate_id;
            location.href= busUrl;

        });
    }
    else{
        /*
         * 点击某一个商家的信息
         * 跳转到商家的详细页面（利用sessionStorage发送当前点击的商家ID和分类ID ）
         */
        $('#shopkeeper').on('tap','.course_institution_item',function (event) {
            var $sid=$(event.currentTarget).attr('alt');
            sessionStorage.pagecount=$sid;
            sessionStorage.cateid=cate_id;
            location.href= busUrl;

        });
    }

    /**
     * 获取数据调用的方法，根据不同的args，获取不同数据
     * @param flat
     */
    function getInfo(flat,first){

        if($(".course_institution_item").size()==0){
            hiddenPages();
        }
        showLoading();
        if(first=="nearby"){
            args={cate_id:cate_id,greet:$greet,nearby:$nearby,x:$x,y:$y,pages:pages};

        }
        else{
            args={area_id:area_id,cate_id:cate_id,greet:$greet,pages:pages};


        }
        $.post(areasUrl,args,function(msg){
            if(first=="nearby"){
                temp={area_id:area_id,cate_id:cate_id,greet:$greet,nearby:$nearby,x:$x,y:$y,pages:pages};
            }
            else{
                temp={area_id:area_id,cate_id:cate_id,greet:$greet,pages:pages};

            }
            var tpl = document.getElementById('shopinfo').innerHTML;
            var html = juicer(tpl, msg);
            if(msg.info==null){

                $('.search_nofound').removeClass("visualization");
                if($(".course_institution_item").size()!=0){
                    $('.search_nofound').addClass("visualization");
                    easyAlert('加载完毕');
                    hiddenPages();
                }
                else{
                    hiddenPages();
                }
            }else{
                $('.search_nofound').addClass("visualization");
                showPages();
                if(flat==ajaxFlat.flat) {//如果ajaxFlat.flat与传给他的变量flat相同才append
                    console.log(msg)
                    $("#shopkeeper").append(html);
                }
            }
            hiddenLoading();
        },'json');

    }
    getInfo(ajaxFlat.flat);


    $('.resbut').click(function(){
        pages=pages+1;
        args=temp;
        if( ajaxFlat.nearby==true){
            getInfo(ajaxFlat.flat,"nearby");

        }
        else{
            getInfo(ajaxFlat.flat);

        }

    });
});