<?php
namespace Common\Model;
use Common\Model\CommonModel;

/**
 *
 * @author user
 *
 */

class UserModel extends CommonModel {


    public $sendweibo='刚注册的 #17约课# 有点意思，移动网页端来的，不用下载app很方便的说。这个网站神奇的地方是学生们可以课程发布心愿单，号召更多的同学们跟约，引起培训机构的关注，从而为其推送课程。想要参加培训的同学们，艾特我，一起结伴去约课吧！（分享自@17约课）';

	//验证
	protected $_validate=array(
			array('firstname', 'require', '姓不能为空！', 1),
			array('firstname', '/^[\x{4e00}-\x{9fa5}]{1,2}$/u', '姓必须为1到2个中文', 1, 'regex'),

			array('lastname', 'require', '呢称不能为空！', 1),
			array('lastname', '/^[\x{4e00}-\x{9fa5}]{1,3}$/u', '呢称只能是4-15个字母或2-5个汉字', 1, 'regex'),

			array('email', 'require', '邮箱不能为空！', 0),
			array('email', 'is_email', '邮箱不合法', 0, 'function'),
			array('email', '', '邮箱已经被注册！', 0, 'unique'),

			array('phone', 'require', '手机号码不能为空！', 0),
// 			array('phone', '/^(1[3|5|8])[\d]{9}$/', '手机号码不合法', 0, 'regex'),
			array('phone', '/^(1)[\d]{10}$/', '手机号码不合法', 0, 'regex'),
			array('phone', '', '手机号码已经被注册！', 0, 'unique'),

			array('password', 'require', '密码不能为空！', 1, regex, 1),				 //注册通过  ,登录
			array('password', '/^\S{6,12}$/', '密码必须为6到12个字符', 1, 'regex', 1),



			array('age','/^([0-9]|[0-9]{2}|100)$/','年龄必须在0到100之间',2,'regex'),
			array('sex','/^([0-1]{1})$/','性别必须是男或女',2,'regex'),
			array('remark', '/^\S{1,60}$/','描述信息长度必须在1-20位之间！', 2, 'regex'),


	);


	protected $_auto = array (
			array('password', 'encrypt_passwd', 1, 'function'),
			array('ctime', 'current_datetime', 1, 'function')
	);

	/**
	 *
	 *  增加一个用户
	 *  firstname lastname email||phone password
	 * @return string|boolean
	 */
	public function addOne() {
		$typevalue=I('post.typevalue');
		$type=I('post.type');
		$checkTypeStatus=$this->checktype($typevalue,$type);//设置注册类型和值，email，phone
		if(checkTypeStatus===false){
			return "非法操作！";
		}
		//判断帐号 密码 格式是否正确，判断验证码是否正确
		$rules_reg=array(
// 		    array('firstname', 'require', '姓不能为空！', 1),
// 		    array('firstname', '/^[\x{4e00}-\x{9fa5}]{1,2}$/u', '姓必须为1到2个中文', 1, 'regex'),

		    array('lastname', 'require', '呢称不能为空！', 1),
// 		    array('lastname', '/^[\x{4e00}-\x{9fa5}]{2,5}$|^[a-zA-Z0-9]{4,15}$/u', '呢称只能是4-15个字母或2-5个汉字', 1, 'regex'),
// 		    array('lastname', '/^.{4,15}$/u', '呢称只能是4-15个字母或2-5个汉字', 1, 'regex'),
		    array('lastname', 'is_nickname', '呢称只能是4-15个字母或2-7个汉字', 1, 'function'),

		    array('email', 'require', '邮箱不能为空！', 0),
		    array('email', 'is_email', '邮箱不合法', 0, 'function'),
		    array('email', '', '邮箱已经被注册！', 0, 'unique'),

		    array('phone', 'require', '手机号码不能为空！', 0),
// 		    array('phone', '/^(1[3|5|8])[\d]{9}$/', '手机号码不合法', 0, 'regex'),
		    array('phone', '/^(1)[\d]{10}$/', '手机号码不合法', 0, 'regex'),
		    array('phone', '', '手机号码已经被注册！', 0, 'unique'),

		    array('password', 'require', '密码不能为空！', 1, regex, 1),				 //注册通过  ,登录
		    array('password', '/^\S{6,12}$/', '密码必须为6到12个字符', 1, 'regex', 1),



		    array('age','/^([0-9]|[0-9]{2}|100)$/','年龄必须在0到100之间',2,'regex'),
		    array('sex','/^([0-1]{1})$/','性别必须是男或女',2,'regex'),
		    array('remark', '/^\S{1,60}$/','心情长度必须在1-20位之间！', 2, 'regex'),

//		    array('verify','check_verify','验证码错误！',1,'function'), //默认情况下用正则进行验证
		);
		$auto_reg = array (
		    array('password', 'encrypt_passwd', 1, 'function'),
		    array('ctime', 'current_datetime', 1, 'function')
		);
		if (!$this->validate($rules_reg)->auto($auto_reg)->create()) {
			return $this->getError();
		}


/*
		$now_reg_num=0;
		$now_reg_num=S('user_reg_num');
		if (empty($now_reg_num)) {
			S('user_reg_num',1,86400);
		}elseif ($now_reg_num>2){
			$now_reg_num=$now_reg_num+1;
			S('user_reg_num',$now_reg_num,86400);
			return "恶意操作！";
		}else {
			$now_reg_num=$now_reg_num+1;
			S('user_reg_num',$now_reg_num,86400);
		}

		*/


		$userid=$this->add();
		if (!$userid) {
			return $this->getDbError();
		}
		if(!$this->userpath($userid)){//给新注册用户一个默认头像
			return $this->getDbError();
		}
// 		switch ($type) {//注册成功发送验证邮箱
// 			case 'email':
// 				$rel = D('Useractive')->sendActiveEmail($userid,$typevalue,'Userregsign/handleActive');
// 				break;
// 			case 'phone'://注册成功发送验证手机号码======待续，在后续的版本再开发-------
// 				$rel = trim($typevalue);
// 				break;
// 			default:
// 				break;
// 		}

		//注册成功自动登录
		$userinfo=$this->getUserInfo($userid);
		// 登陆成功，设置user的二维数组
		session('user.id',$userid);  						//设置session
		session('user.name',$userinfo['firstname'].$userinfo['lastname']);  	//设置session
		session('user.remark',$userinfo['remark']);  						//设置session
		session('user.profession',$userinfo['profession']);  						//设置session
		session('user.phone',$userinfo['phone']);  						//设置session
		session('user.email',$userinfo['email']);  						//设置session
		session('user.avatar',$userinfo['avatar']);  					//设置session
		session('user.telstatus',$userinfo['telstatus']);  					//设置session
		session('user.vtype',$userinfo['vtype']);  					//设置session
		session('user.vstatus',$userinfo['vstatus']);  					//设置session
		session('shopkeeper',null);
		session('shop_auto_login',null);


		//发送注册成功的系统消息
		$sysRegText='恭喜您已成功注册17约课http://17yueke.cn';
		D('UserSystem')->sendSysNew($userid,$sysRegText,0);

		//邀请码处理
		if (session('?reg.invitecode')){
            $code = session('reg.invitecode');
		    D('InviteGroup')->addInvite($userid,$code);
		}
		session('reg',null);
		return true;
	}

//=====================================================================
	//注册发送验证post.type    post.regtype
	/**
	 * 注册发送验证码
	 * @return string|unknown|boolean
	 */
	public function checkRegSend(){
	    $logintype = trim(I('post.typevalue'));//获取,并且去掉两端的空格
	    $inviteCode = trim(I('post.invitecode'));//获取,并且去掉两端的空格
// 	    $firstname = trim(I('post.firstname'));//获取,并且去掉两端的空格
// 	    $lastname  = trim(I('post.lastname'));//获取,并且去掉两端的空格
	    if (empty($logintype)) {
	        return '邮箱或者手机号码不能为空';
	    }
	    if (!empty($inviteCode)){
	        session('reg.invitecode',$inviteCode);
	    }
	    $type = trim(I('post.type'));//获取，并且去掉两端的空格
	    switch ($type){
	        case 'email':
	            $exitsEmailReg = $this->existemail($logintype);
	            if($exitsEmailReg){
	                $result = '邮箱已经被注册！';
	            }else {
	                $result = $this->sendRegEmail($logintype);
	            }
	            break;
	        case 'phone':
	            $exitsPhoneReg = $this->existphone($logintype);
	            if($exitsPhoneReg){
	                $result = '手机号码已经被注册！';
	            }else {
	                $result = $this->sendRegPhone($logintype);
	            }
	            break;
	        default:
	            return '所填写的帐号格式不对，请重新填写！';
	    }
	    if ($result!==true) {
	        return $result;
	    }
	    return true;
	}
	/**
	 * reg邮件验证码
	 * @param string $email
	 * @return Ambigous <boolean, string>
	 */
	public function sendRegEmail($email){
	    $oldtime=session('reg.codetime');
	    if ($oldtime + 60 >= time()) { //
	        return '60s内不能重新发送!';
	    }
	    $mun = mt_rand(100000, 999999);
	    $bady= '【17约课】尊敬的客户，您正在进行会员注册服务。验证码为<font style="color:#FF0000">'.$mun.'</font>，10分钟内验证有效，谢谢。';
	    $result = sendMail($email,$bady,'17约课');
	    if ($result!==true){
	        return '出错，请重新发送';//5011系统错误
	    }
// 	    session('reg.firstname',$firstname);
// 	    session('reg.lastname',$lastname);
	    session('reg.typevalue',$email);
	    session('reg.type','email');
	    session('reg.munber',$mun);
	    session('reg.time',time());
	    session('reg.codetime',time());//60秒才可以发送一次
	    return true;
	}

	/**
	 * 发送手机验证码--reg
	 * @param string $phone
	 * @return Ambigous <boolean, string>
	 */
	public function sendRegPhone($phone){
	    $msg1="尊敬的客户，您正在进行会员注册服务。短信验证码为";
	    $msg2="，10分钟内验证有效，谢谢。";
	    $company="【17约课】";
	    $oldtime=session('reg.codetime');
	    if ($oldtime + 60 >= time()) { //
	        return '60s内不能重新发送!';
	    }
	    require_once('./Api/sms/sms_send.php');
	    $code=mt_rand(100000,999999);
// 	    session('reg.firstname',$firstname);
// 	    session('reg.lastname',$lastname);
	    session('reg.typevalue',$phone);
	    session('reg.type','phone');
	    session('reg.munber',$code);
	    session('reg.time',time());
	    $msg=$msg1.$code.$msg2.$company;
	    if (!$msg){
	        return "验证码为空";
	    }
	    $msgcode=sendnote($phone,urlencode(mb_convert_encoding($msg, 'gbk' ,'utf-8')));//如果你网站或软件的格式是utf-8的，需要对发送的内容转成gbk的
	    session('reg.codetime',time());//60秒才可以发送一次
	    if ($msgcode!=1){
	        return '请联系客服人员';
	    }
	    return true;

	}
//=============================用户注册的验证================================================================================
	/**
	 * I('post.code')
	 * session('reg.munber')
	 * 判断注册的验证码是否正确
	 * @return boolean
	 */
	public function checkRegSendCodeNum(){
		$code = trim(I('post.code'));
		if (!session('?reg.invitecode')){
		    $inviteCode = trim(I('post.invitecode'));//获取,并且去掉两端的空格
		    if (!empty($inviteCode)){
		        session('reg.invitecode',$inviteCode);
		    }
		}
		$sessioncode=session('reg.munber');
		$changetime =session('reg.time')+600;
		$nowtime	=time();
		if ($changetime<=$nowtime) {
			return '验证码已过期，请重新发送验证码';
		}
		if ($code!=$sessioncode) {
			return '验证码不正确';
		}
		return true;
	}
//=============================用户注册的save===============================================================================
	/**
	 * 用户注册时--验证注册验证码，成功返回true，，失败返回错误信息
	 * 其中需要的post提交的数据为type,typevalue,password,code
	 * @return Ambigous <boolean, string>|Ambigous <string, boolean>|boolean
	 */
	public function regCheckAll(){
// 	    $ststus	   = $this->checkRegSendCodeNum();
// 	    if ($ststus!==true) {
// 	        session('reg',null);
// 	        return $ststus;
// 	    }


	    $_POST['type'] 	    = session('reg.type');
	    $_POST['typevalue'] = session('reg.typevalue');
	    $regStatusOne       = $this->addOne();
	    if ($regStatusOne!==true){
	        return $regStatusOne;
	    }
	    return true;
	}





//=========================================================登录===================
	/**
	 * 用户登录函数
	 * logintype 填写的登录帐号
	 * type      填写的登录类型email或者phone
	 * password  填写的登录密码
	 * @return string|boolean
	 */
	public function userLogin(){
		$logintype = trim(I('post.logintype'));//获取登录字符串，并且去掉两端的空格
		if (empty($logintype)) {
			return '邮箱或者手机号码不能为空';
		}
		$password  = trim(I('post.password'));//获取密码字符串，并且去掉两端的空格
		if (empty($password)) {
			return '密码不能为空';
		}
		$type = trim(I('post.type'));//获取登录帐号类型，并且去掉两端的空格
		$this->checktype($logintype,$type); //判断登录类型，是邮箱还是手机，并且设置post表单的email和phone
		//自定义动态验证表单规则
		$rules = array(
				array('email','is_email','邮箱格式不正确！',0,'function'),
// 				array('phone','/^(1[3|5|8])[\d]{9}$/', '手机号码不合法', 0, 'regex'),
				array('phone','/^(1)[\d]{10}$/', '手机号码不合法', 0, 'regex'),
				array('password','6,12','密码格式不正确',1,'length')
		);
		//自定义动态的完成表单规则
		$auto =array (
			array('password', 'encrypt_passwd', 1, 'function'),
		);
		//创建表单对象， 如果创建失败 表示验证没有通过 输出错误提示信息
		if(!$this->validate($rules)->auto($auto)->create()){
			return $this->getError();
		}
		$pw=$this->password;   //把post获取后md5的密码赋值给$pw用于后续密码对比
		//判断是登录类型来执行相应的登录方式
		if ($type=='email') {
			$row = $this->loginByEmail($logintype);
			if (empty($row)) {
				return '该邮箱不存在';
			}
		}elseif ($type=='phone'){
			$row = $this->loginByPhone($logintype);
			if (empty($row)) {
				return '该手机号码不存在';
			}
		}else {
			return "I don\'t know";
		}
		//判断登录密码时候正确
		if ($pw!=$row['password']) {
			return '密码错误';
		}
		$rel = $this->logintime($row['id']);
		$userinfo=$this->getUserInfo($row['id']);
		// 登陆成功，设置user的二维数组
		session('user.id',$row['id']);  						//设置session
		session('user.name',$userinfo['firstname'].$userinfo['lastname']);  	//设置session
		session('user.remark',$userinfo['remark']);  						//设置session
		session('user.profession',$userinfo['profession']);  						//设置session
		session('user.phone',$userinfo['phone']);  						//设置session
		session('user.email',$userinfo['email']);  						//设置session
		session('user.avatar',$userinfo['avatar']);  					//设置session
		session('user.telstatus',$userinfo['telstatus']);  					//设置session
		session('user.vtype',$userinfo['vtype']);  					//设置session
		session('user.vstatus',$userinfo['vstatus']);  					//设置session
		session('shopkeeper',null);
		session('shop_auto_login',null);
		$this->setUsercookise($row['id']);
		return true;
	}
	//设置cookise功能
	public function setUsercookise($id=0){
		$cookisename=I('post.remember');
		if ($cookisename=='true') {
			$key	 = C('basekey');
			$bstring = C('basestr');
			$id	 	 = $bstring.$id;
			$encrypt = encrypt($id, $key);//加密
			cookie('userid',$encrypt,2592000);//60*60*24*30
		}
	}
	//cookise登录
	public function logincookise($cookiseid){
		$key	 = C('basekey');
		$bstring = C('basestr');
    	$decrypt = decrypt($cookiseid, $key);//解密
    	$uid	 = substr($decrypt,32);//32位之后的字符就是uid

// 		$rel=$this->where('id=%d',$uid)->find();

		$rel=$this->getUserInfo($uid);

		if ($rel) {
    		// 登陆成功，设置user的二维数组
    		session('user.id',$rel['id']);  						//设置session
    		session('user.name',$rel['firstname'].$rel['lastname']);  	//设置session
    		session('user.remark',$rel['remark']);  						//设置session
    		session('user.profession',$rel['profession']);  						//设置session
    		session('user.phone',$rel['phone']);  						//设置session
    		session('user.email',$rel['email']);  						//设置session
    		session('user.avatar',$rel['avatar']);  					//设置session
    		session('user.telstatus',$rel['telstatus']);  					//设置session
    		session('user.vtype',$rel['vtype']);  					//设置session
    		session('user.vstatus',$rel['vstatus']);  					//设置session
    		session('shopkeeper',null);
			session('shop_auto_login',null);
			$this->logintime($rel['id']);
			$this->setUsercookise($rel['id']);
			return true;
		}
		return false;
	}

	/**
	 * 用户邮箱登录查询数据
	 * @param string $email
	 * @return Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function loginByEmail($email=''){
		$row=$this->where("email='%s'",$email)->field(array('id','password'))->find();
		return $row;
	}

	/**
	 * 用户手机号码登录查询数据
	 * @param number $phone
	 * @return Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function loginByPhone($phone=0){
		$row=$this->where("phone ='%s'", $phone)->field(array('id','password'))->find();
		return $row;
	}

	/**
	 * 更新用户登录时间
	 * @param number $id
	 * @return unknown
	 */
	public function logintime($id=0){
		$data=array('lasttime'=>date('Y-m-d H:i:s'));
		$res = $this->where("id=%d",$id)->filter('strip_tags')->setField($data);
		return  $res;
	}

	/**
	 * 检查某个邮箱是否存在
	 * @param string $emailvalue
	 * @return boolean
	 */
	public function existemail($emailvalue=''){
		$exist=$this->where("email='%s'",$emailvalue)->getField('id');
		if(!$exist){
			return false;
		}
		return true;
	}
	/**
	 * 检查某个手机号码是否存在
	 * @param string $emailvalue
	 * @return boolean
	 */
	public function existphone($phonevalue=''){
		$exist=$this->where("phone='%s'",$phonevalue)->getField('phone');
		if(!$exist){
			return false;
		}
		return true;
	}

	/**
	 * 判断登录类型，并且设置post表单的email和phone，
	 * @param unknown $typevalue值
	 * @param unknown $type类型
	 * @return string
	 */
	public function checktype($typevalue,$type){
		// 判断登录类型是邮箱登录还是手机号码登录，并且赋值给对应的表单字段
		switch ($type) {
			case 'email':
				$_POST['email'] = trim($typevalue);
				break;
			case 'phone':
				$_POST['phone'] = trim($typevalue);
				break;
			default:
				return false;
		}
		return true;
	}





//============================================================================================
	/**
	 * POST传递的值logintype
	 * 检查是发送邮件or短信 来找回密码,sendtype
	 * @return string|boolean
	 */
	public function checkSend(){
		$logintype = trim(I('post.sendtype'));//获取找回密码字符串，并且去掉两端的空格
		if (empty($logintype)) {
			return '邮箱或者手机号码不能为空';
		}
		$type = trim(I('post.type'));//获取找回密码的帐号类型，并且去掉两端的空格
		switch ($type){
			case 'email':
				$result = $this->sendEmail($logintype);
				break;
			case 'phone':
				$result = $this->sendPhone($logintype);
				break;
			default:
				return '所填写的帐号格式不对，请重新填写！';
		}
		if ($result!==true) {
			return $result;
		}
		return true;
	}


	/**
	 * 发送邮件验证码找回密码
	 * @param string $email
	 * @return Ambigous <boolean, string>
	 */
	public function sendEmail($email){
		$relexist=$this->existemail($email);
		if ($relexist===false) {
			return '发送失败，不存在该邮箱帐号';//发送失败，不存在该邮箱帐号502
		}
		$mun = mt_rand(100000, 999999);
		$bady= '【17约课】尊敬的客户，您正在进行找回登录密码服务。验证码为<font style="color:#FF0000">'.$mun.'</font>，10分钟内验证有效，谢谢。';
		$result = sendMail($email,$bady,'17约课');
		if ($result!==true){
		    return '出错，请重新发送';//5011系统错误
		}
		session('forgetpw.changetypevalue',$email);
		session('forgetpw.changetype','email');
		session('forgetpw.munber',$mun);
		session('forgetpw.time',time());
		return true;
	}


	/**
	 * 发送手机验证码找回密码
	 * @param string $phone
	 * @return Ambigous <boolean, string>
	 */
	public function sendPhone($phone){
		$relexist=$this->existphone($phone);
		if (!$relexist) {
			return '不存在该手机号码';
		}
	    $msg1="尊敬的客户，您正在进行找回密码服务。短信验证码为";
	    $msg2="，10分钟内验证有效，谢谢。";
		$company="【17约课】";
		$oldtime=session('forgetpw.codetime');
		if ($oldtime + 65 >= time()) { //
			return '60s内不能重新发送!';
		}
		require_once('./Api/sms/sms_send.php');
		$code=mt_rand(100000,999999);
		session('forgetpw.changetypevalue',$phone);
		session('forgetpw.changetype','phone');
		session('forgetpw.munber',$code);
		session('forgetpw.time',time());
		$msg=$msg1.$code.$msg2.$company;
		if (!$msg){
			return "验证码为空";
		}
		$msgcode=sendnote($phone,urlencode(mb_convert_encoding($msg, 'gbk' ,'utf-8')));//如果你网站或软件的格式是utf-8的，需要对发送的内容转成gbk的
		session('forgetpw.codetime',time());//60秒才可以发送一次
		if ($msgcode!=1){
			return '请联系客服人员';
		}
		return true;

	}
	/**
	 * I('post.code')
	 * session('forgetpw.munber')
	 * 判断找回密码的验证码是否正确
	 * @return boolean
	 */
	public function checksendcodemun(){
		$code = trim(I('post.code'));
		$sessioncode=session('forgetpw.munber');
		$changetime =session('forgetpw.time')+300;
		$nowtime	=time();
		if ($changetime<=$nowtime) {
			return '验证码已过期，请重新发送验证码';
		}
		if ($code!=$sessioncode) {
			return '验证码不正确';
		}
		return true;
	}

	/**
	 * 更改用户密码 ---- 找回密码方法判断
	 * @return Ambigous <boolean, unknown>|boolean
	 */
	public function changpw(){
		$ststus	   = $this->checksendcodemun();
		if ($ststus!==true) {
			session('forgetpw',null);
			return $ststus;
		}
		$type 	   = session('forgetpw.changetype');
		$typevalue = session('forgetpw.changetypevalue');

		$typestatus= $this->checktype($typevalue,$type);
		if (!$typestatus) {
			session('forgetpw',null);
			return $typestatus;
		}
		$ruless = array(
				array('password', '/^\S{6,12}$/', '密码必须为6到12个字符', 1, 'regex', 1),
		);
		$rules = array (
				 array('password','encrypt_passwd',3,'function') , // 对password字段在新增和编辑的时候使md5函数处理
		);
		$resule=$this->field('password')->validate($ruless)->auto($rules)->create();
		$password=$this->password;
		if (!$resule) {
			session('forgetpw',null);
			return $this->getError();
		}
		switch ($type){
			case 'email':
				$checkrel=$this->where("email='%s'",$typevalue)->field('password')->find();
				if ($checkrel['password']!=$password) {
					$rel = $this->where("email='%s'",$typevalue)->setField('password',$password);
					session('forgetpw',null);
					break;
				}else {
					$rel='请设置其它密码';
					break;
				}
			case 'phone':
				$checkrel=$this->where("phone='%s'",$typevalue)->field('password')->find();
				if ($checkrel['password']!=$password) {
					$rel = $this->where("phone='%s'",$typevalue)->setField('password',$password);
					session('forgetpw',null);
					break;
				}else {
					$rel='请设置其它密码';
					break;
				}
			default:
				return '重置密码错误，请重新重置';
		}
		if ($rel!=true) {
			return $rel;
		}else {
 			session('forgetpw',null);
			return true;
		}
	}



	/**
	 * 处理注销
	 */
	public function userLogout() {
		session("user", null); //设置session,删除session中的user二维数组的整个数组
		cookie('userid',null);
		return true;
	}






	/**
	 * 激活某个用户邮箱
	 * @param int $id
	 */
	public function actOne($id) {
		$id = intval($id);
		$status = $this->where('id = %d', $id)->getField('estatus');
		// 判断status，只有当status为0（未激活）时才能激活
		if (is_null($status)) {  //是否为空
			return false;
		}
		if ($status != 0) {  //是否为0
			return false;
		}
		// 激活用户
		$status = $this->where('id = %d', $id)->setField('estatus', 1);

		return true;
	}



	/**
	 * 检查手机验证状态,并且获取认证的手机号码
	 * @param number $uid
	 * @return boolean|Ambigous <\Think\mixed, NULL, mixed, multitype:Ambigous <unknown, string> unknown , unknown, object>
	 */
	public function checkTelStatus($uid=0){
	    if ($uid==0){
	        return false;
	    }
	    $telStatus=$this->where('id=%d',$uid)->field('telstatus,telauthen')->find();
	    return $telStatus;
	}


	/**
	 * 验证手机认证号码是否存在
	 * @param string $tel
	 * @return boolean|Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function checkTelExit($tel=''){
	    if (!$tel){
	        return false;
	    }
	    $telStatus=$this->where("telauthen='%s'",$tel)->field('id')->find();
	    return $telStatus;
	}


	/**
	 * 更改手机验证状态
	 * @param string $phone
	 * @return Ambigous <boolean, string>
	 */
	public function phoneStatus($uid=0,$status=0){
	    $data= array(
	        'telstatus'=>$status,
	    );
	    $rel = $this->where('id=%d',$uid)->setField($data);
	    if (!$rel){
	        return $this->getDbError();
	    }
	    session('user.telstatus',$status);  					//设置session
	    return true;
	}

	/**
	 * 添加认证的手机号码
	 * @param number $uid
	 * @param number $phone
	 * @return string|boolean
	 */
	public function addPhone($uid=0,$phone=0){
	    $data= array(
	        'telauthen'=>$phone,
	    );
	    $rel = $this->where("id='%s'",$uid)->setField($data);
	    if (!$rel){
	        return $this->getDbError();
	    }
	    return true;
	}








//==后台=====

	/**
	 * 后台的用户管理页面
	 * 返回用户的详细信息
	 * @param number $pageOffset
	 * @param number $perPage
	 * @param string $sort
	 * @return string
	 */
	public function userinfo($pageOffset=0,$perPage=2,$sort='asc'){
		//要查询的字段
		$array = array(
				'id',
				'firstname',
				'lastname',
				'phone',
				'email',
				'ctime',
				'lasttime'
		);
		$order = 'id '.$sort;
		$res=$this  ->where("(email<>'') or (phone<>'')")
		            ->order($order)
					->limit($pageOffset,$perPage)
					->field($array)
					->select();
		return $res;
	}


	/**
	 * 获取用户详细信息
	 * @param number $id
	 * @return Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function userdetails($id=0){
		$rel=$this->where("id=%d",$id)->find();
		return $rel;
	}




	/**
	 * 返回总用户数
	 * @return $res
	 */
	public function usercount(){
		$res = $this->where("(email<>'') or (phone<>'')")->order('id')->count();
		return  $res;
	}


	/**
	 * 用户分页数据
	 * 返回分页信息
	 * @param number $curPage
	 * @param number $perPage
	 * @return array $page
	 */
	public function userpage($curPage=1,$perPage=5){
		import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
		$count= $this->usercount(); // 查询总记录数
		$Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
		$pageArray=$Page->getCounts();
		return  $pageArray;
	}






	/**
	 * 获取用户头像的路径
	 * @param number $id
	 * @return Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function useravatar($id=0){
		$userpath=$this->where("id=%d",$id)->field(array('id','avatar'))->find();
		$userfilepath=$userpath['avatar'];
		if ($userfilepath) {
			return $userfilepath;
		}
		return false;
	}


	/**
	 * 更新用户的头像路径
	 * $id
	 * $data=array() 要更新的头像信息
	 * @param int $id
	 * @param string $data
	 * @return 0or1
	 */
	public function usersave($id=0,$data=''){
		$data=array('avatar'=>$data);
		$res = $this->where("id=%d",$id)->filter('strip_tags')->setField($data);
		return  $res;
	}




	/**
	 * 给用户一个默认头像
	 * @param number $id
	 * @return boolean
	 */
	public function userpath($id=0){
		$allpic = $this->traverse();
		$key	= array_rand($allpic,1);
		$munpic = $allpic[$key];
		$munpic = substr($munpic, 1);//去掉首字符的.号
		$relss  = $this->usersave($id,$munpic);
			if($relss){
				return true;
			}else {
				return false;
			}
	}



	/**
	 * 获取默认头像目录下的文件，并把获取到的文件名组合成一个数组$k
	 * 其中默认头像的目录为./Public/Uploads/avatar/
	 * @param string $path
	 * @return string
	 */
	public function traverse($path = './Public/Uploads/avatar/') {
         $current_dir = opendir($path);    //opendir()返回一个目录句柄,失败返回false
         while(($file = readdir($current_dir)) !== false) {    //readdir()返回打开目录句柄中的一个条目
             $sub_dir = $path . '/' . $file;    //构建子目录路径
             if($file == '.' || $file == '..') {
                 continue;
             } else {    //如果是文件,直接输出
             	$k[]=$path . $file;
             }
         }
         return $k;
    }


    /**
     * 判断当前的头像是否为默认头像，
     * @param string $path 路径
     * @return boolean|是默认头像则返回true|不是默认头像则返回false
     */
    public function checkpath($path='./Public/Uploads/avatar/1.jpg'){
    	$array=$this->traverse();
    	$arrlength=count($array);
    	for($x=0;$x<$arrlength;$x++) {
    		if ($path==$array[$x]) {
    			return true;
    			break;
    		}
    	}
    	return false;
    }






    /**
     * 用户上传用户头像and删除用户自己上传的头像
     * @return multitype:number string |multitype:number Ambigous <multitype:, unknown, boolean, multitype:mixed string >
     */
    public function uploadavatar($id=0){
    	$user_avatar=C('user_avatar');
    	$upload = new \Think\Upload();// 实例化上传类
    	$upload->maxSize   =     2145728 ;// 设置附件上传大小
    	$upload->exts      =     array('jpg', 'png', 'jpeg');// 设置附件上传类型
    	$upload->mimes     =     array('image/jpg', 'image/jpeg', 'image/png', 'application/x-MS-bmp', 'image/nbmp', 'image/vnd.wap.wbmp');
    	$upload->rootPath  =      '.'.$user_avatar.'/'; // 设置附件上传目录
    	$upload->savePath  =     '';
    	$upload->autoSub = true;
    	$upload->subName = array('date','Y/m/d');
    	// 上传文件
    	$info   =   $upload->uploadOne($_FILES['useravatar']);
    	if(!$info) {// 上传错误提示错误信息
    		return array($upload->getError(),0);
    	}else{// 上传成功
    		$unavatar=$this->useravatar($id);
    		$rel = $this->unUserAvatar($unavatar);

//     		if (!$rel) {
//     			$savepath  = $user_avatar.'/'.$info['savepath'].$info['savename'];
//     			$rel1 = $this->unUserAvatar($savepath);
//     			return array('上传失败',0);
//     		}

    		return array(0,$info);
    	}
    }


    /**
     * 裁剪图片75*75，$path是完整的路径，带.的，如./xx/xxx.jpg
     * @param string $path
     * @return \Think\Image|boolean
     */
    public function imagecut($path=''){
        $user_avatar=C('user_avatar');
        $pathname=date("Y/m/d");
        $imgname=uniqid();
        $thumb='.'.$user_avatar.'/'.$pathname.'/'.$imgname.'.jpg';
        $image = new \Think\Image();
        $image->open($path);
        // 生成一个固定大小为75*75的缩略图并保存为thumb.jpg
        $imageInfo=$image->thumb(75, 75,\Think\Image::IMAGE_THUMB_FIXED)->save($thumb);
        if (!$imageInfo){
            return false;
        }
        $unimagestatus=unlink($path);		//删除//
        $thumbdeldian=$user_avatar.'/'.$pathname.'/'.$imgname.'.jpg';
        return $thumbdeldian;
    }


    /**
     * 删除用户头像  and 判断是否是默认头像
     * @param unknown $id
     * @return boolean
     */
	public function unUserAvatar($userpath=''){
		if (!$userpath) {
			return false;
		}
    	$userfilepath   =  '.'.$userpath;		//定义为.的路径形式
    	$checkava		=	$this->checkpath($userfilepath);//判断是否是默认头像
    	if ($checkava) {
    		return true;
    	}
		$unstatus=unlink($userfilepath);		//删除
    	if ($unstatus) {
    		return true;
    	}
    	return false;
	}


	/**
	 * 获取总的注册用户数
	 * @return Ambigous <\Think\mixed, boolean, string, NULL, multitype:, unknown, mixed, object>
	 */
	public function userAll(){
		$rel = $this->count();
		if ($rel) {
			return $rel;
		}else {
			return false;
		}
	}


	/**
	 * 用户信息编辑，
	 * 获取用户的信息到页面显示并且修改
	 * @return boolean|Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function myeditor(){
		$uid = session('user.id');
		$array = array(
				'id',
				'sex',
				'age',
				'firstname',
				'lastname',
				'interest',
				'remark',
				'school',
				'home',
				'profession',
				'avatar',
		);
		$info = $this->where('id=%d',$uid)->field($array)->find();
		if (!$info) {
			return false;
		}
		$info['degree'] = $this->degreeNum($info);
		$info['lastname'] = $info['firstname'].$info['lastname'];
		return $info;
	}

	/**
	 * 获取当前登录的用户的资料评分
	 * @return boolean|number
	 */
	public function userScore(){
	    $user_info=$this->myinfoall();
	    if($user_info===false){
	        return false;
	    }
	    $score_num=$this->degreeNum($user_info);
	    return $score_num;
	}

	/**
	 * 当前用户头像是否已经上传了，上传就true，根据session与配置文件
	 * @param number $uid
	 * @return boolean
	 */
	public function userAvatarBysession(){
	    $useravatar=session('user.avatar');
	    $def_avatar=C('default_avatar');
	    foreach ($def_avatar as $defkey=>$defvalue){
	        if ($defvalue==$useravatar){
	            return false;
	            break;
	        }
	    }
	    return true;
	}

	/**
	 * 是否已经上传了头像，上传了就true----根据默认头像的文件夹判断
	 * @param number $uid
	 * @return boolean
	 */
	public function userAvatars($uid=0){
	    $infoAvatar = $this->where('id=%d',$uid)->getField('avatar');

	    $status = $this->checkpath('.'.$infoAvatar);
	    if(!$status){
	        return true;
	    }
	    return false;
	}


	/**
	 * 资料完善度计算
	 * @param unknown $info
	 * @return number
	 */
	public function degreeNum($info=array()){
		$i	= 0;
		$status = $this->checkpath('.'.$info['avatar']);
		if (!$status) {
			$i=$i+30;
		}
		if ($info['age']!=0) {
			$i=$i+10;
		}
// 		if ($info['firstname']!='') {
// 			$i=$i+5;
// 		}
		if ($info['lastname']!='') {
			$i=$i+10;
		}
		if ($info['interest']!='') {
			$i=$i+10;
		}
		if ($info['remark']!='') {
			$i=$i+10;
		}
		if ($info['school']!='') {
			$i=$i+10;
		}
		if ($info['home']!='') {
			$i=$i+10;
		}
		if ($info['profession']!=0) {
			$i=$i+10;
		}
		return $i;
	}


	/**
	 * 用户完善资料，保存完善的信息
	 * @return string|boolean
	 */
	public function saveInfo(){
		$uid	= session('user.id');
		$imgsrc	= I('post.imgsrc');
		$sex	= I('post.sex');
		$sexnan='/Public/Uploads/avatar/1.jpg';
		$sexnv ='/Public/Uploads/avatar/2.jpg';
		if ($imgsrc==$sexnan||$imgsrc==$sexnv){
		    $_POST['avatar']=$imgsrc;
		    session('user.avatar',$imgsrc);
		}else {
    	    $imgpath= $this->useravatar($uid);
    		if ($imgsrc!=$imgpath) {
    			$avaarray=$this->uploadavatar($uid);
    			if ($avaarray[1]==0) {//上传失败
    				return $avaarray[0];
    			}
    			$avatarinfo=$avaarray[1]; //上传头像的信息
    			$user_avatar=C('user_avatar');
    			$savepath  = '.'.$user_avatar.'/'.$avatarinfo['savepath'].$avatarinfo['savename'];
    			$saveThumb = $this->imagecut($savepath);//略缩图
    			if ($saveThumb===false){
    			    return false;
    			}
    			$_POST['avatar']=$saveThumb;
    		}
		}
		$relus	= array(
// 			array('firstname', 'require', '姓不能为空！', 1),
// 			array('firstname', '/^[\x{4e00}-\x{9fa5}]{1,2}$/u', '姓必须为1到2个中文', 1, 'regex'),

			array('lastname', 'require', '呢称不能为空！', 1),
// 			array('lastname', '/^[\x{4e00}-\x{9fa5}]{2,5}$|^[a-zA-Z0-9]{4,15}$/u', '呢称只能是4-15个字母或2-5个汉字', 1, 'regex'),
		    array('lastname', 'is_nickname', '呢称只能是4-15个字母或2-7个汉字', 1, 'function'),
// 			array('lastname', '/^[\s\S]{4,15}$/u', '呢称只能是4-15个字母或2-7个汉字', 1, 'regex'),

			array('age','/^([0-9]|[0-9]{2}|100)$/','年龄必须在0到100之间',2,'regex'),
			array('sex','/^([0-2]{1})$/','性别必须是男或女',2,'regex'),
			array('interest', '/^.{1,93}$/','爱好信息长度必须在1-30位之间！', 2, 'regex'),
			array('remark', '/^.{1,93}$/','签名信息长度必须在1-30位之间！', 2, 'regex'),
			array('school','/^[\x{4e00}-\x{9fa5}]{1,20}$/u','学校名称不合法1~20',2,'regex'),
			array('home','/^.{1,93}$/','家乡名称不正确1~30',2,'regex'),
			array('profession','/^[0-9]{1}$/','专业不正确0~9',2,'regex'),
		);
		$auto	= array();
		$result	= $this->field('lastname,age,sex,interest,remark,school,home,profession,avatar')->validate($relus)->auto($auto)->create();
		if (!$result) {
			return $this->getError();
		}
		$this->firstname='';
		$rel	= $this->where('id=%d',$uid)->field('firstname,lastname,age,sex,interest,remark,school,home,profession,avatar')->save();

		$userarray=$this->getUserInfo($uid);
        session('user.name',$userarray['firstname'].$userarray['lastname']);  	//设置session
        session('user.remark',$userarray['remark']);  						//设置session
        session('user.profession',$userarray['profession']);  						//设置session
        session('user.phone',$userarray['phone']);  						//设置session
        session('user.email',$userarray['email']);  						//设置session
        session('user.avatar',$userarray['avatar']);  					//设置session
        session('user.telstatus',$userarray['telstatus']);  					//设置session
        session('user.vtype',$userarray['vtype']);  					//设置session
        session('user.vstatus',$userarray['vstatus']);  					//设置session

		return true;
	}

    /**
     * 用户通过AJAX上传头像
     * $_FILES['useravatar']
     * @return Ambigous <number, string, number, Ambigous <multitype:, unknown, boolean, multitype:mixed string >>|boolean
     */
	public function setUserAvatar(){
	    $uid = session('user.id');
        $avaarray=$this->uploadavatar($uid);
        if ($avaarray[1]==0) {//上传失败
        	return $avaarray[0];
        }
        $avatarinfo=$avaarray[1]; //上传头像的信息
        $user_avatar=C('user_avatar');
        $savepath  = '.'.$user_avatar.'/'.$avatarinfo['savepath'].$avatarinfo['savename'];
        $saveThumb = $this->imagecut($savepath);//略缩图
        if ($saveThumb===false){
            return false;
        }
        $uploadstatus=$this->where('id=%d',$uid)->setField('avatar',$saveThumb);
        if (!$uploadstatus){
            $unUploadThumb = $this->unUserAvatar($saveThumb);
            return '头像上传失败，请稍后重试！';
        }
        return true;
	}

	/**
	 * 用户详细信息界面
	 * 查询用户的全部信息，除了密码，时间
	 * 并且获取该用户发布约课的总数，最新一条约课的信息,商家的场景图
	 * find错误返回false null
	 * @return boolean|Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function myInfo($uid=0){
	    $alias	= 'u';
	    $join	= array(
	        'left join __USER_V__ uv on uv.uid = u.id',
	        'left join __GROUP_INFO__ gi on gi.uid = u.id',
	        'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = gi.sid',
	    );
	    $array	= array(
	        'u.id',
	        'u.firstname',
	        'u.lastname',
	        'u.age',
	        'u.sex',
	        'u.remark',
	        'u.school',
	        'u.home',
	        'u.interest',
	        'u.profession',
	        'u.avatar',
	        'u.telstatus',
	        'uv.vstatus',
	        'uv.vtype',
	        'gi.id as gid',
	        'gi.uid',
	        'gi.title',
	        'skd.sid',
	        'skd.environ'
	    );
	    $where = 'u.id='.$uid;
	    $order = 'gi.id desc';
	    $info	= $this     -> alias($alias)
                    	    -> join($join)
                    	    -> where($where)
                    	    -> order($order)
                    	    -> field($array)
                    	    -> find();
	    if (!$info) {
	        return false;
	    }
	    return $info;
	}



	/**
	 * 用户详细信息界面====用于首页
	 * find错误返回false null
	 * @return boolean|Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function getUserInfo($uid=0){
	    $alias	= 'u';
	    $join	= array(
	        'left join __USER_V__ uv on uv.uid = u.id',
	    );
	    $array = array(
	        'u.id',
	        'u.firstname',
	        'u.lastname',
	        'u.phone',
	        'u.profession',
	        'u.remark',
	        'u.avatar',
	        'u.age',
	        'u.telstatus',
	        'uv.vtype',
	        'uv.vstatus',
	    );
	    $where = 'u.id='.$uid;
	    $info	= $this     -> alias($alias)
                    	    -> join($join)
                    	    -> where($where)
                    	    -> field($array)
                    	    -> find();
	    if (!$info) {
	        return false;
	    }
	    return $info;
	}



	/**
	 * 查询用户的全部信息，除了密码，时间
	 * find错误返回false null
	 * @return boolean|Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
	 */
	public function myinfoall(){
		$uid	= session('user.id');
		$info	= $this-> where('id=%d',$uid)-> field('password,ctime,lasttime',true)-> find();
		if (!$info) {
			return false;
		}
		return $info;
	}







// 	/**
// 	 * 更新用户详细资料
// 	 * 表单中useravatar,firstname,lastname,age,sex,remark,school,home,prof,useravatar,interest[],ctime
// 	 * @return Ambigous <number, string, number, Ambigous <multitype:, unknown, boolean, multitype:mixed string >>|string|Ambigous <boolean, unknown>|boolean
// 	 */
// 	public function perfectUser(){

// 		$_POST['interest']=implode(',',$_POST['interest']);

// 		$avaarray=$this->uploadavatar($_POST['id']);
// 		if ($avaarray[1]==0) {
// 			return $avaarray[0];
// 		}
// 		$avatarinfo=$avaarray[1]; //上传头像的信息
// 		$user_avatar=C('user_avatar');
// 		$savepath  = $user_avatar.'/'.$avatarinfo['savepath'].$avatarinfo['savename'];
// 		$_POST['avatar']=$savepath;

// 		if (!$this->field('firstname,lastname,age,sex,remark,school,home,prof,avatar,interest,ctime')->create()) {
// 			return $this->getError();
// 		}
// 		$userid=$this->where("id='%d'",$_POST['id'])->filter('strip_tags')->save();

// return $userid;

// 		$userid=$this->add();
// 		if (!$userid) {
// 			return $this->getDbError();
// 		}
// 		if(!$this->userpath($userid)){
// 			return $this->getDbError();
// 		}
// 		return true;

// 	}


	/**
	 * 获取某个用户的密码
	 * @param number $uid
	 * @return unknown
	 */
	public function userFindPw($uid=0) {
		$rel	= $this->where('id=%d',$uid)->getField('password');
		if (!$rel) {
			return $this->getDbError();
		}
		return $rel;
	}

	/**
	 * 用户更换密码
	 * @return string|boolean
	 */
	public function userResetPw() {
		$uid	= session('user.id');
		$relus	= array(
				array('password', 'require', '密码不能为空！', 1, regex, 1),				 //注册通过  ,登录
				array('password', '/^\S{8,12}$/', '密码必须为8到12个字符', 1, 'regex', 1),
		);
		$auto	= array(
				array('password', 'encrypt_passwd', 1, 'function'),
		);
		$result	= $this->field('password')->validate($relus)->auto($auto)->create();
		if (!$result) {
			return $this->getError();
		}
		$oldPw	= $this->userFindPw($uid);//获取old原来的密码
		if ($oldPw==$this->password) {
			return '密码不能与旧密码一致!';
		}
		$rel	= $this->where('id=%d',$uid)->filter('strip_tags')->setField('password',$this->password);
		if (!$rel) {
			return $this->getDbError();
		}
		return true;
	}



	/**
	 * 用户消息中，获取用户报名的商家课程的信息，
	 * @param unknown $curPage
	 * @param unknown $perPage
	 * @param unknown $uid
	 * @param string $order
	 */
	public function shopEnrollNews($curPage=1,$perPage=2,$order='desc'){
		$uid    = session('user.id');
		$name   = session('user.name');
		$avatar = session('user.avatar');
		$page = $this->enrollPage($curPage, $perPage, $uid);
		$alias = 'u';//定义当前数据表的别名
		//要查询的字段
		$array = array(
		      'u.id as userid',
		      'siu.ctime',
		      'siu.shop_info_id as shopid',
		      'siu.user_id',
		      'si.environ',
		      'si.title',
		);
		$join  = array(
				'__SHOP_INFO_USER__ siu on siu.user_id = u.id',
				'__SHOP_INFO__ si on siu.shop_info_id = si.id',
		);

		$where='u.id='.$uid.' AND siu.user_id = u.id AND siu.shop_info_id = si.id';
		$order='siu.ctime '.$order;
		$res=$this-> alias($alias)
                		-> join($join)
                		-> where($where)
                		-> order($order)
                		-> limit($page['pageOffset'],$page['perPage'])
                		-> field($array)
                		-> select();
		foreach ($res as $key=>$value){
		    $res[$key]['name'] = $name;
		    $res[$key]['avatar'] = $avatar;
		    $res[$key]['ctime'] = transDate(date("Y-m-d H:i:s",$value['ctime']));
		}
		return $res;
	}
	/**
	 * 用户动态课程中的对用户报名商家课程的信息进行分页，用户报名商家的表名为shop_info_user
	 * @param unknown $curPage
	 * @param unknown $perPage
	 * @param unknown $uid
	 * @return Ambigous <\Common\Util\multitype:number, multitype:number >
	 */
	public function enrollPage($curPage,$perPage,$uid){
	    $shopInfoUser = M('shop_info_user');
		$count= $shopInfoUser->where('user_id=%d',$uid)->count(); // 查询总记录数
	    import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
		$Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
		$pageArray=$Page->getCounts();
		return  $pageArray;
	}

	/**
	 * 用户动态课程中的，获取用户报名的商家课程的信息，
	 * @param unknown $curPage
	 * @param unknown $perPage
	 * @param unknown $uid
	 * @param string $order
	 */
	public function shopEnrollCourse($uid=0,$curPage=1,$perPage=2,$order='desc'){
		$page = $this->enrollPage($curPage, $perPage, $uid);
	
		$alias = 'u';//定义当前数据表的别名
		//要查询的字段
		$array = array(
		    'u.id as userid',//用户id
		    'u.firstname',
		    'u.lastname',
		    'u.avatar',
		    'skd.nickname',
		    'skd.avatar as skavatar',
		    'siu.shop_info_id as shopid',//课程id
		    'siu.user_id',
		    'si.id',//课程id
		    'si.title',
		    'si.sid',
		    'si.cateid',
		    'si.title',
		    'si.areaid',
		    'si.price',
		    'si.mode',
		    'si.tags',
		    'si.ctime',
		    'si.view',
		    'si.phone_tel',

		    'si.overtime',
		    'si.preferent',
		    'si.teacher_age',
		    'si.teacher_exp',
		    'si.teacher_feature',
		    'si.teacher_remark',

		    'si.number user_count',
		    'si.comment_count',
		    'si.area_detail',
		    'si.environ',
		    'cate.catename',
		    'sk.login_phone',
		    'sk.company_name',
		    'sk.status',
		);
		$join  = array(
		    'right join __SHOP_INFO_USER__ siu on siu.user_id = u.id',
		    'left join __SHOP_INFO__ si on si.id = siu.shop_info_id',
		    'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = si.sid',
		    'left join __SHOPKEEPER__ sk on sk.id = si.sid',
		    'left join __CATEGORY__ cate on si.cateid = cate.id',
		);
		$where='u.id='.$uid;
		$order='siu.ctime '.$order;
		$res=$this  -> alias($alias)
            		-> join($join)
            		-> where($where)
            		-> order($order)
            		-> limit($page['pageOffset'],$page['perPage'])
            		-> field($array)
            		-> select();
		if (!$res){
		    return $res;
		}

		// 获取结果数组的所以地区ID
		$areaids = array();
		foreach ($res as $key => $row) {
		    $areaids[] = $row['areaid'];
		}
		// 获取两级地区名称
		$areanames = D('Common/Area')->getTwoLevelNameInArr($areaids);

		// 将地区信息放进结果数组
		$tmpAreanames = array();
		foreach ($areanames as $row) {
		    $tmpAreanames[$row["id"]][] = $row["parent_arename"];
		    $tmpAreanames[$row["id"]][] = $row["this_arename"];
		}
        $nowTime = time();
		foreach ($res as $key=>$value){

		    // 课程内容格式化成有<br/>
// 		    $res[$key]['content'] = clean_br_content($row['content']);
            // 将价钱去掉小数点后位数
            $res[$key]['price'] = floor($value['price']);
		    
		    
		    $res[$key]['areaname'][] = $tmpAreanames[$value["areaid"]];
		    $res[$key]['name'] = $value['firstname'].$value['lastname'];
		    $res[$key]['ctime'] = transDate(date("Y-m-d H:i:s",$value['ctime']));
		    $res[$key]['tags']  = explode("|", $value['tags']);
		    if ($res[$key]['login_phone']==''){
		        $res[$key]['phonestatus'] = 0;
		    }else {
		        $res[$key]['phonestatus'] = 1;
		    }


		    //约课模式
		    $Mode=C('mode');
		    foreach ($Mode as $k => $v){
		        if($value['mode']==$k){
		            $res[$key]['mode']= $v;
		        }
		    }

            // 判断是否过期
            if ($value['overtime']) {
                $res[$key]['isTimeOut'] = ($nowTime > strtotime($value['overtime']));
            } else {
                $res[$key]['isTimeOut'] = false;
            }
		}
		$data =  array(
		    'info'=>$res,
		    'page'=>$page,
		);
		return $data;
	}
//--------------------------------------第三方----------------------------------------------------------------------
    /**
     * 第三方关联时，验证用户的账号密码是否存在并且是否相对应
     * @return multitype:boolean string |multitype:boolean Ambigous <\Think\mixed, boolean, NULL, multitype:, mixed, unknown, string, object>
     */
    public function relationUid(){
        $typevalue  = I('post.typevalue');
        $type       = I('post.type');
        $checkTypeStatus=$this->checktype($typevalue,$type);//设置注册类型和值，email，phone
        if(checkTypeStatus===false){
            return array(false,"非法操作！");
        }
        //判断帐号 密码 格式是否正确，判断验证码是否正确
        $rules_reg=array(
            array('email', 'require', '邮箱不能为空！', 0),
            array('email', 'is_email', '邮箱不合法', 0, 'function'),
        
            array('phone', 'require', '手机号码不能为空！', 0),
            array('phone', '/^(1)[\d]{10}$/', '手机号码不合法', 0, 'regex'),
        
            array('password', 'require', '密码不能为空！', 1, regex, 1),				 //注册通过  ,登录
            array('password', '/^\S{6,12}$/', '密码必须为6到12个字符', 1, 'regex', 1),
        );
        $auto_reg = array (
            array('password', 'encrypt_passwd', 1, 'function'),
        );
        if (!$this->validate($rules_reg)->auto($auto_reg)->create()) {
            return array(false,$this->getError());
        }
        if ($this->phone){
            $result = $this->where('phone="%s" and password="%s"',$this->phone,$this->password)->find();
        }elseif ($this->email){
            $result = $this->where('email="%s" and password="%s"',$this->email,$this->password)->find();
        }
        if (!$result){
            return array(false,$result);
        }
        return array(true,$result);
    }
	
	
//     //微信
//     //获取code--微信直接登录中使用到--
// 	public function wxLogin(){
// 	    require_once('./Api/wx/config.php');
// 	    require_once('./Api/wx/OauthAction.class.php');
// 	    $Owx = new \OauthAction();
// 	    $Owx->index( WX_APP_ID , my_wx_uri );
// 	}
	//获取token---微信--微信登录and判断中使用到 
	public function wxCode($code){
	    require_once('./Api/wx/config.php');
	    require_once('./Api/wx/OauthAction.class.php');
	    $Owx = new \OauthAction();
	    $wx_token = $Owx->access_token( WX_APP_ID , WX_APP_SECRET ,$code);
	    $_SESSION['access_token']  = $wx_token['access_token'];
	    $_SESSION['expires_in']    = $wx_token['expires_in'];
	    $_SESSION['refresh_token'] = $wx_token['refresh_token'];
	    $Wx = D('Wx');
	    $check_exist = $Wx->checkopenid($wx_token['openid']);
	    if ($check_exist!==false){
	        $loginStatus = $this->wxLoginByUid($check_exist);
	        return true;
	    }
	    return false;
	}
	/**
	 * 授权登录
	 */
	public function reWxlogin(){
	}
	/**
	 * 授权获取token
	 */
	public function reWxCode($code){
	    require_once('./Api/wx/config.php');
	    require_once('./Api/wx/OauthAction.class.php');
	    $Owx = new \OauthAction();
	    $wx_token = $Owx->access_token( WX_APP_ID , WX_APP_SECRET ,$code);
	    $_SESSION['access_token']  = $wx_token['access_token'];
	    $_SESSION['expires_in']    = $wx_token['expires_in'];
	    $_SESSION['refresh_token'] = $wx_token['refresh_token'];
	    $_SESSION['openid']        = $wx_token['openid'];
	    $Wx = D('Wx');
	    $check_exist = $Wx->checkopenid($wx_token['openid']);
	    if ($check_exist!==false){
            $loginStatus = $this->wxLoginByUid($check_exist);
            return true;
	    }
	    $wxOpenId = $_SESSION['openid'];
	    $wxToken  = $_SESSION['access_token'];
	    
	    $wxInfo   = $this->wxGetUserInfo($wxToken,$wxOpenId,$code);
	    
	    if ($wxInfo === false){
	        return false;
	    }
	    $wxAddSt  = $Wx->addWxUser($wxOpenId,$wxInfo);
	    if ($wxAddSt!==true){
	        return $wxAddSt;
	    }
	    return true;
	}
	/**
	 * 获取微信-资料
	 * @param unknown $wxToken
	 * @param unknown $wxOpenId
	 * @param unknown $code
	 * @return mixed
	 */
	public function wxGetUserInfo($wxToken,$wxOpenId,$code){
	    require_once('./Api/wx/config.php');
	    require_once('./Api/wx/OauthAction.class.php');
	    $Owx = new \OauthAction();
	    $wx_info = $Owx->getWxUserInfo($wxToken,$wxOpenId,$code);
	    return $wx_info;
	}
	/**
	 * 微信---通过用户登录
	 * @param unknown $userInfo
	 * @return boolean
	 */
	public function wxLoginByUid($userInfo){
	    $userinfo=$this->getUserInfo($userInfo['uid']);

	    // 登陆成功，设置user的二维数组
	    session('user.id',$userinfo['id']);  						//设置session
	    session('user.name',$userinfo['firstname'].$userinfo['lastname']);  	//设置session
	    session('user.remark',$userinfo['remark']);  						//设置session
	    session('user.profession',$userinfo['profession']);  						//设置session
	    session('user.phone',$userinfo['phone']);  						//设置session
	    session('user.email',$userinfo['email']);  						//设置session
	    session('user.avatar',$userinfo['avatar']);  					//设置session
	    session('user.telstatus',$userinfo['telstatus']);  					//设置session
	    session('user.vtype',$userinfo['vtype']);  					//设置session
	    session('user.vstatus',$userinfo['vstatus']);  					//设置session
	    session('shopkeeper',null);
	    session('shop_auto_login',null);

	    $rel_update = $this->logintime($userInfo['uid']);
	    return true;
	}




	//QQ
	/**
	 * qq登录
	 * @return boolean
	 */
	public function qqlogin(){
	    require_once('./Api/qq/comm/config.php');
	    require_once('./Api/qq/oauth/qq_login.php');
	    S('states',md5(uniqid(mt_rand(), TRUE)),60);
	    $statess=S('states');
	    qq_login($statess);
	}
	/**
	 * qq登录回调地址，返回$qquser_info
	 * @return string
	 */
	public function qqCallBack(){
	    $code = $_REQUEST["code"];
	    if(empty($code)){
// 	        return false;
	        return array($code,0);
	    }else {
            require_once('./Api/qq/comm/config.php');
            require_once('./Api/qq/oauth/qq_callback.php');
            $checkstatus=qq_callback();//获取access_token
            if ($checkstatus!==true){
//                 return false;
	            return array($checkstatus,0);
            }
            $qqopenid=get_openid();//获取openid
            if ($qqopenid===false){
//                 return false;
	            return array($qqopenid,0);
            }
            $qqUser=D('Qq');
            $resule=$qqUser->checkopenid($qqopenid);//检查是否登录过
            if ($resule!==false){   //登录过 ，则$resule=该qq的信息


                $updataExpires=$resule['update_time'];
                $updata_expires_in=$resule['expires_in'];
                $updata_num=$updataExpires+$updata_expires_in;

                if ($updata_num<=time()+2592000){//剩余token有限期一个月，自动续权
                    $callback_again=qq_callback_again();
                    $callToken_again=$qqUser->updataToken($qqopenid);
                }


                $userinfo=$this->getUserInfo($resule['uid']);


        		// 登陆成功，设置thirduser的二维数组
        		session('thirduser.id',$userinfo['id']);  						//设置session
        		session('thirduser.name',$userinfo['firstname'].$userinfo['lastname']);  	//设置session
        		session('thirduser.remark',$userinfo['remark']);  						//设置session
        		session('thirduser.profession',$userinfo['profession']);  						//设置session
        		session('thirduser.phone',$userinfo['phone']);  						//设置session
        		session('thirduser.email',$userinfo['email']);  						//设置session
        		session('thirduser.avatar',$userinfo['avatar']);  					//设置session
        		session('thirduser.telstatus',$userinfo['telstatus']);  					//设置session
        		session('thirduser.vtype',$userinfo['vtype']);  					//设置session
        		session('thirduser.vstatus',$userinfo['vstatus']);  					//设置session

//         		// 登陆成功，设置user的二维数组
//         		session('user.id',$userinfo['id']);  						//设置session
//         		session('user.name',$userinfo['firstname'].$userinfo['lastname']);  	//设置session
//         		session('user.remark',$userinfo['remark']);  						//设置session
//         		session('user.profession',$userinfo['profession']);  						//设置session
//         		session('user.phone',$userinfo['phone']);  						//设置session
//         		session('user.email',$userinfo['email']);  						//设置session
//         		session('user.avatar',$userinfo['avatar']);  					//设置session
//         		session('user.telstatus',$userinfo['telstatus']);  					//设置session
//         		session('user.vtype',$userinfo['vtype']);  					//设置session
//         		session('user.vstatus',$userinfo['vstatus']);  					//设置session
        		session('shopkeeper',null);
        		session('shop_auto_login',null);
//         		$this->setUsercookise($userinfo['id']);
        		$rel_update = $this->logintime($resule['uid']);
	            return array(true,1);
//                 return true;
            }

            $qquser_info=$this->qq_user($qqopenid);//获取qq用户的详细信息

            if ($qquser_info===false){
//                 return false;
	            return array($qquser_info,0);
            }
            $userid=$qqUser->addqqUser($qqopenid,$qquser_info);//插入
//             return $userid;
            if ($userid!==true){
                return array($userid,0);
            }
            return array($userid,1);
	    }
	}

	/**
	 * 获取qq用户的详细信息$user_info
	 * @param unknown $openid
	 * @return boolean|Ambigous <mixed, boolean>
	 */
	public function qq_user($openid){
	    require_once('./Api/qq/user/get_user_info.php');
	    $user_info=get_user_info($openid);
	    return $user_info;
	}


	//sina ---login
	public function sinaLogin(){
		//判断是否已经登录
	    $slast_key=session('?user');
		if($slast_key)
		{
		    return false;
		}
	    require_once('./Api/sina/config.php');
	    require_once('./Api/sina/saetv2.ex.class.php');

		$o = new \SaeTOAuthV2( WB_AKEY , WB_SKEY );
		$code_url = $o->getAuthorizeURL( WB_CALLBACK_URL );

		return $code_url;
		//链接跳转

	}


	public function sinaCallBack(){
	    //判断是否已经登录
	    $slast_key=$_SESSION['slast_key'];
		if($slast_key)
	    {
		    return '405';//已经登录
	    }
	    require_once('./Api/sina/config.php');
	    require_once('./Api/sina/saetv2.ex.class.php');

	    $o = new \SaeTOAuthV2( WB_AKEY , WB_SKEY );

	    if (isset($_REQUEST['code'])) {
	        $keys = array();
	        $keys['code'] = $_REQUEST['code'];
	        $keys['redirect_uri'] = WB_CALLBACK_URL;
	        try {
	            $token = $o->getAccessToken( 'code', $keys ) ;//获取用户的唯一的uid,获取授权过的Access Token
	        } catch (OAuthException $e) {
	        }
	    }


	    if ($token) {
	        $_SESSION['token'] = $token;
	        $_SESSION['access_token'] = $token['access_token'];
	        $_SESSION['expires_in'] = $token['expires_in'];
	        setcookie( 'weibojs_'.$o->client_id, http_build_query($token) );
	        //转到注册登录页面
 //============================================================me==============================
	        $userId=$token['uid'];//用户唯一标识符
	        $sinaUser=D('Sina');
	        $resule=$sinaUser->checksinauid($userId);//检查是否登录过
	        if ($resule!==false){

	            $rel_update = $this->logintime($resule['uid']);

	            $userinfo=$this->getUserInfo($resule['uid']);


	            // 登陆成功，设置thirduser的二维数组
	            session('thirduser.id',$userinfo['id']);  						//设置session
	            session('thirduser.name',$userinfo['firstname'].$userinfo['lastname']);  	//设置session
	            session('thirduser.remark',$userinfo['remark']);  						//设置session
	            session('thirduser.profession',$userinfo['profession']);  						//设置session
	            session('thirduser.phone',$userinfo['phone']);  						//设置session
	            session('thirduser.email',$userinfo['email']);  						//设置session
	            session('thirduser.avatar',$userinfo['avatar']);  					//设置session
	            session('thirduser.telstatus',$userinfo['telstatus']);  					//设置session
	            session('thirduser.vtype',$userinfo['vtype']);  					//设置session
	            session('thirduser.vstatus',$userinfo['vstatus']);  					//设置session

// 	            // 登陆成功，设置user的二维数组
// 	            session('user.id',$userinfo['id']);  						//设置session
// 	            session('user.name',$userinfo['firstname'].$userinfo['lastname']);  	//设置session
// 	            session('user.remark',$userinfo['remark']);  						//设置session
// 	            session('user.profession',$userinfo['profession']);  						//设置session
// 	            session('user.phone',$userinfo['phone']);  						//设置session
// 	            session('user.email',$userinfo['email']);  						//设置session
// 	            session('user.avatar',$userinfo['avatar']);  					//设置session
// 	            session('user.telstatus',$userinfo['telstatus']);  					//设置session
// 	            session('user.vtype',$userinfo['vtype']);  					//设置session
// 	            session('user.vstatus',$userinfo['vstatus']);  					//设置session
	            session('shopkeeper',null);
	            session('shop_auto_login',null);
// 	            $this->setUsercookise($userinfo['id']);
	            return true;
	        }

 //==========================================================================================

	        $oo=new \SaeTClientV2(WB_AKEY,WB_SKEY,$token['access_token'],$o->client_secret);
	        try {
	            $uidinfo = $oo->show_user_by_id( $token['uid'] ) ;//尝试获取新浪用户的详细信息
	        } catch (OAuthException $e) {
	        }


	        //为微博用户发布一条注册的微博信息
	        $sendWeiboPic = 'http://'.$_SERVER['HTTP_HOST'].'/Public/Home/img/weibo_share.jpg';
	        try {
	            $uidSendWeibo = $oo->upload( $this->sendweibo, $sendWeiboPic ) ;//尝试获取新浪用户的详细信息
	        } catch (OAuthException $e) {
	        }

            //关注官方微博
	        try {
	            $yk_Weibo = $oo->follow_by_name( WB_YUEKE ) ;//关注官方微博
	        } catch (OAuthException $e) {
	        }

// header("content-Type: text/html; charset=Utf-8");//设置字符编码
// print_r($uidinfo);
// print_r('<br/>');
// print_r($token);
// print_r('<br/>');
// print_r($uidSendWeibo);
// print_r('<br/>');
// print_r($yk_Weibo);
// exit;
	        $user_yk_id=$sinaUser->addsinaUser($userId,$uidinfo);//插入  新浪用户的详细信息
	        if ($user_yk_id===false){
	            return '406';
	        }
	        return true;
	    } else {
	        return '407';//获取新浪uid失败，授权失败。
	    }
	}


//=============qq sina登录时注册本网站===================================================
	public function sendThirdCode($uid=0,$tel=0){
	    if ($tel==0){
	        return "请输入正确的手机号码!";
	    }
	    if ($uid==0){
	        return "请先登录!";
	    }
	    $regex = '/^(1)[\d]{10}$/';
	    if(!preg_match($regex, $tel)){
	        return '请输入正确的手机号码！';
	    }
	    $oldtime=session('third.codetime');
	    if ($oldtime + 60 >= time()) { //
	        return '60s内不能重新发送!';
	    }
	    //
	    $check_phone_exit = $this->existphone($tel);
	    if ($check_phone_exit===true){
	        return '该手机号码已被注册!';
	    }

	    require_once('./Api/sms/sms_send.php');
	    $code      = mt_rand(100000,999999);
	    $_SESSION["thirdcode"]  = $code;

	    $msg="尊敬的客户，您正在进行第三方登录服务。短信验证码为";
	    $messages="，10分钟内验证有效，谢谢。";
	    $company="【17约课】";

	    $msg=$msg.$code.$messages.$company;//【乐莘网络】 可以换成自己的签名，签名一般用公司或网站的简称
	    if (!$msg){
	        return "验证码为空";
	    }
	    $msgcode=sendnote($tel,urlencode(mb_convert_encoding($msg, 'gbk' ,'utf-8')));//如果你网站或软件的格式是utf-8的，需要对发送的内容转成gbk的
	    session('third.codetime',time());//60秒才可以发送一次
	    session('third.codephone',$tel);
	    if ($msgcode!=1){
	        return '请联系客服人员';
	    }
	    return true;
	}
	/**
	 * 保存手机第三方的手机号码
	 * @param number $code
	 * @param number $uid
	 */
	public function saveThirdPhone($code=0,$uid=0){
	    $thirdCode = $_SESSION["thirdcode"];
	    $thirdTime = session('third.codetime');
	    if ($thirdTime+600<time()){
	        return '验证码已过期!';
	    }
	    if ($thirdCode!=$code){
	        return '验证码不正确!';
	    }
	    $phone = session('third.codephone');
	    $rel = $this->where('id=%d',$uid)->setField('phone',$phone);
	    if (!$rel){
	        return '网络延迟!';
	    }
	    return true;
	}

	/**
	 * 查找第三方登录是否存在手机号码
	 * @param number $uid
	 * @return boolean|Ambigous <\Think\mixed, NULL, mixed, multitype:Ambigous <unknown, string> unknown , unknown, object>
	 */
	public function gainPhone($uid=0){
	    $third_phone = $this->where('id=%d',$uid)->getField('phone');
	    if (!$third_phone){
	        return false;
	    }
	    return $third_phone;
	}



	/**
	 * 把session中值赋值到$userInfo变量中
	 * @return multitype:Ambigous <mixed, NULL> Ambigous <mixed, NULL, unknown>
	 */
	public function sessionToUserInfo(){
	    $userInfo=array();
	    $userInfo['id'] = session('user.id');  						//设置session
    	$userInfo['name'] = session('user.name');  	//设置session
    	$userInfo['remark'] = session('user.remark');  						//设置session
    	$userInfo['profession'] = session('user.profession');  						//设置session
    	$userInfo['phone'] = session('user.phone');  						//设置session
    	$userInfo['email'] = session('user.email');  						//设置session
    	$userInfo['avatar'] = session('user.avatar');  					//设置session
    	$userInfo['telstatus'] = session('user.telstatus');  					//设置session
    	$userInfo['vtype'] = session('user.vtype');  					//设置session
    	$userInfo['vstatus'] = session('user.vstatus');  					//设置session
    	return $userInfo;
	}
//-------------------------------------------------游客--↓---------------------------------------------------------
	/**
	 * 当前游客的信息关联到当前的用户信息中
	 * @return boolean
	 */
	public function updataVisitorToUserInfo(){
	    //游客
	    if (session('?visitor')){
	        $visitor_id=session('visitor.id');//session里没有游客记录   则--取用户浏览器中的游客记录
	    }else {
	        $visitor_id=I('post.visitorid')?I('post.visitorid'):0;
	    }
        $uid=session('user.id');
        D('GroupAssist')->updataVisitorToUid($uid,$visitor_id);
        D('Groupcomment')->updataVisitorToCid($uid,$visitor_id);
        D('ShopInfo')->updataVisitorToUserId($uid,$visitor_id);
        D('ShopComment')->updataVisitorTouid($uid,$visitor_id);
	    return true;
	}
//-------------------------------------------------游客  ↑---------------------------------------------------------
	/**
	 * 短信推广 所有用户--后台admin
	 */
	public function promotionAllSms($body='',$Phone=''){
	    if ($body==''){
	        return '发送内容不能为空';
	    }

	    $validates=array(
	        array('verify','require','验证码不能为空', 1),
	        array('verify','check_verify','验证码错误！',1,'function'), //默认情况下用正则进行验证
	    );
	    $autodate=array();
	    if (!$this->validate($validates)->auto($autodate)->create()) {
	        return $this->getError();
	    }

	    $field=array(
	        'id',
	        'email',
	        'phone',
	        'firstname',
	        'lastname'
	    );
	    $relAll = $this->field($field)->where("(email<>'') or (phone<>'')")->select();
	    if (!$relAll){
	        $sql_error = $this->_sql();
	        return '查询错误！'.$sql_error;
	    }
        $emails=array();
        $moble=array();
        $name = array();
	    foreach ($relAll as $keypro=>$valuepro){
	        if ($valuepro['phone']) { // 这个user使用手机作为联系方式
                $moble[] = $valuepro['phone'];
                $name[] = $valuepro['firstname'].$valuepro['lastname'];
            }else if ($valuepro['email']) { // 这个user是使用邮箱作为联系方式
                $emails[$keypro] = $valuepro['email'];
            }
	    }
        $subject='【17约课】';
        if (!empty($emails)){
            $emailcode=sendMail($emails,$body, $subject);
//             $emailcode=true;
        }
//         $statusall = array();
        if (!empty($moble)){
            require_once('./Api/sms/sms_swt.php');
//             foreach ($moble as $moblekey=>$mobleval){
//                 $bodyname = '嗨!'.$name[$moblekey].$body;
                $snsstatus = send($moble , $body);
//                 $statusall[] = true;
//             }
        }

        $allStatus=array();
        $allStatus[]=$emails;
        $allStatus[]=$emailcode;
        $allStatus[]=$moble;
//         $allStatus[]=$statusall;
        $allStatus[]=$snsstatus;
        $allStatus[]=$name;
        
        $snsRecord = json_encode($allStatus);
        D('Record')->addRecord($snsRecord);
        session('snssendall',$allStatus);
        
//         $alltrue=true;
//         foreach ($statusall as $statuskey=>$statusvlaue){
//             if ($statusvlaue==true){
//                 $alltrue = true;
//             }else {
//                 $alltrue = false;
//             }
//         }
        
        if ($emailcode===true && $snsstatus===true){
            return true;
        }
        return $allStatus;
	}

//=======================================================








//==========================================================================================


        	   /*
        	    * 获取用户的数据
        	    */
            public function getUserData($pageOffset=0,$perPage=30,$sort='desc'){
                /* $alias = 'u';//定义当前数据表的别名
                //要查询的字段
                $array = array(
                    'u.id',//用户id
                    'u.firstname',
                    'u.lastname',
                    'u.phone',
                    'u.email',

                );
                $order='u.id '.$sort;


                $res=$this  -> alias($alias)
                -> order($order)
                -> limit($pageOffset,$perPage)
                ->where("(email<>'') or (phone<>'')")
                -> field($array)
                -> select();
            $GroupAssist=D('GroupAssist');
            $ShopInfoUser=D('ShopInfoUser');
            $GroupInfo=D('GroupInfo');
            $ShopkeeperDetail=D('ShopkeeperDetail');
            $Shopkeeper=D('Shopkeeper');
          foreach ($res as $key=>$value){
              $uid=intval($value['id']);

                $res[$key]['gacount'] = $GroupAssist->where("whoid='$uid'")->count('whoid');

                $res[$key]['bmcount'] = $ShopInfoUser->where("user_id='$uid'")->count('user_id');
                $res[$key]['xycount'] = $GroupInfo->where("uid='$uid'")->count('uid');

            }
            $arr=arr_sort($res, 'xycount','desc');
              return $res;
               */
                $result = M('User')->alias('u')
                ->field(['u.id uid', 'u.firstname', 'u.lastname', 'u.phone', 'u.email', 'count(gi.id) info_count'])
                ->join('LEFT JOIN __GROUP_INFO__ gi on gi.uid = u.id')
                ->group('u.id')
//                 ->order('count(gi.id) desc')
                ->order('u.id desc')
                ->where("(u.email<>'') or (u.phone<>'')")
                ->limit($pageOffset, $perPage)
                ->select();

                if (!$result){
                    return false;
                }

                $uids = [];
                foreach ($result as $row) {
                    $uids[] = $row['uid'];
                }

                $enrollArr = M('ShopInfoUser')->field(['user_id uid', 'count(*) enroll_count'])
                ->where('user_id in (%s)', implode(',', $uids))
                ->group('user_id')
                ->select();
                if ($enrollArr===false){
                    return false;
                }

                $assistArr = M('GroupAssist')->field(['whoid uid', 'count(*) assist_count'])
                ->where('whoid in (%s)', implode(',', $uids))
                ->group('whoid')
                ->select();
                if ($assistArr===false){
                    return false;
                }

                foreach ($result as $key => $row) {
                    foreach ($enrollArr as $countRow) {
                        if ($row['uid'] == $countRow['uid']) {
                            $result[$key]['enroll_count'] = $countRow['enroll_count'];
                            continue 2;
                        }
                    }
                    $result[$key]['enroll_count'] = 0;
                }

                foreach ($result as $key => $row) {
                    foreach ($assistArr as $countRow) {
                        if ($row['uid'] == $countRow['uid']) {
                            $result[$key]['assist_count'] = $countRow['assist_count'];
                            continue 2;
                        }
                    }
                    $result[$key]['assist_count'] = 0;
                }
                return $result;
            }

            /*
             * 　获取对应的跟约人的数据信息
             */
            public function getAllGroupId($gid){
                $GroupAssist=D('GroupAssist');
                $User=D('User');
                $Commid= $GroupAssist->where("groupid='$gid'")->field('whoid')->select();
                $arr=array();
                foreach ($Commid as $key1 =>$value1){
                    array_push($arr,$value1['whoid']);
                }
                $s=implode(',',$arr);
                $map['id']  = array('in',$s);
                $array= $User
                ->where($map)
                ->field('id,firstname,lastname,phone,email')->select();
                return $array;
            }


            /*
             * 　获取用户对应的跟约信息
             */
            public function getAssInfo($pageOffset=0,$perPage=30,$uid=0){
                $alias = 'u';//定义当前数据表的别名
                //要查询的字段
                $array = array(
                    'u.id',//用户id
                    'u.firstname',
                    'u.lastname',
                     'gi.title',
                    'cate.catename',
                    'gi.ltprice',
                    'gi.mode',
                    'gi.title',
                    'gi.content',
                    'gi.tags',
                    'sd.area_detail',
                    'sd.nickname',
                    'sk.company_name',
                    'gi.id as groupid',
                   //  'ga.whoid',
                );

               $join  = array(
                 'left  join __GROUP_INFO__ gi on gi.uid = u.id',
                'left join __SHOPKEEPER_DETAIL__ sd on sd.sid =gi.sid',
                   'left join __CATEGORY__ cate on gi.cateid = cate.id',
                   'left join __SHOPKEEPER__ sk on gi.sid = sk.id',
                );
               $where=("gi.uid='$uid'");
                $res=$this  -> alias($alias)
               // -> order($order)
                 ->join($join)
                -> limit($pageOffset,$perPage)
                -> field($array)
                ->where($where)
                -> select();
                $res=$this->grouptags($res);
                foreach ($res as $key=>$value){
                    $gid=intval($value['groupid']);

                 $res[$key]['who']=$this->getAllGroupId($gid);
                 $Mode=C('mode');
                 foreach ($Mode as $k => $v){
                     if($value['mode']==$k){
                         $res[$key]['mode']= $v;
                     }
                 }
                }


                return $res;
            }


            /*
             * 　获取用户对应的推送课程信息
             */
            public function getTsInfo($pageOffset=0,$perPage=30,$uid=0){
                $alias = 'u';//定义当前数据表的别名
                //要查询的字段
                $array = array(
                    'u.id',//用户id
                    'u.firstname',
                    'u.lastname',
                  'gi.title',
                    'gp.sinfoid',
                  'si.title as tstitle',
                     'sp.company_name',
                    'sp.login_email',
                    'sp.login_phone',
                    'sp.tel',
                    'sd.nickname'
                );

                $join  = array(
                    'left join __GROUP_INFO__ gi on gi.uid = u.id',
                    'left join __GROUP_PUSHED__ gp on gp.gid =gi.id',
            'right join __SHOP_INFO__ si on si.id =gp.sinfoid',
                        'left join __SHOPKEEPER__ sp on sp.id =si.sid',
                    'left join __SHOPKEEPER_DETAIL__ sd on sd.sid =sp.id',
                );
                $where=("gi.uid='$uid'");
                $res=$this  -> alias($alias)
                ->join($join)
                -> limit($pageOffset,$perPage)
                -> field($array)
                ->where($where)
                -> select();
                return $res;
            }


            /*
             * 　获取对应的用户发布心愿的评论数据信息
             */
            public function getCommAllUser($gid){
                $Groupcomment=D('Groupcomment');
                $User=D('User');
                $Commid= $Groupcomment->where("gid='$gid'")->field('cid')->select();
                $arr=array();
                foreach ($Commid as $key1 =>$value1){
                    array_push($arr,$value1['cid']);
                }
                $s=implode(',',$arr);
                $map['id']  = array('in',$s);
                $info['gid']  = array('in',$s);
                $array= $User
                ->where($map)
                ->field('id,firstname,lastname,phone,email')->select();
                foreach ($array as $key =>$value){
                    $uid=$value['id'];

                      $array[$key]['userinfo']=$Groupcomment->where("cid='$uid' and gid='$gid'")->field('c_info')->select();
                }


                return $array;
            }

            /*
             * 　获取用户对应发布心愿的评论信息
             */
            public function getcomInfo($pageOffset=0,$perPage=30,$uid=0){
                $alias = 'u';//定义当前数据表的别名
                //要查询的字段
                $array = array(
                    'u.id',//用户id
                    'u.firstname',
                    'u.lastname',
                    'gi.title',
                    'gi.id as groupid',
                );

                $join  = array(
                    'left join __GROUP_INFO__ gi on gi.uid = u.id',
                );
                $where=("gi.uid='$uid'");
                $res=$this  -> alias($alias)
                ->join($join)
                -> limit($pageOffset,$perPage)
                -> field($array)
                ->where($where)
                -> select();
                foreach ($res as $key=>$value){
                    $gid=intval($value['groupid']);

                    $res[$key]['user']=$this->getCommAllUser($gid);

                }
                return $res;
            }



            /**
             * 对用户数据的信息进行分页
             * @param unknown $curPage
             * @param unknown $perPage
             * @return Ambigous <\Common\Util\multitype:number, multitype:number >
             */
            public function userDataPage($curPage,$perPage,$whoid=null){
                $count= $this->where("(email<>'') or (phone<>'')")->count('id');
                if($whoid!=null){
            $GroupInfo=D('GroupInfo');
           $GroupAssist=D('GroupAssist');
           $arr=array();
           $count= $GroupInfo->where("uid='$whoid'")->count();
                }
               // 查询总记录数
                import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
                $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
                $pageArray=$Page->getCounts();
                return  $pageArray;
            }

            /**
             * 对用户数据的对应课程推送信息进行分页
             * @param unknown $curPage
             * @param unknown $perPage
             * @return Ambigous <\Common\Util\multitype:number, multitype:number >
             */
            public function tsTnfoDataPage($curPage,$perPage,$whoid){
                    $GroupInfo=D('GroupInfo');
                    $Grouppushed=D('GroupPushed');
                    $arr=array();
                    $Group= $GroupInfo->where("uid='$whoid'")->field('id')->select();
                    foreach ($Group as $key1 =>$value1){
                        array_push($arr,$value1['id']);
                    }
                    $s=implode(',',$arr);
                    $map['gid']  = array('in',$s);
                    $count= $Grouppushed->where($map)->count();

                // 查询总记录数
                import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
                $Page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数
                $pageArray=$Page->getCounts();
                return  $pageArray;
            }

            public function sum(){
                $GroupAssist=D('GroupAssist');
                $ShopkeeperDetail=D('ShopkeeperDetail');
                $ShopInfoUser=D('ShopInfoUser');
                $GroupInfo=D('GroupInfo');
                $array=array();
                $array['gasum'] = $GroupAssist->count('whoid');
                $array['bmsum'] = $ShopInfoUser->count('user_id');
                $array['xysum'] = $GroupInfo->count('uid');
                return $array;
            }

            /**
             * 把组团信息的标签分割成数组
             * @param array $info
             * @return array $info
             */
            public function grouptags($info=array()){
                //把组团标签以，逗号分割成数组
                $i=0;
                foreach ($info as $rows)
                {
                    $rows['tags']=explode("|", $rows['tags']);
                    $info[$i]['tags']=$rows['tags'];
                    $i++;
                }
                return $info;
            }


            public function getText($sid,$mode,$tags,$overtime,$priceid){
                $uid = session('user.id');
                $alias = 'u';//定义当前数据表的别名
                $overtime=time()+$overtime*3600*24;
                $overtime=date("Y年m月d日",$overtime);
                $array = array(
                'u.id',
                    'u.lastname',
                    'u.profession',
                    'u.telstatus',
                    'u.avatar',
                    'uv.uid',
                    'uv.vstatus',
                    'uv.vtype',
                );
                $join  = array(
                    'left join __USER_V__ uv on uv.uid = u.id'
                );
                $res =$this  ->alias($alias)
                ->join($join)
                ->where("uid='$uid'")
                ->field($array)
                ->find();
                $ShopkeeperDetail=D('ShopkeeperDetail');

                $areaAllName=D('Common/Area');
                $Price=D('Price');
                $Mode=C('mode');

                //$res[$key]['areaname']=$areaAllName->getAllById($value['areaid']);//地区名字
                $ShopkeeperInfo =$ShopkeeperDetail
                ->where("sid='$sid'")
                ->field('areaid,environ,nickname')
                ->find();
                $ShopkeeperInfo['tags']=explode("|",$tags);
                    $ShopkeeperInfo['areaname']=$areaAllName->getAllById($ShopkeeperInfo['areaid']);//地区名字
                    $ShopkeeperInfo['priceid']=$Price->getId($priceid);
                    $ShopkeeperInfo['userinfo']=$res;
                    $ShopkeeperInfo['overtime']=$overtime;
                    foreach ($Mode as $k => $v){
                        if($mode==$k){
                            $ShopkeeperInfo['mode']= $v;
                        }
                    }
            return $ShopkeeperInfo;
            }
}
