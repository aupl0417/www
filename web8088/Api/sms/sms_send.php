<?php
//56短信网php短信接示例(http://www.56dxw.com)
// session_start();
error_reporting(0);//禁用错误报告
header("content-type:text ml;charset=utf-8");
//帐号配置文件
// $comid= "3365"; //企业ID
// $username= "test106"; //用户名
// $userpwd= "test106"; //密码
// $smsnumber= "10690"; //所用平台
define('smscomid','2246'); //企业ID
define('smsusername','17yueke'); //用户名
define('smsuserpwd','yk8h5g3k'); //密码
define('smsnumber','10690'); //所用平台

/**
 * 根据传进来的$length的长度参数，生成验证码
 * @param unknown $length
 * @return string
 */
function randomkeys($length){
   $pattern = '1234567890';    //字符池,可任意修改
   for($i=0;$i<$length;$i++){
       $key .= $pattern{mt_rand(0,35)};    //生成php随机数
   }
   return $key;
}


// function rstr($str){
//     print($str);
//     exit();
// }
/**
 * $mobtel手机号码 返回发送返回码
 * $msg发送的信息
 * @param unknown $mobtel
 * @param unknown $msg
 * @return string
 */
function sendnote($mobtel,$msg){
     global $username,$userpwd,$smsnumber,$comid;
//      $url = "http://jiekou.56dxw.com/sms/HttpInterface.aspx?comid=$comid&username=$username&userpwd=$userpwd&handtel=$mobtel&sendcontent=$msg&sendtime=&smsnumber=$smsnumber";
     $url = "http://jiekou.56dxw.com/sms/HttpInterface.aspx?comid="
             .smscomid."&username="
             .smsusername."&userpwd="
             .smsuserpwd."&handtel="
             .$mobtel."&sendcontent="
             .$msg."&sendtime=&smsnumber=".smsnumber;
     $string = file_get_contents($url);
     return  $string;
}

// $_SESSION["code"]=randomkeys(6);
// $handtel =$_POST["Tel"];
// $msg="您的手机验证码是:".$_SESSION["code"]."【乐莘网络】";//【56短信网】 可以换成自己的签名，签名一般用公司或网站的简称
// !$handtel && die('手机号必填');
// !$msg && die('发生内容必填');
// echo sendnote($_POST["Tel"],urlencode(mb_convert_encoding($msg, 'gbk' ,'utf-8')));//如果你网站或软件的格式是utf-8的，需要对发送的内容转成gbk的


//$fromuser=iconv("UTF-8","gb2312",$username);
//echo sendnote($_POST["Tel"],urlencode(iconv("UTF-8","gbk",$msg)));//如果你网站或软件的格式是utf-8的，需要对发送的内容转成gbk的
//echo sendnote($_POST["Tel"],urlencode($msg));//如果网站本身是gbk的编码，就不用转了
?>