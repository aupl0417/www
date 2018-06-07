<?php

namespace Admin\Model;
use Think\Model;
use Think\Page;

class CourseModel extends Model {

	protected $_validate = array(
		array('co_name','require','课程名称必须填写'), //默认情况下用正则进行验证
	    array('co_name', '', '课程名称已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
	    array('co_name', '1,50', '课程名称不能超过50个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
	    array('co_description', 'require', '描述不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
	    array('co_content','require','课程内容必须填写'),
	    array('co_score','require','课程学分必须填写'),
	    array('co_score','checkScore','课程学分必须为数字', self::EXISTS_VALIDATE , 'callback', self::MODEL_UPDATE),
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
	 * @param $fields 查询字段
	 * return array
	 * */
	public function getCourseDataById($id, $fields = '*'){
	    return $data = $this->field($fields)->where(array('co_id'=>$id))->find();
	}
	
	/* 删除课程
	 * @param $id 课程id
	 * return true/false
	 * */
	public function delCourse($id){
	    return $this->where(array('co_id'=>$id))->delete();
	}
	
	public function getCourseData($pageSize=10){
	    $count = $this->count();
	    $page = new Page($count, $pageSize);
	    $limit = $page->firstRow . ',' . $page->listRows;
	    $result = $this->limit($limit)->order('co_createTime desc')->select();
	    $show = $page->show();
	    return array('result'=>$result, 'show'=>$show);
	}
}