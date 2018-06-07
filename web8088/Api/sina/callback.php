<?php
// // session_start();
// //判断是否已经登录
// if(isset($_SESSION['slast_key'])) 
// {
// 	header("Location:".SiteUrl."/index.php");
// 	exit;	
// }
// include_once( 'config.php' );
// include_once( 'saetv2.ex.class.php' );

$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );


if (isset($_REQUEST['code'])) {
	$keys = array();
	$keys['code'] = $_REQUEST['code'];
	$keys['redirect_uri'] = WB_CALLBACK_URL;
	try {
		$token = $o->getAccessToken( 'code', $keys ) ;//获取用户的唯一的uid,获取授权过的Access Token 
	} catch (OAuthException $e) {
	}
}


if ($token) {
	$_SESSION['token'] = $token;
	$_SESSION['access_token'] = $token['access_token'];
	$_SESSION['expires_in'] = $token['expires_in'];
	setcookie( 'weibojs_'.$o->client_id, http_build_query($token) );
	//转到注册登录页面
//============================================================me==============================	
	$userId=$token['uid'];//用户唯一标识符
	$sinaUser=D('Sina');
	$resule=$sinaUser->checksinauid($userId);//检查是否登录过
	if ($resule!==false){
	    $User=D('User');
	    $userinfo=$User->getUserInfo($resule['uid']);
	    // 登陆成功，设置user的二维数组
	    session('user.id',$userinfo['id']);  						//设置session
	    session('user.name',$userinfo['firstname'].$userinfo['lastname']);  	//设置session
	    session('user.remark',$userinfo['remark']);  						//设置session
	    session('user.profession',$userinfo['profession']);  						//设置session
	    session('user.phone',$userinfo['phone']);  						//设置session
	    session('user.email',$userinfo['email']);  						//设置session
	    session('user.avatar',$userinfo['avatar']);  					//设置session
	    session('user.telstatus',$userinfo['telstatus']);  					//设置session
	    session('user.vtype',$userinfo['vtype']);  					//设置session
	    session('user.vstatus',$userinfo['vstatus']);  					//设置session
	    session('shopkeeper',null);
	    session('shop_auto_login',null);
	    $User->setUsercookise($userinfo['id']);
	    return true;
	}

//==========================================================================================	
	
	$oo=new SaeTClientV2(WB_AKEY,WB_SKEY,$token['access_token'],$o->client_secret);
	try {
		$uidinfo = $oo->show_user_by_id( $token['uid'] ) ;
	} catch (OAuthException $e) {
	}
	

//==========================================================================================
// print_r($uidinfo);
// print_r($_SESSION['token']);
// print_r($_SESSION['access_token']);
// print_r($_SESSION['expires_in']);
// exit;
	
	$user_yk_id=$sinaUser->addsinaUser($userId,$uidinfo);//插入
	if ($user_yk_id===false){
	    @header('location: http://17yueke.cn/Home/Userregsign');//me
	    exit;//me
	}

	
	
	
	
	

// 	print_r('<br/><br/>');
// 	print_r($token);
	
// 	print_r('<br/><br/>');
// 	print_r($o->access_token);
	
// 	print_r('<br/><br/>');
	
// 	print_r($o);exit;
	
	
//     @header('location: http://17yueke.cn/Home/Index/index');
// 	exit;
	
} else {

    @header('location: http://17yueke.cn/Home/Userregsign');//me
    exit;//me
    
//  echo "授权失败。"; 
}







