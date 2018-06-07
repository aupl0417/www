<?php
namespace Home\Controller;
use Think\Controller;

class TestController extends Controller {


    /**
     * 商家测试登录微信
     */
    public function testShopWeiXin() {
        if (is_weixin()) {
            require_once('./Api/wx/config.php');
            require_once('./Api/wx/OauthAction.class.php');
            $Owx = new \OauthAction();
            $Owx->index(WX_APP_ID , SHOP_WX_URI, 2);
        }
        die('不是微信内置浏览器，不能执行该操作');
    }

    /**
     * 微信接口地址
     */
    public function wxtoken(){
        require_once('./Api/wx/config.php');
        require_once ('./Api/wx/wx_def.php');
        $wechatObj = new \wechatCallbackapiTest(WX_APP_ID,WX_APP_SECRET);
        $wechatObj -> Run();

    }


    public function creatMenu(){
    	require_once ('./Api/wx/config.php');
    	require_once ('./Api/wx/wx_def.php');
    	$wechatObj = new \wechatCallbackapiTest(WX_APP_ID,WX_APP_SECRET);
    	$wechatObj->createmenu();
    	echo 'ok';
    	exit;
    }
    
    
    
    
    
    
    public function llogin(){
        require_once('./Api/wx/config.php');
        require_once('./Api/wx/OauthAction.class.php');
        $Owx = new \OauthAction();
        $wx_urll = 'http://17yueke.cn/Home/test/wxcode';
        $Owx->index( WX_APP_ID , $wx_urll );
    }

    public function wxcode(){
        $code = $_REQUEST["code"];
        $User=D('User');
//         $loginWxUser = $User->wxCode($code); //===true 则存在该openid的记录，并且通过该openid登录17网站


        require_once('./Api/wx/config.php');
        require_once('./Api/wx/OauthAction.class.php');
        $Owx = new \OauthAction();
        $wx_token = $Owx->access_token( WX_APP_ID , WX_APP_SECRET ,$code);
        $_SESSION['access_token']  = $wx_token['access_token'];
        $_SESSION['expires_in']    = $wx_token['expires_in'];
        $_SESSION['refresh_token'] = $wx_token['refresh_token'];
        $Wx = D('Wx');
        $check_exist = $Wx->checkopenid($wx_token['openid']);
        
        $wx_info_user = $User->wxGetUserInfo($wx_token['access_token'] , $wx_token['openid'] , $code);
        
        
        print_r($wx_token);
        print_r('<br/>');
        print_r('<br/>');
        print_r($check_exist);
        print_r('<br/>');
        print_r('<br/>');
        print_r($wx_info_user);
        exit;
        
        if ($check_exist!==false){
        	$loginStatus = $this->wxLoginByUid($check_exist);
        	return true;
        }
        return false;
        
        
    }
    
    
    public function login(){
        require_once('./Api/wx/config.php');
        require_once('./Api/wx/OauthAction.class.php');
        $Owx = new \OauthAction();
        $wx_url = 'http://17yueke.cn/Home/test/rewxcode';
        $Owx->re_index( WX_APP_ID , $wx_url );
    }

    public function reWxCode(){
        $code = $_REQUEST["code"];
        $User=D('User');
        
        require_once('./Api/wx/config.php');
        require_once('./Api/wx/OauthAction.class.php');
        $Owx = new \OauthAction();
        $wx_token = $Owx->access_token( WX_APP_ID , WX_APP_SECRET ,$code);
        $_SESSION['access_token']  = $wx_token['access_token'];
        $_SESSION['expires_in']    = $wx_token['expires_in'];
        $_SESSION['refresh_token'] = $wx_token['refresh_token'];
        $_SESSION['openid']        = $wx_token['openid'];
        $Wx = D('Wx');
        $check_exist = $Wx->checkopenid($wx_token['openid']);
//         if ($check_exist!==false){
//         	$loginStatus = $User->wxLoginByUid($check_exist);
//         	return true;
//         }
        $wxOpenId = $_SESSION['openid'];
        $wxToken  = $_SESSION['access_token'];
         
        $wxInfo   = $User->wxGetUserInfo($wx_token['access_token'],$wxOpenId,$code);

        if (empty($wxInfo)){
        	return false;
        }
        
        $url_wx_wx = $this->wxCutImage($wxInfo['headimgurl']); 
        print_r($wx_token);
        print_r('<br/>');
        print_r('<br/>');
        print_r($wxInfo);
        print_r('<br/>');
        print_r('<br/>');
        print_r($url_wx_wx);
        exit;
        $wx_path_wx = $this->imagecut($url_wx_wx);
        
//         $wxAddSt  = $Wx->addWxUser($wxOpenId,$wxInfo);
        

        print_r($wx_token);
        print_r('<br/>');
        print_r('<br/>');
        print_r($check_exist);
        print_r('<br/>');
        print_r('<br/>');
        print_r($wxInfo);
        print_r('<br/>');
        print_r('<br/>');
        print_r($url_wx_wx);
        print_r('<br/>');
        print_r('<br/>');
        print_r($wx_path_wx);
        exit;
        
        
    }
    
    public function jkjk(){
    	$kkkk = $this->wxCutImage('http://wx.qlogo.cn/mmopen/a18XcQ1EBBggYvkqOSiaEnOrCksUodEt3IlfAKBvIicX4HjVGSUme39g7vu1F7uY6cOemXNURgnJzl39aicpcdYsw/0');
    	$kkkk = '.'.$kkkk;
    	$dddddd = $this->imagecut($kkkk);
    	print_r($dddddd);exit;
    }
    public function wxCutImage($url,$filename="") {
    	if($url==""){
    		return false;
    	}
    	if($filename=="") {
    		$filename=md5(date("dMYHis")).'.jpg';
    	}
    	$qqpath=C('user_avatar');
    	$filepath=$qqpath.'/wx/'.$filename;
    	$filename='.'.$qqpath.'/wx/'.$filename;
    
    	file_put_contents($filename, file_get_contents($url));
    	return $filepath;
    }
    public function imagecut($path='./Public/Uploads/useravatar/bg2.jpg'){
        
        $user_avatar=C('user_avatar');
        $pathname=date("Y/m/d");
        $imgname=uniqid();
        $thumb='.'.$user_avatar.'/wx/'.$pathname.'/'.$imgname.'.jpg';

        $thumbimg='.'.$user_avatar.'/wx/'.$pathname;
        $this->mk_dir($thumbimg);

        $image = new \Think\Image();
        $image->open($path);
        // 生成一个固定大小为75*75的缩略图并保存为thumb.jpg
        $imageInfo=$image->thumb(75, 75,\Think\Image::IMAGE_THUMB_FIXED)->save($thumb);
        if (!$imageInfo){
        	print_r($imageInfo);exit;
            return false;
        }
        $unimagestatus=unlink($path);		//删除//
        
        $thumbdeldian=$user_avatar.'/wx/'.$pathname.'/'.$imgname.'.jpg';
        print_r($thumbdeldian);exit;
        return $thumbdeldian;
    }
    public function mk_dir($dir, $mode = 0777){
    	if (is_dir($dir) || @mkdir($dir,$mode)){
    		return true;
    	}
    	if (!$this->mk_dir(dirname($dir),$mode)){
    		return false;
    	}
    	return @mkdir($dir,$mode);
    }
    
    
    
    
    
    
    public function getsnsinfo(){
        $info = session('snssendall');
//         $info = session('codeinfo');
//         $info = session('postdatasns');
        print_r($info);
        exit;
    }
    
    
    
    
    
    
    public function defwx(){
        require_once('./Api/wx/config.php');
        require_once ('./Api/wx/wx_def.php');
        $wechatObj = new \wechatCallbackapiTest(WX_APP_ID,WX_APP_SECRET);
        $wechatObj->valid();
        $wechatObj -> Run();

    }



    //模板消息
    public function hhhhhh(){
        $assist = D('GroupAssist');
        $jsondata = $assist->sendSnsAllAssist(3,25);

        print_r($jsondata);exit;
    }
    
    
    //多媒体上传
    public function uploadWX(){
        require_once('./Api/wx/config.php');
        require_once ('./Api/wx/wx_def.php');
        $wechatObj = new \wechatCallbackapiTest(WX_APP_ID,WX_APP_SECRET);
        $status_wx = $wechatObj->uploadMedia();
        print_r($status_wx);exit;
    }
    
//-------------------------------------

    public function index(){
        $this->display();
    }
    public function register(){
        $this->display();
    }

    public function success(){
        $this->display();
    }

    public function user_login(){
        $this->display();
    }

    public function shop_login(){
        $this->display();
    }

    public function signupstart(){
        $this->display();
    }

    public function consult(){
        $this->display('newLogin');
    }

    public function newLogin(){
        $this->display();
    }

    public function bus_reg(){
        $this->display();
    }

    public function bus_set(){
        $this->display();
    }
}
