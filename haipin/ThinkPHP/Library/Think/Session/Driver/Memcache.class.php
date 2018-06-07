<?php
//自定义memcache处理 驱动
namespace Think\Session\Driver;
Class Memcache {
  //	memcache连接对象
  Private $memcache;
  
  //	SESSION有效时间
  Private $expire;
  
  Public function execute () {
  session_set_save_handler(
    array(&$this,"open"),
    array(&$this,"close"),
    array(&$this,"read"),
    array(&$this,"write"),
    array(&$this,"destroy"),
    array(&$this,"gc"));
  }

  //打开Session
  Public function open ($path, $name) {
    $this->expire = C('SESSION_EXPIRE') ? C('SESSION_EXPIRE') : ini_get('session.gc_maxlifetime');
    $this->memcache = new Memcache();
    return $this->memcache->connect(C('memcache_HOST'),C('memcache_PORT'));
  }
  
  Public function close () {
    return $this->memcache->close();
  }
  
  Public function read ($id) {
    $id = C('SESSION_PREFIX') . $id;
    $data = $this->memcache->get($id);
    return $data ? $data : '';
  }
  
  //写入session
  Public function write ($id, $data) {
    $id = C('SESSION_PREFIX') . $id;
    return $this->memcache->set($id, $data, $this->expire);
  }
  
  //销毁SESSION
  Public function destroy ($id) {
    $id = C('SESSION_PREFIX') . $id;
    return $this->memcache->delete($id);
  }
  
  Public function gc ($maxLifeTime) {
    return true;
  }
}