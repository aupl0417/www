<?php
namespace Home\Controller;
use Common\Controller\BaseController;

class EmptyController extends BaseController {

    function _empty() {
        header("HTTP/1.0 404 Not Found");//使HTTP返回404状态码
        $this->display("Home/404");
    }

}
