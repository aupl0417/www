/**
 * Created by Administrator on 2015/3/23.
 */
(function () {

var ajaxflat={};//控制append的时候不出错，post成功后检查传给他的参数与ajaxflat的内容是不是相同
ajaxflat.groupnews=true;
function showLoading(){
    $('.css-loading').removeClass("hidden");
}
function hiddenLoading(){
    $('.css-loading').addClass("hidden");
}
//绑定滚动事件
$(window).bind('scroll', pageScroll);

function pageScroll() {

    if($('#groupnews').attr('class')=="collect-ul-active"){
        var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
        if ($('body').height()-5 <= totalheight)
        {
            ajaxflat={}
            ajaxflat.groupnews=true;
            hiddenLoading();
            // 禁用滚动
            $(window).unbind('scroll', pageScroll);
            //
            postnews();/*ajax获取更多数据*/

        }
    }else if($('#comment').attr('class')=="middle_nav_course collect-ul-active"){
        var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
        if ($('body').height()-5 <= totalheight)
        {
            ajaxflat={}
            ajaxflat.comment=true;
            hiddenLoading();
            // 禁用滚动
            $(window).unbind('scroll', pageScroll);
            //
            postcomment();/*ajax获取更多数据*/
        }

    }else if($('#pushed').attr('class')=="collect-ul-active"){
        var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
        if ($('body').height()-5 <= totalheight)
        {
            ajaxflat={}
            ajaxflat.pushed=true;
            hiddenLoading();
            // 禁用滚动
            $(window).unbind('scroll', pageScroll);
            //
            postpushed();/*ajax获取更多数据*/
        }

    }
}



$('#groupnews').click(function(){
    ajaxflat={}
    ajaxflat.groupnews=true;
    showLoading();
    $('#ul_padding').addClass('hidden');
    $('#comment').removeClass('collect-ul-active');
    $('#pushed').removeClass('collect-ul-active');
    $('#groupnews').addClass('collect-ul-active');
    j=0;
    z=0;
    if(i<1){
        $('#sectionscroll').html('');
    }
    postnews();
    $(".rednum").addClass('hidden');
})
$('#comment').click(function(){
    ajaxflat={}
    ajaxflat.comment=true;
    showLoading();
    $('#ul_padding').addClass('hidden');
    $('#groupnews').removeClass('collect-ul-active');
    $('#pushed').removeClass('collect-ul-active');
    $('#comment').addClass('collect-ul-active');
    i=0;
    z=0;
    if(j<1){
        $('#sectionscroll').html('');
    }
    postcomment();
    $(".rednum").addClass('hidden');
})
$('#pushed').click(function(){
    ajaxflat={}
    ajaxflat.pushed=true;
    showLoading();
    $('#ul_padding').removeClass('hidden');
    $('#groupnews').removeClass('collect-ul-active');
    $('#comment').removeClass('collect-ul-active');
    $('#pushed').addClass('collect-ul-active');
    i=0;
    j=0;
    if(z<1){
        $('#sectionscroll').html('');
    }
    postpushed();
    $(".rednum").addClass('hidden');
})

var i=0;
var j=0;
var z=0;
function postnews(){
    i=i+1;
    var newsassist=$.post(postnews_url,{"page":i},function(data){
        var tpl = document.getElementById('tplnews').innerHTML;
        var html = juicer(tpl, data);
        console.log(data);
        $(".nodata_wrap").addClass("hidden");
        if(ajaxflat.groupnews) {
            $('#sectionscroll').append(html);
            hiddenLoading();
            imgonerror();
        }
        //绑定滚动事件
        if(i==1&&data.info==null){
            $("#chip_info").html("");
            $(".nodata_wrap").removeClass("hidden");
        }
        $(window).bind('scroll', pageScroll);
    }, "json");
}
function postcomment(){
    j=j+1;
    var newscomment=$.post(postcomment_url,{"page":j},function(data){
        var tpl = document.getElementById('tplcomment').innerHTML;
        var html = juicer(tpl, data);
        console.log(data);
        $(".nodata_wrap").addClass("hidden");
        if(ajaxflat.comment) {
            $('#sectionscroll').append(html);
            hiddenLoading();
            imgonerror();
        }        //绑定滚动事件
        $(window).bind('scroll', pageScroll);
        if(j==1&&data.info==null){
            $("#chip_info").html("请评论自己喜欢的课程吧");
            $(".nodata_wrap").removeClass("hidden");
        }
    }, "json");
}
function postpushed(){
    z=z+1;
    var newspush=$.get(postpushed_url,{"page":z},function(data){
        var tpl = document.getElementById('tplrecommend').innerHTML;
        var html = juicer(tpl, data.pushnews);
        console.log(data);
        $(".nodata_wrap").addClass("hidden");
        if(ajaxflat.pushed) {
            $('#sectionscroll').append(html);
            hiddenLoading();
            imgonerror();
        }        //绑定滚动事件
        if(z==1&&data.pushnews==null){
            $("#chip_info").html("我们正在准备优秀课程");
            $(".nodata_wrap").removeClass("hidden");

        }
        $(window).bind('scroll', pageScroll);
    }, "json");
}
postnews();
})()
