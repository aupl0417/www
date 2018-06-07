<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2017/4/27
 * Time: 8:55
 */
set_time_limit(0);
$a = range(1, 10000);
$b = range(1, 10000);

$c = array();
$startTime = microtime(true);
//for ($i = 0; $i < 10000; $i++) {
//    $c[] = $a[$i] * $a[$i];
//}
//foreach($a as $key => $value) {
//    foreach ($b as $key1 => $value1) {
//        if ($key1 == $key) {
//            $c[] = $value * $value1;
//            unset($b[$key1]);
//            continue 2;
//        }
//    }
//}

//回调函数中传入的第一个参数的值为第一个数组的值，第二个参数为第二个数组的值，依次类推
$fun = function ($a, $b){
    return $a * $b;
};
$c = array_map($fun, $a, $b);
//var_dump(count($c));
$endTime = microtime(true);
$s_time = ($endTime - $startTime) * 1000;
/*
 * for   spend7.0011615753174ms
 * foreach spend2134.7711086273ms
 * array_map  spend75.510025024414ms
 * */
echo 'spend' . $s_time . 'ms';