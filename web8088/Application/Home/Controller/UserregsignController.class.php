<?php
namespace Home\Controller;
use Common\Controller\BaseController;

/**
 *
 * @author user
 * 注册and登录
 */
class UserregsignController extends BaseController {


	public function __construct(){
		parent::__construct();
		
	}
	

    public function strlength(){
        echo encrypt_passwd('u17o1dk');
    }

    function get_onlineip() {
        $onlineip = '';
        if(getenv(HTTP_CLIENT_IP) && strcasecmp(getenv(HTTP_CLIENT_IP), unknown)) {
            $onlineip = getenv(HTTP_CLIENT_IP);
        } elseif(getenv(HTTP_X_FORWARDED_FOR) && strcasecmp(getenv(HTTP_X_FORWARDED_FOR), unknown)) {
            $onlineip = getenv(HTTP_X_FORWARDED_FOR);
        } elseif(getenv(REMOTE_ADDR) && strcasecmp(getenv(REMOTE_ADDR), unknown)) {
            $onlineip = getenv(REMOTE_ADDR);
        } elseif(isset($_SERVER[REMOTE_ADDR]) && $_SERVER[REMOTE_ADDR] && strcasecmp($_SERVER[REMOTE_ADDR], unknown)) {
            $onlineip = $_SERVER[REMOTE_ADDR];
        }
    echo $onlineip;
        return $onlineip;
    }


    public function xxxxx(){
        $body='有用户发布了新约课，请及时查看。登录17yueke.cn/g/48';
        $subject='【17约课】';
        $emailstatus=sendMail('827506590@qq.com',$body, $subject);
var_dump($emailstatus);exit;
    }

    //各种测试的接口
    public function gggggssss(){
// header("content-Type: text/html; charset=Utf-8");//设置字符编码
        $phonecode=D('GroupAssist')->sendSnsAllAssist(2,18);
var_dump($phonecode);exit;
    }

    /**
     * 测试短信内容
     */
    public function fffsns(){
        $msg = '您好！您在17约课上发布的课程，已经有用户报名您的课程，请及时查看。登录17yueke.cn/s/5。【17约课】';
        require_once(realpath('Api/sms/sms_send.php'));
        $rrr=sendnote(18826483652, urlencode(iconv('utf-8', 'gbk', $msg)));
        echo $rrr;
    }


	//验证码
	public function verify(){
		//C()专用于读取配置文件中的项目
		$config = C('verifycode');
		$Verify = new\Think\Verify($config);
		$Verify->entry();
	}

	/**
	 * 注册页面
	 */
	public function index(){
		$this->display('user_reg_two');
	}

	//注册发送验证码，post.type    post.regtype  post.firstname  post.lastname  可能条款post.terms
	public function register_send(){
    	$User = D('User');
    	$result = $User->checkRegSend();
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
    //注册，判断code是否正确，正确则注册成功
	public function register_check(){
	    $User = D('User');
	    $reg_result = $User->checkRegSendCodeNum();
    	if ($reg_result!==true) {
            //session('reg',null);
    		$this->ajaxReturn(array(
    				'status'	=>	400,
    				'msg'		=>	$reg_result,
    		));
    	}
    	$this->ajaxReturn(array(
    			'status'		=>	200,
    			'msg'			=>	$reg_result,
    	));
	}
	//注册。提交登录密码，呢称
	public function register_save(){
	    $User = D('User');
	    $reg_result = $User->regCheckAll();
    	if ($reg_result!==true) {
    		$this->ajaxReturn(array(
    				'status'	=>	400,
    				'msg'		=>	$reg_result,
    		));
    	}

	    $User->updataVisitorToUserInfo();//游客处理

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


//===============================================================================
	/**
	 * 暂时废弃
	 * 提交注册数据
	 */
	public function register(){

		if(session('?user.id')){
        		$this->ajaxReturn(array(
        				'status'	=>	200,//注册成功自动登录
        				'msg'		=>	'注册成功',
        		));
		}


		$User = D('User');
		$result = $User->addOne();
		if ($result!==true){
        		$this->ajaxReturn(array(
        				'status'	=>	400,
        				'msg'		=>	$result,
        		));
		}else {
        		$this->ajaxReturn(array(
        				'status'	=>	200,//注册成功自动登录
        				'msg'		=>	$result,
        		));
		}
	}



	/**
	 * 登录页面
	 */
	public function login(){

		if (session('?user')) {
			$this->redirect('Index/index');//已登录跳转
		}
		$ucookiseid=cookie('userid');//cookise登录
		if ($ucookiseid) {
			$User = D('User');
			$resule=$User->logincookise($ucookiseid);
			if ($resule==true) {

	            $User->updataVisitorToUserInfo();//游客处理

                if (session('?historyhref')){
                	$historyurl=session('historyhref');
                	session('historyhref',null);
                	@header("location:$historyurl");
                	exit;
                }
				$this->redirect('Index/index');//cookise登录跳转首页
			}
		}
		$this->display('user_login');
	}



	/**
	 * 用户登陆
	 */
	public function userLogin() {
	    //     	header("content-Type: text/html; charset=Utf-8");//设置字符编码
	    $User = D('User');
	    $result = $User->userLogin();
	    if ($result!==true){
	        $this->ajaxReturn(array(
	            'status'	=>	400,
	            'msg'		=>	$result,
	        ));
	    }
	    $User->updataVisitorToUserInfo();//游客处理

	    if (session('?historyhref')){
	        $historyurl=session('historyhref');
	        session('historyhref',null);
	        $this->ajaxReturn(array(
	            'status'	=>	200,
	            'msg'		=>	$result,
	            'histhref'  =>	$historyurl,
	        ));
	    }
        $this->ajaxReturn(array(
            'status'	=>	200,
            'msg'		=>	$result,
	        'histhref'  =>	400,
        ));
	}

	/**
     * 用户登陆---首页
     */
    public function userLoginIndex() {
//     	header("content-Type: text/html; charset=Utf-8");//设置字符编码
		$User = D('User');
    	$result = $User->userLogin();
    	if ($result!==true){
    	    $this->ajaxReturn(array(
    	        'status'	=>	400,
    	        'msg'		=>	$result,
    	    ));
    	}
    	$userInfo = $User->sessionToUserInfo();

    	$User->updataVisitorToUserInfo();//游客处理

	    $this->ajaxReturn(array(
	        'status'	=>	200,
	        'msg'		=>	$userInfo,
	    ));
    }


//=======================------------------------------------------第三方登录==============

    
//     /**
//      * 判断是否微信客户端--是则登录---
//      */
//     public function isweixin(){
//         $weixin=is_weixin();
//         if ($weixin===true){
//             $this->wxlogin();
//         }else {
//             $this->error("不是微信内置浏览器,请切换到微信客户端中登录",'/Home/Index/index');
//             exit;
//         }
//     }
//     //获取code--微信
//     public function wxlogin(){
//         $User=D('User');
//         $User->wxlogin();
//     }
//     //回调获取token----微信
//     public function wxtoken(){
//         $code = $_REQUEST["code"];
//         $User=D('User');
//         $loginWxUser = $User->wxToken($code);
//         if ($loginWxUser!==true){
//             $this->error($loginWxUser,'/Home/Userregsign/index');
//             exit;
//         }
//         S('returnIndex',1,20);//判断个人详情页面的的的返回地址-是否返回首页--存在则首页 
// 		if (session('?historyhref')){
// 			$historyurl=session('historyhref');
// 			session('historyhref',null);
// 			$this->redirect($historyurl);
// 		}
//         $this->redirect('Home/LookUser/moInfo');
//     }
//     //回调，授权获取token----微信
//     public function rewxtoken(){
//         $code = $_REQUEST["code"];
//         $User=D('User');
//         $loginWxUser = $User->reWxToken($code);
//         if ($loginWxUser!==true){
//             $this->error($loginWxUser,'/Home/Userregsign/index');
//             exit;
//         }
//         S('returnIndex',1,20);//判断个人详情页面的的的返回地址-是否返回首页--存在则首页 
// 		if (session('?historyhref')){
// 			$historyurl=session('historyhref');
// 			session('historyhref',null);
// 			$this->redirect($historyurl);
// 		}
//         $this->redirect('Home/LookUser/moInfo');
//     }

//     /**
//      * 微信绑定页面--或者--直接登录选择页面
//      */
//     public function wx(){
//         $weixin=is_weixin();
//         if ($weixin===true){
//             $this->display();
//         }else {
//             $this->error("不是微信内置浏览器,请切换到微信客户端中登录",'/Home/Index/index');
//             exit;
//         }
//     }
//     /**
//      * 微信绑定账号页面中----获取openid 
//      */
//     public function wxRelUser(){
//         $weixin=is_weixin();
//         if ($weixin!==true){
//             $this->error("不是微信内置浏览器,请切换到微信客户端中登录",'/Home/Index/index');
//             exit;
//         }
//         require_once('./Api/wx/config.php');
//         require_once('./Api/wx/OauthAction.class.php');
//         $Owx = new \OauthAction();
//         $Owx->index( WX_APP_ID , rel_wx_uri );
//     }
//     /**
//      * 微信关联--直接的回调地址
//      */
//     public function wxRelation(){
//         $code = $_REQUEST["code"];
//         if (!$code){
//             $this->error('授权出错','/Home/Userregsign/wx');
//             exit;
//         }
//         require_once('./Api/wx/config.php');
//         require_once('./Api/wx/OauthAction.class.php');
//         $Owx = new \OauthAction();
//         $wx_token = $Owx->access_token( WX_APP_ID , WX_APP_SECRET ,$code);
//         $_SESSION['access_token']  = $wx_token['access_token'];
//         $_SESSION['expires_in']    = $wx_token['expires_in'];
//         $_SESSION['refresh_token'] = $wx_token['refresh_token'];
//         $Wx = D('Wx');
//         $check_exist = $Wx->checkopenid($wx_token['openid']);
//         if ($check_exist!==false){ //已经微信号直接登录过了---无法关联 
//             D('User')->wxLoginByUid($check_exist);
//             S('returnIndex',1,20);//判断个人详情页面的的的返回地址-是否返回首页--存在则首页 
//             $this->error('已经微信号直接登录过了!无法再次关联','/Home/Userregsign/wx');
//             exit;
//         }
//         session('wx.openid',$wx_token['openid']);
//         $this->display();
//     }
    
//     /**
//      * 微信--验证关联用户，，验证成功则关联成功
//      */
//     public function wxRelUserLogin(){  
//         $openid = session('wx.openid');
//         if (!$openid){
//             $this->ajaxReturn(array(
//                 'status'    =>  400,
//                 'histhref'  =>  400,
//                 'msg'       =>  "非法操作!",
//             ));
//         }
//         $check_exist = D('Wx')->checkopenid($openid);
//         if ($check_exist!==false){
//             D('User')->wxLoginByUid($check_exist);
//             S('returnIndex',1,20);//判断个人详情页面的的的返回地址-是否返回首页--存在则首页
//             if (session('?historyhref')){
//             	$historyurl=session('historyhref');
//             	session('historyhref',null);
//             	$this->ajaxReturn(array(
// 	                'status'    =>  200,
// 	                'histhref'  =>  $historyurl,
// 	                'msg'       =>  "该微信号已经关联17账号",
//             	));
//             }
//             $this->ajaxReturn(array(
//                 'status'    =>  200,
//                 'histhref'  =>  400,
//                 'msg'       =>  "该微信号已经关联17账号",
//             ));
//         }     
//         $User = D('User');
//         $wxRes = $User->relationUid();
//         if ($wxRes[0]!==true){
//             $this->ajaxReturn(array(
//                 'status'    =>  400,
//                 'histhref'  =>  400,
//                 'msg'       =>  $wxRes[1],
//             ));
//         }
//         $wxRes = D('Wx')->relationToUid($wxRes[1]);
//         if($wxRes!==true){
//             $this->ajaxReturn(array(
//                 'status'    =>  400,
//                 'histhref'  =>  400,
//                 'msg'       =>  '系统出错，请联系客服或者直接在服务号里反馈情况',
//             ));
//         }

//         S('returnIndex',1,20);//判断个人详情页面的的的返回地址-是否返回首页--存在则首页

//         if (session('?historyhref')){
//         	$historyurl=session('historyhref');
//         	session('historyhref',null);
//         	$this->ajaxReturn(array(
//         			'status'    =>  200,
//         			'histhref'  =>  $historyurl,
//         			'msg'       =>  "该微信号关联17账号成功",
//         	));
//         }
//         $this->ajaxReturn(array(
//             'status'    =>  200,
//             'histhref'  =>  400,
//         ));
//     }
    
    
    
    
    
//--------------------------------↑--
    
    /**
     * QQ登录
     */
    public function qqlogin(){
		$User = D('User');
		$qqinfo=$User->qqlogin();
    }


    /**
     * QQ回调地址
     */
    public function qqCallbank(){
        $User = D('User');
        $qqinfo=$User->qqCallBack();
        if ($qqinfo[1]==0){
            $this->assign('msgError','QQ登录失败，请重新登录'.$qqinfo[0]);
            $this->display('user_login');
        }

        //检查是否完善了登录手机号码
        $uid = session('thirduser.id');
        $phoneExit = $User->gainPhone($uid);

        if ($phoneExit===false){
            $this->redirect('Userregsign/regPhone');
        }


		// 登陆成功，设置user的二维数组
		session('user.id',session('thirduser.id'));  						//设置session
		session('user.name',session('thirduser.name'));  	//设置session
		session('user.remark',session('thirduser.remark'));  						//设置session
		session('user.profession',session('thirduser.profession'));  						//设置session
		session('user.phone',session('thirduser.phone'));  						//设置session
		session('user.email',session('thirduser.email'));  						//设置session
		session('user.avatar',session('thirduser.avatar'));  					//设置session
		session('user.telstatus',session('thirduser.telstatus'));  					//设置session
		session('user.vtype',session('thirduser.vtype'));  					//设置session
		session('user.vstatus',session('thirduser.vstatus'));  					//设置session
        session('shopkeeper',null);
        session('shop_auto_login',null);
        //游客处理
        $User->updataVisitorToUserInfo();
        if (session('?historyhref')){
        	$historyurl=session('historyhref');
        	session('historyhref',null);

        	@header("location:$historyurl");
        	exit;
        }
        $this->redirect('Index/index');
    }



    //微博登录
    public function sinaLogin(){

    	$User = D('User');
    	$sinainfo=$User->sinaLogin();
        if (!$sinainfo){
            $this->redirect('Index/index');//已经登录
        }
        @header("location:$sinainfo");
        exit;
    }


    //微博回调
    public function sinaCallBack(){
        $User = D('User');
        $sinainfo=$User->sinaCallBack();
        if ($sinainfo==='405'){
             $this->redirect('Index/index');//已经登录
        }elseif ($sinainfo==='406'){
            $this->assign('msgError','新浪登录失败，请重新登录');
            $this->display('user_login');
        }elseif ($sinainfo==='407'){
            $this->assign('msgError','新浪登录失败，请重新登录');//该用户授权失败，请重新登录
            $this->display('user_login');
        }
        //检查是否完善了登录手机号码
        $uid = session('thirduser.id');
        $phoneExit = $User->gainPhone($uid);
        if ($phoneExit===false){
            $this->redirect('Userregsign/regPhone');
        }


        // 登陆成功，设置user的二维数组
        session('user.id',session('thirduser.id'));  						//设置session
        session('user.name',session('thirduser.name'));  	//设置session
        session('user.remark',session('thirduser.remark'));  						//设置session
        session('user.profession',session('thirduser.profession'));  						//设置session
        session('user.phone',session('thirduser.phone'));  						//设置session
        session('user.email',session('thirduser.email'));  						//设置session
        session('user.avatar',session('thirduser.avatar'));  					//设置session
        session('user.telstatus',session('thirduser.telstatus'));  					//设置session
        session('user.vtype',session('thirduser.vtype'));  					//设置session
        session('user.vstatus',session('thirduser.vstatus'));  					//设置session
        session('shopkeeper',null);
        session('shop_auto_login',null);


        //游客处理
        $User->updataVisitorToUserInfo();

        if (session('?historyhref')){
            $historyurl=session('historyhref');
            session('historyhref',null);
        	@header("location:$historyurl");
        	exit;
        }
        $this->redirect('Index/index');
    }




    /**
     * 第三方登录后，发送验证码界面
     */
    public function regPhone(){
        $this->display('login_reg_phone');
    }
    /**
     * 第三方登录后，发送手机验证码
     */
    public function sendPhone(){
        if (!session('?thirduser')){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'msg'       =>  '请先进行第三方登录!',
            ));
        }
        $uid = session('thirduser.id');
        $tel = I('post.phone')?I('post.phone'):0;
        $User = D('User');
        $result = $User->sendThirdCode($User,$tel);
        if ($result!==true){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'msg'       =>  $result,
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'msg'       =>  $result,
        ));
    }
    /**
     * 第三方登录后，完善手机号码后，
     */
    public function loginByCode(){
        $code = I('post.code')?I('post.code'):0;
        if (!session('?thirduser')){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'msg'       =>  '请先进行第三方登录!',
            ));
        }
        $uid = session('thirduser.id');
        $User = D('User');
        $result = $User->saveThirdPhone($code,$uid);
        if ($result!==true){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'msg'       =>  $result,
            ));
        }

        // 登陆成功，设置user的二维数组
        session('user.id',session('thirduser.id'));  						//设置session
        session('user.name',session('thirduser.name'));  	//设置session
        session('user.remark',session('thirduser.remark'));  						//设置session
        session('user.profession',session('thirduser.profession'));  						//设置session
        session('user.phone',session('thirduser.phone'));  						//设置session
        session('user.email',session('thirduser.email'));  						//设置session
        session('user.avatar',session('thirduser.avatar'));  					//设置session
        session('user.telstatus',session('thirduser.telstatus'));  					//设置session
        session('user.vtype',session('thirduser.vtype'));  					//设置session
        session('user.vstatus',session('thirduser.vstatus'));  					//设置session
        session('shopkeeper',null);
        session('shop_auto_login',null);

        //游客处理
        $User->updataVisitorToUserInfo();
	    if (session('?historyhref')){
	        $historyurl=session('historyhref');
	        session('historyhref',null);
	        $this->ajaxReturn(array(
	            'status'	=>	200,
	            'msg'		=>	$result,
	            'histhref'  =>	$historyurl,
	        ));
	    }

        $this->ajaxReturn(array(
            'status'    =>  200,
            'msg'       =>  $result,
        ));
    }
//===================================================================
	/**
	 * 用户忘记密码
	 */
    public function forgetpw(){
    	$this->display('forget_password');
    }


    /**
     * 发送邮件或者短信  AJAX
     */
    public function forgetsend(){
    	$User = D('User');
    	$result = $User->checkSend();
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

    //忘记密码页
    public function resetpwindex(){
    	$this->display('forget_setpassword');
    }

    /**
     * 忘记密码,设置新密码AJAX
     */
    public function resetpw(){
//     	header("content-Type: text/html; charset=Utf-8");//设置字符编码
    	$User = D('User');
    	$result = $User->changpw();
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




//跳转未有页面
    /**
     * 激活用户的邮箱
     * return true | false
     */
    public function handleActive() {
    	$token=I('get.token');
    	$Useractive=D('Useractive');
    	$result = $Useractive->handleActive($token);
// print_r($result);exit;
        $this->assign('msg',$result);
        $this->display('handsessce');
    }



}

