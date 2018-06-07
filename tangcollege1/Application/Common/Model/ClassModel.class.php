<?php

namespace Common\Model;
use Think\Model;
use Common\Logic\AutoCache;

class ClassModel extends Model {
	use AutoCache;
	protected $_validate = array(
		array('cl_gradeId', 'require','学习级别必须填写'),
		array('cl_startTime', 'require','授课开始时间必须填写'), 
		array('cl_endTime', 'require','授课结束时间必须填写'),
		array('cl_boardLodgingType', 'require','食宿必须填写'),
		array('cl_teachingType',[0,1],'授课方式必须填写',self::MUST_VALIDATE,'in'),
		array('cl_description', '0,255', '班级介绍不能超过255个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
		array('cl_description', '4,120', '班级描叙必须是4到120个字符', self::MUST_VALIDATE , 'length', self::MODEL_BOTH),
		array('cl_defaultTrainingsiteId', 'require', '默认培训地址必须填写'),
		array('cl_allowableNumber', '/^[1-9][0-9]*$/', '开班人数必须填写'),
		array('cl_cost', 'number', '报名费必须填写'),
	);
	
	
	
	public function getTime(){
	    return date('Y-m-d H:i:s', time());
	}
	protected function _before_insert(&$data,$options) {
		$data['cl_createTime'] = $this->getTime();
	}
	
	public function getClassByBranchId($branchId, $field = true){
	    return $this->field($field)->where(array('cl_branchId'=>$branchId))->select();
	}
	
	public function getStudentByClassId($classId, $field = true){
	    $data = $this->field($field)
	            ->join('LEFT JOIN __CLASS_STUDENT__ on __CLASS_STUDENT__.cs_classId=__CLASS__.cl_id')
	            ->join('LEFT JOIN __UCENTER_MEMBER__ on __CLASS_STUDENT__.cs_studentId=__UCENTER_MEMBER__.id')
	            ->where(array('cs_id'=>$classId))
	            ->select();
	    return $data;
	}

    
	public function addInfo($fields=[]) {
		return $this->create() && $this->add($fields);
	}
	
	public function editInfo($where,$fields=[]) {
		return $this->where($where)->save($fields);
	}
	
	public function lists($where=null,$field = '*',$order = 'cl_createTime DESC',$limit ='') {
		if(stripos($field,'cl_studentCount')!=false || $field == '*') {
			$extra = ",(SELECT count(*) FROM ".C('DB_PREFIX')."class_student where a.cl_id = cs_classId) as cl_studentCount";
			$field = str_replace('cl_studentCount',$extra,$field);
		}
		if(!empty($where)) {
			$where = ' where '.$where;
		}
		return $this->query("SELECT $field FROM ".C('DB_PREFIX')."class a LEFT JOIN ".C('DB_PREFIX')."branch b ON cl_branchId = br_id LEFT JOIN ".C('DB_PREFIX')."grade c ON gr_id = cl_gradeId $where ORDER BY cl_createTime DESC ".(empty($limit) ? '' : 'limit '.$limit ));
		
	}
	//通过id 获取单条记录
	public function info($id,$field = '*') {
		$where = '';
		if(is_numeric($id)) {
			$where = ' where cl_id="'.$id.'"';
		}elseif(!empty($id) && is_string($id)) {
			$where = ' where '.$id;
		}
		$result = $this->query("SELECT $field ,(SELECT count(*) FROM ".C('DB_PREFIX')."class_student where a.cl_id = cs_classId) as cl_studentCount FROM ".C('DB_PREFIX')."class a LEFT JOIN ".C('DB_PREFIX')."branch b ON cl_branchId = br_id LEFT JOIN ".C('DB_PREFIX')."grade c ON gr_id = cl_gradeId LEFT JOIN ".C('DB_PREFIX')."trainingsite on tra_id=cl_defaultTrainingsiteId $where limit 0,1");
		return empty($result) ? [] : current($result);
		
	}
	//通过班级id获得学生
	public function getStudentListsByclassId($id,$field = 'a.cl_id,a.cl_gradeId,a.cl_name,c.*',$order = 'cs_createTime DESC',$limit ='') {
		if(is_numeric($id)) {
			$where = 'cl_id="'.$id.'"';
		}else{
			$where = $id; 
		}
		return $this->alias('a')->join('__CLASS_STUDENT__ b on cs_classId = cl_id')
		       ->join('__UCENTER_MEMBER__ c on id = cs_studentId')
			   ->where($where)
			   ->field($field)
			   ->order($order)
			   ->limit($limit)
			   ->select();
		
		
		
	}
	public function infoCount($where) {
		return $this->where($where)->count();
	}
	//删除班级学生关联表 删除班级课时表 等
	public function del($where='') {
		//暂时简单删除
		return $this->where($where)->delete();
	}
	
	//获取班级关联的课程
	public function getCoursesById($id) {
	  if(is_numeric($id)) {
			$where = 'cl_id="'.$id.'"';
	  }else{
			$where = $id; 
	  }	
	  return $this->field('co_id,co_name')
		     ->join("__GRADE_COURSE__ on gc_gradeId = cl_gradeId")
			 ->join("__COURSE__ on gc_courseId = co_id")
			 ->where($where)
			 ->select();
		
		
	}
}