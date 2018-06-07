<?php

namespace Home\Behaviors;

class ShopkeeperAutoLoginBehavior extends \Think\Behavior{
	
	//行为执行入口
	public function run(&$param){
		
		// 如果商家没有登录
		if (!session('shopkeeper')) {
			$autoCookie = cookie('shop_auto_login');
			
			// 存在自动登录Cookie
			if ($autoCookie) {
				// 解密
				$raw = decrypt($autoCookie, C('basekey'));
				
				// 分解出id和密码，凡是不成功都不是好人
				if(!$raw) {
					cookie('shop_auto_login', null);
					return;
				}
				list($id, $encryptedPasswd) = explode('|', $raw);
				if (!$id || !$encryptedPasswd) {
					cookie('shop_auto_login', null);
					return;
				}
				
				// 查询数据库 
				$bool = D('Common/Shopkeeper')->autoLogin($id, $encryptedPasswd);
				
				// 登录失败，被伪造数据了，用户一定不是好人
				if (!$bool) {
					cookie('shop_auto_login', null);
				}
				
			}
		}
	}
}