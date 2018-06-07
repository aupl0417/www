<?php

use Think\View;
use Think\Model\MergeModel;




/**
 * 邮箱合法性验证
 * @param string $user_email
 * @return boolean
 */
function is_email($user_email){

	$result = filter_var($user_email, FILTER_VALIDATE_EMAIL);

	if (!$result) {
		return false;
	}
	return true;

// 	$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
// 	if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false)
// 	{
// 		if (preg_match($chars, $user_email))
// 		{
// 			return true;
// 		}
// 		else
// 		{
// 			return false;
// 		}
// 	}
// 	else
// 	{
// 		return false;
// 	}
}
/**
 * 验证用户呢称是否合格
 * @param unknown $str
 * @return boolean
 */
function is_nickname($str){
    $strlengths = strlen(mb_convert_encoding($str, 'gbk' ,'utf-8'));
    if ($strlengths>15||$strlengths<4){
        return false;
    }
    return true;
}


/**
 * 加密密码
 * @param string $passwd
 * @return string
 */
function encrypt_passwd($passwd) {
	return md5(md5($passwd) . C('SALT'));
}

/**
 * 以mysql的datetime字段的形式获取当前时间
 * @return string
 */
function current_datetime() {
	return date('Y-m-d H:i:s');
}

/**
 * 将mysql的datetime转成时间戳
 */
function parse_datetime($date) {
	return strtotime($date);
}



/**
 * 时间的转换
 * @param string $time 当前的时间戳
 * @return string $date 转换后的时间
 */
  function transDate($time){

    if (is_numeric($time)) {
      $d=$time;
    } else {
      $d=strtotime($time);
    }


  	//结束的时间（目前的时间）
  	$now=time();

  	$startdate=date("Y-m-d",$d);
  	$enddate=date("Y-m-d",$now);
  	$date=floor((strtotime($enddate)-strtotime($startdate))/86400);
	$hour=floor((strtotime($enddate)-strtotime($startdate))%86400/3600);
	$minute=floor((strtotime($enddate)-strtotime($startdate))%86400/60);
	$second=floor((strtotime($enddate)-strtotime($startdate))%86400%60);
  	$ss=date("H:i",$d);
  	$st=date("m-d",$d);
  	if($date==0){
  		return "今天  " .$ss;
  	}elseif($date==1){
  		return "昨天  " .$ss;
  	}elseif($date==2){
  		return "前天  " .$ss;
  	}else{
  		return $st ." ". $ss;
  	}

  }




/**
 * 邮件发送
 * @param string $address
 * @param string $body
 * @param string $password
 * @param string $subject
 * @param string $email
 * @param string $user
 */
function  sendMail($address,$body,$subject){
    $mailconfig=C('mail_config');

	include_once VENDOR_PATH."PHPMailer/class.phpmailer.php";
	include_once VENDOR_PATH."PHPMailer/class.smtp.php";
	$mail = new PHPMailer();
	$mail->IsSMTP();  					// 设置用smtp协议去发送邮件
	$mail->SMTPDebug=0;					// 启用调试输出 0否
	$mail->Host = $mailconfig['Host'];   	// smtp邮件服务器的地址
	$mail->SMTPAuth = true;           	//是否用smtp账户认证
	$mail->Username = $mailconfig['Username'];		//smtp邮件服务器的账号
	$mail->Password = $mailconfig['Password']; 		//smtp邮件服务器的密码
	$mail->From = $mailconfig['Username'];				//发件人邮箱地址
	$mail->FromName =$mailconfig['FromName'];             //发件人名称
	$mail->SMTPSecure="SSL";			//启用TLS加密，SSL ` `也接受
	$mail->CharSet = "utf-8"; 			//设置文档字符编码
	$mail->Encoding = "base64"; 		//设置编码

    if (is_array($address)) {
        foreach ($address as $value) {
            $mail->addAddress($value); 	// 设置收件人的邮箱地址
        }
    } else {
        $mail->addAddress($address); 	// 设置收件人的邮箱地址
    }

	$mail->addReplyTo($mailconfig['Username'],$mailconfig['FromName']); 	//设置回复邮件地址
//	$mail->WordWrap = 50; 				// 设置换行字符数量
//	$mail->addAttachment("p1.jpg"); 	// 添加附件
//	$mail->AddAttachment("/tmp/image.jpg", "new.jpg");// 添加附件
	$mail->isHTML(true);  				//设置发送html格式内容
	$mail->Subject = $subject;			//邮件标题
	$mail->Body =$body.'<br />';		//邮件内容
// 	$mail->AltBody ="This is the body in plain text for non-HTML mail clients";//邮件附加信息
	if(!$mail->Send())//发送函数Send()
	{
		return $mail->ErrorInfo;
	}
	else {
		return true;
	}
}




/**
 * base64加密
 * @param unknown $data要加密的数据
 * @param unknown $key加密的密匙
 * @return string
 */
function encrypt($data, $key)
{
 $key = md5($key);
    $x  = 0;
    $len = strlen($data);
    $l  = strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l)
        {
         $x = 0;
        }
        $char .= $key{$x};
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
    }
    return base64_encode($str);
}
/**
 * 解密算法base64_decode
 * @param unknown $data要加密的数据
 * @param unknown $key加密的密匙
 * @return string
 */
function decrypt($data, $key)
{
 $key = md5($key);
    $x = 0;
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++)
    {
        if ($x == $l)
        {
         $x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++)
    {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1)))
        {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }
        else
        {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}

//
// Function: 获取远程图片并把它保存到本地====QQ
//
//
// 确定您有把文件写入本地服务器的权限
//
//
// 变量说明:
// $url 是远程图片的完整URL地址，不能为空。
// $filename 是可选变量: 如果为空，本地文件名将基于时间和日期
// 自动生成.
function GrabImage($url,$filename="") {
    if($url==""){
        return false;
    }
    if($filename=="") {
        $filename=md5(date("dMYHis")).'.jpg';
    }
    $qqpath=C('user_avatar');
    $filepath=$qqpath.'/qq/'.$filename;
    $filename='.'.$qqpath.'/qq/'.$filename;

    file_put_contents($filename, file_get_contents($url));

//     $fileP = fopen($filename, 'w');
//     $netP = fopen($url, 'r');

//     while(!feof($netP)) {
//         $buf = fread($netP, 1024);
//         fwrite($fileP, $buf);
//     }

//     fclose($fileP);
//     fclose($netP);

    return $filepath;
}
//数组排序=key
function arr_sort($array,$key,$order="asc"){//asc是升序 desc是降序

    $arr_nums=$arr=array();

    foreach($array as $k=>$v){

        $arr_nums[$k]=$v[$key];

    }

    if($order=='asc'){

        asort($arr_nums);

    }else{

        arsort($arr_nums);

    }

    foreach($arr_nums as $k=>$v){

        $arr[$k]=$array[$k];

    }

    return $arr;

}
// 检测输入的验证码是否正确，$code为用户输入的验证码字符串
function check_verify($code, $id = ''){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}
//返回截止日期
function time_list_date_format(){
    $now_time = time();
    $nows_time = date("m月d日 今天 24 00",$now_time);
    $timelist=array();
    for ($time=1;$time<=90;$time++){
        $time1     = date("m月d日",$now_time+3600*24*$time);
        $time2     = date("w",$now_time+3600*24*$time);
        switch ($time2){
            case 0:
                $time2="周日";
                break;
            case 1:
                $time2="周一";
                break;
            case 2:
                $time2="周二";
                break;
            case 3:
                $time2="周三";
                break;
            case 4:
                $time2="周四";
                break;
            case 5:
                $time2="周五";
                break;
            case 6:
                $time2="周六";
                break;
            default:
                break;
        }
        $time3     = date("H s",$now_time+3600*24*$time);//时分
        $timelist[]= $time1.' '.$time2.' 24 00';
    }
    array_unshift($timelist,$nows_time);
    return $timelist;
}

/**
 * 转义嵌入到js双引号中的模板变量
 */
function clean_js_content($content) {
    return addslashes(str_replace(["\r\n", "\n"], " ", $content));
}
/**
 * 转义嵌入到js双引号中的模板变量
 */
function clean_br_content($content) {
//     return addslashes(str_replace(["\r?\n?"], "\n", $content));
    return preg_replace('/(\r?\n)+/', '<br />', $content);
}
/**
 * 获取一天的时间戳范围（00:00:00 - 23:59:59）
 */
function get_day_time_scope() {
    $date = getdate();
    $starTime = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
    $endTime = mktime(23, 59, 59, $date['mon'], $date['mday'], $date['year']);
    return array($starTime, $endTime);
}

/**
 * 一天之内隔多少时间可以做啥，一天之内可以做多少次啥（比如手机短信）
 * @param string $prefix 缓存的键前缀
 * @param string $key 缓存的键（比如手机号码）
 * @param int $waitTime 等待时间，就是隔多少时间才能发一次
 * @param int $limitCount 一天之内只能发多少次
 * @return int 0代表发送成功，1代表在等待时间之内，2代表一天之内限制
 */
function limit_day_operate($prefix, $key, $waitTime, $limitCount) {
    // 获取缓存
    $sKey = $prefix . $key;
    $cache = S($sKey);

    // 获取今天的日期
    $nowDate = date('Ymd');
    $nowTime = time();

    // 判断缓存存不存在，并且是不是当天的缓存
    if ($cache && $cache['date'] == $nowDate) {
        // 判断在不在等待时间之内
        if ($cache['oldtime'] + $waitTime > $nowTime) {
            return 1;
        }

        // 判断有没有超过当天的量
        if ($cache['count'] >= $limitCount) {
            return 2;
        }
    }

    // 重新生成缓存
    $cache['oldtime'] = $nowTime;
    $cache['count'] = isset($cache['count']) ? $cache['count'] + 1 : 0;
    $cache['date'] = $nowDate;

    // 重新保存缓存
    S($sKey, $cache, 24*60*60);

    return 0;
}

/**
 * 验证url的正确性
 */
function validate_url($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    return true;
}



function is_weixin(){
    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
        return true;
    }
    return false;
}

function is_mobile_request()
{
    $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';
    $mobile_browser = '0';
    if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))
        $mobile_browser++;
    if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))
        $mobile_browser++;
    if(isset($_SERVER['HTTP_X_WAP_PROFILE']))
        $mobile_browser++;
    if(isset($_SERVER['HTTP_PROFILE']))
        $mobile_browser++;
    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
    $mobile_agents = array(
        'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
        'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
        'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
        'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
        'newt','noki','oper','palm','pana','pant','phil','play','port','prox',
        'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
        'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
        'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
        'wapr','webc','winw','winw','xda','xda-'
    );
    if(in_array($mobile_ua, $mobile_agents))
        $mobile_browser++;
    if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)
        $mobile_browser++;
    // Pre-final check to reset everything if the user is on Windows
    if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)
        $mobile_browser=0;
    // But WP7 is also Windows, with a slightly different characteristic
    if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)
        $mobile_browser++;
    if($mobile_browser>0)
        return true;
    else
        return false;
}

/**
 * 类似与U方法，但是将get参数都拼到url参数上
 */
function mergeGetU($url, $args=array()) {
    $args = array_merge(I('get.'), $args);
    return U($url, $args);
}
