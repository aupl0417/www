<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2017/5/13
 * Time: 14:54
 */
    $arr1 = array(2, 1, array(5, 2, 1, array(9, array(5, 4, 7), 7)), 5, 0);

    function Reverse_Array($array){
        if(is_array($array)){
            $array = array_reverse($array);
            foreach($array as $key => &$val){
                if(is_array($val)){
                    $array[$key] = Reverse_Array($val);
                }else{
                    $array[$key] = $val;
                }
            }
        }

        return $array;
    }

    $arr2 = Reverse_Array($arr1);
    var_dump($arr2);