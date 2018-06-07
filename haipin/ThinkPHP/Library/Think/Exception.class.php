<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think;
/**
 * ThinkPHP系统异常基类
 */
class Exception extends \Exception {
	
/**
	 * 构造方法
	 *
	 * @param int $code
	 * @param string $message
	 */
	public function __construct($code, $message = null) {
		//确保$code不为null，子类已定义$_codeList，并且$code在$_codeList预定义范围内
		if (empty($code) || empty($this->_codeList) || false === array_key_exists($code, $this->_codeList)) {
			$code = -1;
			$message = '未定义异常信息';
		}
		
		if (empty($message)) {
			$message = $this->_codeList[$code];
		}
		
		parent::__construct($message, $code);
	}
}