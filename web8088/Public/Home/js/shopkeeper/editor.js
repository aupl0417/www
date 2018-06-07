$(function() {

    /**
     * 特技Input框
     */
    $("input[type='text']").on("focus", function (event) {
        $(event.target).next().removeClass("op_0");

    });
    $("input[type='text']").on("blur", function (event) {
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


    // 选择特色
    $('#first_new_p').text($('#zone option').not(function(){
        return !this.selected;
    })[0].innerHTML);
    $('#second_new_p').text($('#areaid option').not(function(){
        return !this.selected;
    })[0].innerHTML);

    $('#btn').click(function () {
        $('#file').trigger('click');
    });
    $('#bar').css('width', g.credit + '%').text(g.credit + '%');

    $('#zone').on("change", function (event) {
        $('#first_new_p').text($('#zone option').not(function(){
            return !this.selected;
        })[0].innerHTML);
        $('#second_new_p').text('请选择');

        // 基本地区变换
        var id = $(this).val();

        $.get(g.zoneUrl, {"parentid": id}, function(data) {
            console.log(data);

            if (data.status != "200") {
                easyAlert(data.msg);
                return;
            }
            var html = '<option value="0">请选择</option>';
            for (var i in data.data) {
                html = html + '<option value="' + data.data[i].id + '">'+data.data[i].areaname+'</option>'
            }
            $("#areaid").html(html);
        }, "json");

    });

    $('#areaid').on("change", function (event) {
        $('#second_new_p').text($('#areaid option').not(function(){
            return !this.selected;
        })[0].innerHTML);

    });

    $('#environImage').on("click", function (event) {
        $('#environFile').trigger("click");
    });

    $('#shopkeeper_select_type').on('change', function() {
        var typeHtml = $('#shopkeeper_select_type option').not(function(){
            return !this.selected;
        })[0].innerHTML;

        $('#type').html(typeHtml);
    });


    $('.course_select_ul').on('click','li',function (event) {
        if($(event.target).hasClass("course_select_ul_active")){
            $(event.target).removeClass("course_select_ul_active");
        }
        else{
            if ($(".course_select_ul_active").length >= 3) {
                easyAlert("只能选择3个");
                return
            }
            $(event.target).addClass("course_select_ul_active");
        }
    });
    $('.course_feature_btn').click(function (event) {
        var value = $('.register_input').val();
        if(value==""){
            easyAlert("内容不能为空")
        }
        else if(value.length>4){
            easyAlert("长度不能大于4")
        }
        else {
            $('.course_select_ul').append('<li>' + value + '</li>');
        }
    });


    // 改变分类，只有当没选分类时才能用
    if (g.cateid == 0) {
        $("#categoryPanel").click(function(e){
            var item_first=$(e.currentTarget);
            console.log(e.currentTarget);

            var item_first_child = item_first.siblings(".shopkeeper_type_item");
            var array=item_first.next().children().eq(0);
           changeArray(array);
            for(var i=0;i<item_first_child.length;i++){
                if($(item_first_child[i]).hasClass("hidden")){
                    $(item_first_child[i]).removeClass("hidden");
                }else{
                    $(item_first_child[i]).addClass("hidden");
                }
                var temp=$(item_first_child[i]).next();
                if(!$(temp).hasClass("hidden")) {
                    $(temp).addClass("hidden");
                    var temp=$(item_first_child[i]).children().eq(0).children().eq(0);
                    changeArray(temp);
                }
            }
        });
    }

    // 改变二级分类
    $(".shopkeeper_type_item").click(function(e){
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

    // 分类改变事件
    $("input[name=category]").change(function() {
        var cateid = $(this).val();
        var labelFor = "categoryInput_" + cateid;
        var catename = $("label[for=" + labelFor +"]").html();
        $("#catenamePanel").html(catename);
    });


});

// 监听头像变化
(function () {
    var multi_file = document.getElementById('file'),                 // 获取上传控件
            slice = Array.prototype.slice;                                      // 获取数组slice原型方法
    multi_file.addEventListener('change', function (e) {                    // 监听上传控件数据变化
        var files = slice.call(multi_file.files, 0);                        // 将FileList对象转为数组获取forEach方法
        if(files[0].size>2*1024*1024){
            easyAlert("文件太大了");
            files[0]=null;
        }
        else if (files[0].type.toLowerCase().match(/(jpg)|(jpeg)|(png)|(bmp)/)) {
            // 将头像改成图片
            var objUrl = getObjectURL(this.files[0]) ;
            console.log("objUrl = "+objUrl) ;
            if (objUrl) {
                $("#img0").attr("src", objUrl) ;
            }
        }
        else{
            easyAlert("只能选择图片格式：jpg、png、bmp");
            files[0]=null;
        }
    });
})();

// 监听场景图变化
(function () {
    var multi_file = document.getElementById('environFile'),                 // 获取上传控件
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
                $("#environImage").attr("src", objUrl) ;
            }
        }
        else{
            easyAlert("只能选择图片格式：jpg、png、bmp");
            files[0]=null;
        }
    });
})();


$(function() {

    $("#switchFeatureBtn").click(switchFeature);
    $("#submitBtn").click(finish);
    $("#featureBackBtn").click(function() {
        switchInfo(false);
    });
    $("#featureSubmitBtn").click(function() {
        switchInfo(true);
    });

    /**
     * 切换到特点选择界面
     */
    function switchFeature() {
        // 滚动到特性页顶部，防止自定义输入框被挡住
        window.scroll(0, 0)

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
                easyAlert("只能选择3个");
                return;
            }
            $(".course_select_ul_active").each(function() {
                features = features + "|" + $(this).html();
            });
            $("#featureInput").val(features.substring(1));

            $("#featurePanel").html(features.substring(1) + g.designBtn);
        }
        $("#feature").addClass("hidden");
        $("#info").removeClass("hidden");
    }

    /**
     * 检测输入
     */
    function finish() {
        var nickname = $.trim($("#nickname").val());
        var remark = $.trim($("#remark").val());
        var tel = $.trim($("#tel").val());
        var area_raw = $.trim($("#area_raw").val());
        var features = $("#featureInput").val();
        var areaid = $("#areaid").val();
        var files = document.getElementById("file").files;
        var environFiles = document.getElementById("environFile").files;
        var age = $.trim($("#age").val());
        var website = $.trim($("#website").val());
        var teacher_power = $.trim($("#teacher_power").val());
        var cateid = $("input[name='category']:checked").val();
        
        if (nickname != "") {
            if (!/^[-\w\u4e00-\u9fa5]{2,12}$/.test(nickname)) {
                easyAlert("昵称必须为2~12位有效字符");
                return;
            }
        }

        if (remark != "") {
            if (!/^[\s\S]{0,512}$/.test(remark)) {
                easyAlert("机构简介长度不能超过512个字符");
                return;
            }
        }

        if (tel != "") {
            if (!/^\d{3,4}\-\d{7,8}$/.test(tel)) {
                easyAlert("固定电话号码不正确");
                return;
            }
        }

        if (area_raw!= "") {
            if (!/^[\s\S]{0,40}$/.test(area_raw)) {
                easyAlert("地址长度不能超过40个字符");
                return;
            }
        }

        // 校验通过了
        var postData = new FormData();
        postData.append("nickname", nickname);
        postData.append("remark", remark);
        postData.append("tel", tel);
        postData.append("area_raw", area_raw);
        postData.append("areaid", areaid);
        postData.append("features", features);
        postData.append("age", age);
        postData.append("website", website);
        postData.append("teacher_power", teacher_power);
        postData.append("cateid", cateid);

        postData.append("file", files[0]);
        postData.append("environFile", environFiles[0]);

        // 禁用上传操作
        $("#submitBtn").unbind("click", finish);
        easyAlert("正在上传中，请耐心等待");

        // 开始上传信息
        $.ajax({
            url: g.handleEditUrl,
            type: 'POST',
            data: postData,
            cache: false,
            dataType: 'json',
            processData: false, // Don't process the files
            contentType: false, // Set content type to false as jQuery will tell the server its a query string request
            success: function (data, textStatus, jqXHR) {
                console.log(data);

                if (data.status != "200") {
                    $("#submitBtn").bind("click", finish)
                    return easyAlert(data.msg)
                }

                // 判断有没有完成后跳转的url
                if (window.sessionStorage) {
                    var backUrl = sessionStorage.getItem("shop.finishDetailBackUrl");
                    if (backUrl) {
                        location = backUrl;
                        return;
                    }
                }

                // 没有，跳转到首页
                location = g.indexUrl;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                easyAlert('ERRORS: ' + textStatus);
                $("#submitBtn").bind("click", finish)
            }
        });

    }


});

/**
 * 分类改变图片吧
 */
function changeArray(array){
    if(array.attr("src") == g.upImg){
        array.attr("src", g.indexMoreImg);
    }else{
        array.attr("src", g.upImg);
    }
}

