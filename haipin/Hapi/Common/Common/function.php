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
             if ($key == 'api_sign' || $val === '') {
                 continue;
             }
             $sign .= html_entity_decode($val, ENT_COMPAT);
         }

         if ($sign) {
             $sign .= $config['auth'][$request['api_key']]['secret'];
             $sign  = md5($sign);
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
    //return $version_conf;
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

/**
 *数组转为where查询条件
 * @param $data array()
 */
function arrryToWhere ($data = array(), $pre= '') {
    if (! is_array($data)) {
        return $data;
    }
    //取得最后一个元素对应得键值
    $i = 0;
    $count = sizeof($data);
    foreach ($data as $k => $v) {
        $i ++ ;
        $flag = ($count== $i ) ? "" : " AND ";
        $where .= $pre .$k ." = ". "'" .$v . "'" . $flag;
    }
    return $where;
}

/**
 *数组转为where LIKE查询条件
 * @param $data array()
 */
function arrryToWhereLike ($data = array(), $keyVal = 'title') {
    if (! is_array($data)) {
        return $data;
    }
    //取得最后一个元素对应得键值
    $i = 0;
    $count = sizeof($data);
    foreach ($data as $k => $v) {
        $i ++ ;
        $flag = ($count== $i ) ? "" : " OR ";
        $where .= "{$keyVal} LIKE '%" . $v . "%'" . $flag;
    }

    return $where;
}


/**
 * [send_valnum 发送手机短信]
 * @param  [type] $mobile  [手机号码]
 * @param  [type] $content [发送内容]
 * @return [type]          [description]
 */
function send_valnum($mobile,$content){
    $url="http://124.173.70.59:8081/SmsAndMms/mt?";
    $post = array(
        'Sn' => 'SDK-ZML-0182',
        'Pwd' => 'TR67JFJ',
        'mobile' => $mobile,
        'content' => $content.'【海品药店】',
    );
    $return = cpost($url,$post);
    $str = '/(\d+)|-(\d+)/';
    preg_match($str,$return,$match);
    if( $match[0]==0 ){
        $msg = array( 'status' => '1', 'info' => '短信发送成功' );
    }
    else{
        $msg = array( 'status' => '0', 'info' => '短信发送失败' );
    }
    return $msg;
}

/**
 *
 * @author xuxiaojie 2014-12-03
 *函数名称:encrypt
 *函数作用:加密解密字符串
 *加密     :encrypt('str','E','123245');
 *解密     :encrypt('被加密过的字符串','D','123245');
 *$string   :需要加密解密的字符串
 *$operation:判断是加密还是解密:E:加密   D:解密
 *$key      :加密的钥匙(密匙);
*********************************************************************/
function encrypt($string, $operation, $key){
	$key = md5($key ? $key : C('UC_KEY'));
	$key_length = strlen($key);
	$string = $operation == 'D' ? base64_decode($string) : substr(md5($string . $key),0,16).$string;
	$string_length = strlen($string);
	$rndkey = $box = array();
	$result = '';
	for($i=0; $i<=255; $i++){
		$rndkey[$i] = ord($key[$i%$key_length]);
		$box[$i] = $i;
	}
	for($j=$i=0; $i<256; $i++){
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for($a=$j=$i=0; $i<$string_length; $i++){
		$a = ($a+1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
	}
	if($operation == 'D'){
		if(substr($result,0, 16) == substr(md5(substr($result,16).$key),0,16)){
			return substr($result, 16);
		}else{
			return'';
		}
	}else{
		return str_replace('=', '', base64_encode($result));
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

//将图片地址转化为外网链接
function getImgLink($litpic,$source=''){
    if($source=='哈秀时尚网'){
        $res=str_replace('/html/',C('HX_URL'),$litpic);
    }elseif($source=='妆品网'){
        $res=str_replace('/html/',C('ZP_URL'),$litpic);
    }else{
        $res=str_replace('/zixun/html/uploads',C('CFG_IMGHOST'),$litpic);
    }
    return $res;
}

/**
 * [random 图片名称随机数]
 */
function random($length = 6, $type = 0) {
    $hash = '';
    $chararr = array(
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',
        '0123456789',
        '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'
    );
    $chars = $chararr[$type];
    $max = strlen($chars) - 1;
    PHP_VERSION < '4.2.0' && mt_srand((double) microtime() * 1000000);
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

/**
 *  生成一个随机字符
 *
 * @access    public
 * @param     string  $ddnum
 * @return    string
 */
function dd2char($ddnum)
    {
        $ddnum = strval($ddnum);
        $slen = strlen($ddnum);
        $okdd = '';
        $nn = '';
        for($i=0;$i<$slen;$i++)
        {
            if(isset($ddnum[$i+1]))
            {
                $n = $ddnum[$i].$ddnum[$i+1];
                if( ($n>96 && $n<123) || ($n>64 && $n<91) )
                {
                    $okdd .= chr($n);
                    $i++;
                }
                else
                {
                    $okdd .= $ddnum[$i];
                }
            }
            else
            {
                $okdd .= $ddnum[$i];
            }
        }
        return $okdd;
    }

    /**
    *  获取用户真实地址
    *
    * @return    string  返回用户ip
    */
    function GetIP()
    {
        static $realip = NULL;
        if ($realip !== NULL)
        {
            return $realip;
        }
        if (isset($_SERVER))
        {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                /* 取X-Forwarded-For中第x个非unknown的有效IP字符? */
                foreach ($arr as $ip)
                {
                    $ip = trim($ip);
                    if ($ip != 'unknown')
                    {
                        $realip = $ip;
                        break;
                    }
                }
            }
            elseif (isset($_SERVER['HTTP_CLIENT_IP']))
            {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }
            else
            {
                if (isset($_SERVER['REMOTE_ADDR']))
                {
                    $realip = $_SERVER['REMOTE_ADDR'];
                }
                else
                {
                    $realip = '0.0.0.0';
                }
            }
        }
        else
        {
            if (getenv('HTTP_X_FORWARDED_FOR'))
            {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            }
            elseif (getenv('HTTP_CLIENT_IP'))
            {
                $realip = getenv('HTTP_CLIENT_IP');
            }
            else
            {
                $realip = getenv('REMOTE_ADDR');
            }
        }
        preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
        $realip = ! empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
        return $realip;
    }

/**
 * array_column兼容
 */
if(!function_exists('array_column')){
    function array_column($input, $columnKey, $indexKey=null){
        $columnKeyIsNumber      = (is_numeric($columnKey)) ? true : false;
        $indexKeyIsNull         = (is_null($indexKey)) ? true : false;
        $indexKeyIsNumber       = (is_numeric($indexKey)) ? true : false;
        $result                 = array();
        foreach((array)$input as $key=>$row){
            if($columnKeyIsNumber){
                $tmp            = array_slice($row, $columnKey, 1);
                $tmp            = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
            }else{
                $tmp            = isset($row[$columnKey]) ? $row[$columnKey] : null;
            }
            if(!$indexKeyIsNull){
                if($indexKeyIsNumber){
                    $key        = array_slice($row, $indexKey, 1);
                    $key        = (is_array($key) && !empty($key)) ? current($key) : null;
                    $key        = is_null($key) ? 0 : $key;
                }else{
                    $key        = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key]       = $tmp;
        }
        return $result;
    }
}

/**
 * 发送邮件
 * @param email $mail_to
 * @param string $subject
 * @param text/html $body
 * @return boolean
 */
function send_email( $mail_to,$subject,$body ){
    $email = new \Org\Util\Email();
    $data['mailto']  =  $mail_to; //收件人
    $data['subject'] =  $subject;    //邮件标题
    $data['body']    =  $body;    //邮件正文内容
    return $email->send( $data ) ? true : false;
}

/**
 * 邮件验证加密/解密
 * @param string $string [DECODE：解密字符串 / ENCODE：用户ID]
 * @param string $operation 方法[DECODE / ENCODE]
 * @param string $key 密钥
 * @param number $expiry 验证超时时间
 * @return string
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0){
    $ckey_length = 4; // 随机密钥长度 取值 0-32;
    // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
    // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
    // 当此值为 0 时，则不产生随机密钥

    $key = md5($key ? $key : C('SHOP_HASH'));
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(str_replace(array('-','_'), array('+','/'), substr($string, $ckey_length))) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace(array('+','/','='), array('-','_',''), base64_encode($result));
    }
}

/**
* NULL值返回空串
*/
function del_null($data){
	if(is_array($data)){
	    foreach ($data as $key => $value) {
	    $v = del_null($value);
	    $data[$key] = $v;
	    }
	}else{
	   if(is_null($data)){
	      $data = '';
	   }
	}
    return $data;
}

/**
* 字符集转换
*/
function auto_charset($fContents, $from='gbk', $to='utf-8') {
    $fContents = strip_tags($fContents);
    $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
    $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
    if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
        //如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if (is_string($fContents)) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    } elseif (is_array($fContents)) {
        foreach ($fContents as $key => $val) {
            $_key = auto_charset2($key, $from, $to);
            $fContents[$_key] = auto_charset2($val, $from, $to);
            if ($key != $_key)
                unset($fContents[$key]);
        }
        return $fContents;
    }else {
        return $fContents;
    }
}

/**
 * 会员等级最低值与最高值
 * @param array $integral  等级区间
 */
function memberGrade($integral){

	$config = C('USER_RANK');
	foreach ($config as $key => $val){
		if($integral > $val['mincosts'] && $integral <= $val['maxcosts']){
			$result['mincosts'] = $val['mincosts'];
			$result['maxcosts'] = $val['maxcosts'];
			break;
		}
		else{
			continue;
		}
	}
	return $result;

}

//替换医生前端名字
function replaceDoctorName($name){
	$surname = mb_substr($name,0,1,'utf8');
	return $surname.'药师';
}

//得到随机唯一user_name
function getRandOnlyId() {
    $user_name = uniqid() . mt_rand(0,9);
    return $user_name;
}

//文章内容替换处理
function getArcBody($body){
    $res = str_replace('#p#副标题#e#','',$body);
    $res = preg_replace("/<a[^>]*>(.*)<\/a>/isU",'${1}',$res);
    return $res;
}


// HTML字符实体转换
function html2text($str){
   $str = preg_replace("/&nbsp;/i", " ", $str);
   $str = preg_replace("/&nbsp/i", " ", $str);
   $str = preg_replace("/&amp;/i", "&", $str);
   $str = preg_replace("/&amp/i", "&", $str);
   $str = preg_replace("/&lt;/i", "<", $str);
   $str = preg_replace("/&lt/i", "<", $str);
   $str = preg_replace("/&ldquo;/i", '"', $str);
   $str = preg_replace("/&ldquo/i", '"', $str);
   $str = preg_replace("/&lsquo;/i", "'", $str);
   $str = preg_replace("/&lsquo/i", "'", $str);
   $str = preg_replace("/&rsquo;/i", "'", $str);
   $str = preg_replace("/&rsquo/i", "'", $str);
   $str = preg_replace("/&gt;/i", ">", $str);
   $str = preg_replace("/&gt/i", ">", $str);
   $str = preg_replace("/&rdquo;/i", '"', $str);
   $str = preg_replace("/&rdquo/i", '"', $str);
   $str = strip_tags($str);
   $str = html_entity_decode($str, ENT_QUOTES, "utf-8");
   $str = preg_replace("/&#.*?;/i", "", $str);
   return $str;
}

/**
* discuz  标签内容解析
*/
function discuzcode($message, $smileyoff=1, $bbcodeoff=0, $htmlon = 0, $allowsmilies = 1, $allowbbcode = 1, $allowimgcode = 1, $allowhtml = 0, $jammer = 0, $parsetype = '0', $authorid = '0', $allowmediacode = '0', $pid = 0, $lazyload = 0, $pdateline = 0, $first = 0) {
	global $_G;

	static $authorreplyexist;

	// 	if($pid && strpos($message, '[/password]') !== FALSE) {
	// 		if($authorid != $_G['uid'] && !$_G['forum']['ismoderator']) {
	// 			$message = preg_replace("/\s?\[password\](.+?)\[\/password\]\s?/ie", "parsepassword('\\1', \$pid)", $message);
	// 			if($_G['forum_discuzcode']['passwordlock'][$pid]) {
	// 				return '';
	// 			}
	// 		} else {
	// 			$message = preg_replace("/\s?\[password\](.+?)\[\/password\]\s?/ie", "", $message);
	// 			$_G['forum_discuzcode']['passwordauthor'][$pid] = 1;
	// 		}
	// 	}

	if($parsetype != 1 && !$bbcodeoff && $allowbbcode && (strpos($message, '[/code]') || strpos($message, '[/CODE]')) !== FALSE) {
		$message = preg_replace("/\s?\[code\](.+?)\[\/code\]\s?/ies", "codedisp('\\1')", $message);
	}

	$msglower = strtolower($message);

	$htmlon = $htmlon && $allowhtml ? 1 : 0;
	/*
	 if(!$htmlon) {
	 $message = dhtmlspecialchars($message);
	 } else {
	 $message = preg_replace("/<script[^\>]*?>(.*?)<\/script>/i", '', $message);
	 }

	 if($_G['setting']['plugins']['func'][HOOKTYPE]['discuzcode']) {
	 $_G['discuzcodemessage'] = & $message;
	 $param = func_get_args();
	 hookscript('discuzcode', 'global', 'funcs', array('param' => $param, 'caller' => 'discuzcode'), 'discuzcode');
	 }

	 //表情解析
	 if(!$smileyoff && $allowsmilies) {
	 $message = parsesmiles($message);
	 }

	 if($_G['setting']['allowattachurl'] && strpos($msglower, 'attach://') !== FALSE) {
	 $message = preg_replace("/attach:\/\/(\d+)\.?(\w*)/ie", "parseattachurl('\\1', '\\2', 1)", $message);
	 }

	 if($allowbbcode) {
	 if(strpos($msglower, 'ed2k://') !== FALSE) {
	 $message = preg_replace("/ed2k:\/\/(.+?)\//e", "parseed2k('\\1')", $message);
	 }
	 }
	 */

	if(!$bbcodeoff && $allowbbcode) {
		if(strpos($msglower, '[/url]') !== FALSE) {
			$message = preg_replace("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.|mailto:)?([^\r\n\[\"']+?))?\](.+?)\[\/url\]/ies", "parseurl('\\1', '\\5', '\\2')", $message);
		}
		if(strpos($msglower, '[/email]') !== FALSE) {
			$message = preg_replace("/\[email(=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+))?\](.+?)\[\/email\]/ies", "parseemail('\\1', '\\4')", $message);
		}

		$nest = 0;
		while(strpos($msglower, '[table') !== FALSE && strpos($msglower, '[/table]') !== FALSE){
			$message = preg_replace("/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/ies", "parsetable('\\1', '\\2', '\\3')", $message);
			if(++$nest > 4) break;
		}

		$message = str_replace(array(
				'[/color]', '[/backcolor]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]', '[s]', '[/s]', '[hr]', '[/p]',
				'[i=s]', '[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
				'[list=A]', "\r\n[*]", '[*]', '[/list]', '[indent]', '[/indent]', '[/float]'
		), array(
				'</font>', '</font>', '</font>', '</font>', '</div>', '<strong>', '</strong>', '<strike>', '</strike>', '<hr class="l" />', '</p>', '<i class="pstatus">', '<i>',
				'</i>', '<u>', '</u>', '<ul>', '<ul type="1" class="litype_1">', '<ul type="a" class="litype_2">',
				'<ul type="A" class="litype_3">', '<li>', '<li>', '</ul>', '<blockquote>', '</blockquote>', '</span>'
		), preg_replace(array(
				"/\[color=([#\w]+?)\]/i",
				"/\[color=((rgb|rgba)\([\d\s,]+?\))\]/i",
				"/\[backcolor=([#\w]+?)\]/i",
				"/\[backcolor=((rgb|rgba)\([\d\s,]+?\))\]/i",
				"/\[size=(\d{1,2}?)\]/i",
				"/\[size=(\d{1,2}(\.\d{1,2}+)?(px|pt)+?)\]/i",
				"/\[font=([^\[\<]+?)\]/i",
				"/\[align=(left|center|right)\]/i",
				"/\[p=(\d{1,2}|null), (\d{1,2}|null), (left|center|right)\]/i",
				"/\[float=left\]/i",
				"/\[float=right\]/i"

		), array(
				"<font color=\"\\1\">",
				"<font style=\"color:\\1\">",
				"<font style=\"background-color:\\1\">",
				"<font style=\"background-color:\\1\">",
				"<font size=\"\\1\">",
				"<font style=\"font-size:\\1\">",
				"<font face=\"\\1\">",
				"<div align=\"\\1\">",
				"<p style=\"line-height:\\1px;text-indent:\\2em;text-align:\\3\">",
				"<span style=\"float:left;margin-right:5px\">",
				"<span style=\"float:right;margin-left:5px\">"
		), $message));

		if($pid && !defined('IN_MOBILE')) {
			$message = preg_replace("/\s?\[postbg\]\s*([^\[\<\r\n;'\"\?\(\)]+?)\s*\[\/postbg\]\s?/ies", "parsepostbg('\\1', '$pid')", $message);
		} else {
			$message = preg_replace("/\s?\[postbg\]\s*([^\[\<\r\n;'\"\?\(\)]+?)\s*\[\/postbg\]\s?/is", "", $message);
		}

		if($parsetype != 1) {
			if(strpos($msglower, '[/quote]') !== FALSE) {
				$message = preg_replace("/\s?\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s?/is", tpl_quote(), $message);
			}
			if(strpos($msglower, '[/free]') !== FALSE) {
				$message = preg_replace("/\s*\[free\][\n\r]*(.+?)[\n\r]*\[\/free\]\s*/is", tpl_free(), $message);
			}
		}
		if(!defined('IN_MOBILE')) {
			if(strpos($msglower, '[/media]') !== FALSE) {
				$message = preg_replace("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/ies", $allowmediacode ? "parsemedia('\\1', '\\2')" : "bbcodeurl('\\2', '<a href=\"{url}\" target=\"_blank\">{url}</a>')", $message);
			}
			if(strpos($msglower, '[/audio]') !== FALSE) {
				$message = preg_replace("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/ies", $allowmediacode ? "parseaudio('\\2', 400)" : "bbcodeurl('\\2', '<a href=\"{url}\" target=\"_blank\">{url}</a>')", $message);
			}
			if(strpos($msglower, '[/flash]') !== FALSE) {
				$message = preg_replace("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/ies", $allowmediacode ? "parseflash('\\2', '\\3', '\\4');" : "bbcodeurl('\\4', '<a href=\"{url}\" target=\"_blank\">{url}</a>')", $message);
			}
		} else {
			if(strpos($msglower, '[/media]') !== FALSE) {
				$message = preg_replace("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/is", "[media]\\2[/media]", $message);
			}
			if(strpos($msglower, '[/audio]') !== FALSE) {
				$message = preg_replace("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/is", "[media]\\2[/media]", $message);
			}
			if(strpos($msglower, '[/flash]') !== FALSE) {
				$message = preg_replace("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", "[media]\\4[/media]", $message);
			}
		}

		if($parsetype != 1 && $allowbbcode < 0 && isset($_G['cache']['bbcodes'][-$allowbbcode])) {
			$message = preg_replace($_G['cache']['bbcodes'][-$allowbbcode]['searcharray'], $_G['cache']['bbcodes'][-$allowbbcode]['replacearray'], $message);
		}
		if($parsetype != 1 && strpos($msglower, '[/hide]') !== FALSE && $pid) {
			if($_G['setting']['hideexpiration'] && $pdateline && (TIMESTAMP - $pdateline) / 86400 > $_G['setting']['hideexpiration']) {
				$message = preg_replace("/\[hide[=]?(d\d+)?[,]?(\d+)?\]\s*(.*?)\s*\[\/hide\]/is", "\\3", $message);
				$msglower = strtolower($message);
			}
			if(strpos($msglower, '[hide=d') !== FALSE) {
				$message = preg_replace("/\[hide=(d\d+)?[,]?(\d+)?\]\s*(.*?)\s*\[\/hide\]/ies", "expirehide('\\1','\\2','\\3', $pdateline)", $message);
				$msglower = strtolower($message);
			}
			if(strpos($msglower, '[hide]') !== FALSE) {
				if($authorreplyexist === null) {
					if(!$_G['forum']['ismoderator']) {
						if($_G['uid']) {
							$authorreplyexist = C::t('forum_post')->fetch_pid_by_tid_authorid($_G['tid'], $_G['uid']);
						}
					} else {
						$authorreplyexist = TRUE;
					}
				}
				if($authorreplyexist) {
					$message = preg_replace("/\[hide\]\s*(.*?)\s*\[\/hide\]/is", tpl_hide_reply(), $message);
				} else {
					$message = preg_replace("/\[hide\](.*?)\[\/hide\]/is", tpl_hide_reply_hidden(), $message);
					$message = '<script type="text/javascript">replyreload += \',\' + '.$pid.';</script>'.$message;
				}
			}
			if(strpos($msglower, '[hide=') !== FALSE) {
				$message = preg_replace("/\[hide=(\d+)\]\s*(.*?)\s*\[\/hide\]/ies", "creditshide(\\1,'\\2', $pid, $authorid)", $message);
			}
		}
	}

	if(!$bbcodeoff) {
		if($parsetype != 1 && strpos($msglower, '[swf]') !== FALSE) {
			$message = preg_replace("/\[swf\]\s*([^\[\<\r\n]+?)\s*\[\/swf\]/ies", "bbcodeurl('\\1', ' <img src=\"'.STATICURL.'image/filetype/flash.gif\" align=\"absmiddle\" alt=\"\" /> <a href=\"{url}\" target=\"_blank\">Flash: {url}</a> ')", $message);
		}

		if(defined('IN_MOBILE') && !defined('TPL_DEFAULT') && !defined('IN_MOBILE_API')) {
			$allowimgcode = false;
		}
		$attrsrc = !IS_ROBOT && $lazyload ? 'file' : 'src';

		if(strpos($msglower, '[/img]') !== FALSE) {

			$message = preg_replace(array(
					"/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies",
					"/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/ies"
			), $allowimgcode ? array(
					"parseimg(0, 0, '\\1', ".intval($lazyload).", ".intval($pid).", 'onmouseover=\"img_onmouseoverfunc(this)\" ".($lazyload ? "lazyloadthumb=\"1\"" : "onload=\"thumbImg(this)\"")."')",
					"parseimg('\\1', '\\2', '\\3', ".intval($lazyload).", ".intval($pid).")"
			) : ($allowbbcode ? array(
					(!defined('IN_MOBILE') ? "bbcodeurl('\\1', '<a href=\"{url}\" target=\"_blank\">{url}</a>')" : "bbcodeurl('\\1', '')"),
					(!defined('IN_MOBILE') ? "bbcodeurl('\\3', '<a href=\"{url}\" target=\"_blank\">{url}</a>')" : "bbcodeurl('\\3', '')"),
			) : array("bbcodeurl('\\1', '{url}')", "bbcodeurl('\\3', '{url}')")), $message);
			 
		}
	}

	for($i = 0; $i <= $_G['forum_discuzcode']['pcodecount']; $i++) {
		$message = str_replace("[\tDISCUZ_CODE_$i\t]", $_G['forum_discuzcode']['codehtml'][$i], $message);
	}

	unset($msglower);

	if($jammer) {
		$message = preg_replace("/\r\n|\n|\r/e", "jammer()", $message);
	}
	if($first) {
		if(helper_access::check_module('group')) {
			$message = preg_replace("/\[groupid=(\d+)\](.*)\[\/groupid\]/i", lang('forum/template', 'fromgroup').': <a href="forum.php?mod=forumdisplay&fid=\\1" target="_blank">\\2</a>', $message);
		} else {
			$message = preg_replace("/(\[groupid=\d+\].*\[\/groupid\])/i", '', $message);
		}

	}
	return $message;
	//return $htmlon ? $message : nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
}

function getCouponCode(){//优惠券编码
	$rand = rand(10,99);
	$code= $rand.time().substr(microtime(),2,4);
	return $code;
}

/*==============================201406=============================================*/
/*
 * mssql
 * 会员接口操作
 * $data 数据
 * $is_i_u_d 改值用于判断是否添加删除修改动作 地区表：0添加，1修改，2删除 ；会员表：0添加，1修改，2删除，3会员登录,4会员手机判重验证,5会员手机重置密码验证,6用户名（user_name）判重验证
 * $ismember 0表示接收是user_ddress地址信息,1表示接收是member会员信息
 */
function add_member($data,$is_i_u_d =0,$ismember=0,$timeout)
{
    $url = 'http://172.16.0.2/API/member/Apreceive_member.aspx';
    $data['is_i_u_d'] = $is_i_u_d;
    $data['ismember'] = $ismember;
    $return = cpost($url,$data,$timeout);
    return $return;
}

/**
 * 根据地址ID获取中文名字
 * @param int $region_id 地址ID
 * @return Ambigous <mixed, NULL, multitype:Ambigous <unknown, string> unknown , multitype:>
 */
function get_region_name( $region_id ){
    return M('Region')->where( array('region_id'=>$region_id) )->getField('region_name');
}

/**
 * 自动转换字符集【支持数组转换】
 *
 */
function auto_charset2($fContents, $from='gbk', $to='utf-8') {
    $fContents = strip_tags($fContents);
    $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
    $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
    if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {
        //如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if (is_string($fContents)) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    } elseif (is_array($fContents)) {
        foreach ($fContents as $key => $val) {
            $_key = $this->auto_charset2($key, $from, $to);
            $fContents[$_key] = $this->auto_charset2($val, $from, $to);
            if ($key != $_key)
                unset($fContents[$key]);
        }
        return $fContents;
    }else {
        return $fContents;
    }
}

/**
 * discuz 模拟用户登录 。。。发帖，回帖，点评都需要先模拟用户登录
 * $username 用户名
 * $password 密码
 */
function discuz_login($username,$password){
		
	$discuz_url = C('UCENTER_SERVER_URL');//论坛地址
	$login_url = $discuz_url.'member.php?mod=logging&action=login';//登录页地址
		
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
/*
 * 处理发表时间
 * @param  int $replytime 发表的时间
 *
 */
function create_time($replytime=''){
	if(!is_numeric($replytime) && $replytime <= 0){
		return '';
	}

	$time = time();

	//计算时间
	$diff_time = $time-$replytime;

	if($diff_time<60){
		$new_time = $diff_time.'秒前';
	}elseif ($diff_time<3600){
		$new_time = round($diff_time/60).'分钟前';
	}elseif ($diff_time<86400){
		$new_time = round($diff_time/3600).'小时前';
	}elseif ($diff_time<259200){
		$new_time = round($diff_time/86400).'天前';
	}else{
		$new_time = '大于一个月前';
	}

	return $new_time;
}
function get_image_path($file){
	if(!empty($file)){
		$files=explode('.', $file);
		$img= $files[0]."_".C("img_width")."x".C("img_heigth")."_".C("img_quality").".".$files[1];

		return $img;
	}else{
		return C("APP_IMG_URL")."/Uploads/Picture/onimg.png";
	}

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
