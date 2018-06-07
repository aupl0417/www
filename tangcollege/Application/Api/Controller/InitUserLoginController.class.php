<?php
namespace Api\Controller;
class InitUserLoginController extends ApiController {
    public function index(){
		
		$userId = I('userId', '');
		if(empty($userId)) {
			$this->ajaxReturn(['code'=>201,'msg'=>'用户不存在！']);
		}
	    $userInfo = M('ucenter_member')->where(is_numeric($userId) ? 'id='.$userId : 'thirdPartyUserId="'.$userId.'"')->field('id,identityType,branchId,thirdPartyUserId')->find();
		if(empty($userInfo) && is_string($userId)) {
			$userId = $this->searchUserToThirdPartyById($userId);
			if(!$userId) {
				$this->ajaxReturn(['code'=>201,'msg'=>'用户不存在！']);//用户不存在
			}
			
		}else{
			$userId = $userInfo['id'];
		}
		$StudentModel = new \Student\Model\StudentModel();
		$StudentModel->login($userId);
		
	}
}
