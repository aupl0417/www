<?php
return array(
    //'配置项'=>'配置值'
    'DEFAULT_MODULE'   => 'Haipin',  // 默认模块
    'LOG_HANDLER_PATH' => '/apps/logs/api/',
    'LOAD_EXT_CONFIG'  => array('APP'=>'app','EX'=>'exception'),
    'LOAD_DB_CONFIG'   => 'db',//加载数据库配置
    'SHOW_ERROR_MSG'   => false,
    // 'REQUEST_HAPI_URL' => 'http://hapi.taqu.haipin.com/index.php?',
    'REQUEST_HAPI_URL' => 'http://aupl0417.sinaapp.com/Hapi/index.php?',
	'POST_IMG_UPLOAD'=>dirname(dirname(dirname(__FILE__))).'/Public/post_img/',
	'API_PUBLIC'=>dirname(dirname(dirname(__FILE__))).'/Public',
    //默认值
    'DEFAULT_LIST_NUMS'     => 10, //分页默认值    
    //'SESSION_TYPE'          => 'Memcache',
    /*Cookie*/
    //'COOKIE_EXPIRE'         =>  0,    // Cookie有效期
    'COOKIE_DOMAIN'           =>  '.domain.com',      // Cookie有效域名
    //'COOKIE_PATH'           =>  '/',     // Cookie路径
    //'COOKIE_PREFIX'         =>  '',      // Cookie前缀 避免冲突
    'SESSION_OPTIONS'=>array('domain'=>'.domain.com'),
    /* SESSION 和 COOKIE 配置 */
    'SESSION_PREFIX' => '', //session前缀
    'COOKIE_PREFIX'  => '', // Cookie前缀 避免冲突
    'APP_IMG_URL'    => '',//APP图片域名
	'UCENTER_SERVER_URL'	 => '',
);