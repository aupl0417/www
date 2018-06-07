/**
 *
 */
$(function(){
    //var page = 1;
    //var tmpKeywords;

    function showLoading() {
        $('.css-loading').removeClass("hidden");
    }

    function hiddenLoading() {
        $('.css-loading').addClass("hidden");
    }

    function showSearchNotFound() {
        $('.search_nofound').removeClass("hidden");
    }

    function hiddenSearchNotFound() {
        $('.search_nofound').addClass("hidden");
    }

    //function showPages() {
    //    $('.resbut').removeClass("hidden");
    //}
    //
    //function hiddenPages() {
    //    $('.resbut').addClass("hidden");
    //}

    var searchBtn = $('.course_institution_btn');
    searchBtn.click(function() {
        var $keywords = $.trim($('.register_input').val());
        if($keywords.match(/^\s*$/)) {
            $("#desc").html('');
            showSearchNotFound();
        } else {
            showLoading();
            //page = 1;
            var args = {keywords: $keywords};
            $.post(searchUrl, args, function(msg) {
                hiddenLoading();
                var tpl = document.getElementById('shopAndUser').innerHTML;
                var data = {};
                data.info = msg;
                var html = juicer(tpl, data);
                if(html.match(/^\s*$/)) {
                    $("#desc").html('');
                    showSearchNotFound();
                } else {
                    $("#desc").html('');
                    $("#desc").append(html);
                    hiddenSearchNotFound();
                    showPages();
                }
            }, 'json');
        }
    });
    // 搜索没有分页
    //$('.resbut').click(function () {
    //    page++;
    //    console.log('page ' + page);
    //    showLoading();
    //    $.post(searchUrl, {keywords: tmpKeywords, pages: page}, function(msg) {
    //        hiddenLoading();
    //        var tpl = document.getElementById('shopAndUser').innerHTML;
    //        var data = {};
    //        data.info = msg;
    //        var html = juicer(tpl, data);
    //        if(html.match(/^\s*$/)) {
    //            easyAlert("加载完毕");
    //            hiddenPages();
    //        } else {
    //            $("#desc").append(html);
    //        }
    //    }, 'json');
    //});


});