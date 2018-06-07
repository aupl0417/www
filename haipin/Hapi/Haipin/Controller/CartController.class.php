<?php

namespace Haipin\Controller;
use Think\Controller;

class CartController extends Controller {

    public function __construct(){
    	
    }
    
    public function get() {
        $return = array(
			'param1' => 1,
			'param2' => 2,
			'param3' => 3,
		);
        return $return;
    }
}
?>