<?php

namespace Api\Controller;
use Api\Controller\CommonController;
use Common\Model\ShopkeeperModel;
use Common\Model\ShopkeeperActiveModel;

/**
 * 
 * @author jmjoy
 *
 */
class ShopkeeperActiveController extends CommonController {

	/**
	 * 商家激活模型
	 * @var ShopkeeperActiveModel
	 */
	public $shopkeeperActiveModel;
	
	/**
	 * 初始化
	 */
	public function _initialize() {
		$this->shopkeeperActiveModel = D('Common/ShopkeeperActive');
	}
	
    /**
     * 发送激活邮件 
     */
    public function sendActiveEmail($sid = 0, $email = '', $baseUrl = '') {
		$result = $this->shopkeeperActiveModel->sendActiveEmail(
				$sid, $email, $baseUrl
		);
		$this->simpleAjaxReturn($result);
    }
    
    /**
     * @todo 激活某位商家
	 * @param number $status 
	 * @param string $order
     */
    public function handleActive($token = '') {
    	$result = $this->shopkeeperActiveModel->handleActive($token);
    	$this->simpleAjaxReturn($result);
    }
    
}