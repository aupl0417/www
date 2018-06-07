<?php

namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 商家Model
 * @author jmjoy
 *
 */
class ShopkeeperModel extends CommonModel {

	/**
	 * 增加之后最后的Id
	 * @var unknown
	 */
	public $addedLastId;

    /**
     * 手机号码验证规则
     */
    public $phoneRegexp = '/^1\d{10}$/';

	protected $_validate = array(
			array('company_name', 'require', '公司名称不能为空！', 1),
			array('company_name', '/^[\w\x{4e00}-\x{9fa5}]{6,20}$/u', '公司名称必须为6~20个字！', 1, 'regex'),
//			array('company_name', '', '公司名称已经被注册！', 1, 'unique'),

			array('login_email', 'require', '登陆邮箱不能为空！', 2),
			array('login_email', 'is_email', '登陆邮箱不合法！', 2, 'function'),
			array('login_email', '', '登陆邮箱已经被注册！', 2, 'unique'),

			array('login_phone', 'require', '手机号码不能为空！', 2),
			array('login_phone', '/^1\d{10}$/', '手机号码不合法！', 2, 'regex'),
			array('login_phone', '', '手机号码已经被注册！', 2, 'unique'),

			array('password', 'require', '密码不能为空！', 1),
			array('password', '/^\S{6,12}$/', '密码必须为6到12个字符！', 1, 'regex'),

			array('company_email', 'require', '邮箱不能为空！', 2),
			array('company_email', 'is_email', '邮箱不合法！', 2, 'function'),
			array('company_email', 'isCompanyEmail', '邮箱不是企业邮箱！', 2, 'callback'),
			array('company_email', '', '邮箱已经被注册！', 2, 'unique'),

			array('tel', 'require', '固定电话不能为空！', 2),
			array('tel', '/^\d{3,4}\-\d{7,8}$/', '固定电话不合法！', 2, 'regex'),
// 			array('tel', '', '固定电话已经被注册！', 1, 'unique'),
	);

	protected $_auto = array (
			array('password', 'encrypt_passwd', 1, 'function'),
			array('ctime', 'current_datetime', 1, 'function')
	);

	public function getByEmail($email) {
		$row = $this->field(true)
						->where("login_email = '%s'", $email)
						->find();
		return $row;
	}

	public function getByPhone($phone) {
		$row = $this->field(true)
						->where("login_phone = '%s'", $phone)
						->find();
		return $row;
	}

	/**
	 * 处理登陆请求
	 * @return string|boolean
	 */
	public function handleLogin() {
		// 检测传入参数是否为空或者纯空格
		$arg = I('post.arg');
		if (trim($arg) == '') {
			return '邮箱或者手机号码不能为空';
		}
		$password = I('post.password');
		if (trim($password) == '') {
			return '密码不能为空';
		}
		// 根据邮箱地址或者手机号码获取商家信息，包含密码
		switch (I('post.type')) {
		case 'email':
			$row = $this->getByEmail(I('post.arg'));
			break;

		case 'phone':
			$row = $this->getByPhone(I('post.arg'));
			break;

		default:
			return 'what\'s wrong with you?';
		}
		// 邮箱或者手机号码不存在
		if (!$row) {
			return '邮箱或者手机号码不存在';
		}
		// 检测密码正确与否
		if ($row['password'] != encrypt_passwd(I('post.password'))) {
			return '密码不正确';
		}
		// 检测有没有激活企业邮箱
		if ($row['status'] <= 0) {
			return '企业邮箱没有激活';
		}
		// 登陆成功
		$this->loginData($row);

		// 设置自动登陆的Cookie
		if (I('post.autoLogin') == 'true') {
			$cookie = encrypt($row['id'] . "|" . $row['password'], C('basekey'));
			cookie('shop_auto_login', $cookie, 10 * 24 * 60 * 60);
		}
		// 成功
		return true;
	}

	/**
	 * 商家自动登录处理
	 * @param unknown $id
	 * @param unknown $encryptedPasswd
	 * @return boolean
	 */
	public function autoLogin($id, $encryptedPasswd) {
		$row = $this->where('id = %d and password = "%s"', $id, $encryptedPasswd)
				->find();

		// 登录失败，被伪造数据了，用户一定不是好人！
		if (!$row) {
			return false;
		}

		// 登录成功
		$this->loginData($row);
		return true;
	}

    /**
     * 微信登录使用，根据商家的id进行登录
     */
    public function wxLogin($sid) {
        $row = $this->where('id = %d', $sid)->find();

        // 没有找到该商家或者数据库出错
        if (!$row) {
            return false;
        }

        // 登录成功
        $this->loginData($row);
        return true;
    }

	/**
	 * 保存商家的资料到session，并且删除用户的session和自动登录
	 * @param unknown $row
	 */
	protected function loginData($row) {
		session('shopkeeper.id', $row['id']);
		session('shopkeeper.company_name', $row['company_name']);
		session('shopkeeper.login_email', $row['login_email']);
		session('shopkeeper.login_phone', $row['login_phone']);
		session('shopkeeper.company_email', $row['company_email']);
		session('shopkeeper.tel', $row['tel']);
		session('shopkeeper.ctime', $row['ctime']);
		session('shopkeeper.status', $row['status']);

		// 再把商家详情表的东西放进去
		$detail = M('ShopkeeperDetail')->field(true)
										->where('sid = %d', session('shopkeeper.id'))
										->find();
        if ($detail) {
            session('shopkeeper.nickname', $detail['nickname']);
            session('shopkeeper.remark', $detail['remark']);
            session('shopkeeper.avatar', $detail['avatar']);
            session('shopkeeper.age', $detail['age']);
            session('shopkeeper.cateid', $detail['cateid']);
        }

		// 删掉普通用户登陆信息
		session('user', null);
		cookie('userid', null);
	}

	/**
	 * 获取商家数量
	 * @param int $status 根据状态码获取，如果为10代表获取所有
	 * @return int|boolean
	 */
	public function getCount($status = 10) {
		// 过滤
		$status = intval($status);
		// 根据状态取数据
		if ($status !== 10) {
			$this->where('status = %d', $status);
		}
		return $this->count();
	}

	/**
	 * 获取商家信息并分页
	 * @param int $skip 跳过多少页
	 * @param int $list 列出多少条
	 * @param int $status 根据状态码获取，如果为10代表获取所有
	 * @param string $order 顺序，'asc'或者'desc'
	 * @return array 结果数组
	 */
	public function getAndPaginate($skip, $list, $status = true, $order = 'desc') {
		// 过滤
		$status = intval($status);
		if ($order !== 'desc' && $order !== 'asc') {
			$order = 'desc';
		}
		//
		$this->field(true)
			->order('ctime ' . $order)
			->limit($skip, $list);
		// 根据状态取数据
		if ($status !== 10) {
			$this->where('status = %d', $status);
		}
		// 获取结果数组
		$resArr = $this->select();
		if (!$resArr) {
			return array();
		}
		return $this->pushStatusStrToResArr($resArr);
	}

	/**
	 * 通过商家的ID获取他的状态
	 *
	 * @param unknown $id
	 * @return \Think\mixed
	 */
	public function getStatus($id) {
		return $this->where('id = %d', $id)->getField('status');
	}

	/**
	 * 处理注册请求
	 * @param unknown $data
	 * @return string|boolean
	 */
	public function handleSignUp($data) {
		// 判断登陆邮箱和手机号码是不是同时为空
		if (empty($data['login_email']) && empty($data['login_phone'])) {
			return "登陆邮箱或登陆手机号码不存在！";
		}
		// 插入
		if (!$this->create($data)) {
			return $this->getError();
		}
		if (!$this->add()) {
			return $this->getDbError();
		}
		return true;
	}

	/**
	 *  增加一个商家（包括邮箱地址和手机号码）
     *  @param login bool    是否帮商家登录
     *  @param returnId bool 是否返回insertid，否则返回bool值
	 */
	public function addOne($data = null, $login=false, $returnId=false) {
		// 判断数据来源
		if ($data === null) {
			$data = $_POST;
		}
		// 插入
		if (!$this->create($data)) {
			return $this->getError();
		}

		$company_name = $this->company_name;

        // 需求改了，要求商家注册更加简单，所以就不需要激活企业邮箱，状态永远>1
        $this->status = 1;

        // 正常注册的商家是合作商家，呵呵
        $this->is_us = 1;

		if (!$sid = $this->add()) {
			return $this->getDbError();
		}

		// 最后插入的Id，我不知道是不是tp弄成这种鬼样子
		$this->addedLastId = $this->getLastInsID();

		// 添加默认的基本信息
		D('Common/ShopkeeperDetail')->addDefault($sid, $company_name);

        // 帮商家登录
        if ($login) {
            $row = $this->where('id=%d', $login)->find();
            $this->loginData($row);
        }

        // 返回最后插入的id
        if ($returnId) {
            return $this->addedLastId;
        }

		return true;
	}

    /**
     * 用于后台添加一个商家
     */
    public function adminAddOne($data) {
		// 插入
		if (!$this->create($data)) {
			return $this->getError();
		}

        // 后台添加的商家都是认证过得
        $this->status = 3;

        // 后台添加的商家都是自己人
        $this->is_us = 1;

		if (!$sid = $this->add()) {
			return $this->getDbError();
		}

		return true;
    }

	/**
	 * 处理通过审核请求
	 * @param unknown $sid
	 * @return string|boolean
	 */
	public function handleAuth($sid) {
		$status = $this->where('id = %d', $sid)->getField('status');
		if ($status != 2) {
			return '这位商家未提交审核资料或者已经审核过了！';
		}
		$result = $this->where('id = %d', $sid)->setField('status', 3);
		if (!$result) {
			return $this->getDbError();
		}
		return true;
	}

	/**
	 * 激活某个商家
	 * @param int $id
	 */
	public function activeOne($id) {
		$id = intval($id);
		$status = $this->where('id = %d', $id)->getField('status');
		// 判断status，只有当status为0（未激活）时才能激活
		if (is_null($status)) {
			return;
		}
		if ($status != 0) {
			return;
		}
		// 激活
		$status = $this->where('id = %d', $id)->setField('status', 1);
	}

	/**
	 * 验证特定字段
	 * @param string $field
	 * @return string|boolean 如果返回true代表验证通过，否则返回错误信息
	 */
	public function validateField($field, $data = '') {
		// 获取特定字段的验证规则
		$rules = array();
		foreach ($this->_validate as $row) {
			if ($row[0] == $field) {
				$rules[] = $row;
			}
		}
		// 验证
		if (!$rules) {
			return 'what a big error!';
		}
		C('TOKEN_ON',false);
		if (!$this->validate($rules)->create($data)){
			return $this->getError();
		}
		// 验证OK
		return true;
	}

	/**
	 * 看是不是企业邮箱！！！
	 * @param string $email
	 * @return boolean
	 */
	public function isCompanyEmail($email) {
		$suffix = explode('@', $email)[1];
		// 国人常用邮箱
		$person_emails = array(
				'163.com', 'vip.163.com', '126.com', 'qq.com', 'vip.qq.com',
				'foxmail.com', 'gmail.com', 'sohu.com', 'tom.com',
				'vip.sina.com', 'sina.com.cn', 'sina.com', 'yahoo.com.cn',
				'yahoo.cn', 'yeah.net', '21cn.com', 'hotmail.com',
				'sogou.com', '188.com', '139.com', '189.cn', 'wo.com.cn',
				'139.com'
		);
		if (in_array($suffix, $person_emails)) {
			return false;
		}
		return true;
	}

	/**
	 * 根据商家查询结果数组的status添加管理员可以执行的操作
	 * status和operate_permission(可执行操作)的对应关系：
	 * 0 -- active , 2 -- look , 3 -- look
	 * @param array $resArr 查询结果数组
	 * @return array 新字段：operate_permission
	 */
	public function pushOpeartePermission($resArr) {
		foreach ($resArr as $key => $row) {
			// 0：未激活，1：未审核，2：审核中，3：已审核
			// 操作类型有： active（激活），look（查看信息）
			$permission = '';
			switch ($row['status']) {
			case 0:
				$permission = 'active';
				break;
			case 1:
				$permission = '';
				break;
			case 2:
			case 3:
				$permission = 'look';
				break;
			}
			$resArr[$key]['operate_permission'] = $permission;
		}
		return $resArr;
	}

	/**
	 * 发送忘记密码邮件
	 * @param unknown $email
	 * @return string
	 */
	public function sendEmailForChangePasswd($email) {
		// 验证邮箱合法性
		$res = filter_var($email, FILTER_VALIDATE_EMAIL);
		if ($res === false) {
			return "邮箱不合法";
		}

		// 看看这个邮箱注册了没有
		$res = $this->checkExist('email', $email);
		if (!$res) {
			return '登陆邮箱不存在';
		}

		// 生成验证码
		$token = "";
		for ($i = 0; $i < 6; $i++) {
			$token .= mt_rand(0, 9);
		}
		session("shopkeeper.change_passwd_token", $token);
		session("shopkeeper.change_passwd_login", array(
				"type"	=>	"email",
				"arg"	=>	$email,
		));
		// 发送验证邮件，成功返回true，错误返回错误信息
		return sendMail($email, "你的验证码是 $token ，请勿泄露！", "修改密码");
	}

	/**
	 * 发送忘记密码短信
	 * @param unknown $phone
	 */
	public function sendPhoneMsgForChangePasswd($phone) {
		// 验证
		if (!preg_match($this->phoneRegexp, $phone)) {
			return '手机不合法';
		}

		// 看看这个邮箱注册了没有
		$res = $this->checkExist('phone', $phone);
		if (!$res) {
			return '登陆手机号码不存在';
		}

        // 发送短信防止攻击
        $result = limit_day_operate('shop-send-sms-', $phone, 60, 10);

        // 判断返回结果
        switch ($result) {
        case 1:
            return '60秒之内不能重发短信';

        case 2:
            return '1天之内不能发超过10条短信';
        }

        // 生成验证码
        $token = "";
        for ($i = 0; $i < 6; $i++) {
            $token .= mt_rand(0, 9);
        }
        session("shopkeeper.change_passwd_token", $token);
        session("shopkeeper.change_passwd_login", array(
                "type"	=>	"phone",
                "arg"	=>	$phone,
        ));

        // 发送验证邮件，成功返回true，错误返回错误信息
        require_once(realpath('Api/sms/sms_send.php'));
        $msg = "您的验证码是 $token ，请勿泄露！【17约课】";
        $result = sendnote($phone, urlencode(iconv('utf-8', 'gbk', $msg)));
        if ($result <= 0) {
            return "短信发送出现异常，请联系管理员(code： $result)";
        }

        return true;
	}

	/**
	 * 处理密码重置请求
	 * @param unknown $verify 验证码
	 * @param unknown $password 密码
	 * @return 成功返回true，错误返回错误信息string
	 */
	public function handleResetPasswd($token, $password) {
		// 校验密码
		if (!preg_match('/^\S{6,12}$/', $password)) {
			return '密码必须为6到12个字符';
		}
		// 校验验证码
		if ($token != session("shopkeeper.change_passwd_token")) {
			return "验证码不正确";
		}
		// 开始修改数据库了
		$loginArgs = session("shopkeeper.change_passwd_login");
		// 判断是通过邮件还是手机号码找到这个商家
		switch ($loginArgs["type"]) {
		case "email":
			$this->where("login_email = '%s'", $loginArgs["arg"]);
			break;
		case "phone":
			$this->where("login_phone = '%s'", $loginArgs["arg"]);
			break;
		default:
			return "异常错误";
		}
		// 修改
		$this->limit('1');
		$this->setField("password", encrypt_passwd($password));
		// 删除session
		session("shopkeeper.change_passwd_token", null);
		session("shopkeeper.change_passwd_login", null);
		// OK
		return true;
	}

	/**
	 * 商家手机验证和营业执照验证
	 * @param unknown $id
	 * @return multitype:boolean
	 */
	public function hasPhoneAndV($id) {
		// 获取手机号码和商家状态
		$resArr = $this->field(array('login_phone', status))
						->where('id = %d', $id)
						->find();

		$phone = false;
		$v = false;
		// 是否有手机号码
		if ($resArr['login_phone']) {
			$phone = true;
		}
		// 是否商家验证
		if ($resArr['status'] >= 3) {
			$v = true;
		}

		return array(
				'phone'		=>	$phone,
				'v'			=>	$v,
		);
	}

	/**
	 * 根据商家的查询结果数组添加status的文字解析字段
	 * @param array $resArr 查询结果数组
	 * @return array 新字段：status_str
	 */
	protected function pushStatusStrToResArr($resArr) {
		foreach ($resArr as $key => $row) {
			$resArr[$key]['status_str'] = $this->getStatusStr($row['status']);
		}
		return $resArr;
	}

	/**
	 * 根据商家Stauts返回文字解析
	 * @param int $index 状态码
	 * @return string
	 */
	protected function getStatusStr($index) {
		switch ($index) {
		case 0:
			return '未激活（企业邮箱）';
		case 1:
			return '未认证（已激活企业邮箱）';
		case 2:
			return '认证中（已提交认证资料）';
		case 3:
			return '已认证';
		}
	}

	/**
	 * 加密自动登陆的Cookie
	 * @param unknown $id
	 * @param unknown $password
	 * @return string
	 */
	protected function cryptAutoLoginCookie($id, $password) {
		$passwd = encrypt_passwd($password);
		$str = $id . "|" . $passwd;
		return base64_encode($str);
	}

	/**
	 * 获取商家的公司名称和LOGO
	 * @return array
	 */
	/* public function getShopName(){
		//要查询的字段
		$array = array(
				'sh.company_name',
				'sd.avatar',
		);
		$alias = 'sh';//定义当前数据表的别名
		$join  = array(
				'ls_shopkeeper_detail sd on sh.id = sd.sid'
		);//join可以使用array
		$where = 'sh.id = sd.sid';
		$order = 'sh.id';
		$res=$this  ->alias($alias)
		->join($join)
		->where($where)
		->order($order)
		->field($array)
		->select();
		return $res;
	}  */
	//生成
	/* $num=substr(uniqid(),0,10);
	$login_email="598714408@qq.com";
	$password=md5("123456");
	$ctime=date('Y-m-d H:i:s',time());
	$data['company_name'] = $comname;
	$data['tel'] = $phone;
	$data['login_email'] = $login_email;
	$data['password'] = $password;
	$data['ctime'] = $ctime;
	$res=$this->add($data);

	if($res==true){
	    return true;
	} */

	/**
	 *插入新添加的商家机构
	 * @return array
	 */
	public function  insertNewShopkeeper($cateid,$areaid,$comname,$nickname,$phone,$avatar,$areaname){

	    $uniqid=uniqid();
	    $num=substr($uniqid,0,10);
	    $login_email=$num."@qq.com";
	    $password=encrypt_passwd("123456");
	    $time=time();
	    $ctime=date('Y-m-d H:i:s',$time);
	    $data['company_name'] = $comname;
	    $data['tel'] = $phone;
	    $data['login_email'] = $login_email;
	    $data['password'] = $password;
	    $data['ctime'] = $ctime;
	    $data['login_phone'] = "";
	    $data['company_email'] = $num."@cc.com";
	    $data['status'] = "0";
	   $sid= $this->add($data);
	    if($sid==true)
	    {
	        $ShopkeeperDetail= D('ShopkeeperDetail');
	        $data['sid'] = $sid;
	        $data['cateid'] = $cateid;
	        $data['areaid'] = $areaid;
	        $data['nickname'] = $nickname;
	        $data['area_detail'] = $areaname;
	        if($avatar){
	            $data['avatar'] =$avatar;
	        }else{
	            $data['avatar'] ='./Public/Home/img/shop_default_avatar.jpg';
	        }
	        $res= $ShopkeeperDetail->add($data);
	        if($res==true){
	            return $sid;
	        }else{
	            return false;
	        }
	    }else{
	        return false;
	    }



	}

	/**
	 * 更新红点的最新时间
	 * @param unknown $shopkeeperId
	 * @param unknown $time
	 * @param unknown $type
	 * @return \Think\false
	 */
	public function updateRedot($shopkeeperId, $type, $time) {
		$fmt = 'REPLACE INTO __SHOP_REDOT__ (`sid`,`type`,`time`) VALUES(%d,"%s",%d)';
		return M()->execute(sprintf($fmt, $shopkeeperId, $type, $time));
	}

	/**
	 * 修改密码
	 * @param unknown $shopkeeperId
	 * @param unknown $password
	 * @return string|bool
	 */
	public function changePassword($shopkeeperId, $password) {
		// 检验合法性
		if (!preg_match('/^\S{6,12}$/', $password)) {
			return '密码不合法';
		}

		// 修改咯
		$this->where('id = %d', $shopkeeperId)->setField('password', encrypt_passwd($password));

		cookie('shop_auto_login', null);
		return true;
	}

	/**
	 * 判断邮箱或者手机存不存在
	 * @param string $type "email"或者"phone"
	 * @param string $arg  email或者phone的值
	 */
	public function checkExist($type, $arg) {
		// 看看这个东西存不存在
		$result = $this->field('1')
						->where('login_%s = "%s"', $type, $arg)
						->find();

		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * 看看商家是不是了提交认证审核资料
	 * @param unknown $shopId
	 * @return string|boolean
	 */
	public function checkSendAuth($shopId) {
		$status = $this->where('id = %d', $shopId)->getField('status');

		if ($status === false) {
			$this->getDbError();
		}

		if ($status >= 2) {
			return '您已经提交认证审核资料了！';
		}

		return true;
	}


	//==========================================================
	/**
	 * 后台的商家数据
	 * @param unknown $shopId
	 * @return string|boolean
	 */
        public function getShopkeeperData($pageOffset=0,$perPage=30,$sort='desc', $is_us=1){
            $alias = 'sh';//定义当前数据表的别名
            //要查询的字段
            $array = array(
                'sh.id',//用户id
                'sh.login_phone',
                'sh.login_email',
                'sh.tel',
                'sh.status',
                'sh.company_name',//用户id
                'skd.nickname',
                'skd.area_detail',
            );
            $order='id '.$sort;
            $join  = array(
                'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = sh.id',
            );
            $res=$this  -> alias($alias)
            -> order($order)
            ->join($join)
            -> limit($pageOffset,$perPage)
            -> field($array)
            ->where('is_us = %d', $is_us)
            -> select();
            $ShopInfo=D('ShopInfo');
           foreach ($res as $key=>$value){
                $sid=intval($value['id']);
                $res[$key]['kccount'] = $ShopInfo->where("sid='$sid'")->count('sid');

                $res[$key]['number']=$ShopInfo->where("sid='$sid'")->sum('number');

                // 获取状态文字
                $res[$key]['statusText'] = $this->getStatusStr($value['status']);
            }
            return $res;
        }

        /**
         * 用于后台搜索商家
         * 抄袭getShopkeeperData
         */
        public function findShopkeeperData($pageOffset=0, $perPage=30, $find_type, $find_word, $sort='desc') {
            $alias = 'sh';//定义当前数据表的别名
            //要查询的字段
            $array = array(
                'sh.id',//用户id
                'sh.login_phone',
                'sh.login_email',
                'sh.tel',
                'sh.status',
                'sh.company_name',//用户id
                'skd.nickname',
                'skd.area_detail',
            );
            $order='id '.$sort;
            $join  = array(
                'left join __SHOPKEEPER_DETAIL__ skd on skd.sid = sh.id',
            );

            // 搜索
            $types = ['company_name', 'login_phone', 'login_email'];
            if (in_array($find_type, $types)) {
                $this->where($find_type . ' like "%%%s%%"', $find_word);
            }

            $res=$this  -> alias($alias)
            -> order($order)
            ->join($join)
            -> limit($pageOffset,$perPage)
            -> field($array)
            -> select();

            $ShopInfo=D('ShopInfo');
           foreach ($res as $key=>$value){
                $sid=intval($value['id']);
                $res[$key]['kccount'] = $ShopInfo->where("sid='$sid'")->count('sid');

                $res[$key]['number']=$ShopInfo->where("sid='$sid'")->sum('number');

                // 获取状态文字
                $res[$key]['statusText'] = $this->getStatusStr($value['status']);
            }

            return $res;
        }

        /**
         * 获取后台搜索的总数
         */
        public function countFindShopkeeperData($find_type, $find_word) {
            // 这里先获取总数
            $types = ['company_name', 'login_phone', 'login_email'];
            if (in_array($find_type, $types)) {
                $this->where($find_type . ' like "%%%s%%"', $find_word);
            }
            $count = $this->count();
        }

        /**
         * 对 商家数据的信息进行分页
         * @param unknown $curPage
         * @param unknown $perPage
         * @return Ambigous <\Common\Util\multitype:number, multitype:number >
         */
        public function shopDataPage($curPage,$perPage, $is_us){
            $count= $this->where('is_us = %d', $is_us)->count('id'); // 查询总记录数
            import("Common.Util.AjaxPage");//分页调用Common\Util\AjaxPage下的这个文件
            $page = new \Common\Util\AjaxPage($count,$curPage,$perPage);  //--实例化调用自己写的分页类----- 实例化分页类 传入总记录数和每页显示的记录数 $pageArray=$Page->getCounts();
            $pageArray = $page->getCounts();
            return  $pageArray;
        }


        /**
         * 统计分数
         * @param unknown $resArr
         * @return number
         */
        public function sumCreidt($shopkeeperId) {
           // 需要获取的字段
           $fields = [
                's.tel',
                'sd.avatar',
                'sd.environ',
                'sd.nickname',
                'sd.age',
                'sd.website',
                'sd.teacher_power',
                'sd.cateid',
                'sd.features',
                'sd.remark',
                'sd.areaid',
                'sd.area_raw',
            ];

            $resArr = $this->alias('s')
                           ->field($fields)
                           ->join('__SHOPKEEPER_DETAIL__ sd on sd.sid = s.id')
                           ->where('s.id = %d', $shopkeeperId)
                           ->find();

            $credit = 0;

            $defaultAvatar = 'shop_avatar/shop_default_avatar.jpg';
            $defaultEnviron = 'shop_environ/shop_default_environ.jpg';

            if ($resArr['avatar'] && $resArr['avatar'] != $defaultAvatar) {
                $credit += 10;
            }
            if ($resArr['environ'] && $resArr['environ'] != $defaultEnviron) {
                $credit += 10;
            }
            if ($resArr['tel']) {
                $credit += 8;
            }
            if ($resArr['nickname']) {
                $credit += 8;
            }
            if ($resArr['age']) {
                $credit += 8;
            }
            if ($resArr['website']) {
                $credit += 8;
            }
            if ($resArr['teacher_power']) {
                $credit += 8;
            }
            if ($resArr['cateid']) {
                $credit += 8;
            }
            if ($resArr['features']) {
                $credit += 8;
            }
            if ($resArr['remark']) {
                $credit += 8;
            }
            if ($resArr['areaid']) {
                $credit += 8;
            }
            if ($resArr['area_raw']) {
                $credit += 8;
            }

            return $credit;
        }

        public function checkRegSend() {
            $logintype = trim(I('post.typevalue'));//获取,并且去掉两端的空格
    // 	    $firstname = trim(I('post.firstname'));//获取,并且去掉两端的空格
    // 	    $lastname  = trim(I('post.lastname'));//获取,并且去掉两端的空格
            if (empty($logintype)) {
                return '邮箱或者手机号码不能为空';
            }
            $type = trim(I('post.type'));//获取，并且去掉两端的空格
            switch ($type){
                case 'email':
                    $exitsEmailReg = $this->existemail($logintype);
                    if($exitsEmailReg){
                        $result = '邮箱已经被注册！';
                    }else {
                        $result = D('Common/User')->sendRegEmail($logintype);
                    }
                    break;
                case 'phone':
                    $exitsPhoneReg = $this->existphone($logintype);
                    if($exitsPhoneReg){
                        $result = '手机号码已经被注册！';
                    }else {
                        $result = D('Common/User')->sendRegPhone($logintype);
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
	 * 检查某个邮箱是否存在
	 * @param string $emailvalue
	 * @return boolean
	 */
	public function existemail($emailvalue=''){
		$exist=$this->where("login_email='%s'",$emailvalue)->getField('id');
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
		$exist=$this->where("login_phone='%s'",$phonevalue)->getField('id');
		if(!$exist){
			return false;
		}
		return true;
	}

}
