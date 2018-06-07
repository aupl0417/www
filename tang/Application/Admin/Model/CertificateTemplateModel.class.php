<?php

namespace Admin\Model;
use Think\Model;
use Think\Page;

class CertificateTemplateModel extends Model {
    
    protected $tableName = 'certificate_templet';
    
	protected $_validate = array(
		array('cce_gradeId','require','请选择年级'),
// 	    array('n_content', 'require', '内容不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
	);
	
	protected $_auto = array(
	    array('cce_createTime', getTime, self::MODEL_INSERT, 'callback'),
	    array('cce_updateTime', getTime, self::MODEL_BOTH, 'callback'),
	);
	
	public function getTime(){
	    return date('Y-m-d H:i:s', time());
	}
	
	/* 获取证书数据
	 * @param $id 课程id
	 * @param $fields 查询字段
	 * return array
	 * */
	public function getCertificateDataById($id, $fields = '*'){
	    return $data = $this->field($fields)
	                   ->join("LEFT JOIN __GRADE__ on __CERTIFICATE_TEMPLET__.cce_gradeId=__GRADE__.gr_id")
	                   ->where(array('cce_id'=>$id))->find();
	}
	
	/* 删除证书
	 * @param $id 课程id
	 * return true/false
	 * */
	public function delCertificate($id){
	    return $this->where(array('co_id'=>$id))->delete();
	}
	
	/* 获取证书数据
	 * @param $pageSize 每页数据大小
	 * @param $fields 查询的字段
	 * */
	public function getCertificateData($pageSize=10, $fields = '*'){
	    $count = $this->count();
	    $page = new Page($count, $pageSize);
	    $limit = $page->firstRow . ',' . $page->listRows;
	    $result = $this->field($fields)
    	    ->join('left join __GRADE__ on __CERTIFICATE_TEMPLET__.cce_gradeId=__GRADE__.gr_id')
    	    ->limit($limit)->order('cce_createTime desc')->select();
	    
	    return array('result'=>$result, 'show'=>$page->show());
	}
}