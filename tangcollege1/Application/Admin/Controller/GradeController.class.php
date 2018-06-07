<?php

namespace Admin\Controller;
use Common\Model\GradeModel;
use Common\Model\BranchModel;

class GradeController extends AdminController {
   protected $GradeModel;
   public function __construct() {
	   $this->GradeModel =  new GradeModel();	
       parent::__construct();
    }
    public function index(){
        $pageSize = C('SHOW_PAGE_SIZE');
		$count = $this->GradeModel->infoCountCache();
		if($pageSize < $count) {
		   $Page = new \Think\Page($count,$pageSize);
           $lists = $this->GradeModel->listsCache('','*','gr_id DESC',$Page->firstRow.','.$Page->listRows);
		   $page      = $Page->show();
		   $this->assign('page',$page);
		}else{
		   $lists = $this->GradeModel->listsCache('');
		} 
		$this->assign('lists',$lists);
        $this->display(__FUNCTION__);
    }
	
	public function getGradeListByAjax($page=1) {
		 $this->lists($page,'',false);
		 $html = $this->fetch('getGradeListByAjax');
		 echo $html;
	}
    public function lists($page=1,$isAjaxWay=true) {
		if(!($page > 0)) 
		    $page = 1;
		 $pageSize = C('SHOW_PAGE_SIZE');
		 $total = $this->GradeModel->infoCountCache('');
		 $totalPage = ceil($total/$pageSize);
		 $page = max(min($page,$total),0);
		 $limit = ($page - 1)* $pageSize .',' . $pageSize;
		 $lists = $this->GradeModel->listsCache('','*','gr_id DESC',$limit);
		 if($isAjaxWay) {
			$returnData = compact("pageSize","totalPage","page","lists"); 
			$this->ajaxReturn($returnData,'JSON'); 
		 }else {
		    $this->assign('page',$page);
		    $this->assign('totalPage',$totalPage);
		    $this->assign('GradeList',$lists);
		 }
		
	}
	public function del($id='') {
		if(empty($id)) {
		   $this->error('ID不能为空');
		   exit();
		}
		if($this->GradeModel->delInfoCacheClean($id))
		  $this->success('删除成功！');
		else{
		  $this->error('删除失败！');  
		}
	}
	public function getAddGradeTempletByAjax($classId='') {
		if(IS_POST){
		   if($this->GradeModel->addInfoCacheClean()) {
			 $this->success('添加成功！');  
		   }else{
			 $errorMsg = empty($this->GradeModel->getError()) ? '添加失败' : $this->GradeModel->getError();
		     $this->error($errorMsg);  
		   }
		}else{
		   $this->display(__FUNCTION__);
		}
	}
	public function getEditGradeTempletByAjax($id='') {
		if(empty($id)) {
			 $this->error('ID不能为空');
		 }
		if(IS_POST){
		   if($this->GradeModel->editInfoCacheClean($id)) {
			 $this->success('编辑成功！');  
		   }else{
			 $errorMsg = empty($this->GradeModel->getError()) ? '编辑失败' : $this->GradeModel->getError();
		     $this->error($errorMsg);  
		   }
		}else{
		   $info = $this->GradeModel->infoCache($id);
		   $this->assign('info',$info);
		   $this->display(__FUNCTION__);
		}
		
	}
	public function lookClassTableTempletByAjax($id='') {
		if(empty($id)) {
			 $this->error('ID不能为空！'); 
		}
		$lists = $this->GradeModel->getClassTableTemplentList($id);
		$this->assign('lists',$lists);
		$this->assign('id',$id);
		$this->assign('empty','<tr><td colspan="6" align="center">没有数据</td></tr>');
		$this->display(__FUNCTION__);
	}
	
	public function sortClassTableTempletByAjax($id='',$ctt_sort='') {
		if(empty($id)) {
			 $this->error('ID不能为空！'); 
		}
		if(!($ctt_sort > 0)) {
			 $this->error('排序值不对！'); 
		}
		if($this->GradeModel->editClassTableTemplent($id,['ctt_sort'=>$ctt_sort])) {
			  $this->success('修改成功！'); 
		}else{
			  $this->error('修改失败！');  
		}
	}
	
	
	
	public function editClassTableTempletByAjax($id='') {
		if(empty($id)) {
			 $this->error('ID不能为空！'); 
		}
		if(IS_POST) {
		   $data = I('post.');
		   if(empty($data['ctt_teacherId'])) {
			  $this->error('请选择培训老师！'); 
		   }
		   if(empty($data['ctt_courseId'])) {
			  $this->error('请选择课程！'); 
		   }
		   if($this->GradeModel->editClassTableTemplent($id,$data)) {
			  $this->success('保存成功！'); 
		   }else{
			  $this->error('保存失败！');  
		   }
		}else{
		   $info = $this->GradeModel->getClassTableTemplentInfo($id);
		   if(empty($info)) {
			 $this->error('信息不存在！'); 	
		   }
		   $gradeInfo = $this->GradeModel->infoCache($info['ctt_gradeId'],'gr_id,gr_name');
		   $this->assign('info',$info);
		   $this->assign('gradeCourses',$gradeInfo['courseList']);
		   $this->display(__FUNCTION__);
		}
	}
	
	public function delClassTableTempletByAjax($id='') {
		if(empty($id)) {
			 $this->error('ID不能为空！'); 
		}
		if($this->GradeModel->delClassTableTemplent($id)) {
			$this->success('删除成功！');
		}else{
			$this->error('删除失败！');
		}
		 
		
	}
	
	public function getAddCourseTableTempletByAjax($id='') {
		if(empty($id)) {
			 $this->error('ID不能为空！'); 
		}
		if(IS_POST) {
		   $data = I('post.');
		   if(empty($data['ctt_teacherId'])) {
			  $this->error('请选择序号为'.$data['sort'].'项的培训老师！'); 
		   }
		   if(empty($data['ctt_courseId'])) {
			  $this->error('请选择序号为'.$data['sort'].'项的课程！'); 
		   }
		   $data['ctt_createTime'] = $this->GradeModel->getTime();
		   if($this->GradeModel->addClassTableTemplent($data)) {
			  $this->success('请选择序号为'.$data['sort'].'项的添加成功！'); 
		   }else{
			  $this->error('请选择序号为'.$data['sort'].'项的添加失败！');  
		   }
		}else{
		   $info = $this->GradeModel->infoCache($id,'gr_id,gr_name');
		   $this->assign('info',$info);
		   $this->display(__FUNCTION__);
		}
	}
}
