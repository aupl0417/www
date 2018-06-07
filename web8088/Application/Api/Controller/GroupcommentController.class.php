<?php

namespace Api\Controller;
use Api\Controller\CommonController;

/**
 * 
 * @author user
 *
 */
class GroupcommentController extends CommonController {

	/**
	 * 发表组团课程的评论
	 */
	public function commentAdd(){
	    $gid=I('post.gid')?I('post.gid'):0;//为了发表评论 获取该gid的评论信息
	    $nowhref=I('post.nowhref');
		$Groupcomment = D('Groupcomment');
    	$result = $Groupcomment->addComment();
    	if ($result !== true) {// 增加失败
    	    if ($result==="401"){
    	        session('historyhref',$nowhref);
//     	        $this->ajaxReturn(array(
//     	            'status'	=>	401,
//     	            'comment'	=>	$result,
//     	        ));
//---------------------------------------------------处理游评论
    	        $visitor_id=I('post.visitorid')?I('post.visitorid'):0;
    	        if ($visitor_id!=0&&!session('?visitor')){
    	            session('visitor.id',$visitor_id);
    	        }
    	        if (!session('?visitor')){
    	            $visitor = D('Visitor');
    	            $addOneId = $visitor->addOneVisitor();//增加一个游客
    	            session('visitor.id',$addOneId);
    	        }else {
    	            $addOneId=session('visitor.id');
    	        }
    	        $result = $Groupcomment->addCommentByVisitor($gid,$addOneId);
    	        if ($result!==true){
    	            $this->ajaxReturn(array(
    	                'status'	=>	400,
    	                'comment'	=>	$result,
    	            ));
    	        }
    	        $onecom = $Groupcomment->oneCommentByVisitor(0,$gid,0,$order='desc',$addOneId);
    	        $this->ajaxReturn(array(// 增加成功
    	            'status'	=>	200,
    	            'comment'	=>	$onecom,
    	            'visitor'   =>  $addOneId,
    	        ));
//---------------------------------------------------处理游评论
    	    }elseif ($result==="408"){
    	        $this->ajaxReturn(array(
    	            'status'	=>	408,
    	            'comment'	=>	'评论过于频繁',
    	        ));
    	    }elseif ($result==="410"){
    	        $this->ajaxReturn(array(
    	            'status'	=>	410,
    	            'comment'	=>	'请先完善个人资料',
    	        ));
    	    }else {
    		$this->ajaxReturn(array(
    				'status'	=>	400,
    				'comment'	=>	$result,
    		));
    	    }
    	}
    	if (session('?user')){
    	    $uid=session('user.id');
    	    $sid=0;
    	}elseif (session('?shopkeeper')){
    	    $uid=0;
    	    $sid=session('shopkeeper.id');
    	}
		$onecom = $Groupcomment->oneComment($uid,$gid,$sid);
    	$this->ajaxReturn(array(// 增加成功
    			'status'	=>	200,
    			'comment'	=>	$onecom,
    	));
	}
	


	/**
	 * 获取某用户最新一条评论 该gid的评论内容
	 * @param number $uid
	 * @param number $gid
	 */
	public function comNewsByGidUid($uid=0,$gid=0){
	    $Groupcomment = D('Groupcomment');
	    $result = $Groupcomment->oneComment($uid,$gid);
	    if ($result === false) {// 增加失败
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
	 * 根据get提交的gid 获取该gid的评论信息
	 */
	public function comGroup(){
header("content-Type: text/html; charset=Utf-8");//设置字符编码
	    $gid = I('post.gid')?I('post.gid'):0;
	    $curPage=I('post.page')?I('post.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
	    $depth=I('post.depth')?I('post.depth'):0;      //当前深度。   取值，从前端传过来的值,通过get来获取参数
	    $perPage=5;//每页显示
	    $commentGroup = D('Groupcomment');
	    $rel = $commentGroup->commentByGid($gid,$curPage,$perPage,$depth,$order='desc');
// print_r($rel);exit;
	    if ($rel === false) {// 增加失败
		$this->ajaxReturn(array(
				'status'	=>	400,
				'comment'		=>	$rel,
		));
	    }
	    $this->ajaxReturn(array(// 增加成功
			'status'	=>	200,
			'comment'		=>	$rel,
	    ));
	}
	
	

	/**
	 * AJAX返回当前用户的 被评论数--现在首页在轮循中
	 */
	public function newsComment(){
	    $uid = session('user.id');
	    $NewsComment   = D('Groupcomment');
	    $numComment  = $NewsComment->newsNumComment($uid);//未读评论数
	    if ($numComment===false){
	        $this->ajaxReturn(array(
	            'status'	=>	400,//查询错误
	            'data'	=>	$numComment,
	        ));
	    }
	    $this->ajaxReturn(array( // 查询跟约数
	        'status'	=>	200,
	        'data'	=>	$numComment,
	    ));
	}
	
	
	/**
	 * 删除某条约课下的某条评论
	 */
	public function delGcom(){
	    $comid    = I('post.comid')?I('post.comid'):0;
	    $gid      = I('post.gid')?I('post.gid'):0;

	    $nowhref=I('post.nowhref');
	    if (!session('?user')){
    	    session('historyhref',$nowhref);
// 	        $this->ajaxReturn(array(
// 	            'status'=>	401,//查询错误
// 	            'data'	=>	'请先登录！',
// 	        ));
//------------------------------------------游客评论处理
            if (!session('?visitor')){
    	        $this->ajaxReturn(array(
    	            'status'=>	400,//查询错误
    	            'data'	=>	'此评论不能删除!',
    	        ));
            }
            $visitor_id=session('visitor.id');
            $delVisiCom=D('Groupcomment')->delCommByGid($gid,$comid,0,$visitor_id);
            if ($delVisiCom!==true){
                $this->ajaxReturn(array(
                    'status'    =>  400,
                    'data'      =>  '此评论不能删除!',
                ));
            }
            $this->ajaxReturn(array(
                'status'    =>  200,
                'data'      =>  $delVisiCom,
            ));
//-------------------------------------------游客评论处理
	    }
	    
	    $user = session('user.id');
	    $delModel = D('Groupcomment');
	    $delInfo  = $delModel->delCommByGid($gid,$comid,$user);
	    
// print_r($delInfo);exit;

        if ($delInfo!==true){
	        $this->ajaxReturn(array(
	            'status'=>	400,//查询错误
	            'data'	=>	$delInfo,
	        ));
	    }
	    $this->ajaxReturn(array( // 查询跟约数
	        'status'=>	200,
	        'data'	=>	$delInfo,
	    ));
	}
	
	
	
	
}