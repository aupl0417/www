<?php
namespace Module;

class Memcache{
	private static $_memcache;
	private static $instance;
    private function __construct() {  
        self::$_memcache = new \Think\Cache\Driver\Memcache;
    } 

    public static function getInstance() {  
        if (!(self::$instance instanceof self))  
        {  
            self::$instance = new self();  
        }  
        return self::$instance;
    } 
	
	private function __clone() {} 
	
	/**
	 * 根据传入参数生成mc_key
	 * @param array $param 
	 */
	public static function getKey($param, $service) {
		$parString = self::sortParam($param);
		$service = $service ? $service : getService();
		$key = $service. $parString;
		return md5($key);
	}
	
	/**
	 * 读取缓存
	 * @parm String $key 
	 */
    public static function _get($key) {
        $api_name = getService();
        if (! self::is_open($api_name)) {
            return false;
        }
		return self::$_memcache->get($key);
	}
	
	/**
	 * 缓存写入
	 * @param String $key  
	 * @param String $value  
	 * @param int $expire 
	 */
	public static function _set($key, $value, $service) {
        $service = $service ? $service : getService();
        $expire = self::expire($service);
		return self::$_memcache->set($key, $value, $expire);
	}
	
    /**
	 * 缓存删除
	 * @param String $key  
	 */
	public static function _delete($key) {
        return self::$_memcache->rm($key);
	}
	
	/**
	 * 清除缓存
	 */
	public static function _clear() {
		return self::$_memcache->clear();
	}
	
	/**
	 *  防并发处理
	 *  @param int $time (/s)
	 */
	public static function preventRefrsh($expire = 6, $user_id){
		 if (! $user_id) 
		     return true;
         $key = "API_MC_TO_PRE_REFRESH" . getService(). GetIP() . $user_id;
         $time = self::_get($key);
         if ($time === false) {
         	self::_set($key,time(),$expire);
         	return true;
         }
         
         if ((time() - $time) < $expire) {
         	 return false;
         } else {
	         // 超过或等于指定时间没解锁,则自动解锁
		     self::_delete($key);
			 self::_set($key,time(),$expire);
         }
        return true; 
	}
	
	/**
	* 参数排序组合
	*/
	public static function sortParam($param = array()) {
        $sortStr = '';
	    if (is_array($param)) {
	        ksort($param);
	        if ($key == 'api_sign' || $key == 'api_key' || $key == 'ver' ) {
                 continue;
            }
            while (list($key, $val) = each($param)) {
                $sortStr .= html_entity_decode($val, ENT_COMPAT);
            }
        }
        return $sortStr;
    }

    /**
     * 接口开关控制
	 * 默认全部打开  1:打开 2：关闭
	 * @param sting $interface
     */
    public static function is_open($interface) {
    	
        $api = self::cacheConfig(2);
        if (!array_key_exists($interface, $api) || $api[$interface] == 1) {
        	return true;
        } else {
        	return false;
        }
	}
	
	/**
	 * 接口缓存时间控制
	 * @param sting $interface
	 */
    public static function expire($interface) {
    	// 数据库配置缓存
		$api = self::cacheConfig(1);
		$time = $api[$interface] ? $api[$interface] : C('DATA_CACHE_TIME');
		return $time;
	}
	
	/**
	 * 读取缓存配置 
	 */
	public function cacheConfig($type) {
		$key    = C('API_MEMCACHE_CONFIGS_KEY');
	    $config = C('API_MEMCACHE_CONFIG');
	    $cache  = self::$_memcache->get(md5($key));
	    $result = $cache['API_MEMCACHE_CONFIG'][$config[$type]];
	    if (! $result) {	  
	        $result =  self::getConfig();
	    	$result = $result['API_MEMCACHE_CONFIG'][$config[$type]];
	    }
	    return $result;
	}
	
	/**
	 * 数据库读取配置
	 * @param int $type 2: 开关 , 1：时间
	 */
	public static function getConfig() {
	    $mcModel = new \Model\Cache\Memcache;
	    $field   = array('interface', 'set_value', 'is_open');
	    $where   = array('type' => 2);
	    $config  = $mcModel->_getList($where, $field);
        $config_time = array();
        $config_open = array();
                
        foreach ($config as $k => $v) {
            $config_time[$v['interface']] = $v['set_value'];
            $config_open[$v['interface']] = $v['is_open'];
        }
        
        $cacheArry = array();
	    $key       = C('API_MEMCACHE_CONFIGS_KEY');
	    $mc_config = C('API_MEMCACHE_CONFIG');
	    $cacheArry['API_MEMCACHE_CONFIG'][$mc_config[1]] = $config_time;
	    $cacheArry['API_MEMCACHE_CONFIG'][$mc_config[2]] = $config_open;	    
	    
	    self::$_memcache->set(md5($key), $cacheArry, 0);
	    return $cacheArry;
	}
	
}