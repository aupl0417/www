<?php
namespace Common\Model;
use Think\Model;
use Common\Logic\AutoCache;
use Common\Model\UcenterMemberModel;
class TeacherModel extends Model{
   use AutoCache;	
   protected $_validate = array(
        array('te_birthday', 'require', '出生日期必须填写'),
        array('te_eduLevel', '0,1,2,3,4,5,6', '最高学历必须填写', self::MUST_VALIDATE, 'in',self::MODEL_BOTH),
		array('te_birthday', 'require', '出生日期必须填写'),
		array('te_fromAcademy', '2,125', '毕业院校必须是2到125个字符',self::MUST_VALIDATE,'length',self::MODEL_BOTH),
		array('te_description', '10,125', '教师介绍必须是20到800个字符',self::EXISTS_VALIDATE,'length',self::MODEL_BOTH),
		array('te_level', 'require', '教师等级必须选择'),
		
    );
	
   
    public function lists($where,$field,$order,$limit='') {
		$field = empty($field) ? 'a.te_birthday,a.te_eduLevel,a.te_fromAcademy,a.te_level,tl_name,a.te_sex,br_name,b.id,b.username,b.identityType,b.branchId' : $field;
		$order = empty($order) ? 'te_id DESC' : $order; 
        return $this->alias('a')->join('__UCENTER_MEMBER__ b on te_userId = id')
		           ->join('__BRANCH__ on br_id = branchId')
				   ->join('__TEACHER_LEVEL__ on tl_id = te_level')
				   ->where($where)
				   ->order($order)
				   ->field($field)
				   ->limit($limit)
				   ->select(); 	 
	}
	public function infoCount($where) {
        return $this->join('__UCENTER_MEMBER__ on te_userId = id')
		           ->join('__BRANCH__ on br_id = branchId')
				   ->join('__TEACHER_LEVEL__ on tl_id = te_level')
				   ->where($where)
				   ->count();
				   	 
	}
	
	public function info($id,$field = '*') {
		$where = null;
		if(is_numeric($id)) {
			$where = 'id="'.$id.'"';
		}else{
			$where = $id;
		}
		$field = empty($field) ? 'a.*,b.*,tl_name' : $field;
        return $this->alias('a')->join('__UCENTER_MEMBER__ b on te_userId = id')
		           ->join('__BRANCH__ on br_id = branchId')
				   ->join('__TEACHER_LEVEL__ on tl_id = te_level')
				   ->where($where)
				   ->field($field)
				   ->find(); 	 
	}
		 
	public function editInfo($userId,$data) {
		    $this->startTrans();
			$ucenterModel = new UcenterMemberModel();
			if(!$ucenterModel->checkFields($data)) {
				 $this->error = $ucenterModel->getError();
				 return false; 
			}
			$ucenterModel->where('id="'.$userId.'"')->save($data);
			if(!$this->create($data)) {
				$this->rollback();
				return false;
			}
			$this->where('te_userId="'.$userId.'"')->save($data);
			$this->commit();
			return true;
	}
	
	public function addInfo($data) {
		    $this->startTrans();
			$ucenterModel = new UcenterMemberModel();
			if(!$ucenterModel->checkFields($data)) {
				 $this->error = $ucenterModel->getError();
				 return false; 
			}
			$uid = $ucenterModel->add();
			if(!$uid) {
				$this->error = $ucenterModel->getError();
				return false;  
			}
			$data['te_userId'] = $uid; 
			if(!$this->create($data)) {
				$this->rollback();
				return false;
			}
			if(!$this->add()) {
				$this->rollback();
				return false;
			}
			$this->commit(); 
			return true;
	}
	
	public function delInfo($id='') {  //先简单删除教师记录
		     $this->startTrans();
			 if(!$this->where('te_userId='.$id)->delete())
			    return false;
			 if(!$this->table('__UCENTER_MEMBER__')->where('id='.$id)->delete()) {
			    $this->rollback();
				return false;
			 }else{
				$this->commit();  
				return true; 
			 }
			 
			
	}
	
	
}
?>
