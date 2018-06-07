<?php
/**
 * PHP SDK for wx登录 OpenAPI
* @brief 本文件作为demo的配置文件。
*
*/


// define("TOKEN", "weixin0827");//改成自己的TOKEN
// define('WX_APP_ID', 'wx0d32683dca7ad665');//改成自己的APPID
// define('WX_APP_SECRET', '31fe71a2bd78adaffcf761d1bb82ffe3');//改成自己的APPSECRET

define("TOKEN", "user20150522175215");//改成自己的TOKEN
define('WX_APP_ID', 'wx36aa9de7f20096aa');//改成自己的APPID
define('WX_APP_SECRET', '7c6a200a103b5bea3f9ed1acd192321d');//改成自己的APPSECRET





//申请到的appid
// define('wx_app_id','wx0d32683dca7ad665');
// define('wx_app_id','wx36aa9de7f20096aa');
//申请到的appkey
// define('wx_app_secret', '31fe71a2bd78adaffcf761d1bb82ffe3');
// define('wx_app_secret', '7c6a200a103b5bea3f9ed1acd192321d');

//回调地址
define('my_wx_uri','http://17yueke.cn/home/Wx/wxCode');
//oauth验证成功授权后的回调地址
define('my_re_wx_uri','http://17yueke.cn/home/Wx/reWxCode');

//回调地址--关联已有账号的
define('rel_wx_uri','http://17yueke.cn/home/Wx/wxRelation');

//回调地址--获取openid--判断是否已经登录
define('estimate_wx_url','http://17yueke.cn/Home/Wx/CallBack');
//授权的接口，也就是需要调用的接口,如若需要调用多个接口，则只需要每个接口中以，逗号分隔
// define('scope', 'get_user_info')


// 商家微信登陆跳转地址
define('SHOP_WX_URI', urlencode('http://17yueke.cn/Home/Shopkeeper/wxToken'));
define('SHOP_RE_WX_URI', urlencode('http://17yueke.cn/Home/Shopkeeper/reWxToken'));

?>
