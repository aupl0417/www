/**
 * Created by Administrator on 2015/3/24.
 */
(function () {
    var curPage = 1;
    $(window).bind('scroll', pageScroll);

    $(function() {
        ajaxGetData();
    });

    function pageScroll() {
        // 禁用滚动
        $(window).unbind('scroll', pageScroll);

        var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
        if ($('body').height()-5 <= totalheight)
        {
            ajaxGetData();
        } else {
            $(window).bind('scroll', pageScroll);
        }
    }

    function ajaxGetData() {
        /*ajax获取更多数据*/
        $(".css-loading").removeClass("hidden");
        $.get(mycourse_url, {'page': curPage}, function(data) {

            $(".css-loading").addClass("hidden");

            if (data.status != 200) {
                // 先处理商家没有登陆的情况
                handleNotSignIn(data.msg);

                $(window).bind('scroll', pageScroll);
                return easyAlert(data.msg)
            }

            if (data.data == null) {
                showCoffee(curPage);
                return
            }

            // 拼接模板了
            var tpl = document.getElementById('tpl').innerHTML;
            var html = juicer(tpl, data);
            $("#wrap").append(html);

            curPage++;
            $(window).bind('scroll', pageScroll);

        }, "json")
    }

    /**
     * 处理没有登录的情况
     */
    function handleNotSignIn(msg) {
        if (msg != "商家还没有登录") {
            return;
        }

        if (window.localStorage) {
            localStorage.setItem("s_sign_timeout_loc",s_sign_timeout_loc )
        }

        easy_alert.work(function(){
            location.href = signin_url;
        });
        easy_alert.setText(msg);
        easy_alert.show();

        throw "exit"
    }

    /**
     * 显示coffee图标（没有信息）
     */
    function showCoffee(page) {
        if (page == 1) {
            $(".nodata_wrap").removeClass("hidden");
        }
    }

})();
