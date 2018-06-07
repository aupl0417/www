<?php

namespace Module;

class OutputHandler {
	
    public function __construct(){
    	
    }
    
    /**
     * 获取输出信息
     * @access public
     */
    public static function getOutput($out_result, $code = 200, $msg = 'success')
    {
        $object = (object)($out_result);
        $result = array(
            'code' => $code,
            'msg' => $msg,
            'result' => $object
        );
        $result = formatResult($result);
        $result = charsetResult($result);
        return $result;
    }
}
?>