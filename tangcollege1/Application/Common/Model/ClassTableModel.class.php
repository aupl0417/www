<?php

namespace Common\Model;
use Think\Model;
use Common\Logic\AutoCache;
class ClassTableModel extends Model {
	use AutoCache;
	protected $_validate = array(
		array('cta_classId', 'require','班级必须选择'),
		array('cta_trainingsiteId', 'require','培训地址必须选择'), 
		array('cta_courseId', 'require','培训课程必须选择'),
		array('cta_teacherId', 'require','培训老师必须选择'),
		array('cta_startTime', 'require','上课时间必须填写'),
		array('cta_endTime', 'require','上课结束时间必须填写'),
		array('cta_description', '0,255', '描叙字符不能超过255个字符', self::MUST_VALIDATE , 'length', self::MODEL_BOTH),
	);
	
	protected $_auto = array(
	    array('cta_createTime', getTime, 1, 'callback'),
	);

	public function getTime(){
	    return date('Y-m-d H:i:s', time());
	}
	
	public function addInfo() {
		if(!$this->create())
		  return false;
		else  
		  return $this->add();
	}
	
	public function editInfo($id='') {
		if(!$this->create())
		  return false;
		else
		  return $this->where('cta_id="'.$id.'"')->save($fields);
	}
	
	public function lists($where=null,$field,$order,$limit ='') {
	  $field = empty($field) ? 'a.*,b.cl_id,b.cl_name,b.cl_gradeId,b.cl_branchId,d.id,d.username,e.co_id,e.co_name,e.co_studyDirectionId,f.tra_id,f.tra_name,f.tra_address' : $field;
	  $order = empty($order) ? 'cta_createTime DESC' : $order;
	  return $this->alias('a')
	         ->join("__CLASS__ b on cta_classId = cl_id")
	         ->join("__GRADE__ c on gr_id = cl_gradeId")
		     ->join("__UCENTER_MEMBER__ d on d.id = cta_teacherId")
			 ->join("__COURSE__ e on cta_courseId = co_id")
			 ->join("__TRAININGSITE__ f on cta_trainingsiteId = tra_id")
			 ->where($where)
			 ->field($field)
			 ->order($order)
			 ->limit($limit)
			 ->select();
	}
	//通过id 获取单条记录
	public function info($id='',$field) {
		if(is_numeric($id)) {
			$where = 'cta_id="'.$id.'"';
	    }else{
			$where = $id; 
	    }	
		$field = empty($field) ? 'a.*,b.cl_id,b.cl_name,b.cl_gradeId,b.cl_branchId,d.id,d.username,e.co_id,e.co_name,e.co_studyDirectionId,f.tra_id,f.tra_name,f.tra_address' : $field;
		return $this->alias('a')
		     ->join("__CLASS__ b on cta_classId = cl_id")
	         ->join("__GRADE__ c on gr_id = cl_gradeId")
		     ->join("__UCENTER_MEMBER__  d on d.id = cta_teacherId")
			 ->join("__COURSE__ e on cta_courseId = co_id")
			 ->join("__TRAININGSITE__ f on cta_trainingsiteId = tra_id")
			 ->where($where)
			 ->field($field)
			 ->find();
		
	}
	
	public function infoCount($where='') {
		return $this->join("__CLASS__ on cta_classId = cl_id")
		            -> where($where)->count();
	}
	
	public function del($id) {
		if(is_numeric($id)) {
			$where = 'cta_id="'.$id.'"';
	    }else{
			$where = $id; 
	    }
		return $this->where($where)->delete();
	}
	
		
		
		
	
}