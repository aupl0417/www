    $(function() {
        $(window).bind('scroll', pageScroll);
        pageScroll()
    });

    /**
     *
     */
    function pageScroll() {

        var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
        if ($('#enrollPanel').height()-5 > totalheight) {
            return;
        }

        // 禁用滚动
        $(window).unbind('scroll', pageScroll);

        /*ajax获取更多数据*/
        $(".css-loading").removeClass("hidden");
        $.get(global.getEnrollsUrl, {"page": global.page}, function(data) {
            console.log(data);

            $(".css-loading").addClass("hidden");

            if (data.status != 200) {
                // 先处理商家没有登陆的情况
                handleNotSignIn(data.msg);

                $(window).bind('scroll', pageScroll);
                return easyAlert(data.msg)
            }

            if (data.data == null) {
                showCoffee(global.page);
                return;
            }

            // 拼接模板了
            var tpl = document.getElementById("enrollTpl").innerHTML;
            var html = juicer(tpl, data);
            $("#enrollPanel").append(html)

            global.page++
            $(window).bind('scroll', pageScroll)

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
            localStorage.setItem("s_sign_timeout_loc", global.selfUrl);
        }


        easy_alert.work(function(){
            location = global.signinUrl;
        });
        easy_alert.setText(msg);
        easy_alert.show();

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
