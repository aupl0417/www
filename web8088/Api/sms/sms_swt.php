<?php
/**
 * sms_swt
 * 商务通短信平台【优易网】提供
 * 
 * http://inter.ueswt.com/sms.aspx 对应UTF-8
 * http://inter.ueswt.com/smsGBK.aspx 对应GB2312
 * 
 */

define('swtuserid','11507'); //企业ID
define('swtaccount','17yueke'); //发送用户帐号
define('swtpassword','123456'); //发送帐号密码

// define('swtmobile','10690'); //全部被叫号码
// define('swtcontent','10690'); //发送内容
// define('swtsendTime','10690'); //定时发送时间
// define('swtaction','10690'); //发送任务命令
// define('swtextno','10690'); //扩展子号



/**
 * $mobile手机号码--一维数组 
 * $content发送短信的内容 
 * @param array $mobile
 * @param string $content
 * @return boolean|unknown
 */
function send( $mobile , $content='' ) {
    if (is_array($mobile)){
        if (empty($mobile) || empty($mobile)){
            return false;
        }
        $phone = implode(',',$mobile);
    }else {
        $phone = $mobile;
    }
    $msg = '退订回T【17约课】';
    $content.=$msg;
    $url = 'http://inter.ueswt.com/sms.aspx';
    $postdata = 'action=send&userid='.swtuserid.'&account='.swtaccount.'&password='.swtpassword.'&mobile='.$phone.'&content='.$content.'&sendTime=&extno=';
    $xmldata = curlPost($url, $postdata);
    $objectData = simplexml_load_string($xmldata);
    if ($objectData->returnstatus == 'Success'){
        return true;
    }
    $errorMsg = $objectData->message;
    return $errorMsg;
}



/**
 * curl---post提交数据
 * @param unknown $customMessageSendUrl
 * @param unknown $postJosnData
 * @return mixed
 */
function curlPost($messageSendUrl,$postJosnData){
    $ch = curl_init($messageSendUrl);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postJosnData);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}


