<?php
	interface UserInterface {
		function getName();
	}
	
	interface TeacherInterface {
		function getLengthOfService();
		function getTeacherName();
	}
	
	class User implements UserInterface {
		private $name;
		
		public function __construct($name){
			$this->name = $name ? $name : 'tom';
		}
		
		public function getName(){
			return $this->name;
		}
	}
	
	class Teacher implements TeacherInterface {
		private $user;
		private $lengthOfService;
		
		public function __construct($name, $age){
			$this->user = new User($name);
			$this->lengthOfService = $age ? $age : 5;
		}
		
		public function getLengthOfService(){
			return $this->lengthOfService;
		}
		
		public function getTeacherName(){
			return $this->user->getName();
		}
	}
	
	class GraduateStudent extends User implements TeacherInterface {
		private $teacher;
		
		public function __construct($name, $age){
			parent::__construct($name);
			$this->teacher = new Teacher($name, $age);
		}
		
		public function getLengthOfService(){
			return $this->teacher->getLengthOfService();
		}
		
		public function getTeacherName(){
			return $this->teacher->getTeacherName();
		}
	}
	
	class Act {
		public static function getUserName(TeacherInterface $_User){
			echo "Name is ". $_User->getTeacherName();
		}
		
		public static function getLengthOfService(TeacherInterface $_Teacher){
			echo "Age is " . $_Teacher->getLengthOfService();
		}
	}
	
	$graduateStudent = new GraduateStudent('aupl0417', 4);
	Act::getUserName($graduateStudent);
	Act::getLengthOfService($graduateStudent);

?>