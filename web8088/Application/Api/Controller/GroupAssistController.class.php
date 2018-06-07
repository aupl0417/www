<?php
namespace Api\Controller;
use Api\Controller\CommonController;
/**
 *
 * @author user
 *
 */
class GroupAssistController extends CommonController {
	
    /**
     * 添加一条跟约信息
     */
	public function addAssist(){
		$gid=I('post.groupid');
		$nowhref=I('post.nowhref');
		$visitor_id=I('post.visitorid')?I('post.visitorid'):0;
		if (!session('?user')){//还未登录
		        session('historyhref',$nowhref);
//  				$this->ajaxReturn(array(
//  						'status'	=>	401,
//  						'assist'	=>	'请先登录！',
//  				));
//--------------------游客跟约处理
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
		        $result = D('GroupAssist')->addAssistByVisitor($gid,$addOneId);
//--------------------游客跟约处理-				
		        
		}else {
		    $uid=session('user.id');
    		$Groupassist = D('GroupAssist');
    		$result = $Groupassist->addAssistByUser($uid,$gid);
    		$addOneId=0;//不是游客
		}
		

		if ($result !== true) {
			if ($result==="404"){ //已经跟约成功并且删除成功
				$this->ajaxReturn(array(
						'status'	=>	404,
						'assist'	=>	$result,
				));
			}else {
				$this->ajaxReturn(array(
						'status'	=>	400,
						'assist'	=>	$result,
				));
			}
		}
		$this->ajaxReturn(array(// 增加成功
				'status'	=>	200,
				'assist'	=>	$result,
		        'visitor'   =>  $addOneId,
		));
	}
	
	
	/**
	 * AJAX返回当前用户的 被跟约数  现在首页在轮循中
	 */
	public function newsAssist(){
	    $uid = session('user.id');
	    $NewsAssist   = D('GroupAssist');
	    $numAssist   = $NewsAssist->newsNumAssist($uid);//未读约课数
	    if ($numAssist===false){
	        $this->ajaxReturn(array(
	            'status'	=>	400,//查询错误
	            'data'	=>	$numAssist,
	        ));
	    }
	    $this->ajaxReturn(array( // 查询跟约数
	        'status'	=>	200,
	        'data'	=>	$numAssist,
	    ));
	}
	
}