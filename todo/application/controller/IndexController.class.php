<?php

class IndexController extends Controller{
	
	function index(){
		
		
		
		// $class = new ClassModel();
		// $class = new AttribModel();
		$data = array(
			'username' => 'aupl',
			'age'      => 25
		);
		// $data1 = $data ?: array();
		// dump($data1);die;
		$where = array(
			'cl_id' => 123
		);
		$class = D('Class');
		dump(D('Class')->table()->data($data)->where($where));die;
		// $result = $class->getData();
		$result = $class->getData();
		echo $class->getLastSql();
		echo '<pre>';
		var_dump($result);
		// var_dump($class->getLastId());
	}
	
	function test(){
		dump($this->option);die;
	}
	
}