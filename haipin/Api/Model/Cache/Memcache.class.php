<?php
/**
 * 缓存
 */
namespace Model\Cache;
use Think\Model;

class Memcache extends Model  {
   protected $dbName       = 'api_admin';
    
   protected $tablePrefix  = 'api_';
    
   protected $tableName    = 'memcache';
    
   private  static $tbModel;

   public function __construct() {
       self::$tbModel = M($this->tableName, $this->tablePrefix , DB_CONFIG1);
   }

   /**
    * 获取列表
    */
   public function _getList($where, $field, $limit = 0, $page = 1) { 	  
   	   $start = ($page - 1 ) * $limit;
   	   $limits =  $limit > 0 ? "{$start}, {$limit}" : '';
       return self::$tbModel->where($where)->field($field)->limit($limits)->select();
   }
   
}