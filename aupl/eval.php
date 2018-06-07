<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2017/4/17
 * Time: 10:08
 */
//$str = "array('id' => 1, 'name' => 'aupl')";
//$result = eval("return $str;");
//var_dump($result);

//$ar1 = array("color" => array("favorite" => "red"), 5);
//$ar2 = array(10, "color" => array("favorite" => "green", "blue"));
//$result = array_merge_recursive($ar1, $ar2);
echo '<pre>';
//print_r($result);
$array = array(
    'a1' => array(
        'id' => 1,
        'name' => 'aupl1'
    ),
    'a2' => array(
        'id' => 2,
        'name' => 'aupl2'
    ),
    'a3' => array(
        'id' => 3,
        'name' => 'aupl3'
    ),
);

$arrayKeys = array();
foreach ($array as $key=>$value){
    $arrayKeys[] = md5(json_encode(array_keys($value)));
}
$arrayKeys = array_unique($arrayKeys);
print_r($arrayKeys);
?>