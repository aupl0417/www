<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2017/4/27
 * Time: 8:55
 */

$arr = array(
    array('id' => 1, 'name' => 'larry', 'age' => 25),
    array('id' => 2, 'name' => 'john', 'age' => 26),
    array('id' => 3, 'name' => 'anny', 'age' => 27),
);

$c = array_map(function (&$val){
    $val['addr'] = $val['id'] * $val['age'];
    return $val;
}, $arr);
var_dump($c);

$data = array();
$data = array_pad($data, 10, 'noop');
var_dump($data);

$array1 = array(0 => 'zero_a', 2 => 'two_a', 3 => 'three_a');
$array2 = array(1 => 'one_b', 3 => 'three_b', 4 => 'four_b');
$result = $array1 + $array2;
var_dump($result);

$data1 = array(0 => 'zero_a', 2 => 'two_a', 3 => 'three_a');
$data2 = array();
$data1 = array_merge($data1, $data2);
var_dump($data1);