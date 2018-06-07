/**
 * Created by Administrator on 2015/3/23.
 */
(function () {
    var i=1;
    //绑定滚动事件
    $(window).bind('scroll', pageScroll);
    function pageScroll(){
        var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
        if ($('body').height()-5 <= totalheight)
        {
            // 禁用滚动
            $(window).unbind('scroll', pageScroll);
            postnews();/*ajax获取更多数据*/
        }
    }
    function postnews(){
        i=i+1;
        var pages={};
        pages.page=i;
        $.post(url,pages,function(data){

            console.log(data);
        	if(data.info==null){
        		return;
        	}
            var tpl = document.getElementById('tplnews').innerHTML;
            var html = juicer(tpl, data.info);
            console.log(data);
            console.log(pages);
            $('#sectionscroll').append(html);
        }, "json");
        $(window).bind('scroll', pageScroll);
    }
})();