<?php

namespace Admin\Controller;
use Common\Model\BranchModel;
use Common\Model\GradeModel;
use Common\Model\ClassModel;
use Common\Model\ClassTableModel;
/**
 * 后台班级课时管理控制器
 */
class ClassTableController extends AdminController {
   protected $branchModel;
   protected $gradeModel;
   protected $classModel;
   protected $classTableModel;
   public function __construct() {
	   $this->branchModel =  new BranchModel();	
	   $this->gradeModel =  new GradeModel();
	   $this->classTableModel = new ClassTableModel();	
       parent::__construct();
    }
   
    public function index(){
		$pageSize = C('SHOW_PAGE_SIZE');
		$where = 'cl_branchId in('.implode(',',$this->branchIds).')';
		$count = $this->classTableModel->infoCount($where);
		if($pageSize < $count) {
		   $Page = new \Think\Page($count,$pageSize);
           $lists = $this->classTableModel->lists($where,null,null,$Page->firstRow.','.$Page->listRows);
		   $page      = $Page->show();
		   $this->assign('page',$page);
		}else{
		   $lists = $this->classTableModel->lists($where,null,null);
		} 
		$this->assign('lists',$lists);
        $this->display(__FUNCTION__);
    }
	
	public function add() {
		if(IS_POST) {	
		   if($id = $this->classTableModel->addInfoCacheClean()) {
			  $this->success('添加成功');  
		   }else{
			  $errorMsg = empty($this->classTableModel->getError()) ? '添加失败' : $this->classTableModel->getError();
			  $this->error($errorMsg);  
		   }   
		}
	}
	
	public function getClassTableImageByAjax($id) {
		   if(empty($id))
		     $this->error('ID不能为空！');
		   $info = $this->classTableModel->infoCache(['cta_id="'.$id.'"'],'cta_image');	
		   if(!empty($info['cta_image']) && is_file('.'.$info['cta_image'])) {  //暂时简单处理
			  echo $info['cta_image'];
			  exit();
		   }	
		   vendor('qrcode.phpqrcode');
		   $data = 'tangcollege_'.$id;
		   $level = 'L';
		   $size = 7;
		   $path = "./Uploads/Download/";
           $fileName = $path.$size.time().'.png';
		   \QRcode::png($data, $fileName, $level, $size);
		   $save['cta_image'] = ltrim($fileName,'.');
		   $this->classTableModel->editInfo($id,$save);
		   echo $save['cta_image'];
		   exit();
	}
	
	
	
	public function del($ids='') {
		if(empty($ids)) {
			$this->error('ID不能为空！'); 
		}
		if(is_array($ids)) {
			$ids =array_filter($ids,function ($v, $k) {
                return $v > 0 ? true : false;
            });
		    if(empty($ids)) {
			   $this->error('ID不能为空！');
			}
		}elseif(is_numeric($ids)) {
		    $ids = (array)$ids;	
		}else{
		   $this->error('非法操作！');
		}
		$where = 'cta_id in('.implode(',',$ids).')';
		$result = $this->classTableModel->lists($where,'cl_branchId');
		$branchIds = array_column($result,'cl_branchId');
		$diff = array_diff($branchIds,$this->branchIds);
		if(count($diff) > 0) {
			 $this->error('非法操作！');
		}
		if($this->classTableModel->delCacheClean($where)){
		    $this->success('删除成功');
		}else{	
			$this->error('删除失败'); 
	  	}
	 
	}
	
	public function edit($id='') {
        if(empty($id)) {
			$this->error('ID不能为空！'); 
			
		}
		$info = $this->classTableModel->infoCache(['cta_id="'.$id.'"'],'cl_branchId');
		if(empty($info)) {
			$this->error('信息不存在！');
		}
		if(!in_array($info['cl_branchId'],$this->branchIds)) {
			$this->error('非法操作！');
		}
		$isOK = $this->classTableModel->editInfoCacheClean($id);
		if($isOK)
		   $this->success('编辑成功');
		else{	
		    $errorMsg = empty($this->classTableModel->getError()) ? '编辑失败' : $this->classTableModel->getError();
			$this->error($errorMsg);  
		}
	}
	
	
	
	public function getClassTableListTempletByAjax() {
		$classId = I('get.classId');
		$where = null;
		if(!empty($classId)) {
			$where['cl_id'] = $classId;
			$where['cl_branchId'] = array('in',implode(',',$this->branchIds));
			$this->assign('classId',$classId);
		}
		$lists = $this->classTableModel->lists($where);
		$this->assign('lists',$lists);
		$this->assign('empty','<td colspan="8" align="center">没有课时</td>');
		$this->display(__FUNCTION__);
	}
	
	public function sortClassTableByAjax($id='',$cta_sort='') {
		if(empty($id)) {
			 $this->error('ID不能为空！'); 
		}
		if(!($cta_sort > 0)) {
			 $this->error('排序值不对！'); 
		}
		if($this->classTableModel->editInfoCacheClean($id,['cta_sort'=>$cta_sort])) {
			  $this->success('修改成功！'); 
		}else{
			  $this->error('修改失败！');  
		}
	}
	
	public function getAddClassTableTempletByAjax() {
		 $classId = I('get.classId');
		 if(!empty($classId)) {
			   $classModel = new ClassModel();
			   $info = $classModel->infoCache($classId,'cl_name,cl_branchId');
			   if(empty($info) || !in_array($info['cl_branchId'],$this->branchIds)) {
			      $this->error('非法操作！');
		       }
			   $classInfo['name'] = $info['cl_name'];
			   $classInfo['id'] = $classId; 
			   $this->assign('classInfo',$classInfo);  
		   }
		 $this->display(__FUNCTION__);
		
	}
	
	public function getEditClassTableTempletByAjax($id = '',$act = 0) {
		$info = $this->classTableModel->infoCache($id);
		if(empty($info) || !in_array($info['cl_branchId'],$this->branchIds)) {
			      $this->error('非法操作！');
		}
		$this->assign('info',$info);
		$this->assign('act',$act);
		$this->display(__FUNCTION__);
	}
	
}
