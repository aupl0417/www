<?php

namespace Common\Controller;
use Think\Controller;

class BaseController extends Controller {

    /**
     * 我弄的简单的返回接口的方式！
     * @param boolean|string $result
     */
    public function simpleAjaxReturn($result) {
        if ($result !== true) {
            $this->ajaxReturn(array(
                    'status'	=>	400,
                    'msg'		=>	$result,
            ));
        }
        $this->ajaxReturn(array(
                'status'	=>	200,
                'msg'		=>	'',
        ));
    }

}
