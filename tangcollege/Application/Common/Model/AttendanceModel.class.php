<?php

namespace Common\Model;
use Think\Model;

class AttendanceModel extends Model {

	protected $_validate = array(
// 		array('co_name','require','课程名称必须填写'), //默认情况下用正则进行验证
// 	    array('co_name', '', '课程名称已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
// 	    array('co_name', '1,50', '课程名称不能超过50个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
// 	    array('co_description', 'require', '描述不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
// 	    array('co_content','require','课程内容必须填写'),
// 	    array('co_studyDirectionId','require','请选择课程分类'),
// 	    array('co_branchId','require','请选择分院'),
// 	    array('co_score','require','课程学分必须填写'),
// 	    array('co_score','checkScore','课程学分必须为数字', self::EXISTS_VALIDATE , 'callback', self::MODEL_BOTH),
	);
	
	protected $_auto = array(
// 	    array('co_createTime', getTime, self::MODEL_INSERT, 'callback'),
// 	    array('co_updateTime', getTime, self::MODEL_BOTH, 'callback'),
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
	
	
	/* 删除考勤
	 * @param $id 考勤id集
	 * @param $branchIds 所在分院及下属分院id集
	 * return true/false
	 * */
	public function delInfo($id, $branchIds){
	    $where['att_id'] = array('in', $id);
	    $attendanceBranchId = $this->where($where)->getField('att_branchId', true);
	    $attendanceBranchId = array_unique($attendanceBranchId);
	    $diff = array_diff($attendanceBranchId, $branchIds);
	    
	    if(!empty($diff)){
	        return 2;
	    }
	    
	    return $this->where($where)->delete();
	}
	
	/*
	 * 获取考勤列表
	 * @param $field type : array/string
	 * @param $where 查询条件 type : array/string
	 * @param $limit type : string
	 * return array
	 * */
    public function getAttendanceData($fields= '*', $where = '', $limit='', $order='att_branchId desc, att_createTime desc'){
	    $result = $this->field($fields)
	               ->join('LEFT JOIN __UCENTER_MEMBER__ on att_userId=id')
	               ->join("LEFT JOIN __CLASS_TABLE__ on cta_id=att_classTableId")
	               ->join('LEFT JOIN __CLASS__ on cta_classId=cl_id')
	               ->join("LEFT JOIN __COURSE__ on cta_courseId=co_id")
	               ->join("LEFT JOIN __BRANCH__ on br_id=att_branchId")
	               ->join("__TRAININGSITE__ f on cta_trainingsiteId = tra_id")
	               ->where($where)
	               ->limit($limit)
	               ->order($order)
	               ->select();
	    
	    return $result;
	}
	
}