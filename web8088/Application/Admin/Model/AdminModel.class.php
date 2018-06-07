<?php

namespace Admin\Model;
use Common\Model\CommonModel;

/**
 * 
 * @author jmjoy
 *
 */
class AdminModel extends CommonModel {
	
	protected $fields = array('id', 'username', 'password');
	protected $pk     = 'id';

	protected $_map = array(
			'name' =>'username',
			'passwd'  =>'password',
	);
	
	protected $_validate=array(
			array('username','require','用户名不能为空'),
			array('password','require','密码不能为空'),
			array('verify','require','验证码不能为空', 1),
			array('verify','checkVerify','验证码错误', 1, 'callback'),
	);
	
	/**
	 * 处理用户登录
	 * @return boolean|string 登陆成功返回true，登陆失败返回失败消息
	 */
	public function handleLogin() {
		if (!IS_POST) {
			return false;
		}
		C('TOKEN_ON', false);
		if (!$this->create()){
		// 如果创建失败 表示验证没有通过 输出错误提示信息
			return $this->getError();
		}
		$form_username = $this->username;
		$form_password = $this->password;
   		// 验证通过 验证用户名和密码
   		$result = $this->field(true)
   						->where("username = '%s'", $form_username)
   						->find();
   		$err = '用户名或者密码错误！';
   		if (!$result) {
   			return $err;
   		}
   		if ($result['password'] != encrypt_passwd($form_password)) {
   			return $err;
   		}
   		// 登陆成功
   		session('admin_id', $result['id']);
   		session('admin_name', $result['username']);
   		return true;
	}
	
	/**
	 * 处理注销
	 */
	public function handleLogout() {
		session('admin_id', null);	
		session('admin_name', null);
	}
	
	/**
	 * 获取验证码
	 */
	public function getVerify() {
		$Verify = new \Think\Verify();
		$Verify->fontSize = 16;
		$Verify->length   = 4;
		$Verify->useNoise = false;
		$Verify->imageW   = 120;
		$Verify->imageH   = 40;
		$Verify->entry();
	}
	
	/**
	 * 验证码是否正确
	 * @param boolean $reset 验证成功后是否重置
	 * @return boolean
	 */
	public function checkVerify($code, $reset = true){
		$verify = new \Think\Verify();
		$verify->reset = $reset;
		return $verify->check($code);
	}
	
}