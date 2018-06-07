<?php
namespace Home\Controller;
use Common\Controller\BaseController;

/**
 *
 * @author user
 *
 */
class WxController extends BaseController {


    public function __construct(){
        parent::__construct();
//         $historyurl  = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        //用户也没有登录的时候，则判断是否是微信客服端--是的话则登录

    }

    /**
     * 是否是微信浏览器
     * @return boolean
     */
    public function isWx(){
        $weixin=is_weixin();
        if ($weixin!==true){
            echo '不是微信内置浏览器,请切换到微信客户端中登录';
            exit;
        }
        return true;
    }



    /**
     * 微信接口地址
     */
    public function wxtoken(){
        require_once ('./Api/wx/config.php');
        require_once ('./Api/wx/wx_def.php');
        $wechatObj = new \wechatCallbackapiTest(WX_APP_ID,WX_APP_SECRET);
        $wechatObj -> Run(); //微信业务处理
         //$wechatObj -> valid(); //微信接口验证
    }










    /**
     * 微信绑定页面--或者--直接登录选择页面
     */
    public function wx(){
        $this->isWx();
        $this->display();
    }


    /**
     * 微信直接登录-----判断是否微信客户端--是则登录-
     */
    public function isWeiXin(){
        $this->isWx();
        require_once('./Api/wx/config.php');
        require_once('./Api/wx/OauthAction.class.php');
        $Owx = new \OauthAction();
        $Owx->re_index( WX_APP_ID , my_re_wx_uri );
    }
    /**
     * 微信直接登录-----获取code--微信--
     */
    public function wxLogin(){
        $this->isWx();
	    require_once('./Api/wx/config.php');
	    require_once('./Api/wx/OauthAction.class.php');
	    $Owx = new \OauthAction();
	    $Owx->index( WX_APP_ID , my_wx_uri );
    }
    /**
     * 微信直接登录-----回调获取token----微信Code
     */
    public function wxCode(){
        $code = $_REQUEST["code"];
        $User=D('User');
        $loginWxUser = $User->wxCode($code);
        if ($loginWxUser!==true){ //数据库中木有该openid的记录---false
            //-----授权登录---
            $User->reWxlogin();
            exit;
        }
        S('returnIndex',1,20);//判断个人详情页面的的的返回地址-是否返回首页--存在则首页
        if (session('?historyhref')){
            $historyurl=session('historyhref');
            session('historyhref',null);
            redirect($historyurl);
        }
        $this->redirect('Home/LookUser/moInfo');
    }
    /**
     * 微信直接登录-----回调，授权获取token----微信Code
     */
    public function reWxCode(){
        $code = $_REQUEST["code"];
        $User=D('User');
        $loginWxUser = $User->reWxCode($code);
        if ($loginWxUser!==true){ //授权回调--记录信息-出错
            $this->error($loginWxUser,'/Home/Userregsign/index');
            exit;
        }
        S('returnIndex',1,20); //判断个人详情页面的的的返回地址-是否返回首页--存在则首页
        if (session('?historyhref')){
            $historyurl=session('historyhref');
            session('historyhref',null);
            redirect($historyurl);
        }
        $this->redirect('Home/LookUser/moInfo');
    }

    /**
     * 微信绑定账号页面中----获取openid--
     */
    public function wxRelUser(){
        $this->isWx();
        require_once('./Api/wx/config.php');
        require_once('./Api/wx/OauthAction.class.php');
        $Owx = new \OauthAction();
        $Owx->index( WX_APP_ID , rel_wx_uri );
    }
    /**
     * 微信关联--直接的回调地址
     */
    public function wxRelation(){
        $code = $_REQUEST["code"];
        if (!$code){
            $this->error('授权出错','/Home/Wx/wx');
            exit;
        }
        require_once('./Api/wx/config.php');
        require_once('./Api/wx/OauthAction.class.php');
        $Owx = new \OauthAction();
        $wx_token = $Owx->access_token( WX_APP_ID , WX_APP_SECRET ,$code);
        $_SESSION['access_token']  = $wx_token['access_token'];
        $_SESSION['expires_in']    = $wx_token['expires_in'];
        $_SESSION['refresh_token'] = $wx_token['refresh_token'];
        $Wx = D('Wx');
        $check_exist = $Wx->checkopenid($wx_token['openid']);
        if ($check_exist!==false){ //已经微信号直接登录过了---无法关联
            D('User')->wxLoginByUid($check_exist);
            S('returnIndex',1,20);//判断个人详情页面的的的返回地址-是否返回首页--存在则首页
            $this->error('已经微信号直接登录过了!无法再次关联','/Home/Wx/wx');
            exit;
        }
        session('wx.openid',$wx_token['openid']);
        $this->display();
    }

    /**
     * 微信--验证关联用户，，验证成功则关联成功
     */
    public function wxRelUserLogin(){
        $this->isWx();
        $openid = session('wx.openid');
        if (!$openid){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'histhref'  =>  400,
                'msg'       =>  "非法操作!",
            ));
        }
        $check_exist = D('Wx')->checkopenid($openid);
        if ($check_exist!==false){
            D('User')->wxLoginByUid($check_exist);
            S('returnIndex',1,20);//判断个人详情页面的的的返回地址-是否返回首页--存在则首页
            if (session('?historyhref')){
                $historyurl=session('historyhref');
                session('historyhref',null);
                $this->ajaxReturn(array(
                    'status'    =>  200,
                    'histhref'  =>  $historyurl,
                    'msg'       =>  "该微信号已经关联17账号",
                ));
            }
            $this->ajaxReturn(array(
                'status'    =>  200,
                'histhref'  =>  400,
                'msg'       =>  "该微信号已经关联17账号",
            ));
        }
        $User = D('User');
        $wxRes = $User->relationUid();
        if ($wxRes[0]!==true){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'histhref'  =>  400,
                'msg'       =>  $wxRes[1],
            ));
        }
        $wxRes = D('Wx')->relationToUid($wxRes[1]);
        if($wxRes!==true){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'histhref'  =>  400,
                'msg'       =>  '系统出错，请联系客服或者直接在服务号里反馈情况',
            ));
        }

        S('returnIndex',1,20);//判断个人详情页面的的的返回地址-是否返回首页--存在则首页
        if (session('?historyhref')){
            $historyurl=session('historyhref');
            session('historyhref',null);
            $this->ajaxReturn(array(
                'status'    =>  200,
                'histhref'  =>  $historyurl,
                'msg'       =>  "该微信号关联17账号成功",
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'histhref'  =>  400,
        ));
    }





    public function myInfo(){
        $this->isWx();
        $historyurl  = U('Home/LookUser/moInfo');
        session('historyhref',$historyurl);
        $this->getOpenid();
    }

    public function course(){
        $this->isWx();
        $historyurl  = U('Home/User/course');
        session('historyhref',$historyurl);
        $this->getOpenid();
    }

    public function resetPw(){
        $this->isWx();
        $historyurl  = U('Home/User/resetPw');
        session('historyhref',$historyurl);
        $this->getOpenid();
    }





    //获取openid的code
    //判断数据库是否存在openid记录，存在则登录并且跳转到上面链接；若无记录则跳转到微信账号选择界面选择微信登录或者账号绑定
    public function getOpenid(){
        $this->isWx();
        require_once('./Api/wx/config.php');
        require_once('./Api/wx/OauthAction.class.php');
        $Owx = new \OauthAction();
        $Owx->index( WX_APP_ID , estimate_wx_url );
    }
    //获取openid的回调地址
    public function CallBack(){
        $code = $_REQUEST["code"];
        $User=D('User');
        $loginWxUser = $User->wxCode($code); //===true 则存在该openid的记录，并且通过该openid登录17网站
        if ($loginWxUser !== true){ //没有该openid的记录--跳到选择微信登录或者绑定
            $this->redirect('Home/Wx/wx');
        }
        S('returnIndex',1,20);//判断个人详情页面的的的返回地址-是否返回首页--存在则首页
        //已登陆
        if (session('?historyhref')){
        	$historyurl=session('historyhref');
        	session('historyhref',null);
            redirect($historyurl);
        }
        $this->redirect('Home/LookUser/moInfo');
        return true;
    }















}
