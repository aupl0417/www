<?php
/**
 * 通用工具库
 * @version    1.0.0 at 2014-12-10
 */

/**
 * 数字签名生成
 */
function authSign($request, $config) {

     $sign = '';
     if (is_array($request) 
            && !empty($request['api_key'])
            && !empty($config['auth'][$request['api_key']])) {
         ksort($request);
        
         while (list($key, $val) = each($request)) {
             if ($key == 'api_sign' || $key == 'attach1' || $val === '') {
                 continue;
             }
             $sign .= html_entity_decode($val, ENT_COMPAT);
         }
         if ($sign) {
             $sign .= $config['auth'][$request['api_key']]['secret'];
             $sign = md5($sign);
         } 	
     }
    
     return $sign;
 }
 
/**
 * 验证签名
 */
function authVerify($request, $config) {
    if (empty($request['api_sign'])) {
        return false;
    }

    $sign = authSign($request, $config);
    if (!$sign) {
        return false;
    }
    return $request['api_sign'] === $sign ? true : false;
}

/**
* 验证版本号
*/
function versionVerify($version, $api_key, $appConfig) {
    $name = $appConfig['auth'][$api_key]['name'];
    $version_conf = $appConfig['version'][$name];
    if( in_array($version, $version_conf) ){
        return true;
    }else{
        return false;
    }
}

/**
* 验证api_key是否合法
*/
function apiKeyVerify($key, $appConfig) {
    return isset($appConfig['auth'][$key]) ? true : false;
}

/**
 * 获取当前服务接口名 <模块.控制器.方法>
 */
function getService() {
	if(!__ACTION__) {
        return false;
	}
	$arry = explode("/", __ACTION__);
	$service = strtolower(end($arry));
	return $service;
}

/**
 * 异常快捷函数
 * @param string $exPre 前缀
 */
function Ex($code, $exPre = 'Api') {
	$exceptionClsss = '\\Exception\\' . ucfirst($exPre) . 'Exception';
    throw new $exceptionClsss($code);
}

/**
 * 写入日志快捷函数
 */
function LogInfo($msg) {
    $object = new \Log\WriterLog;
    $object::AccessLog($msg);
}

/**
 * 入口一系列验证
 */
function requestVerify($request) {
    $appConfig = C('APP');
    
    //验证api_key
    if (! apiKeyVerify($request['api_key'],$appConfig)) {
    	Ex(400);
    }
    
    // 版本验证
    if (! $request['ver'] || ! versionVerify($request['ver'], $request['api_key'], $appConfig)) {
        Ex(402);
    }  
      
    // 数字签名验证
    if (! authVerify($request,$appConfig)) {
        Ex(401);
    }
}


/*
 * curl post 模拟提交数据函数
 */
function cpost($url,$data,$timeout=10)
{
    $curl = curl_init(); // 启动一个CURL会话
    $this_header = array(
        "content-type: application/x-www-form-urlencoded;charset=UTF-8"
    );
    //curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec($curl); // 执行操作
    if (curl_errno($curl)) {
          echo 'Errno：'.curl_error($curl);//捕抓异常
    }
    curl_close($curl); // 关闭CURL会话
    return $tmpInfo; // 返回数据
}
/**
 * discuz 模拟用户登录 。。。发帖，回帖，点评都需要先模拟用户登录
 * $username 用户名
 * $password 密码
 */
function discuz_login($username,$password){

	$discuz_url = C('UCENTER_SERVER_URL');//论坛地址
	$login_url = $discuz_url.'/member.php?mod=logging&action=login';//登录页地址

	$post_fields = array();
	//以下两项不需要修改
	$post_fields['loginfield'] = 'username';
	$post_fields['loginsubmit'] = 'true';
	//用户名和密码，必须填写
	$post_fields['username'] = $username;
	$post_fields['password'] = $password;
	//安全提问
	$post_fields['questionid'] = 0;
	$post_fields['answer'] = '';
	//@todo验证码
	$post_fields['seccodeverify'] = '';

	//获取表单FORMHASH
	$ch = curl_init($login_url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$contents = curl_exec($ch);
	curl_close($ch);
	preg_match('/<input\s*type="hidden"\s*name="formhash"\s*value="(.*?)"\s*\/>/i', $contents, $matches);
	if(!empty($matches)) {
		$formhash = $matches[1];
	} else {
			
		die('Not found the forumhash.');
	}


	//POST数据，获取COOKIE,cookie文件放在网站的temp目录下
	$cookie_file = tempnam('./temp','cookie');

	$ch = curl_init($login_url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
	curl_exec($ch);
	curl_close($ch);

	return $cookie_file;
}
function get_avatar($uid, $size = 'middle', $type = '') {
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$typeadd = $type == 'real' ? '_real' : '';

	$avatar_img=C('UCENTER_SERVER_URL').'/uc_server/data/avatar/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg".'?times='.time();

	if(get_http_response_code($avatar_img)=="200"){
		return $avatar_img;
	}else{

		$gender =M('common_member_profile')->where("uid=".$uid)->find();

		$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';

		if($gender['gender']==0 || empty($gender['gender']) ){
			//不分男女默认头像
			$avatar_url = C('UCENTER_SERVER_URL').'/uc_server/images/default_'.$size.'.png';
		}elseif ($gender['gender']==1){
			//男默认头像
			$avatar_url = C('UCENTER_SERVER_URL').'/uc_server/images/man_'.$size.'.png';
		}elseif ($gender['gender']==2){
			//女默认头像
			$avatar_url = C('UCENTER_SERVER_URL').'/uc_server/images/woman_'.$size.'.png';
		}
		return $avatar_url;
	}
}
function get_http_response_code($theURL) {
	$headers = get_headers($theURL);
	return substr($headers[0], 9, 3);
}

function get_star_icon($stars,$gender){
	if($gender == '2') {
		$icon = C('APP_IMG_URL').'/Uploads/Picture/icon/woman/'.$stars.'.png';
	}else if($gender == '1') {
		$icon  = C('APP_IMG_URL').'/Uploads/Picture/icon/man/'.$stars.'.png';
	}else {
		$icon  = C('APP_IMG_URL').'/Uploads/Picture/icon/max/'.$stars.'.png';
	}
	return $icon;
}