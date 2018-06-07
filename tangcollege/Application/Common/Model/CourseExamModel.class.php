<?php

namespace Common\Model;
use Think\Model;
use Common\Logic\AutoCache;

class CourseExamModel extends Model {
    use AutoCache;
    
	protected $_validate = array(
		array('cre_name','require','题目名称必须填写'), //默认情况下用正则进行验证
	    array('cre_name', '', '题目名称已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
	    array('cre_name', '1,255', '题目名称不能超过255个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
	    array('cre_a','require','请填写A选项的内容'),
	    array('cre_a', '1,255', 'A选项不能超过255个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
	    array('cre_b','require','请填写B选项的内容'),
	    array('cre_b', '1,255', 'B选项不能超过255个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
	    array('cre_c','require','请填写C选项的内容'),
	    array('cre_c', '1,255', 'C选项不能超过255个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
	    array('cre_d', '1,255', 'D选项不能超过255个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
	    array('cre_type','require','请选择题目类型'),
// 	    array('cre_answer','require','请填写正确答案'),
	    array('cre_courseId','require','请选择课程'),
	);
	
	protected $_auto = array(
	    array('cre_name', 'htmlspecialchars', 3, 'function'),
	    array('cre_name', trimContent, 3, 'callback'),
	    array('cre_userId', getUid, self::MODEL_INSERT, 'callback'),
	);
	
	public function getUid(){
	    return UID;
	}
	
	/*
	 * 过滤掉括号及括号内的内容
	 * */
	public function trimContent($data){
        $data = preg_replace_callback(
            '/（.*?）/',
            function($match){
                return str_replace($match[0], '', $data);
            }, $data);
        
	    return $data;
	}
	
	/* 获取课程数据
	 * @param $id 课程id
	 * @param $fields type : array/string/bool 默认为true : 指查询所有字段，比用*性能好
	 * return array
	 * */
	public function getCourseExamById($id, $fields = true){
	    return $data = $this->field($fields)
	                   ->join("LEFT JOIN __UCENTER_MEMBER__ on cre_userId=id")
	                   ->join("LEFT JOIN __COURSE__ on co_id=cre_courseId")
	                   ->where(array('cre_id'=>$id))
	                   ->limit(1)->find();
	}
	
	/*
	 * 获取题库中的数据（默认是取自己添加的题目和其他共享的题目）
	 * @param $fields type : array/string/bool 默认为true : 指查询所有字段，比用*性能好
	 * @param $where  type : array/string  查询条件
	 * @param $limit  type : string
	 * return array
	 * */
	public function getCourseExamData($fields= true, $where = '', $limit, $order = 'cre_id desc'){
	    $where = empty($where) ? 'cre_userId='.UID.' or (cre_userId<>'.UID.' and cre_isPublic=1)' : $where;
	    $result = $this->field($fields)
        	    ->join("LEFT JOIN __UCENTER_MEMBER__ ON __COURSE_EXAM__.cre_userId=__UCENTER_MEMBER__.id")
        	    ->where($where)
	            ->limit($limit)
	            ->order($order)
	            ->select();
	    
	    return $result;
	}
	
	//添加试题
	public function addInfo(){
	    $data = $this->create();
	    if(!$data){
	        return false;
	    }
	    if(count($data['cre_answer']) == 0){
	        return -2;
	    }
	    if($data['cre_type'] == 0 && count($data['cre_answer']) != 1){
	        return -3;//单选类型
	    }
	    $data['cre_answer'] = implode(',', $data['cre_answer']);
	    
	    return $this->add($data);
	}
	
	//编辑试题
	public function editInfo(){
	    $data = $this->create();
	    if(!$data){
	        return false;
	    }
	    if(count($data['cre_answer']) == 0){
	        return -2;
	    }
	    if($data['cre_type'] == 0 && count($data['cre_answer']) != 1){
	        return -3;//单选类型
	    }
	    $data['cre_answer'] = implode(',', $data['cre_answer']);
	    
	    return $this->save($data);
	}
	
	/* 删除试题
	 * $ids 试题ids集
	 * $branchIds 所在分院及下属分院的id集
	 * */
	public function delInfo($ids, $branchIds){
	    $where['cre_id'] = array('in', $ids);
	    $courseExamBranchIds =  $this->getCourseExamData('branchId', $where);
	    $examBranchIds = array_column($courseExamBranchIds, 'branchId');
	    
	    $diff = array_diff(array_unique($examBranchIds), $branchIds);
	    
	    if(!empty($diff)){
	        return 2;
	    }
	    
	    return $this->where($where)->delete();
	}
	
	public function shareHandle($id, $act){
	    $data = array('cre_id'=>$id, 'cre_isPublic'=>$act);
	    return $this->save($data);
	}
	
}