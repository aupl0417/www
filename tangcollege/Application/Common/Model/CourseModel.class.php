<?php

namespace Common\Model;
use Think\Model;
use Common\Logic\AutoCache;
class CourseModel extends Model {
    use AutoCache;
	protected $_validate = array(
		array('co_name','require','课程名称必须填写'), //默认情况下用正则进行验证
	    array('co_name', '', '课程名称已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
	    array('co_name', '1,50', '课程名称不能超过50个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
	    array('co_description', 'require', '描述不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
	    array('co_content','require','课程内容必须填写'),
	    array('co_studyDirectionId','require','请选择课程分类'),
	    array('co_branchId','require','请选择分院'),
	    array('co_score','require','课程学分必须填写'),
	    array('co_score','checkScore','课程学分必须为数字', self::EXISTS_VALIDATE , 'callback', self::MODEL_BOTH),
	);
	
	protected $_auto = array(
	    array('co_createTime', getTime, self::MODEL_INSERT, 'callback'),
	    array('co_updateTime', getTime, self::MODEL_BOTH, 'callback'),
	);
	
	public function getTime(){
	    return date('Y-m-d H:i:s', time());
	}

	public function checkScore($data){
	    if(!is_numeric($data)){
	        return false;
	    }
	    return true;
	}
	
	/* 获取课程数据
	 * @param $id 课程id
	 * @param $fields type : array/string/bool 默认为true : 指查询所有字段，比用*性能好
	 * return array
	 * */
	public function getCourseDataById($id, $fields = true){
	    return $data = $this->field($fields)
	                   ->join("LEFT JOIN __BRANCH__ ON co_branchId=br_id")
	                   ->join("LEFT JOIN __STUDY_DIRECTION__ ON sd_id=co_studyDirectionId")
	                   ->where(array('co_id'=>$id))
	                   ->limit(1)
	                   ->find();
	}
	
	//添加课程
	public function addInfo(){
	    $data = $this->create();
	    if(!$data){
	        return false;
	    }
	    $data['co_logo'] = $data['co_logo'][0];
	    return $this->add($data);
	}
	
	//编辑课程
	public function editInfo(){
	    $data = $this->create();
	    if(!$data){
	        return false;
	    }
	    $data['co_logo'] = $data['co_logo'][0];
	    return $this->save($data);
	}
	
	/* 删除课程
	 * @param $id 课程id
	 * return true/false
	 * */
	public function delCourse($id, $branchIds){
	    $where['co_id'] = array('in', $id);
	    $courseBranchIds = $this->where($where)->getField('co_branchId', true);
// 	    return $courseBranchIds;
	    $courseBranchIds = array_unique($courseBranchIds);
	    $diff = array_diff($courseBranchIds, $branchIds);
	    
	    if(!empty($diff)){
	        return 2;
	    }
	    
	    return $this->where($where)->delete();
	}
	
	/*
	 * 获取课程列表
	 * @param $field type : array/string/bool 默认为true:指查询所有字段
	 * @param $where 查询条件 type : array/string
	 * @param $pageSize type : int 默认每页10条数据
	 * return array
	 * */
	public function getCourseData($fields= '*', $where = '', $limit, $order = 'co_createTime desc'){
	    $result = $this->field($fields)
	               ->join('LEFT JOIN __STUDY_DIRECTION__ on sd_id=co_studyDirectionId')
	               ->join("LEFT JOIN __BRANCH__ on br_id=co_branchId")
	               ->where($where)
	               ->limit($limit)
	               ->order($order)
	               ->select();
	    
	    return $result;
	}
	
	
	public function getCourseList($field=true, $where = ''){
	    return $result = $this->where($where)
	                   ->field($field)
	                   ->select();
	}
	
	public function lists($where=null,$field = '*',$order = 'co_createTime DESC',$limit ='') {
		return   $this->join('__STUDY_DIRECTION__ on sd_id = co_studyDirectionId')
		               ->where($where)
	                   ->field($field)
					   ->order($order)
					   ->limit($limit)
	                   ->select();
		
	}
}