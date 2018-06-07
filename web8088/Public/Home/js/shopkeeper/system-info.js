/**
 * Created by Administrator on 2015/3/24.
 */
(function () {
    var global = {
        'page':	1
    };

    $(function() {
        // 滚动刷新
        $(window).bind('scroll', postnews);

        postnews();
    });

    /**
     * 更新信息
     */
    function postnews(){
        // 禁用滚动刷新
        $(window).unbind('scroll', postnews);
        $(".css-loading").removeClass("hidden");
        $.get(listMsg_url, {"page": global.page}, function(data){
            console.log(data);
            $(".css-loading").addClass("hidden");

            handleNotSignIn(data);

            if (data.data == null) {
                showCoffee(global.page);
                return;
            }

            var tpl = document.getElementById('tplnews').innerHTML;
            var html = juicer(tpl, data);
            $('#sectionscroll').append(html);

            global.page++;

            // 恢复滚动刷新
            $(window).bind('scroll', postnews);
        }, "json");
    }

    /**
     * 处理没有登录的情况
     */
    function handleNotSignIn(data) {
        if (data.status != 403) {
            return;
        }

        easyAlert(data.msg);
        if (window.localStorage) {
            localStorage.setItem("s_sign_timeout_loc", s_sign_timeout_loc);
        }
        location.href = sign_url;
        throw "exit";
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
