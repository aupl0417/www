    (function () {
        var global = {
            "commentPage":	1
        };

        $(window).bind('scroll', pageScroll);

        getCommentData();

        /**
         *
         */
        function pageScroll() {
            // 禁用滚动
            $(window).unbind('scroll', pageScroll);

            var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
            if ($('#commentPanel').height()-5 <= totalheight)
            {
                /*ajax获取更多数据*/
                $(".css-loading").removeClass("hidden");
                $.get("/Api/shop_Info/getcomments", {'page': global["commentPage"]}, function(data) {
                    console.log(data);

                    $(".css-loading").addClass("hidden");

                    if (data.status != 200) {
                        // 先处理商家没有登陆的情况
                        handleNotSignIn(data.msg);

                        $(window).bind('scroll', pageScroll);
                        return easyAlert(data.msg)
                    }

                    if (data.data == null) {
                        return
                    }

                    // 拼接模板了
                    bindTpl("comment", data);

                    global["commentPage"]++;
                    $(window).bind('scroll', pageScroll);

                }, "json")

            } else {
                $(window).bind('scroll', pageScroll);
            }
        }

        /**
         *
         */
        function getCommentData() {
            $(".css-loading").removeClass("hidden");
            $.get("/Api/shop_Info/getcomments", {'page': global["commentPage"]}, function(data) {
                console.log(data);

                $(".css-loading").addClass("hidden");

                if (data.status != 200) {
                    // 先处理商家没有登陆的情况
                    handleNotSignIn(data.msg);

                    return easyAlert(data.msg);
                }

                if (data.data == null) {
                   showCoffee(global["commentPage"]);
                   return;
                }

                // 拼接模板了
                bindTpl("comment", data);

                global["commentPage"]++;
                $(window).bind('scroll', pageScroll);

            }, "json")
        }

        /**
         *
         */
        function bindTpl(type, data) {
            var tpl = document.getElementById(type+'Tpl').innerHTML;
            var html = juicer(tpl, data);
            $("#"+type+"Panel").append(html)
        }

        /**
         * 处理没有登录的情况
         */
        function handleNotSignIn(msg) {
            if (msg != "商家还没有登录") {
                return;
            }

            if (window.localStorage) {
                localStorage.setItem("s_sign_timeout_loc", g.selfUrl);
            }


            easy_alert.work(function(){
                location = g.signInUrl;
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

