<?php 
/**
 * 
 * 登录回调 
 * 通过Authorization Code获取Access Token
 * @param unknown $app_id
 * @param unknown $my_url
 * @param unknown $app_secret //申请到的appkey
 * 设置了$_SESSION['access_token']
 * @return boolean|string
 */
function qq_callback(){
	$code = $_REQUEST["code"];
	
$state=S('states');
// $rrrr=session();

// print_r($code);
// print_r( '<br/>');
// print_r($_REQUEST['state']);
// print_r( '<br/>');
// print_r($rrrr);
// print_r( '<br/>');
// print_r($state);
// print_r( '<br/>');
// exit;

	//Step2：通过Authorization Code获取Access Token
	if($_REQUEST['state'] == $state)
	{
		//拼接URL
		$token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
				. "client_id=" . app_id . "&redirect_uri=" . urlencode(my_url)
				. "&client_secret=" . app_secret . "&code=" . $code;
		$response = file_get_contents($token_url);

// 		$ch = curl_init();
// 		curl_setopt($ch, CURLOPT_URL, $token_url);
// 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// 		$output = curl_exec($ch);
// 		curl_close($ch);
// 		$result = json_decode($output, true);
// 		if ($result["error"] != 0){
// 			return $result["status"];
// 		}
// 		$_SESSION['access_token']=$result['access_token'];
// 		$_SESSION['expires_in']=$result['expires_in'];
// 		$_SESSION['refresh_token']=$result['refresh_token'];
		
		
		
		if (strpos($response, "callback") !== false)
		{
			$lpos = strpos($response, "(");
			$rpos = strrpos($response, ")");
			$response  = substr($response, $lpos + 1, $rpos - $lpos -1);
			$msg = json_decode($response);
	
			if (isset($msg->error))
			{
				return $msg->error.$msg->error_description;
			}
		}
		$params = array();
		parse_str($response, $params);
		$_SESSION['access_token']=$params['access_token'];
		$_SESSION['expires_in']=$params['expires_in'];
		$_SESSION['refresh_token']=$params['refresh_token'];
		return true;
	}else
	{
		return 'The state does not match. You may be a victim of CSRF.';
	}	
}		

		
/**
 * 使用Access Token来获取用户的OpenID
 * 调用$_SESSION['access_token']
 * return openid
 */
function get_openid(){
		$access_token=$_SESSION['access_token'];
		//Step3：使用Access Token来获取用户的OpenID
		$graph_url = "https://graph.qq.com/oauth2.0/me?access_token=".$access_token;
		$str  = file_get_contents($graph_url);
		if (strpos($str, "callback") !== false)
		{
			$lpos = strpos($str, "(");
			$rpos = strrpos($str, ")");
			$str  = substr($str, $lpos + 1, $rpos - $lpos -1);
		}
		$user = json_decode($str);
		if (isset($user->error))
		{
		    return false;
// 			return $user->error;
		}
		return $user->openid;
}




/**
 * 过期重新授权 通过Authorization Code获取Access Token
 * @param unknown $app_id
 * @param unknown $app_secret
 * @param unknown $my_url
 * @param unknown $refresh_token
 * @return string
 */
function qq_callback_again($refresh_token){
	//Step2：通过Authorization Code获取Access Token
	if($_REQUEST['state'] == $_SESSION['state'])
	{
		//拼接URL
		$token_url = "https://graph.qq.com/oauth2.0/token?grant_type=refresh_token&"
				. "client_id=" . app_id
				. "&redirect_uri=" . urlencode(my_url)
				. "&client_secret=" . app_secret
				. "&refresh_token=" . $refresh_token;
		$response = file_get_contents($token_url);
		if (strpos($response, "callback") !== false)
		{
			$lpos = strpos($response, "(");
			$rpos = strrpos($response, ")");
			$response  = substr($response, $lpos + 1, $rpos - $lpos -1);
			$msg = json_decode($response);

			if (isset($msg->error))
			{
				return $msg->error;
			}
		}
		$params = array();
		parse_str($response, $params);
		$_SESSION['access_token']=$params['access_token'];
		$_SESSION['expires_in']=$params['expires_in'];
		$_SESSION['refresh_token']=$params['refresh_token'];
		return true;
	}else
	{
		return 'The state does not match. You may be a victim of CSRF.';
	}
}


?>
