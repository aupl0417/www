<?php
namespace Home\Controller;
use Common\Controller\CommonController;


/**
 * 
 * @author user
 *
 */
class UserController extends CommonController {
	

    
	/**
	 * 每页显示的条数
	 * @var int
	 */
	public $perPage = 3;


	public function index(){
		$this->display();
	}
	
	/**
	 * 编辑用户详细信息
	 */
	public function editor(){
		$User	= D('User');
		$info	= $User->myeditor();
		$this->assign('info',$info);
		$this->display('info_editor');
	}
	
	/**
	 * 保存编辑的信息
	 */
	public function saveInfo(){
		header("Content-type:text/html;charset=utf-8");
		$User	= D('User');
		$rel	= $User->saveInfo();
		if ($rel!==true) {
		    $this->ajaxReturn(array(
		        'status'  =>  400,
		        'msg'     =>  $rel,
		    ));
		}else {
		    S('returnIndex',1,20);//判断返回个人详情的的返回地址
		    $this->ajaxReturn(array(
		        'status'  =>  200,
		        'msg'     =>  $rel,
		    ));
		}
		
	}
	
	
	
	/**
	 * 用户收藏
	 */
	public function collect(){
		$uid = session('user.id');
		
		$curPage=I('post.page')?I('post.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
		$type	=I('post.type')?I('post.type'):'group';//请求的类型，grou约课信息，shop是商家课程
		
// header("Content-type:text/html;charset=utf-8");
		
		$UserCollect= D('UserCollect');
		if ($type=='group') {
	    	$pageArray  = $UserCollect->collectPage($uid,$type,$curPage,$this->perPage);	//获取收藏分页
			$rel		= $UserCollect->collectGroupInfo($uid,$pageArray['pageOffset'],$pageArray['perPage']);
		}elseif ($type=='shop'){
	    	$pageArray  = $UserCollect->collectPage($uid,$type,$curPage,$this->perPage);	//获取收藏分页
			$rel		= $UserCollect->collectShopInfo($pageArray['pageOffset'],$pageArray['perPage'],$uid);
		}
		if (IS_AJAX) {
			$data=array(
			    'info'=>$rel,
			    'page'=>$pageArray,
			);
			$this->ajaxReturn($data);
		}
		
// print_r($rel);exit;

		$this->assign('info',$rel);
		$this->display('collect');
	}
	

	
	
	
//========================

        /**
         * 用户的动态课程，包括发布的约课
         */
	public function course(){
	    
	    if (IS_AJAX){
            	    $curPage=I('post.page')?I('post.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
            	    $uid   = session('user.id');
            	    $groupByUser  =  D('Common/GroupInfo');
            	    $result       =  $groupByUser->groupByUser($uid,$curPage,$this->perPage);
        	        $this->ajaxReturn($result);
	    }
	    $this->display('course');
	}
	
	
	/**
	 * 用户组团的约课信息AJAX
	 */
	public function courseAssist(){
// header("Content-type:text/html;charset=utf-8");
	    $curPage=I('post.page')?I('post.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
	    $uid   = session('user.id');
	    $groupAssist  =  D('Common/GroupAssist');
	    $result       =  $groupAssist->userAssist($uid,$curPage,$this->perPage);
	    
        $this->ajaxReturn($result);
	}
	
	
	

	
	/**
	 * 
	 * 用户报名的课程信息 AJAX
	 */
	public function courseShop(){
// header("Content-type:text/html;charset=utf-8");
	    $curPage=I('post.page')?I('post.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
	    $uid   = session('user.id');
	    $shopInfoUser = D('User');
	    $info = $shopInfoUser->shopEnrollCourse($uid,$curPage,$this->perPage);
	    
	    $this->ajaxReturn($info);
	}
	

	public function delGroup(){
	    $gid=I('post.gid',0,'intval');
	    $group=D('GroupInfo');
	    $rel = $group->delGroupByGid($gid);
	    if ($rel!==true){
	        $this->ajaxReturn(array(
	               'status'    =>  400,
	               'msg'       =>  $rel,
	        ));
	    }
	    $this->ajaxReturn(array(
	           'status'        =>  200,
	           'msg'           =>  $rel,
	    ));
	}
	
//=================================
	/**
	 * 用户消息中---返回各种未读消息记录数
	 */
	public function news(){
	    $uid = session('user.id');
	    $NewsAssist   = D('GroupAssist');
	    $numAssist   = $NewsAssist->newsNumAssist($uid);//未读约课数
	    $NewsComment   = D('Groupcomment');
	    $numComment  = $NewsComment->newsNumComment($uid);//未读评论数
	    $NewsPushed   = D('GroupPushed');
	    $numPushed   = $NewsPushed->newsNumPushed($uid);//未读推送数
// print_r($numAssist.'<br/>'.$numComment.'<br/>'.$numPushed.'<br/>');exit;
	    
	    $this->assign('numAssist',$numAssist);
	    $this->assign('numComment',$numComment);
	    $this->assign('numPushed',$numPushed);
	    $this->display('user_news');
	}
	
	
	/**
	 * 用户消息中的约课【用户约课后在这里提示】
	 */
	public function userAssist(){
	    $uid = session('user.id');
	    $NewsAssist   = D('GroupAssist');
	    $curPage=I('post.page')?I('post.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
	    $enroll = $NewsAssist->assistInfo($uid,$curPage,$this->perPage);
// print_r($enroll);exit;
	    $this->ajaxReturn($enroll);
	}
	
	
	/**
	 * 用户消息中的评论【用户评论约课后在这里提示】
	 */
	public function userComment(){
	    $curPage=I('post.page')?I('post.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
	    $uid = session('user.id');
	    $News   = D('Groupcomment');
	    $comment = $News->groupComments($uid,$curPage,$this->perPage);
// print_r($comment);exit;
	    $this->ajaxReturn($comment);
	}
	
	
	/**
	 * 用户消息中的为你推荐【商家为用户推荐的约课】   此控制器转移到api中调用了
	 */
	public function user_suggest(){
	}
	
	public function xxx(){
		$curPage=I('post.page')?I('post.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
		$uid = session('user.id');
		$News   = D('Groupcomment');
		$comment = $News->groupComments($uid,$curPage,$this->perPage);
	}
	
	
//============设置======	
	
	/**
	 * 设置
	 */
	public function set(){
	    $uid = session('user.id');
	    $NewsSystem   = D('UserSystem');
	    $numSystem    = $NewsSystem->newsNumSystem($uid);//未读系统消息
	    $this->assign('numSystem',$numSystem);
	    $this->display('set');
	}

	/**
	 * 用户设置下面的系统消息
	 */
	public function system_info(){
	    $curPage=I('post.page')?I('post.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
	    $uid = session('user.id');
	    $UserSys = D('UserSystem');
	    $result = $UserSys->sysByUid($curPage,$this->perPage,$uid);
	    $this->assign('info',$result['info']);
	    $this->display('system_info');
	}
	
	
	
	
	/**
	 * 重置密码
	 */
	public function resetPw(){
	    $checkdo = I('post.reset');
	    if ($checkdo=='do'){
	        $User = D('User');
	        $rel = $User->userResetPw();
	        if ($rel!==true){
	            $this->ajaxReturn(
	                array('msg'=>400,
	                       'data'=>$rel, 
	            ));
	        }
	        $this->ajaxReturn(
	                array('msg'=>200,
	                       'data'=>$rel, 
	        ));
	    }
		$this->display('resetpassword');
	}
	
	/**
	 * 反馈
	 */
	public function feedback(){
		$message=I('get.reset');
		if ($message=='do') {
			$Feedback 	= D('Feedback');
			$rel		= $Feedback->addFeedback();//插入反馈信息,$_POST['feedback']
			if ($rel===true) {
				$this->redirect('User/set');
			}
			$this->assign('errorinfo',$rel);
			$this->redirect('feedback');
		}
		$this->display('feedback');
	}
	
	
	
	/**
	 * 用户注销登录
	 */
	public function outlogout(){
		if (I('get.zk')=='out') {
			$out=D('User');
			$resule=$out->userLogout();
			$this->redirect('Index/index');
		}
	}

	
	

	
	
	
	
	
}