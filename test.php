<?php
// $a1=array("a"=>"red","b"=>"green","c"=>"blue","d"=>"yellow");
// $a2=array("e"=>"red1","f"=>"green","g"=>"blue");

// $result=array_diff($a1,$a2);
// print_r($result);
header("Content-type:text/html;charset=utf-8");
// var_dump(memory_get_usage(true));
// $a = "laruence";
// var_dump(memory_get_usage(true));
// unset($a);
// var_dump(memory_get_usage(true));
ob_start();
echo 'Text that won\'t get displayed.';
$output = ob_get_contents();
ob_end_clean();
echo $output;