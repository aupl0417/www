<?php
namespace Common\Model;
use Think\Model;
use Common\Logic\AutoCache;
/**
 * 分院模型
 */
class BranchModel extends Model{
	use AutoCache;
	/* 用户模型自动验证 */
	protected $_validate = array(
	    array('br_areaId', 'require','所在地区必须填写'),
		array('br_address','3,200','详细地址必须在3至200个内',3,'length'),
		array('br_name','3,125','机构名必须是3到125个字符',3,'length'),
	);
	
	/*protected $_auto = array(
	    array('cr_userId', getUid, self::MODEL_INSERT, 'callback'),
	    array('cr_userName', getUserNameByUid, self::MODEL_INSERT, 'callback'),
	    array('br_createTime', getTime, self::MODEL_INSERT, 'callback'),
	    array('br_updateTime', getTime, self::MODEL_BOTH, 'callback'),
	);
	*/
	protected function _before_insert(&$data) {
		$data['br_createTime'] = $this->getTime();
		
	}
	 protected function _before_update(&$data) {
		$data['br_updateTime'] = $this->getTime(); 
	}
	
	
	
    private function getTime() {
		 return date('Y-m-d H:i:s', time());
	}
	/**
     * 获取分院信息
     * @param  milit   $id 分类ID或标识
     * @param  boolean $field 查询字段
     * @return array     分类信息
     */
    public function info($id=''){
		$info = $this->query("SELECT br_id,br_areaId,br_parentId,br_address,br_name,(select br_name from ".C('DB_PREFIX')."branch b where b.br_id=a.br_parentId) as br_parentName FROM  ".C('DB_PREFIX')."branch a where br_id =".$id);
		return empty($info) ? [] : current($info);
    } 
	
	/**
     * 获取分院信息列表
     * @param  string or array  查询条件
     * @param  boolean $field 查询字段
	 * @param  boolean $isReturnSubcount 是否返回子级数目
     * @return array     分类信息
     */
	public function lists($where=null,$field = '*',$order = 'br_parentId ASC',$isReturnSubcount=false) {
		if(!empty($where)){
		   if(is_array($where)) 
			 $where = implode(' and ',$where);	 
		}
		if($isReturnSubcount){
		   $where = empty($where) ? '' : 'where '.$where;
		   $lists = $this->query("SELECT $field,(select count(*) from ".C('DB_PREFIX')."branch b where b.br_parentId=a.br_id) as subCount FROM  ".C('DB_PREFIX')."branch a $where order by $order"); 
	    }else{
		   $lists = $this->where($where)->field($field)->order($order)->select();
		}
		return $lists;   	 
	}
	
	//递归分院
	public function recursion($id=0) {
		$data = [];
		$where = 'br_parentId ='.$id;
		$lists = $this->lists($where,'br_id,br_areaId,br_parentId,br_name');
		if(!empty($lists)){
		  foreach($lists as $val) {
			$item = ['id'=>$val['br_id'],'name'=>$val['br_name']];
		    $item['list'] = $this->recursion($val['br_id']);
			$data[] = $item;
		  }
	    }
		return $data;
	}
	 
	public function edit($br_id) {
		$data = I('post.');
		if(!$this->create($data)) {
			return false;
		}
		 if(empty($data['br_parentId'])) {
			   $data['br_level'] = 0; 
		 }else{
			   $info = $this->where(['br_id'=>$data['br_parentId']])->field('br_level')->find();
			   if(!empty($info)) {
			     $data['br_level'] = $info['br_level'] + 1;
			   }else{
				  return false;
			   }
		  }
		  return $this->where('br_id="'.$br_id.'"')->save($data);	
	}
	
	public function addInfo() {
		    $data = I('post.');
		    if(!$this->create($data)) {
				return false;
			}
		    if(empty($data['br_parentId'])) {
			   $data['br_level'] = 0; 
		    }else{
			   $info = $this->where(['br_id'=>$data['br_parentId']])->field('br_level')->find();
			   if(!empty($info)) {
			     $data['br_level'] = $info['br_level'] + 1;
			   }else{
				  return false;
			   }
		    }
			return $this->add($data);
	}
	//通过id获取其子孙后代
	public function getPosterityIds($id) {
		$data = [];
		$this->getIdsByParentId($id,$data);
		return $data;
	}
	//获取分院id的子孙后代的id
	public function getIdsByParentId($id,&$ids) {
		$lists = $this->lists('br_parentId='.$id,'br_id');
		if(!empty($lists)) {
			foreach($lists as $val) {
			   $ids[] = $val['br_id'];	
			   $this->getIdsByParentId($val['br_id'],$ids);
			}
		}
		
	}
	
}
?>
