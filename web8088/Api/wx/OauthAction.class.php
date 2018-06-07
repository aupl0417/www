<?php
/*
*oauth2验证
*
*/
//class OauthAction extends Action {
class OauthAction{
    /**
     *  静默授权
     * @param unknown $wx_app_id
     * @param unknown $my_wx_uri
     */
    public function index($wx_app_id,$my_wx_uri, $state=1){
        $oauthUrl='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$wx_app_id.'&redirect_uri='.$my_wx_uri.'&response_type=code&scope=snsapi_base&state='.$state.'#wechat_redirect';
        header('Location:'.$oauthUrl);
	}


	/**
	 * 弹出授权界面
	 * @param unknown $wx_app_id
	 * @param unknown $my_wx_uri
	 */
	public function re_index( $wx_app_id , $my_wx_uri, $state=1){
	    $oauthUrl='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$wx_app_id.'&redirect_uri='.$my_wx_uri.'&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
	    header('Location:'.$oauthUrl);
	}

	/**
	 * 授权后获取微信资料
	 * @param unknown $access_token
	 * @param unknown $openid
	 * @param unknown $code
	 * @return mixed
	 */
	public function getWxUserInfo($access_token,$openid,$code){
        $userinfo_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
	    $userinfo_json = $this->curlGet($userinfo_url);
	    $userinfo_array = json_decode($userinfo_json, true);
	    return $userinfo_array;
	}


    /**
     * 获取access_token
     * @param unknown $api
     * @param unknown $secret
     * @param unknown $code
     * @return mixed
     */
	public function access_token($api,$secret,$code){
	    $url_get='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$api.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
	    $json=json_decode($this->curlGet($url_get), true);
	    return $json;
	}
	
	
	
	/**
	 * curl---get
	 * @param unknown $url
	 * @return mixed
	 */
	function curlGet($url){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		return $temp;
	}


}
