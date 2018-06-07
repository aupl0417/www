<?php

namespace Common\Model;
use Think\Model;
use Common\Logic\AutoCache;


class TeacherCommentModel extends Model {
    use AutoCache;
	protected $_validate = array(
	    
	    
	);
	
	
	/*
	 * 获取留言数据
	 * @param $fields type :　array/string
	 * @param $where type : array/string  查询条件
	 * @param $limit type : string
	 * @param $order type : string
	 * return array
	 * */
    public function getTeacherCommentData($fields = '*', $where = '', $limit, $order = 'tc_classTableId desc,tc_count desc'){
        $where = empty($where) ? array('tc_status'=>0) : $where;
        $result = $this->field($fields)
                ->join("LEFT JOIN __COMMENT_TAG__ ON __TEACHER_COMMENT__.tc_tagId=__COMMENT_TAG__.ct_id")
                ->limit($limit)
                ->where($where)
                ->order($order)
                ->select();
        
        return $result;
    }
    
    public function delInfo($ids){
        $where['tc_id'] = array('in', $ids);
        return $this->where($where)->save(array('tc_status'=>1));
    }
    
}