<?php
namespace Common\Controller;
use Common\Controller\BaseController;

class CommonController extends BaseController {

	public function _initialize() {
		// 判断模块
		switch (MODULE_NAME) {
		case 'Admin':
			if (!session('?admin_id')) {
				$this->redirect('Admin/Sign/index');
			}
			break;
		case 'Home':
			if (!session("?user")) {
				$this->redirect('Home/Userregsign/login');
			}
			break;
		}
	}

}
