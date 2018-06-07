<?php

namespace Student\Controller;
use Think\Controller;

class CommonController extends Controller {
	
    protected function _initialize(){
		header('Content-type:text/html;charset=utf-8');

		//$user = $_SESSION['user_auth'];
		if(empty($user)) {
			//$this->ajaxReturn(['code'=>201,'msg'=>'禁止访问！']);//用户不存在
		}
       // define('UID',$user['uid']);
       
        //获取所有参数
		$data = I('request.');
		
		$this->app = isset($data['app']) ? 1 : 0;//是否是app调用
		
		//判断学员是否存在
		$userId = isset($data['userId']) ? $data['userId'] : session('userId');//暂时用session
		$field = is_numeric($userId) ? 'id' : 'thirdPartyUserId';
		$this->userId = M('ucenter_member')->where(array($field=>$userId))->getField('id');
		if(empty($this->userId) && is_string($userId)){
		    $this->userId = $this->searchUserToThirdPartyById($userId);
		    !$this->userId && $this->error('该学员不存在');
		}
		
		session('userId', $this->userId);//暂时用session保存
		$this->data = $data;
    }
	
	 //如果本系统无此用户 去第三方系统寻找
    protected function searchUserToThirdPartyById($userId, $branchId = null) {
       //定义一个要发送的目标URL；
       $url = C('ERP_OPEN_API_URL').'/mallapi/getUserInfo.json';
       //定义传递的参数数组；
       $data['userID']=$userId;
	   $data['parterId']=C('PARTERID');
	   ksort($data);
	   $secretkey = C('ERP_ECRET_KEY');
	   $data['signValue'] =  md5(http_build_query($data)."&".$secretkey);
       //定义返回值接收变量；
       $httpstr = http($url, $data, 'GET', array("Content-type: text/html; charset=utf-8"));
	   if(empty($httpstr))
	     return false;
	   $result = json_decode($httpstr,true);
	   if(!is_array($result)) {
		   return false;
	   }else{
		   if($result['id']!='1001'){
		     return false;
	       }else{
			   $info = $result['info'];
			   $studentModel = new \Common\Model\StudentModel();
			   $data = [];
			   $data['username'] = $info['u_nick'];
			   $data['password'] = $info['u_loginPwd'];
			   $data['reg_time'] = time();
			   $data['reg_ip'] = get_client_ip(1);
			   $data['identityType'] = 0;
			   $data['branchId'] = !is_null($branchId) ? $branchId : 0;
			   $data['thirdPartyUserId'] = $info['u_id'];
			   $data['mobile'] = !empty($info['u_tel']) ? $info['u_tel'] : null;
			   $data['email'] = !empty($info['u_email']) ? $info['u_email'] : null; 
			   $uid = $studentModel->addInfoCacheClean($data);
			   if(!$uid){
					return false; 
                } else {
                    return $uid;
                }
		   }
	   }
	}
	
	
}
