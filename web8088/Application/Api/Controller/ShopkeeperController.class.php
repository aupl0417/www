<?php

namespace Api\Controller;
use Api\Controller\CommonController;
use Common\Model\ShopkeeperModel;
use Common\Model\ShopkeeperActiveModel;

/**
 *
 * @author jmjoy
 *
 */
class ShopkeeperController extends CommonController {

    /**
     * 商家模型
     * @var ShopkeeperModel
     */
    public $shopkeeperModel;

    /**
     * 每页显示数量
     * @var int
     */
    public $rowlist = 10;

    public function _initialize() {
        $this->shopkeeperModel = D('Common/Shopkeeper');
    }

    /**
     * 注册一个商家
     */
    public function handleAdd() {
    //	$result = $this->shopkeeperModel->addOne();
        // 增加失败
    //	$this->simpleAjaxReturn($result);
        $this->simpleAjaxReturn(array('status'=>200));
    }

    /**
     * 检测某一字段
     */
    public function validateField() {
        $type = I('post.type');
        $_POST[$type] = I('post.arg');
        $result = $this->shopkeeperModel->validateField($type);
        // 开始验证
        $this->simpleAjaxReturn($result);
    }

    /**
     * 校验登录账号和密码
     */
    public function validateLoginAndPassword() {
        // 验证手机或者邮箱
        switch (I('post.type')) {
        case 'email':
            $arr = ['login_email' => I('post.arg')];
            $result = $this->shopkeeperModel->validateField('login_email', $arr);
            break;

        case 'phone':
            $arr = ['login_phone' => I('post.arg')];
            $result = $this->shopkeeperModel->validateField('login_phone', $arr);
            break;

        default:
            $this->simpleAjaxReturn('邮箱或者手机号码不合法！');
        }
        if ($result !== true) {
            $this->simpleAjaxReturn($result);
        }

        // 验证密码
        $arr = ['password' => I('post.password')];
        $result = $this->shopkeeperModel->validateField('password', $arr);
        if ($result !== true) {
            $this->simpleAjaxReturn($result);
        }

        // 验证码校验
        $code = I('post.verify');
        $verify = new \Think\Verify(['reset' => false]);
        if (!$verify->check($code)) {
            $this->simpleAjaxReturn('验证码不正确');
        }

        // 把数据放进session
        switch (I('post.type')) {
            case 'email':
                session('shopSignUp.login_email', I('post.arg'));
                break;

            case 'phone':
                session('shopSignUp.login_phone', I('post.arg'));
                break;
        }
        session('shopSignUp.password', I('post.password'));
        session('shopSignUp.verifyCode', $code);

        $this->simpleAjaxReturn(true);
    }

    /**
     * 校验固话和企业邮箱
     */
    public function validateTelAndCompanyEmail() {
        // 获取参数
        $tel = I('post.tel');
        $companyEmail = I('post.company_email');

        // 验证
        $result = $this->shopkeeperModel->validateField('tel', ['tel' => $tel]);
        if ($result !== true) {
            return $this->simpleAjaxReturn($result);
        }

        $result = $this->shopkeeperModel->validateField('company_email', ['company_email' => $companyEmail]);
        if ($result !== true) {
            return $this->simpleAjaxReturn($result);
        }

        // 放进session里面
        session('shopSignUp.tel', $tel);
        session('shopSignUp.company_email', $companyEmail);

        $this->simpleAjaxReturn(true);
    }

    /**
     * 验证公司名，然后注册了
     */
    public function validateCompanyName() {
        // 获取参数
        $companyName = I('post.company_email');

        // 验证
        $result = $this->shopkeeperModel->validateField('company_name', ['company_name' => $companyName]);
        if ($result !== true) {
            return $this->simpleAjaxReturn($result);
        }

        // 放进session里面
        session('shopSignUp.company_name', $companyName);

        // 验证码校验
        $verifyCode = session('shopSignUp.verifyCode');
        $verify = new \Think\Verify();
        if (!$verify->check($verifyCode)) {
            return $this->simpleAjaxReturn('验证码不正确');
        }

        // 获取商家在session里面的注册信息
        $data = session('shopSignUp');

        // 插进数据库
        $result = D("Common/Shopkeeper")->addOne($data);
        if ($result !== true) {
            return $this->simpleAjaxReturn($result);
        }

        // 发送激活邮件
        $data['sid'] = D("Common/Shopkeeper")->addedLastId;
        session('shopSignUp.sid', $data['sid']);

        $result = D("Common/ShopkeeperActive")->sendActiveEmail(
                $data['sid'], $data['company_email'], 'Home/Shopkeeper/handleActive'
        );

        // 发送失败了！
        if ($result !== true) {
            return $this->simpleAjaxReturn($result);
        }

        // 哎，终于成功了
        $this->ajaxReturn([
                'status'			=>	200,
                'company_email'		=>	$data['company_email'],
        ]);

    }

    /**
     * 处理登陆请求
     */
    public function handleLogin() {
        $result = $this->shopkeeperModel->handleLogin();
        $this->simpleAjaxReturn($result);
    }

    /**
     * 处理发送验证码的请求
     */
    public function handleForgetPassword() {
        $arg = I("post.arg");
        // 根据传入参数type判断类型
        switch (I("post.type")) {
            case "email":
                $result = $this->shopkeeperModel->sendEmailForChangePasswd($arg);
                break;

            case "phone":
                $result = $this->shopkeeperModel->sendPhoneMsgForChangePasswd($arg);
                break;

            default:
                $result = '未知操作';
        }
        // 呵呵
        $this->simpleAjaxReturn($result);
    }

    /**
     * 处理密码重置请求
     */
    public function handleResetPasswd() {
        // 获取输入参数
        $token = I("post.token");
        $password = I("post.password");
        // 处理输入参数
        $result = $this->shopkeeperModel->handleResetPasswd($token, $password);
        // 呵呵
        $this->simpleAjaxReturn($result);
    }

    /**
     * 注销（退出登录）
     */
    public function signOut() {
        // 删除sesion和记住我cookie
        session('shopkeeper', null);
        cookie('shop_auto_login', null);

        $this->ajaxReturn([
                'status'	=>	200,
        ]);
    }

    /**
     * 修改密码
     */
    public function changePassword() {
        // 看有没有登录
        if (!session('?shopkeeper')) {
            return $this->ajaxReturn([
                'status'	=>	403,
                'msg'		=>	'商家还没登录',
            ]);
        }

        // 登录了
        $password = I("post.password");
        $result = $this->shopkeeperModel->changePassword(session('shopkeeper.id'), $password);

        $this->simpleAjaxReturn($result);
    }

    /**
     * 商家发起一条反馈信息
     */
    public function addFeedback() {
        // 看有没有登录
        $this->checkShopSignIn();

        // 数据库操作
        $result = D('Common/ShopFeedback')->addFeedback(session('shopkeeper.id'), $_POST['content']);

        $this->simpleAjaxReturn($result);
    }

    /**
     * 列出系统信息
     * @param number $page
     */
    public function listMsg($page = 0, $is_desktop = 0) {
        // 看有没有登录
        $this->checkShopSignIn();

        $perPage = 3;
        $resArr = D('Common/ShopMsg')->listMsg(session('shopkeeper.id'), $page, $perPage);

        if ($resArr) {
            foreach ($resArr as $key => $value) {
                $resArr[$key]['ctime'] = transDate($value['ctime']);
            }
        }

        // 桌面版使用，获取总页数
        if ($is_desktop) {
            $count = D('Common/ShopMsg')->countMsg(session('shopkeeper.id'));
            $totalPages = ceil($count / $perPage);

            return $this->ajaxReturn([
                    'status'        =>  200,
                    'data'          =>  $resArr,
                    'totalPages'    =>  $totalPages,
            ]);
        }

        $this->ajaxReturn([
                'status'	=>	200,
                'data'		=>	$resArr,
        ]);
    }


	//注册发送验证码，post.type    post.regtype  post.firstname  post.lastname
	public function register_send(){
    	$result = D('Common/Shopkeeper')->checkRegSend();
    	if ($result!==true) {
    		$this->ajaxReturn(array(
    				'status'	=>	400,
    				'msg'		=>	$result,
    		));
    	}
    	$this->ajaxReturn(array(
    			'status'		=>	200,
    			'msg'			=>	$result,
    	));
	}

	//注册。提交登录密码，呢称
	public function register_save(){
        // 组装注册需要的数据
        switch (session('reg.type')) {
            case 'phone':
                $data['login_phone'] = session('reg.typevalue');
                break;

            case 'email':
                $data['login_email'] = session('reg.typevalue');
                break;

            default:
                $this->ajaxReturn(array(
                        'status'		=>	200,
                        'msg'			=>	'系统异常（手机号码或邮箱丢失）',
                ));
        }

        $data['company_name'] = $_POST['company_name'];
        $data['password'] = $_POST['password'];

	    $reg_result = D('Common/Shopkeeper')->addOne($data);

    	if ($reg_result!==true) {
    		$this->ajaxReturn(array(
    				'status'	=>	400,
    				'msg'		=>	$reg_result,
    		));
    	}

	    if (session('?historyhref')){
	        $historyurl=session('historyhref');
	        session('historyhref',null);
	        $this->ajaxReturn(array(
	            'status'	=>	200,
	            'msg'		=>	$reg_result,
	            'histhref'  =>	$historyurl,
	        ));
	    }

    	$this->ajaxReturn(array(
    			'status'		=>	200,
    			'msg'			=>	$reg_result,
    	));
	}

    /**
     * 爬虫抓取商家信息
     */
    public function fetch() {
        if (I('get.p', 0, 'intval') != 88888888) {
            $this->ajaxReturn(array(
                'status'    =>  403,
                'msg'       =>  'incorrcet password',
            ));
        }

        $postData = $GLOBALS['HTTP_RAW_POST_DATA'];
        $jsonArr = json_decode($postData, true);

        if (!$jsonArr) {
            $this->ajaxReturn(array(
                'status'            =>  400,
                'msg'               =>  'incorrcet json format',
            ));
        }

        $mode = M('ShopkeeperFetch');

        $mode->startTrans();

        $fields = array(
            'name', 'phone', 'province', 'city', 'area',
            'street', 'onecate', 'twocate', 'picurl', 'website',
        );

        foreach ($jsonArr as $row) {

            foreach ($fields as $value) {
                if (!isset($row[$value])) {
                    $this->ajaxReturn(array(
                        'status'    =>  404,
                        'msg'       =>  'some filed missed',
                        'field'     =>  $value,
                    ));
                }
            }

            $result = $mode->data($row)->add();
            if (!$result) {
                $mode->rollback();
                $this->ajaxReturn(array(
                    'status'    =>  500,
                    'msg'       =>  $this->getDbError(),
                ));
            }
        }

        $mode->commit();
        $this->ajaxReturn(array(
            'status'    =>  200,
            'msg'       =>  'success',
        ));
    }

}
