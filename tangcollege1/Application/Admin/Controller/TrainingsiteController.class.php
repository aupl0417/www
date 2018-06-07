<?php

namespace Admin\Controller;
use Common\Model\TrainingsiteModel;
use Common\Model\BranchModel;
/**
 * 培训地址控制器
 */
class TrainingsiteController extends AdminController {
   protected $trainingsiteModel;
   public function __construct() {
	   $this->trainingsiteModel =  new TrainingsiteModel();	
       parent::__construct();
    }
   
    public function index(){
        $pageSize = C('SHOW_PAGE_SIZE');
		$where = '';
		if(BRANCHID!=0) {
		   $where = 'tra_branchId in('.implode(',',$this->branchIds).')';
		}
		$count = $this->trainingsiteModel->infoCount($where);
		if($pageSize < $count) {
		   $Page = new \Think\Page($count,$pageSize);
           $lists = $this->trainingsiteModel->lists($where,'','',$Page->firstRow.','.$Page->listRows);
		   $page      = $Page->show();
		   $this->assign('page',$page);
		}else{
		   $lists = $this->trainingsiteModel->lists($where);
		} 
		$this->assign('lists',$lists);
        $this->display(__FUNCTION__);
    }
	
	public function getTrainingsiteListByAjax($page=1,$banchId='') {
		 $this->lists($page,false);
		 $html = $this->fetch('getTrainingsiteListByAjax');
		 echo $html;
	}
    public function lists($page=1,$isAjaxWay=true) {
		if(!($page > 0)) 
		    $page = 1;
		 $pageSize = C('SHOW_PAGE_SIZE');	
		 $where = '';
		 if(BRANCHID!=0) {
		   $where = 'tra_branchId in('.implode(',',$this->branchIds).')';
		 }
		 $total = $this->trainingsiteModel->infoCount($where);
		 $totalPage = ceil($total/$pageSize);
		 $page = max(min($page,$total),0);
		 $limit = ($page - 1)* $pageSize .',' . $pageSize;
		 $lists = $this->trainingsiteModel->lists($where,'','',$limit);
		 if($isAjaxWay) {
			$returnData = compact("pageSize","totalPage","page","lists"); 
			$this->ajaxReturn($returnData,'JSON'); 
		 }else {
		    $this->assign('page',$page);
		    $this->assign('totalPage',$totalPage);
		    $this->assign('trainingsiteList',$lists);
		 }
		
	}
	public function del($id='') {
		if(empty($id)) {
		   $this->error('ID不能为空');
		}
		$where = '';
		if(BRANCHID!=0) {
		   $where = 'tra_id="'.$id.'" and tra_branchId in('.implode(',',$this->branchIds).')';
		}else{
		    $where = $id;	
		}
		if($this->trainingsiteModel->delInfoCacheClean($where))
		  $this->success('删除成功！');
		else{
		  $this->error('删除失败！');  
		}
	}
	public function getAddTrainingSiteTempletByAjax() {
		if(IS_POST){
		   if($this->trainingsiteModel->addInfoCacheClean()) {
			 $this->success('添加成功！');  
		   }else{
			 $errorMsg = empty($this->trainingsiteModel->getError()) ? '添加失败' : $this->trainingsiteModel->getError();
		     $this->error($errorMsg);  
		   }
		}else{
		   $Brach = new BranchModel();
		   $brachLists = $Brach->recursionCache(BRANCHID);
		   $this->assign('brachLists',json_encode($brachLists));
		   $this->display(__FUNCTION__);
		}
	}
	public function getEditTrainingSiteTempletByAjax($id='') {
		if(empty($id)) {
			 $this->error('ID不能为空');
		}
		$where = '';
		if(BRANCHID!=0) {
		   $where = 'tra_id="'.$id.'" and tra_branchId in('.implode(',',$this->branchIds).')';
		}else{
		    $where = $id;	
		}
		if(IS_POST){
		   if($this->trainingsiteModel->editInfoCacheClean($where)) {
			 $this->success('编辑成功！');  
		   }else{
			 $errorMsg = empty($this->trainingsiteModel->getError()) ? '编辑失败' : $this->trainingsiteModel->getError();
		     $this->error($errorMsg);  
		   }
		}else{
		   $info = $this->trainingsiteModel->infoCache($where);
		   if(empty($info)) {
			   $this->error('信息不存在！');
		   }
		   $this->assign('info',$info);
		   $Brach = new BranchModel();
		   $brachLists = $Brach->recursionCache(BRANCHID);
		   $this->assign('brachLists',json_encode($brachLists));
		   $this->display(__FUNCTION__);
		}
		
	}
}
