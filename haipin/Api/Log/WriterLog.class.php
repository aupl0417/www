<?php
namespace Log;
class WriterLog extends \Think\Log {

	
    public function __construct() 
    {
    }
	
    /**
	 * 调用haipin日志记录
	 * @param string $message
	 * @param string $logType
	 * @param string $type=in,out
	 * @return void
	 */
    public static function AccessLog($message, $logType = 'info')
    {
        $request = I('request.');
        
        $content = '';
        // 请求方式
        if (!empty($_SERVER['REQUEST_METHOD'])) {
            $content .= '[' . $_SERVER['REQUEST_METHOD'] . ']-';
        }
         // 服务接口
        $content .= '[' . getService() . ']-';
        // 请求信息
        $content .= '[request ' . self::_getRequestUrl($request) . ']-';
        // 访问信息
        $content .= '[message ' . $message . ']';
        self::_AddLog($logType, $content);
    }
    
   
    /**
	 * 手动写入日志
	 * @param string $logType 日志类型
	 * @param int $isReal 0:非实时  1：实时
	 * @return void
	 */
    private static function _AddLog($logType = 'info', $message, $isReal = 0)
    {
        $logPath = C('LOG_HANDLER_PATH');
    	if (!$logPath) {
            $logPath = RUNTIME_PATH . '/Logs/Handler/';
        }
        if (!file_exists($logPath)) {
            mkdir($logPath);
        }

        
        $content  = '[HANDLER]-';
        $content .= '[' . date('Y-m-d H:i:s', NOW_TIME) . ']-';     // 日志时间
        $content .= '[' . $_SERVER['REMOTE_ADDR']  . ']-';    // 客户端 IP
        $content .= $message;                                   // 日志内容
        // 记录 log
        C('LOG_PATH', $logPath . $logType . '_');
        
        if ($isReal) {
        	\Think\Log::write($content , 'INFO');
        } else {
        	\Think\Log::record($content ,'INFO');
        }
        
    }
    
    private static function _getRequestUrl($request)
    {
        // 请求域名
        $requestUrl = 'http://' . $_SERVER['HTTP_HOST'];
        // 请求端口
        if (!empty($_SERVER['SERVER_PORT'])) {
            $requestUrl .= ':' . $_SERVER['SERVER_PORT'];
        }
        // 请求文件名称
        if (!empty($_SERVER['SCRIPT_NAME'])) {
            $requestUrl .= '' . $_SERVER['SCRIPT_NAME'];
        }
        // 请求参数
        if (!empty($request) && is_array($request)) {
            $array = array();
            foreach ($request as $k => $v) {
                $array[] = $k . '=' . $v;
            }
            $requestUrl .= '?' . implode('&' ,$array);
        }
        return $requestUrl;
    }

}