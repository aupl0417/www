<?php

namespace Api\Controller;
use Api\Controller\CommonController;
use Common\Model\ShopkeeperDetailModel;

class ShopkeeperDetailController extends CommonController {

	/**
	 * 商家详细资料模型
	 * @var ShopkeeperDetailModel
	 */
	public $shopkeeperDetailModel;

	public function _initialize() {
		$this->shopkeeperDetailModel = D('Common/ShopkeeperDetail');
	}

	/**
	 * 根据post提交过来的商家id查询一条详细信息
	 * @return string 返回JSON
	 */
	public function getBySid($sid = 0) {
		$resArr = $this->shopkeeperDetailModel->getBySid($sid);
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

    /**
     * 这是我喝醉写的吗，可能没用了，可是不敢删除
     */
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
     * 处理修改商家个人信息
     */
    public function handleEdit() {
        $this->checkShopSignIn();

        $result = $this->shopkeeperDetailModel->handleEdit();
        $this->simpleAjaxReturn($result);
    }

}
