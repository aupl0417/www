<?php

namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 商家认证信息Model
 * @author jmjoy
 *
 */
class ShopkeeperAuthModel extends CommonModel {

	/**
	 * 商家的ID
	 */
	public $sid = 0;

	/**
	 * 验证规则
	 */
	protected $_validate = array(
			array('company_name', 'require', '公司名称不能为空！', 1),
			array('company_name', '/^[\w\x{4e00}-\x{9fa5}]{6,20}$/u', '公司名称应该为6~20个有效字符！', 1, 'regex'),

			array('legal_name', 'require', '法人代表名称不能为空！', 1),
			array('legal_name', '/^[\w\x{4e00}-\x{9fa5}]{2,8}$/u', '法人代表名称应该为2~8个有效字符！', 1, 'regex'),

			array('tel', 'require', '固定电话不能为空！', 1),
			array('tel', '/^\d{3,4}\-\d{7,8}$/', '固定电话不正确！', 1, 'regex'),
	);

	/**
	 * 自动完成规则
	 */
	protected $_auto = array (
			array('ctime', 'current_datetime', 1, 'function')
	);

	/**
	 * 前置方法
	 * @see \Think\Model::_initialize()
	 */
	public function _initialize() {
		if (!session('?shopkeeper.id')) {
			return;
		}
		$this->sid = session('shopkeeper.id');
	}

	/**
	 * 根据商家的ID获取他的
	 * @param int $sid
	 * @return \Think\mixed
	 */
	public function getBySid($sid = null) {
		// 不传sid就用商家的session.sid
		if ($sid === null) {
			$sid = $this->sid;
		}
		$row = $this->field(true)
						->where('sid = %d', $sid)
						->find();
		if (!$row) {
			return array();
		}
		return $row;
	}

	/**
	 * 更新认证信息
	 * @return Ambigous <multitype:string NULL , multitype:NULL string >|string|boolean
	 */
	public function handleUpsert() {
		// 看看是否登陆了
		if (!$this->sid) {
			return '商家还没有登陆呢';
		}
		// 看看是不是处于未审核状态
		if (session("shopkeeper.status") != 1) {
			return '未激活或者已经提交审核资料';
		}
		// 看有没有上传过认证信息
		$count = $this->where('sid = %d', $this->sid)->count();
		if ($count) {
			return '已经上传过认证信息了！';
		}
		// 验证信息是否合法
		if (!$this->create()) {
			return $this->getError();
		}
		// 上传营业执照
		list($filepath, $err) = $this->validateCardPic();
		// 检测是否上传成功
		if ($err !== null) {
			return $err;
		}
		// 要插入数据库的数据
		$data = array_merge($this->data, array(
				'sid'		=>	$this->sid,
				'cardpic'	=>	$filepath,
		));
		// 添加认证信息
		if (!$this->data($data)->add()) {
			return $this->getDbError();
		}
		// 修改成功, 改变商家的状态
		M('Shopkeeper')->where('id = %d', $this->sid)
						->setField('status', 2);
		session("shopkeeper.status", 2);
		return true;
	}

	/**
	 * 检测上传是否成功，成功会上传的
	 * @return multitype:NULL string |multitype:string NULL
	 */
	public function validateCardPic() {
		// 实例化并配置上传类
		$upload = new \Think\Upload();
		$upload->maxSize = 5 * 1024 * 1024;
		$upload->rootPath = './Public/Uploads/cardpic/';
		$upload->savePath = '';
		$upload->saveName = array('uniqid','');
		$upload->exts     = array('jpg', 'gif', 'png', 'jpeg');
		$upload->autoSub  = true;
		$upload->subName  = array('date','Y/m/d');
		// 上传哦！
		$info = $upload->uploadOne($_FILES['cardpic']);
		if (!$info) {
			// 上传失败
			return array(null, $upload->getError());
		}
		// 上传成功
		return array($info['savepath'].$info['savename'], null);
	}

}
