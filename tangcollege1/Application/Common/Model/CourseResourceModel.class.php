<?php

namespace Common\Model;
use Think\Model;
use Common\Logic\AutoCache;

class CourseResourceModel extends Model {
    use AutoCache;
    
	protected $_validate = array(
		array('cr_name','require','课件名称必须填写'), //默认情况下用正则进行验证
	    array('cr_name', '', '课件名称已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
	    array('cr_name', '1,50', '课件名称不能超过50个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
	    array('cr_description', 'require', '简介不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
	    array('cr_courseId','require','请选择课程'),
	    array('cr_type','require','请选择资源类型'),
	    array('co_isPublic','require','请选择是否共享'),
	);
	
	protected $_auto = array(
	    array('cr_userId', getUid, self::MODEL_INSERT, 'callback'),
	    array('cr_userName', getUserNameByUid, self::MODEL_INSERT, 'callback'),
	    array('cr_createTime', getTime, self::MODEL_INSERT, 'callback'),
	    array('cr_updateTime', getTime, self::MODEL_BOTH, 'callback'),
	);
	
	public function getTime(){
	    return date('Y-m-d H:i:s', time());
	}
	
	public function getUid(){
	    return UID;
	}
	
	public function getUserNameByUid(){
	    return M('UcenterMember')->getFieldById(UID, 'username');
	}
	
	/* 获取课件资源数据(关联 course_resource_file表)
	 * @param $where 课件资源查询条件，一般用cr_id
	 * @param $fields type : array/string/bool 默认为true:指查询所有字段
	 * return array
	 * */
	public function getCourseResourceFile($where, $fields = true){
	    return $data = $this->field($fields)
	                   ->join("LEFT JOIN __COURSE_RESOURCE_FILE__ on cr_id=crd_resourceId")
	                   ->join("LEFT JOIN __COURSE__ on cr_courseId=co_id")
	                   ->where($where)
	                   ->limit(1)
	                   ->find();
	}
	
    /* 获取课件资源数据(关联 course_resource_page表)
	 * @param $where 课件资源查询条件，一般用cr_id
	 * @param $fields type : array/string/bool 默认为true:指查询所有字段
	 * return array
	 * */
	public function getCourseResourcePage($where, $fields = true){
	    return $data = $this->field($fields)
	                   ->join("LEFT JOIN __COURSE_RESOURCE_PAGE__ on cr_id=crp_resourceId")
	                   ->join("LEFT JOIN __COURSE__ on cr_courseId=co_id")
	                   ->where($where)
	                   ->limit(1)
	                   ->find();
	}
	
	/*
	 * 获取课件资源列表（默认取用户自己发布的资源和其他教师共享的资源）
	 * @param $fields type : array/string/bool 默认为true:指查询主表所有字段
	 * @param $where 查询条件   默认查询用户自己发布的资源和其他教师共享的资源
	 * @param $pageSize 默认每页10条数据
	 * return array
	 * */
	public function getCourseResourceData($where = '', $limit='', $fields= true,  $order = 'cr_createTime desc'){
	    $where = !empty($where) ? $where : 'cr_userId='.UID.' or (cr_userId<>'.UID.' and cr_isPublic=1)';
	    $result = $this->field($fields)
        	    ->join("LEFT JOIN __BRANCH__ ON __COURSE_RESOURCE__.cr_branchId=__BRANCH__.br_id")
        	    ->join("LEFT JOIN __COURSE__ ON cr_courseId=co_id")
        	    ->join("LEFT JOIN __STUDY_DIRECTION__ on co_studyDirectionId=sd_id")
        	    ->where($where)
	            ->limit($limit)->order($order)
	            ->select();
	    
	    return $result;
	}
	
	/*
	 * 获取当前用户发布的课件资源
	 * @param $field type : array/string/bool 默认为true:指查询所有字段
	 * @param $where type:array/string 查询条件   默认查询用户自己发布的资源
	 * return array
	 * */
	public function listInfo($field = true, $where = ''){
	    return $this->field($field)
	                   ->where($where)
	                   ->order('cr_createTime desc')
	                   ->select();
	}
	
	//编辑课件资源
	public function editInfo($info, $fieldName, $model){
	    $data = $this->create();
	    if(!$data){
	        return false;
	    }
	    
	    $courseInfo = M('Course')->where(array('co_id'=>$data['cr_courseId']))->field(array('co_name', 'co_branchId'))->find();
	    $data['cr_courseName'] = $courseInfo['co_name'];
	    $data['cr_branchId'] = $courseInfo['co_branchId'];
	    
	    M()->startTrans();//开启事务
	    $res = $this->save($data);
	    $resSave = M($model)->where(array($fieldName=>$data['cr_id']))->save($info);
	    
	    if($res === false || $resSave === false){
	        M()->rollback();//事务回滚
	        return false;
	    }
	    //记录行为
	    action_log('update_Course_Resource', 'Course_Resource', $data['cr_id'], UID);
	    M()->commit();//事务提交
	    return true;
	}
	
	public function addInfo($info, $fieldName, $model, $type){
	    $data = $this->create();
	    if(!$data){
	        return false;
	    }
	    
	    M()->startTrans();
	    //课程对应有课程名和所在分院的id
	    $courseInfo = M('Course')->where(array('co_id'=>$data['cr_courseId']))->field(array('co_name', 'co_branchId'))->find();
	    $data['cr_courseName'] = $courseInfo['co_name'];
	    $data['cr_branchId'] = $courseInfo['co_branchId'];
	    $data['cr_type'] = $type;
	    $id = $this->add($data);//保存到主表
	    
	    //保存到资源表附表
	    $info[$fieldName] = $id;
	    $res = M($model)->add($info);
	    if(!$id || !$res){
	        M()->rollback();
	        return false;
	    }
	    M()->commit();
	    return  $id;
	}
	
	/*
	 * 组合文件路径、文件类型及后缀到一个数组
	 * $fileUrlArr type : array 文件路径数据集
	 * return array
	 * */
	public function fileUrlHandle($fileUrlArr, $type = 'file'){
	    $len = count($fileUrlArr);
	    $urlArray = array();
	    for($i=0; $i < $len; $i++){
	        if(empty($fileUrlArr[$i])){
	            continue;
	        }
	        if($type == 'file'){
	            $urlArray[$i]['url'] = $fileUrlArr[$i];
	            $temp = explode('.', $fileUrlArr[$i]);
	            $urlArray[$i]['ext'] = end($temp);
	            if(in_array($urlArray[$i]['ext'], array('jpg','png','gif','jpeg'))){
	                $urlArray[$i]['type'] = 'image';
	            }else {
	                $urlArray[$i]['type'] = $urlArray[$i]['ext'];
	            }
	        }else if($type == 'url'){
	            $res = $this->regex($fileUrlArr[$i], 'url');
	            if(!$res){
	                return false;
	            }
	            $urlArray[$i]['url'] = $fileUrlArr[$i];
	        }
	    }
	    return $urlArray;
	}
	
	//删除课程资源
	public function delInfo($ids){
	    M()->startTrans();
	    foreach($ids as $val){
	        $type = $this->getFieldByCrId($val, 'cr_type');
	        $res = $this->delete($val);
	        //删除附属表中对应的数据
	        if($type == 0 || $type == 1){
	            $modelName = 'course_resource_file';
	            $resourceIdName = 'crd_resourceId';
	        }else if($type == 3) {
	            $modelName = 'course_resource_page';
	            $resourceIdName = 'crp_resourceId';
	        }
	        $result = M($modelName)->where(array($resourceIdName=>$val))->delete();
	        if(!$res || !$result){
	            M()->rollback();
	            return false;
	        }
	    }
	    M()->commit();
	    return true;
	}
	
	//分享
	public function shareHandle($id, $act){
	    $data = array('cr_id'=>$id, 'cr_isPublic'=>$act);
	    return $this->save($data);
	}
	
}