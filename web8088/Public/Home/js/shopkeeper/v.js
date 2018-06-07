/**
 * Created by Administrator on 2015/3/24.
 */
(function () {
    var multi_file = document.getElementById('file'),                 // 获取上传控件
        slice = Array.prototype.slice;                                      // 获取数组slice原型方法
    multi_file.addEventListener('change', function (e) {                    // 监听上传控件数据变化
        var files = slice.call(multi_file.files, 0);                        // 将FileList对象转为数组获取forEach方法
        if(files[0].size>5*1024*1024){
            easyAlert("文件太大了");
            files[0]=null;
        }
        else if (files[0].type.toLowerCase().match(/image.*/)) {
            // 将头像改成图片
            var objUrl = getObjectURL(this.files[0]) ;
            console.log("objUrl = "+objUrl) ;
            if (objUrl) {
                $("#img0").attr("src", objUrl) ;
            }
        }
        else{
            easyAlert("只能选择图片");
            files[0]=null;
        }
    });
})();
(function () {
    $("#submit").click(function () {
        finish();
    });
    $('#btn').click(function (event) {
        $('#file').trigger("click");
    })
        /*Input上传图片*/

        var multi_file = document.getElementById('file'),                 // 获取上传控件
            slice = Array.prototype.slice;                                      // 获取数组slice原型方法
        multi_file.addEventListener('change', function (e) {                    // 监听上传控件数据变化
            var files = slice.call(multi_file.files, 0);                        // 将FileList对象转为数组获取forEach方法
            if(files[0].size>5*1024*1024){
                easyAlert("文件太大了");
                files[0]=null;
            }
            else if (files[0].type.toLowerCase().match(/image.*/)) {
                // 将头像改成图片
                var objUrl = getObjectURL(this.files[0]) ;
                console.log("objUrl = "+objUrl) ;
                if (objUrl) {
                    $("#img0").attr("src", objUrl) ;
                }
            }
            else{
                easyAlert("只能选择图片");
                files[0]=null;
            }
        });
    /**
     * 检测输入
     */
    function finish() {
        var company_name = $("input[name=company_name]").val();
        var legal_name = $("input[name=legal_name]").val();
        var tel = $("input[name=tel]").val();
        var file = $("#file").val();

        if (!/^[-\w\u4e00-\u9fa5]{6,15}$/.test(company_name)) {
            easyAlert("机构名称必须为6~15个字符");
            return
        }

        if (!/^[-\w\u4e00-\u9fa5]{2,15}$/.test(legal_name)) {
            easyAlert("法人名称必须为6~15个字符");
            return
        }

        if (!/^0\d{2,3}\-\d{7,8}$/.test(tel)) {
            easyAlert("固定电话号码不合法");
            return
        }

        if (file == "") {
            easyAlert("必须上传营业执照");
            return
        }

        document.getElementById("form").submit();

    }
    // <!-- 看看商家有没有认证过？？？ -->
    $(function() {
        $(".css-loading").removeClass("hidden");
        $.post(hasSendAuth_url, function(data) {

            handleNotSignIn(data);

            $(".css-loading").addClass("hidden");

            if (data.status != 200) {

                // 弹窗之后后退
                easy_alert.work(function(){
                    history.back(-1)
                });
                easy_alert.setText(data.msg);
                easy_alert.show();
                return false;

            }

        }, "json");
    });

    /**
     * 处理没有登录的情况
     */
    function handleNotSignIn(data) {
        if (data.status != 403) {
            return;
        }

        easyAlert(data.msg);
        if (window.localStorage) {
            localStorage.setItem("s_sign_timeout_loc",s_sign_timeout_loc);
        }
        location.href = signup_url;
        throw "exit"
    }
})();
