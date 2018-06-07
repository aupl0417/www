<?php
namespace Home\Controller;
use Common\Controller\CommonController;

class GroupInfoController extends CommonController {

	public function release() {
		$title=I('post.title');
		$Mode=C('mode');
		$this->assign('mode',$Mode);
		$this->display();
	}
	
	public function organiza(){
	
		$this->display();
	}

	public function feature(){
		$this->display();
	}
	
	public function addwish(){
	    $this->display();
	}

	public function business(){
		$ShopInfo=D('ShopInfo');
		//获取主页的课程推广
		$gener=$ShopInfo->gener();
		$this->assign('gener',$gener);
		$this->display();
	}
	

	
	/**
	 * 获取用户发布的约课信息
	 */
	public function groupinfo(){
		$sort   =I('get.sort')?I('get.sort'):'desc'; //排序
		$curPage=I('get.page')?I('get.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
		$group_info = D('GroupInfo');// 实例化GroupInfo对象
		$uid		= session('user.id');
		$pageArray  = $group_info->grouppage($curPage,$this->perPage,$uid);	//获取组团分页
		$info	    = $group_info->userGroupInfo($uid,$pageArray['pageOffset'],$pageArray['perPage'],$sort);  //获取组团的信息
		//数组变成字符串
		foreach ($info as $key=>$value){
			$info[$key]['areaname']	= implode(' ',$value['areaname']);
		}	 
		$this->assign('order',$sort); // 赋值分页输出
		$this->assign('page',$pageArray); // 赋值分页输出
		$this->assign('rel',$info);	//组团信息
		$this->display();
	}
	
	
	
	/**
	 * 根据get提交过来的gid，获取该gid的详细信息
	 */
	public function onedetail(){
header("Content-type:text/html;charset=utf-8");
              $gid = I('get.gid');
	    $groupOne = D('GroupInfo');
	    $info  = $groupOne->groupInfoOne($gid);

              $this->assign('info',$info);
	    $this->display('gdetail');
	}
	
	
	/**
	 * 根据get提交过来的gid，page来 获取该gid的组团人的信息
	 */
	public function assistGroup(){
	    $gid = I('get.gid')?I('get.gid'):1;
	    $curPage=I('get.page')?I('get.page'):1;      //当前页码。   取值，从前端传过来的值,通过get来获取参数page
	    $perPage=8;//每页显示的组团人数
	    $assistUser = D('GroupAssist');
	    $assist = $assistUser->assistByGid($gid,$curPage,$perPage,$order='desc');
//               $data=array('info'=>$assist);
// print_r($data);exit;
              $this->ajaxReturn($assist);              
	}
	
	
	
	
	
	
	
	
	
}