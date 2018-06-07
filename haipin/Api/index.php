<?php
/**
 * haipin API 应用入口文件
 * @version     1.0 
 */

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',True);

// 定义应用目录
define('APP_PATH','../Api/');

// 引入ThinkPHP入口文件
require '../ThinkPHP/ThinkPHP.php';


$serviceName= getService();

// 调用服务
$serviceArr = explode(".", $serviceName);
if (!is_array($serviceArr) || count($serviceArr) != 3) {
    Ex(404);
}

exit();
