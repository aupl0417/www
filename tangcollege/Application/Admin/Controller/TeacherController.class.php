<?php

namespace Admin\Controller;
use Common\Model\BranchModel;
use Common\Model\TeacherModel;
use Common\Model\TeacherLevelModel;
use User\Api\UserApi;
/**
 * 后台班级课时管理控制器
 */
class TeacherController extends AdminController {
   protected $branchModel;
   protected $teacherModel;
   protected $teacherLevelModel;
   public function __construct() {
	   $this->branchModel =  new BranchModel();	
	   $this->teacherModel =  new TeacherModel();
	   $this->teacherLevelModel =  new TeacherLevelModel();
       parent::__construct();
    }
   
    public function index(){
		$pageSize = C('SHOW_PAGE_SIZE');
		$where[] = 'branchId in('.implode(',',$this->branchIds).')';
		$words = I('get.words');
		$words = isset($words) ? trim($words) : '';
		if(!empty($words)) {
		  if(preg_match('/^[1-9]\d*$/',$words)) {
			$where[] ='(id ="'.$words.'" or username like "%'.$words.'%")'; 
		  }else{
			$where[] ='username like "%'.$words.'%"';   
		  }
		}
		$where = implode(' and ',$where);
		$count = $this->teacherModel->infoCount($where);
		if($pageSize < $count) {
		   $Page = new \Think\Page($count,$pageSize);
           $lists = $this->teacherModel->lists($where,'','',$Page->firstRow.','.$Page->listRows);
		   $page      = $Page->show();
		   $this->assign('page',$page);
		}else{
		   $lists = $this->teacherModel->lists($where);
		} 
		$this->assign('lists',$lists);
        $this->display(__FUNCTION__);
    }
	
	public function add($username = '', $password = '', $repassword = '',$branchId=0) {
		 if(IS_POST){
			if(!empty($branchId) && !in_array($branchId,$this->branchIds)) {
				$this->error('非法操作！'); 
			}
            if($password != $repassword){
                $this->error('密码和重复密码不一致！');
            }
		   $data = I('post.');	
           if(!$this->teacherModel->addInfoCacheClean($data)){
				$errorMsg = empty($this->teacherModel->getError()) ? '教师添加失败' : $this->teacherModel->getError();
			    $this->error($errorMsg); 
            } else {
                $this->success('教师添加成功！',U('index'));
            }
        } else {
			$Brach = new BranchModel();
			$brachLists = $Brach->recursionCache(BRANCHID);
			$levelLists = $this->teacherLevelModel->lists(); 
			$this->assign('brachLists',json_encode($brachLists));
			$this->assign('levelLists',$levelLists);
            $this->meta_title = '新增教师';
            $this->display(__FUNCTION__);
        }
	}
	public function lists($page=0,$banchId=0,$isAjaxWay=true) {
		$fields = I('get.');
		$map = [];
		$branchIds = [];
		if(!empty($fields['branchId'])) {
			$branchIds = $this->branchModel->getPosterityIdsCache($fields['branchId']) + (array)$fields['branchId'];
		}else{
			$branchIds = $this->branchIds;
		}
		$map[] =  'branchId in('.implode(',',$branchIds).')';
		if($fields['words']!='') {
			$map[] = 'username like "%'.$fields['words'].'%"';	
		}
		if(!($page > 0)) 
		    $page = 1;
		 $pageSize = C('SHOW_PAGE_SIZE');
		 $where = implode(' and ',$map);	
		 $total  = $this->teacherModel->infoCount($where);
		 $totalPage = ceil($total/$pageSize);
		 $page = max(min($page,$total),0);
		 $limit = ($page - 1)* $pageSize .',' . $pageSize;
		 $lists = $this->teacherModel->lists($where,'','',$limit);
		 $lists = !empty($lists) ? $lists : [];
		 if($isAjaxWay) {
			$returnData = compact("pageSize","totalPage","page","lists"); 
			$this->ajaxReturn($returnData,'JSON'); 
		 }else {
		    $this->assign('page',$page);
		    $this->assign('totalPage',$totalPage);
		    $this->assign('teacherList',$lists);
			$this->display(__FUNCTION__);
		 }
	}	
    public function rank() {
		$TeacherLevelModel = new TeacherLevelModel();
		$lists = $TeacherLevelModel->listsCache();
		$this->assign('lists',$lists);
		$this->display(__FUNCTION__);
		
	}
	
	public function rankedit($id='') {
		if(empty($id)) {
		   $this->error('ID不能为空');
		   exit();
		}
		$TeacherLevelModel = new TeacherLevelModel();
		if(IS_POST) {
		  if(!$TeacherLevelModel->editInfoCacheClean($id)){  
			     $errorMsg = empty($TeacherLevelModel->getError()) ? '更新失败' : $TeacherLevelModel->getError();
			      $this->error($errorMsg); 
            } else {
                 $this->success('更新成功！',U('index'));
           }
		}else{
		  $info = $TeacherLevelModel->infoCache($id);	
		  $this->assign('info',$info);
		  $this->display(__FUNCTION__);
		}
		
	}
	
	public function rankdel($id='') {
		if(empty($id)) {
		   $this->error('ID不能为空');
		   exit();
		}
		$TeacherLevelModel = new TeacherLevelModel();
		
		  if(!$TeacherLevelModel->delInfoCacheClean($id)){  
			      $this->error('删除失败'); 
            } else {
                 $this->success('删除成功');
           }
		
	}
	
	public function rankadd() {
		
		if(IS_POST) {
		  $TeacherLevelModel = new TeacherLevelModel();
		  if(!$TeacherLevelModel->addInfoCacheClean()){  
			     $errorMsg = empty($TeacherLevelModel->getError()) ? '添加失败' : $TeacherLevelModel->getError();
			      $this->error($errorMsg); 
            } else {
                 $this->success('添加成功！',U('index'));
           }
		}else{
		  $this->display(__FUNCTION__);
		}
		
	}
	public function del($id='') {
		if(empty($id)) {
		   $this->error('ID不能为空');
		   exit();
		}
		if($this->teacherModel->delInfoCacheClean($id))
		  $this->success('删除成功！');
		else{
		  $this->error('删除失败！');  
		}
	}
	
	public function getTeacherTempletByAjax() {
		$this->display(__FUNCTION__);
	}
	
	public function edit($id, $password = '', $repassword = '',$branchId='') {
		 if(empty($id)) {
			 $this->error('ID不能为空');
		 }
		 $where =  'id="'.$id.'" and branchId in('.implode(',',$this->branchIds).')';
		 $info = $this->teacherModel->infoCache($where);
		 if(empty($info)) {
			$this->error('信息不存在！'); 
		 }
		 if(IS_POST){
			$data = I('post.');
            /* 检测密码 */
            if(!(empty($password) && empty($repassword)) && ($password != $repassword)){
                $this->error('密码和重复密码不一致！');
            }else{
				unset($data['password']);
			} 
            if(!$this->teacherModel->editInfoCacheClean($id,$data)){
					$errorMsg = empty($this->teacherModel->getError()) ? '教师更新失败' : $this->teacherModel->getError();
			        $this->error($errorMsg); 
            } else {
                    $this->success('教师添加成功！',U('index'));
            }
            
        } else {
			$Brach = new BranchModel();
			$brachLists = $Brach->recursionCache(BRANCHID);
			$levelLists = $this->teacherLevelModel->lists();
			$this->assign('info',$info);
			$this->assign('brachLists',json_encode($brachLists));
			$this->assign('levelLists',$levelLists);
            $this->meta_title = '新增教师';
            $this->display(__FUNCTION__);
        }
	}
}
