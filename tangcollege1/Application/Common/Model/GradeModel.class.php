<?php

namespace Common\Model;
use Think\Model;
use Common\Model\CourseModel;
use Common\Logic\AutoCache;
class GradeModel extends Model {
	use AutoCache;
	protected $_validate = array(
		
	);
	protected function _before_insert(&$data,$options) {
		$data['gr_updateTime'] = $this->getTime();
		$data['gr_createTime'] = $this->getTime();
	}
	protected function _before_update(&$data,$options) {
		$data['gr_updateTime'] = $this->getTime();
	}
	public function getTime(){
	    return date('Y-m-d H:i:s', time());
	}
    
	public function lists($where=null,$field = '*',$order = 'gr_id DESC') {
		$lists = $this->where($where)
			        ->field($field)
			        ->order($order)
		            ->select();
		 return $lists;
	}
	
	public function info($id,$field = '*') {
		$info = $this->where('gr_id="'.$id.'"')
			        ->field($field)
		            ->find();
		$courseList = [];			
	    if(!empty($info)) {				
		   $gradeCourseModel = M('grade_course');
		   $courseList = $gradeCourseModel->join('__COURSE__ on co_id = gc_courseId')->where('gc_gradeId='.$id)->field('gc_courseId,co_name')->select();
		}
		$info['courseList'] = $courseList;
		return $info;
	}
	
	
	public function infoCount($where='') {
       
	}
	
	public function delInfo($id) {
		$where = '';
		if(is_numeric($id)) {
			$where = 'gc_gradeId="'.$id.'"';
		}else{
			$where = $id;
		}
		$this->startTrans();
		if(!$this->where('gr_id='.$id)->delete()) {
			return false;
		}
		$gradeCourseModel = M('grade_course');
		$have = $gradeCourseModel->where($where)->count(); 
		if($have > 0) {
		  if(!$gradeCourseModel->where($where)->delete()) {	
			 $this->rollback();
			 return false;
		   }
		 }
		 $this->commit();  
		 return true;    
	}
	
	
	
	public function editInfo($id='') {
		   $this->startTrans();
		   if(!$this->create())
		     return false; 
		   if(!$this->where('gr_id='.$id)->save())
		     return false;
		   $gradeCourseModel = M('grade_course');
		   $courseList = I('post.courseId');
		   $result = $gradeCourseModel->where('gc_gradeId='.$id)->field('gc_courseId')->select();
		   if(!empty($courseList)) {
			  $inserts = [];
			  $deletes = [];
			  if(!empty($result)) {
				  $saveedIds = array_column($result,'gc_courseId');
				  $diff0 = array_diff($courseList,$saveedIds);
				  if(!empty($diff0)) {
					 $inserts = $diff0;
				  }
				  $diff1 = array_diff($saveedIds,$courseList);
				  if(!empty($diff1)) {
					$deletes = $diff1;   
				  }
			  }else{
				 $inserts = $courseList;  
			  }
			  if(!empty($inserts)) {
				  $insertData = [];
				  foreach($inserts as $val) {
				     $insertData[] = ['gc_gradeId'=>$id,'gc_courseId'=>$val];  
			      }
				  if(!$gradeCourseModel->addAll($insertData)) {
			        $this->rollback();
			        return false; 
		          }
			  }
			  if(!empty($deletes)) {
				  $deleteData = [];
				  foreach($deletes as $val) {
				     $deleteData[] =   '(gc_gradeId='.$id .' and gc_courseId='.$val.')'; 
			      }
				  $where = implode(' or ',$deleteData);
				  if(!$gradeCourseModel->where($where)->delete()) {
					 $this->rollback();
			         return false; 
				  }
			  }
		   }elseif(!empty($result)) {
			   if(!$gradeCourseModel->where('gc_gradeId='.$id)->delete()) {
					 $this->rollback();
			         return false; 
			   }
		   }
		   $this->commit();  
		   return true; 
	}
	
	public function addClassTableTemplent($data) {
		$classTableTemplentModel = M('class_table_templet');
		return $classTableTemplentModel->add($data);
	}
	
	public function editClassTableTemplent($id,$data) {
		$classTableTemplentModel = M('class_table_templet');
		return $classTableTemplentModel->where('ctt_id='.$id)->save($data);
	}
	
	public function delClassTableTemplent($id) {
		$classTableTemplentModel = M('class_table_templet');
		return $classTableTemplentModel->where('ctt_id='.$id)->delete();
	}
	
	public function getClassTableTemplentList($gradeId) {
		$classTableTemplentModel = M('class_table_templet');
		return $classTableTemplentModel->alias('a')
		       ->join('__UCENTER_MEMBER__ b on id=ctt_teacherId')
		       ->join('__TRAININGSITE__ c on tra_id=ctt_trainingsiteId')
			   ->join('__COURSE__ d on co_id = ctt_courseId')
		       ->field('a.*,b.username,c.tra_name,d.co_name')->where('ctt_gradeId="'.$gradeId.'"')->order('ctt_sort desc,ctt_id asc')->select();
	}
	
	public function getClassTableTemplentInfo($id) {
		$classTableTemplentModel = M('class_table_templet');
		return $classTableTemplentModel->alias('a')
		       ->join('__UCENTER_MEMBER__ b on id=ctt_teacherId')
		       ->join('__TRAININGSITE__ c on tra_id=ctt_trainingsiteId')
			   ->join('__COURSE__ d on co_id = ctt_courseId')
		       ->field('a.*,b.username,c.tra_name,d.co_name')->where('ctt_id="'.$id.'"')->find();
	}
	
	
	public function addInfo() {
		   $this->startTrans();
		   if(!$this->create())
		     return false; 
		   $id = $this->add();
		   if(!$id)
		     return false;
		   $gradeCourseModel = M('grade_course');
		   $courseList = I('post.courseId');
		   if(!empty($courseList)) {
			  $inserts = []; 
			  foreach($courseList as $val) {
				  $inserts[] = ['gc_gradeId'=>$id,'gc_courseId'=>$val,'gc_createTime'=>$this->getTime()];  
			  }
		      if(!$gradeCourseModel->addAll($inserts)) {
			    $this->rollback();
			    return false; 
		      }
		   }
		   $this->commit();  
		   return true; 
	}	 
	
	
}