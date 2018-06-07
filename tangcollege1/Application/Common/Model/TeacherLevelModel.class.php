<?php
namespace Common\Model;
use Think\Model;
use Common\Logic\AutoCache;

class TeacherLevelModel extends Model{
   use AutoCache;	
   protected $_validate = array(
        array('tl_name', 'require', '教师级别名必须填写'),
		array('tl_description', '10,1000', '教师级别描叙请输入10到1000个内的字符',self::MUST_VALIDATE,'length',self::MODEL_BOTH),
		array('tl_weight', 'number', '教师等级权重请填写一个数字'),
		
    );
	
    public function info($id){
		return $this->where('tl_id="'.$id.'"')->find();	
    } 
	
    public function lists($where=null,$field = '*',$order = 'tl_weight DESC',$limit='') {
        return $this->where($where)->order($order)->field($field)->limit($limit)->select(); 	 
	}
	
	public function addInfo() {
		return $this->create() && $this->add();	
	}
	public function editInfo($id) {
		return $this->create() && $this->where('tl_id="'.$id.'"')->save();	
	}	 
	public function delInfo($id) {
		return  $this->where('tl_id="'.$id.'"')->delete();	
	}
	
}
?>
