<?php
namespace Api\Controller;
use Think\Controller;

class ApiController extends Controller {
    protected $app = array(
	     '1' => 'D8OZLSE2NEDC0FR4XTGBKHY67UJZ8IK9', //ios
		 '2' =>'DFHGKZLSE2NFDEHGFHHR4XTGBKHY67EJZ8IK9', //安卓
	);
	
    protected function _initialize(){
		$data = I('request.');
		if(!isset($data['appId']) || empty($data['appId'])){
			$this->ajaxReturn(['code'=>100,'msg'=>'appId不能为空']);
		}
		if(!isset($data['signValue']) || empty($data['signValue'])){
			$this->ajaxReturn(['code'=>101,'msg'=>'signValue不能为空']);
		}
		$this->sign = $data['signValue'];
		unset($data['signValue']);
		!$data && $this->ajaxReturn(['code'=>102,'msg'=>'没数据处理']);
		
		$this->data = $data;
		$this->validate = $this->signValidate($this->data, $this->sign);
		if(!$this->validate){
			$this->ajaxReturn(['code'=>103,'msg'=>'签名错误']);
		}
    }
	
	//签名校验
	protected function signValidate($data, $sign){
		if(empty($data) || !is_array($data) || !($data['appId'] > 0) || !isset($this->app[$data['appId']])){
			return false;
		}
		$secretKey = $this->app[$data['appId']];
		ksort($data);
		$queryString = http_build_query($data);
		
		if(md5("{$queryString}&{$secretKey}") != $sign){
			$this->newSign = md5( "{$queryString}&{$secretKey}");
			return false;
		}
		return true;
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
