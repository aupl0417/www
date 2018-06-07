<?php

namespace Common\Model;
use Think\Model;
use Think\Page;

class CourseResourceFileModel extends Model {

	protected $_validate = array(
		/* array('cr_name','require','课件名称必须填写'), //默认情况下用正则进行验证
	    array('cr_name', '', '课件名称已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
	    array('cr_name', '1,50', '课件名称不能超过50个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
	    array('cr_description', 'require', '简介不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
	    array('cr_courseId','require','请选择课程'),
	    array('cr_type','require','请选择资源类型'),
	    array('co_isPublic','require','请选择是否共享'), */
	);
	
	protected $_auto = array(
	    /* array('cr_userId', getUid, self::MODEL_INSERT, 'callback'),
	    array('cr_userName', getUserNameByUid, self::MODEL_INSERT, 'callback'),
	    array('cr_createTime', getTime, self::MODEL_INSERT, 'callback'),
	    array('cr_updateTime', getTime, self::MODEL_BOTH, 'callback'), */
	);
	
	public function getTime(){
	    return date('Y-m-d H:i:s', time());
	}
	
	public function getUid(){
	    return UID;
	}
	
	/* 获取课程资源文件数据
	 * @param $id 课程id
	 * @param $fields type : array/string/bool 默认为true:指查询所有字段
	 * return array
	 * */
	public function getCourseResourceFileById($id, $fields = true){
	    return $data = $this->field($fields)
	                   ->join("LEFT JOIN __COURSE_RESOURCE__ on cr_id=crd_resourceId")
	                   ->where(array('crd_id'=>$id))
	                   ->find();
	}
	
	/*
	 * 获取课件资源文件列表（默认取用户自己发布的资源文件和其他教师共享的资源文件）
	 * @param $fields type : array/string/bool 默认为true:指查询所有字段
	 * @param $where 查询条件   默认查询用户自己发布的资源文件和其他教师共享的资源文件
	 * @param $pageSize 默认每页10条数据
	 * return array
	 * */
	public function getCourseResourceFileData($fields= true, $where = '', $pageSize=10){
	    $where = !empty($where) ? $where : 'cr_userId='.UID.' or (cr_userId<>'.UID.' and cr_isPublic=1)';
	    $count = $this->count();
	    $page = new Page($count, $pageSize);
	    $limit = $page->firstRow . ',' . $page->listRows;
	    $result = $this->field($fields)
        	    ->join("LEFT JOIN __COURSE_RESOURCE__ on cr_id=crd_resourceId")
                ->where($where)
                ->limit($limit)
                ->order('cr_createTime desc')
                ->select();
	    
	    return array('result'=>$result, 'show'=>$page->show());
	}
	
	
}