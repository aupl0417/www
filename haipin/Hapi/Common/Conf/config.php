<?php
return array(
    //'配置项'=>'配置值'
    'DEFAULT_MODULE'   => 'Haipin',  // 默认模块
    'LOG_HANDLER_PATH' => '/apps/logs/hapi/',
    'LOAD_EXT_CONFIG'  =>  array('APP'=>'app','EX'=>'exception'),
    'LOAD_DB_CONFIG'   => 'db',//加载数据库配置
    'SHOW_ERROR_MSG'   => false,


    /* 数据库设置 */
    'DB_FIELDTYPE_CHECK'    =>  false,     // 是否进行字段类型检查
    'DB_FIELDS_CACHE'       =>  true,      // 启用字段缓存
    'DB_CHARSET'            =>  'utf8',     // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        =>  0, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        =>  false,       // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         =>  1, // 读写分离后 主服务器数量
    'DB_SLAVE_NO'           =>  '', // 指定从服务器序号
    'DB_SQL_BUILD_CACHE'    =>  false, // 数据库查询的SQL创建缓存
    'DB_SQL_BUILD_QUEUE'    =>  'file',   // SQL缓存队列的缓存方式 支持 file xcache和apc
    'DB_SQL_BUILD_LENGTH'   =>  20, // SQL缓存的队列长度
    'DB_SQL_LOG'            =>  false, // SQL执行日志记录
    'DB_BIND_PARAM'         =>  false, // 数据库写入数据自动参数绑定

    /* 数据缓存设置 */
    'DATA_CACHE_TIME'       =>  3600,      // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_COMPRESS'   =>  false,   // 数据缓存是否压缩缓存
    'DATA_CACHE_CHECK'      =>  false,   // 数据缓存是否校验缓存
    'DATA_CACHE_PREFIX'     =>  '',     // 缓存前缀
    'DATA_CACHE_TYPE'       =>  'File',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    'DATA_CACHE_PATH'       =>  TEMP_PATH,// 缓存路径设置 (仅对File方式缓存有效)
    'DATA_CACHE_SUBDIR'     =>  false,    // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    'DATA_PATH_LEVEL'       =>  1,        // 子目录缓存级别

    //统一参数配置
    'LIST_PAGE_NUMS'        => 10, //默认列表页
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
    'UCENTER_SERVER_URL'     => '',
	// 数据库数据加密秘钥
	'UC_KEY'                => '',


);