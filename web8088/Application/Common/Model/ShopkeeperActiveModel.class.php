<?php

namespace Common\Model;
use Common\Model\CommonModel;

/**
 * 商家激活信息Model
 * @author jmjoy
 *
 */
class ShopkeeperActiveModel extends CommonModel {
	
	public $subject = "激活邮箱";
	
	public $body = "<p>激活地址：<a href=\"%1\$s\">%1\$s</a></p><p>此邮件乃系统自动发送，请勿回复。</p>";
	
	/**
	 * 发送激活邮件
	 * @param unknown $sid
	 * @param unknown $email
	 * @param unknown $baseUrl
	 * @return boolean
	 */
	public function sendActiveEmail($sid, $email, $baseUrl) {
		$companyEmail = M('Shopkeeper')->where('id = %d', $sid)
										->getField('company_email');
		if ($companyEmail != $email) {
			return "这个邮箱是您的吗";
		}
		$token = $this->createToken($sid);
		if ($token === false) {
			return "激活码生成失败";
		}
		
		$url = U($baseUrl, array('token' => $token . $sid), true, true);
		$this->body = sprintf($this->body, $url);
		
		sendMail($email, $this->body, $this->subject);
		
		return true;
	}
	
	/**
	 * 生成激活码
	 * @param unknown $sid
	 * @return string
	 */
	public function createToken($sid) {
		$token = md5(uniqid() . mt_rand(0, 100));
		$this->sid = $sid;
		$this->token = $token;
		$this->ctime = current_datetime();
	
		// 先删除
		$this->where('sid = %d', $sid)->delete();
		// 在添加
		$this->add();
		
		return $token;
	}
	
	/**
	 * 处理激活
	 * @param unknown $token
	 * @return string|boolean
	 */
	public function handleActive($token) {
		if (!$token) {
			return '没收接收到激活码';
		}
		
		$sid = substr($token, 32);
		$token = substr($token, 0, 32);
		if (!$sid || !$token) {
			return '激活码不正确';
		}
		
		$data = $this->where('sid = %d', $sid)->find();
		if (!$data) {
			return '数据库没有找到';
		}
		
		if ($data['token'] != $token) {
			return '激活码不正确';
		}
		
		$ctime = parse_datetime($data['ctime']);
		if ($ctime + 24 * 60 * 60 < time()) {
			return '已经超过了24小时，请重新发送激活邮件';
		}
		
		// 把没用的激活码给删了
		$this->where('sid = %d', $sid)->delete();
		D('Common/Shopkeeper')->activeOne($sid);
		
		return true;
	}
	
}