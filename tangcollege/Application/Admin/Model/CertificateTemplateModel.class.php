<?php

namespace Admin\Model;
use Think\Model;
use Common\Logic\AutoCache;

class CertificateTemplateModel extends Model {
    use AutoCache;
    protected $tableName = 'certificate_templet';
    
	protected $_validate = array(
		array('cce_gradeId','require','请选择年级'),
	    array('cce_type','require','请选择证书类型'),
// 	    array('cce_url','require','请选择证书'),
// 	    array('n_content', 'require', '内容不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
	);
	
	protected $_auto = array(
	    array('cce_branchId', getBranchId, self::MODEL_INSERT, 'callback'),
	    array('cce_createTime', getTime, self::MODEL_INSERT, 'callback'),
	    array('cce_updateTime', getTime, self::MODEL_BOTH, 'callback'),
	);
	
	public function getTime(){
	    return date('Y-m-d H:i:s', time());
	}
	
	public function getBranchId(){
	    return BRANCHID;
	}
	
	/* 获取证书数据
	 * @param $id 课程id
	 * @param $fields 查询字段
	 * return array
	 * */
	public function getCertificateDataById($id, $fields = true){
	    return $data = $this->field($fields)
	                   ->join("LEFT JOIN __GRADE__ on __CERTIFICATE_TEMPLET__.cce_gradeId=__GRADE__.gr_id")
	                   ->where(array('cce_id'=>$id))->find();
	}
	
	public function addInfo(){
	    $data = $this->create();
	    if(!$data){
	        return false;
	    }
	    
	    $data['cce_url'] = $data['cce_url'][0];
	    return $this->add($data);
	}
	
	public function editInfo(){
	    $data = $this->create();
	    if(!$data){
	        return false;
	    }
	     
	    $data['cce_url'] = $data['cce_url'][0];
	    return $this->save($data);
	}
	
	/* 删除证书
	 * @param $ids 课程id集
	 * return bool/2
	 * */
	public function delCertificate($ids, $branchIds){
	    $where['cce_id'] = array('in', $ids);
	    $cerTempBranchId = D('CertificateTemplate')->getCertificateData('cce_branchId', $where);
	    $cerTempBranchId = array_column($cerTempBranchId, 'cce_branchId');//要删除的证书所在的分院id集
	    $diff = array_diff($cerTempBranchId, $branchIds);//得出不在所属分院的id集
	    
	    if(!empty($cerTempBranchId)){
	        return 2;
	    }
	    
	    return $this->where($where)->delete();
	}
	
	/* 获取证书数据
	 * @param $pageSize 每页数据大小
	 * @param $fields 查询的字段
	 * */
	public function getCertificateData($fields = true, $where = '', $limit = '', $order = 'cce_createTime desc'){
	    $result = $this->field($fields)
    	    ->join('left join __GRADE__ on __CERTIFICATE_TEMPLET__.cce_gradeId=__GRADE__.gr_id')
    	    ->where($where)
    	    ->limit($limit)->order($order)
	        ->select();
	    
	    return $result;
	}
}