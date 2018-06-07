<?php
namespace Admin\Controller;
use Think\Controller;

/**
 * 
 * @author jmjoy
 *
 */
class SignController extends Controller {
	
	/**
	 * 登陆页面
	 * 
	 */
    public function index() {
    	// Flash Cookie
		$this->msg = cookie('login_msg');
		$this->name = cookie('login_name');
		cookie('login_msg', null);
		// display
    	$this->display();
    }
	
    /**
     * 处理用户登录
     */
    public function handleLogin() {
    	// 登陆表单处理
 		$result = D('Admin')->handleLogin();
 		// 检验登陆成功与否
 		if ($result !== true) {
 			// 登陆失败
 			cookie("login_name", I('post.name'));
 			cookie("login_msg", $result);
 			$this->redirect('index');
 		}
 		// 登陆成功
 		$this->redirect('Index/index');
    }
    
    /**
     * 处理注销
     */
    public function handleLogout() {
		D('Admin')->handleLogout();
		$this->redirect('Sign/index');
    }
    
    /**
     * 获取验证码图片
     */
    public function getVerify() {
		D('Admin')->getVerify();
    }

}