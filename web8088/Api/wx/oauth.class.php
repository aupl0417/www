<?php
/**
 * 用户点击登录
 * @param unknown $app_id APPID
 * @param unknown $scope授权的接口
 * @param unknown $my_url回调地址
 */
function wx_login($state='')
{
    //Step1：获取Authorization Code
    // 	$code = $_REQUEST["code"];
    // 	if(empty($code))
        // 	{
    //state参数用于防止CSRF攻击，成功授权后回调时会原样带回
    // 		session('state',md5(uniqid(mt_rand(), TRUE)));
    // 		$_SESSION['state'] = md5(uniqid(mt_rand(), TRUE));
    //拼接URL
//     $dialog_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=". app_id
//     . "&redirect_uri=" . urlencode(my_url)
//     . "&state=".$state."&scope=".scope;
    
 //   $dialog_url = "https://open.weixin.qq.com/connect/qrconnect?appid=".wx_app_id
 //   ."&redirect_uri=".my_wx_uri
 //   ."&response_type=code&scope=snsapi_login&state=".$state."#wechat_redirect";
    
 //   header("Location:".$dialog_url);
	

	
//	$url='http://'. $_SERVER['SERVER_NAME'].'/index.php/Qyapp/Oauth/wap_url/token/'.$data['token'].'/module/'.$data['module'].'/action/'.$action.'/pid/'.$pid;
	$oauthUrl='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.wx_app_id.'&redirect_uri='.my_wx_uri.'&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect';
	header('Location:'.$oauthUrl);
	
	
	
	
	
//     $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri=
//&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
//     header("Location:".$url);
    
    
//     echo("<script> top.location.href='" . $dialog_url . "'</script>");
    // 	}
    // 	return $code;
}