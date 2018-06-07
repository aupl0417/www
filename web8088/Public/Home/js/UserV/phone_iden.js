/**
 * Created by Administrator on 2015/3/24.
 */
(function () {
    $('#sendtel').bind('click', sendphonecode);
    //发送验证码
    function sendphonecode(){
        var phone=$("#phone").val();
        if(phone==''){
            easyAlert('请输入手机号码');
        }else{
            $.post(phoneCodeSend_url, {tel:phone}, function(data) {
                if(data.status==200){
                    // 禁用滚动
                    $('#sendtel').unbind('click', sendphonecode)
                    onaclick();
                    easyAlert('发送成功！');
                }else{
                    easyAlert(data.msg);
                }
            }, 'json');
        }
    }


    //倒计时
    function onaclick(){
        var i=60;
        setInterval(function(){
            if(i>=0){
                // sendtel.setAttribute('color','red'); //改变id为sendtel的颜色
                munid.innerHTML=i+'s';
            }
            else{
                //绑定滚动事件
                $('#sendtel').bind('click', sendphonecode);
                window.clearInterval(this); //停止计时
            }
            i--;
        },1000);
    }

    //验证验证码
    $('#ontelcode').click(function(){
        var telcode=$("#telcode").val();
        if(telcode!=null&&telcode!=""){

            $.post(phoneCode_url, {code:telcode}, function(data) {
                if(data.status==200){
                	easyAlert("验证成功");
                    window.location.href=yuekehref;
                }else{
                    easyAlert(data.msg);
                }
            }, 'json');
        }
        else{
            easyAlert("验证码不能为空");

        }

    })
})();