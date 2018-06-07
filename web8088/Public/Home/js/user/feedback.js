/**
 * Created by Administrator on 2015/3/23.
 */

(function () {
    $("textarea").on("focus", function (event) {
        $(event.target).next().removeClass("op_0");

    });
    $("textarea").on("blur", function (event) {
        $(event.target).next().addClass("op_0");

    });
    $(".input_close_icon").click(function (event) {
        $(event.target).prev().val("");
    });
    $("#btn").click(function () {
       return AllCheck();
    });
    function AllCheck(){
        var remarkvalue=document.feedbackform.feedback.value;
        var remarktest=/^.{1,500}$/;	//字符长度
        if(remarkvalue.length==0){
            easyAlert('反馈信息未填写');
            return false;
        }
        else if(!remarktest.test(remarkvalue)){
            easyAlert('反馈信息不能超过250个字');
            return false;
        }
        return true;
    }
})();
