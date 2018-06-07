<?php

namespace Student\Controller;
/**
 * 学生首页控制器
 */
class IndexController extends CommonController {

	
    public function index(){
         $url = 'http://www.tang.com/index.php/Api/Attendance/index.html';
		 $data['appId'] = 1;
		 $data['userId'] = '00086c9791e070ef24ed6a2c0803b0b2';
		 $data['periodId'] = 69;
		 ksort($data);
		 $queryString = http_build_query($data);
		 $secretKey= 'D8OZLSE2NEDC0FR4XTGBKHY67UJZ8IK9';
		 $sign = md5("{$queryString}&{$secretKey}");
		 $data['signValue'] = $sign;
         //$httpstr = http($url, $data, 'GET', array("Content-type: text/html; charset=utf-8"));
		 exit($url.'?'.$queryString.'&signValue='.$sign);
    }
}
