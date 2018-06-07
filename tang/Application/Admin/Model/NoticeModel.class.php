<?php

namespace Admin\Model;
use Think\Model;
use Think\Page;

class NoticeModel extends Model {

	protected $_validate = array(
		array('n_branchId','require','请选择分院'),
	    array('n_content', 'require', '内容不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
	);
	
	protected $_auto = array(
	    array('n_createTime', getTime, self::MODEL_INSERT, 'callback'),
	    array('n_updateTime', getTime, self::MODEL_BOTH, 'callback'),
	    array('n_userId', getUid, self::MODEL_BOTH, 'callback'),
	);
	
	public function getTime(){
	    return date('Y-m-d H:i:s', time());
	}
	
	public function getUid(){
	    return UID;
	}
	
	/* 获取课程数据
	 * @param $id 课程id
	 * @param $fields 查询字段
	 * return array
	 * */
	public function getNoticeDataById($id, $fields = '*'){
	    return $data = $this->field($fields)->where(array('n_id'=>$id))->find();
	}
	
	/* 删除课程
	 * @param $id 课程id
	 * return true/false
	 * */
	public function delNotice($id){
	    return $this->where(array('co_id'=>$id))->delete();
	}
	
	/* 获取公告数据
	 * @param $pageSize 每页数据大小
	 * @param $fields 查询的字段
	 * */
	public function getNoticeData($pageSize=10, $fields = '*'){
	    $count = $this->count();
	    $page = new Page($count, $pageSize);
	    $limit = $page->firstRow . ',' . $page->listRows;
	    $result = $this->field($fields)
    	    ->join('left join __BRANCH__ on __NOTICE__.n_branchId=__BRANCH__.br_id')
    	    ->join('left join __UCENTER_MEMBER__ on __NOTICE__.n_userId=__UCENTER_MEMBER__.id')
    	    ->limit($limit)->order('n_createTime desc')->select();
	    
	    return array('result'=>$result, 'show'=>$page->show());
	}
}