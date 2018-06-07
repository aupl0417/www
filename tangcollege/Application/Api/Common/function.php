<?php

/**
 * 跟系统的http_build_str()功能相同，但不用安装pecl_http扩展
 *
 * @param array $array      需要组合的数组
 * @param string $separator 连接符
 *
 * @return string               连接后的字符串
 * eg: 举例说明
 */
function http_build_string ( $array, $separator = '&' ) {
    $string = '';
    foreach ( $array as $key => $val ) {
        $string .= "{$key}={$val}{$separator}";
    }
    //去掉最后一个连接符
    return substr( $string, 0, strlen( $string ) - strlen( $separator ) );
}

/*
* 	返回数据到客户端
*	@param $code type : int		状态码
*   @param $info type : string  状态信息
*	@param $data type : mixed	要返回的数据
*	return json
*/
function jsonReturn($code, $info = '', $data = null){
	header('Content-Type:application/json; charset=utf-8');//返回JSON数据格式到客户端 包含状态信息
	include_once( APP_PATH . '/Api/Exception/message.php' );//引入状态信息数组 $msg
	
	$jsonData = array(
		'code' => false === array_key_exists($code, $msg) ? -1 : $code,
		'msg'  => empty($info) ? (false === array_key_exists($code, $msg) ? '未定义异常信息' : $msg[$code]) : $info,
		'data' => $data
	);
	
	exit(json_encode($jsonData));
}
