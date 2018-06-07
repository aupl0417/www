<?php

namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 用户激活信息Model--激活用户手机
 * @author users
 *
 */
class UsertelactiveModel extends CommonModel {
	
   
    public $msg="尊敬的客户，您正在进行手机认证服务。短信验证码为";
    public $messages="，10分钟内验证有效，谢谢。17yueke.cn";
    public $company="【17约课】";
	
	/**
	 * 发送激活用户手机验证码
	 */
	public function sendActiveTel($uid=0,$tel=0) {
	    if ($tel==0){
	        return "请输入认证的手机号码";
	    }
	    if ($uid==0){
	        return "请先登录后再认证";
	    }

	    $regex = '/^(1)[\d]{10}$/';
	    if(!preg_match($regex, $tel)){
	        return '请输入正确的手机号码！';
	    }
	    
	    $User = D('User');
	    
	    $check_tel_status=$User->checkTelStatus($uid);
	    if ($check_tel_status){
	        if ($check_tel_status['telstatus']==1){
	            return '手机号码已认证，认证的手机号码为:'.$check_tel_status['telauthen'];
	        }
	    }
	    $check_tel_exit=$User->checkTelExit($tel);
	    if ($check_tel_exit){
	        return '该手机号码已被认证';
	    }
	    
	    
	    $oldtime=session('user.codetime');
	    
	    if ($oldtime + 65 >= time()) { //
	        return '60s内不能重新发送!';
	    }
	    
		require_once('./Api/sms/sms_send.php');
// 		$code=randomkeys(6);
        $code=mt_rand(100000,999999);
		$_SESSION["code"]=$code;
		

		$addStatus=$this->addTelToken($uid,$code);//插入数据库
		$UserPhoneAdd = $User->addPhone($uid,$tel);//插入验证的手机号码
		
		
		$msg=$this->msg.$code.$this->messages.$this->company;//【乐莘网络】 可以换成自己的签名，签名一般用公司或网站的简称
		if (!$msg){
		    return "验证码为空";
		}
		$msgcode=sendnote($tel,urlencode(mb_convert_encoding($msg, 'gbk' ,'utf-8')));//如果你网站或软件的格式是utf-8的，需要对发送的内容转成gbk的
		session('user.codetime',time());//60秒才可以发送一次
		if ($msgcode!=1){
		    return '请联系客服人员';
		}
		return true;
	}
	
	/**
	 * 
	 * @param number $uid
	 * @param string $token
	 * @return boolean
	 */
	public function addTelToken($uid=0,$token=""){
	    $tryselect=$this->getByUid($uid);
	    if ($tryselect===false){
	        return false;
	    }elseif ($tryselect!==0){
	        $delPhonecode=$this->delByUid($uid);
	        if ($delPhonecode!==true){
	            return false;
	        }
	    }
	    
	    $rules=array(
	    );
	    $auto=array(
	    );
	    $create['uid']=$uid;
	    $create['token']=$token;
	    $create['ctime']=current_datetime();
	    $checkcreate=$this->validate($rules)->auto($auto)->create($create);
	    if (!$checkcreate){
	        return false;
	    }
	    $rel = $this->add();
	    if (!$rel){
	        return false;
	    }
	    return true;
	}
	
	
	/**
	 * 根据uid获取一条信息
	 * @param number $uid
	 * @return boolean|Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function getByUid($uid=0){
	    if ($uid==0){
	        return false;
	    }
	    $resule = $this->where('uid=%d',$uid)->find();
	    if (!$resule){
	        return 0;
	    }
	    return $resule;
	}
	/**
	 * 根据uid删除uid记录
	 * @param number $uid
	 * @return boolean
	 */
	public function delByUid($uid=0){
	    if ($uid==0){
	        return false;
	    }
	    $delStatus=$this->where('uid=%d',$uid)->delete();
	    if (!$delStatus){
	        return false;
	    }
	    return true;
	}
	
	/**
	 * 检查验证码是否正确
	 * @param string $code
	 * @param number $uid
	 * @return boolean
	 */
	public function checktrue($code='',$uid=0){
	    if ($code==''||$uid==0){
	        return '验证码不能为空';
	    }
	    $codeInfo = $this->getByUid($uid);
	    $datacode = $codeInfo['token'];
	    if ($code!=$datacode){
	        return $code.'||'.$codeInfo['token'].'|1';
	        //$delcode=$this->delByUid($uid);
	        return '验证码错误';
	    }
	    $ctime = strtotime($codeInfo['ctime']); //变成时间戳
	    if ($ctime + 5 * 60 < time()) { //时间对比是否大于24小时
	        $delcode=$this->delByUid($uid);
	        return '已经超过了24小时，请重新发送激活验证码';
	    }
	    $delcode=$this->delByUid($uid);
	    $User=D('User');
	    $phonestatus=$User->phoneStatus($uid,1);
	    return true;
	}
	
	
	
}