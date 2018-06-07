<?php
namespace Common\Model;
use Think\Model;
use Common\Logic\AutoCache;

class StudyDirectionModel extends Model{
    use AutoCache;
    protected $_validate = array(
        array('sd_name','require','课程分类名称必须填写'), //默认情况下用正则进行验证
        array('sd_name', '', '课程分类名称已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
        array('sd_description','require','课程分类名称必须填写'),
    );
	
    protected $_auto = array(
        array('sd_createTime', getTime, 1, 'callback'),
    );
	
    public function getTime() {
		 return date('Y-m-d H:i:s', time());
	}
	
    public function info($id='',$field = '*'){
	   
    } 
	public function infoCount($where='') {
      
	}
	
	public function lists($where=null,$field = true,$order = 'sd_createTime DESC',$limit='') {
		$lists = $this->where($where)->field($field)->order($order)->limit($limit)->select();
		return $lists;
		
	}
	
	public function editInfo() {
			return $this->create() && $this->save();
	}
	
	public function addInfo() {
			return $this->create() && $this->add();
	}	 
	
	public function delInfo($ids) {  //简单删除
	    $where['sd_id'] = array('in', $ids);
	    return $this->where($where)->delete();
	}
	
	public function getStudyDirectionList($field = true, $where = '', $limit = '', $order = 'sd_createTime desc'){
	    $result = $this->where($where)
	            ->field($field)->order($order)
	            ->limit($limit)
	            ->select();
	    
	    return $result;
	}
	
	public function getStudyDirectionData($field = true, $where = ''){
	    return $this->where($where)->field($field)->select();
	}
	
	public function getCourseTypeDataOne($where, $field = true){
	    return $this->where($where)->field($field)->limit(1)->find();
	}
}
?>
