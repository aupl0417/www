/**
 * Created by Administrator on 2015/3/23.
 */
(function () {
    var i=0;
    var j=0;
    var z=0;
    var ajaxflat={};
    ajaxflat.publish=true;
    function showLoading(){
        $('.css-loading').removeClass("hidden");
    }
    function hiddenLoading(){
        $('.css-loading').addClass("hidden");
    }
//绑定滚动事件
    $(window).bind('scroll', pageScroll)

    function pageScroll() {

        //
        if($('#publish').attr('class')=="collect-ul-active"){
            var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
            if ($("body").height()-5 <= totalheight)
            {
                ajaxflat={}
                ajaxflat.publish=true;
                hiddenLoading();
                // 禁用滚动
                $(window).unbind('scroll', pageScroll);
                postpublish();/*ajax获取更多数据*/
            }

        }else if($('#group').attr('class')=="middle_nav_course collect-ul-active"){
            var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
            if ($('body').height()-5 <= totalheight)
            {
                ajaxflat={}
                ajaxflat.group=true;
                hiddenLoading();
                // 禁用滚动
                $(window).unbind('scroll', pageScroll);
                postgroup();/*ajax获取更多数据*/
            }

        }else if($('#signup').attr('class')=="collect-ul-active"){
            var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
            if ($('body').height()-5 <= totalheight)
            {
                ajaxflat={}
                ajaxflat.signup=true;
                hiddenLoading();
                // 禁用滚动
                $(window).unbind('scroll', pageScroll);
                postsignup();/*ajax获取更多数据*/
            }
        }

    }


    $('#publish').click(function(){
        ajaxflat={}
        ajaxflat.publish=true;
        showLoading();
        $('#group').removeClass('collect-ul-active');
        $('#signup').removeClass('collect-ul-active');
        $('#publish').addClass('collect-ul-active');
        j=0;
        z=0;
        if(i<1){
            $('#sectionscroll').html('');
        }
        postpublish(ajaxflat.publish=true);
    });
    $('#group').click(function(){
        ajaxflat={};
        ajaxflat.group=true;
        showLoading();
        $('#publish').removeClass('collect-ul-active');
        $('#signup').removeClass('collect-ul-active');
        $('#group').addClass('collect-ul-active');
        i=0;
        z=0;
        if(j<1){
            $('#sectionscroll').html('');
        }
        postgroup(ajaxflat.group=true);
    });
    $('#signup').click(function(){
        ajaxflat={}
        ajaxflat.signup=true;
        showLoading();
        $('#publish').removeClass('collect-ul-active');
        $('#group').removeClass('collect-ul-active');
        $('#signup').addClass('collect-ul-active');
        i=0;
        j=0;
        if(z<1){
            $('#sectionscroll').html('');
        }
        postsignup(ajaxflat.signup=true);
    });

    function postpublish(flat){
        i=i+1;
        $.post(postpublish_url,{"page":i},function(data){
            var tpl = document.getElementById('tplpublish').innerHTML;
            var html = juicer(tpl, data);
            console.log(data);
            if(ajaxflat.publish==flat) {
                $('#sectionscroll').append(html);
                hiddenLoading();
                imgonerror();
            }
            if(i==1&&data.info==null){
                $(".nodata_wrap").removeClass("hidden");
            }
            else{
                $(".nodata_wrap").addClass("hidden");
            }

            $(window).bind('scroll', pageScroll);
        }, "json");
    }
    function postgroup(flat){
        j=j+1;
        $.post(postgroup_url,{"page":j},function(data){
            var tpl = document.getElementById('tplgroup').innerHTML;
            var html = juicer(tpl, data);
            console.log(data);
            if(ajaxflat.group==flat) {
                $('#sectionscroll').append(html);
                hiddenLoading();
                imgonerror();
            }
            if(j==1&&data.info==null){
                $(".nodata_wrap").removeClass("hidden");
            }
            else{
                $(".nodata_wrap").addClass("hidden");
            }
            $(window).bind('scroll', pageScroll);
        }, "json");
    }
    function postsignup(flat){
        z=z+1;
        $.post(postsiginup_url,{"page":z},function(data){
            var tpl = document.getElementById('tplsignup').innerHTML;
            var html = juicer(tpl, data);
            console.log(data);
            if(ajaxflat.signup==flat) {
                $('#sectionscroll').append(html);
                hiddenLoading();
                imgonerror();
            }
            if(z==1&&data.info==null){
                $(".nodata_wrap").removeClass("hidden");
            }
            else{
                $(".nodata_wrap").addClass("hidden");
            }
            $(window).bind('scroll', pageScroll);
        }, "json");
    }
    ajaxflat.publish=true;
    postpublish(ajaxflat.publish);

    
    $("#sectionscroll").on("click",".section_item_pv_delete_user",function(e) {
        e.preventDefault();
        if (!confirm("确认删除？")) {
            window.event.returnValue = false;
        }else{
	        var delClick = $(e.target);
	        var n = delClick.parent(".get_del_user_id").find(".hidden_del_git").val();
	        console.log(n);
	        delGroup(n);
	   }
    })
    

    function delGroup(n){
        var postgid={};
        postgid.gid=n;
        console.log(postgid.gid);
        $.post(deletegroup_url,postgid,function(data){
            console.log(data);
            if(data.status!=200){
                alert('删除失败，请刷新重试');
            }else{
                $("#groupinfoid" + n).remove();
                alert('删除成功');
            }
        }, "json");
    }
})();