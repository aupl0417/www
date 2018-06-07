(function() {

    // 删除sessionStoarge中的跳转url
    if (window.sessionStorage) {
        sessionStorage.removeItem("shop.finishDetailBackUrl");
    }

    // 特色按钮
    $("#switchFeatureBtn").click(switchFeature);
    $("#comeBackInfoBtn").click(function() {
        switchInfo(false);
    });
    $("#saveFeaturesBtn").click(function() {
        switchInfo(true);
    });

    /**
     * 课程价格
     * */
    $('.course_select_price_input').on("blur", function() {
        $('#price').css("opacity", 1);
        var price = $(this).val();
        if (!isNaN(price)) {
            if (price > 0) {
                $('#price').html(price);
            }
            else{
                $('#price').html("输入价格");
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
        $(this).css("opacity", 1);
        $('#price').css("opacity", 0);
    })
    /**
     * 特技Input框
     */
    $("input").on("focus", function (event) {
        $("#top_header").addClass("fixedIOSBug");
        $(event.target).next().removeClass("op_0");
    });
    $("input").on("blur", function (event) {
        $(event.target).next().addClass("op_0");
        $("#top_header").removeClass("fixedIOSBug");
    });
    $("textarea").on("focus", function (event) {
        $("#top_header").addClass("fixedIOSBug");
        $(event.target).next().removeClass("op_0");
    });
    $("textarea").on("blur", function (event) {
        $(event.target).next().addClass("op_0");
        $("#top_header").removeClass("fixedIOSBug");
    });

    $('#course_select_time').bind('change', function() {
        var time = $('#course_select_time').val();
        $('#time').html($("#course_select_time_option_" + time).html());
    });

    $(".input_close_icon").click(function (event) {
        $(event.target).prev().val("");
    });
    $('#course_select_model').bind('change', function() {
        $('#model').html($('#course_select_model option').not(function(){
            return !this.selected;
        })[0].innerHTML);
    });
    $("#test").click(function () {
        var height = $(window).scrollTop()+200;
        $('.alert_bg ').toggleClass("show");
        $('#loading').css("top",height+"px");
        $(window).on("touchmove",function (event) {
            event.preventdefault()
        })
    });

    $('.course_slide').on('click', 'li', function(event,arg) {

        $("#allcate").html("");
        if ($(event.target).attr('src') != null) {
            cate_id = $(event.target).attr("alt");
            sessionStorage.cate_id = cate_id;
            //sessionStorage.catename = $(event.target).next().text();
            var old_url = $('.course_active img').attr('src');
            var alt_url;
            if(old_url.split('a.').length>1){
                alt_url = old_url.split('a.')[0] + '.png';
            }
            else{
                alt_url = old_url.split('a.')[0];
            }
            $('.course_active img').attr('src', alt_url);
            $('.course_active').removeClass('course_active');
            if (!$(event.target).hasClass('course_active')) {
                var url = $(event.target).attr('src');
                var new_url = url.split('.')[0] + 'a.png';
                $(event.target).attr('src', new_url);
                $(event.target).parent().addClass('course_active');
            }

            if(arg!="people"){
                $('#local').html("请选择").css("color","#d7d7d7");
                sessionStorage.sid = undefined;
                sessionStorage.nickname = undefined;
                var args={cateid:cate_id};
                $.post(cateUrl,args,function(msg){
                    //获取地址
                    var tpl = document.getElementById('er').innerHTML;
                    var html = juicer(tpl, msg);
                    $('.select_feature_header').text(msg.catename);
                    $("#allcate").append(html);
                },'json');
                $('.select_feature').removeClass('hidden');
            }



        }

    });


    $('#zone').on("change", function (event) {
        $('#first_new_p').text($('#zone option').not(function(){
            return !this.selected;
        })[0].innerHTML);
        $('#second_new_p').text('请选择');
    });
    $('#areaid').on("change", function (event) {
        $('#second_new_p').text($('#areaid option').not(function(){
            return !this.selected;
        })[0].innerHTML);

    });

    // 特性路径
    $('.course_select_ul').on('click','li',function (event) {
        if($(event.target).hasClass("course_select_ul_active")){
            $(event.target).removeClass("course_select_ul_active");
        }
        else{
            if ($(".course_select_ul_active").length >= 3) {
                return easyAlert("只能选择3个");
                // alert("只能选择3个")
            }
            $(event.target).addClass("course_select_ul_active");
        }
    });

    $('.course_feature_btn').click(function (event) {
        var value = $('#feature_input').val();
        if(value==""){
            easyAlert("内容不能为空");
            // alert("内容不能为空")
        }
        else if(value.length>4){
            easyAlert("长度不能大于4");
            // alert("长度不能大于4")
        }
        else {
            $('.course_select_ul').append('<li>' + value + '</li>');
        }
    });

    // 点击图片时间
    $('#image').on("click", function (event) {
        $('#file').trigger("click");
    });

    // 检查有没有发布权限有没有达到一天发布的上限
    //$(".loading").removeClass("hidden");
    $(".css-loading").removeClass("hidden");
    $.post(g.checkCanSendUrl, function(data) {
        console.log("today:", data);

        if (data.status != 200) {
            // 这是没有完善商家资料
            if (data.status == 403) {
                var win = new alertWin();
                win.setText(data.msg);
                win.show();
                win.work(function(){
                    // 保存当前这个路径
                    if (window.sessionStorage) {
                        sessionStorage.setItem("shop.finishDetailBackUrl", location.href);
                        location = g.shopEditorUrl;
                    }
                });

                return;
            }

            // 有可能达到一天发布的上限
            easyAlert(data.msg);
        }

        //$(".loading").addClass("hidden");
        $(".css-loading").addClass("hidden");
    }, "json");


    // 填完价格和优惠之后
    $("#priceSwitchBtn").on("click", function() {
        $(".course_price_box ").addClass("hidden");
        var price = $("#price").val();
        var preferent = $("#course_textarea").val();

        if (price != "") {
            $(".price_preview_up").html(g.pricePreviewUpIcon + price);
        }

        if (preferent != "") {
            $(".price_preview_down").html(g.pricePreviewDownIcon + preferent);
        }
    });

    // 初始化一个分类
    //$("#categoryPanel").html(sessionStorage.name);
})();


(function (){
    // 发布课程按钮点击之后
    $("#submitBtn").bind("click", finish);

    //
    var multi_file = document.getElementById('file'),                 // 获取上传控件
            slice = Array.prototype.slice;                                      // 获取数组slice原型方法
    multi_file.addEventListener('change', function (e) {                    // 监听上传控件数据变化
        var files = slice.call(multi_file.files, 0);                        // 将FileList对象转为数组获取forEach方法
        if(files[0].size>1*1024*1024){
            easyAlert("文件太大了");
            files[0]=null;
        }
        else if (files[0].type.toLowerCase().match(/(jpg)|(jpeg)|(png)|(bmp)/)) {
            // 将头像改成图片
            var objUrl = getObjectURL(this.files[0]) ;
            console.log("objUrl = "+objUrl) ;
            if (objUrl) {
                $("#image").attr("src", objUrl) ;
            }
        }
        else{
            easyAlert("只能选择图片格式：jpg、png、bmp");
            files[0]=null;
        }
    });
})();

// <!-- 基本地区变换 -->
(function() {
    $("#zone").change(function () {
        var id = $(this).val();
        $.get(g.getAreaIdUrl, {"parentid": id}, function(data) {
            if (data.status != "200") {
                easyAlert(data.msg);
                return;
            }
            var html = '<option value="0">请选择</option>';
            for (var i in data.data) {
                html = html + '<option value="' + data.data[i].id + '">'+data.data[i].areaname+'</option>';
            }
            $("#areaid").html(html);
        }, "json");
    });
})();


function hiddenTagById(id){
    var selector = "#"+id;
    $(selector).addClass("hidden");
}

function showTagById(id){
    var selector = "#"+id;
    $(selector).removeClass("hidden");
}
// 当商家选择了培训类型时，为培训类型p标签添加的点击事件
function trainTypeClick() {
    $('#swipeSlide').find('img').each(function () {
        if (this.alt == sessionStorage.cate_id) {
            $(this).click();
        }
    });
}
function echoOperation () {
    var categoryPanel = document.getElementById('categoryPanel');
    var trainType = document.getElementById('train-type');
    $(categoryPanel).parent().removeClass('hidden');
    $(categoryPanel).html(name);
    trainType.onclick = trainTypeClick;
}

/**
 * 切换到特点选择界面
 */
function switchFeature() {
    $(window).scrollTop(0,0);
    $("#info").addClass("hidden");
    $("#feature").removeClass("hidden");
}

/**
 * 切换回信息页面
 */
function switchInfo(isSave) {
    if (isSave) {
        var features = "";
        if ($(".course_select_ul_active").length > 3) {
            return easyAlert("只能选择3个");
            // alert("只能选择3个")
            // return
        }
        $(".course_select_ul_active").each(function() {
            features = features + "|" + $(this).html()
        });
        $("#featureInput").val(features.substring(1));
        $("#featurePanel").html(features.substring(1)); // 删除了 请选择
    }
    $("#feature").addClass("hidden");
    $("#info").removeClass("hidden");
}
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
/**
 * 点击发布按钮
 * @param 接受一个参数，true代表发布，false代表预览，默认是true
 */
function finish() {

    var isPreview = arguments[1] || false;

    var check = true;
    // 获取输入
    var cateid = sessionStorage.treecateid;
    var phoneTel = $.trim($("#phone").val());
    var areaDetail = $.trim($("#address").val());
    var price = $.trim($("#price").val());
    var mode = $("#course_select_model").val();
    var tags = $.trim($("#featureInput").val());
    var title = $.trim($("#title").val());
    var content = $.trim($("#content").val());
    var areaid = $("#areaid").val();
    var overtime = $("#course_select_time").val();

    var preferent = $.trim($("#course_textarea").val());
    var teacher_age = $.trim($("#year").val());
    var teacher_exp = $.trim($("#experiment").val());
    var teacher_remark = $.trim($("#brief_info").val());
    var teacher_feature = $.trim($("#special").val());

    // 检验
    if (!cateid) {
        check = false;
        $("#course_select_type").removeClass("hidden");
    } else {
        $("#course_select_type").addClass("hidden");
    }

    if (!/^1\d{10}$/.test(phoneTel) && !/^\d{3,4}\-\d{7,8}$/.test(phoneTel)) {
        check = false;
        $("#course_phone").removeClass("op_0");
        $("#phone").trigger("click");
    } else {
        $("#course_phone").addClass("op_0");
    }

    if (areaid==0) {
        check = false;
        $("#area_err").removeClass("op_0");
    }
    else {
        $("#area_err").addClass("op_0");

    }

    if (!areaDetail) {
        check = false;
        $("#course_address").removeClass("op_0");
        $("#address").trigger("click");
    }
    else {
        $("#course_address").addClass("op_0");
    }

    if (!tags) {
        check = false;
        showTagById("course_select_feature");
    } else {
        hiddenTagById("course_select_feature");
    }

    if (areaDetail.length > 32) {
        check = false;
        return easyAlert("联系地址不能超过32个字符")
        // return alert("联系地址不能超过32个字符")
    }

    if (price === "" || isNaN(price) || price < 0) {

        check = false;
        $("#course_release_err").html("课程价格不能为空").removeClass("hidden");
        // return alert("请选择价钱")
    } else {
        $("#course_release_err").addClass("hidden");
    }

    if (mode == 0) {
        check = false;
        showTagById("course_select_attendtime");
    } else {
        hiddenTagById("course_select_attendtime");
    }

    if(overtime==0){
        $("#time_err").html("截止日期不能为空").removeClass("hidden");
    } else{
        $("#time_err").addClass("hidden");
    }

    if (title==""||title==null||title.length > 30) {
        check = false;
        $("#course_title").html("组团标题字数为1至30个").removeClass("op_0");
        $("#title").trigger("focus");

    } else {
        $("#course_title").addClass("op_0");
    }

    if (content==""||content==null) {
        check = false;
        $("#content").trigger("click");
        $("#course_content").html("课程内容不能为空").removeClass("op_0");
    } else {
        $("#course_content").addClass("op_0");
    }

    var files = document.getElementById("file").files
    if (files.length <= 0) {
        check = false;
        $("#photo_err").removeClass("op_0");
//            $("#file").trigger("click");
    }
    else {
        $("#photo_err").addClass("op_0");

    }

    if (!teacher_age) {
        check = false;
        $("#course_teacher_year").removeClass("op_0");
    } else {
        $("#course_teacher_year").addClass("op_0");
    }

    if (!teacher_exp) {
        check = false;
        $("#course_experiment").removeClass("op_0");
    } else {
        $("#course_experiment").addClass("op_0");
    }

    if (!teacher_remark) {
        check = false;
        $("#teacher_brief_info").removeClass("op_0");
    } else {
        $("#teacher_brief_info").addClass("op_0");
    }

    if (!teacher_feature) {
        check = false;
        $("#course_special").removeClass("op_0");
    } else {
        $("#course_special").addClass("op_0");
    }

    if (check) {

        var args = {
            "cateid": cateid,
            "phone_tel": phoneTel,
            "area_detail": areaDetail,
            "price": price,
            "mode": mode,
            "tags": tags,
            "title": title,
            "content": content,
            "areaid": areaid,
            "overtime": overtime,

            "preferent":        preferent,
            "teacher_age":      teacher_age,
            "teacher_exp":      teacher_exp,
            "teacher_remark":   teacher_remark,
            "teacher_feature":  teacher_feature
        };

        // 这里拼接字符串
        var data = new FormData();
        for (var i in args) {
            data.append(i, args[i]);
        }
        data.append("file", files[0]);

        // 这里是发布而不是预览
        if (isPreview) {
            return echoPreView(args);
        }

        // 禁用上传操作
        $("#submitBtn").unbind("click", finish)
        easyAlert('发布中。。。');

        // 开始上传信息
        $.ajax({
            url: g.addCourseUrl,
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'json',
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            success: function (data, textStatus, jqXHR) {
                if (data.status != "200") {
                    $("#submitBtn").bind("click", finish)
                    return easyAlert(data.msg)
                }

                // 显示完成框
                $("[data-id=courseSuccessPanel]").removeClass("hidden");
            },
            error: function (jqXHR, textStatus, errorThrown) {
                easyAlert('ERRORS: ' + textStatus);
                $("#submitBtn").bind("click", finish)
            }
        });

    }

    return false;
}

/**
 * 预览回显数据
 */
function echoPreView(args) {
        //var args = {
        //    "cateid": cateid,
        //    "phone_tel": phoneTel,
        //    "area_detail": areaDetail,
        //    "price": price,
        //    "mode": mode,
        //    "tags": tags,
        //    "title": title,
        //    "content": content,
        //    "areaid": areaid,
        //    "overtime": overtime,

        //    "preferent":        preferent,
        //    "teacher_age":      teacher_age,
        //    "teacher_exp":      teacher_exp,
        //    "teacher_remark":   teacher_remark,
        //    "teacher_feature":  teacher_feature
        //};

    console.log(args);

    // 通用数据回显
    var newArgs = {
        "title":            args.title,
        "teacher_age":      args.teacher_age,
        "price":            args.price,
        "mode":             $('#course_select_model option:selected').text(),
        "address":          args.area_detail,
        "phone":            args.phone_tel,
        "overdate":         $('#course_select_time option:selected').text().split(" ")[0],
        "tags":             args.tags,
        "content":          args.content,
        "teacher_feature":  args.teacher_feature,
        "teacher_age":      args.teacher_age,
        "teacher_exp":      args.teacher_exp,
        "teacher_remark":   args.teacher_remark,
    }

    console.log(newArgs);

    for (var i in newArgs) {
        $("#preview_" + i).html(newArgs[i]);
    }

    // 特殊数据回显
    $("#preview_twocatename").html(sessionStorage.twocatename);

    var objUrl = getObjectURL(document.getElementById("file").files[0]) ;
    $("#preview_environ").attr("src", objUrl) ;
    $("#preview_environ").val(objUrl) ;

    var preferent = '';
    if (args.preferent) {
        preferent = args.preferent;
        $("#preview_preferent_icon").removeClass("hidden");
    } else {
        $("#preview_preferent_icon").addClass("hidden");
    }

    $("#preview_preferent").html(preferent);

    return true;
}

if(isMobile>800) {
//分类选择
    $('.select_feature_content').on('click', '.select_feature_item', function (e) {
        var curt = $(e.target);
        if (!curt.next().hasClass('select_feature_item_cont')) {
            curt.find('i').removeClass('select_feature_icon_up');
        }
        else if (!curt.next().hasClass('hidden')) {
            curt.find('i').removeClass('select_feature_icon_up');
            curt.next().addClass('hidden')
        }
        else {
            curt.find('i').addClass('select_feature_icon_up');
            curt.next().removeClass('hidden')
        }
        //二级分类的id
        cateid = curt.attr('index');
        parentid = curt.attr('alt');
        sessionStorage.parentid = parentid;
        twocatename = curt.text();
        sessionStorage.cateid = cateid;

        twocatename = twocatename.replace(/\s/gi, "");
        sessionStorage.twocatename = twocatename;
        args = {cateid: cateid};


        if (curt.text().trim() == "大学活动") {
            id = curt.attr("index");

            treecateid = id;
            name = curt.text().trim();
            dd = $(".select_feature_header").html();
            sessionStorage.twocatename = dd;
            sessionStorage.catename = name;
            //  sessionStorage.cate_id = treecateid;

            sessionStorage.treecateid = treecateid;
            sessionStorage.name = name;
            $("#xzcate").html(sessionStorage.twocatename + "/" + sessionStorage.catename);
            $(".select_feature").addClass("hidden")

            // 回显分类
            echoOperation();
            return;
        }


        if (curt.hasClass("select_feature_item")) {
            $.post(sencateUrl, args, function (msg) {
                //获取地址
                $("").insertAfter(e.target);
                var tpl = document.getElementById('san').innerHTML;
                var html = juicer(tpl, msg);
                if (!curt.next().hasClass('select_feature_item_cont')) {
                    curt.find('i').addClass('select_feature_icon_up');
                    console.log(e.target)
                    $(html).insertAfter(e.target);
                    curt.next().removeClass('hidden')
                }
            }, 'json');
        }
    });
}
else{
    //分类选择
    $('.select_feature_content').on('tap', '.select_feature_item', function (e) {

        var curt = $(e.target);
        if (!curt.next().hasClass('select_feature_item_cont')) {
            curt.find('i').removeClass('select_feature_icon_up');
        }
        else if (!curt.next().hasClass('hidden')) {
            curt.find('i').removeClass('select_feature_icon_up');
            curt.next().addClass('hidden')
        }
        else {
            curt.find('i').addClass('select_feature_icon_up');
            curt.next().removeClass('hidden')
        }
        //二级分类的id
        cateid = curt.attr('index');
        parentid = curt.attr('alt');
        sessionStorage.parentid = parentid;
        twocatename = curt.text();
        sessionStorage.cateid = cateid;

        twocatename = twocatename.replace(/\s/gi, "");
        sessionStorage.twocatename = twocatename;
        args = {cateid: cateid};


        if (curt.text().trim() == "大学活动") {
            id = curt.attr("index");

            treecateid = id;
            name = curt.text().trim();
            dd = $(".select_feature_header").html();
            sessionStorage.twocatename = dd;
            sessionStorage.catename = name;
            //  sessionStorage.cate_id = treecateid;

            sessionStorage.treecateid = treecateid;
            sessionStorage.name = name;
            $("#xzcate").html(sessionStorage.twocatename + "/" + sessionStorage.catename);
            $(".select_feature").addClass("hidden")

            // 回显分类
            echoOperation();

            return;
        }


        if (curt.hasClass("select_feature_item")) {
            $.post(sencateUrl, args, function (msg) {
                //获取地址
                $("").insertAfter(e.target);
                var tpl = document.getElementById('san').innerHTML;
                var html = juicer(tpl, msg);
                if (!curt.next().hasClass('select_feature_item_cont')) {
                    curt.find('i').addClass('select_feature_icon_up');
                    console.log(e.target)
                    $(html).insertAfter(e.target);
                    curt.next().removeClass('hidden')
                }
            }, 'json');
        }
    });
}
$(".select_feature").click(function(e){
        e.preventDefault();
        var theEvent = e || window.event;
        var theTarget = theEvent.target || theEvent.srcElement;
        if(theTarget === this)
            if(!$(this).hasClass('hidden')) $(this).addClass("hidden");
    }
);

function getCatePrice(){


    name=twocatename+"/"+catename;
    sessionStorage.name = name;
    if (catename== null || catename==undefined) {
        $('#xzcate').html("请选择");
    } else {
        $('#xzcate').html(name).css("color","#999");
    }

    // 回显分类
    echoOperation();
}

if(isMobile>800) {

//三级的点击
    $('.select_feature_content').on('click', '.select_feature_item_li', function (e) {
        var curt = $(e.target);
        curt.parent('ul').addClass('hidden').prev().find('i').removeClass('select_feature_icon_up');
        $('.select_feature').addClass('hidden');

        catename = curt.text();
        treecateid = curt.attr('index');
        sessionStorage.catename = catename;
        sessionStorage.treecateid = treecateid;
        //三级分类id

        getCatePrice();

    });
}else{
    $('.select_feature_content').on('touchend', '.select_feature_item_li', function (e) {
        e.preventDefault();
        var curt = $(e.target);
        curt.parent('ul').addClass('hidden').prev().find('i').removeClass('select_feature_icon_up');
        $('.select_feature').addClass('hidden');

        catename = curt.text();
        treecateid = curt.attr('index');
        sessionStorage.catename = catename;
        sessionStorage.treecateid = treecateid;
        //三级分类id

        getCatePrice();

    });
}
getCatePrice();

$('.select_feature').click(function (e) {
    if($(e.target).hasClass('select_feature')){
        $(e.target).addClass('hidden');
    }
})

//培训类型选择
/*$('#realease-select-type').click(function(){
    if(sessionStorage.treecateid){
        $('.select_feature').removeClass('hidden');
    }
})*/



$(".release_preview").click(function (e) {
  infoErr();
  if(check){
      args = {
              sid: $sid,
              tags: $tags,
              model: $model,
              overtime: time,
              priceid:price,
            };
            $.post(infoUrl, args, function(msg) {
                $("#groupinfo").html("");
                var tpl = document.getElementById('info').innerHTML;
                var html = juicer(tpl, msg.info);

                $("#groupinfo").append(html);
                $('#infocontent').html($content);
                $('.detail_h3').html($title);
                $('#infocatename').html(name);
                if(environ!=null || environ!=""){
                    $('#info_environ').attr("src","/Public/Uploads/"+environ);
                }
            }, 'json');
      e.preventDefault();

      $('#release_box_bg').removeClass("bounceInDown");
      $('#release_box_bg').addClass("bounceInDown");
      $("#release_box_bg").removeClass("hidden");
  }

});
$(".release_box_bg").click(function(e){

      e.preventDefault();
      if($(e.target).hasClass('release_box_bg')){
          $("#release_box_bg").addClass("hidden");
      }
  }
);
