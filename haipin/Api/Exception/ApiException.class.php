<?php
namespace Exception;

class ApiException extends \Think\Exception {
    /**
     * 通用错误信息数组
     */
    protected $_codeList = array(

        //系统级别
        400  => 'API KEY ERROR',
        401  => 'SIGNATURE VERIFICATION FAILED',
        402  => 'INVALID VERSION',
        403  => 'INVALID SERVICE',
        404  => 'SERVICE NO FOUND',
        405  => 'SERVICE BUSY',   // 防刷新
    );

    public function __construct($code, $message = null)
    {
        parent::__construct($code, $message);
    }
}