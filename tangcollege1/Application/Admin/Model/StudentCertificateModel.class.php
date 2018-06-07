<?php

namespace Admin\Model;
use Think\Model;
use Common\Logic\AutoCache;

class StudentCertificateModel extends Model {
    use AutoCache;
    
	protected $_validate = array(
	    array('se_classId','require','请选择班级'),
	    array('se_studentId','require','请选择学生'),
	    array('se_studentId', '', '学生已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
	);
	
	protected $_auto = array(
	    array('se_createTime', getTime, self::MODEL_INSERT, 'callback'),
	    array('se_updateTime', getTime, self::MODEL_BOTH, 'callback'),
	    array('se_createPersonId', getUid, self::MODEL_BOTH, 'callback'),
	);
	
	public function getTime(){
	    return date('Y-m-d H:i:s', time());
	}
	
	public function getUid(){
	    return UID;
	}
	
	
	
	/* 获取公告数据
	 * @param $fields type ： array/string/bool 默认为true : 指 查询所有字段
	 * @param $where type : array/string 查询条件
	 * @param $pageSize 每页数据大小
	 * return array
	 * */
	public function getStudentCertificateList($fields = '*', $where = '', $limit='', $order = 'se_createTime desc'){
	    
	    $result = $this->field($fields)
	               ->join("LEFT JOIN __UCENTER_MEMBER__ uma on uma.id=se_studentId")
	               ->join("LEFT JOIN __BRANCH__ ON br_id=se_branchId")
	               ->join("LEFT JOIN __UCENTER_MEMBER__ umb on umb.id=se_createPersonId")
	               ->join("LEFT JOIN __CLASS__ on cl_id=se_classId")
	               ->where($where)->limit($limit)
	               ->order($order)
	               ->select();
	    
	    return $result;
	}
	
	
	/*
	 * 获取指定id的一条数据
	 * @param $id type : int 学生证书id
	 * @param $fields type : array/string/bool 默认为true
	 * return array
	 * */
	public function getStudentCertificateById($id, $fields = true, $order = 'se_createTime desc'){
	    return $result = $this->field($fields)
	                   ->join("Left JOIN __CLASS__ on __CLASS__.cl_id=__STUDENT_CERTIFICATE__.se_classId")
	                   ->join('LEFT JOIN __UCENTER_MEMBER__ on __STUDENT_CERTIFICATE__.se_studentId=__UCENTER_MEMBER__.id ')
	                   ->where(array('se_id'=>$id))
	                   ->limit(1)->order($order)
	                   ->find();
	}
	
	public function addInfo($url){
	    $data = $this->field(array('se_classId', 'se_studentId'))->create();
	    if(!$data){
	        return false;
	    }
	    $where = array('cs_classId'=>$data['se_classId'], 'cs_studentId'=>$data['se_studentId']);
	    $res = M('class_student')->where($where)->field('cs_createTime', true)->find();
	    if(!$res){
	        return 2;
	    }else if($res['cs_isGraduated'] == 0){
	        return 3;
	    }else if($res['cs_status'] != 0){
	        return 4;
	    }
	    $data['se_branchId'] = M('class')->getFieldByClId($data['se_classId'], 'cl_branchId');
	    $data['se_url'] = $url;
	    return $this->add($data);
	}
	
	public function editInfo($url, $branchId){
	    $data = $this->create();
	    if(!$data){
	        return false;
	    }
	    $where = array('cs_classId'=>$data['se_classId'], 'cs_studentId'=>$data['se_studentId']);
	    $res = M('class_student')->where($where)->field('cs_createTime', true)->find();
	    if(!$res){
	        return 2;
	    }else if($res['cs_isGraduated'] == 0){
	        return 3;
	    }else if($res['cs_status'] != 0){
	        return 4;
	    }
	    $data['se_branchId'] = $branchId;
	    $data['se_url'] = $url;
	    
	    return $this->save($data);
	}
	
	/* 删除学生证书
	 * param $ids 学生证书id集  type : string
	 * param $branchIds 所属分院及下属分院id集   type : array
	 * return bool
	 * */
	public function delInfo($ids, $branchIds){
	    $where['se_id'] = array('in', $ids);
	    $studentCerBranchId = $this->where($where)->getField('se_branchId', true);
	    $studentCerBranchId = array_unique($studentCerBranchId);
	    $diff = array_diff($studentCerBranchId, $branchIds);
	    
	    if(!empty($diff)){
	        return 2;
	    }
	    
	    return $res = $this->where($where)->delete();
	}
}