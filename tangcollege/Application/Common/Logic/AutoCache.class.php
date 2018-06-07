<?php
namespace Common\Logic;

trait AutoCache
{
    protected $cache_default;
    protected $cache_prefix;
    protected $cache_dispatch;
    private function __initAutoCache()
    {
        $this->cache_default = 60 * 60 * 24 * 7;
        $this->cache_prefix = MODULE_NAME . __CLASS__;
        $this->cache_dispatch = md5($this->cache_prefix . 'dispatch', false);
    }
    private function __setCacheName($cacheName)
    {
		$allCacheNames = $this->__getCacheNames();
        if (empty($allCacheNames) || !is_array($allCacheNames)) {
            S($this->cache_dispatch, (array) $cacheName);
        } elseif (!in_array($cacheName,$allCacheNames)) {
			array_push($allCacheNames, $cacheName);
            S($this->cache_dispatch, $allCacheNames, $this->cache_default);
        }
    }
    private function __getCacheNames()
    {
		return $this->__get($this->cache_dispatch);
    }
    public function __get($cacheName)
    {
        $data = S($cacheName);
        return empty($data) ? null : $data;
    }
    protected function __knockOut($cacheName)
    {
        S($cacheName, null);
    }
    public function __set($cacheName, $value)
    {
        return S($cacheName, $value, $this->cache_default);
    }
    public function clean()
    {
        $names = $this->__getCacheNames();
        if (!empty($names)) {
            foreach ($names as $v) {
                $this->__knockOut($v);
            }
        }
    }
    function __call($function_name, $args)
    {
       
        $getLastFlag = substr($function_name, -5);
        $true_function_name = '';
		$this->__initAutoCache();
        if ($getLastFlag == 'Cache') {
            $true_function_name = substr($function_name, 0, -5);
        } else {
            $getLastFlag = substr($function_name, -10);
            if ($getLastFlag == 'CacheClean') {
                $this->clean();
                $true_function_name = substr($function_name, 0, -10);
            }
        }
        if ($true_function_name != '') {
            $cacheName = md5($this->cache_prefix . $true_function_name . (empty($args) ? '' : serialize($args)), FALSE);
            $fromCacheResult = $this->__get($cacheName);
            //if (empty($fromCacheResult)) {
			if (1) {	
                $result = call_user_func_array([$this,$true_function_name], $args);
               // $this->__setCacheName($cacheName);
               // $this->__set($cacheName, $result);
                return $result;
            } else {
                return $fromCacheResult;
            }
        }
		return parent::__call($function_name,$args);
    }
}