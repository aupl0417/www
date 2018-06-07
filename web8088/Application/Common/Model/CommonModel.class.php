<?php

namespace Common\Model;
use Think\Model;

class CommonModel extends Model {

	/**
	 * 每页显示条目数
	 * @var int
	 */
	public $rowlist = 10;


	/**
	 * 检测上传是否成功，成功会上传的
	 * @return multitype:NULL string |multitype:string NULL
	 */
	public function validateUpload($maxSize, $dir, $fileKey, $sizes = null) {
		// 实例化并配置上传类
		$upload = new \Think\Upload();
		$upload->maxSize = $maxSize;
		$upload->rootPath = "./Public/Uploads/$dir/";
		$upload->savePath = '';
		$upload->saveName = array('uniqid','');
		$upload->exts     = array('jpg', 'png', 'jpeg', 'bmp');
        $upload->mimes    = array('image/jpg', 'image/jpeg', 'image/png', 'application/x-MS-bmp', 'image/nbmp', 'image/vnd.wap.wbmp');
		$upload->autoSub  = true;
		$upload->subName  = array('date','Y/m/d');
		// 上传哦！
		$info = $upload->uploadOne($_FILES[$fileKey]);
		if (!$info) {
			// 上传失败
			return array(null, $upload->getError());
		}

		if ($sizes !== null) {
			// 生成缩略图
			$imgPath = "./Public/Uploads/$dir/".$info['savepath'].$info['savename'];
			$image = new \Think\Image();
			$image->open($imgPath);
			// 按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.jpg
			$image->thumb($sizes[0], $sizes[1], \Think\Image::IMAGE_THUMB_FIXED)->save($imgPath);
		}

		// 上传成功
		return array("$dir/".$info['savepath'].$info['savename'], null);
	}

    /**
     * 生成短信验证码
     */
    protected function createSmsToken($phone) {
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
		session("shop_sms.sms_token", $token);

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
    * 发送邮件给管理员
    */
    protected function sendEmailToManager($msg) {
        // require_once(realpath('Api/sms/sms_send.php'));
        // sendnote('18826480831', urlencode(iconv('utf-8', 'gbk', $msg)));
        sendMail('412361010@qq.com', $msg, '17约课：您有新消息了');
        sendMail('455295000@qq.com', $msg, '17约课：您有新消息了');
    }


}
