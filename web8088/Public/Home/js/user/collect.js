/**
 * Created by Administrator on 2015/3/23.
 */
(function () {
    var ajaxflat={};
    var i=1;
    var j=0;
    function showLoading(){
        $('.css-loading').removeClass("hidden");
    }
    function hiddenLoading(){
        $('.css-loading').addClass("hidden");
    }
//绑定滚动事件
    $(window).bind('scroll', pageScroll);
    function pageScroll() {
        if($('#group').attr('class')=="collect-ul-active"){
            var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
            if ($('body').height()-5 <= totalheight)
            {
                ajaxflat={};
                ajaxflat.group=true;
                hiddenLoading();
                // 禁用滚动
                $(window).unbind('scroll', pageScroll);
                postgroup();/*ajax获取更多数据*/
            }
            //绑定滚动事件
        }else if($('#shop').attr('class')=="collect-ul-active"){
            var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
            if ($('body').height()-5 <= totalheight)
            {
                ajaxflat={};
                ajaxflat.shop=true;
                hiddenLoading();
                // 禁用滚动
                $(window).unbind('scroll', pageScroll)
                postshop();/*ajax获取更多数据*/
            }

        }

    }

    $('#group').click(function(){
        ajaxflat={};
        ajaxflat.group=true;
        showLoading();
        $('#shop').removeClass('collect-ul-active');
        $('#group').addClass('collect-ul-active');
        j=0;
        if(i<1){
            $('#sectionscroll').html('');
        }
        postgroup();
    });
    $('#shop').click(function(){
        ajaxflat={};
        ajaxflat.shop=true;
        showLoading();
        $('#group').removeClass('collect-ul-active');
        $('#shop').addClass('collect-ul-active');
        i=0;
        if(j<1){
            $('#sectionscroll').html('');
        }
        postshop();
    })

    function postgroup(){
        i=i+1;
        $.post(url,{"page":i,"type":"group"},function(data){
            var tpl = document.getElementById('tpl').innerHTML;
            var html = juicer(tpl, data);
            console.log(data);
            if(ajaxflat.group) {
                $('#sectionscroll').append(html);
                hiddenLoading();
                imgonerror();//图片加载失败给默认图片
            }

            if(i==1&&data.info==false){
                $(".nodata_wrap").removeClass("hidden");
            }
            else{
                $(".nodata_wrap").addClass("hidden");

            }
            $(window).bind('scroll', pageScroll);

        }, "json");
    }
    function postshop(){
        j=j+1;
        $.post(url,{"page":j,"type":"shop"},function(data){
            var tpl = document.getElementById('tplshop').innerHTML;
            var html = juicer(tpl, data);
            console.log(data);
            if(ajaxflat.shop){
                $('#sectionscroll').append(html);
                hiddenLoading();
                imgonerror();//图片加载失败给默认图片
            }
            $(window).bind('scroll', pageScroll);
            if(j==1&&data.info==false){
                $(".nodata_wrap").removeClass("hidden");
            }
            else{
                $(".nodata_wrap").addClass("hidden");
            }
        }, "json");
    }
})()