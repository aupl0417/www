<?php
namespace Admin\Controller;
use Common\Controller\CommonController;


/**
 * 
 * @author user
 *
 */
class WxController extends CommonController {
	
    
    /**
     * 微信菜单页面
     */
    public function menu(){
        $this->display();
    }
    /**
     * 微信菜单创建--AJAX
     */
    public function creatMenu(){
        $validates=array(
            array('verify','require','验证码不能为空', 1),
            array('verify','check_verify','验证码错误！',1,'function'), //默认情况下用正则进行验证
        );
        $autodate=array();
        if (!M('Wx')->validate($validates)->auto($autodate)->create()) {

            $this->ajaxReturn(array(
                'status'    =>  400,
                'msg'       =>  M('Wx')->getError(),
            ));
            
        }

        require_once ('./Api/wx/config.php');
        require_once ('./Api/wx/wx_def.php');
        $wechatObj = new \wechatCallbackapiTest(WX_APP_ID,WX_APP_SECRET);
        $wechatObj->createmenu();
        $this->ajaxReturn(array(
            'status'    =>  200,
            'msg'       =>  'ok',
        ));
    }
    /**
     * 微信注册数据调出
     */
    public function wxList(){
        $curPage = 1;
        $perPage = 30;
        $Wx = D('Wx');
        $page = $Wx->pageAll($curPage,$perPage);
        $wxInfo = $Wx->wxList( $page['pageOffset'] , $page['perPage'] );
        $this->assign('page',$page);
        $this->assign('info',$wxInfo);
        
        $this->display();
    }
    
    //微信头像数据转移
    public function wxUpdata(){
        
//         $regex = '/^http:\/\/([\w.]+)\/([\w]+)\/([\w]+)\/([\w]+)\.html$/i';
//         $reggg = 'http:\/\/';
//         $str = 'http://17yueke.cn/s/show_page/id_ABCDEFG.html';
        
//         preg_match_all('http:\/\/([\w])', $str, $match);
//         print_r($match);
//         exit;
        
        
//         $matches = array();
//         if(preg_match($reggg, $str, $matches)){
//             var_dump($matches);
//         }
//         echo "\n";
//         exit;
        
        
        
        $this->display('avatarupdata');
    }
    public function avatarShift(){

        $validates=array(
            array('verify','require','验证码不能为空', 1),
            array('verify','check_verify','验证码错误！',1,'function'), //默认情况下用正则进行验证
            array('password','require','密码不能为空', 1),
        );
        $autodate=array();
        if (!M('Wx')->validate($validates)->auto($autodate)->create()) {
            $this->ajaxReturn(array(
                'status'    =>  400,
                'msg'       =>  M('Wx')->getError(),
            ));
        }

        $password = I('post.password',0,'intval');
        if ($password!=88888888){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'msg'       =>  '转移密码错误',
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  400,
            'msg'       =>  '该功能开发中，敬请期待',
        ));
    }
    
    
    public function getcardlist(){
        require_once ('./Api/wx/config.php');
        require_once ('./Api/wx/wx_def.php');
        $wechatObj = new \wechatCallbackapiTest(WX_APP_ID,WX_APP_SECRET);
//         $info = $wechatObj->getcard();
        $info = $wechatObj->toucard();
        print_r($info);
        exit;
    }


    public function getmenu(){
        require_once ('./Api/wx/config.php');
        require_once ('./Api/wx/wx_def.php');
        $wechatObj = new \wechatCallbackapiTest(WX_APP_ID,WX_APP_SECRET);
        $info = $wechatObj->infomenu();
        print_r($info);
        exit;
    }


    public function getmenuauto(){
        require_once ('./Api/wx/config.php');
        require_once ('./Api/wx/wx_def.php');
        $wechatObj = new \wechatCallbackapiTest(WX_APP_ID,WX_APP_SECRET);
        $info = $wechatObj->infomenuauto();
        print_r($info);
        exit;
    }
    
    
    
    
    
}