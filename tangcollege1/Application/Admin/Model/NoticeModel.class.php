<?php

namespace Admin\Model;
use Think\Model;
use Common\Logic\AutoCache;

class NoticeModel extends Model {
    use AutoCache;
	protected $_validate = array(
		array('n_branchId','require','请选择分院'),
	    array('n_content', 'require', '内容不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
	);
	
	protected $_auto = array(
	    array('n_content', 'htmlspecialchars', 3, 'function'),
	    array('n_content', 'trim', 3, 'function'),
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
	
	/* 获取公告数据
	 * @param $id 公告id
	 * @param $fields 查询字段
	 * return array
	 * */
	public function getNoticeDataById($id, $fields = true){
	    return $data = $this->field($fields)->where(array('n_id'=>$id))->find();
	}
	
	public function addInfo(){
	    return $this->create() && $this->add();
	}
	
	public function editInfo(){
	    return $this->create() && $this->save();
	}
	
	/* 删除公告
	 * @param $id 公告id集
	 * return true/false
	 * */
	public function delNotice($ids, $branchIds){
	    $where['n_id'] = array('in', $ids);
	    $noticeBranchIds = $this->where($where)->getField('n_branchId', true);
	    $noticeBranchIds = array_unique($noticeBranchIds);
	    $diff = array_diff($noticeBranchIds, $branchIds);
	    
	    if(!empty($diff)){
	        return 2;
	    }
	    
	    return $this->where($where)->delete();
	}
	
	/* 获取公告数据
	 * @param $pageSize 每页数据大小
	 * @param $fields type : array/string/bool 默认为true:指查询所有字段
	 * return array
	 * */
	public function getNoticeData($fields = true, $where = '', $limit='', $order = 'n_createTime desc'){
	    $result = $this->field($fields)
    	    ->join('left join __BRANCH__ on __NOTICE__.n_branchId=__BRANCH__.br_id')
    	    ->join('left join __UCENTER_MEMBER__ on __NOTICE__.n_userId=__UCENTER_MEMBER__.id')
    	    ->where($where)
    	    ->limit($limit)
	        ->order($order)
	        ->select();
	    
	    return $result;
	}
}