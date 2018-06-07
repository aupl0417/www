<?php

namespace Common\Model;
use Think\Model;
use Common\Logic\AutoCache;
class UcenterMemberModel extends Model {
    use AutoCache;
    protected $_validate = array(
	   /* 验证用户名 */
		array('username', '1,30', '用户名长度不合法', self::EXISTS_VALIDATE, 'length'),
		array('username', '', '用户名被占用', self::EXISTS_VALIDATE, 'unique'), 
		
		/* 验证密码 */
		array('password', '6,40', '密码长度不合法', self::EXISTS_VALIDATE, 'length'),

		/* 验证邮箱 */
		array('email', 'email', '邮箱格式不正确', self::EXISTS_VALIDATE),
		array('email', '1,32', '邮箱长度不合法', self::EXISTS_VALIDATE, 'length'), 
		array('email', '', '邮箱被占用', self::EXISTS_VALIDATE, 'unique'), 

		/* 验证手机号码 */
		array('mobile', '/^1[3|4|5|8]\d{9}$/', '手机格式不正确', self::EXISTS_VALIDATE), 
		array('mobile', '', '手机号被占用', self::EXISTS_VALIDATE, 'unique'), 
		array('identityType','/^[0-9]{1}$/','身份类别必须填写',self::EXISTS_VALIDATE,'regex',self::MODEL_INSERT), 
		
    );
	public function checkFields($data) {
		return $this->create($data);
		
	}
	
	public function getUserList($field = true, $where = '', $join = '', $limit = '', $order = 'id desc'){
	    $obj = $this->field($field);
	    
	    if(!empty($obj)){
	        $obj->join($join);
	    }
	    
	    return $obj->limit($limit)
	               ->order($order)
	               ->where($where)
	               ->select();
	}
}
?>
