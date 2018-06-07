<?php


/**
 * 用户点击登录
 * @param unknown $app_id APPID
 * @param unknown $scope授权的接口
 * @param unknown $my_url回调地址
 */
function qq_login($state='')
{
	//Step1：获取Authorization Code
// 	$code = $_REQUEST["code"];
// 	if(empty($code))
// 	{
		//state参数用于防止CSRF攻击，成功授权后回调时会原样带回
// 		session('state',md5(uniqid(mt_rand(), TRUE)));
// 		$_SESSION['state'] = md5(uniqid(mt_rand(), TRUE));
		//拼接URL
		$dialog_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=". app_id 
					. "&redirect_uri=" . urlencode(my_url) 
					. "&state=".$state."&scope=".scope;
		echo("<script> top.location.href='" . $dialog_url . "'</script>");
// 	}
// 	return $code;
}









?>
