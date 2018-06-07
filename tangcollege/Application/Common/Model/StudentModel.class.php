<?php

namespace Common\Model;
use Think\Model;
use Common\Model\UcenterMemberModel;
use Common\Logic\AutoCache;
class StudentModel extends Model {
    use AutoCache;
    protected $_validate = array(
	  
		
    );
	/*
		获取一个学生信息
	*/
	public function getStudentDataById($id, $field = true){
		return $this->where(array('stu_id'=>$id))->field($field)->limit(1)->find();
	}
	public function getTime(){
	    return date('Y-m-d H:i:s', time());
	}
	protected function _before_insert(&$data,$options) {
		$data['stu_createTime'] = $this->getTime();
    }
    public function lists($where=null,$field,$order,$limit='') {
		$field = empty($field) ? 'b.*,stu_sex,stu_birthday,stu_createTime' : $field;
		$order = empty($order) ? 'stu_id DESC' : $order;
        return $this->alias('a')
		           ->join('__UCENTER_MEMBER__ b on stu_userId = id')
				   ->join('__CLASS_STUDENT__ on cs_studentId=stu_userId','LEFT')
				   ->join('__CLASS__ c on cl_id = cs_classId','LEFT')
				   ->distinct(true)
				   ->where($where)
				   ->order($order)
				   ->field($field)
				   ->limit($limit)
				   ->select(); 	 
	}
	//获得班级和学生列表
	public function getStudentAndClass($where, $field = '*', $order = 'cl_id DESC,stu_userId DESC',$limit=''){
	    return $this->field($field)
	            ->join('__CLASS_STUDENT__ on cs_studentId=stu_userId')
			    ->join('__CLASS__ on cs_classId=cl_id')
	            ->join('__UCENTER_MEMBER__ on stu_userId=__UCENTER_MEMBER__.id')
	            ->where($where)
				->order($order)
				->limit($limit)
	            ->select();
	    
	}
	public function infoCount($where) {
        return $this->join('__UCENTER_MEMBER__ on stu_userId = id')
				   ->where($where)
				   ->count();
				   	 
	}
	
	public function info($id,$field) {
		   if(is_numeric($id)) {
			  $where = 'stu_userId ='.$id;
		   }else{
			  $where = $id;
		   }
		   $field = empty($field) ? 'a.*,b.*,cl_id,c.cl_name,cl_endTime' : $field;
           $infos = $this->alias('a')
		              ->join('__UCENTER_MEMBER__ b on stu_userId = id','LEFT')
				      ->join('__CLASS_STUDENT__ on stu_userId = cs_studentId','LEFT')
				      ->join('__CLASS__ c on cs_classId = cl_id','LEFT')
				      ->where($where)
				      ->field($field)
				      ->select(); 
			$newInfo = [];  
			if(!empty($infos)) {
				foreach($infos as &$info) {
					$classInfo = [];
					if(isset($info['cl_id'])) {
					   $classInfo['classId'] = $info['cl_id'];
					   unset($info['cl_id']);
					}if(isset($info['cl_name'])){
					   $classInfo['className'] = $info['cl_name'];
					   unset($info['cl_name']); 
					}if(isset($info['cl_endTime'])){
					   $nowtime = time();
					   $endtime = strtotime($info['cl_endTime']);
					   if($nowtime > $endtime) {
						   $classInfo['is_edit'] = 0; 
					   }else{
						   $classInfo['is_edit'] = 1; 
					   } 
					}
					if(empty($classInfo)){  
					  break;  
					}
					$newInfo['classInfo'][] = $classInfo;
				}
				return $newInfo + $infos[0];
			}else{
				return [];
			}
	}
		 

    public function editInfo($userId,$data) {
		    $this->startTrans();
			$ucenterModel = new UcenterMemberModel();
			if(!$ucenterModel->checkFields($data)) {
				 $this->error = $ucenterModel->getError();
				 return false; 
			}
			$ucenterModel->where('id='.$userId)->save($data);
		    $existed = M('class_student')->where('cs_studentId="'.$userId.'"')->field('cs_classId')->select();
			$existedClass = empty($existed) ? [] : array_column($existed,'cs_classId');
			$addData = [];
		    $deleteData = [];
		    if(!empty($data['class'])) {
				  if(!empty($existedClass)) {
					  $addData  = array_diff($data['class'],$existedClass);
					  $deleteData = array_diff($existedClass,$data['class']);
				   }else{
					  $addData =  $data['class']; 
			       }
				   if(!empty($addData)) {
					 $tempArr = [];  
					 foreach($addData as $val) {
						$tempArr[] = ['cs_studentId'=>$userId,'cs_classId'=>$val]; 
					 }
				     if(!M('class_student')->addAll($tempArr)) {
					   $this->rollback();  
				       return false; 
				     }
			       }
			}else{
				 $deleteData =  $existedClass;
			}
			if(!empty($deleteData)) {
				$tempArr = [];
				foreach($deleteData as $val) {
					$tempArr[] = '(cs_studentId='.$userId.' and cs_classId='.$val.')';	
				}
				$where = implode(' or ',$tempArr);
				if(!M('class_student')->where($where)->delete()) {
					$this->rollback();  
				    return false; 
				  }
			}
			$this->commit(); 
			return  true;
	}
	
	public function addInfo($data) {
			 $this->startTrans();
			 $ucenterModel = new UcenterMemberModel();
			 if(!$ucenterModel->checkFields($data)) {
				 $this->error = $ucenterModel->getError();
				 return false; 
			 }
			 $uid = $ucenterModel->add($data);
			 if(!$uid) {
				$this->error = $ucenterModel->getError();
				return false;  
			 }
			 $data['stu_userId'] = $uid; 
			 if(!$this->create($data)) {
				$this->rollback(); 
			    return false;
			 }
			 if(!$this->add()) {
				$this->rollback(); 
				return false;
			 }
			 if(!empty($data['class'])) {
				   $tempArr = [];  
				   foreach($data['class'] as $val) {
						$tempArr[] = ['cs_studentId'=>$data['stu_userId'],'cs_classId'=>$val,'cs_createTime'=>$this->getTime()]; 
				   }
				  if(!M('class_student')->addAll($tempArr)) {
					$this->rollback();  
				    return false; 
				  }
			 }
			$this->commit();  
			return $uid; 
			 	
	}
	
	public function delInfo($id) {  
		     $this->startTrans();
			 if(!$this->where('stu_userId="'.$id.'"')->delete())
			    return false;
			 if(!$this->table('__UCENTER_MEMBER__')->where('id="'.$id.'"')->delete()) {
			    $this->rollback();
				return false;
			 }
			 $stuCount = M('class_student')->where('cs_studentId="'.$id.'"')->count();
			 if($stuCount > 0) {
			    if(!M('class_student')->where('cs_studentId="'.$id.'"')->delete()) {
				 $this->rollback();
				 return false;
			   }
	        }
			$this->commit();  
			return true; 
			 	 	
	}
	public function cutOutStudentFromBranch($id,$branchIds) {  //从分院中剔除学生
		if(empty($id) || empty($branchIds))
		  return false;
		$branchIds = is_array($branchIds) ? $branchIds : (array)$branchIds;
		$this->execute("delete from ".C('DB_PREFIX')."class_student where (cs_classId in(select cl_id from ".C('DB_PREFIX')."class where cl_branchId in(".implode(',',$branchIds)."))) and 	cs_studentId='".$id."'");
		M('ucenter_member')->where('id="'.$id.'"')->save(['branchId'=>0]);
		return true;
		
	}
	
}
?>
