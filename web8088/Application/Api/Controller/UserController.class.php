<?php

namespace Api\Controller;
use Api\Controller\CommonController;

/**
 * 
 * @author user
 *
 */
class UserController extends CommonController {


    /**
     *
     * 每页显示数量
     * @var munber
     */
    public $perPage = 10;
    
    
    /**
     * 通过AJAX获取当前页面的url地址，用于后续的操作
     */
    public function nowshref(){

    	$nowhref=I('post.nowhref');

    	session('historyhref',$nowhref);
    	$this->ajaxReturn(array(
    			'data'=>200,
    			'msg'=>'ok',
    	));
    }
    
    
    /**
     * AJAX上传用户头像
     * $_FILES['useravatar']
     */
    public function uploadavatar(){
        if (!session('?user')){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'msg'       =>  '请先登录！',
            ));
        }
        $relstatus = D('User')->setUserAvatar();
        if ($relstatus!==true){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'msg'       =>  $relstatus,
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'msg'       =>  $relstatus,
        ));
    }
    
    
    
    public function feedback(){
        if (!session('?user')){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'msg'       =>  '请先登录！',
            ));
        }
        $Feedback 	= D('Feedback');
        $rel		= $Feedback->addFeedback();//插入反馈信息,$_POST['feedback']
        if ($rel===true) {
            $this->ajaxReturn(array(
                'status'    =>  200,
                'msg'       =>  '反馈成功',
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  400,
            'msg'       =>  $rel,
        ));
    }
    
    
    
    
//====================以下的全部都测试方法，废用==========================
	
	
	
	
    /**
     * 注册一个用户
     */
    public function userAdd() {
		$User = D('User');
    	$result = $User->addOne();
    	if ($result !== true) {// 增加失败
    		$this->ajaxReturn(array(
    				'status'	=>	400,
    				'msg'		=>	$result,
    		));
    	}
    	$this->ajaxReturn(array(// 增加成功
    			'status'	=>	200,
    			'msg'		=>	$result,
    	));
    }
    
    
    
    /**
     * 用户登陆
     */
    public function userLogin() {
		$User = D('User');
    	$result = $User->userLogin();
    	if ($result !== true) {
    		$this->ajaxReturn(array(
    				'status'	=>	400,
    				'msg'		=>	$result,
    		));
    	}
    	$this->ajaxReturn(array(
    			'status'	=>	200,
    			'msg'		=>	'登陆成功',
    	));
    }
    

    
    /**
     * 用户注销登录
     */
    public function userloginout(){
    	$User = D('User');
    	$result = $User->userLogout();
    	if ($result !== true) {
    		$this->ajaxReturn(array(
    				'status'	=>	400,
    				'msg'		=>	$result,
    		));
    	}
    	$this->ajaxReturn(array(
    			'status'	=>	200,
    			'msg'		=>	'登陆成功',
    	));
    }
    
    
    
    /**
     * 用户忘记密码，发送验证码到用户手机或者邮箱,并且重新设置密码
     */
    public function sendUserEmail() {
		$User = D('User');
    	$result = $User->checkSend();
    	if ($result !== true) {
    	$this->ajaxReturn(array(
	    			'status'	=>	400,
	    			'msg'		=>	$result,
    	));
    	}
    	$this->ajaxReturn(array(
	    	'status'	=>	200,
	    	'msg'		=>	'校验码已发送',
    	));
    }

    
    
    /**
     * 获取总的注册用户数
     */
    public function allUser(){
    	$User = D('User');
    	$resule = $User->userAll();
    	if ($resule !== false) {
	    	$this->ajaxReturn(array(
		    			'status'	=>	400,
		    			'msg'		=>	'无法获取用户总注册数',
	    	));
    	}
    	$this->ajaxReturn(array(
	    	'status'	=>	200,
	    	'msg'		=>	$resule,
    	));
    }
    
    
    /**
     * 更新用户的详细资料
     */
    public function consummateUser(){
    	$User = D('User');
    	$resule = $User->perfectUser();
    	print_r($resule);exit;
    	if ($resule !== 1) {					//1----true
    		$this->ajaxReturn(array(
    				'status'	=>	400,
    				'msg'		=>	$resule,
    		));
    	}
    	$this->ajaxReturn(array(
    			'status'	=>	200,
    			'msg'		=>	'已保存',
    	));
    }
    

    
    
    
    /**
     * 手机号码验证   未
     */
    public function phoneverify(){
    	$User = D('User');
    	$resule = $User->phoneStatus();
    	print_r($resule);exit;
    }
    
    
    

    /**
     * @todo 发送用户验证邮件
     */
    public function sendActiveEmail($uid=0, $email='') {
    	$Useractive=D('Useractive');
    	$result =$Useractive->sendActiveEmail($uid, $email, 'handleActive');
    	$this->simpleAjaxReturn($result);
    }
    
    /**
     * 激活某位用户的邮箱
     * return true | false
     */
    public function handleActive() {
    	$token=I('get.token');
    	$Useractive=D('Useractive');
    	$result = $Useractive->handleActive($token);
    	print_r($result);exit;
    	$this->simpleAjaxReturn($result);
    }
    
    
    
}