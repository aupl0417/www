<?php
header("Content-type: text/html; charset=utf-8");

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
echo '<p><b>开发机服务层</b></p>
<p>
<input type="submit" name="Submit" id="btn2" value="haipin.index.test" />
</p>
<form id="form1" name="form1" method="get" target="_blank" action=""><p><textarea style=" width:800px; height:150px" id="test2" name="stringquery">' .$url_str. '</textarea></p><input type="submit" name="Submit"  value="提交" /></form>';
}

parse_str($url_str, $arry);

$api_sign = authSign($arry);

$url = 'hapi.taqu.haipin.com/index.php?&' . $url_str . '&api_sign='.$api_sign;
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
			'name' => 'tanteng'
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