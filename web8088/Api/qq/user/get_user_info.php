<?php

/**
 * 
 * 获取用户的资料
 * @param unknown $openid
 * @return mixed
 */
function get_user_info($qqopenid){
	$access_token=$_SESSION['access_token'];
	//Step4：使用OpenID来获取用户的用户资料
	$graph_url = "https://graph.qq.com/user/get_user_info?access_token=".$access_token
				."&oauth_consumer_key=".app_id
				."&openid=".$qqopenid;
	$qqinfostr = file_get_contents($graph_url);

	$userinfo  = json_decode($qqinfostr,true);
	if ($userinfo['ret']!=0)
	{
	    return false;
// 		return $userinfo->ret;
	}
	return $userinfo;
}


//
// Function: 获取远程图片并把它保存到本地
//
//
// 确定您有把文件写入本地服务器的权限
//
//
// 变量说明:
// $url 是远程图片的完整URL地址，不能为空。
// $filename 是可选变量: 如果为空，本地文件名将基于时间和日期
// 自动生成.
// function GrabImage($url,$filename="") {
// 	if($url==""):return false;endif;
// 	if($filename=="") {
// 		$ext=strrchr($url,".");		//strrchr() 函数查找字符串在另一个字符串中最后一次出现的位置
// 		if($ext!=".gif" && $ext!=".jpg" && $ext!=".png" && $ext!=".jpeg"):return false;endif;
// 		$filename=md5(date("dMYHis")).$ext;
// 	}
// 	$qqpath=C('user_avatar');
//     $filename='.'.$qqpath.'/qq/'.$filename;
//     $filepath=$qqpath.'/qq/'.$filename;
    
//     $img = @file_get_contents(trim($url));
    
// // 	ob_start();
// // 	readfile($url);
// // 	$img = ob_get_contents();
// // 	ob_end_clean();
// // 	$size = strlen($img);
	
// 	$fp2=@fopen($filename, "a");
// 	fwrite($fp2,$img);
// 	fclose($fp2);
// 	return $filepath;
// }









?>
