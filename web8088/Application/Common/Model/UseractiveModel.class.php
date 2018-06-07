<?php

namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 用户激活信息Model--激活用户邮箱
 * @author users
 *
 */
class UseractiveModel extends CommonModel {
	
	public $subject = "邮箱激活";
	
	public $body = '<div style="text-align: -webkit-auto;"><span class="Apple-style-span" style="font-family: arial, sans-serif; border-collapse: collapse; "><b><span class="Apple-style-span" style="font-size: 18px;">
						尊敬的%s:</span></b></span></div>
					<div style="text-align: -webkit-auto;"><span class="Apple-style-span" style="font-family: arial, sans-serif; border-collapse: collapse; "><br  /></span></div>
					<div style="text-align: -webkit-auto;"><span class="Apple-style-span" style="font-size:18px;"><span class="Apple-style-span" style="font-family: arial, sans-serif; border-collapse: collapse; "></span>
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 尊敬的客户，您正在进行邮箱激活服务。邮箱激活码为（<a href="http://17yueke.cn/">17yueke.cn</a>）。
					<br  /><p></p>
					<b>请点击以下链接完成确认：</b>
					<a href="%s">%s</a>
					<br  /><p></p>
						如果链接不能点击，请复制地址到浏览器，然后直接打开。
					<br  /><p></p>
					</span></div>
					<div style="text-align: center;"><span class="Apple-style-span" style="font-size:22px;">17约课</span></div>
					';
	
	/**
	 * 发送激活邮件
	 * @param number $uid
	 * @param string $email
	 * @param string $baseUrl跳转的路径
	 * @return string|boolean
	 */
	public function sendActiveEmail($uid=0, $email='', $baseUrl='') {
		$token = $this->createToken($uid);//删除原来的验证码，并且生成新年的验证码
		if ($token === false) {
			return "激活码生成失败";
		}
		$url = U($baseUrl, array('token' => $token . $uid), true, true);
// 		$this->body = sprintf($this->body,session('user.name'), $url); //把格式化的字符串写入一个变量中
		$this->body = sprintf($this->body,'用户', $url,$url); //把格式化的字符串写入一个变量中
		sendMail($email, $this->body, $this->subject);
		
		return true;
	}
	
	/**
	 * 生成激活码，并保存在数据库中
	 * @param unknown $uid
	 * @return string
	 */
	public function createToken($uid=0) {
		$token = md5(uniqid() . mt_rand(0, 100));//验证码32位的
		$this->uid   = $uid;
		$this->token = $token;
		$this->ctime = date('Y-m-d H:i:s');
	
		// 先删除
		$this->where('uid = %d', $uid)->delete();
		// 在添加
		$this->add();
		
		return $token;
	}
	
	/**
	 * 处理激活
	 * @param munber $token
	 * @return string|boolean
	 */
	public function handleActive($token=0) {
		if (!$token) {
			return '没收接收到激活码';
		}
		
		$uid = substr($token, 32);//32位之后的字符就是uid
		$token = substr($token, 0, 32);
		
		if (!$uid || !$token) {
			return '激活码不正确';
		}
		
		$data = $this->where('uid = %d', $uid)->find();
		
		if (!$data) {
			return '数据库没有找到';
		}
		
		if ($data['token'] != $token) {
			return '激活码不正确';
		}
		
		$ctime = strtotime($data['ctime']); //变成时间戳
		if ($ctime + 24 * 60 * 60 < time()) { //时间对比是否大于24小时
			return '已经超过了24小时，请重新发送激活邮件';
		}
		
		// 把没用的激活码给删了
		$this->where('uid = %d', $uid)->delete();
		D('Common/User')->actOne($data['uid']);//激活用户的邮箱
		
		return true;
	}
	
}