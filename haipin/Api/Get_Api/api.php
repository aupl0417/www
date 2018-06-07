<?php
header("Content-type: text/html; charset=utf-8");

//test
// $arr = array('ver' => '1.0', 'api_key' =>'381e0d75c2f0d9687b13f7345b009f03' , 'submit' => 'login');
// echo '381e0d75c2f0d9687b13f7345b009f03';
// echo '</br>';
// echo authSign($arr);exit;
//test



$url_str ='';
$url_str = @$_GET['stringquery'];
if(empty($url_str)) {
echo '<script src="./jquery-1.8.0.min.js"></script>
<script>
$(document).ready(function(){
  $("#btn2").click(function(){
    $("#test2").html("service=haipin.index.test&api_key=45427346a3dea51c7a10fe6e3584f267&ver=1.0");
  });
});
</script>';
echo '<p>本地中间层接口</p>
<p>
<input type="submit" name="Submit" id="btn2" value="haipin.index.test" />

</p>
<form id="form1" name="form1" method="get" target="_blank" action="" enctype="multipart/form-data">
    
    <p><textarea style=" width:800px; height:150px" id="test2" name="stringquery">' .$url_str. '</textarea></p><input type="submit" name="Submit"  value="提交" /></form>';
}

parse_str($url_str, $arry);
//$arry['uploadfile']=$_FILES;
//var_dump($arry);
$api_sign = authSign($arry);
// var_dump($api_sign);
// var_dump($arry);

$url_str = str_replace(' ', '+', $url_str);
$url = 'api.taqu.haipin.com/index.php?&' . $url_str . '&api_sign='.$api_sign;

$result=curl_get($url);
echo $result;
/**
 * 数字签名生成
 */
function authSign($request) {
     $sign = '';
     if (is_array($request)) {
         ksort($request);        
         while (list($key, $val) = each($request)) {
             if ($key == 'api_sign' || $val === '' || $key == 'service') {
                 continue;
             }
             // $val = str_replace(' ', '+', $val);
             $sign .= $val;
         }
         if ($sign) {
             $sign .= '1151a0c9bf14fd77dddc00a42186b9d9';
             $sign = md5($sign);
         } 	
     }
     return $sign;
 }
 


function curl_post($uri) { 
	$data = array (		
	);

	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $uri );
	curl_setopt ( $ch, CURLOPT_POST, 1 );
	curl_setopt ( $ch, CURLOPT_HEADER, 0 );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
	$return = curl_exec ( $ch );
	curl_close ( $ch );
}

function curl_get($url) { 
	$ch = curl_init($url) ;
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ;
	curl_setopt($ch, CURLOPT_TIMEOUT,30); 
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; 
	$output = curl_exec($ch) ;
	curl_close ($ch);
    return $output;
}