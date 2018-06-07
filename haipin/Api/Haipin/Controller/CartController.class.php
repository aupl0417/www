<?php

namespace Haipin\Controller;
use Think\Controller;

class CartController extends Controller {
    public function __construct(){
    
    }
    
    public function _empty(){
    	Ex(403);
    }
    
    public function get(){
    	$service = 'haipin.cart.get';
    	$user_id = I('request.uid', 0, 'intval');
        $unique_code = I('request.unique_code', '', 'trim');	//唯一标识符

        // 用户不能为空
        if (! $unique_code && ! $user_id>0) {
        	Ex(1003);
        }

        $request  = I('request.');
        $result   = \Module\Curl::getApi($service,$request);
        return  $result;
    }
}
?>