<?php
namespace Home\Controller;

use Common\Model\ShopkeeperModel;

class ShopkeeperController extends \Common\Controller\CommonController {

	/**
	 * 商家模型
	 * @var ShopkeeperModel
	 */
	public $shopkeeperModel;

	/**
	 * 初始化
	 */
	public function _initialize() {
		$this->shopkeeperModel = D("Common/Shopkeeper");
	}

	public function index() {
// 		$aArr = array('info', 'editor', 'mycourse', 'v', 'tend', 'msg', 'course', 'cont');
// 		foreach ($aArr as $v) {
// 			echo "<h1><a style=\"font-size: 50px;\" href=\"".U($v)."\">$v</a></h1>";
// 		}
	}

	/**
	 * 商家注册开始
	 */
	public function signUpStart() {
// 		$resArr = D('Common/ShopInfo')->listInfo(0, 1, 'desc', null, null);
// 		$resArr = $resArr[0];

		// 获取额外的信息
// 		$resArr['ctime'] = transDate($resArr['ctime']);
// 		$resArr['avatar'] = $this->getAvatar($resArr['avatar']);
// 		$resArr['phoneAndVStatus'] = $this->shopkeeperModel->hasPhoneAndV($resArr['sid']);

// 		$this->resArr = $resArr;

		//$this->display();
        $this->display("bus_reg");
	}

	/**
	 * 商家注册第一步
	 */
	public function signUpSetpOne() {
		$this->display();
	}

	/**
	 * 商家注册第二步
	 */
	public function signUpSetpTwo() {
		$this->err = cookie('signup_err');
		cookie('signup_err', null);
		$this->display();
	}

	/**
	 * 商家注册最后一步
	 */
	public function signUpSetpThree() {
		$this->data = session('shopSignUp');
		$this->display();
	}

	/**
	 * 处理商家邮箱激活
     */
    public function handleActive($token = '') {
    	$result = D('Common/ShopkeeperActive')->handleActive($token);
//     	$this->simpleAjaxReturn($result);

    	if ($result !== true) {
    		return print($result);
    	}

    	$this->redirect('signin');
    }

    /**
     * 商家登陆
     */
    public function signIn() {
    	$this->display();
    }

    /**
     * 忘记密码填写邮箱或者手机页面
     */
    public function forgetPassword() {
    	$this->display('forget_password');
    }

    /**
     * 验证码填写，密码重置页面
     */
    public function resetPassword() {
    	$this->display('reset_password');
    }

    public function editor() {
    	// 检测有没有登陆
    	if (!session('?shopkeeper.id')) {
    		return $this->display('s_sign_timeout_loc');
    	}
    	//echo session('shopkeeper.id');
    	// 获取信息
    	$resArr = D("Common/ShopkeeperDetail")->info(session('shopkeeper.id'), false);

    	// 先计个分再说
        //$resArr['credit'] =$this->sumCreidt($resArr);
        $resArr['credit'] = $this->shopkeeperModel->sumCreidt(session('shopkeeper.id'));

    	// 如果商家没有上传头像，就给他默认头像
    	$resArr['avatar'] = $this->getAvatar($resArr['avatar']);

        // 判断场景图是不是默认的，如果是默认的就删掉
        if ($resArr['environ'] == 'shop_environ/shop_default_environ.jpg') {
            $resArr['environ'] = '';
        }
    	// 特性字符串
    	if (!$resArr['features']) {
    		$resArr['features'] = '';
    	}
    	$resArr['featureArr'] = $this->getFeaturesArr($resArr['features']);

    	// 获取所有与areaid同级的地区
    	if ($resArr['areaid']) {
    		$this->allArea = D("Common/Area")->getSameLevel($resArr['areaid']);
    		$this->areaPid = D("Common/Area")->getParentId($resArr['areaid']);
    	} else {
    		$this->areaPid = 0;
    	}

    	// 获取所有广州地区
    	$this->allZone = D("Common/Area")->getGuangZhouZone();

        // 判断是否填写了分类，需求是填写过分类下次就不给改了
        // $this->category = M('Category')->field('id, catename')->select();
        $this->allCategory = D('Common/Category')->getAllTwoLevel();
        
    	// 显示
    	$this->resArr = $resArr;

    	$this->display();
    }

    /**
     * 处理修改商家个人资料
     */
    public function handleEdit() {
    	$result = D("Common/ShopkeeperDetail")->handleEdit();
    	if ($result !== true) {
            header('Content-Type: text/html;charset=utf-8');
    		return print($result);
    	}

        //
    	$this->redirect('Index/index');
    }

    /**
     * 商家个人详细信息
     */
    public function info() {
    	if (!$id = I('get.id', 0, 'intval')) {
    		if (!session('?shopkeeper.id')) {
    			return $this->display('s_sign_timeout_loc');
    		}
    		$id = session('shopkeeper.id');
    	}

    	$resArr = D("Common/ShopkeeperDetail")->info($id);
    	// 如果商家没有上传头像，就给他默认头像
		$resArr['avatar'] = $this->getAvatar($resArr['avatar']);
		$resArr['features'] = $this->getFeaturesArr($resArr['features']);
		// 显示
    	$this->resArr = $resArr;
    	$this->display();
    }

    /**
     * 商家自己发布的信息
     */
    public function mycourse($id = 0) {
    	// 看是不是我啊
    	$isMe = false;

    	// 是我了
    	if (session('?shopkeeper') && session('shopkeeper.id') == $id) {
    		$isMe = true;
    	}

    	$this->isMe = $isMe;
    	$this->sid = $id;
    	$this->display();
    }

    /**
     * 修改认证信息
     */
    public function v() {
    	// 看有没有的登陆
    	if (!session('?shopkeeper.id')) {
    		return $this->display('s_sign_timeout_loc');
    	}

    	// 取出闪存数据
    	$err['company_name'] = cookie('flash_v_company_name');
    	$err['legal_name'] = cookie('flash_v_legal_name');
    	$err['tel'] = cookie('flash_v_tel');
    	$err['msg'] = cookie('flash_v_msg');
    	// 删除闪存数据
    	cookie('flash_v_company_name', null);
    	cookie('flash_v_legal_name', null);
    	cookie('flash_v_tel', null);
    	cookie('flash_v_msg', null);

    	$this->err = $err;

    	$this->display();
    }

    /**
     * 处理修改商家认证信息请求
     */
    public function handleV($isPhone=false) {
    	// 看有没有的登陆
    	if (!session('?shopkeeper.id')) {
    		return $this->display('s_sign_timeout_loc');
    	}

    	$result = D('Common/ShopkeeperAuth')->handleUpsert();
    	if ($result !== true) {
    		// 保存闪存 cookie
    		cookie('flash_v_company_name', I('post.company_name'));
    		cookie('flash_v_legal_name', I('post.legal_name'));
    		cookie('flash_v_tel', I('post.tel'));
    		cookie('flash_v_msg', $result);
    	}

        if ($isPhone) {
            $this->redirect('Desktop/Shopkeeper/certification', ['t'=>time()]);
        } else {
            $this->redirect('v', ['t'=>time()]);
        }
    }

    /**
     * 看谁报名了
     */
    public function tend() {
    	$this->display();
    }

    /**
     * 商家消息
     */
    public function msg() {
    	$this->display();
    }

    /**
     * 发布课程
     */
    public function course() {
    	// 看有没有的登陆
    	if (!session('?shopkeeper.id')) {
    		return $this->display('s_sign_timeout_loc');
    	}

        // 如果分数小于100就说明商家资料不完善
        $this->credit = $this->shopkeeperModel->sumCreidt(session('shopkeeper.id'));

    	// 获取所有广州地区
    	$this->allZone = D("Common/Area")->getGuangZhouZone();

//     	// 获取分类
//     	$this->categorys = D('Category')->select();
//     	$this->numSlide = ceil(count($this->categorys) / 4);

        // 获取截止日期数组
        $this->timeList = time_list_date_format();

        //$modes  = [];
        //for ($i = 1; $i <= 4; $i++) {
        //    $modes[$i] = $this->mapCourseMode($i);
        //}

    	$this->modes = C('mode');

    	$this->display();
    }

    /**
     * 发布课程成功
     */
    public function courseSuccess() {
    	$this->display('course-success');
    }

    /**
     * 某一条课程信息的详细界面
     */
    public function cont($id = 0) {
        if (!$id) {
            return $this->redirect("Index/notFound");
        }
		$resArr = D('Common/ShopInfo')->listInfo(0, 1, 'desc', $id, null, true);
		if ($resArr === null) {
            return $this->redirect("Index/notFound");
		}
		$resArr = $resArr[0];

		// 获取额外的信息
		$resArr['ctime'] = transDate($resArr['ctime']);
		$resArr['avatar'] = $this->getAvatar($resArr['avatar']);

		// 每次访问 +1
		M('ShopInfo')->where('id = %d', $id)->setInc('view');

        // 看看用户是否已经收藏或者报名
        $isUserEnroll = '';
        $isUserStar = '';
        if (session('?user')) {
            $isUserStar = M('UserCollect')->where('shopid = %d and uid = %d', $id, session('user.id'))
                                            ->getField('id');

            $isUserEnroll = M('ShopInfoUser')->where('shop_info_id = %d and user_id = %d', $id, session('user.id'))
                                            ->getField('id');
        }

        // 课程信息数据
		$this->resArr = $resArr;

        // 判断用户是否登录
        $this->isUserSignIn = session('?user.id');
        // 判断用户是否报名了
        $this->isUserEnroll = $isUserEnroll;
        // 判断用户是否收藏了
        $this->isUserStar = $isUserStar;
        // 判断用户是否有头像
        // 获取用户的分数和是否有头像
        if (session('?user.id')) {
            $this->userScore = D('Common/User')->userScore();
            $this->isUserHasAvatar = D('Common/User')->userAvatars(session('user.id'));
        } else {
            $this->userScore = 0;
            $this->isUserHasAvatar = "";
        }

        //----游客头像
        $this->visitor_avatar=C('visitor_config')['avatar'];
        //----商家是否登录了
        $this->isShopkeepSignIn=session('?shopkeeper.id');

		$this->display();
    }

	/**
	 * 如果商家没有上传头像，就给他默认头像
	 * @param unknown $avatarPath
	 * @return string
	 */
    public function getAvatar($avatarPath) {
    	if (!$avatarPath) {
    		return C('TMPL_PARSE_STRING')['__HIMG__'] . '/shop_default_avatar.jpg';
    	}
    	return C('TMPL_PARSE_STRING')['__UPLOAD__'] . '/' . $avatarPath;
    }

    /**
     * 系统
     */
    public function system() {
    	// 看有没有的登陆
    	if (!session('?shopkeeper.id')) {
    		return $this->display('s_sign_timeout_loc');
    	}

    	// 获取系统消息更新数量
    	$numUpdatedSysMsg = D('Common/ShopMsg')->numUpdate(session('shopkeeper.id'));
    	if (!is_int($numUpdatedSysMsg)) {
    		$numUpdatedSysMsg = 0;
    	}
    	$this->numUpdatedSysMsg = $numUpdatedSysMsg;

    	$this->display();
    }

    /**
     * 修改密码
     */
    public function resetPw() {
    	// 看有没有的登陆
    	if (!session('?shopkeeper.id')) {
    		return $this->display('s_sign_timeout_loc');
    	}
    	$this->display('reset_pw');
    }

    /**
     * 意见反馈
     */
    public function feedback() {
    	// 看有没有的登陆
    	if (!session('?shopkeeper.id')) {
    		return $this->display('s_sign_timeout_loc');
    	}
    	$this->display();
    }

    /**
     * 意见反馈
     */
    public function systemInfo() {
    	// 看有没有的登陆
    	if (!session('?shopkeeper.id')) {
    		return $this->display('s_sign_timeout_loc');
    	}
    	$this->display('system-info');
    }

    /**
     * 退出登录
     */
    public function signOut() {
		// 删除sesion和记住我cookie
		session('shopkeeper', null);
		cookie('shop_auto_login', null);

		$this->redirect('Index/index', ['t'=>time()]);
    }

    /**
     * 微信静默登录处理方法
     */
    public function wxToken() {
        $bool = $this->preWxToken();

        // 曾经用微信登陆过，直接登录成功
        if ($bool) {
            return $this->redirect('Index/index');
        }

	    $Owx = new \OauthAction();
        $Owx->re_index(wx_app_id , SHOP_RE_WX_URI);
    }

    /**
     * 微信授权登录处理方法
     */
    public function reWxToken() {
        $bool = $this->preWxToken();

        // 这里能够登录成功，有点诡异的
        if ($bool) {
            return $this->redirect('Index/index');
        }

	    $Owx = new \OauthAction();

        // 根据微信号注册一个商家
        $code = I('get.code', '', '');
	    $wxOpenId = $_SESSION['openid'];
	    $wxToken  = $_SESSION['access_token'];

	    $wxInfo   = $Owx->wxGetUserInfo($wxToken,$wxOpenId,$code);
	    $wxAddSt  = D('Common/Wx')->addWxShopkeeper($wxOpenId,$wxInfo);

	    if ($wxAddSt!==true){
            die('商家：微信登录失败');
	    }

        return $this->redirect('Index/index');
    }

    /**
     * 微信静默登录和授权登录的共同部分（获取openid）
     * @return bool  是否已经登陆成功
     */
    protected function preWxToken() {
	    require_once('./Api/wx/config.php');
	    require_once('./Api/wx/OauthAction.class.php');

        // 获取access_token
	    $Owx = new \OauthAction();
        $code = I('get.code', '', '');
	    $wx_token = $Owx->access_token( wx_app_id , wx_app_secret ,$code);

	    $_SESSION['access_token']  = $wx_token['access_token'];
	    $_SESSION['openid']  = $wx_token['openid'];
	    $_SESSION['expires_in']    = $wx_token['expires_in'];
	    $_SESSION['refresh_token'] = $wx_token['refresh_token'];

        // 检测微信号(openid)是否存在数据库
	    $Wx = D('Wx');
	    $check_exist = $Wx->checkopenid($wx_token['openid']);
	    if ($check_exist!==false) { // 曾经微信登录过
            // 如果这里因为某些原因登录不成功，也不管了
            D('Common/Shopkeeper')->wxLogin($check_exist['sid']);
            return true;
        }

        return false;
    }

	/**
	 * 将features分割成数组
	 * @param unknown $features
	 * @return string
	 */
    protected function getFeaturesArr($features) {
   		$arr  = explode('|', $features);
   		foreach ($arr as $key => $value) {
   			if ($value === "") {
   				unset($arr[$key]);
   			}
   		}
   		return $arr;
    }

    /**
     * 统计分数
     * @param unknown $resArr
     * @return number
     */
    protected function sumCreidt($resArr) {
    	$credit = 0;

    	if ($resArr['avatar']) {
    		$credit += 20;
    	}
    	if ($resArr['environ']) {
    		$credit += 20;
    	}
    	if ($resArr['nickname']) {
    		$credit += 10;
    	}
    	if ($resArr['features']) {
    		$credit += 10;
    	}
    	if ($resArr['remark']) {
    		$credit += 10;
    	}
    	if ($resArr['tel']) {
    		$credit += 10;
    	}
    	if ($resArr['areaid']) {
    		$credit += 10;
    	}
    	if ($resArr['area_raw']) {
    		$credit += 10;
    	}

    	return $credit;
    }


}
