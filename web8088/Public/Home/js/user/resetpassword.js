/**
 * Created by Administrator on 2015/3/23.
 */
//隐藏错误提示
function ontext(){
    document.getElementById('reg_err').className='input-wrap hidden';
}

$("#btn").click(function () {
    pwCheck();
});

$("#pw1").click(function () {
    ontext();
});
$("#pw").click(function () {
    ontext();
});
//错误提示
function errtext(classn,etext){
    easyAlert(etext)
}
function clearpw(){
    $('.input-wrap-input').val("");
}
function pwCheck(){
    var pw=$('#pw').val();
    var pw1=$('#pw1').val();
    var rule=/^\S{8,12}$/;
    if(pw==''){
        errtext('input-wrap','密码未填写');
        clearpw();
        return false;
    }
    if(pw1==''){
        errtext('input-wrap','密码未填写');
        clearpw();
        return false;
    }
    if(pw!=pw1){
        errtext('input-wrap','密码不一致');
        clearpw();
        return false;
    }
    if(!rule.test(pw)){
        errtext('input-wrap','密码必须是8~12个字符');
        clearpw();
        return false;
    }
    $.post(post_url,{"reset":"do","password":pw},function(data){
        console.log(data.msg);
        if(data.msg!=200){
            errtext('input-wrap',data.data);
            clearpw();
        }else{
            errtext('input-wrap','密码修改成功');
            setTimeout(window.location.href=set_url,5000);
        }

    }, "json");
    return true;
}
