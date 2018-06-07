<?php

namespace Api\Controller;
use Api\Controller\CommonController;
use Common\Model\ShopkeeperActiveModel;

class ShopkeeperAuthController extends CommonController {

	/**
	 * 商家详细资料模型
	 * @var ShopkeeperActiveModel
	 */	
	public $shopkeeperAuthModel;
	
	public function _initialize() {
		$this->shopkeeperAuthModel = D('Common/ShopkeeperAuth');
	}
	
	/**
	 * 根据post提交过来的商家id查询一条详细信息
	 * @return string 返回JSON
	 */
	public function getBySid() {
		$resArr = $this->shopkeeperAuthModel->getBySid();
		// 检测查询结果
		if ($resArr === false) {
			// 数据库查询失败！
			$resArr = array(
					'status'	=>	400,
					'msg'		=>	'数据库查询失败',
			);
		} elseif ($resArr === null) {
			// 查询成功， 但没有找到！
			$resArr = array(
					'status'	=>	404,
					'msg'		=>	'没有找到数据',
			);
		} else {
			// 查询成功， 并且找到数据了！
			$tmpArr = array(
					'status'	=>	200,
					'msg'		=>	'',
			);
			$resArr = array_merge($resArr, $tmpArr);			
		}
		// 查询成功，有记录
		$this->ajaxReturn($resArr);
	}
	
	public function handleUpsert() {
		$result = $this->shopkeeperAuthModel->handleUpsert();
		if ($result !== true) {
			$this->ajaxReturn(array(
					'status'	=>	400,
					'msg'		=>	$result,
			));
		}
		$this->ajaxReturn(array(
				'status'		=>	200,
				'msg'			=>	'',
		));
	}
	
	/**
	 * 是否已经发送了认证资料
	 */
	public function hasSendAuth() {
		// 看看商家有没有登录
		$this->checkShopSignIn();
		
		$result = D('Common/Shopkeeper')->checkSendAuth(session('shopkeeper.id'));
		$this->simpleAjaxReturn($result);
	}
	
}
