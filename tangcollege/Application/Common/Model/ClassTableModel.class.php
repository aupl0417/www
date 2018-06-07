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
		else  { 
		   return $this->add();
		}
	}
	//导入课时模板里面的数据
	public function addInfoFromClassTableTemplet($classId,$where) {
		$templetData = M('class_table_templet')->field('ctt_sort as cta_sort,ctt_description as cta_description,ctt_teacherId as cta_teacherId,ctt_courseId as cta_courseId,ctt_trainingsiteId as cta_trainingsiteId,ctt_createTime as cta_createTime')->where($where)->select();
		if(!empty($templetData)) {
			array_walk($templetData,function(&$value) use ($classId){
				$value['cta_classId'] = $classId;
			});
			return $this->addAll($templetData);
		}else{
			return false;
		}
		
	}
	
	public function editInfo($id,$data) {
		if(!$this->create($data)){
		  return false;
	    }else {	
		  return $this->where('cta_id="'.$id.'"')->save($data);	
		}  
	}
	
	public function lists($where=null,$field,$order,$limit ='') {
	  $field = empty($field) ? 'a.*,b.cl_id,b.cl_name,b.cl_gradeId,b.cl_branchId,d.id,d.username,gr_name,br_name,e.co_id,e.co_name,e.co_studyDirectionId,f.tra_id,f.tra_name,f.tra_address' : $field;
	  $order = empty($order) ? 'cta_sort DESC,cta_id DESC' : $order;
	  return $this->alias('a')
	         ->join("__CLASS__ b on cta_classId = cl_id")
	         ->join("__GRADE__ c on gr_id = cl_gradeId")
		     ->join("__UCENTER_MEMBER__ d on d.id = cta_teacherId")
			 ->join("__COURSE__ e on cta_courseId = co_id")
			 ->join("__TRAININGSITE__ f on cta_trainingsiteId = tra_id")
			 ->join("__BRANCH__ g on cl_branchId = br_id")
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
		$field = empty($field) ? 'a.*,b.cl_id,b.cl_name,b.cl_gradeId,b.cl_branchId,d.id,d.username,br_name,e.co_id,e.co_name,e.co_studyDirectionId,f.tra_id,f.tra_name,f.tra_address' : $field;
		return $this->alias('a')
		     ->join("__CLASS__ b on cta_classId = cl_id")
	         ->join("__GRADE__ c on gr_id = cl_gradeId")
		     ->join("__UCENTER_MEMBER__  d on d.id = cta_teacherId")
			 ->join("__COURSE__ e on cta_courseId = co_id")
			 ->join("__TRAININGSITE__ f on cta_trainingsiteId = tra_id")
			 ->join("__BRANCH__ g on cl_branchId = br_id")
			 ->where($where)
			 ->field($field)
			 ->find();
		
	}
	
	public function infoCount($where='') {
		return $this->alias('a')
	         ->join("__CLASS__ b on cta_classId = cl_id")
	         ->join("__GRADE__ c on gr_id = cl_gradeId")
		     ->join("__UCENTER_MEMBER__ d on d.id = cta_teacherId")
			 ->join("__COURSE__ e on cta_courseId = co_id")
			 ->join("__TRAININGSITE__ f on cta_trainingsiteId = tra_id")
			 ->join("__BRANCH__ g on cl_branchId = br_id")
			 ->where($where)->count();
	}
	
	public function del($id) {
		if(is_numeric($id)) {
			$where = 'cta_id="'.$id.'"';
	    }else{
			$where = $id; 
	    }
		return $this->where($where)->delete();
	}
	
    public function getList($where, $field = true, $order='cta_startTime asc', $limit=''){
        return $this->field($field)
                    ->join('LEFT JOIN __COURSE__ on co_id=cta_courseId')
                    ->join('LEFT JOIN __UCENTER_MEMBER__ on cta_teacherId=id')
                    ->join('LEFT JOIN __TRAININGSITE__ on cta_trainingsiteId=tra_id')
                    ->join("LEFT JOIN __CLASS__ on cl_id=cta_classId")
                    ->order($order)
                    ->limit($limit)
                    ->where($where)
                    ->select();
    }
		
		
	
}