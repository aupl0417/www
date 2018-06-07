<?php
/**
 * PHP SDK for QQ登录 OpenAPI
 * @brief 本文件作为demo的配置文件。
 *
 */





//申请到的appid
define('app_id','101198596');
//申请到的appkey
define('app_secret', '1c4dee65aee5e897fa619fd88ea40831');

//QQ登录成功后跳转的地址,请确保地址真实可用，否则会导致登录失败。
//成功授权后的回调地址
define('my_url','http://17yueke.cn/Home/Userregsign/qqCallbank');

//授权的接口，也就是需要调用的接口,如若需要调用多个接口，则只需要每个接口中以，逗号分隔
define('scope', 'get_user_info')
?>
