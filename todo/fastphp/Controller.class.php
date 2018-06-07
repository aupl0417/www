<?php 

class Controller {
	
	protected $_controller;
	protected $_action;
	protected $_view;
	
	function __construct($controller, $action, $param = array()){
		$this->_controller = $controller;
		$this->_action     = $action;
		
		if($param){
			foreach($param as $key=>$val){
				$key % 2 == 0 ? $data_key[] = $val : $data_value[] = $val;
			}
			$param = array_combine($data_key, $data_value);
		}
		
		if($_POST){
			$param = array_merge($param, $_POST);
		}
		
		if($_COOKIE){
			$param = array_merge($param, $_COOKIE);
		}
		
		if($_SESSION){
			$param = array_merge($param, $_SESSION);
		}
		
		if($_FILES){
			$param['Files'] = array_merge($param, $_FILES);
		}
		
		is_array($param) && array_walk_recursive($param, 'filter_exp');
		
		$filters = DEFAULT_FILTER ? DEFAULT_FILTER : 'htmlspecialchars,strip_tags';
		$filters = explode(',', $filters);
		foreach($filters as $filter){
			if(function_exists($filter)) {
                $param   =   is_array($param) ? array_map_recursive($filter, $param) : $filter($param); // 参数过滤
			}else{
				$param   =   filter_var($param, is_int($filter) ? $filter : filter_id($filter));
				if(false === $param) {
					return isset($default) ? $default : NULL;
				}
			}
		}
		
		$this->option = $param;
		
		// $this->_view       = new View($controller, $action);
	}
	
	function set($name, $value){
		$this->_view->set($name, $value);
	}
	
	function get(){
		
	}
	
	function __destruct(){
		// $this->_view->render();
	}
	
}