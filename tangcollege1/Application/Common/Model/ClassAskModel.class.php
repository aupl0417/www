<?php

namespace Common\Model;
use Think\Model;
use Think\Page;

class ClassAskModel extends Model {
   
	
	protected $_validate = array(
	    array('ca_classTableId','require','请选择课时'),
	    array('ca_description','require','请填写问答内容'),
	    array('ca_teacherId','require','请选择老师'),
	);
	
	protected $_auto = array(
	    array('ca_createTime', getTime, self::MODEL_INSERT, 'callback'),
	    array('ca_updateTime', getTime, self::MODEL_BOTH, 'callback'),
	    array('ca_userId', getUid, self::MODEL_INSERT, 'callback'),
	);
	
	public function getTime(){
	    return date('Y-m-d H:i:s', time());
	}
	
	public function getUid(){
	    return UID;
	}
	
	
	/*
	 * 获取指定id的答疑
	 * @param $id 
	 * @param $classTableId 课时id
	 * @param $teacherId 提疑问所针对的老师的id
	 * @param $field type : array/string/bool 默认为true:指查询所有字段,查询效果比用*好
	 * $field = array('ca_id', 'ca_parentId', 'ca_description', 'ca_userId', 'ca_teacherId', 'ca_createTime', 'ca_updateTime', 'username as teacherName');
	 * return array
	 * */
	public function getClassAskById($id, $classTableId, $teacherId, $field = true){
	    $where = array('ca_id'=>$id, 'ca_classTableId'=>$classTableId, 'ca_teacherId'=>$teacherId);
	    $data = $this->field($field)
	           ->join('LEFT JOIN __UCENTER_MEMBER__ on __CLASS_ASK__.ca_userId=__UCENTER_MEMBER__.id')
	           ->join('LEFT JOIN __CLASS_TABLE__ on __CLASS_ASK__.ca_classTableId=__CLASS_TABLE__.cta_id')
	           ->where($where)->find();
	    $data['comment'] = $this->getClassAskList($data['ca_parentId'], array());
	    return $data;
	}
	
	
	/*
	 * 递归获取提问答疑
	 * @param $pid type ： int 答疑parentId
	 * @param $field type : array/string/bool 默认为true:指查询所有字段,查询效果比用*好
	 * $field = array('ca_id', 'ca_parentId', 'ca_description', 'ca_userId', 'ca_teacherId', 'ca_createTime', 'ca_updateTime');
	 * @info array 存储结果的数组
	 * return array
	 * */
	public function getClassAskList($pid = 0, $field = true, $info= array()){
	    $where = array('ca_parentId'=>$pid);
	    $data = $this->where($where)->field($field)->select();
	    if($data){
	        $res = $data;
	        foreach ($data as $key=>$val){
	            $res[$key]['child'] = $this->getClassAskList($val['ca_id'], $info);
	        }
	        $info[] = $res;
	    }
	    return $info;
	}
	
	/*
	 * 获取同一课时，同一教师所对应的父id为0的答疑
	 * param $classTableId type : int 课时id
	 * param $teacherId type:int 提疑问所针对的老师的id
	 * param $field type:array/string/bool 默认为true:指查询所有字段,查询效果比用*好
	 * $field = array('ca_id', 'ca_parentId', 'ca_description', 'ca_userId', 'ca_teacherId', 'ca_createTime', 'ca_updateTime', 'username as teacherName');
	 * return array
	 * */
	public function getParentAskList($classTableId, $teacherId, $field = true, $pageSize = 10){
	    $where = array('ca_classTableId'=>$classTableId, 'ca_teacherId'=>$teacherId, 'ca_parentId'=>0);
	    $count = $this->where($where)->count();
	    $page = new Page($count, $pageSize);
	    $limit = $page->firstRow . ',' . $page->listRows;
	    $result = $this->field($field)
	            ->join('LEFT JOIN __UCENTER_MEMBER__ on __CLASS_ASK__.ca_userId=__UCENTER_MEMBER__.id')
	            ->join('LEFT JOIN __CLASS_TABLE__ on __CLASS_ASK__.ca_classTableId=__CLASS_TABLE__.cta_id')
	            ->where($where)
	            ->order('ca_createTime desc')
	            ->select();
	    $show = $page->show();
	    return array('result'=>$result, 'show'=>$show);
	}
	
	
}