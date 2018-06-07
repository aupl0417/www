/*
 * select
 *
 * Copyright (c) 2015 xyc
 * Licensed under the MIT license.
 */

$(function () {
    var isMobile = $(window).width();

    function showLoading() {
        $('.css-loading').removeClass("hidden");
    }

    function hiddenLoading() {
        $('.css-loading').addClass("hidden");
    }

    function showPages() {
        $('.resbut').removeClass("hidden");
    }

    function hiddenPages() {
        $('.resbut').addClass("hidden");
    }

    //定义变量
    var city = "广州市";
    var area;
    var area_id = "";
    var address;
    var cateid =sessionStorage.cateid;
    var  thirdcateid=sessionStorage.thirdcateid;
    var cate_id = 0;
    var sort;
    var publish = "用户发布";
    var $greet = "";
    var $nearby = "";
    var $x;
    var $y;
    var pages = 1;
    var temp = {};
    var tags = "";
    var ajaxFlat = {};
    ajaxFlat.flat = "cate";
    if (sessionStorage.cateid == undefined) {
        $('#course_constitution span').attr('alt', '');
    } else {
        $('#course_constitution span').attr('alt', sessionStorage.cateid);
    }
    if (sessionStorage.catename == undefined) {
        $('#course_constitution span').text("选择分类");
    } else {
        $('#course_constitution span').text(sessionStorage.catename);
    }


    var args = {};
    $.post(cateUrl, args, function (msg) {
        //获取地址
        var tpl = document.getElementById('catename').innerHTML;
        var html = juicer(tpl, msg);
        $("#cate").html('');
        $("#cate").append(html);
        var select_alt = "[alt='" + sessionStorage.cateid + "']"
        var li = $('.course_constitution_select ').find(select_alt);
        li.addClass("first_select_active");
    }, 'json');


    $('#cate').on('click', 'li', function (event) {
    	twocateid=$(event.target).attr('alt');  //三级分类的ID
    	args = {cateid:twocateid};
   	 	$.post(thirdcateUrl, args, function (msg) {
   	        //获取分类
   	        var tpl = document.getElementById('two').innerHTML;
   	        var html = juicer(tpl, msg);
   	        console.log(msg);
   	        $("#twocate").html('');
   	        $("#twocate").append(html);
   	 }, 'json');
        pages = 1;
        $("#desc").html('');
        $('.course_constitution_select_li').removeClass('first_select_active');
        $('.course_constitution_select_second_li').removeClass('second_select_active');
        $(event.target).addClass('first_select_active');
        $('.course_constitution_select_second').removeClass('select_hidden');
        $('.course_constitution_select_three').addClass('select_hidden');
        //$('.course_constitution_select_second').html('');
        //$('.course_constitution_select_three').html('');

        /*这里获取ajax内容放在course_constitution_select_second里面*/
    });

    $('.course_constitution_select_second').on('click','.course_constitution_select_second_li', function (event) {
    	thirdcateid=$(event.target).attr('alt');  //三级分类的ID
    	args = {cateid:thirdcateid};
    	 $.post(thirdcateUrl, args, function (msg) {
    	        //获取分类
    	        var tpl = document.getElementById('third').innerHTML;
    	        var html = juicer(tpl, msg);
    	        $("#thirdcate").html('');
    	        $("#thirdcate").append(html);
    	 }, 'json');
        $('.course_constitution_select_second_li').removeClass('second_select_active');
        $(event.currentTarget).addClass('second_select_active');
        $('.course_constitution_select_three').removeClass('select_hidden');
        //$('.course_constitution_select_three').html('');
    });
    $('.course_constitution_select_three').on('click','.course_constitution_select_three_li', function (event) {
         $('.course_constitution_select_three').addClass('select_hidden');
         $('.course_constitution_select_second').addClass('select_hidden');
         $('.course_constitution_select_first').toggleClass('course_sort_select_active');
         $('#course_constitution').removeClass("select_color");
         $('#constitution-img').toggleClass('course_institution_nav_img_active');
         $('.course-box').toggleClass('course-box-active');
        $('#course_constitution span').text($(event.target).text());
        thirdcateid=$(event.target).attr('alt');
        sessionStorage.thirdcateid=thirdcateid;
     ajaxFlat = {};

        ajaxFlat.flat = "cate" + thirdcateid;
        getAllInfo(ajaxFlat.flat);
    });


    /*
     * 进入到页面时，页面加载获取的当前分类的所有商家信息
     */


    thirdcateid = $('#course_constitution span').attr('alt');


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

    if (isMobile > 800) {
        /*此处ajax动态获取数据库的二级地址*/
        $('.course_all_btn').click(function (event) {
            var args = {c: 1}
            $.post(areaUrl, args, function (msg) {
                //获取地址
                var tpl = document.getElementById('one').innerHTML;
                var html = juicer(tpl, msg);
                $("#area").append(html);
            }, 'json');
        });
    }
    else {
        /*此处ajax动态获取数据库的二级地址*/
        $('.course_all_btn').tap(function (event) {
            var args = {c: 1}
            $.post(areaUrl, args, function (msg) {
                //获取地址
                var tpl = document.getElementById('one').innerHTML;
                var html = juicer(tpl, msg);
                $("#area").append(html);
            }, 'json');
        });
    }


    if (isMobile > 800) {
        /*地区选择栏第一次*/
        $('#course_local').click(function (event) {
            $("#area").html("");
            if ($('#course_constitution').hasClass("select_color")) {
                $('#constitution-img').toggleClass('course_institution_nav_img_active');
                $('#course_constitution').toggleClass("select_color");
                $('.course_constitution_select_first').toggleClass('course_sort_select_active');
                $('.course_constitution_select_second').addClass('select_hidden');
                $('.course_constitution_select_three').addClass('select_hidden');
                $('.course-box').toggleClass('course-box-active');
            }
            if ($('#course_sort').hasClass("select_color")) {
                $('#sort-img').toggleClass('course_institution_nav_img_active');
                $('#course_sort').toggleClass("select_color");
                $('.course_sort_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            if ($('#course_type').hasClass("select_color")) {
                $('#course_type').toggleClass("select_color");
                $('#type-img').toggleClass('course_institution_nav_img_active');
                $('.course_type_select').toggleClass('course_type_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            $('.course-box').toggleClass('course-box-active');
            $('#course_local').toggleClass("select_color");
            $('#local-img').toggleClass('course_institution_nav_img_active');
            $('.course_institution_nav_all_img').attr('src', "__HIMG__/con (1).png");
            /*此处写ajax获取区*/
            $('.course_local_first_select').toggleClass('course_local_first_select_active');
            if (!$('.course_local_first_select').hasClass('course_local_first_select_active')) {
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
        });
    }
    else {
        /*地区选择栏第一次*/
        $('#course_local').tap(function (event) {
            $("#area").html("");
            if ($('#course_constitution').hasClass("select_color")) {
                $('#constitution-img').toggleClass('course_institution_nav_img_active');
                $('#course_constitution').toggleClass("select_color");
                $('.course_constitution_select_first').toggleClass('course_sort_select_active');
                $('.course_constitution_select_second').addClass('select_hidden');
                $('.course_constitution_select_three').addClass('select_hidden');
                $('.course-box').toggleClass('course-box-active');
            }
            if ($('#course_sort').hasClass("select_color")) {
                $('#sort-img').toggleClass('course_institution_nav_img_active');
                $('#course_sort').toggleClass("select_color");
                $('.course_sort_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            if ($('#course_type').hasClass("select_color")) {
                $('#course_type').toggleClass("select_color");
                $('#type-img').toggleClass('course_institution_nav_img_active');
                $('.course_type_select').toggleClass('course_type_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            $('.course-box').toggleClass('course-box-active');
            $('#course_local').toggleClass("select_color");
            $('#local-img').toggleClass('course_institution_nav_img_active');
            $('.course_institution_nav_all_img').attr('src', "__HIMG__/con (1).png");
            /*此处写ajax获取区*/
            $('.course_local_first_select').toggleClass('course_local_first_select_active');
            if (!$('.course_local_first_select').hasClass('course_local_first_select_active')) {
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
        });
    }
    if (isMobile > 800) {
        $('.course_local_first_select').on('click', 'li', function (event) {

            $("#secondArea").html('');
            area = $(event.target).text();
            area_id = $(event.target).attr('alt');
            if (area_id == 0) {
                pages = 1;
                $('#course_local span').text("全部地区")
                $("#shopkeeper").html('');
                $(".course_local_first_select_li").removeClass('first_select_active');
                $('.course_local_second_select').addClass('course_local_second_select_hidden');
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').removeClass("select_color");
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course-box').toggleClass('course-box-active');
                ajaxFlat = {};
                ajaxFlat.flat = "area" + area_id;
                getAllInfo(ajaxFlat.flat);
            } else {
                var args = {area_id: area_id};
                $.post(secondUrl, args, function (msg) {
                    //获取地址
                    var tpl = document.getElementById('second').innerHTML;
                    var html = juicer(tpl, msg);
                    $("#secondArea").append(html);
                }, 'json');
                $('.course_local_second_select').removeClass('course_local_second_select_hidden');
            }
            /*此处ajax获取二级地址*/
            $(".course_local_first_select_li").removeClass('first_select_active');
            $(event.target).addClass('first_select_active');

        });
    }
    else {
        $('.course_local_first_select').on('tap', 'li', function (event) {

            $("#secondArea").html('');
            area = $(event.target).text();
            area_id = $(event.target).attr('alt');
            if (area_id == 0) {
                pages = 1;
                $('#course_local span').text("全部地区")
                $("#shopkeeper").html('');
                $(".course_local_first_select_li").removeClass('first_select_active');
                $('.course_local_second_select').addClass('course_local_second_select_hidden');
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').removeClass("select_color");
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course-box').toggleClass('course-box-active');
                ajaxFlat = {};
                ajaxFlat.flat = "area" + area_id;
                getAllInfo(ajaxFlat.flat);
            } else {
                var args = {area_id: area_id};
                $.post(secondUrl, args, function (msg) {
                    //获取地址
                    var tpl = document.getElementById('second').innerHTML;
                    var html = juicer(tpl, msg);
                    $("#secondArea").append(html);
                }, 'json');
                $('.course_local_second_select').removeClass('course_local_second_select_hidden');
            }
            /*此处ajax获取二级地址*/
            $(".course_local_first_select_li").removeClass('first_select_active');
            $(event.target).addClass('first_select_active');

        });
    }

    if (isMobile > 800) {
        $('.course_local_second_select').on('click', 'li', function () {
            ajaxFlat = {};
            pages = 1;
            $("#desc").html('');
            $('#course_local span').text($(event.target).text());
            $('.course_local_second_select').addClass('course_local_second_select_hidden');
            $('.course_local_first_select').toggleClass('course_local_first_select_active');
            $('#course_local').removeClass("select_color");
            $('#local-img').toggleClass('course_institution_nav_img_active');
            $('.course-box').toggleClass('course-box-active');
            area_id = $(event.target).attr('alt');
            ajaxFlat.flat = "area" + area_id;
            getAllInfo(ajaxFlat.flat);

        });
    }
    else {
        $('.course_local_second_select').on('tap', 'li', function () {
            ajaxFlat = {};
            pages = 1;
            $("#desc").html('');
            $('#course_local span').text($(event.target).text());
            $('.course_local_second_select').addClass('course_local_second_select_hidden');
            $('.course_local_first_select').toggleClass('course_local_first_select_active');
            $('#course_local').removeClass("select_color");
            $('#local-img').toggleClass('course_institution_nav_img_active');
            $('.course-box').toggleClass('course-box-active');
            area_id = $(event.target).attr('alt');
            ajaxFlat.flat = "area" + area_id;
            getAllInfo(ajaxFlat.flat);

        });
    }

    if (isMobile > 800) {
        /*排序*/
        $('#course_sort').click(function (event) {
            if ($('#course_constitution').hasClass("select_color")) {
                $('#constitution-img').toggleClass('course_institution_nav_img_active');
                $('#course_constitution').toggleClass("select_color");
                $('.course_constitution_select_first').toggleClass('course_sort_select_active');
                $('.course_constitution_select_second').addClass('select_hidden');
                $('.course_constitution_select_three').addClass('select_hidden');
                $('.course-box').toggleClass('course-box-active');
            }
            if ($('#course_local').hasClass("select_color")) {
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').toggleClass("select_color");
                $('.course-box').toggleClass('course-box-active');
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course_institution_nav_all_img').attr('src', "__HIMG__/icon (1).png");
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
            if ($('#course_type').hasClass("select_color")) {
                $('#course_type').toggleClass("select_color");
                $('#type-img').toggleClass('course_institution_nav_img_active');
                $('.course_type_select').toggleClass('course_type_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            $('#sort-img').toggleClass('course_institution_nav_img_active');
            $('#course_sort').toggleClass("select_color");
            $('.course_sort_select').toggleClass('course_sort_select_active');
            $('.course-box').toggleClass('course-box-active');

        });
    }
    else {
        /*排序*/
        $('#course_sort').tap(function (event) {
            if ($('#course_constitution').hasClass("select_color")) {
                $('#constitution-img').toggleClass('course_institution_nav_img_active');
                $('#course_constitution').toggleClass("select_color");
                $('.course_constitution_select_first').toggleClass('course_sort_select_active');
                $('.course_constitution_select_second').addClass('select_hidden');
                $('.course_constitution_select_three').addClass('select_hidden');
                $('.course-box').toggleClass('course-box-active');
            }
            if ($('#course_local').hasClass("select_color")) {
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').toggleClass("select_color");
                $('.course-box').toggleClass('course-box-active');
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course_institution_nav_all_img').attr('src', "__HIMG__/icon (1).png");
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
            if ($('#course_type').hasClass("select_color")) {
                $('#course_type').toggleClass("select_color");
                $('#type-img').toggleClass('course_institution_nav_img_active');
                $('.course_type_select').toggleClass('course_type_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            $('#sort-img').toggleClass('course_institution_nav_img_active');
            $('#course_sort').toggleClass("select_color");
            $('.course_sort_select').toggleClass('course_sort_select_active');
            $('.course-box').toggleClass('course-box-active');

        });
    }

    if (isMobile > 800) {
        $('.course_sort_select_li').click(function (event) {
            $('.course_sort_select_li').removeClass('first_select_active');
            $(event.target).addClass('first_select_active');
            $('#course_sort span').text($(event.target).text());
            $('.course_sort_select').toggleClass('course_sort_select_active');
            $('#course_sort').removeClass("select_color");
            $('#sort-img').toggleClass('course_institution_nav_img_active');
            $('.course-box').toggleClass('course-box-active');
        });


        $('#course_constitution').click(function () {
            if ($('#course_type').hasClass("select_color")) {
                $('#course_type').toggleClass("select_color");
                $('#type-img').toggleClass('course_institution_nav_img_active');
                $('.course_type_select').toggleClass('course_type_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            if ($('#course_local').hasClass("select_color")) {
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').toggleClass("select_color");
                $('.course-box').toggleClass('course-box-active');
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course_institution_nav_all_img').attr('src', "__HIMG__/icon (1).png");
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
            if ($('#course_sort').hasClass("select_color")) {
                $('#sort-img').toggleClass('course_institution_nav_img_active');
                $('#course_sort').toggleClass("select_color");
                $('.course_sort_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            $('#constitution-img').toggleClass('course_institution_nav_img_active');
            $('#course_constitution').toggleClass("select_color");
            
            $('.course_constitution_select_first').toggleClass('course_sort_select_active');
            $('.course_constitution_select_second').addClass('select_hidden');
            $('.course_constitution_select_three').addClass('select_hidden');
            $('.course-box').toggleClass('course-box-active');

        });
    }
    else {
        $('.course_sort_select_li').tap(function (event) {
            $('.course_sort_select_li').removeClass('first_select_active');
            $(event.target).addClass('first_select_active');
            $('#course_sort span').text($(event.target).text());
            $('.course_sort_select').toggleClass('course_sort_select_active');
            $('#course_sort').removeClass("select_color");
            $('#sort-img').toggleClass('course_institution_nav_img_active');
            $('.course-box').toggleClass('course-box-active');
        });
        $('#course_constitution').tap(function () {
            if ($('#course_type').hasClass("select_color")) {
                $('#course_type').toggleClass("select_color");
                $('#type-img').toggleClass('course_institution_nav_img_active');
                $('.course_type_select').toggleClass('course_type_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            if ($('#course_local').hasClass("select_color")) {
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').toggleClass("select_color");
                $('.course-box').toggleClass('course-box-active');
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course_institution_nav_all_img').attr('src', "__HIMG__/icon (1).png");
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
            if ($('#course_sort').hasClass("select_color")) {
                $('#sort-img').toggleClass('course_institution_nav_img_active');
                $('#course_sort').toggleClass("select_color");
                $('.course_sort_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            $('#constitution-img').toggleClass('course_institution_nav_img_active');
            $('#course_constitution').toggleClass("select_color");
            $('.course_constitution_select_first').toggleClass('course_sort_select_active');
            $('.course_constitution_select_second').addClass('select_hidden');
            $('.course_constitution_select_three').addClass('select_hidden');
            $('.course-box').toggleClass('course-box-active');

        });
    }

    if (isMobile > 800) {
        $('#course_type').click(function () {
            if ($('#course_constitution').hasClass("select_color")) {
                $('#constitution-img').toggleClass('course_institution_nav_img_active');
                $('#course_constitution').toggleClass("select_color");
                $('.course_constitution_select_first').toggleClass('course_sort_select_active');
                $('.course_constitution_select_second').addClass('select_hidden');
                $('.course_constitution_select_three').addClass('select_hidden');
                $('.course-box').toggleClass('course-box-active');
            }
            if ($('#course_local').hasClass("select_color")) {
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').toggleClass("select_color");
                $('.course-box').toggleClass('course-box-active');
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course_institution_nav_all_img').attr('src', "__HIMG__/icon (1).png");
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
            if ($('#course_sort').hasClass("select_color")) {
                $('#sort-img').toggleClass('course_institution_nav_img_active');
                $('#course_sort').toggleClass("select_color");
                $('.course_sort_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            $('#course_type').toggleClass("select_color");
            $('#type-img').toggleClass('course_institution_nav_img_active');
            $('.course_type_select').toggleClass('course_type_select_active');
            $('.course-box').toggleClass('course-box-active');

        });
    }
    else {
        $('#course_type').tap(function () {
            if ($('#course_constitution').hasClass("select_color")) {
                $('#constitution-img').toggleClass('course_institution_nav_img_active');
                $('#course_constitution').toggleClass("select_color");
                $('.course_constitution_select_first').toggleClass('course_sort_select_active');
                $('.course_constitution_select_second').addClass('select_hidden');
                $('.course_constitution_select_three').addClass('select_hidden');
                $('.course-box').toggleClass('course-box-active');
            }
            if ($('#course_local').hasClass("select_color")) {
                $('.course_local_first_select').toggleClass('course_local_first_select_active');
                $('#course_local').toggleClass("select_color");
                $('.course-box').toggleClass('course-box-active');
                $('#local-img').toggleClass('course_institution_nav_img_active');
                $('.course_institution_nav_all_img').attr('src', "__HIMG__/icon (1).png");
                $('.course_local_second_select').addClass('course_local_second_select_hidden')
            }
            if ($('#course_sort').hasClass("select_color")) {
                $('#sort-img').toggleClass('course_institution_nav_img_active');
                $('#course_sort').toggleClass("select_color");
                $('.course_sort_select').toggleClass('course_sort_select_active');
                $('.course-box').toggleClass('course-box-active');
            }
            $('#course_type').toggleClass("select_color");
            $('#type-img').toggleClass('course_institution_nav_img_active');
            $('.course_type_select').toggleClass('course_type_select_active');
            $('.course-box').toggleClass('course-box-active');

        });
    }

    if (isMobile > 800) {
        $('.course_type_select_li').click(function (event) {
            ajaxFlat = {};
            pages = 1;
            $("#desc").html('');
            $('.course_type_select_li').removeClass('first_select_active');
            $('#type-img').toggleClass('course_institution_nav_img_active');
            $(event.target).addClass('first_select_active');
            $('#course_type span').text($(event.target).text());
            publish = $(event.target).text();
            ajaxFlat.flat = "type" + publish;
            getAllInfo(ajaxFlat.flat);
            $('.course_type_select').toggleClass('course_type_select_active');
            $('#course_type').removeClass("select_color");
            $('#type-img').toggleClass('course_type_nav_img_active');
            $('.course-box').toggleClass('course-box-active');
        });
    }
    else {
        $('.course_type_select_li').tap(function (event) {
            ajaxFlat = {};
            pages = 1;
            $("#desc").html('');
            $('.course_type_select_li').removeClass('first_select_active');
            $('#type-img').toggleClass('course_institution_nav_img_active');
            $(event.target).addClass('first_select_active');
            $('#course_type span').text($(event.target).text());
            publish = $(event.target).text();
            ajaxFlat.flat = "type" + publish;
            getAllInfo(ajaxFlat.flat);
            $('.course_type_select').toggleClass('course_type_select_active');
            $('#course_type').removeClass("select_color");
            $('#type-img').toggleClass('course_type_nav_img_active');
            $('.course-box').toggleClass('course-box-active');
        });
    }


    if (isMobile > 800) {
        /*点击默认排序的数据*/
        $('#sort').click(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "sort";
            $greet = "";
            $nearby = "";
            pages = 1;
            $("#desc").html('');
            $sort = $('#sort').attr('alt');
            getAllInfo(ajaxFlat.flat);
        });
    }
    else {
        /*点击默认排序的数据*/
        $('#sort').tap(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "sort";
            $greet = "";
            $nearby = "";
            pages = 1;
            $("#desc").html('');
            $sort = $('#sort').attr('alt');
            getAllInfo(ajaxFlat.flat);
        });
    }

    if (isMobile > 800) {
        /*点击最受欢迎的数据*/
        $('#greet').click(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "greet";
            $nearby = "";
            pages = 1;
            $("#desc").html('');
            $greet = $('#greet').attr('alt');
            getAllInfo(ajaxFlat.flat);

        });
    }
    else {
        /*点击最受欢迎的数据*/
        $('#greet').tap(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "greet";
            $nearby = "";
            pages = 1;
            $("#desc").html('');
            $greet = $('#greet').attr('alt');
            getAllInfo(ajaxFlat.flat);

        });
    }


    /**
     * GPS定位
     */

    function showPosition(position) {
        $x = position.coords.longitude;
        $y = position.coords.latitude;
    }

    function showError(error) {
        switch (error.code) {
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

    function getGPS() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition, showError);
        }
        else {
            easyAlert("地理位置是该浏览器不支持.");
        }
    }

    getGPS();   //进入时马上访问GPS定位
    if (isMobile > 800) {
        /*点击离我最近的数据*/
        $('#nearby').click(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "nearby";

            $greet = "";
            pages = 1;
            $("#desc").html('');
            $nearby = $('#nearby').attr('alt');
            //GPS定位
            getGPS();
            ajaxFlat.nearby = true;
            getAllInfo(ajaxFlat.flat, "nearby");
        });
    }
    else {
        /*点击离我最近的数据*/
        $('#nearby').tap(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "nearby";

            $greet = "";
            pages = 1;
            $("#desc").html('');
            $nearby = $('#nearby').attr('alt');
            //GPS定位
            getGPS();
            ajaxFlat.nearby = true;
            getAllInfo(ajaxFlat.flat, "nearby");
        });
    }

    if (isMobile > 800) {
        $('.course_institution_btn').click(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "institution";
            $("#desc").html('');
            hiddenPages();
            showLoading();
            var $keywords = $('.register_input').val();
            var args = {keywords: $keywords};
            $.post(searchUrl, args, function (msg) {
                var tpl = document.getElementById('shopAndUser').innerHTML;
                var data = {}
                data.info = msg;
                var html = juicer(tpl, data);
                ////if(msg.info==null){
                //$("#desc").html('');
                //}else{
                hiddenPages();
                $("#desc").html('');
                $("#desc").append(html);
                //	}
                hiddenLoading();
            }, 'json');
        });

    }
    else {
        $('.course_institution_btn').tap(function (event) {
            ajaxFlat = {};
            ajaxFlat.flat = "institution";
            $("#desc").html('');
            hiddenPages();
            showLoading();
            var $keywords = $('.register_input').val();
            var args = {keywords: $keywords};
            $.post(searchUrl, args, function (msg) {
                var tpl = document.getElementById('shopAndUser').innerHTML;
                var data = {}
                data.info = msg;
                var html = juicer(tpl, data);
                ////if(msg.info==null){
                //$("#desc").html('');
                //}else{
                hiddenPages();
                $("#desc").html('');
                $("#desc").append(html);
                //	}
                hiddenLoading();
            }, 'json');
        });

    }

    function getAllInfo(flat, first) {
        if ($(".section_item").size() == 0) {
            hiddenPages();
        }
        showLoading();
        if (publish == "用户发布") {
            if (first == "nearby") {
                args = {cate_id: thirdcateid, nearby: $nearby, greet: $greet, x: $x, y: $y, pages: pages};
                console.log(args);
            }
            else {
                args = {cate_id: thirdcateid, area_id: area_id, greet: $greet, pages: pages};
                console.log(args);
            }
            $.post(userCateUrl, args, function (msg) {
                if (first == "nearby") {
                    temp = {
                        cate_id: thirdcateid,
                        area_id: area_id,
                        nearby: $nearby,
                        greet: $greet,
                        x: $x,
                        y: $y,
                        pages: pages
                    };
                }
                else {
                    temp = {cate_id: thirdcateid, area_id: area_id, greet: $greet, pages: pages};
                }
                var tpl = document.getElementById('user').innerHTML;
                var html = juicer(tpl, msg);
                if (msg.info == null) {
                    $('.search_nofound').removeClass("visualization");
                    if ($(".section_item").size() != 0) {
                        $('.search_nofound').addClass("visualization");
                        easyAlert("加载完毕");
                        hiddenPages();
                    }
                    else {
                        hiddenPages();
                    }
                } else {
                    $('.search_nofound').addClass("visualization");
                    showPages();
                    if (flat == ajaxFlat.flat) {
                        $("#desc").append(html);
                    }
                }
                hiddenLoading();
            }, 'json');
        } else {
            if (first == "nearby") {
                args = {cate_id: thirdcateid, nearby: $nearby, greet: $greet, x: $x, y: $y, pages: pages};

            }
            else {
                args = {cate_id: thirdcateid, area_id: area_id, greet: $greet, pages: pages};
            }
            $.post(shopCateUrl, args, function (msg) {
                if (first == "nearby") {
                    temp = {
                        cate_id: thirdcateid,
                        area_id: area_id,
                        nearby: $nearby,
                        greet: $greet,
                        x: $x,
                        y: $y,
                        pages: pages
                    };
                }
                else {
                    temp = {cate_id: thirdcateid, area_id: area_id, greet: $greet, pages: pages};
                }
                var tpl = document.getElementById('shop').innerHTML;
                var html = juicer(tpl, msg);
                if (msg.info == null) {
                    $('.search_nofound').removeClass("visualization");
                    if ($(".section_item").size() != 0) {
                        $('.search_nofound').addClass("visualization");
                        easyAlert("加载完毕");
                        hiddenPages();
                    }
                    else {
                        hiddenPages();
                    }
                } else {
                    $('.search_nofound').addClass("visualization");
                    showPages();
                    if (flat == ajaxFlat.flat) {
                        $("#desc").append(html);
                    }
                }
                hiddenLoading();
            }, 'json');
        }
    }

    getAllInfo(ajaxFlat.flat);
    $('.resbut').click(function () {
        pages = pages + 1;
        args = temp;

        if (ajaxFlat.nearby == true) {
            getAllInfo(ajaxFlat.flat, "nearby");
        }
        else {
            getAllInfo(ajaxFlat.flat);

        }
    });
});
