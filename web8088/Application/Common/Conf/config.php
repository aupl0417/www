<?php
return array(

        /* 模块映射 */
        //'URL_MODULE_MAP'    =>    array('onesevenlesson' => 'admin'),
		/* 模块相关配置 */
		
		'DEFAULT_MODULE'     => 'Admin',
		'MODULE_DENY_LIST'   => array('Common'),
		'MODULE_ALLOW_LIST'  => array('Home','Admin','Api','Desktop'),

        // 开启路由
        'URL_ROUTER_ON'  => true,
        'URL_ROUTE_RULES'=>array(
            'g/:gid'            => '/home/look_group/onedetail/gid/:1',
            's/:id'            => '/home/shopkeeper/cont/id/:1',
        ),

		'APP_SUB_DOMAIN_DEPLOY'=>1, // 开启子域名配置
		'APP_SUB_DOMAIN_RULES'=>array(   
			'admin.web8088.com'=>array('Admin/'),  // admin域名指向Admin分组
			'www.web8088.com'=>array('Desktop/'),  // test域名指向Test分组
			'web.web8088.com'=>array('Home/'),  // test域名指向Test分组
		),

		/* 模板设置 */
		'TMPL_PARSE_STRING'		=>	array(
				'__ACSS__'		=>	'/Public/Admin/css',
				'__AJS__'		=>	'/Public/Admin/js',
				'__AIMG__'		=>	'/Public/Admin/img',
				'__HCSS__'		=>	'/Public/Home/css',
				'__HJS__'		=>	'/Public/Home/js',
				'__HIMG__'		=>	'/Public/Home/img',
				'__HUP__'          =>'/Public/Uploads/cate/',
		        '__ADIMG__'          =>'/Public/Uploads/advert/',
				'__UPLOAD__'	=>	'/Public/Uploads/',
				'__IMGDEFAULT__'=>	'/Public/Home/img/iPhone.png',
		        '__PCJS__'      =>  '/Public/Desktop/js',
		        '__PCIMG__'     =>  '/Public/Desktop/img',
		        '__PCCSS__'     =>  '/Public/Desktop/css',

		),
		//用户注册验证码
		'verifycode'=>array(
				'fontSize' => 17,//字体大小
				'imageW' => 124,//字体大小
				'imageH' => 43,//字体大小
				'length' => 4, //长度
				'useImgBg'=>false,//是否使用背景图片 默认为false
				'bg'    => array(243, 251, 254),//验证码背景颜色 rgb数组设置，例如 array(243, 251, 254)
				'useNoise'  =>true,
		),

                /* 数据库设置 */
                // 'DB_TYPE'               =>  'mysql',     // 数据库类型
                // 'DB_HOST'               =>  '120.24.67.54', // 服务器地址
                // 'DB_NAME'               =>  'lesson',          // 数据库名
                // 'DB_USER'               =>  'lesson',      // 用户名
                // 'DB_PWD'                =>  '31a9d59fc7',        // 密码
                // 'DB_PREFIX'             =>  'ls_',    // 数据库表前缀

		/* 数据库设置 */
		'DB_TYPE'               =>  'mysql',     // 数据库类型
		'DB_HOST'               =>  'localhost', // 服务器地址
		'DB_NAME'               =>  'lession',          // 数据库名
		'DB_USER'               =>  'root',      // 用户名
		'DB_PWD'                =>  '',        // 密码
		'DB_PREFIX'             =>  'ls_',    // 数据库表前缀

//    /* 数据库设置 */
//    'DB_TYPE'               =>  'mysql',     // 数据库类型
//    'DB_HOST'               =>  'localhost', // 服务器地址
//    'DB_NAME'               =>  'lesson',          // 数据库名
//    'DB_USER'               =>  'root',      // 用户名
//    'DB_PWD'                =>  'root',        // 密码
//    'DB_PREFIX'             =>  'ls_',    // 数据库表前缀


   // 'DB_TYPE'               =>  'mysql',     // 数据库类型
   // 'DB_HOST'               =>  'localhost', // 服务器地址
   // 'DB_NAME'               =>  'lesson',          // 数据库名
   // 'DB_USER'               =>  'root',      // 用户名
   // 'DB_PWD'                =>  '',        // 密码
   // 'DB_PREFIX'             =>  'ls_',    // 数据库表前缀

		/* 数据缓存设置 */
		'DATA_CACHE_TIME'       =>  24 * 60 * 60,      // 数据缓存有效期 0表示永久缓存

		/* 令牌验证 */
		'TOKEN_ON'      =>    false,  // 是否开启令牌验证 默认关闭

		/* URL设置 */
		'URL_CASE_INSENSITIVE'  =>  true,	// 默认false 表示URL区分大小写 true则表示不区分大小写
		'URL_MODEL'             =>  3,		// URL访问模式, 2 (REWRITE  模式);

		/* 其他 */
		// 盐
		'SALT'					=>	'123',


		/* 默认头像路径  */
		'all_avatar'            =>	'/Public/Uploads/avatar',
        'default_avatar'        =>array(
            '1' =>  '/Public/Uploads/avatar/1.jpg',
            '2' =>  '/Public/Uploads/avatar/2.jpg',
        ),
		/* 用户头像路径  */
		'user_avatar'            =>	'/Public/Uploads/useravatar',

		/* 百度地图的ak */
		'ak'                     => '4b312ce0e3931ab65e07ba2c59a3c152',

		/* 用户大V认证 */
		'user_v'				=>	'/Public/Uploads/userv',
		/*cookise登录中的base64的32位加密密匙*/
		'basekey'				=>	'cfff26517ee0a9175340b9a1bc98dd5c',
		/*cookise加密前先进行的字符串拼接密匙*/
		'basestr'				=>	'b3d24aa6d351641389b94febcedddf3b',

         /* 约课模式的配置 */
        'mode'=>array(
            '1' => '全日制',
            '2' => '工作日',
            '3' => '周末班',
            '4' => '寒暑假',
            '5' => '网络班',
            '6' => '其他',
        ),
        //给后台管理员发送  发布约课时  的信息
        'managers'=>array(
            '1' =>  13580320658,
        ),
        'managersAmail'=>array(
            '1' =>  '742433087@qq.com',
            '2' =>  '455295000@qq.com',
        ),
		

        //邮件发送配置信息
        'mail_config'=>array(
            'Host'      =>  'smtp.mxhichina.com',// smtp邮件服务器的地址
            'Username'  =>  'welcome@17yueke.cn',//smtp邮件服务器的账号
            'Password'  =>  'Aa123456',//smtp邮件服务器的密码
            'FromName'  =>  '乐莘网络科技有限公司-17约课',//发件人名称
        ),
        //访客的默认头像
        'visitor_config'=>array(
            'avatar'    =>  '/Public/Home/img/visitor_avatar.png',
        ),


        'wx_config'=>array(
            'AppID'=>'wx36aa9de7f20096aa',
            'AppSecret'=>'',
        ),

);
