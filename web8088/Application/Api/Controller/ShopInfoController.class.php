<?php

namespace Api\Controller;
use Api\Controller\CommonController;
use Common\Model\ShopInfoModel;

/**
 *
 * @author jmjoy
 *
 */
class ShopInfoController extends CommonController {

	/**
	 * 商家组团信息模型
	 * @var ShopInfoModel
	 */
	public $shopInfoModel;

	public function _initialize() {
		$this->shopInfoModel = D('Common/ShopInfo');
	}

	public function listInfo() {
		$resArr = $this->shopInfoModel->listInfo();
		$this->ajaxReturn($resArr);
	}

	/**
	 *
	 * @param number $id
	 */
	public function getInfoById($id = 0) {
		$res = $this->shopInfoModel->getInfoById($id);
		$this->ajaxReturn($res);
	}

	/**
	 * 增加课程
	 */
	public function addCourse() {
		if (!session("?shopkeeper.id")) {
			return $this->ajaxReturn(array(
					"status"	=>	400,
					"msg"		=>	"商家未登陆",
			));
		}

        // 判断有没有发布课程的权限
        $result = $this->checkShopkeeperPermission();
        if ($result !== true) {
            return $this->ajaxReturn([
                'status'    =>  403,
                'msg'       =>  $result,
            ]);
        }

		$result = $this->shopInfoModel->addCourse1();
		$this->simpleAjaxReturn($result);
	}

	/**
	 * 获取报名的人的头像信息
	 * @param number $id
	 */
	public function getUsersInfo($id = 0, $page = 1) {
		$perPage = 9;

		$totalRow = $this->shopInfoModel->countUserInfo($id);
		$resArr = $this->shopInfoModel->getUsersInfo($id, $page, $perPage);
		$isLast = $this->isLastPage($page, $perPage, $totalRow);

		$this->ajaxReturn(array(
				'data'		=>	$resArr,
				'isLast'	=>	$isLast,
		));
	}

	/**
	 * 获取两条热门的信息
	 * @param number $id
	 */
	protected function getSimpleInfo($id = 0, $page = 1, $perPage = 3) {
		$resArr = $this->shopInfoModel->getSimpleInfo($id, $page, $perPage);
		foreach ($resArr as $key => $value) {
			$resArr[$key]['ctime'] = transDate($value['ctime']);
			$resArr[$key]['avatar'] = A('Home/Shopkeeper')->getAvatar($value['avatar']);
		}
		$this->ajaxReturn([
				'status'	=>	200,
				'data'		=>	$resArr,
		]);
	}

	/**
	 * 获取十条简单的数据
	 * @param number $id
	 * @param number $page
	 */
	public function getSimpleInfoTen($id = 0, $page = 1) {
		$this->getSimpleInfo($id, $page, 10);
	}

	/**
	 * 获取三条简单的数据
	 * @param number $id
	 * @param number $page
	 */
	public function getHotShopInfos($id = 0, $page = 1) {
		$perPage = 3;

		$totalRow = $this->shopInfoModel->countInfoBySid($id);
		$resArr = $this->shopInfoModel->getSimpleInfo($id, $page, $perPage);
		$isLast = $this->isLastPage($page, $perPage, $totalRow);

		foreach ($resArr as $key => $value) {
			$resArr[$key]['ctime'] = transDate($value['ctime']);
			$resArr[$key]['avatar'] = A('Home/Shopkeeper')->getAvatar($value['avatar']);
		}

		$this->ajaxReturn(array(
				'data'		=>	$resArr,
				'isLast'	=>	$isLast,
		));
	}

	/**
	 * 用户报名课程信息
	 */
	public function enroll() {
		$nowhref=I('post.nowhref');


		$info_id = I('post.id', 0, 'intval');
		$phone = I('post.phone');
		$smsVerify = I('post.sms_verify');
		// 过滤和验证
		if (!session('?user.id')) {
		    session('historyhref',$nowhref);
//---------------------------------------------------------------游客-----------------------
		    $visitor_id=I('post.visitorid')?I('post.visitorid'):0;
            if ($visitor_id!=0&&!session('?visitor')){
                session('visitor.id',$visitor_id);
            }
	        if (!session('?visitor')){
		        $visitor = D('Visitor');
		        $addOneId = $visitor->addOneVisitor();//增加一个游客
		        session('visitor.id',$addOneId);
	        }else {
	            $addOneId=session('visitor.id');
	        }
		    $result = D('Common/ShopInfo')->visitorEnroll($info_id,$addOneId);
		    if ($result!==true){
		        $this->ajaxReturn(array(
		            'status'  =>  400,
		            'msg'     =>  $result,
		            'visitor' =>  $addOneId,
		        ));
		    }
			return $this->ajaxReturn(array(
					'status'	=>	200,
					'msg'		=>	$result,
		            'visitor'   =>  $addOneId,
			));
//------------------------------------------------------------------游客--------------------
// 			return $this->ajaxReturn(array(
// 					'status'	=>	403,
// 					'msg'		=>	'请先登录',
// 			));
		}

        // 判断用户执行该操作的权限
        $result = $this->checkUserPermisson();
        if ($result !== true) {
            return $this->ajaxReturn([
                'status'    =>  403,
                'msg'       =>  $result,
            ]);
        }

// 		$info_id = I('post.id', 0, 'intval');
//         $phone = I('post.phone');
//         $smsVerify = I('post.sms_verify');

		$result = D('Common/ShopInfo')->enroll($info_id, session('user.id'), $phone, $smsVerify);
		$this->simpleAjaxReturn($result);
	}

	/**
	 * 收藏商家课程信息
	 */
	public function star() {
		// 过滤和验证
		if (!session('?user.id')) {
			return $this->ajaxReturn(array(
					'status'	=>	400,
					'msg'		=>	'请先登录',
			));
		}

		$info_id = I('post.id', 0, 'intval');

		$result = D('Common/ShopInfo')->star($info_id, session('user.id'));
		$this->simpleAjaxReturn($result);
	}

	/**
	 * 商家自己发布的信息
     * @param int $is_desktop    如果是桌面版，就用1
	 */
	public function mycourse($id = 0, $page = 1, $is_desktop=0) {
		// 看有没有的登陆（需求改了，要求大家都能看到）
		if (!$id) {
			if (!session('?shopkeeper.id')) {
				return $this->simpleAjaxReturn('商家还没有登录');
			} else {
				$id = session('shopkeeper.id');
			}
		}

		// 安全点
		$id = intval($id);

		// 获取商家的课程信息
		$perPage = 6;
		$pageOffset = (intval($page) - 1) * $perPage;
		$resArr = D('Common/ShopInfo')->listInfo($pageOffset, $perPage, 'desc', null, $id);

		foreach ($resArr as $key => $value) {
			$resArr[$key]['avatar'] = A('Home/Shopkeeper')->getAvatar($value['avatar']);
		}

		// 获取商家的手机和审核状态
		$phoneAndVStatus = D('Common/Shopkeeper')->hasPhoneAndV($id);

		foreach ($resArr as $key => $value) {
			// 手机和审核状态
			$resArr[$key]['hasPhone'] = $phoneAndVStatus['phone'];
			$resArr[$key]['hasV'] = $phoneAndVStatus['v'];
			// 时间
			$resArr[$key]['ctime'] = transDate($value['ctime']);
		}

        if ($is_desktop) {
            // 这里是获取该商家的被报名总数
            $count = $this->shopInfoModel->countInfoBySid($id);
            $totalPages = ceil($count / $perPage);
            // 返回获取到的数据
            return $this->ajaxReturn(array(
                    'status'	=>	200,
                    'data'		=>	$resArr,
                    'totalPages'    =>  $totalPages,
            ));
        }

		// 返回获取到的数据
		$this->ajaxReturn(array(
				'status'	=>	200,
				'data'		=>	$resArr,
		));
	}

	/**
	 * 获取登录的商家的报名用户
	 * @param number $page
	 */
	public function getEnrolls($page = 1) {
		// 看有没有的登陆
		if (!session('?shopkeeper.id')) {
			return $this->simpleAjaxReturn('商家还没有登录');
		}

		$perPage = 4;
		$resArr = $this->shopInfoModel->getEnroll(session('shopkeeper.id'), $page, $perPage);

		// 处理时间
		foreach ($resArr as $key => $value) {
			$resArr[$key]['ctime'] = transDate($value['ctime']);
		}

		$this->ajaxReturn([
				'status'	=>	200,
				'data'		=>	$resArr,
		]);
	}

	/**
	 * 获取登录的商家的报名用户，还带了分页的参数呢
	 * @param number $page
	 */
    public function getDesktopEnrolls($page = 1) {
		// 看有没有的登陆
		if (!session('?shopkeeper.id')) {
			return $this->simpleAjaxReturn('商家还没有登录');
		}

		$perPage = 4;
		$resArr = $this->shopInfoModel->getEnroll(session('shopkeeper.id'), $page, $perPage);

		// 处理时间
		foreach ($resArr as $key => $value) {
			$resArr[$key]['ctime'] = transDate($value['ctime']);
		}

        // 这里是获取该商家的被报名总数
        $count = $this->shopInfoModel->countEnroll(session('shopkeeper.id'));
        $totalPages = ceil($count / $perPage);

		$this->ajaxReturn([
				'status'        =>  200,
				'data'          =>  $resArr,
                'totalPages'    =>  $totalPages,
		]);
    }

	/**
	 * 根据商家的ID获取评论
	 * @param number $page
	 */
	public function getComments($page = 1) {
		// 看有没有的登陆
		if (!session('?shopkeeper.id')) {
			return $this->simpleAjaxReturn('商家还没有登录');
		}

		$perPage = 4;
		$resArr = $this->shopInfoModel->getComment(session('shopkeeper.id'), $page, $perPage);

		// 处理时间
		if ($resArr) {
			foreach ($resArr as $key => $value) {
				$resArr[$key]['ctime'] = transDate($value['ctime']);
			}
		}

		$this->ajaxReturn([
				'status'	=>	200,
				'data'		=>	$resArr,
		]);
	}

	/**
	 * 根据商家的ID获取评论，供pc版使用，需要分页信息
	 * @param number $page
	 */
    public function getDesktopComments($page = 1) {
		// 看有没有的登陆
		if (!session('?shopkeeper.id')) {
			return $this->simpleAjaxReturn('商家还没有登录');
		}

		$perPage = 4;
		$resArr = $this->shopInfoModel->getComment(session('shopkeeper.id'), $page, $perPage);

		// 处理时间
		if ($resArr) {
			foreach ($resArr as $key => $value) {
				$resArr[$key]['ctime'] = transDate($value['ctime']);
			}
		}

        // 这里是获取该商家的被报名总数
        $count = $this->shopInfoModel->countComment(session('shopkeeper.id'));
        $totalPages = ceil($count / $perPage);

		$this->ajaxReturn([
				'status'	=>	200,
				'data'		=>	$resArr,
                'totalPages'    =>  $totalPages,
		]);
    }

	/**
	 * 获取热门的课程
	 */
	public function gener() {
		$data = $this->shopInfoModel->gener();

		foreach ($data as $key => $value) {
			$data[$key]['avatar'] = A('Home/Shopkeeper')->getAvatar($value['avatar']);
		}

		$this->ajaxReturn([
				'status'	=>	200,
				'data'		=>	$data,
		]);
	}

	/**
	 * 看看评论有没有更新啊
	 * @param number $id
	 * @param number $time
	 */
	public function numEnrollUpdate() {
		// 看商家有没有登录
		$this->checkShopSignIn();

		$result = $this->shopInfoModel->numEnrollUpdate(session('shopkeeper.id'));

		if (!is_int($result)) {
			return $this->ajaxReturn([
					'status'	=>	400,
					'msg'		=>	$result,
			]);
		}

		$this->ajaxReturn([
				'status'	=>	200,
				'data'		=>	$result,
		]);
	}

	/**
	 * 获取非常简单的商家课程信息
	 * @param number $sid
	 */
	public function getVerySimpleInfo($sid = 0) {
		// 看商家有没有登录
		$this->checkShopSignIn();

		$resArr = $this->shopInfoModel->getVerySimpleInfo($sid);

		$this->ajaxReturn([
				'status'	=>	200,
				'data'		=>	$resArr,
		]);
	}

	/**
	 * 删除一条课程信息，不是在说笑
	 */
	public function deleteInfo($id = 0) {
		// 看商家有没有登录
		$this->checkShopSignIn();

		$result = $this->shopInfoModel->deleteInfo(session('shopkeeper.id'), $id);

		$this->simpleAjaxReturn($result);
	}

    /**
     * 看有没有达到一天的发布课程上限
     */
    public function checkCanSend() {
		// 看商家有没有登录
		$this->checkShopSignIn();

        // 判断有没有发布课程的权限
        $result = $this->checkShopkeeperPermission();
        if ($result !== true) {
            return $this->ajaxReturn([
                'status'    =>  403,
                'msg'       =>  $result,
            ]);
        }

        $result = $this->shopInfoModel->countShopInfoToday(session('shopkeeper.id'));

        if (!is_int($result)) {
            return $this->ajaxReturn([
                'status'    =>  500,
                'msg'       =>  $result,
            ]);
        }

        if ($result > 10) {
            return $this->ajaxReturn([
                'status'    =>  400,
                'msg'       =>  '对不起，您今日之内已经发送超过10条课程信息，请明天再发',
            ]);
        }

        return $this->ajaxReturn([
            'status'    =>  200,
            'count'     =>  $result,
        ]);
    }


    /**
     * 发送短信验证码
     */
    public function sendSmsVerify() {
        $phone = I('post.phone');

        $result = $this->shopInfoModel->sendSmsVerify($phone);

        $this->simpleAjaxReturn($result);
    }

}
