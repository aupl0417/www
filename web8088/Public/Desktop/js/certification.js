(function(){
    $('.upload-btn').click(function(e){
        $(this).parent().find('input').click();
    });
    /* this time, however we use click event */
    new TabController({
        menuId: 'switcher',
        itemsWrapperId: 'switcher-wrapper',
        itemClass: 'switcher-item',
        eventName: 'click'
    });
    (function(){
        var uPhoneNum = $('#u-phone-num');
        if(!uPhoneNum) return;
        var formValidator = new FormValidator();
        var sendTipP = $('#send-tip-p');
        var uVerCode = $('#u-ver-code');
        var uVerCodeSend = $('#u-ver-code-send');
        uVerCodeSend.click(function(){
            if(!formValidator.isPhone(uPhoneNum.val())){
                sendTipP.html('<span style="color: red;">请先输入正确的手机号码</span>');
                return;
            }
            sendphonecode();
            sendTipP.html('<span id="u-count-down">60s</span>内没收到验证码，请重新发送');
            uVerCodeSend.attr('disabled','disabled');
            var timeLimit = 59;
            var uCountDown = $('#u-count-down');
            var timer = setInterval(function(){
                uCountDown.html(timeLimit-- + 's');
                if(timeLimit < 0){
                    sendTipP.html('');
                    uVerCodeSend.removeAttr('disabled');
                    clearInterval(timer);
                }
            }, 1000);
        });
    })();
})();