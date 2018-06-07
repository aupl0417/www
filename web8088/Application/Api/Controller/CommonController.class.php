<?php

namespace Api\Controller;

class CommonController extends \Common\Controller\CommonController {

	/**
	 * 我弄的简单的返回接口的方式！
	 * @param boolean|string $result
	 */
	public function simpleAjaxReturn($result) {
		if ($result !== true) {
			$this->ajaxReturn(array(
					'status'	=>	400,
					'msg'		=>	$result,
			));
		}
		$this->ajaxReturn(array(
				'status'	=>	200,
				'msg'		=>	'',
		));
	}

	/**
	 * 判断是不是最后一页
	 * @param unknown $nowPage
	 * @param unknown $perPage
	 * @param unknown $totalRow
	 * @return boolean
	 */
	protected function isLastPage($nowPage, $perPage, $totalRow) {
		$totalPage = ceil(floatval($totalRow / $perPage));
		return $nowPage == $totalPage;
	}

	/**
	 * 检查商家是否登录了，没有登录返回403
	 */
	protected function checkShopSignIn() {
		// 看有没有登录
		if (!session('?shopkeeper')) {
			$this->ajaxReturn([
					'status'	=>	403,
					'msg'		=>	'商家还没登录',
			]);
		}
	}

    /**
     * 用户要执行报名、评论之类的操作之前要检查一下权限
     */
    protected function checkUserPermisson() {
        // 判断用户有没有头像
        $isUserHasAvatar = D('Common/User')->userAvatars(session('user.id'));
        if (!$isUserHasAvatar) {
            return '您还没有上传头像，不能执行该操作呢';
        }

        // 判断分数有没有超过40分
        $userScore = D('Common/User')->userScore();
        if ($userScore < 40) {
            return '您的资料总分还没有超过40分，不能执行该操作呢';
        }

        return true;
    }

    /**
     * 商家要执行评论之类的操作之前要检查一下权限
     */
    protected function checkShopkeeperPermission() {
        // 这个数组有东西表示有错误
        $errArr = [];
        // 判断商家有没有头像
        $default_avatar = 'shop_avatar/shop_default_avatar.jpg';
        if (!session('?shopkeeper.avatar') || session('shopkeeper.avatar') == $default_avatar) {
            $errArr[] = '头像';
        }

        // 判断商家有没有填写昵称
        if (!session('shopkeeper.nickname')) {
            $errArr[] = '昵称';
        }

        // 判断商家有没有填写教龄
        if (!session('shopkeeper.age')) {
            $errArr[] = '机构年龄';
        }

        // 判断商家有没有填写机构类别
        if (!session('shopkeeper.cateid')) {
            $errArr[] = '机构类别';
        }

        //$score = D('Common/Shopkeeper')->sumCreidt(session('shopkeeper'));
        //if ($score < 40) {
        //    return '您的资料总分还没有超过40分，不能执行该操作呢';
        //}

        if (count($errArr) != 0) {
            $text = implode('，', $errArr);
            return "请先完善资料（{$text}）";
        }
        return true;
    }

}
