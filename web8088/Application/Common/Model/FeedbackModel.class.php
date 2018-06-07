<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */

class FeedbackModel extends CommonModel {
	
	
	//验证
	protected $_validate=array(
			array('feedback', '/^.{1,255}$/', '课程内容必须在255个字符以内！', 1, 'regex'),
	);
	
	protected $_auto = array (
			array('ctime', 'current_datetime', 1, 'function')
	);
	
	
	
	/**
	 * 插入反馈信息
	 * @return string|boolean
	 */
	public function addFeedback(){
		$uid = session('user.id');
		if (!$this->create()){
			return $this->getDbError();
		}
		$this->uid = $uid;
		if (!$this->filter('strip_tags')->add()) {
			return $this->getDbError();
		}
		return true;
	}
	
	
}