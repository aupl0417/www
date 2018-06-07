<?php

namespace Admin\Controller;
use Common\Model\BranchModel;
use Common\Model\GradeModel;
use Common\Model\ClassModel;
use Common\Model\ClassTableModel;
/**
 * 后台班级管理控制器
 */
class ClassController extends AdminController {
   protected $branchModel;
   protected $gradeModel;
   protected $classModel;
   public function __construct() {
	   $this->branchModel =  new BranchModel();	
	   $this->gradeModel =  new GradeModel();
	   $this->classModel = new ClassModel();	
       parent::__construct();
    }
    
    public function index(){
		$pageSize = C('SHOW_PAGE_SIZE');
		$where = 'cl_branchId in('.implode(',',$this->branchIds).')'; 
		$count = $this->classModel->infoCount($where);
		if($pageSize < $count) {
		   $Page = new \Think\Page($count,$pageSize);
           $lists = $this->classModel->lists($where,'*','cl_createTime DESC',$Page->firstRow.','.$Page->listRows);
		   $page      = $Page->show();
		   $this->assign('page',$page);
		}else{
		   $lists = $this->classModel->lists($where,'*','cl_createTime DESC');
		} 
		$this->assign('lists',$lists);
        $this->display(__FUNCTION__);
    }
	
	public function add($cl_branchId='') {
		if(IS_POST) {
		   if(!empty($cl_branchId) && !in_array($cl_branchId,$this->branchIds)) {
			 $this->error('您没有权限！');
		   }
		   $fields = I('post.');
		   if(isset($fields['icon'])) {
			  $fields['cl_logo'] = current($fields['icon']);
			  unset($fields['icon']); 
		   }
		   if($this->classModel->addInfoCacheClean($fields)) {
			  $this->success('添加成功',U('index'));  
		   }else{
			  $errorMsg = empty($this->classModel->getError()) ? '添加失败' : $this->classModel->getError();
			  $this->error($errorMsg);  
		   }   
		}else{
		   $brachLists = $this->branchModel->recursionCache(0);
		   $this->assign('brachLists',json_encode($brachLists));
		   $gradeLists = $this->gradeModel->lists();
		   $this->assign('gradeLists',$gradeLists);
		   $this->display(__FUNCTION__);	
		}
	}
	
	public function del($ids='') {
		$idstr = '';
		if(is_array($ids)) {
			$ids =array_filter($ids,function ($v, $k) {
                return $v > 0 ? true : false;
            });
			if(!empty($ids))
			   $idstr = implode(',',$ids);
		}elseif($ids > 0){
			$idstr = $ids;
		}
		if(!(empty($ids))) {
			$map['cl_id'] = array('in',$idstr);
			$map['cl_branchId'] = array('in',implode(',',$this->branchIds));
			if($this->classModel->delCacheClean($map))
			  $this->success('删除成功');
			else{	
			  $this->error('删除失败'); 
			}
		}else{
			$this->error('ID不能为空');
		}
	}
	
	public function edit($id='') {
		if(IS_POST) {
		   $fields = I('post.');
		   if(isset($fields['icon'])) {
			  $fields['cl_logo'] = current($fields['icon']);
			  unset($fields['icon']); 
		   }
		   $map['cl_id'] = array('in',$id);
		   $map['cl_branchId'] = array('in',implode(',',$this->branchIds));
		   if($this->classModel->editInfoCacheClean($map,$fields)) {
			  $this->success('编辑成功',U('index'));  
		   }else{
			  $errorMsg = empty($this->classModel->getError()) ? '编辑失败' : $this->classModel->getError();
			  $this->error($errorMsg);  
		   }   
		}else{
		   $brachLists = $this->branchModel->recursionCache(BRANCHID);
		   $this->assign('brachLists',json_encode($brachLists));
		   $where = 'cl_id="'.$id.'" and cl_branchId in('.implode(',',$this->branchIds).')';
		   $info = $this->classModel->infoCache($where);
		   if(empty($info)) {
			   $this->error('信息不存在！');  
		   }
		   $this->assign('info',$info);
		   $gradeLists = $this->gradeModel->lists();
		   $this->assign('gradeLists',$gradeLists);
		   $this->display(__FUNCTION__);	
		}
	}
	
	public function getselectClassListTempletByAjax() {
		 $this->display(__FUNCTION__);	
	}
	public function getselectClassListByAjax() {
		$fields = I('get.');
		$map = [];
		if(empty($fields['branchId'])) {
		    $map[] = 'cl_branchId in('.implode(',',$this->branchIds).')';
		}else{
			$map[] = 'cl_branchId ="'.$fields['branchId'].'"';
		}
		if($fields['time']!='') {
			$map[] = 'cl_startTime >="'.$fields['time'].'"';	
		}
		if($fields['words']!='') {
			$map[] = 'cl_name like "%'.$fields['words'].'%"';	
		}
		$classLIsts = $this->classModel->lists(!empty($map) ? implode(' and ',$map) : '','cl_id,cl_name,br_name');
		$this->ajaxReturn($classLIsts,'JSON');
	}
	
	public function selectClassCourseListByAjax($id) {
		if(empty($id)) {
			 $this->error('ID不能为空！');
		}
		$where = 'cl_id="'.$id.'" and cl_branchId in('.implode(',',$this->branchIds).')'; 
		$courseLists = $this->classModel->getCoursesById($where);
		$courseLists = !empty($courseLists) ? $courseLists : [];
		$this->ajaxReturn($courseLists,'JSON');
	}
	//获得该班级下面的学生
	public function getClassStudnetListTempletByAjax($id) {
		if(empty($id)) {
			 $this->error('ID不能为空！');
		}
		$where = 'cl_id="'.$id.'" and cl_branchId in('.implode(',',$this->branchIds).')'; 
		$lists = $this->classModel->getStudentListsByclassId($where);
		$this->assign('lists',$lists);
		$this->assign('empty','<td colspan="8" align="center">没有学生</td>');
		$this->display(__FUNCTION__);
	}
	//删除班级下面的学生
	public function delstudent($classId,$stuId) {
		if(empty($classId) || empty($stuId)) {
			$this->error('删除失败！');
		}
		if($this->classModel->delstudent(['cs_classId'=>$classId,'cs_studentId'=>$stuId])) {
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
		
	}
	public function importclassTableTemplet($id='') {
		if(empty($id)) {
			 $this->error('ID不能为空！');
		}
		if(IS_POST) {
		   $data = I('post.classTableTempletIds');
		   if(empty($data)) {
			    $this->error('请选择要导入的数据！');
		   }
		   $where = 'ctt_id in('.implode(',',$data).')';
		   $classTableModel = new ClassTableModel();
		   if($classTableModel->addInfoFromClassTableTemplet($id,$where)) {
			   $this->success('导入成功！');
		   }else{
			   $this->error('导入失败！');
		   }
		}else{
		   $lists = $this->classModel->getClassTableTempletByClassId($id);
		   $this->assign('lists',$lists);
		   $this->assign('id',$id);
		   $this->assign('empty','<td colspan="8" align="center">没有数据</td>');
		   $this->display(__FUNCTION__);
		}
		
	}
	
}
