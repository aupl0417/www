<?php

namespace Api\Controller;
use Api\Controller\CommonController;
use Common\Model\AreaModel;

/**
 * 地区Api控制器
 * @author jmjoy
 *
 */
class AreaController extends CommonController {

	/**
	 * 地区模型
	 * @var AreaModel
	 */
	public $areaModel;

	public function _initialize() {
		$this->areaModel = D('Common/Area');
	}

	/**
	 * GET：根据上一级地区的ID获取所有地区信息
	 * 接收：parentid（上一级地区的ID）
	 */
	public function getByParentId($parentid = 0) {
        if (!$parentid) {
            $this->ajaxReturn([
                'status'    =>  200,
                'data'      =>  [],
            ]);
        }

		$resArr = $this->areaModel->getByParentId($parentid);
		$this->ajaxReturn(array(
				'status'	=>	200,
				'msg'		=>	'',
				'data'		=>	$resArr,
		));
	}

	/**
	 * GET：根据上一级地区的ID获取所有地区信息
	 * 接收：id（上一级地区的ID）
	 */
	public function getAllById($id = 0) {
		$resArr = $this->areaModel->getAllById($id);
		$this->ajaxReturn(array(
				'status'	=>	200,
				'msg'		=>	'',
				'data'		=>	$resArr,
		));
	}

	/**
	 * POST:根据上一级地区的parentid获取所有地区信息
	 * 接收：parentid（上一级地区的parentid）广州市 1953父级ID
	 */
	public function getArea($parentid = 1953) {
		$resArr = $this->areaModel->getByParentId($parentid);
		$data=array('list'=>$resArr);
		$this->ajaxReturn($data);
	}

	public function getSecondArea() {
		$parentid=I('post.area_id');
		$resArr = $this->areaModel->getByParentId($parentid);
		$data=array('list'=>$resArr);
		$this->ajaxReturn($data);
	}
}
