<?php
namespace Admin\Controller;
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
	public $perPage = 30;
	
	/**
	 * 用户管理
	 */
	public function index() {
		
		$sort   =I('get.sort')?I('get.sort'):'desc'; //排序
		$curPage=I('get.page')?I('get.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
		$User = D('User');// 实例化User对象
		$pageArray  = $User->userpage($curPage,$this->perPage);	//获取user分页
		$info	    = $User->userinfo($pageArray['pageOffset'],$pageArray['perPage'],$sort);  //获取user的信息
		$this->assign('order',$sort); // 赋值分页输出
		$this->assign('page',$pageArray); // 赋值分页输出
		$this->assign('rel',$info);	//组团信息
		$this->display();
	}

	
	
	/**
	 * 用户详情
	 */
    public function details() {
    	$id=I('get.uid');
    	$User  = D("User");
    	$rel   = $User->userdetails($id);
    	$userInfo = D('UserV')->uservinfo($id);
    	$rel['userv']=$userInfo;
    	$this->assign('rel',$rel);
    	$this->display();
    }
    
    
    /**
     * 删除用户头像
     * 自动给用户一个默认头像
     */
    public function del() {
    	header("Content-type:text/html;charset=utf-8");
    	$id    =  I('get.uid');
    	$User  =  D("User");
    	$userfilepath   =  $User->useravatar($id);	//获取用户的头像字段信息
    	$userfilepath   =  '.'.$userfilepath;		//定义为.的路径形式
    	$ortrue=$User->checkpath($userfilepath);//判断是否为默认头像
    	if ($ortrue) {
    		redirect(U('User/details',array('uid' =>$id)),1,'已经是默认头像无法更改');
    	}
		//根据用户的头像字段信息删除头像文件
		$unseesion      =  unlink($userfilepath);
    	//判断是否成功删除违规头像
    	if ($unseesion) {
    		$relss =  $User->userpath($id);//给用户一个默认头像
    		if($relss){//判断是否已更新用户头像为默认头像 
    			redirect(U('User/details',array('uid' =>$id)),1,'给用户默认一个头像成功');
    		}else {
    			redirect(U('User/details',array('uid' =>$id)),1,'给用户默认一个头像失败了');
    		}
    	}else {
    		redirect(U('User/details',array('uid' =>$id)),1,'删除用户头像失败');
    	}
    }
    

    
    
    
	/**
	 * 用户组团所有信息
	 */
    public function dynamic() {
		$sort   =I('get.sort')?I('get.sort'):'desc'; //排序
		$curPage=I('get.page')?I('get.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
    	$group_info = D('GroupInfo');// 实例化GroupInfo对象
    	$pageArray  = $group_info->grouppage($curPage,$this->perPage);	//获取组团分页
    	$info	    = $group_info->group($pageArray['pageOffset'],$pageArray['perPage'],$sort);  //获取组团的信息
//     	//数组变成字符串
//     	foreach ($info as $key=>$value){
//     		$info[$key]['areaname']	= implode(' ',$value['areaname']);
//     	}
    	$this->assign('order',$sort); // 赋值分页输出
    	$this->assign('page',$pageArray); // 赋值分页输出
    	$this->assign('rel',$info);	//组团信息
    	$this->display();
    }

    

    
    /**
     * 某条约课下的信息
     */
    public function groupinfo(){
header("content-Type: text/html; charset=Utf-8");//设置字符编码
		$gid    = I('get.id',0, 'intval')?I('get.id',0, 'intval'):0;
        $result = D('GroupInfo')->getInfoAllBygid($gid);
// print_r($result);exit;
        $this->assign('info',$result);
        $this->display();
    }
    

    /**
     * 某条约课下的信息的跟约信息
     */
    public function groupassist(){
		$gid   =I('post.gid', 1, 'intval')?I('post.gid', 1, 'intval'):0; //gid
		$curPage= I('post.page', 1, 'intval')?I('post.page', 1, 'intval'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
		$perPage= 30;//每页显示的记录数
		$GroupAssist  = D('GroupAssist');
        $GAssist  = $GroupAssist->assistByGid($gid,$curPage,$perPage,$order='desc');
        if ($GAssist===false){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'gassist'      =>  $GAssist,
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'gassist'      =>  $GAssist,
        ));
    }

    /**
     * 某条约课下的信息的推送信息
     */
    public function grouppush(){
		$gid   =I('post.gid', 1, 'intval')?I('post.gid', 1, 'intval'):0; //gid
		$curPage= I('post.page', 1, 'intval')?I('post.page', 1, 'intval'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
		$perPage= 30;//每页显示的记录数
		$GroupPushed  = D('GroupPushed');
        $GPushed  = $GroupPushed->pushedByGid($gid,$curPage,$perPage,$order='desc');
        if ($GPushed===false){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'gpushed'      =>  $GPushed,
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'gpushed'      =>  $GPushed,
        ));
    }

    /**
     * 某条约课下的信息的评论信息
     */
    public function groupcom(){
// header("content-Type: text/html; charset=Utf-8");//设置字符编码
		$gid   =I('post.gid', 1, 'intval')?I('post.gid', 1, 'intval'):0; //gid
		$curPage= I('post.page', 1, 'intval')?I('post.page', 1, 'intval'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
		$perPage= 30;//每页显示的记录数
		$GroupComment = D('Groupcomment');
        $GComment = $GroupComment->getComByGid($gid,$curPage,$perPage,$depth=0,$order='desc');
        if ($GComment===false){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'gcommen'      =>  $GComment,
            ));
        }
        $gcuser=array();
        foreach ($GComment['comment'] as $keygc=>$valuegc){
            if ($valuegc['uid']==0){
                $gcuser[$valuegc['sid']][] = $valuegc;
                $gcuser[$valuegc['sid']]['company_name'] = $valuegc['company_name'];
                $gcuser[$valuegc['sid']]['login_phone'] = $valuegc['login_phone'];
                $gcuser[$valuegc['sid']]['login_email'] = $valuegc['login_email'];
                $gcuser[$valuegc['sid']]['tel'] = $valuegc['tel'];
                $gcuser[$valuegc['sid']]['sid'] = $valuegc['sid'];
                $gcuser[$valuegc['sid']]['uid'] = 0;
            }else{
                $gcuser[$valuegc['uid']][] = $valuegc;
                $gcuser[$valuegc['uid']]['name']  = $valuegc['firstname'].$valuegc['lastname'];
                $gcuser[$valuegc['uid']]['phone'] = $valuegc['phone'];
                $gcuser[$valuegc['uid']]['email'] = $valuegc['email'];
                $gcuser[$valuegc['uid']]['uid'] = $valuegc['uid'];
                $gcuser[$valuegc['uid']]['sid'] = 0;
            }
        }
        $gcusers=array();
        foreach ($gcuser as $kgcu=>$vgcu){
            $gcusers[]=$vgcu;
        }
        $gcusers['page']=$GComment['page'];
        $this->ajaxReturn(array(
            'status'    =>  200,
            'gcommen'      =>  $gcusers,
        ));
    }
    

    
//================================    
    /**
     * 用户个人申V
     */
    public function perAuthen(){
        $this->display('perAuthen');
    }
    /**
     * 用户社团申V
     */
    public function stAuthen(){
        $this->display('stAuthen');
    }
    
    /**
     * ajax，根据类型、页码、排序，获取用户申V的信息
     */
    public function userAuthen(){
        $vtype  = I('post.vtype')?I('post.vtype'):0;
		$sort   = I('post.sort')?I('post.sort'):'desc'; //排序
		$status = I('post.status')?I('post.status'):10; //0or2未审核
		$curPage= I('post.page')?I('post.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
		$perPage= 8;//每页显示的记录数
		$UserV=D('UserV');
        $authenV=$UserV->userAuthen($vtype,$curPage,$perPage,$sort,$status);
        if ($authenV===false){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'info'      =>  $authenV,
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'info'      =>  $authenV,
        ));
    }
    
    /**
     * ajax，根据***审核 通过 userv
     */
    public function userPassVstatus(){
        $uid  = I('post.uid')?I('post.uid'):0;
        $vtype  = I('post.vtype')?I('post.vtype'):0;
        $status  = I('post.status')?I('post.status'):0;
        $UserV=D('UserV');
        $rel = $UserV->userPassV($uid,$vtype,$status);
        if ($rel===false){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'info'      =>  $rel,
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'info'      =>  $rel,
        ));
    }
    
    
    
    /*
     * 短信推广，给所有用户
     */
    public function promotion(){
        $this->display();
    }
    /*
     * 短信推广，给所有用户---AJAX
     */
    public function promotionsms(){
		$Content = I('post.content')?I('post.content'):''; 
		$Phone = I('post.phone')?I('post.phone'):'';  
		$User = D('User');
        $rel  = $User->promotionAllSms($Content,$Phone);
        if ($rel!==true){
            $this->ajaxReturn(array(
                'status'    =>  400,
                'msg'       =>  $rel,
            ));
        }
        $this->ajaxReturn(array(
            'status'    =>  200,
            'msg'       =>  $rel,
        ));
    }

    
    
    public function unidistinct(){
header("content-Type: text/html; charset=Utf-8");//设置字符编码
        $relshop   = M('ShopInfoUser')->distinct(true)->field('user_id')->select();
        $relassist = M('GroupAssist') ->distinct(true)->field('whoid')  ->select();
        $relshopnum=count($relshop);
        $relassistnum=count($relassist);
        echo '共有'.$relshopnum.'参与了报名课程的活动<br/>';
        echo '共有'.$relassistnum.'参与了跟约约课的活动<br/>';
        
        
    }
    
    
    

    
}