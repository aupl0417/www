<?php
namespace Common\Model;
use Think\Model;
use Common\Model\AreaModel;
use Common\Logic\AutoCache;
class TrainingsiteModel extends Model{
	use AutoCache;
	 protected $_validate = array(
        array('tra_name', '3,125', '地址名请输入3到125个字符',self::MUST_VALIDATE,'length',self::MODEL_BOTH),
        array('tra_address', '10,255', '详细地址请输入10到255个字符',self::MUST_VALIDATE,'length',self::MODEL_BOTH),
		array('te_birthday', 'require', '出生日期必须填写'),
		array('tra_branchId', 'require', '分院必须填写'),
		
    );
	
	
	protected function _before_insert(&$data,$options) {
		$data['tra_createTime'] = $this->getTime();
	}
    private function getTime() {
		 return date('Y-m-d H:i:s', time());
	}
	
    public function info($id,$field){
	   if(is_numeric($id)) {
		   $where = 'tra_id="'.$id.'"';
	   }else{
		  $where = $id; 
	   }
	   $field = empty($field) ? 'a.*,b.br_name' : $field;
	   $info = $this->alias('a')
	         ->join('__BRANCH__ b on br_id = tra_branchId')
		     ->where($where)
			 ->field($field)
			 ->find();
		   $Area = new \Common\Model\AreaModel();
		   $areaInfo = $Area->getFullArea($info['tra_areaId']);
		   if(empty($areaInfo)) {
			   $info['br_area'] = 0;
		   }else{
			   $tempArr[] = ['id'=>0,'selectVal'=>$areaInfo[0]];
			   $tempArr[] = ['id'=>$areaInfo[0],'selectVal'=>$areaInfo[1]];
			   $tempArr[] = ['id'=>$areaInfo[1],'selectVal'=>$areaInfo[2]];	
			   $info['tra_area'] = json_encode($tempArr); 
			   unset($tempArr);     
		   } 
		   return $info;
    } 
	public function infoCount($where) {
       return $this->join('__BRANCH__ b on br_id = tra_branchId')
		     ->where($where)
			 ->count();
	}
	
	public function lists($where=null,$field,$order,$limit='') {
		$field = empty($field) ? 'a.*,b.br_name' : $field;
		$order = empty($order) ? 'tra_createTime DESC' : $order;
		$lists = $this->alias('a')
		     ->join('__BRANCH__ b on br_id = tra_branchId')
		     ->where($where)
			 ->field($field)
			 ->order($order)
			 ->limit($limit)
			 ->select();
	    $areaModel = new AreaModel();		 
		foreach($lists as &$value) {
			$areas = $areaModel->getFullArea($value['tra_areaId'],0);
			$value['area'] = implode(' / ',$areas);
		}
		return $lists;
		
	}
	
	public function editInfo($id) {
		  if(is_numeric($id)) {
		    $where = 'tra_id="'.$id.'"';
	      }else{
		    $where = $id; 
	      }
		  return $this->create() && $this->where($where)->save();
	}
	
	public function addInfo() {
			return $this->create() && $this->add();
	}	 
	
	public function delInfo($id='') {  //简单删除
		 return $this->where('tra_id="'.$id.'"')->delete();   
			 
			
	}
}
?>
