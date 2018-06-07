<?php
/**
 * Created by PhpStorm.
 * User: dttx
 * Date: 2017/3/17
 * Time: 15:52
 */

function array_map_recur($m, $n){
    if($m == $n){
        return $m;
    }
}
$arr1 = array(1,3,4,5,6);
$arr2 = array(1,3,6,5,7,8);
$b = array_map('array_map_recur', $arr1, $arr2);
var_dump($b);