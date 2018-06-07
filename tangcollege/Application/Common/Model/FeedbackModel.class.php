<?php

namespace Common\Model;
use Think\Model;
use Common\Logic\AutoCache;
class FeedbackModel extends Model {
    use AutoCache;
	protected $_validate = array(
	    array('f_name','require','课程名称必须填写'), //默认情况下用正则进行验证
	    array('f_name', '', '课程名称已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
	    array('f_name', '1,50', '标题不能超过50个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
	    array('f_content','require','留言内容必须填写'),
	    array('f_content', '1,255', '留言内容不能超过255个字符', self::VALUE_VALIDATE , 'length', self::MODEL_BOTH),
	    array('f_email','require','留言内容必须填写'),
	    array('f_state','require','请选择审核状态'),
	);
	
	protected $_auto = array(
	    array('f_branchId', getBranchId, self::MODEL_INSERT, 'callback'),
	    array('f_userId', getUid, self::MODEL_INSERT, 'callback'),
	    array('f_createTime', getTime, self::MODEL_INSERT, 'callback'),
	);
	
	public function getTime(){
	    return date('Y-m-d H:i:s', time());
	}
	
	public function getUid(){
	    return UID;
	}
	
	//获取分院id
	public function getBranchId(){
	    return M('UcenterMember')->where(array('id'=>UID))->getField('branchId');
	}
	
	/*
	 * 获取留言数据
	 * @param $fields type : array/string/bool 默认为true : 指查询主表的所有字段
	 * @param $where  type : array/string  查询条件   默认为空
	 * @param $pageSize type : int 默认每页10条数据
	 * return array
	 * */
    public function getFeedbackData($fields = true, $where = '', $limit = '', $order = 'f_createTime desc'){
        
        $result = $this->field($fields)
                ->join("LEFT JOIN __UCENTER_MEMBER__ ON __FEEDBACK__.f_userId=__UCENTER_MEMBER__.id")
                ->join("LEFT JOIN __BRANCH__ ON __FEEDBACK__.f_branchId=__BRANCH__.br_id")
                ->where($where)
                ->limit($limit)
                ->order($order)
                ->select();
        
        return $result;
    }
    
    /* 获取指定id的留言
     * @param $id  type : int 留言id
     * @param $fields type : array/string/bool 默认为true ： 指查询所有字段
     * return array
     * */
    public function getFeedbackById($id, $fields = true){
        return $this->where(array('f_id' => $id))
                    ->field($fields)
                    ->limit(1)
                    ->find();
    }
    
    public function reviewInfo(){
        return $this->create() && $this->save();
    }
    
    public function delInfo($ids, $branchIds){
        $where['f_id'] = array('in', $ids);
        $feedbackBranchIds = $this->where($where)->getField('f_branchId', true);
        $feedbackBranchIds = array_unique($feedbackBranchIds);
        $diff = array_diff($feedbackBranchIds, $branchIds);
        
        if(!empty($diff)){
            return 2;
        }
        
        return $this->where($where)->delete();
    }
	
}