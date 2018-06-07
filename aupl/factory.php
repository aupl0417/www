<?php
interface IUser {
	function getName();
}

class User implements IUser {
	private $userId;
	public static function load($id){
		return new User($id);
	}
	
	public static function create(){
		return new User(null);
	}
	
	public function __construct($id = null){
		$this->userId = $id == null ? '' : $id;
	}
	
	public function getName(){
		return 'jack' . $this->userId;
	}
	
	
}

class Test {
	function index(){
		
		// $user = User::load(1);
		// echo $user->getName();
		$user = new User();
		$newUser = $user::load(1);
		
		echo $newUser->getName();
	}
}

$test = new test();
echo $test->index();

// $user = User::load(0);
// echo $user->getName();



?>